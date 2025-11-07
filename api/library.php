<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid resource ID']);
    exit;
}

$resourceId = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT lr.*, u.name as uploader_name
        FROM library_resources lr
        LEFT JOIN users u ON lr.uploaded_by = u.id
        WHERE lr.id = ?
    ");
    $stmt->execute([$resourceId]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resource) {
        http_response_code(404);
        echo json_encode(['error' => 'Resource not found']);
        exit;
    }

    echo json_encode($resource);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>