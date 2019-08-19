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

define('IS_GERRIT', isset($_SERVER['GERRIT_SITE']));

// Setup logging
if (IS_GERRIT) {
	fclose(STDOUT);
	$STDOUT = fopen(realpath(dirname(__FILE__) . '/../logs/hooks_log'), 'a');
}

// Read configuration
global $config;
$config = parse_ini_file(realpath(dirname(__FILE__) . '/../etc/gitlab.config'), TRUE)['gitlab'];
$gerritConfig = parse_ini_file(realpath(dirname(__FILE__) . '/../etc/gerrit.config'), TRUE);

// Parse command-line options
$opts = [];
for ($i = 1; $i < count($argv)-1; $i+=2) {
	$opts[preg_replace('/^\-\-/', '', $argv[$i])] = $argv[$i+1];
}

?>
