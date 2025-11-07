<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

$course_id = $_GET['course_id'] ?? null;

if (!$course_id) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de mención requerido'
    ]);
    exit;
}

try {
    // Get teachers assigned to this course
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.id, u.name
        FROM users u
        JOIN teacher_courses tc ON u.id = tc.teacher_id
        WHERE tc.course_id = ? AND tc.status = 'active' AND u.role = 'teacher'
        ORDER BY u.name
    ");
    $stmt->execute([$course_id]);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'teachers' => $teachers
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener docentes: ' . $e->getMessage()
    ]);
}
?>