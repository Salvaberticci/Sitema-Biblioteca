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

        $stmt = $pdo->prepare("INSERT INTO schedules (classroom_id, course_id, teacher_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$classroom_id, $course_id, $teacher_id, $day_of_week, $start_time, $end_time]);
        $success = "Horario creado exitosamente.";
    } elseif (isset($_POST['delete_schedule'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Horario eliminado exitosamente.";
    }
}

// Fetch data for dropdowns
$classrooms = $pdo->query("SELECT * FROM classrooms ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$courses = $pdo->query("SELECT * FROM courses ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$teachers = $pdo->query("SELECT * FROM users WHERE role = 'teacher' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all schedules with related data
$stmt = $pdo->prepare("
    SELECT s.*, c.name as classroom_name, co.name as course_name, co.code as course_code, u.name as teacher_name
    FROM schedules s
    JOIN classrooms c ON s.classroom_id = c.id
    JOIN courses co ON s.course_id = co.id
    JOIN users u ON s.teacher_id = u.id
    ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time
");
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group schedules by day
$schedules_by_day = [];
foreach ($schedules as $schedule) {
    $schedules_by_day[$schedule['day_of_week']][] = $schedule;
}
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-calendar-check mr-4 text-primary"></i>
        Gestión de Horarios de Aulas
    </h2>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 animate-fade-in-up">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-plus-circle mr-2 text-primary"></i>
            Crear Nuevo Horario
        </h3>
        <form method="POST" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
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
            <div class="lg:col-span-3">
                <button type="submit" name="create_schedule" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                    <i class="fas fa-calendar-plus mr-2"></i>
                    Crear Horario
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-calendar-week mr-2 text-primary"></i>
            Horarios Programados
        </h3>

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
                        <p class="text-gray-500 italic">No hay clases programadas para este día.</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($day_schedules as $schedule): ?>
                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border-l-4 border-primary">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h5 class="font-semibold text-gray-800"><?php echo htmlspecialchars($schedule['course_name']); ?> (<?php echo htmlspecialchars($schedule['course_code']); ?>)</h5>
                                            <p class="text-sm text-gray-600">
                                                <i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($schedule['teacher_name']); ?> |
                                                <i class="fas fa-map-marker-alt mr-1 ml-2"></i><?php echo htmlspecialchars($schedule['classroom_name']); ?> |
                                                <i class="fas fa-clock mr-1 ml-2"></i><?php echo date('H:i', strtotime($schedule['start_time'])); ?> - <?php echo date('H:i', strtotime($schedule['end_time'])); ?>
                                            </p>
                                        </div>
                                        <form method="POST" class="ml-4" onsubmit="return confirm('¿Está seguro de eliminar este horario?')">
                                            <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">
                                            <button type="submit" name="delete_schedule" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm transition duration-200">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>