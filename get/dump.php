<?php

if (!isset($_GET["c"]) || !isset($_GET["l"]) || !isset($_GET["t"])) {
  header("HTTP/1.1 404 Not Found");
  exit();
}

require_once '../lib/db.php';

$content = getCache($_GET["c"], $_GET["l"], $_GET["t"]);

if (!$content) {
  header("HTTP/1.1 404 Not Found");
}

if (count(ob_list_handlers()) > 0) {
  ob_clean();
}

header('Cache-Control: max-age=864000');
header('Content-Type: application/json');
header('Content-Length: ' . strlen($content));
echo $content;
flush();
