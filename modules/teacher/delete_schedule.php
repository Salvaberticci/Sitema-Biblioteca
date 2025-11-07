<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

// Get POST data
$schedule_id = $_POST['schedule_id'] ?? null;

if (!$schedule_id) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de horario no proporcionado'
    ]);
    exit;
}

try {
    // Verify the schedule belongs to the current teacher
    $teacher_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT s.id FROM schedules s
        WHERE s.id = ? AND s.teacher_id = ?
    ");
    $stmt->execute([$schedule_id, $teacher_id]);

    if ($stmt->rowCount() == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Horario no encontrado o no tienes permisos para eliminarlo'
        ]);
        exit;
    }

    // Check if there are active enrollments for this schedule's course
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM enrollments e
        JOIN schedules s ON e.course_id = s.course_id
        WHERE s.id = ? AND e.status = 'enrolled'
    ");
    $stmt->execute([$schedule_id]);

    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No se puede eliminar el horario porque hay estudiantes matriculados en esta mención'
        ]);
        exit;
    }

    // Check if there are activities for this schedule's course
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM activities a
        JOIN schedules s ON a.course_id = s.course_id
        WHERE s.id = ?
    ");
    $stmt->execute([$schedule_id]);

    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No se puede eliminar el horario porque hay actividades asignadas para esta mención'
        ]);
        exit;
    }

    // Delete the schedule
    $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ? AND teacher_id = ?");
    $result = $stmt->execute([$schedule_id, $teacher_id]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Horario eliminado exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar el horario'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>