<?php
header('Content-Type: application/json');
echo json_encode([
    'method' => $_SERVER['REQUEST_METHOD'] ?? '',
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? '',
    'post_count' => count($_POST),
    'post_data' => $_POST,
    'input_stream' => file_get_contents('php://input'),
    'input_length' => $_SERVER['CONTENT_LENGTH'] ?? '',
]);
