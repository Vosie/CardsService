<?php

require_once '../config.php';
require_once '../lib/db.php';

global $wikicards_folder;

$wikicards = opendir($wikicards_folder);

if (!$wikicards) {
  echo 'open directory error...';
  exit();
}

$conn = openConnection();
while (false !== ($file = readdir($wikicards))) {
  if ($file != '.' && $file != '..' && is_dir($wikicards_folder . $file)) {
    processLanguageFolder($conn, $file, $wikicards_folder . $file . '/');
  }
}
$conn->close();
closedir($wikicards);

function processLanguageFolder($conn, $lang, $folder) {
  echo 'import ' . $lang . '<br>';
  $langFolder = opendir($folder);
  while (false !== ($file = readdir($langFolder))) {
    if ($file != '.' && $file != '..' && is_dir($folder . $file)) {
      processCategoryFolder($conn, $lang, $file, $folder . $file . '/');
    }
  }
  closedir($langFolder);
}

function processCategoryFolder($conn, $lang, $category, $folder) {
  echo 'import ' . $lang . ', ' . $category . '<br>';
  $langFolder = opendir($folder);
  while (false !== ($file = readdir($langFolder))) {
    if ($file != '.' && $file != '..' && endsWith($file, '.json')) {
      $topic = substr($file, 0, strlen($file) - 5);
      processJSONFile($conn, $lang, $category, $topic, $folder . $file);
    }
  }
  closedir($langFolder);
}

function processJSONFile($conn, $lang, $category, $topic, $file) {
  $content = file_get_contents($file);
  echo 'import ' . $lang . ', ' . $category . ', ' . $topic . ', ' . $file .':';
  $result = createCache($conn, $category, $lang, $topic, $content);
  if (!$result) {
    die("mysql error: " . $conn->error);
  }
  echo $result . '<br>';
  unlink($file);
}

function endsWith($haystack, $needle) {
  return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}
