<?php

require_once '../config.php';

global $service_key, $engine_name;

if (!isset($_GET['k']) || $_GET['k'] != $service_key ||
    !isset($_GET['l'])) {
  header('HTTP/1.1 404 Not Found');
}

require_once '../lib/pronunciations_db.php';

$defaultSize = 300;
echo json_encode(listEmptyJSONFiles($engine_name, $_GET['l'],
                                    $defaultSize));
exit();
