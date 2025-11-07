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

    <!-- Navigation Tabs -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <div class="flex flex-wrap gap-4 mb-6">
            <button onclick="showTab('schedules')" id="schedules-tab"
                    class="px-6 py-3 rounded-lg font-medium transition duration-300 bg-primary text-white">
                <i class="fas fa-calendar-week mr-2"></i>
                Horarios por Mención
            </button>
            <button onclick="showTab('availability')" id="availability-tab"
                    class="px-6 py-3 rounded-lg font-medium transition duration-300 bg-gray-200 text-gray-700 hover:bg-gray-300">
                <i class="fas fa-building mr-2"></i>
                Disponibilidad de Aulas
            </button>
        </div>

        <!-- Course Filter and Actions for Schedules Tab -->
        <div id="schedules-filters" class="border-t pt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold flex items-center">
                    <i class="fas fa-filter mr-2 text-primary"></i>
                    Filtrar por Mención
                </h3>
                <button onclick="openCreateScheduleModal()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Agregar Horario
                </button>
            </div>
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

        <!-- Availability Filters -->
        <div id="availability-filters" class="border-t pt-6 hidden">
            <h3 class="text-xl font-semibold mb-4 flex items-center">
                <i class="fas fa-search mr-2 text-primary"></i>
                Filtros de Disponibilidad
            </h3>
            <div class="grid md:grid-cols-4 gap-4">
                <div>
                    <label for="filter_classroom" class="block text-sm font-medium text-gray-700 mb-2">Aula</label>
                    <select id="filter_classroom" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="all">Todas las aulas</option>
                        <?php
                        $classrooms = $pdo->query("SELECT * FROM classrooms ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($classrooms as $classroom): ?>
                            <option value="<?php echo $classroom['id']; ?>"><?php echo htmlspecialchars($classroom['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="filter_day" class="block text-sm font-medium text-gray-700 mb-2">Día</label>
                    <select id="filter_day" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="all">Todos los días</option>
                        <option value="monday">Lunes</option>
                        <option value="tuesday">Martes</option>
                        <option value="wednesday">Miércoles</option>
                        <option value="thursday">Jueves</option>
                        <option value="friday">Viernes</option>
                        <option value="saturday">Sábado</option>
                        <option value="sunday">Domingo</option>
                    </select>
                </div>
                <div>
                    <label for="filter_start_time" class="block text-sm font-medium text-gray-700 mb-2">Hora Inicio</label>
                    <input type="time" id="filter_start_time" value="08:00"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label for="filter_end_time" class="block text-sm font-medium text-gray-700 mb-2">Hora Fin</label>
                    <input type="time" id="filter_end_time" value="18:00"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
            </div>
            <div class="mt-4 flex gap-4">
                <button onclick="loadAvailability()" class="bg-primary hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-search mr-2"></i>
                    Buscar Disponibilidad
                </button>
                <button onclick="resetFilters()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Limpiar Filtros
                </button>
            </div>
        </div>
    </div>


    <!-- Schedules Content -->
    <div id="schedules-content">
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
        <?php else: ?>
            <!-- Course schedules content here -->
            <?php if ($selected_course === 'all'): ?>
                <!-- Show all courses -->
                <?php foreach ($teacher_courses as $course): ?>
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
                                <?php echo $course['assignment_type']; ?>
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto border-collapse course-table" data-course-id="<?php echo $course['id']; ?>">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Día</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Horario</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Aula</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 border-b">Profesor</th>
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
                                            <td class="px-4 py-4 text-sm border-b">
                                                <?php if (!empty($day_course_schedules)): ?>
                                                    <?php foreach ($day_course_schedules as $schedule): ?>
                                                        <div class="mb-2 last:mb-0">
                                                            <button onclick="openScheduleModal(<?php echo $schedule['id']; ?>, '<?php echo $day_key; ?>', '<?php echo $schedule['start_time']; ?>', '<?php echo $schedule['end_time']; ?>', <?php echo $schedule['classroom_id']; ?>, <?php echo $course['id']; ?>)"
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
    </div>

    <!-- Availability Content -->
    <div id="availability-content" class="hidden">
        <div id="availability-results" class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <div class="text-center py-12">
                <div class="text-6xl text-gray-300 mb-4">
                    <i class="fas fa-building"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Disponibilidad de Aulas</h3>
                <p class="text-gray-500">Selecciona los filtros y haz clic en "Buscar Disponibilidad"</p>
            </div>
        </div>
    </div>

    <!-- Create Schedule Modal -->
    <div id="createScheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-plus mr-2 text-primary"></i>
                    Agregar Nuevo Horario
                </h3>
                <form id="createScheduleForm" class="space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label for="create_course_id" class="block text-sm font-medium text-gray-700 mb-2">Mención</label>
                            <select id="create_course_id" name="course_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Seleccionar mención</option>
                                <?php foreach ($teacher_courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?> (<?php echo htmlspecialchars($course['code']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="create_day" class="block text-sm font-medium text-gray-700 mb-2">Día</label>
                            <select id="create_day" name="day" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="monday">Lunes</option>
                                <option value="tuesday">Martes</option>
                                <option value="wednesday">Miércoles</option>
                                <option value="thursday">Jueves</option>
                                <option value="friday">Viernes</option>
                                <option value="saturday">Sábado</option>
                                <option value="sunday">Domingo</option>
                            </select>
                        </div>
                        <div>
                            <label for="create_classroom" class="block text-sm font-medium text-gray-700 mb-2">Aula</label>
                            <select id="create_classroom" name="classroom_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Seleccionar aula</option>
                                <?php foreach ($classrooms as $classroom): ?>
                                    <option value="<?php echo $classroom['id']; ?>"><?php echo htmlspecialchars($classroom['name']); ?> (<?php echo $classroom['capacity']; ?> personas)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="create_start_time" class="block text-sm font-medium text-gray-700 mb-2">Hora de Inicio</label>
                            <input type="time" id="create_start_time" name="start_time" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label for="create_end_time" class="block text-sm font-medium text-gray-700 mb-2">Hora de Fin</label>
                            <input type="time" id="create_end_time" name="end_time" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div id="create-availability-preview" class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Disponibilidad del aula seleccionada:</h4>
                        <div id="create-availability-status" class="text-sm text-gray-600">
                            Selecciona mención, día, aula y horario para verificar disponibilidad...
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeCreateScheduleModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-200">Cancelar</button>
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-200">Crear Horario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Schedule Modification Modal -->
    <div id="scheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-edit mr-2 text-primary"></i>
                    Cambiar Horario de Clase
                </h3>
                <form id="scheduleForm" class="space-y-4">
                    <input type="hidden" id="modal_schedule_id" name="schedule_id">
                    <input type="hidden" id="modal_course_id" name="course_id">

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label for="modal_day" class="block text-sm font-medium text-gray-700 mb-2">Día</label>
                            <select id="modal_day" name="day" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="monday">Lunes</option>
                                <option value="tuesday">Martes</option>
                                <option value="wednesday">Miércoles</option>
                                <option value="thursday">Jueves</option>
                                <option value="friday">Viernes</option>
                                <option value="saturday">Sábado</option>
                                <option value="sunday">Domingo</option>
                            </select>
                        </div>
                        <div>
                            <label for="modal_classroom" class="block text-sm font-medium text-gray-700 mb-2">Aula</label>
                            <select id="modal_classroom" name="classroom_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <?php foreach ($classrooms as $classroom): ?>
                                    <option value="<?php echo $classroom['id']; ?>"><?php echo htmlspecialchars($classroom['name']); ?> (<?php echo $classroom['capacity']; ?> personas)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="modal_start_time" class="block text-sm font-medium text-gray-700 mb-2">Hora de Inicio</label>
                            <input type="time" id="modal_start_time" name="start_time" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label for="modal_end_time" class="block text-sm font-medium text-gray-700 mb-2">Hora de Fin</label>
                            <input type="time" id="modal_end_time" name="end_time" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div id="availability-preview" class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Disponibilidad del aula seleccionada:</h4>
                        <div id="availability-status" class="text-sm text-gray-600">
                            Selecciona día, aula y horario para verificar disponibilidad...
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeScheduleModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-200">Cancelar</button>
                        <button type="submit" class="bg-primary hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition duration-200">Actualizar Horario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
// Tab switching functionality
function showTab(tabName) {
    const schedulesTab = document.getElementById('schedules-tab');
    const availabilityTab = document.getElementById('availability-tab');
    const schedulesContent = document.getElementById('schedules-content');
    const availabilityContent = document.getElementById('availability-content');
    const schedulesFilters = document.getElementById('schedules-filters');
    const availabilityFilters = document.getElementById('availability-filters');

    if (tabName === 'schedules') {
        schedulesTab.className = 'px-6 py-3 rounded-lg font-medium transition duration-300 bg-primary text-white';
        availabilityTab.className = 'px-6 py-3 rounded-lg font-medium transition duration-300 bg-gray-200 text-gray-700 hover:bg-gray-300';
        schedulesContent.classList.remove('hidden');
        availabilityContent.classList.add('hidden');
        schedulesFilters.classList.remove('hidden');
        availabilityFilters.classList.add('hidden');
    } else {
        availabilityTab.className = 'px-6 py-3 rounded-lg font-medium transition duration-300 bg-primary text-white';
        schedulesTab.className = 'px-6 py-3 rounded-lg font-medium transition duration-300 bg-gray-200 text-gray-700 hover:bg-gray-300';
        availabilityContent.classList.remove('hidden');
        schedulesContent.classList.add('hidden');
        availabilityFilters.classList.remove('hidden');
        schedulesFilters.classList.add('hidden');
    }
}

// Load classroom availability
function loadAvailability() {
    const classroomId = document.getElementById('filter_classroom').value;
    const day = document.getElementById('filter_day').value;
    const startTime = document.getElementById('filter_start_time').value;
    const endTime = document.getElementById('filter_end_time').value;

    const resultsDiv = document.getElementById('availability-results');

    // Show loading
    resultsDiv.innerHTML = `
        <div class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
            <p class="text-gray-600">Cargando disponibilidad...</p>
        </div>
    `;

    // AJAX request
    fetch('get_availability.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            classroom_id: classroomId,
            day: day,
            start_time: startTime,
            end_time: endTime
        })
    })
    .then(response => response.json())
    .then(data => {
        displayAvailability(data, classroomId, day, startTime, endTime);
    })
    .catch(error => {
        console.error('Error loading availability:', error);
        resultsDiv.innerHTML = `
            <div class="text-center py-12">
                <div class="text-red-500 mb-4">
                    <i class="fas fa-exclamation-triangle text-4xl"></i>
                </div>
                <p class="text-gray-600">Error al cargar la disponibilidad</p>
            </div>
        `;
    });
}

// Display availability results
function displayAvailability(data, classroomId, day, startTime, endTime) {
    const resultsDiv = document.getElementById('availability-results');

    let html = `
        <div class="mb-6">
            <h3 class="text-xl font-semibold mb-4 flex items-center">
                <i class="fas fa-building mr-2 text-primary"></i>
                Disponibilidad de Aulas
            </h3>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <p class="text-sm text-gray-600">
                    <strong>Filtros aplicados:</strong>
                    Aula: ${data.classroom_name || 'Todas'},
                    Día: ${day === 'all' ? 'Todos' : getDayName(day)},
                    Horario: ${startTime} - ${endTime}
                </p>
            </div>
        </div>
    `;

    if (data.availability && data.availability.length > 0) {
        html += '<div class="space-y-4">';

        data.availability.forEach(item => {
            const statusClass = item.available ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
            const statusIcon = item.available ? 'fas fa-check-circle text-green-500' : 'fas fa-times-circle text-red-500';
            const statusText = item.available ? 'Disponible' : 'Ocupado';

            html += `
                <div class="border rounded-lg p-4 ${statusClass}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="text-2xl ${statusIcon}"></div>
                            <div>
                                <h4 class="font-semibold text-gray-800">${item.classroom_name}</h4>
                                <p class="text-sm text-gray-600">
                                    ${getDayName(item.day_of_week)} - ${item.start_time} a ${item.end_time}
                                </p>
                                ${item.available ? '' : `<p class="text-sm text-red-600 mt-1">Ocupado por: ${item.course_name} (${item.teacher_name})</p>`}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-3 py-1 rounded-full text-sm font-medium ${item.available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${statusText}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
    } else {
        html += `
            <div class="text-center py-12">
                <div class="text-6xl text-gray-300 mb-4">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No se encontraron resultados</h3>
                <p class="text-gray-500">Intenta ajustar los filtros de búsqueda</p>
            </div>
        `;
    }

    resultsDiv.innerHTML = html;
}

// Reset filters
function resetFilters() {
    document.getElementById('filter_classroom').value = 'all';
    document.getElementById('filter_day').value = 'all';
    document.getElementById('filter_start_time').value = '08:00';
    document.getElementById('filter_end_time').value = '18:00';

    const resultsDiv = document.getElementById('availability-results');
    resultsDiv.innerHTML = `
        <div class="text-center py-12">
            <div class="text-6xl text-gray-300 mb-4">
                <i class="fas fa-building"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Disponibilidad de Aulas</h3>
            <p class="text-gray-500">Selecciona los filtros y haz clic en "Buscar Disponibilidad"</p>
        </div>
    `;
}

// Helper function to get day names
function getDayName(dayKey) {
    const days = {
        'monday': 'Lunes',
        'tuesday': 'Martes',
        'wednesday': 'Miércoles',
        'thursday': 'Jueves',
        'friday': 'Viernes',
        'saturday': 'Sábado',
        'sunday': 'Domingo'
    };
    return days[dayKey] || dayKey;
}

// Create schedule modal functions
function openCreateScheduleModal() {
    document.getElementById('createScheduleModal').classList.remove('hidden');

    // Reset form
    document.getElementById('createScheduleForm').reset();
    document.getElementById('create-availability-status').innerHTML = 'Selecciona mención, día, aula y horario para verificar disponibilidad...';
}

function closeCreateScheduleModal() {
    document.getElementById('createScheduleModal').classList.add('hidden');
}

// Check availability for create schedule form
function checkCreateAvailability() {
    const courseId = document.getElementById('create_course_id').value;
    const classroomId = document.getElementById('create_classroom').value;
    const day = document.getElementById('create_day').value;
    const startTime = document.getElementById('create_start_time').value;
    const endTime = document.getElementById('create_end_time').value;

    if (!courseId || !classroomId || !day || !startTime || !endTime) {
        document.getElementById('create-availability-status').innerHTML = 'Selecciona mención, día, aula y horario para verificar disponibilidad...';
        return;
    }

    // AJAX request to check availability
    fetch('check_availability.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            classroom_id: classroomId,
            day: day,
            start_time: startTime,
            end_time: endTime
        })
    })
    .then(response => response.json())
    .then(data => {
        const statusDiv = document.getElementById('create-availability-status');
        if (data.available) {
            statusDiv.innerHTML = '<span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Aula disponible para el horario seleccionado</span>';
        } else {
            statusDiv.innerHTML = '<span class="text-red-600"><i class="fas fa-times-circle mr-1"></i>Aula ocupada. Conflicto con: ' + data.conflict_info + '</span>';
        }
    })
    .catch(error => {
        console.error('Error checking availability:', error);
        document.getElementById('create-availability-status').innerHTML = '<span class="text-yellow-600"><i class="fas fa-exclamation-triangle mr-1"></i>Error al verificar disponibilidad</span>';
    });
}

// Event listeners for create schedule availability checking
document.getElementById('create_course_id').addEventListener('change', checkCreateAvailability);
document.getElementById('create_classroom').addEventListener('change', checkCreateAvailability);
document.getElementById('create_day').addEventListener('change', checkCreateAvailability);
document.getElementById('create_start_time').addEventListener('change', checkCreateAvailability);
document.getElementById('create_end_time').addEventListener('change', checkCreateAvailability);

// Handle create schedule form submission
document.getElementById('createScheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    // Show loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando...';
    submitBtn.disabled = true;

    fetch('create_schedule.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreateScheduleModal();
            // Show success message
            showAjaxMessage('Horario creado exitosamente', 'success');
            // Reload the page after a short delay to show the message
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showAjaxMessage('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error creating schedule:', error);
        showAjaxMessage('Error al crear el horario', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Schedule modification modal functions
function openScheduleModal(scheduleId, currentDay, currentStartTime, currentEndTime, currentClassroomId, courseId) {
    document.getElementById('modal_schedule_id').value = scheduleId;
    document.getElementById('modal_course_id').value = courseId;
    document.getElementById('modal_day').value = currentDay;
    document.getElementById('modal_start_time').value = currentStartTime;
    document.getElementById('modal_end_time').value = currentEndTime;
    document.getElementById('modal_classroom').value = currentClassroomId;

    document.getElementById('scheduleModal').classList.remove('hidden');

    // Check initial availability
    checkAvailability();
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').classList.add('hidden');
}

// Check classroom availability for selected time
function checkAvailability() {
    const classroomId = document.getElementById('modal_classroom').value;
    const day = document.getElementById('modal_day').value;
    const startTime = document.getElementById('modal_start_time').value;
    const endTime = document.getElementById('modal_end_time').value;
    const scheduleId = document.getElementById('modal_schedule_id').value;

    if (!classroomId || !day || !startTime || !endTime) {
        document.getElementById('availability-status').innerHTML = 'Selecciona día, aula y horario para verificar disponibilidad...';
        return;
    }

    // AJAX request to check availability
    fetch('check_availability.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            classroom_id: classroomId,
            day: day,
            start_time: startTime,
            end_time: endTime,
            exclude_schedule_id: scheduleId
        })
    })
    .then(response => response.json())
    .then(data => {
        const statusDiv = document.getElementById('availability-status');
        if (data.available) {
            statusDiv.innerHTML = '<span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Aula disponible para el horario seleccionado</span>';
        } else {
            statusDiv.innerHTML = '<span class="text-red-600"><i class="fas fa-times-circle mr-1"></i>Aula ocupada. Conflicto con: ' + data.conflict_info + '</span>';
        }
    })
    .catch(error => {
        console.error('Error checking availability:', error);
        document.getElementById('availability-status').innerHTML = '<span class="text-yellow-600"><i class="fas fa-exclamation-triangle mr-1"></i>Error al verificar disponibilidad</span>';
    });
}

// Event listeners for availability checking
document.getElementById('modal_classroom').addEventListener('change', checkAvailability);
document.getElementById('modal_day').addEventListener('change', checkAvailability);
document.getElementById('modal_start_time').addEventListener('change', checkAvailability);
document.getElementById('modal_end_time').addEventListener('change', checkAvailability);

// Handle schedule form submission
document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    // Show loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...';
    submitBtn.disabled = true;

    fetch('update_schedule.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeScheduleModal();
            // Show success message
            showAjaxMessage('Horario actualizado exitosamente', 'success');
            // Reload the page after a short delay to show the message
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showAjaxMessage('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating schedule:', error);
        showAjaxMessage('Error al actualizar el horario', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Function to show AJAX messages
function showAjaxMessage(message, type) {
    const successDiv = document.getElementById('ajax-success-message');
    const errorDiv = document.getElementById('ajax-error-message');
    const successText = document.getElementById('ajax-success-text');
    const errorText = document.getElementById('ajax-error-text');

    // Hide both messages first
    successDiv.classList.add('hidden');
    errorDiv.classList.add('hidden');

    // Show the appropriate message
    if (type === 'success') {
        successText.textContent = message;
        successDiv.classList.remove('hidden');
        // Auto-hide after 3 seconds
        setTimeout(() => {
            successDiv.classList.add('hidden');
        }, 3000);
    } else {
        errorText.textContent = message;
        errorDiv.classList.remove('hidden');
        // Auto-hide after 5 seconds for errors
        setTimeout(() => {
            errorDiv.classList.add('hidden');
        }, 5000);
    }

    // Scroll to top to show the message
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const createModal = document.getElementById('createScheduleModal');
    const editModal = document.getElementById('scheduleModal');

    if (event.target === createModal) {
        closeCreateScheduleModal();
    }
    if (event.target === editModal) {
        closeScheduleModal();
    }
});
</script>

<?php include '../../templates/footer.php'; ?>