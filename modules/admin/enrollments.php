<?php require_once '../../includes/config.php'; ?>
<?php requireRole('admin'); ?>
<?php include '../../templates/header.php'; ?>

<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['enroll_student'])) {
        $student_id = (int)$_POST['student_id'];
        $course_id = (int)$_POST['course_id'];
        $period = sanitize($_POST['period']) ?: date('Y-1');

        // Check if student is already enrolled
        $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$student_id, $course_id]);
        if ($stmt->fetch()) {
            $error = "El estudiante ya está matriculado en este curso.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id, period, status) VALUES (?, ?, ?, 'enrolled')");
            $stmt->execute([$student_id, $course_id, $period]);
            $success = "Estudiante matriculado exitosamente.";
        }
    } elseif (isset($_POST['unenroll_student'])) {
        $enrollment_id = (int)$_POST['enrollment_id'];
        $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ?");
        $stmt->execute([$enrollment_id]);
        $success = "Estudiante desmatriculado exitosamente.";
    }
}

// Fetch all enrollments with student and course info
$stmt = $pdo->query("
    SELECT e.id, e.period, e.status, e.grade,
           u.name as student_name, u.username as student_username,
           c.name as course_name, c.code as course_code
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY c.name, u.name
");
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch students and courses for enrollment form
$students = $pdo->query("SELECT id, name, username FROM users WHERE role = 'student' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$courses = $pdo->query("SELECT id, name, code FROM courses ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-user-plus mr-4 text-primary"></i>
        Gestión de Matrículas
    </h2>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 animate-fade-in-up">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 animate-fade-in-up">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Enrollment Form -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-plus-circle mr-2 text-primary"></i>
            Matricular Estudiante
        </h3>
        <form method="POST" class="grid md:grid-cols-4 gap-6">
            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Estudiante</label>
                <select id="student_id" name="student_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    <option value="">Seleccionar estudiante</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>">
                            <?php echo htmlspecialchars($student['name']); ?> (<?php echo htmlspecialchars($student['username']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">Curso</label>
                <select id="course_id" name="course_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    <option value="">Seleccionar curso</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>">
                            <?php echo htmlspecialchars($course['name']); ?> (<?php echo htmlspecialchars($course['code']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="period" class="block text-sm font-medium text-gray-700 mb-2">Periodo</label>
                <input type="text" id="period" name="period" value="<?php echo date('Y-1'); ?>" placeholder="2025-1" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
            </div>
            <div class="flex items-end">
                <button type="submit" name="enroll_student" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-8 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                    <i class="fas fa-user-plus mr-2"></i>
                    Matricular
                </button>
            </div>
        </form>
    </div>

    <!-- Enrollments List -->
    <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-list mr-2 text-primary"></i>
            Lista de Matrículas
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estudiante</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Usuario</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Curso</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Periodo</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estado</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Calificación</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($enrollments as $enrollment): ?>
                        <tr class="hover:bg-gray-50 transition duration-200">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($enrollment['student_name']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php echo htmlspecialchars($enrollment['student_username']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo htmlspecialchars($enrollment['course_name']); ?> (<?php echo htmlspecialchars($enrollment['course_code']); ?>)
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php echo htmlspecialchars($enrollment['period']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    <?php
                                    if ($enrollment['status'] == 'enrolled') echo 'bg-green-100 text-green-800';
                                    elseif ($enrollment['status'] == 'completed') echo 'bg-blue-100 text-blue-800';
                                    elseif ($enrollment['status'] == 'failed') echo 'bg-red-100 text-red-800';
                                    else echo 'bg-gray-100 text-gray-800';
                                    ?>">
                                    <?php echo ucfirst($enrollment['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php if ($enrollment['grade'] !== null): ?>
                                    <span class="font-bold <?php echo $enrollment['grade'] >= 10 ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo $enrollment['grade']; ?>/20
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-500 italic">Sin calificar</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <form method="POST" class="inline" onsubmit="return confirm('¿Está seguro de desmatricular a este estudiante?')">
                                    <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['id']; ?>">
                                    <button type="submit" name="unenroll_student" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200 flex items-center" title="Desmatricular">
                                        <i class="fas fa-user-minus mr-1"></i>
                                        <span class="hidden sm:inline">Desmatricular</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($enrollments)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-users text-4xl mb-4"></i>
                <p>No hay matrículas registradas.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>