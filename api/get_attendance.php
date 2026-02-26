<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$course_id = isset($_GET['course_id']) ? (int) $_GET['course_id'] : null;
$date = isset($_GET['date']) ? $_GET['date'] : null;

if (!$course_id || !$date) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros']);
    exit();
}

try {
    // Fetch students and their attendance for a specific day
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.username, COALESCE(a.status, 'not_marked') as status
        FROM users u
        LEFT JOIN attendance a ON u.id = a.student_id AND a.course_id = ? AND a.date = ?
        WHERE u.id IN (SELECT DISTINCT student_id FROM enrollments WHERE course_id = ? AND status = 'enrolled')
        AND u.role = 'student'
        ORDER BY u.name
    ");
    $stmt->execute([$course_id, $date, $course_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['students' => $students, 'date' => $date, 'formatted_date' => date('d/m/Y', strtotime($date))]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
