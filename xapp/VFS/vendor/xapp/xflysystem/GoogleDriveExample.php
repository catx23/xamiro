<?php

@include __DIR__.'/vendor/autoload.php';

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\GoogleDrive as Adapter;
use League\Flysystem\Adapter\Local;

$client_id = '914720938366-1b9t1n0d87g7r429j37kh29474n301la.apps.googleusercontent.com';
$client_secret = '-FqmFBTynCy6VBIYwDLeIvPm';

$auth_token = '{"access_token":"ya29.IQD9DFJHv7U1kBgAAAAJSdBBwvJq8lmEj8f9RBsXbK5uX82vXlvlQAbn_pL2Rg","token_type":"Bearer","expires_in":3600,"refresh_token":"1\/_RAFGmxs0bQBSCCI3hJYnntuGiyXq28UGCdsW8E1cb4","created":1401030654}';
// This URL should be the landing page after authorising the site access to your Google Drive.
// It should store your $auth_token or display it for manual entry.
$redirect_url = 'http://localhost/flysystem/GoogleDriveSetup.php';

$client = new Google_Client();
$client->setAccessToken($auth_token);
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_url);

$token = $client->getAccessToken();
		
if (isset($auth_token)) {
	$refreshToken = json_decode($token);
	$refreshToken = $refreshToken->refresh_token;
	if($client->getAuth()->isAccessTokenExpired()) {
		$client->getAuth()->refreshToken($refreshToken);
	}
} else {
	if (isset($_GET['code'])) {
		$client->authenticate($_GET["code"]);
		echo "Your access token for Google Drive is:<br /><br />\n\n";
		echo $client->getAccessToken();
		echo "\n\n<br /><br />This is your \$auth_token value. Set it in the configuration file.";
		exit();
	} else {
		$authUrl = $client->createAuthUrl();
		die("You must first authorise the plugin. Make sure your client ID and secret are set then <a href='{$authUrl}'>click here</a> to do so.");
	}
}

$filesystem = new Filesystem(new Adapter($client));

echo nl2br(print_r($filesystem->listContents("/"),true));

?>