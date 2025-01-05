<?php
$path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cache';
$minimumPhpVersion = '5.2';
$apiUrl = '{{API_URL}}';
$errors = array();

echo 'php ≥' . $minimumPhpVersion . ' – ';
echo version_compare(phpversion(), $minimumPhpVersion, '>=')
  ? 'OK' . "<br />"
  : 'FAIL' . "<br />"
;

echo 'file access – ';
echo is_writable($path)
  ? 'OK' . "<br />"
  : 'FAIL' . "<br />"
;

echo 'check api – ';
echo isApiAvailable($apiUrl)
  ? 'OK' . "<br />"
  : 'FAIL' . "<br />"
;

function isApiAvailable($apiUrl) {
  $headers = @get_headers($apiUrl);
  if (!isset($headers[0])) return false;
  return in_array($headers[0], ['HTTP/1.1 302 Moved Temporarily', 'HTTP/1.1 200 OK']);
}