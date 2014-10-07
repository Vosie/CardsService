<?php

require_once '../config.php';

function openConnection() {
  global $database_server, $database_username, $database_passwd, $database_name;

  $conn = new mysqli($database_server, $database_username, $database_passwd,
                     $database_name);

  $conn->query('SET names=utf8');
  $conn->query('SET character_set_client=utf8');
  $conn->query('SET character_set_connection=utf8');
  $conn->query('SET character_set_results=utf8');
  $conn->query('SET collation_connection=utf8_general_ci');
  $conn->set_charset('utf8');

  return $conn;
}

function getCache($category, $language, $topic) {
  $conn = openConnection();
  $stmt = $conn->prepare("SELECT content FROM JSONFileCache
                            WHERE category = ? AND language = ? AND topic = ?");
  $stmt->bind_param("sss", $category, $language, $topic);
  $stmt->execute();
  $stmt->bind_result($content);
  $stmt->fetch();
  $stmt->close();
  $conn->close();
  return $content;
}

function createCache($conn, $category, $language, $topic, $content) {
  $stmt = $conn->prepare("INSERT INTO JSONFileCache(
                                          category,
                                          language,
                                          topic,
                                          content)
                                   VALUES (?, ?, ?, ?)");

  $stmt->bind_param("ssss", $category, $language, $topic, $content);
  $result = $stmt->execute();
  $stmt->close();
  return $result;
}

function deleteCache($conn, $category, $language, $topic) {
  $stmt = $conn->prepare("DELETE FROM JSONFileCache
                            WHERE category = ? AND language = ? AND topic = ?");
  $stmt->bind_param("sss", $category, $language, $topic);
  $result = $stmt->execute();
  $stmt->close();
  return $result;
}
