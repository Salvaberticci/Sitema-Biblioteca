<?php
require_once '../../includes/config.php';

// Get resource ID from URL parameter
$resource_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$resource_id) {
    http_response_code(400);
    die('Invalid resource ID');
}

// Fetch resource from database
$stmt = $pdo->prepare("SELECT title, file_path FROM library_resources WHERE id = ?");
$stmt->execute([$resource_id]);
$resource = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resource) {
    error_log("Resource not found in database for ID: $resource_id");
    http_response_code(404);
    die('Resource not found');
}

if (!$resource['file_path']) {
    error_log("File path missing for resource ID: $resource_id");
    http_response_code(404);
    die('File path missing');
}

// Construct full file path
$full_path = __DIR__ . '/../../' . $resource['file_path'];

error_log("Download attempt - Resource ID: $resource_id, File path: " . $resource['file_path'] . ", Full path: $full_path");

// Check if file exists
if (!file_exists($full_path)) {
    error_log("File does not exist: $full_path");
    http_response_code(404);
    die('File not found on server');
}

// Check if file is readable
if (!is_readable($full_path)) {
    error_log("File not readable: $full_path");
    http_response_code(403);
    die('File not accessible');
}

// Get file info
$file_size = filesize($full_path);
$file_name = basename($resource['file_path']);

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . $file_size);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Clear output buffer
if (ob_get_level()) {
    ob_clean();
}

// Read and output file
readfile($full_path);
exit;
?>