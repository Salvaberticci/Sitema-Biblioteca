<?php require_once '../../includes/config.php'; ?>
<?php requireRole('teacher'); ?>
<?php include '../../templates/header.php'; ?>

<?php
$teacher_id = $_SESSION['user_id'];

// Get all courses taught by this teacher (both scheduled and self-assigned)
$stmt = $pdo->prepare("
    SELECT DISTINCT c.id, c.name, c.code,
           CASE WHEN tc.id IS NOT NULL THEN 'Auto-asignado' ELSE 'Programado' END as assignment_type
    FROM courses c
    LEFT JOIN schedules s ON c.id = s.course_id AND s.teacher_id = ?
    LEFT JOIN teacher_courses tc ON c.id = tc.course_id AND tc.teacher_id = ? AND tc.status = 'active'
    WHERE (s.teacher_id = ? OR tc.teacher_id = ?)
    ORDER BY c.name
");
$stmt->execute([$teacher_id, $teacher_id, $teacher_id, $teacher_id]);
$teacher_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected course filter
$selected_course = $_GET['course'] ?? 'all';

// Get schedules for the selected course(s)
$query = "
    SELECT s.*, c.name as classroom_name, co.name as course_name, co.code as course_code, u.name as teacher_name
    FROM schedules s
    JOIN classrooms c ON s.classroom_id = c.id
    JOIN courses co ON s.course_id = co.id
    JOIN users u ON s.teacher_id = u.id
    WHERE s.teacher_id = ? AND s.status = 'active'
";

$params = [$teacher_id];

if ($selected_course !== 'all') {
    $query .= " AND s.course_id = ?";
    $params[] = $selected_course;
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
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-calendar-week mr-4 text-primary"></i>
        Horarios de Clases por Mención
    </h2>

    <!-- Course Filter -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-filter mr-2 text-primary"></i>
            Filtrar por Mención
        </h3>
        <form method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center space-x-4">
                <label for="course" class="text-sm font-medium text-gray-700">Seleccionar Mención:</label>
                <select id="course" name="course" onchange="this.form.submit()"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="all" <?php echo $selected_course === 'all' ? 'selected' : ''; ?>>Todas las menciones</option>
                    <?php foreach ($teacher_courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo $selected_course == $course['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['name']); ?> (<?php echo htmlspecialchars($course['code']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="text-sm text-gray-600">
                Mostrando <?php echo count($schedules); ?> horarios
            </div>
        </form>
    </div>

    <?php if ($selected_course === 'all'): ?>
        <!-- Show all courses -->
        <?php foreach ($teacher_courses as $course): ?>
            <?php
            $course_schedules = $schedules_by_course[$course['id']] ?? [];
            if (empty($course_schedules)) continue;
            ?>
            <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold flex items-center">
                        <i class="fas fa-graduation-cap mr-3 text-primary"></i>
                        <?php echo htmlspecialchars($course['name']); ?>
                        <span class="ml-2 text-sm text-gray-600">(<?php echo htmlspecialchars($course['code']); ?>)</span>
                    </h3>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                        <?php echo $course['assignment_type']; ?>
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Día</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Horario</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Aula</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Profesor</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Estado</th>
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
                                                <div class="mb-2 last:mb-0">
                                                    <div class="font-medium">
                                                        <?php echo date('H:i', strtotime($schedule['start_time'])); ?> -
                                                        <?php echo date('H:i', strtotime($schedule['end_time'])); ?>
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
        foreach ($teacher_courses as $course) {
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
                        <?php echo $selected_course_data['assignment_type']; ?>
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Día</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Horario</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Aula</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Profesor</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Estado</th>
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
                                                <div class="mb-2 last:mb-0">
                                                    <div class="font-medium">
                                                        <?php echo date('H:i', strtotime($schedule['start_time'])); ?> -
                                                        <?php echo date('H:i', strtotime($schedule['end_time'])); ?>
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
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (empty($teacher_courses)): ?>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <div class="text-center py-12">
                <div class="text-6xl text-gray-300 mb-4">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay horarios disponibles</h3>
                <p class="text-gray-500">No tienes menciones asignadas con horarios programados.</p>
                <div class="mt-6">
                    <a href="assignments.php" class="bg-primary hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300 inline-flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Asignar Menciones
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include '../../templates/footer.php'; ?>