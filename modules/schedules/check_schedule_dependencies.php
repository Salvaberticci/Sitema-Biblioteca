<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$schedule_id = (int)($_POST['schedule_id'] ?? 0);

if (!$schedule_id) {
    echo json_encode(['error' => 'ID de horario requerido']);
    exit;
}

try {
    // Check for active enrollments in this schedule's course
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as enrollment_count
        FROM enrollments e
        JOIN schedules s ON e.course_id = s.course_id
        WHERE s.id = ? AND e.status = 'enrolled'
    ");
    $stmt->execute([$schedule_id]);
    $enrollment_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $has_enrollments = $enrollment_result['enrollment_count'] > 0;
    $enrollment_count = $enrollment_result['enrollment_count'];

    // Check for activities in this schedule's course
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as activity_count
        FROM activities a
        JOIN schedules s ON a.course_id = s.course_id
        WHERE s.id = ?
    ");
    $stmt->execute([$schedule_id]);
    $activity_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $has_activities = $activity_result['activity_count'] > 0;
    $activity_count = $activity_result['activity_count'];

    echo json_encode([
        'has_enrollments' => $has_enrollments,
        'enrollment_count' => $enrollment_count,
        'has_activities' => $has_activities,
        'activity_count' => $activity_count
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error al verificar dependencias: ' . $e->getMessage()]);
}
?>