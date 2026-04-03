<?php
// public/test_db.php

require_once __DIR__ . '/../src/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Простой запрос для проверки
    $stmt = $pdo->query('SELECT 1 AS test');
    $result = $stmt->fetch();

    if ($result['test'] == 1) {
        echo json_encode(['status' => 'success', 'message' => 'Database connection OK']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}