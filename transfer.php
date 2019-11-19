<?php
include_once 'utils.php';

$ext = 'json';
$uploadFile = $_FILES['uploadFile'] ?? [];
if (strpos($uploadFile['name'], $ext) != (strlen($uploadFile['name']) - 4)) {
    exit('Unsupported file extension: ' . $uploadFile['name'] . ', index: ' . strpos($uploadFile['name'], $ext));
}
if (empty($uploadFile)) {
    exit('uploadFile cannot be empty');
}
$data = file_get_contents($uploadFile['tmp_name']);
header('Content-Type: application/json');
$collection_name = str_replace('.' . $ext, '', $uploadFile['name']);
exit(json_encode(parseCollection(json_decode($data, true), $collection_name), JSON_UNESCAPED_UNICODE));