<?php require_once '../../includes/config.php'; ?>
<?php requireRole('admin'); ?>
<?php include '../../templates/header.php'; ?>

<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_schedule'])) {
        $classroom_id = (int)$_POST['classroom_id'];
        $course_id = (int)$_POST['course_id'];
        $teacher_id = (int)$_POST['teacher_id'];
        $day_of_week = sanitize($_POST['day_of_week']);
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $semester = isset($_POST['semester']) ? sanitize($_POST['semester']) : '2025-1';
        $academic_year = isset($_POST['academic_year']) ? sanitize($_POST['academic_year']) : '2025';
        $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';

        // Check for conflicts
        $conflicts = checkScheduleConflicts($pdo, $classroom_id, $teacher_id, $day_of_week, $start_time, $end_time);

        if (!empty($conflicts)) {
            $error = "Conflicto detectado: " . implode(", ", $conflicts);
        } else {
            $stmt = $pdo->prepare("INSERT INTO schedules (classroom_id, course_id, teacher_id, day_of_week, start_time, end_time, semester, academic_year, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$classroom_id, $course_id, $teacher_id, $day_of_week, $start_time, $end_time, $semester, $academic_year, $notes]);
            $success = "Horario creado exitosamente.";
        }
    } elseif (isset($_POST['update_schedule'])) {
        $id = (int)$_POST['id'];
        $classroom_id = (int)$_POST['classroom_id'];
        $course_id = (int)$_POST['course_id'];
        $teacher_id = (int)$_POST['teacher_id'];
        $day_of_week = sanitize($_POST['day_of_week']);
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $semester = isset($_POST['semester']) ? sanitize($_POST['semester']) : '2025-1';
        $academic_year = isset($_POST['academic_year']) ? sanitize($_POST['academic_year']) : '2025';
        $status = isset($_POST['status']) ? sanitize($_POST['status']) : 'active';
        $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';

        // Check for conflicts (excluding current schedule)
        $conflicts = checkScheduleConflicts($pdo, $classroom_id, $teacher_id, $day_of_week, $start_time, $end_time, $id);

        if (!empty($conflicts)) {
            $error = "Conflicto detectado: " . implode(", ", $conflicts);
        } else {
            $stmt = $pdo->prepare("UPDATE schedules SET classroom_id = ?, course_id = ?, teacher_id = ?, day_of_week = ?, start_time = ?, end_time = ?, semester = ?, academic_year = ?, status = ?, notes = ? WHERE id = ?");
            $stmt->execute([$classroom_id, $course_id, $teacher_id, $day_of_week, $start_time, $end_time, $semester, $academic_year, $status, $notes, $id]);
            $success = "Horario actualizado exitosamente.";
        }
    } elseif (isset($_POST['delete_schedule'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Horario eliminado exitosamente.";
    } elseif (isset($_POST['bulk_update'])) {
        $schedule_ids = $_POST['schedule_ids'] ?? [];
        $action = $_POST['bulk_action'];

        if (!empty($schedule_ids)) {
            if ($action == 'activate') {
                $stmt = $pdo->prepare("UPDATE schedules SET status = 'active' WHERE id IN (" . str_repeat('?,', count($schedule_ids) - 1) . "?)");
                $stmt->execute($schedule_ids);
                $success = count($schedule_ids) . " horarios activados.";
            } elseif ($action == 'cancel') {
                $stmt = $pdo->prepare("UPDATE schedules SET status = 'cancelled' WHERE id IN (" . str_repeat('?,', count($schedule_ids) - 1) . "?)");
                $stmt->execute($schedule_ids);
                $success = count($schedule_ids) . " horarios cancelados.";
            } elseif ($action == 'complete') {
                $stmt = $pdo->prepare("UPDATE schedules SET status = 'completed' WHERE id IN (" . str_repeat('?,', count($schedule_ids) - 1) . "?)");
                $stmt->execute($schedule_ids);
                $success = count($schedule_ids) . " horarios completados.";
            } elseif ($action == 'delete') {
                $stmt = $pdo->prepare("DELETE FROM schedules WHERE id IN (" . str_repeat('?,', count($schedule_ids) - 1) . "?)");
                $stmt->execute($schedule_ids);
                $success = count($schedule_ids) . " horarios eliminados.";
            }
        }
    }
}

// Function to check schedule conflicts
function checkScheduleConflicts($pdo, $classroom_id, $teacher_id, $day_of_week, $start_time, $end_time, $exclude_id = null) {
    $conflicts = [];

    // Check classroom conflict
    $query = "SELECT s.*, c.name as classroom_name, co.name as course_name, u.name as teacher_name
              FROM schedules s
              JOIN classrooms c ON s.classroom_id = c.id
              JOIN courses co ON s.course_id = co.id
              JOIN users u ON s.teacher_id = u.id
              WHERE s.classroom_id = ? AND s.day_of_week = ? AND s.status = 'active'
              AND ((s.start_time <= ? AND s.end_time > ?) OR (s.start_time < ? AND s.end_time >= ?))";

    $params = [$classroom_id, $day_of_week, $start_time, $start_time, $end_time, $end_time];

    if ($exclude_id) {
        $query .= " AND s.id != ?";
        $params[] = $exclude_id;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        $conflict = $stmt->fetch(PDO::FETCH_ASSOC);
        $conflicts[] = "Aula ocupada por '{$conflict['course_name']}' con {$conflict['teacher_name']} ({$conflict['start_time']}-{$conflict['end_time']})";
    }

    // Check teacher conflict
    $query = "SELECT s.*, c.name as classroom_name, co.name as course_name, u.name as teacher_name
              FROM schedules s
              JOIN classrooms c ON s.classroom_id = c.id
              JOIN courses co ON s.course_id = co.id
              JOIN users u ON s.teacher_id = u.id
              WHERE s.teacher_id = ? AND s.day_of_week = ? AND s.status = 'active'
              AND ((s.start_time <= ? AND s.end_time > ?) OR (s.start_time < ? AND s.end_time >= ?))";

    $params = [$teacher_id, $day_of_week, $start_time, $start_time, $end_time, $end_time];

    if ($exclude_id) {
        $query .= " AND s.id != ?";
        $params[] = $exclude_id;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        $conflict = $stmt->fetch(PDO::FETCH_ASSOC);
        $conflicts[] = "Docente ocupado con '{$conflict['course_name']}' en {$conflict['classroom_name']} ({$conflict['start_time']}-{$conflict['end_time']})";
    }

    return $conflicts;
}

// Get filter parameters
$filter_semester = $_GET['semester'] ?? '';
$filter_year = $_GET['year'] ?? '';
$filter_status = $_GET['status'] ?? 'all';

// Fetch data for dropdowns
$classrooms = $pdo->query("SELECT * FROM classrooms ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$courses = $pdo->query("SELECT * FROM courses ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$teachers = $pdo->query("SELECT * FROM users WHERE role = 'teacher' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Build query with filters
$query = "
    SELECT s.*, c.name as classroom_name, co.name as course_name, co.code as course_code, u.name as teacher_name
    FROM schedules s
    JOIN classrooms c ON s.classroom_id = c.id
    JOIN courses co ON s.course_id = co.id
    JOIN users u ON s.teacher_id = u.id
    WHERE 1=1
";

$params = [];

if ($filter_semester) {
    $query .= " AND s.semester = ?";
    $params[] = $filter_semester;
}

if ($filter_year) {
    $query .= " AND s.academic_year = ?";
    $params[] = $filter_year;
}

if ($filter_status && $filter_status != 'all') {
    $query .= " AND s.status = ?";
    $params[] = $filter_status;
}

$query .= " ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group schedules by day
$schedules_by_day = [];
foreach ($schedules as $schedule) {
    $schedules_by_day[$schedule['day_of_week']][] = $schedule;
}

// Get unique semesters and years for filters
$semesters = $pdo->query("SELECT DISTINCT semester FROM schedules ORDER BY semester DESC")->fetchAll(PDO::FETCH_COLUMN);
$years = $pdo->query("SELECT DISTINCT academic_year FROM schedules ORDER BY academic_year DESC")->fetchAll(PDO::FETCH_COLUMN);

// Statistics
$total_schedules = count($schedules);
$active_schedules = count(array_filter($schedules, function($s) { return $s['status'] == 'active'; }));
$conflicts_count = $pdo->query("SELECT COUNT(*) FROM schedule_conflicts WHERE DATE(detected_at) = CURDATE()")->fetchColumn();
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-calendar-check mr-4 text-primary"></i>
        Gestión Completa de Horarios
    </h2>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 animate-fade-in-up">
            <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 animate-fade-in-up">
            <i class="fas fa-exclamation-triangle mr-2"></i><?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Dashboard -->
    <div class="grid md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Horarios</p>
                    <p class="text-3xl font-bold text-primary"><?php echo number_format($total_schedules); ?></p>
                </div>
                <div class="text-4xl text-primary opacity-70">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.1s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Horarios Activos</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo number_format($active_schedules); ?></p>
                </div>
                <div class="text-4xl text-green-600 opacity-70">
                    <i class="fas fa-play-circle"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.2s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Conflictos Hoy</p>
                    <p class="text-3xl font-bold text-red-600"><?php echo number_format($conflicts_count); ?></p>
                </div>
                <div class="text-4xl text-red-600 opacity-70">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.3s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Aulas Ocupadas</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo number_format(count(array_unique(array_column($schedules, 'classroom_id')))); ?></p>
                </div>
                <div class="text-4xl text-blue-600 opacity-70">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-filter mr-2 text-primary"></i>
            Filtros de Horarios
        </h3>
        <form method="GET" class="grid md:grid-cols-4 gap-4">
            <div>
                <label for="semester" class="block text-sm font-medium text-gray-700 mb-2">Semestre</label>
                <select id="semester" name="semester" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    <option value="">Todos los semestres</option>
                    <?php foreach ($semesters as $semester): ?>
                        <option value="<?php echo $semester; ?>" <?php echo $filter_semester == $semester ? 'selected' : ''; ?>><?php echo $semester; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Año Académico</label>
                <select id="year" name="year" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    <option value="">Todos los años</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo $filter_year == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select id="status" name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    <option value="all">Todos los estados</option>
                    <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Activo</option>
                    <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                    <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Completado</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-primary hover:bg-yellow-600 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex items-center">
                    <i class="fas fa-search mr-2"></i>
                    Filtrar
                </button>
                <a href="manage.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex items-center">
                    <i class="fas fa-times mr-2"></i>
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Create Schedule Form -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-plus-circle mr-2 text-primary"></i>
            Crear Nuevo Horario
        </h3>
        <form method="POST" class="space-y-6">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label for="classroom_id" class="block text-sm font-medium text-gray-700 mb-2">Aula</label>
                    <select id="classroom_id" name="classroom_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        <option value="">Seleccionar aula</option>
                        <?php foreach ($classrooms as $classroom): ?>
                            <option value="<?php echo $classroom['id']; ?>"><?php echo htmlspecialchars($classroom['name']); ?> (<?php echo $classroom['capacity']; ?> personas)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">Curso</label>
                    <select id="course_id" name="course_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        <option value="">Seleccionar curso</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?> (<?php echo htmlspecialchars($course['code']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-2">Docente</label>
                    <select id="teacher_id" name="teacher_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        <option value="">Seleccionar docente</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="day_of_week" class="block text-sm font-medium text-gray-700 mb-2">Día de la Semana</label>
                    <select id="day_of_week" name="day_of_week" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
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
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">Hora de Inicio</label>
                    <input type="time" id="start_time" name="start_time" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">Hora de Fin</label>
                    <input type="time" id="end_time" name="end_time" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
                <div>
                    <label for="semester" class="block text-sm font-medium text-gray-700 mb-2">Semestre</label>
                    <select id="semester" name="semester" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        <option value="2025-1">2025-1</option>
                        <option value="2025-2">2025-2</option>
                        <option value="2026-1">2026-1</option>
                        <option value="2026-2">2026-2</option>
                    </select>
                </div>
                <div>
                    <label for="academic_year" class="block text-sm font-medium text-gray-700 mb-2">Año Académico</label>
                    <select id="academic_year" name="academic_year" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                        <option value="2027">2027</option>
                    </select>
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notas Adicionales</label>
                    <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200" placeholder="Información adicional sobre el horario..."></textarea>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" name="create_schedule" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                    <i class="fas fa-calendar-plus mr-2"></i>
                    Crear Horario
                </button>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-tasks mr-2 text-primary"></i>
            Acciones Masivas
        </h3>
        <form method="POST" id="bulkForm" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center space-x-2">
                <input type="checkbox" id="selectAll" class="w-4 h-4 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="selectAll" class="text-sm font-medium text-gray-700">Seleccionar Todos</label>
            </div>
            <select name="bulk_action" required class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="">Seleccionar acción</option>
                <option value="activate">Activar Horarios</option>
                <option value="cancel">Cancelar Horarios</option>
                <option value="complete">Marcar como Completado</option>
                <option value="delete">Eliminar Horarios</option>
            </select>
            <button type="submit" name="bulk_update" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white font-bold py-2 px-4 rounded-lg hover:shadow-lg transition duration-300 flex items-center">
                <i class="fas fa-bolt mr-2"></i>
                Ejecutar Acción
            </button>
        </form>
    </div>

    <!-- Schedules Display -->
    <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold flex items-center">
                <i class="fas fa-calendar-week mr-2 text-primary"></i>
                Horarios Programados (<?php echo $total_schedules; ?>)
            </h3>
            <div class="text-sm text-gray-600">
                Mostrando <?php echo count($schedules); ?> horarios
            </div>
        </div>

        <div class="grid md:grid-cols-1 gap-6">
            <?php
            $days = [
                'monday' => 'Lunes',
                'tuesday' => 'Martes',
                'wednesday' => 'Miércoles',
                'thursday' => 'Jueves',
                'friday' => 'Viernes',
                'saturday' => 'Sábado',
                'sunday' => 'Domingo'
            ];

            foreach ($days as $day_key => $day_name):
                $day_schedules = $schedules_by_day[$day_key] ?? [];
            ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-calendar-day mr-2 text-primary"></i>
                        <?php echo $day_name; ?>
                        <span class="ml-2 text-sm text-gray-500">(<?php echo count($day_schedules); ?> clases)</span>
                    </h4>

                    <?php if (empty($day_schedules)): ?>
                        <div class="text-center py-8">
                            <div class="text-6xl text-gray-300 mb-4">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <p class="text-gray-500 text-lg">No hay clases programadas para este día</p>
                            <p class="text-gray-400 text-sm mt-2">¡Día libre!</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($day_schedules as $schedule): ?>
                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border-l-4 border-primary relative">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start space-x-4 flex-1">
                                            <input type="checkbox" name="schedule_ids[]" value="<?php echo $schedule['id']; ?>" form="bulkForm" class="mt-1 w-4 h-4 text-primary focus:ring-primary border-gray-300 rounded">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3 mb-2">
                                                    <h5 class="font-semibold text-gray-800"><?php echo htmlspecialchars($schedule['course_name']); ?> (<?php echo htmlspecialchars($schedule['course_code']); ?>)</h5>
                                                    <span class="px-2 py-1 text-xs rounded-full <?php
                                                        echo $schedule['status'] == 'active' ? 'bg-green-100 text-green-800' :
                                                             ($schedule['status'] == 'cancelled' ? 'bg-red-100 text-red-800' :
                                                              ($schedule['status'] == 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'));
                                                    ?>">
                                                        <?php echo ucfirst($schedule['status']); ?>
                                                    </span>
                                                </div>
                                                <div class="grid md:grid-cols-2 gap-2 text-sm text-gray-600 mb-2">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-user mr-2 text-blue-500"></i>
                                                        <?php echo htmlspecialchars($schedule['teacher_name']); ?>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-map-marker-alt mr-2 text-green-500"></i>
                                                        <?php echo htmlspecialchars($schedule['classroom_name']); ?>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-clock mr-2 text-purple-500"></i>
                                                        <?php echo date('H:i', strtotime($schedule['start_time'])); ?> - <?php echo date('H:i', strtotime($schedule['end_time'])); ?>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-calendar-alt mr-2 text-orange-500"></i>
                                                        <?php echo $schedule['semester']; ?> (<?php echo $schedule['academic_year']; ?>)
                                                    </div>
                                                </div>
                                                <?php if (!empty($schedule['notes'])): ?>
                                                    <div class="text-sm text-gray-500 italic mt-2">
                                                        <i class="fas fa-sticky-note mr-1"></i>
                                                        <?php echo htmlspecialchars($schedule['notes']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2 ml-4">
                                            <button onclick="editSchedule(<?php echo $schedule['id']; ?>, '<?php echo htmlspecialchars($schedule['classroom_id']); ?>', '<?php echo htmlspecialchars($schedule['course_id']); ?>', '<?php echo htmlspecialchars($schedule['teacher_id']); ?>', '<?php echo $schedule['day_of_week']; ?>', '<?php echo $schedule['start_time']; ?>', '<?php echo $schedule['end_time']; ?>', '<?php echo $schedule['semester']; ?>', '<?php echo $schedule['academic_year']; ?>', '<?php echo $schedule['status']; ?>', '<?php echo htmlspecialchars($schedule['notes'] ?? ''); ?>')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200 flex items-center" title="Editar horario">
                                                <i class="fas fa-edit mr-1"></i>
                                                <span class="hidden sm:inline">Editar</span>
                                            </button>
                                            <form method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este horario?')">
                                                <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">
                                                <button type="submit" name="delete_schedule" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200 flex items-center" title="Eliminar horario">
                                                    <i class="fas fa-trash mr-1"></i>
                                                    <span class="hidden sm:inline">Eliminar</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Edit Schedule Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-edit mr-2 text-primary"></i>
                        Editar Horario
                    </h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <form method="POST" class="space-y-6">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label for="edit_classroom_id" class="block text-sm font-medium text-gray-700 mb-2">Aula</label>
                            <select id="edit_classroom_id" name="classroom_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                                <option value="">Seleccionar aula</option>
                                <?php foreach ($classrooms as $classroom): ?>
                                    <option value="<?php echo $classroom['id']; ?>"><?php echo htmlspecialchars($classroom['name']); ?> (<?php echo $classroom['capacity']; ?> personas)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="edit_course_id" class="block text-sm font-medium text-gray-700 mb-2">Curso</label>
                            <select id="edit_course_id" name="course_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                                <option value="">Seleccionar curso</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?> (<?php echo htmlspecialchars($course['code']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="edit_teacher_id" class="block text-sm font-medium text-gray-700 mb-2">Docente</label>
                            <select id="edit_teacher_id" name="teacher_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                                <option value="">Seleccionar docente</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>"><?php echo htmlspecialchars($teacher['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="edit_day_of_week" class="block text-sm font-medium text-gray-700 mb-2">Día de la Semana</label>
                            <select id="edit_day_of_week" name="day_of_week" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
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
                            <label for="edit_start_time" class="block text-sm font-medium text-gray-700 mb-2">Hora de Inicio</label>
                            <input type="time" id="edit_start_time" name="start_time" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        </div>
                        <div>
                            <label for="edit_end_time" class="block text-sm font-medium text-gray-700 mb-2">Hora de Fin</label>
                            <input type="time" id="edit_end_time" name="end_time" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        </div>
                        <div>
                            <label for="edit_semester" class="block text-sm font-medium text-gray-700 mb-2">Semestre</label>
                            <select id="edit_semester" name="semester" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                                <option value="2025-1">2025-1</option>
                                <option value="2025-2">2025-2</option>
                                <option value="2026-1">2026-1</option>
                                <option value="2026-2">2026-2</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit_academic_year" class="block text-sm font-medium text-gray-700 mb-2">Año Académico</label>
                            <select id="edit_academic_year" name="academic_year" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select id="edit_status" name="status" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                                <option value="active">Activo</option>
                                <option value="cancelled">Cancelado</option>
                                <option value="completed">Completado</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label for="edit_notes" class="block text-sm font-medium text-gray-700 mb-2">Notas Adicionales</label>
                            <textarea id="edit_notes" name="notes" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200" placeholder="Información adicional sobre el horario..."></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEditModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-200">Cancelar</button>
                        <button type="submit" name="update_schedule" class="bg-primary hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition duration-200">Actualizar Horario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>
<script>
// Select all checkboxes functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="schedule_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

// Edit schedule modal functions
function editSchedule(id, classroom_id, course_id, teacher_id, day_of_week, start_time, end_time, semester, academic_year, status, notes) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_classroom_id').value = classroom_id;
    document.getElementById('edit_course_id').value = course_id;
    document.getElementById('edit_teacher_id').value = teacher_id;
    document.getElementById('edit_day_of_week').value = day_of_week;
    document.getElementById('edit_start_time').value = start_time;
    document.getElementById('edit_end_time').value = end_time;
    document.getElementById('edit_semester').value = semester;
    document.getElementById('edit_academic_year').value = academic_year;
    document.getElementById('edit_status').value = status;
    document.getElementById('edit_notes').value = notes;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) {
        closeEditModal();
    }
});

// Auto-update end time when start time changes (assuming 1 hour classes)
document.getElementById('start_time').addEventListener('change', function() {
    const startTime = this.value;
    if (startTime) {
        const [hours, minutes] = startTime.split(':');
        const endHour = (parseInt(hours) + 1) % 24;
        const endTime = `${endHour.toString().padStart(2, '0')}:${minutes}`;
        document.getElementById('end_time').value = endTime;
    }
});

// Same for edit modal
document.getElementById('edit_start_time').addEventListener('change', function() {
    const startTime = this.value;
    if (startTime) {
        const [hours, minutes] = startTime.split(':');
        const endHour = (parseInt(hours) + 1) % 24;
        const endTime = `${endHour.toString().padStart(2, '0')}:${minutes}`;
        document.getElementById('edit_end_time').value = endTime;
    }
});
</script>