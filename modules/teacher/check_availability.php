<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$classroom_id = $data['classroom_id'];
$day = $data['day'];
$start_time = $data['start_time'];
$end_time = $data['end_time'];
$exclude_schedule_id = $data['exclude_schedule_id'] ?? null;

try {
    // Check for conflicts
    $query = "
        SELECT s.*, c.name as classroom_name, co.name as course_name, u.name as teacher_name
        FROM schedules s
        JOIN classrooms c ON s.classroom_id = c.id
        JOIN courses co ON s.course_id = co.id
        JOIN users u ON s.teacher_id = u.id
        WHERE s.classroom_id = ?
        AND s.day_of_week = ?
        AND s.status = 'active'
        AND (
            (s.start_time <= ? AND s.end_time > ?) OR
            (s.start_time < ? AND s.end_time >= ?) OR
            (s.start_time >= ? AND s.end_time <= ?)
        )
    ";

    $params = [$classroom_id, $day, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time];

    if ($exclude_schedule_id) {
        $query .= " AND s.id != ?";
        $params[] = $exclude_schedule_id;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($conflicts)) {
        echo json_encode([
            'available' => true,
            'message' => 'Aula disponible'
        ]);
    } else {
        $conflict = $conflicts[0];
        $conflict_info = "{$conflict['course_name']} con {$conflict['teacher_name']} ({$conflict['start_time']}-{$conflict['end_time']})";

        echo json_encode([
            'available' => false,
            'conflict_info' => $conflict_info,
            'message' => 'Conflicto detectado'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'available' => false,
        'message' => 'Error al verificar disponibilidad: ' . $e->getMessage()
    ]);
}
?>