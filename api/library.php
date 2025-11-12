<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if requesting a specific resource or list of resources
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    // Get specific resource details
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
} elseif (isset($_GET['list'])) {
    // List all resources for chatbot
    try {
        $stmt = $pdo->prepare("
            SELECT lr.id, lr.title, lr.author, lr.type, lr.subject, lr.description,
                   DATE(lr.upload_date) as upload_date, u.name as uploader_name
            FROM library_resources lr
            LEFT JOIN users u ON lr.uploaded_by = u.id
            ORDER BY lr.upload_date DESC
            LIMIT 20
        ");
        $stmt->execute();
        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'resources' => $resources,
            'count' => count($resources)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request. Use ?id=NUMBER for details or ?list for resource list']);
}
?>