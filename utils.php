<?php
include_once 'Faker.php';
/**
 * 创建集合
 * @param string $collection_name
 * @return array
 */
function genCollection($collection_name = '')
{
    if (empty($collection_name)) {
        $collection_name = 'collection_' . date('YmdHis');
    }
    return [
        'info' => [
            'name' => $collection_name,
            'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
        ],
        'item' => []
    ];
}

/**
 * 创建文件夹
 * @param string $folder_name
 * @param string $description
 * @return array
 */
function genFolder($folder_name = '', $description = '')
{
    if (empty($folder_name)) {
        $folder_name = 'folder_' . date('YmdHis');
    }
    return [
        'name' => $folder_name,
        'item' => []
    ];
}

/**
 * @param array $folder_list
 * @param string $collection_name
 * @return stdClass
 * @throws \Throwable
 * @example
 *  {
 * "query_path": {
 * "path": "/yapi/demo",
 * "params": []
 * },
 * "edit_uid": 0,
 * "status": "undone",
 * "type": "static",
 * "req_body_is_json_schema": true,
 * "res_body_is_json_schema": true,
 * "api_opened": false,
 * "index": 0,
 * "tag": [],
 * "_id": 1,
 * "method": "GET",
 * "catid": 1,
 * "title": "demo",
 * "path": "/yapi/demo",
 * "project_id": 1,
 * "req_params": [],
 * "res_body_type": "json",
 * "uid": 1,
 * "add_time": 1570771045,
 * "up_time": 1570867050,
 * "req_query": [
 * {
 * "required": "1",
 * "_id": "5da1876a417ace8e67495269",
 * "name": "design_code",
 * "desc": "设计款号"
 * }
 * ],
 * "req_headers": [],
 * "req_body_form": [],
 * "__v": 0,
 * "markdown": "",
 * "desc": "",
 * "res_body": "{\"$schema\":\"http://json-schema.org/draft-04/schema#\",\"type\":\"object\",\"properties\":{\"code\":{\"type\":\"string\",\"mock\":{\"mock\":\"0\"}},\"msg\":{\"type\":\"string\"},\"error\":{\"type\":\"array\",\"items\":{\"type\":\"string\"}},\"info\":{\"type\":\"object\",\"properties\":{\"material_list\":{\"type\":\"array\",\"items\":{\"type\":\"object\",\"properties\":{\"id\":{\"type\":\"string\"},\"bom_id\":{\"type\":\"string\"},\"material_sku\":{\"type\":\"string\"},\"process_remark\":{\"type\":\"string\"},\"single_amount\":{\"type\":\"string\"},\"width\":{\"type\":\"string\"},\"secondary_process_name\":{\"type\":\"array\",\"items\":{\"type\":\"string\"}},\"color_name\":{\"type\":\"string\"},\"unit_name\":{\"type\":\"string\"},\"supplier_name\":{\"type\":\"string\"},\"material_title\":{\"type\":\"string\"},\"material_color_name\":{\"type\":\"string\"},\"material_pic_list\":{\"type\":\"array\",\"items\":{\"type\":\"object\",\"properties\":{\"height\":{\"type\":\"number\"},\"path\":{\"type\":\"string\"},\"width\":{\"type\":\"number\"},\"id\":{\"type\":\"number\"}},\"required\":[\"height\",\"path\",\"width\",\"id\"]}},\"material_items_name\":{\"type\":\"string\"},\"use_area_name\":{\"type\":\"string\"}},\"required\":[\"id\",\"bom_id\",\"material_sku\",\"process_remark\",\"single_amount\",\"width\",\"secondary_process_name\",\"color_name\",\"unit_name\",\"supplier_name\",\"material_title\",\"material_color_name\",\"material_pic_list\",\"material_items_name\",\"use_area_name\"]}}},\"required\":[]}}}"
 * }
 */
function parseCollection(array $folder_list, $collection_name = ''): \stdClass
{
    $collection = genCollection($collection_name);
    $collection['item'] = [];
    foreach ($folder_list as $folder) {
        $request_list = $folder['list'] ?? [];
        $new_folder = genFolder($folder['name'] ?? '', $folder['desc'] ?? '');
        $new_folder['item'] = [];
        foreach ($request_list as $request) {
            $new_folder['item'][] = [
                'name' => $request['title'] ?? 'api_' . date('YmdHis') . rand(1000, 9999),
                'request' => parseRequest($request),
                'response' => []
            ];
        }
        $collection['item'][] = $new_folder;
    }
    return (object)$collection;
}

function parseRequest(array $request): \stdClass
{
    $req = [];
    $req['method'] = $request['method'] ?? '';
    $url_info = parse_url($request['path'] ?? '');
    $req['url'] = [
        'raw' => $request['path'] ?? '',
        'protocol' => $url_info['scheme'] ?? '',
        'host' => explode(',', $url_info['host'] ?? '{{host}}'),
        'path' => [$url_info['path'] ?? ''],
        'query' => parseQuery($request['req_query'] ?? []),
    ];
    $req['header'] = parseHeaders($request['req_headers'] ?? []);
    switch ($req['method']) {
        case 'GET':
            break;
        default:
            $body_type = $request['req_body_type'] ?? '';
            switch ($body_type) {
                case 'form':
                    $req['body'] = [
                        'mode' => 'formdata',
                        'formdata' => parseForm($request['req_body_form'] ?? []),
                    ];
                    break;
                case 'json':
                    if (empty($request['req_body_other'])) {
                        continue;
                    }
                    $req_body_other = json_decode($request['req_body_other']);
                    $req['body'] = [
                        'mode' => 'raw',
                        'raw' => json_encode(parseSchema($req_body_other), JSON_UNESCAPED_UNICODE)
                    ];
                    break;
                default:
                    throw new \Exception('Unsupported req_body_type: ' . $request['req_body_type']);
            }

    }
    $req['description'] = $request['desc'] ?? '';
    return (object)$req;
}

function parseSchema(\stdClass $body): \stdClass
{
    return (new Faker())->generate($body);
}

function parseQuery(array $query_list): array
{
    $result = [];
    foreach ($query_list as $query) {
        if (!empty(array_diff(['name', 'desc'], array_keys($query)))) {
            throw new \Exception('Unsupported req_query: ' . json_encode($query_list, JSON_UNESCAPED_UNICODE));
        }
        $result[] = [
            'key' => $query['name'],
            'value' => $query['example'] ?? $query['desc'],
            'description' => $query['desc']
        ];
    }
    return $result;
}

function parseForm(array $form_list): array
{
    $result = [];
    foreach ($form_list as $form) {
        if (!empty(array_diff(['name', 'type'], array_keys($form)))) {
            throw new \Exception('Unsupported req_body_form: ' . json_encode($form_list, JSON_UNESCAPED_UNICODE));
        }
        $result[] = [
            'key' => $form['name'],
            'value' => $form['desc'] ?? '',
            'type' => $form['type'] ?? 'text',
            'description' => $form['desc'] ?? '',
        ];
    }
    return $result;
}

function parseHeaders(array $headers): array
{
    $result = [];
    foreach ($headers as $header) {
        if (!empty(array_diff(['name', 'value'], array_keys($header)))) {
            throw new \Exception('Unsupported req_headers: ' . json_encode($headers, JSON_UNESCAPED_UNICODE));
        }
        $result[] = [
            'key' => $header['name'],
            'value' => $header['value'],
            'type' => 'text',
            'description' => $header['desc'] ?? '',
        ];
    }
    return $result;
}


