<?php

# Copyright 2019 Mobimentum Srl
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

// *** FUNCTIONS ***

function missingParam($type, $name) {
    print "Missing required $type: $name\n";

    return FALSE;
}

/** Perform an HTTP GET/POST request with a JSON payload. */
function getJson($url, $postdata=NULL, $contentType='application/json; charset=utf-8') {
	global $config;

	$headers = [ "Content-Type: $contentType", "Private-Token: ".$config['token']];
	list($headers, $body, $code) = doRequest($url, $postdata, $headers);

	return json_decode($body);
}

/** Perform an HTTP GET/POST request with a generic payload. */
function doRequest($url, $postdata=NULL, $headers=[]) {
	$ch = curl_init(); 

	// Headers
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	// Url        
	curl_setopt($ch, CURLOPT_URL, $url);

	// Post data
	if (!empty($postdata)) {
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	}

	// Misc options
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	//curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, TRUE);

	// Do request!
	$response = curl_exec($ch); 

	// Parse response
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$headers = substr($response, 0, $header_size);
	$code = explode(" ", $headers)[1];
	$body = substr($response, $header_size);

	curl_close($ch);

	return array($headers, $body, $code);
}

?>