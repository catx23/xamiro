<?php

if (isset($_POST['url'])) {
    $url = $_POST['url'];
    $mime = pathinfo($url, PATHINFO_EXTENSION);

    if (function_exists('curl_version')) {
    	$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

		$data = curl_exec($handle);

		curl_close($handle);
    } else {
    	$data = file_get_contents($url);
    }

    $imageData = base64_encode($data);
    $formatted = 'data: '.$mime.';base64,'.$imageData;

    echo $formatted;
}



