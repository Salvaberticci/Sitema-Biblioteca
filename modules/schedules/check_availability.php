<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$classroom_id = $data['classroom_id'];
$teacher_id = $data['teacher_id'] ?? null;
$day = $data['day'];
$start_time = $data['start_time'];
$end_time = $data['end_time'];
$exclude_schedule_id = $data['exclude_schedule_id'] ?? null;

try {
    $conflicts = [];

    // Check classroom conflicts
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
    $classroom_conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($classroom_conflicts)) {
        $conflict = $classroom_conflicts[0];
        $conflicts[] = "Aula ocupada por '{$conflict['course_name']}' con {$conflict['teacher_name']} ({$conflict['start_time']}-{$conflict['end_time']})";
    }

    // Check teacher conflicts if teacher_id is provided
    if ($teacher_id) {
        $query = "
            SELECT s.*, c.name as classroom_name, co.name as course_name, u.name as teacher_name
            FROM schedules s
            JOIN classrooms c ON s.classroom_id = c.id
            JOIN courses co ON s.course_id = co.id
            JOIN users u ON s.teacher_id = u.id
            WHERE s.teacher_id = ?
            AND s.day_of_week = ?
            AND s.status = 'active'
            AND (
                (s.start_time <= ? AND s.end_time > ?) OR
                (s.start_time < ? AND s.end_time >= ?) OR
                (s.start_time >= ? AND s.end_time <= ?)
            )
        ";

        $params = [$teacher_id, $day, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time];

        if ($exclude_schedule_id) {
            $query .= " AND s.id != ?";
            $params[] = $exclude_schedule_id;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $teacher_conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($teacher_conflicts)) {
            $conflict = $teacher_conflicts[0];
            $conflicts[] = "Docente ocupado con '{$conflict['course_name']}' en {$conflict['classroom_name']} ({$conflict['start_time']}-{$conflict['end_time']})";
        }
    }

    // Get all available classrooms for this time slot
    $available_query = "
        SELECT c.id, c.name, c.capacity
        FROM classrooms c
        WHERE c.id NOT IN (
            SELECT DISTINCT s.classroom_id
            FROM schedules s
            WHERE s.day_of_week = ?
            AND s.status = 'active'
            AND (
                (s.start_time <= ? AND s.end_time > ?) OR
                (s.start_time < ? AND s.end_time >= ?) OR
                (s.start_time >= ? AND s.end_time <= ?)
            )
            " . ($exclude_schedule_id ? "AND s.id != ?" : "") . "
        )
        ORDER BY c.name
    ";

    $available_params = [$day, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time];
    if ($exclude_schedule_id) {
        $available_params[] = $exclude_schedule_id;
    }

    $stmt = $pdo->prepare($available_query);
    $stmt->execute($available_params);
    $available_classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($conflicts)) {
        echo json_encode([
            'available' => true,
            'message' => 'Aula disponible',
            'available_classrooms' => $available_classrooms
        ]);
    } else {
        echo json_encode([
            'available' => false,
            'conflict_info' => implode(", ", $conflicts),
            'message' => 'Conflicto detectado',
            'available_classrooms' => $available_classrooms
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'available' => false,
        'message' => 'Error al verificar disponibilidad: ' . $e->getMessage()
    ]);
}
?>