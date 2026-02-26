<?php
require_once '../../includes/config.php';
requireRole('admin');

header('Content-Type: application/json');

// Get POST data
$course_id = $_POST['course_id'] ?? null;
$teacher_id = $_POST['teacher_id'] ?? null;
$day = $_POST['day'] ?? null;
$classroom_id = $_POST['classroom_id'] ?? null;
$start_time = $_POST['start_time'] ?? null;
$end_time = $_POST['end_time'] ?? null;
$subject = $_POST['subject'] ?? null;

if (!$course_id || !$teacher_id || !$day || !$classroom_id || !$start_time || !$end_time) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit;
}

try {
    // Check for conflicts (both classroom and teacher)
    $conflicts = [];

    // Check classroom conflicts
    $classroom_conflict_query = "
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

    $stmt = $pdo->prepare($classroom_conflict_query);
    $stmt->execute([$classroom_id, $day, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time]);
    $classroom_conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($classroom_conflicts)) {
        $conflict = $classroom_conflicts[0];
        $conflicts[] = "Aula ocupada por '{$conflict['course_name']}' con {$conflict['teacher_name']} ({$conflict['start_time']}-{$conflict['end_time']})";
    }

    // Check teacher conflicts
    $teacher_conflict_query = "
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

    $stmt = $pdo->prepare($teacher_conflict_query);
    $stmt->execute([$teacher_id, $day, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time]);
    $teacher_conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($teacher_conflicts)) {
        $conflict = $teacher_conflicts[0];
        $conflicts[] = "Docente ocupado con '{$conflict['course_name']}' en {$conflict['classroom_name']} ({$conflict['start_time']}-{$conflict['end_time']})";
    }

    if (!empty($conflicts)) {
        echo json_encode([
            'success' => false,
            'message' => 'Conflicto de horario detectado: ' . implode(", ", $conflicts)
        ]);
        exit;
    }

    // Create the schedule
    $insert_query = "
        INSERT INTO schedules (course_id, subject, teacher_id, classroom_id, day_of_week, start_time, end_time, semester, academic_year, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, '2025-1', '2025', 'active')
    ";

    $stmt = $pdo->prepare($insert_query);
    $result = $stmt->execute([$course_id, $subject, $teacher_id, $classroom_id, $day, $start_time, $end_time]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Horario creado exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear el horario'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>