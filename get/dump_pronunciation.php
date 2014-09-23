<?php

require_once '../config.php';

global $service_key, $engine_name;

if (!isset($_GET['c']) || !isset($_GET['l']) || !isset($_GET['t'])) {
  header('HTTP/1.1 404 Not Found');
  exit();
}

require_once '../lib/db.php';
require_once '../lib/pronunciations_db.php';

$conn = openConnection();
$content = getPronunciation($conn, $engine_name, $_GET['c'], $_GET['l'],
                            $_GET['t']);
$conn->close();
if (!$content || $content == "") {
  header('HTTP/1.1 404 Not Found, no content');
  exit();
}

if (count(ob_list_handlers()) > 0) {
  ob_clean();
}

header('Cache-Control: max-age=86400');
header('Content-Type: audio/mpeg');
header('Content-Length: ' . strlen($content));
header('Access-Control-Allow-Origin: *');
echo $content;
flush();
