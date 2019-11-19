<?php
include_once 'utils.php';

//$data = json_decode("{\"\$schema\":\"http://json-schema.org/draft-04/schema#\",\"type\":\"object\",\"properties\":{\"file_id\":{\"type\":\"string\",\"description\":\"文件id\"},\"file_name\":{\"type\":\"string\",\"description\":\"文件名\"},\"refer_design_code\":{\"type\":\"string\",\"description\":\"参考版型号\"},\"refer_design_code_flag\":{\"type\":\"string\",\"description\":\"是否参考版型：1-是，2-否\"},\"from_act\":{\"type\":\"string\",\"description\":\"来源操作页面：paper-纸样进行中，paper-lib纸样库，paper-confirm纸样修改，size-confirm尺寸推码维护\"},\"design_code\":{\"type\":\"string\",\"description\":\"设计款号\"},\"version_code\":{\"type\":\"string\",\"description\":\"样衣版本号，归档列表时需要传\"}},\"required\":[\"file_id\",\"from_act\",\"refer_design_code_flag\",\"file_name\"]}");
//$result = parseSchema($data);
//header("Content-Type: application/json");
//exit(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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