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
$collection_data = parseCollection(json_decode($data, true), $collection_name);
$api_key = $_POST['apiKey'] ?? '';
if (!empty($api_key)) {
    $url = 'https://api.getpostman.com/collections';
    $headers = [
        'x-api-key: '.$api_key,
        'Content-Type: application/json'
    ];
    $post_data = [
        'collection' => $collection_data
    ];
    $post_data = json_encode($post_data);
    $result = curlPostFile($url, $headers, $post_data);
    exit(json_encode($result, JSON_UNESCAPED_UNICODE));
} else {
    exit($collection_data);
}