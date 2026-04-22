<?php
session_start();

define('DATA_DIR', __DIR__ . '/../data/');

if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

function loadData($table) {
    $file = DATA_DIR . $table . '.json';
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
        return [];
    }

    return json_decode(file_get_contents($file), true) ?? [];
}

function saveData($table, $data) {
    $file = DATA_DIR . $table . '.json';
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getNextId($table) {
    $data = loadData($table);
    if (empty($data)) {
        return 1;
    }

    return max(array_column($data, 'id')) + 1;
}

function addRecord($table, $record) {
    $data = loadData($table);
    $record['id'] = getNextId($table);
    $record['created_at'] = date('Y-m-d H:i:s');
    $data[] = $record;
    saveData($table, $data);
    return $record;
}

function findById($table, $id) {
    $data = loadData($table);
    foreach ($data as $row) {
        if ($row['id'] == $id) {
            return $row;
        }
    }
    return null;
}

function updateRecord($table, $id, $updates) {
    $data = loadData($table);

    foreach ($data as &$row) {
        if ($row['id'] == $id) {
            $row = array_merge($row, $updates);
            break;
        }
    }

    saveData($table, $data);
}

function setMessage($type, $text) {
    $_SESSION['msg'] = [
        'type' => $type,
        'text' => $text
    ];
}

function getMessage() {
    if (isset($_SESSION['msg'])) {
        $msg = $_SESSION['msg'];
        unset($_SESSION['msg']);
        return $msg;
    }

    return null;
}
?>
