<?php

use League\Flysystem\Adapter\GoogleDrive as Adapter;
use \Google_Client;
use \Google_Service_Drive;

define("CLIENT_ID", '914720938366-1b9t1n0d87g7r429j37kh29474n301la.apps.googleusercontent.com');
define("CLIENT_SECRET", '-FqmFBTynCy6VBIYwDLeIvPm');
define("CLIENT_AUTH_TOKEN", '{"access_token":"ya29.IQD9DFJHv7U1kBgAAAAJSdBBwvJq8lmEj8f9RBsXbK5uX82vXlvlQAbn_pL2Rg","token_type":"Bearer","expires_in":3600,"refresh_token":"1\/_RAFGmxs0bQBSCCI3hJYnntuGiyXq28UGCdsW8E1cb4","created":1401030654}');
define("CLIENT_REDIR_URL", "http://localhost/GoogleDriveTests.php");

class GoogleDriveTests extends PHPUnit_Framework_TestCase
{
	protected $client;
	protected $adapter;
	protected $writtenID;
	
    function setup()
    {
		if (!defined("CLIENT_AUTH_TOKEN")) {
			die("Auth token not set, aborting.");
		}
		$this->client = new Google_Client();
		$this->client->setAccessToken(CLIENT_AUTH_TOKEN);
		$this->client->setClientId(CLIENT_ID);
		$this->client->setClientSecret(CLIENT_SECRET);
		$this->client->setScopes(array('https://www.googleapis.com/auth/drive'));
		$this->adapter = new Adapter($this->client);
    }

	public function testWrite()
    {	
		$adapter = $this->adapter;
		$result = $adapter->write('something', 'something');
        $this->assertArrayHasKey("basename", $result);
		return $result['path'];
    }
	/**
     * @depends testWrite
     */
    public function testHas($path)
    {
		$adapter = $this->adapter;
        $this->assertTrue($adapter->has($path));
		return $path;
    }
	/**
     * @depends testHas
     */
	public function testRead($path)
	{
		$adapter = $this->adapter;
		$result = $adapter->read($path);
		$this->assertEquals($result['contents'], "something");
		return $path;
	}
	/**
     * @depends testRead
     */
	public function testRename($path)
    {
        $adapter = $this->adapter;
        $result = $adapter->rename($path, "somethingElse");
		$this->assertEquals($result["title"], "somethingElse");
		return $path;
    }
	/**
     * @depends testRename
     */
	public function testUpdate($path)
	{
		$adapter = $this->adapter;
		$result = $adapter->update($path, 'somethingElse');
		$this->assertEquals($result["path"], $path);
		return $path;
	}
	/**
     * @depends testUpdate
     */
	public function testReadAgain($path)
	{
		$adapter = $this->adapter;
		$result = $adapter->read($path);
		$this->assertEquals($result['contents'], "somethingElse");
		return $path;
	}
	/**
     * @depends testReadAgain
     */
	public function testCopy($path)
	{
		$adapter = $this->adapter;
		$result = $adapter->copy($path, "somethingElseAgain");
		$this->assertEquals($result['basename'], "somethingElseAgain");
		return array($path, $result['path']);
	}
	/**
     * @depends testCopy
     */
	public function testDelete($paths)
	{
		$adapter = $this->adapter;
		$this->assertNull($adapter->delete($paths[0]));
		$this->assertNull($adapter->delete($paths[1]));
	}
}
