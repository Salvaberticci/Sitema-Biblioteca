<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$classroom_id = $data['classroom_id'] ?? 'all';
$day = $data['day'] ?? 'all';
$start_time = $data['start_time'] ?? '';
$end_time = $data['end_time'] ?? '';

try {
    $availability = [];

    // Get classrooms to check
    if ($classroom_id === 'all') {
        $classrooms_query = "SELECT id, name FROM classrooms ORDER BY name";
        $stmt = $pdo->query($classrooms_query);
        $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $classrooms_query = "SELECT id, name FROM classrooms WHERE id = ?";
        $stmt = $pdo->prepare($classrooms_query);
        $stmt->execute([$classroom_id]);
        $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get days to check
    $days_to_check = [];
    if ($day === 'all') {
        $days_to_check = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    } else {
        $days_to_check = [$day];
    }

    foreach ($classrooms as $classroom) {
        foreach ($days_to_check as $check_day) {
            // Check for conflicts
            $conflict_query = "
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

            $params = [$classroom['id'], $check_day, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time];

            $stmt = $pdo->prepare($conflict_query);
            $stmt->execute($params);
            $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($conflicts)) {
                // Available
                $availability[] = [
                    'classroom_name' => $classroom['name'],
                    'day_of_week' => $check_day,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'available' => true,
                    'course_name' => null,
                    'teacher_name' => null
                ];
            } else {
                // Occupied - show first conflict
                $conflict = $conflicts[0];
                $availability[] = [
                    'classroom_name' => $classroom['name'],
                    'day_of_week' => $check_day,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'available' => false,
                    'course_name' => $conflict['course_name'],
                    'teacher_name' => $conflict['teacher_name']
                ];
            }
        }
    }

    // Get classroom name if specific classroom was requested
    $classroom_name = null;
    if ($classroom_id !== 'all') {
        $stmt = $pdo->prepare("SELECT name FROM classrooms WHERE id = ?");
        $stmt->execute([$classroom_id]);
        $classroom_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $classroom_name = $classroom_data ? $classroom_data['name'] : null;
    }

    echo json_encode([
        'success' => true,
        'classroom_name' => $classroom_name,
        'availability' => $availability
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener disponibilidad: ' . $e->getMessage()
    ]);
}
?>