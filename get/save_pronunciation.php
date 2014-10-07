<?php

require_once '../config.php';

global $service_key;

if (!isset($_POST['k']) || $_POST['k'] != $service_key ||
    !isset($_POST['c']) || !isset($_POST['l']) || !isset($_POST['t']) ||
    !isset($_POST['e'])) {
  header('HTTP/1.1 404 Not Found');
  exit();
}

require_once '../lib/pronunciations_db.php';

$file = NULL;
if (isset($_FILES['file']) && UPLOAD_ERR_OK == $_FILES['file']['error'] &&
    $_FILES['file']['size'] > 10) {
  $file = $_FILES['file'];
}

echo savePronunciation($_POST['e'], $_POST['c'], $_POST['l'], $_POST['t'],
                       $file);
exit();
