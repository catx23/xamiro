<?php

namespace League\Flysystem\Adapter;

use \Google_Client;
use \Google_Service_Drive;
use \Google_Service_Drive_DriveFile;
use \Google_Service_Drive_ParentReference;
use \Google_Http_Request;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util;

class GoogleDrive extends AbstractAdapter
{
    /**
     * @var  array  $resultMap
     */
    protected static $resultMap = array(
        'Body'          => 'contents',
        'ContentLength' => 'size',
        'ContentType'   => 'mimetype',
        'Size'          => 'size',
    );

    /**
     * @var  array  $metaOptions
     */
    protected static $metaOptions = array(
        'Cache-Control',
        'Expires',
    );

    /**
     * @var  string  $code  Auth code
     */
    protected $code;

    /**
     * @var  Google_Client  $client  Google PHP Client
     */
    protected $client;
	
	/**
	 * @var Google_Service_Drive	$service	Google Drive Service
	 */
	 protected $service;

    /**
     * @var  string  $prefix  path prefix
     */
    protected $prefix;

    /**
     * @var  array  $options  default options
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @param  Google_Client  $client
     * @param  string    $client_secret
     * @param  string    $code
     * @param  array     $options
     */
    public function __construct(Google_Client $client, array $options = array())
    {
		$this->options = $options;
		
		$client->setScopes(array('https://www.googleapis.com/auth/drive'));
		$client->setAccessType('offline');
		$client->setApprovalPrompt('auto');
		
		$service = new Google_Service_Drive($client);
		$this->service = &$service;
		$this->client = &$client;
    }
	
    /**
     * Check whether a file exists
     *
     * @param   string  $path
     * @return  bool    weather an object result
     */
    public function has($path)
    {
		try {
			$this->service->files->get(basename($path));
			return true;
		} catch(\Exception $e) {
			// Exception thrown on 404
			return false;
		}
    }

    /**
     * Write a file
     *
     * @param   string  $path
     * @param   string  $contents
     * @param   mixed   $config
     * @return  array   file metadata
     */
    public function write($path, $contents, $config = null)
    {
		$mimeType = Util::guessMimeType($path, $contents);
		if (isset($config)) {
			if (isset($config["mimeType"])) {
				$mimeType = $config["mimeType"];
			}
		}
        $config = Util::ensureConfig($config);
        $options = $this->getOptions($path, array(
            'ContentType' => $mimeType,
            'ContentLength' => Util::contentSize($contents),
        ), $config);
		
		$dirname = dirname($path);
		$filename = basename($path);
		
		if ($this->has($path)) {
			$isUpdate = true;
			$file = $this->service->files->get($path);
		} else {
			$file = new Google_Service_Drive_DriveFile();
			$file->setTitle($filename);
			$file->setMimeType($mimeType);
			if ($dirname != ".") {
				$parent = new Google_Service_Drive_ParentReference();
				$parent->setId($dirname);
				$file->setParents(array($parent));
			}
		}
		
		if (isset($isUpdate)) {
			$result = $this->service->files->update($file['id'], $file, array(
			  'data' => $contents,
			  'mimeType' => $mimeType,
			  'uploadType' => "multipart"
			));
		} else {
			$result = $this->service->files->insert($file, array(
			  'data' => $contents,
			  'mimeType' => $mimeType,
			  'uploadType' => "multipart"
			));
		}

        if ($result === false) {
            return false;
        }
		
		$result['mimeType'] = $mimeType;
		$result['title'] = $filename;
		$result['id'] = $result->getId();

        return $this->normalizeObject($result);
    }

    /**
     * Write using a stream
     *
     * @param   string    $path
     * @param   resource  $resource
     * @param   mixed     $config
     *
     * @return  array     file metadata
     */
    public function writeStream($path, $resource, $config = null)
    {
        return $this->write($path, $resource, $config);
    }

    /**
     * Update a file
     *
     * @param   string  $path
     * @param   string  $contents
     * @param   mixed   $config   Config object or visibility setting
     * @return  array   file metadata
     */
    public function update($path, $contents, $config = null)
    {
        return $this->write($path, $contents, $config);
    }

    /**
     * Update a file using a stream
     *
     * @param   string    $path
     * @param   resource  $resource
     * @param   mixed        $config   Config object or visibility setting
     * @return  array     file metadata
     */
    public function updateStream($path, $resource, $config = null)
    {
        return $this->writeStream($path, $resource, $config);
    }

    /**
     * Read a file
     *
     * @param   string  $path
     * @return  array   file metadata
     */
    public function read($path)
    {
        $file = $this->service->files->get(basename($path));
		$request = new Google_Http_Request($file->getDownloadUrl(), 'GET', null, null);
		$httpRequest = $this->client->getAuth()->authenticatedRequest($request);
		if ($httpRequest->getResponseHttpCode() == 200) {
			$contents = $httpRequest->getResponseBody();
			return compact('contents', 'path');
		}

        return null;
    }

    /**
     * Get a read-stream for a file
     *
     * @param   string  $path
     * @return  array   file metadata
     */
    public function readStream($path)
    {
		$file = $this->service->files->get(basename($path));
		$request = new Google_Http_Request($file->getDownloadUrl(), 'GET', null, null);
		$httpRequest = $this->client->getAuth()->authenticatedRequest($request);
		if ($httpRequest->getResponseHttpCode() == 200) {
			$stream = $httpRequest->getResponseBody();
			return compact('stream');
		}

        return null;
    }

    /**
     * Rename a file
     *
     * @param   string  $path
     * @param   string  $newpath
     * @return  array   file metadata
     */
    public function rename($path, $newpath)
    {
        $copiedFile = new Google_Service_Drive_DriveFile();
		$copiedFile->setTitle($newpath);
		$copiedFile->setId($path);

        $result = $this->service->files->patch($path, $copiedFile, array('fields' => 'title', 'fileId' => $path));

        return $result;
    }

    /**
     * Copy a file
     *
     * @param   string  $path
     * @param   string  $newpath
     * @return  array   file metadata
     */
    public function copy($path, $newpath)
    {
        $copiedFile = new Google_Service_Drive_DriveFile();
		$copiedFile->setTitle($newpath);

        $result = $this->service->files->copy($path, $copiedFile);

        return $this->normalizeObject($result, $newpath);
    }

    /**
     * Delete a file
     *
     * @param   string   $path
     * @return  boolean  delete result
     */
    public function delete($path)
    {
        $options = $this->getOptions($path);

        return $this->service->files->delete($path);
    }

    /**
     * Delete a directory (recursive)
     *
     * @param   string   $path
     * @return  boolean  delete result
     */
    public function deleteDir($path)
    {
        $prefix = rtrim($this->prefix($path), '/') . '/';

        return $this->service->files->delete($prefix);
    }

    /**
     * Create a directory
     *
     * @param   string  $path
     * @return  array   directory metadata
     */
    public function createDir($path)
    {
        $result = $this->write(rtrim($path, '/') . '/', '', array("mimeType" => "application/vnd.google-apps.folder"));

        if ( ! $result) {
            return false;
        }

        return array('path' => $result['path'], 'type' => 'dir');
    }

    /**
     * Get metadata for a file
     *
     * @param   string  $path
     * @return  array   file metadata
     */
    public function getMetadata($path)
    {
		$path = basename($path);
        $options = $this->getOptions($path);
        $result = (array)$this->service->files->get($path);
		return $this->normalizeObject($result, $path);
    }

    /**
     * Get the mimetype of a file
     *
     * @param   string  $path
     * @return  array   file metadata
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the file of a file
     *
     * @param   string  $path
     * @return  array   file metadata
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the timestamp of a file
     *
     * @param   string  $path
     * @return  array   file metadata
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the visibility of a file
     *
     * @param   string  $path
     * @return  array   file metadata
     */
    public function getVisibility($path)
    {
        $options = $this->getOptions($path);
        $result = $this->client->getObjectAcl($options)->getAll();
        $visibility = AdapterInterface::VISIBILITY_PRIVATE;

        foreach ($result['Grants'] as $grant) {
            if (isset($grant['Grantee']['URI']) && $grant['Grantee']['URI'] === Group::ALL_USERS && $grant['Permission'] === Permission::READ) {
                $visibility = AdapterInterface::VISIBILITY_PUBLIC;
                break;
            }
        }

        return compact('visibility');
    }

    /**
     * Get mimetype of a file
     *
     * @param   string  $path
     * @param   string  $visibility
     * @return  array   file metadata
     */
    public function setVisibility($path, $visibility)
    {
        $options = $this->getOptions($path, array(
            'ACL' => $visibility === AdapterInterface::VISIBILITY_PUBLIC ? 'public-read' : 'private',
        ));

        $this->client->putObjectAcl($options);

        return compact('visibility');
    }

    /**
     * List contents of a directory
     *
     * @param   string  $dirname
     * @param   bool    $recursive
     * @return  array   directory contents
     */
    public function listContents($dirname = '', $recursive = false)
    {
		if ($dirname=='') { $dirname = "root"; }
		$dirname = basename($dirname);
		$parameters = array();
		$result = array();
		do {
			try {
			  if (isset($pageToken)) {
				$parameters['pageToken'] = $pageToken;
			  }
			  $files = $this->service->children->listChildren($dirname, $parameters);
			  foreach ($files as $file) {
				$meta = (array)$this->service->files->get($file['id'], $parameters);
				if ($dirname=="root") {
				} else {
					$meta['dirname'] = $dirname;
				}
				$result = array_merge($result, array($meta));
			  }
			  //$result = array_merge($result, $files->getItems());
			  $pageToken = $files->getNextPageToken();
			} catch (Exception $e) {
			  print "An error occurred: " . $e->getMessage();
			  $pageToken = NULL;
			}
		  } while ($pageToken);
		
        $result = array_map(array($this, 'normalizeObject'), $result);

        return Util::emulateDirectories($result);
    }

    /**
     * Normalize a result from Google
     *
     * @param   string  $object
     * @param   string  $path
     * @return  array   file metadata
     */
    protected function normalizeObject($object, $path = null)
    {
		$newObject = array();
		
		foreach ((array)$object as $key => $value) {
			if (substr($key, 0, 3)=="\0*\0") {
				$key = substr($key, 3);
			}
			$newObject[$key] = $value;
		}
		
		$object = $newObject;
		
		if (isset($object["dirname"])) {
			$result = array('path' => $object['dirname']."/".$object['id']);
		} else {
			$result = array('path' => $object['id']);
		}

        if (isset($object['modifiedDate'])) {
            $result['timestamp'] = strtotime($object['modifiedDate']);
        }

		$result["basename"] = $object["title"];
		
        if ($object['mimeType'] == 'application/vnd.google-apps.folder') {
            $result['type'] = 'dir';
            $result['path'] = rtrim($result['path'], '/');
            $result['dirname'] = Util::dirname($result['path']);
            return $result;
        }

        $result = array_merge($result, Util::map($object, static::$resultMap), array('type' => 'file'));
        $result['dirname'] = Util::dirname($result['path']);

        if (isset($result['contents'])) {
            $result['contents'] = (string) $result['contents'];
        }

        return $result;
    }

    /**
     * Get options for a Google call
     *
     * @param   string  $path
     * @param   array   $options
     *
     * @return  array   Google options
     */
    protected function getOptions($path, array $options = array(), Config $config = null)
    {
        //$options['Key'] = $this->prefix($path);

        if ($config) {
            $options = array_merge($options, $this->getOptionsFromConfig($config));
        }

        return array_merge($this->options, $options);
    }

    /**
     * Retrieve options from a Config instance
     *
     * @param   Config  $config
     * @return  array
     */
    protected function getOptionsFromConfig(Config $config)
    {
        $options = array();

        foreach (static::$metaOptions as $option) {
            if ( ! $config->has($option)) continue;
            $options[$option] = $config->get($option);
        }

        if ($visibility = $config->get('visibility')) {
            // For local reference
            $options['visibility'] = $visibility;
            // For external reference
            $options['ACL'] = $visibility === AdapterInterface::VISIBILITY_PUBLIC ? 'public-read' : 'private';
        }

        if ($mimetype = $config->get('mimetype')) {
            // For local reference
            $options['mimetype'] = $mimetype;
            // For external reference
            $options['ContentType'] = $mimetype;
        }

        return $options;
    }

    /**
     * Prefix a path
     *
     * @param   string  $path
     * @return  string  prefixed path
     */
    protected function prefix($path)
    {
        if (! $this->prefix) {
            return $path;
        }

        return $this->prefix.'/'.$path;
    }
}
