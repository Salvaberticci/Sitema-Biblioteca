<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$classroom_id = $data['classroom_id'] ?? 'all';
$day = $data['day'] ?? 'all';
$start_time = $data['start_time'] ?? '08:00';
$end_time = $data['end_time'] ?? '18:00';

try {
    // Build query based on filters
    $query = "
        SELECT
            c.name as classroom_name,
            s.day_of_week,
            s.start_time,
            s.end_time,
            co.name as course_name,
            u.name as teacher_name,
            CASE WHEN s.id IS NOT NULL THEN 0 ELSE 1 END as available
        FROM classrooms c
    ";

    $params = [];

    if ($classroom_id !== 'all') {
        $query .= " AND c.id = ?";
        $params[] = $classroom_id;
    }

    // Add time slots for the selected range
    $time_slots = generateTimeSlots($start_time, $end_time);

    $availability = [];

    foreach ($time_slots as $slot) {
        $slot_start = $slot['start'];
        $slot_end = $slot['end'];

        // Check if this time slot is available
        $check_query = "
            SELECT s.*, c.name as classroom_name, co.name as course_name, u.name as teacher_name
            FROM schedules s
            JOIN classrooms c ON s.classroom_id = c.id
            JOIN courses co ON s.course_id = co.id
            JOIN users u ON s.teacher_id = u.id
            WHERE s.status = 'active'
            AND (
                (s.start_time <= ? AND s.end_time > ?) OR
                (s.start_time < ? AND s.end_time >= ?) OR
                (s.start_time >= ? AND s.end_time <= ?)
            )
        ";

        $check_params = [$slot_start, $slot_start, $slot_end, $slot_end, $slot_start, $slot_end];

        if ($classroom_id !== 'all') {
            $check_query .= " AND s.classroom_id = ?";
            $check_params[] = $classroom_id;
        }

        if ($day !== 'all') {
            $check_query .= " AND s.day_of_week = ?";
            $check_params[] = $day;
        }

        $stmt = $pdo->prepare($check_query);
        $stmt->execute($check_params);
        $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get classroom info
        $classroom_query = "SELECT name FROM classrooms WHERE id = ?";
        $stmt = $pdo->prepare($classroom_query);
        $stmt->execute([$classroom_id !== 'all' ? $classroom_id : 1]); // Default to first classroom if all
        $classroom_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($classroom_id === 'all') {
            // Check all classrooms
            $all_classrooms_query = "SELECT id, name FROM classrooms";
            $stmt = $pdo->query($all_classrooms_query);
            $all_classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($all_classrooms as $classroom) {
                $classroom_check_params = [$slot_start, $slot_start, $slot_end, $slot_end, $slot_start, $slot_end, $classroom['id']];
                if ($day !== 'all') {
                    $classroom_check_params[] = $day;
                }

                $stmt = $pdo->prepare($check_query);
                $stmt->execute($classroom_check_params);
                $classroom_conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $availability[] = [
                    'classroom_name' => $classroom['name'],
                    'day_of_week' => $day !== 'all' ? $day : 'monday', // Default day if all
                    'start_time' => $slot_start,
                    'end_time' => $slot_end,
                    'available' => empty($classroom_conflicts),
                    'course_name' => !empty($classroom_conflicts) ? $classroom_conflicts[0]['course_name'] : null,
                    'teacher_name' => !empty($classroom_conflicts) ? $classroom_conflicts[0]['teacher_name'] : null
                ];
            }
        } else {
            $availability[] = [
                'classroom_name' => $classroom_info['name'],
                'day_of_week' => $day !== 'all' ? $day : 'monday', // Default day if all
                'start_time' => $slot_start,
                'end_time' => $slot_end,
                'available' => empty($conflicts),
                'course_name' => !empty($conflicts) ? $conflicts[0]['course_name'] : null,
                'teacher_name' => !empty($conflicts) ? $conflicts[0]['teacher_name'] : null
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'classroom_name' => $classroom_id !== 'all' ? $classroom_info['name'] : 'Todas las aulas',
        'availability' => $availability
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener disponibilidad: ' . $e->getMessage()
    ]);
}

function generateTimeSlots($start_time, $end_time) {
    $slots = [];
    $current = strtotime($start_time);
    $end = strtotime($end_time);

    while ($current < $end) {
        $slot_start = date('H:i', $current);
        $current = strtotime('+1 hour', $current);
        $slot_end = date('H:i', $current);

        if (strtotime($slot_end) <= $end) {
            $slots[] = [
                'start' => $slot_start,
                'end' => $slot_end
            ];
        }
    }

    return $slots;
}
?>