<?php require_once '../../includes/config.php'; ?>
<?php requireRole('teacher'); ?>
<?php include '../../templates/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['mark_attendance'])) {
        $course_id = (int)$_POST['course_id'];
        $date = $_POST['date'];
        $attendance_data = $_POST['attendance'] ?? [];

        // First, get all enrolled students for this course
        $stmt = $pdo->prepare("
            SELECT u.id, u.name
            FROM users u
            JOIN enrollments e ON u.id = e.student_id
            WHERE e.course_id = ? AND e.status = 'enrolled' AND u.role = 'student'
        ");
        $stmt->execute([$course_id]);
        $enrolled_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Insert or update attendance for each student
        foreach ($enrolled_students as $student) {
            $status = $attendance_data[$student['id']] ?? 'absent';
            $stmt = $pdo->prepare("
                INSERT INTO attendance (course_id, student_id, date, status)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE status = VALUES(status)
            ");
            $stmt->execute([$course_id, $student['id'], $date, $status]);
        }
        $success = "Asistencia registrada exitosamente.";
    }
}

// Fetch courses taught by this teacher
$stmt = $pdo->prepare("SELECT DISTINCT c.* FROM courses c JOIN schedules s ON c.id = s.course_id WHERE s.teacher_id = ?");
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected course and date for attendance view
$selected_course = isset($_GET['course']) ? (int)$_GET['course'] : null;
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$students = [];
$existing_attendance = [];

if ($selected_course) {
    // Fetch enrolled students
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.username,
               COALESCE(a.status, 'not_marked') as attendance_status
        FROM users u
        JOIN enrollments e ON u.id = e.student_id
        LEFT JOIN attendance a ON u.id = a.student_id AND a.course_id = ? AND a.date = ?
        WHERE e.course_id = ? AND e.status = 'enrolled' AND u.role = 'student'
        ORDER BY u.name
    ");
    $stmt->execute([$selected_course, $selected_date, $selected_course]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-user-check mr-4 text-primary"></i>
        Control de Asistencia
    </h2>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 animate-fade-in-up">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- Course and Date Selection -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-calendar-check mr-2 text-primary"></i>
            Seleccionar Curso y Fecha
        </h3>
        <form method="GET" class="grid md:grid-cols-3 gap-6">
            <div>
                <label for="course" class="block text-sm font-medium text-gray-700 mb-2">Curso</label>
                <select id="course" name="course" onchange="this.form.submit()" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    <option value="">Seleccionar curso</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo $selected_course == $course['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['name']); ?> (<?php echo htmlspecialchars($course['code']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                <input type="date" id="date" name="date" value="<?php echo $selected_date; ?>" onchange="this.form.submit()" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-primary hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300 transform hover:scale-105 flex items-center">
                    <i class="fas fa-search mr-2"></i>
                    Cargar Asistencia
                </button>
            </div>
        </form>
    </div>

    <?php if ($selected_course && !empty($students)): ?>
        <!-- Attendance Form -->
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold flex items-center">
                    <i class="fas fa-clipboard-list mr-2 text-primary"></i>
                    Lista de Asistencia
                </h3>
                <div class="text-sm text-gray-600">
                    <i class="fas fa-calendar mr-1"></i>
                    <?php echo date('d/m/Y', strtotime($selected_date)); ?> -
                    <i class="fas fa-users mr-1 ml-2"></i>
                    <?php echo count($students); ?> estudiantes
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="course_id" value="<?php echo $selected_course; ?>">
                <input type="hidden" name="date" value="<?php echo $selected_date; ?>">

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estudiante</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Usuario</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600">Presente</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600">Ausente</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600">Tarde</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600">Justificado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($students as $student): ?>
                                <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($student['name']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php echo htmlspecialchars($student['username']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present"
                                               <?php echo $student['attendance_status'] == 'present' ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-green-600 focus:ring-green-500">
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent"
                                               <?php echo $student['attendance_status'] == 'absent' ? 'checked' : ($student['attendance_status'] == 'not_marked' ? 'checked' : ''); ?>
                                               class="w-4 h-4 text-red-600 focus:ring-red-500">
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="late"
                                               <?php echo $student['attendance_status'] == 'late' ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-yellow-600 focus:ring-yellow-500">
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="excused"
                                               <?php echo $student['attendance_status'] == 'excused' ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <div class="flex items-center space-x-6">
                            <span class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                Presente
                            </span>
                            <span class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                Ausente
                            </span>
                            <span class="flex items-center">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                                Tarde
                            </span>
                            <span class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                Justificado
                            </span>
                        </div>
                    </div>
                    <button type="submit" name="mark_attendance" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-8 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Guardar Asistencia
                    </button>
                </div>
            </form>
        </div>

        <!-- Attendance Summary -->
        <div class="bg-white p-6 rounded-2xl shadow-xl mt-8 animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-chart-bar mr-2 text-primary"></i>
                Resumen de Asistencia
            </h3>
            <div class="grid md:grid-cols-4 gap-6">
                <?php
                $present = count(array_filter($students, function($s) { return $s['attendance_status'] == 'present'; }));
                $absent = count(array_filter($students, function($s) { return $s['attendance_status'] == 'absent'; }));
                $late = count(array_filter($students, function($s) { return $s['attendance_status'] == 'late'; }));
                $excused = count(array_filter($students, function($s) { return $s['attendance_status'] == 'excused'; }));
                $not_marked = count(array_filter($students, function($s) { return $s['attendance_status'] == 'not_marked'; }));
                ?>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 mb-2"><?php echo $present; ?></div>
                    <div class="text-sm text-gray-600">Presentes</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo count($students) > 0 ? ($present / count($students)) * 100 : 0; ?>%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-600 mb-2"><?php echo $absent; ?></div>
                    <div class="text-sm text-gray-600">Ausentes</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-red-600 h-2 rounded-full" style="width: <?php echo count($students) > 0 ? ($absent / count($students)) * 100 : 0; ?>%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-600 mb-2"><?php echo $late; ?></div>
                    <div class="text-sm text-gray-600">Tarde</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-yellow-600 h-2 rounded-full" style="width: <?php echo count($students) > 0 ? ($late / count($students)) * 100 : 0; ?>%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo $not_marked; ?></div>
                    <div class="text-sm text-gray-600">Sin marcar</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-gray-600 h-2 rounded-full" style="width: <?php echo count($students) > 0 ? ($not_marked / count($students)) * 100 : 0; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($selected_course): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded animate-fade-in-up">
            No hay estudiantes matriculados en este curso.
        </div>
    <?php endif; ?>
</main>

<?php include '../../templates/footer.php'; ?>