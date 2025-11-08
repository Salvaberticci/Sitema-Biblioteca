<?php
require_once '../../includes/config.php';
requireRole('admin');

header('Content-Type: application/json');

// Get filter parameters
$selected_course = $_GET['course'] ?? 'all';
$selected_teacher = $_GET['teacher'] ?? 'all';

// Get teachers based on selected course filter
if ($selected_course !== 'all') {
    // Get only teachers assigned to the selected course
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.id, u.name
        FROM users u
        JOIN teacher_courses tc ON u.id = tc.teacher_id
        WHERE tc.course_id = ? AND tc.status = 'active' AND u.role = 'teacher'
        ORDER BY u.name
    ");
    $stmt->execute([$selected_course]);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Get all teachers when no course is selected
    $teachers = $pdo->query("SELECT * FROM users WHERE role = 'teacher' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}

// Get schedules for the selected filters
$query = "
    SELECT s.*, c.name as classroom_name, co.name as course_name, co.code as course_code, u.name as teacher_name
    FROM schedules s
    JOIN classrooms c ON s.classroom_id = c.id
    JOIN courses co ON s.course_id = co.id
    JOIN users u ON s.teacher_id = u.id
    WHERE s.status = 'active'
";

$params = [];

if ($selected_course !== 'all') {
    $query .= " AND s.course_id = ?";
    $params[] = $selected_course;
}

if ($selected_teacher !== 'all') {
    $query .= " AND s.teacher_id = ?";
    $params[] = $selected_teacher;
}

$query .= " ORDER BY s.course_id, FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group schedules by course
$schedules_by_course = [];
foreach ($schedules as $schedule) {
    $schedules_by_course[$schedule['course_id']][] = $schedule;
}

// Get all courses for reference
$courses = $pdo->query("SELECT * FROM courses ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Days mapping
$days = [
    'monday' => 'Lunes',
    'tuesday' => 'Martes',
    'wednesday' => 'Miércoles',
    'thursday' => 'Jueves',
    'friday' => 'Viernes',
    'saturday' => 'Sábado',
    'sunday' => 'Domingo'
];

// Generate HTML content
ob_start();
?>

<?php if (empty($courses)): ?>
    <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
        <div class="text-center py-12">
            <div class="text-6xl text-gray-300 mb-4">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay materias disponibles</h3>
            <p class="text-gray-500">No se encontraron materias en el sistema.</p>
        </div>
    </div>
<?php else: ?>
    <!-- Course schedules content here -->
    <?php if ($selected_course === 'all'): ?>
        <!-- Show all courses -->
        <?php foreach ($courses as $course): ?>
            <?php
            $course_schedules = $schedules_by_course[$course['id']] ?? [];
            if (empty($course_schedules)) continue;
            ?>
            <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up course-schedule" data-course-id="<?php echo $course['id']; ?>">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold flex items-center">
                        <i class="fas fa-graduation-cap mr-3 text-primary"></i>
                        <?php echo htmlspecialchars($course['name']); ?>
                        <span class="ml-2 text-sm text-gray-600">(<?php echo htmlspecialchars($course['code']); ?>)</span>
                    </h3>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                        <?php echo count($course_schedules); ?> horarios
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse course-table" data-course-id="<?php echo $course['id']; ?>">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Día</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Horario</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Aula</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Docente</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Estado</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            $day_schedules = [];
                            foreach ($course_schedules as $schedule) {
                                $day_schedules[$schedule['day_of_week']][] = $schedule;
                            }

                            foreach ($days as $day_key => $day_name):
                                $day_course_schedules = $day_schedules[$day_key] ?? [];
                            ?>
                                <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900 border-b">
                                        <?php echo $day_name; ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 border-b">
                                        <?php if (!empty($day_course_schedules)): ?>
                                            <?php foreach ($day_course_schedules as $schedule): ?>
                                                <div class="mb-2 last:mb-0 schedule-item" data-schedule-id="<?php echo $schedule['id']; ?>">
                                                    <div class="font-medium">
                                                        <?php echo date('h:i A', strtotime($schedule['start_time'])); ?> -
                                                        <?php echo date('h:i A', strtotime($schedule['end_time'])); ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic">Sin clases</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 border-b">
                                        <?php if (!empty($day_course_schedules)): ?>
                                            <?php foreach ($day_course_schedules as $schedule): ?>
                                                <div class="mb-2 last:mb-0">
                                                    <i class="fas fa-map-marker-alt mr-1 text-green-500"></i>
                                                    <?php echo htmlspecialchars($schedule['classroom_name']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 border-b">
                                        <?php if (!empty($day_course_schedules)): ?>
                                            <?php foreach ($day_course_schedules as $schedule): ?>
                                                <div class="mb-2 last:mb-0">
                                                    <i class="fas fa-user mr-1 text-blue-500"></i>
                                                    <?php echo htmlspecialchars($schedule['teacher_name']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm border-b">
                                        <?php if (!empty($day_course_schedules)): ?>
                                            <?php foreach ($day_course_schedules as $schedule): ?>
                                                <div class="mb-2 last:mb-0">
                                                    <span class="px-2 py-1 text-xs rounded-full
                                                        <?php
                                                        echo $schedule['status'] == 'active' ? 'bg-green-100 text-green-800' :
                                                             ($schedule['status'] == 'cancelled' ? 'bg-red-100 text-red-800' :
                                                              ($schedule['status'] == 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'));
                                                        ?>">
                                                        <?php echo ucfirst($schedule['status']); ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm border-b">
                                        <?php if (!empty($day_course_schedules)): ?>
                                            <?php foreach ($day_course_schedules as $schedule): ?>
                                                <div class="mb-2 last:mb-0 flex space-x-1">
                                                    <button onclick="openScheduleModal(<?php echo $schedule['id']; ?>, '<?php echo $day_key; ?>', '<?php echo $schedule['start_time']; ?>', '<?php echo $schedule['end_time']; ?>', <?php echo $schedule['classroom_id']; ?>, <?php echo $course['id']; ?>)"
                                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs transition duration-200">
                                                        <i class="fas fa-edit mr-1"></i>
                                                        Cambiar
                                                    </button>
                                                    <button onclick="deleteSchedule(<?php echo $schedule['id']; ?>, '<?php echo htmlspecialchars($schedule['course_name']); ?>', '<?php echo $day_name; ?>')"
                                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs transition duration-200">
                                                        <i class="fas fa-trash mr-1"></i>
                                                        Eliminar
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <!-- Show specific course -->
        <?php
        $selected_course_data = null;
        foreach ($courses as $course) {
            if ($course['id'] == $selected_course) {
                $selected_course_data = $course;
                break;
            }
        }
        $course_schedules = $schedules_by_course[$selected_course] ?? [];
        ?>

        <?php if ($selected_course_data): ?>
            <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold flex items-center">
                        <i class="fas fa-graduation-cap mr-3 text-primary"></i>
                        <?php echo htmlspecialchars($selected_course_data['name']); ?>
                        <span class="ml-2 text-sm text-gray-600">(<?php echo htmlspecialchars($selected_course_data['code']); ?>)</span>
                    </h3>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                        <?php echo count($course_schedules); ?> horarios
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Día</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Horario</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Aula</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Docente</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Estado</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            $day_schedules = [];
                            foreach ($course_schedules as $schedule) {
                                $day_schedules[$schedule['day_of_week']][] = $schedule;
                            }

                            foreach ($days as $day_key => $day_name):
                                $day_course_schedules = $day_schedules[$day_key] ?? [];
                            ?>
                                <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900 border-b">
                                        <?php echo $day_name; ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 border-b">
                                        <?php if (!empty($day_course_schedules)): ?>
                                            <?php foreach ($day_course_schedules as $schedule): ?>
                                                <div class="mb-2 last:mb-0 schedule-item" data-schedule-id="<?php echo $schedule['id']; ?>">
                                                    <div class="font-medium">
                                                        <?php echo date('h:i A', strtotime($schedule['start_time'])); ?> -
                                                        <?php echo date('h:i A', strtotime($schedule['end_time'])); ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic">Sin clases</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 border-b">
                                        <?php if (!empty($day_course_schedules)): ?>
                                            <?php foreach ($day_course_schedules as $schedule): ?>
                                                <div class="mb-2 last:mb-0">
                                                    <i class="fas fa-map-marker-alt mr-1 text-green-500"></i>
                                                    <?php echo htmlspecialchars($schedule['classroom_name']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 border-b">
                                        <?php if (!empty($day_course_schedules)): ?>
                                            <?php foreach ($day_course_schedules as $schedule): ?>
                                                <div class="mb-2 last:mb-0">
                                                    <i class="fas fa-user mr-1 text-blue-500"></i>
                                                    <?php echo htmlspecialchars($schedule['teacher_name']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm border-b">
                                        <?php if (!empty($day_course_schedules)): ?>
                                            <?php foreach ($day_course_schedules as $schedule): ?>
                                                <div class="mb-2 last:mb-0">
                                                    <span class="px-2 py-1 text-xs rounded-full
                                                        <?php
                                                        echo $schedule['status'] == 'active' ? 'bg-green-100 text-green-800' :
                                                             ($schedule['status'] == 'cancelled' ? 'bg-red-100 text-red-800' :
                                                              ($schedule['status'] == 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'));
                                                        ?>">
                                                        <?php echo ucfirst($schedule['status']); ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm border-b">
                                        <?php if (!empty($day_course_schedules)): ?>
                                            <?php foreach ($day_course_schedules as $schedule): ?>
                                                <div class="mb-2 last:mb-0">
                                                    <button onclick="openScheduleModal(<?php echo $schedule['id']; ?>, '<?php echo $day_key; ?>', '<?php echo $schedule['start_time']; ?>', '<?php echo $schedule['end_time']; ?>', <?php echo $schedule['classroom_id']; ?>, <?php echo $selected_course_data['id']; ?>)"
                                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs transition duration-200">
                                                        <i class="fas fa-edit mr-1"></i>
                                                        Cambiar
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $content,
    'teachers' => $teachers
]);
?>