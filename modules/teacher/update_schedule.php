<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

// Get POST data
$schedule_id = $_POST['schedule_id'] ?? null;
$course_id = $_POST['course_id'] ?? null;
$day = $_POST['day'] ?? null;
$classroom_id = $_POST['classroom_id'] ?? null;
$start_time = $_POST['start_time'] ?? null;
$end_time = $_POST['end_time'] ?? null;

if (!$schedule_id || !$course_id || !$day || !$classroom_id || !$start_time || !$end_time) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit;
}

try {
    // Verify the schedule belongs to the current teacher
    $teacher_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT s.id FROM schedules s
        JOIN teacher_courses tc ON s.course_id = tc.course_id
        WHERE s.id = ? AND tc.teacher_id = ? AND tc.status = 'active'
    ");
    $stmt->execute([$schedule_id, $teacher_id]);

    if ($stmt->rowCount() == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No tienes permisos para modificar este horario'
        ]);
        exit;
    }

    // Check for conflicts (excluding current schedule)
    $conflict_query = "
        SELECT s.*, c.name as classroom_name, co.name as course_name, u.name as teacher_name
        FROM schedules s
        JOIN classrooms c ON s.classroom_id = c.id
        JOIN courses co ON s.course_id = co.id
        JOIN users u ON s.teacher_id = u.id
        WHERE s.classroom_id = ?
        AND s.day_of_week = ?
        AND s.status = 'active'
        AND s.id != ?
        AND (
            (s.start_time <= ? AND s.end_time > ?) OR
            (s.start_time < ? AND s.end_time >= ?) OR
            (s.start_time >= ? AND s.end_time <= ?)
        )
    ";

    $stmt = $pdo->prepare($conflict_query);
    $stmt->execute([$classroom_id, $day, $schedule_id, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time]);
    $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($conflicts)) {
        $conflict = $conflicts[0];
        $conflict_info = "Conflicto con {$conflict['course_name']} de {$conflict['teacher_name']} en {$conflict['classroom_name']} ({$conflict['start_time']}-{$conflict['end_time']})";

        echo json_encode([
            'success' => false,
            'message' => 'Conflicto de horario detectado: ' . $conflict_info
        ]);
        exit;
    }

    // Update the schedule
    $update_query = "
        UPDATE schedules
        SET classroom_id = ?, day_of_week = ?, start_time = ?, end_time = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ";

    $stmt = $pdo->prepare($update_query);
    $result = $stmt->execute([$classroom_id, $day, $start_time, $end_time, $schedule_id]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Horario actualizado exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el horario'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>