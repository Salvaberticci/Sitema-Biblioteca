<?php require_once '../../includes/config.php'; ?>
<?php requireRole('teacher'); ?>
<?php include '../../templates/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_grades'])) {
        $course_id = (int)$_POST['course_id'];
        $grades_data = $_POST['grades'] ?? [];

        foreach ($grades_data as $student_id => $grade) {
            $grade = !empty($grade) ? (float)$grade : null;
            $stmt = $pdo->prepare("
                UPDATE enrollments
                SET grade = ?
                WHERE student_id = ? AND course_id = ?
            ");
            $stmt->execute([$grade, $student_id, $course_id]);
        }
        $success = "Calificaciones actualizadas exitosamente.";
    }
}

// Fetch courses taught by this teacher
$stmt = $pdo->prepare("SELECT DISTINCT c.* FROM courses c JOIN schedules s ON c.id = s.course_id WHERE s.teacher_id = ?");
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected course for grades view
$selected_course = isset($_GET['course']) ? (int)$_GET['course'] : null;
$students = [];

if ($selected_course) {
    // Fetch enrolled students with their current grades
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.username, e.grade, e.status
        FROM users u
        JOIN enrollments e ON u.id = e.student_id
        WHERE e.course_id = ? AND e.status = 'enrolled' AND u.role = 'student'
        ORDER BY u.name
    ");
    $stmt->execute([$selected_course]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-graduation-cap mr-4 text-primary"></i>
        Gestión de Calificaciones
    </h2>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 animate-fade-in-up">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- Course Selection -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-book mr-2 text-primary"></i>
            Seleccionar Curso
        </h3>
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <label for="course" class="block text-sm font-medium text-gray-700 mb-2">Mención</label>
                <select id="course" name="course" onchange="this.form.submit()" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    <option value="">Seleccionar mención</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo $selected_course == $course['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['name']); ?> (<?php echo htmlspecialchars($course['code']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <?php if ($selected_course && !empty($students)): ?>
        <!-- Grades Form -->
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold flex items-center">
                    <i class="fas fa-edit mr-2 text-primary"></i>
                    Calificaciones de la Mención
                </h3>
                <div class="text-sm text-gray-600">
                    <i class="fas fa-users mr-1"></i>
                    <?php echo count($students); ?> estudiantes
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="course_id" value="<?php echo $selected_course; ?>">

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estudiante</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Usuario</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Calificación Actual</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Nueva Calificación (0-20)</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estado</th>
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
                                    <td class="px-6 py-4 text-sm">
                                        <?php if ($student['grade'] !== null): ?>
                                            <span class="font-bold <?php echo $student['grade'] >= 10 ? 'text-green-600' : 'text-red-600'; ?>">
                                                <?php echo $student['grade']; ?>/20
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-500 italic">Sin calificar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <input type="number" name="grades[<?php echo $student['id']; ?>]"
                                               min="0" max="20" step="0.1"
                                               value="<?php echo $student['grade'] ?? ''; ?>"
                                               class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                            <?php
                                            if ($student['grade'] >= 10) echo 'bg-green-100 text-green-800';
                                            elseif ($student['grade'] !== null) echo 'bg-red-100 text-red-800';
                                            else echo 'bg-yellow-100 text-yellow-800';
                                            ?>">
                                            <?php
                                            if ($student['grade'] >= 10) echo 'Aprobado';
                                            elseif ($student['grade'] !== null) echo 'Reprobado';
                                            else echo 'Pendiente';
                                            ?>
                                        </span>
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
                                Aprobado (≥10)
                            </span>
                            <span class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                Reprobado (<10)
                            </span>
                            <span class="flex items-center">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                                Pendiente
                            </span>
                        </div>
                    </div>
                    <button type="submit" name="update_grades" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-8 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Guardar Calificaciones
                    </button>
                </div>
            </form>
        </div>

        <!-- Grade Statistics -->
        <div class="bg-white p-6 rounded-2xl shadow-xl mt-8 animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-chart-bar mr-2 text-primary"></i>
                Estadísticas de Calificaciones
            </h3>
            <div class="grid md:grid-cols-4 gap-6">
                <?php
                $graded = count(array_filter($students, function($s) { return $s['grade'] !== null; }));
                $approved = count(array_filter($students, function($s) { return $s['grade'] >= 10; }));
                $failed = count(array_filter($students, function($s) { return $s['grade'] < 10 && $s['grade'] !== null; }));
                $pending = count($students) - $graded;
                $avg_grade = 0;
                if ($graded > 0) {
                    $grades_sum = array_sum(array_map(function($s) { return $s['grade'] ?? 0; }, $students));
                    $avg_grade = round($grades_sum / $graded, 1);
                }
                ?>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo $graded; ?>/<?php echo count($students); ?></div>
                    <div class="text-sm text-gray-600">Calificados</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo count($students) > 0 ? ($graded / count($students)) * 100 : 0; ?>%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 mb-2"><?php echo $approved; ?></div>
                    <div class="text-sm text-gray-600">Aprobados</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo count($students) > 0 ? ($approved / count($students)) * 100 : 0; ?>%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-600 mb-2"><?php echo $failed; ?></div>
                    <div class="text-sm text-gray-600">Reprobados</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-red-600 h-2 rounded-full" style="width: <?php echo count($students) > 0 ? ($failed / count($students)) * 100 : 0; ?>%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary mb-2"><?php echo $avg_grade; ?>/20</div>
                    <div class="text-sm text-gray-600">Promedio</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-primary h-2 rounded-full" style="width: <?php echo ($avg_grade / 20) * 100; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($selected_course): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded animate-fade-in-up">
            No hay estudiantes matriculados en esta mención.
        </div>
    <?php endif; ?>
</main>

<?php include '../../templates/footer.php'; ?>