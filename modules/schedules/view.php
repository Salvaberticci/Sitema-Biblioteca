<?php require_once '../../includes/config.php'; ?>
<?php include '../../templates/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Build query based on user role
if ($user_role == 'student') {
    // Students see schedules for courses they're enrolled in
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.*, c.name as classroom_name, co.name as course_name, co.code as course_code, u.name as teacher_name
        FROM schedules s
        JOIN classrooms c ON s.classroom_id = c.id
        JOIN courses co ON s.course_id = co.id
        JOIN users u ON s.teacher_id = u.id
        JOIN enrollments e ON co.id = e.course_id
        WHERE e.student_id = ? AND e.status = 'enrolled'
        ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time
    ");
    $stmt->execute([$user_id]);
} elseif ($user_role == 'teacher') {
    // Teachers see their own schedules
    $stmt = $pdo->prepare("
        SELECT s.*, c.name as classroom_name, co.name as course_name, co.code as course_code, u.name as teacher_name
        FROM schedules s
        JOIN classrooms c ON s.classroom_id = c.id
        JOIN courses co ON s.course_id = co.id
        JOIN users u ON s.teacher_id = u.id
        WHERE s.teacher_id = ?
        ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time
    ");
    $stmt->execute([$user_id]);
} else {
    // Admin sees all schedules
    $stmt = $pdo->prepare("
        SELECT s.*, c.name as classroom_name, co.name as course_name, co.code as course_code, u.name as teacher_name
        FROM schedules s
        JOIN classrooms c ON s.classroom_id = c.id
        JOIN courses co ON s.course_id = co.id
        JOIN users u ON s.teacher_id = u.id
        ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time
    ");
    $stmt->execute();
}

$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group schedules by day
$schedules_by_day = [];
foreach ($schedules as $schedule) {
    $schedules_by_day[$schedule['day_of_week']][] = $schedule;
}
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-calendar-alt mr-4 text-primary"></i>
        <?php
        if ($user_role == 'student')
            echo 'Mi Horario de Clases';
        elseif ($user_role == 'teacher')
            echo 'Mis Horarios de Clases';
        else
            echo 'Horarios de Clases';
        ?>
    </h2>

    <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold flex items-center">
                <i class="fas fa-info-circle mr-2 text-primary"></i>
                Información del Horario
            </h3>
            <span class="bg-primary text-white px-3 py-1 rounded-full text-sm font-medium">
                <?php echo count($schedules); ?> clases programadas
            </span>
        </div>
        <div class="grid md:grid-cols-3 gap-4 text-center">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg">
                <div class="text-2xl text-blue-600 mb-2"><i class="fas fa-clock"></i></div>
                <p class="text-sm text-gray-600">Horarios activos</p>
                <p class="text-lg font-bold text-blue-800"><?php echo count($schedules); ?></p>
            </div>
            <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg">
                <div class="text-2xl text-green-600 mb-2"><i class="fas fa-building"></i></div>
                <p class="text-sm text-gray-600">Aulas utilizadas</p>
                <p class="text-lg font-bold text-green-800">
                    <?php
                    $unique_classrooms = array_unique(array_column($schedules, 'classroom_name'));
                    echo count($unique_classrooms);
                    ?>
                </p>
            </div>
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg">
                <div class="text-2xl text-purple-600 mb-2"><i class="fas fa-graduation-cap"></i></div>
                <p class="text-sm text-gray-600">Cursos diferentes</p>
                <p class="text-lg font-bold text-purple-800">
                    <?php
                    $unique_courses = array_unique(array_column($schedules, 'course_name'));
                    echo count($unique_courses);
                    ?>
                </p>
            </div>
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
            <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up card-hover">
                <h4 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-day mr-3 text-primary"></i>
                    <?php echo $day_name; ?>
                    <span class="ml-3 bg-secondary text-white px-3 py-1 rounded-full text-sm font-medium">
                        <?php echo count($day_schedules); ?> clases
                    </span>
                </h4>

                <?php if (empty($day_schedules)): ?>
                    <div class="text-center py-8">
                        <div class="text-6xl text-gray-300 mb-4">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <p class="text-gray-500 text-lg">No hay clases programadas para este día</p>
                        <p class="text-gray-400 text-sm mt-2">¡Disfruta tu tiempo libre!</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($day_schedules as $schedule): ?>
                            <div
                                class="bg-gradient-to-r from-primary to-secondary p-6 rounded-xl text-white shadow-lg transform hover:scale-102 transition duration-300">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                    <div class="flex-1 mb-4 md:mb-0">
                                        <h5 class="text-xl font-bold mb-1"><?php echo htmlspecialchars($schedule['course_name']); ?>
                                        </h5>
                                        <p class="text-white font-semibold text-lg mb-2">
                                            <i class="fas fa-book-reader mr-2"></i>
                                            <?php echo htmlspecialchars($schedule['subject'] ?? 'N/A'); ?>
                                        </p>
                                        <p class="text-accent mb-1 font-medium">
                                            <?php echo htmlspecialchars($schedule['course_code']); ?></p>
                                        <div class="flex flex-wrap items-center text-sm text-accent">
                                            <span class="flex items-center mr-4 mb-1">
                                                <i class="fas fa-user mr-2"></i>
                                                <?php echo htmlspecialchars($schedule['teacher_name']); ?>
                                            </span>
                                            <span class="flex items-center mr-4 mb-1">
                                                <i class="fas fa-map-marker-alt mr-2"></i>
                                                <?php echo htmlspecialchars($schedule['classroom_name']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-3xl font-bold mb-1">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="text-lg font-semibold">
                                            <?php echo date('H:i', strtotime($schedule['start_time'])); ?>
                                        </div>
                                        <div class="text-sm text-accent">
                                            a <?php echo date('H:i', strtotime($schedule['end_time'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>