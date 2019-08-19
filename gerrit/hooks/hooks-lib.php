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

/** Get project ID from project name */
function getProjectId($projectName) {
	global $config;

	$projectId = 0;
        $json = getJson($config['url'] . "/api/v4/projects?simple=true&search={$projectName}");
        foreach ($json as $project) {
                if ($project->name == $projectName) { $projectId = $project->id; break; }
        } 

	return $projectId;
}

/** Get branch name from ref ID */
function getBranchName($changeUrl, $patchsetId=0) {
	$refId = intval(@end(explode('/', $changeUrl)));
	$refIdBase = intval(substr($refId, -2)); // FIXME

	return "review/$refIdBase/$refId" . ($patchsetId ? "/$patchsetId" : "");
}

/** Delete all remote branches of a change */
function deleteGitlabBranches($projectId, $changeUrl) {
	// Get branch name
	$branchName = getBranchName($changeUrl);

	// Delete all patchsets
	// XXX: better option could be listing branches with "/projects/:id/repository/branches"
	$i = 1;
	do {
		$result = deleteGitlabBranch($projectId, $branchName, $i);
		print "DELETE of branch $branchName ($i): $result\n";
		$i++;
	}
	while ($result == "OK");

	// Delete "meta" branch
	sleep(2); // FIXME: give time to replication plugin to sync it or it'll create the branch again
	$result = deleteGitlabBranch($projectId, $branchName, "meta");
	print "DELETE of branch $branchName (meta): $result\n";
}

/** Delete a branch on mirroring remote */
function deleteGitlabBranch($projectId, $branchName, $patchsetId) {
	global $config;

	$url = $config['url'] . "/api/v4/projects/{$projectId}/repository/branches/" . urlencode($branchName . '/' . $patchsetId);
	$json = getJson($url, NULL, NULL, 'DELETE');

	return $json ? $json->message : "OK"; 
}

/** Perform an HTTP GET/POST request with a JSON payload. */
function getJson($url, $postdata=NULL, $contentType=NULL, $method=NULL) {
	global $config;

	$headers = [ "Content-Type: $contentType", "Private-Token: ".$config['token']];
	if (!$contentType) $contentType = 'application/json; charset=utf-8';
	list($headers, $body, $code) = doRequest($url, $postdata, $headers, $method);

	return json_decode($body);
}

/** Perform an HTTP request with a generic payload. */
function doRequest($url, $postdata=NULL, $headers=[], $method=NULL) {
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
	
	if ($method) {
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); 
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
