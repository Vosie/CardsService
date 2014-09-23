<?php

require_once '../config.php';
require_once 'db.php';

function ensureTable($conn, $language) {
  $stmt = $conn->prepare("SHOW TABLES LIKE '" .
                         $conn->real_escape_string($language) . "'");
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows == 0) {
    createPronunciationTable($conn, $language);
  }
}

function createPronunciationTable($conn, $language) {
  $lang = $conn->real_escape_string($language);
  $sql = "CREATE TABLE IF NOT EXISTS `Pronunciation_" . $lang . "` (
            `category` varchar(40) NOT NULL,
            `topic` varchar(190) NOT NULL,
            `engine` varchar(20) NOT NULL,
            `status` tinyint(4) NOT NULL DEFAULT '0',
            `content` mediumblob NOT NULL,
            PRIMARY KEY (`category`,`topic`,`engine`),
            UNIQUE KEY `query-type-1` (`category`,`topic`,`engine`,`status`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  $stmt = $conn->prepare($sql);
  $result = $stmt->execute();
  $stmt->close();
  return $result;
}

function listEmptyJSONFiles($name, $language, $size) {
  $conn = openConnection();
  ensureTable($conn, $language);
  $lang = $conn->real_escape_string($language);
  $sql = "SELECT js.category, js.topic, js.content, p.status FROM
            JSONFileCache js LEFT JOIN Pronunciation_" . $lang . " p
            ON p.category = js.category AND
               p.topic = js.topic
            WHERE js.language = ?  AND
                  (p.engine IS NULL OR
                   p.engine = ?) AND
                  status IS NULL
            LIMIT 0, " . $size . ";";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $language, $name);
  $stmt->execute();
  $stmt->bind_result($category, $topic, $content, $status);

  $ret = array();
  $idx = 0;
  while($stmt->fetch()) {
    array_push($ret, array(
      "category" => $category,
      "language" => $language,
      "topic" => $topic,
      "content" => $content
    ));
  }
  $stmt->close();
  $conn->close();
  return $ret;
}

function getPronunciation($conn, $name, $category, $language, $topic) {
  $lang = $conn->real_escape_string($language);

  $sql = "SELECT content FROM Pronunciation_" . $lang . " WHERE
            category = ? AND topic = ? AND engine = ? AND status = 0;";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sss", $category, $topic, $name);
  $stmt->execute();
  $stmt->bind_result($content);
  $stmt->fetch();
  $stmt->close();
  return $content;
}

function markPronunciationMissing($conn, $name, $category, $language, $topic) {
  $lang = $conn->real_escape_string($language);
  $sql = "INSERT INTO Pronunciation_" . $lang . "(
            category, topic, engine, status) VALUES(?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    echo "wrong stmt? => " . $conn->error;
    return NULL;
  }
  // we need to declare this variable for passing by reference.
  $missing = 1;
  $stmt->bind_param("sssi", $category, $topic, $name, $missing);
  $result = $stmt->execute();
  $stmt->close();
  return $result;
}

function savePronunciationFile($conn, $name, $category, $language, $topic,
                               $file) {
  $lang = $conn->real_escape_string($language);
  $sql = "INSERT INTO Pronunciation_" . $lang . "(
            category, topic, engine, status, content) VALUES(?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    echo "wrong stmt? => " . $conn->error;
    return NULL;
  }
  // we need to declare this variable for passing by reference.
  $null = NULL;
  $missing = 0;
  $stmt->bind_param("sssib", $category, $topic, $name, $missing, $null);
  // load file to db
  $fp = fopen($file["tmp_name"], "r");
  if (!$fp) {
    echo "unable to open file";
    $stmt->close();
    return NULL;
  }
  while (!feof($fp)) {
      $stmt->send_long_data(4, fread($fp, 8192));
  }
  fclose($fp);
  $result = $stmt->execute();
  $stmt->close();
  return $result;
}

function savePronunciation($name, $category, $language, $topic, $file) {
  $conn = openConnection();

  if ($file == NULL) {
    $result = markPronunciationMissing($conn, $name, $category, $language,
                                       $topic);
  } else {
    $result = savePronunciationFile($conn, $name, $category, $language, $topic,
                                    $file);
  }

  $conn->close();
  return $result;
}
