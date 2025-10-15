<?php require_once '../../includes/config.php'; ?>
<?php requireRole('teacher'); ?>
<?php include '../../templates/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];

// Fetch courses taught by this teacher
$stmt = $pdo->prepare("SELECT DISTINCT c.* FROM courses c JOIN schedules s ON c.id = s.course_id WHERE s.teacher_id = ?");
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selected_course = isset($_GET['course']) ? (int)$_GET['course'] : null;
$students = [];

if ($selected_course) {
    // Fetch enrolled students for the selected course
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, e.grade, e.status
        FROM users u
        JOIN enrollments e ON u.id = e.student_id
        WHERE e.course_id = ? AND u.role = 'student'
        ORDER BY u.name
    ");
    $stmt->execute([$selected_course]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle grade updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_grades'])) {
    foreach ($_POST['grades'] as $student_id => $grade) {
        $grade = !empty($grade) ? (float)$grade : null;
        if ($grade !== null && ($grade < 0 || $grade > 20)) continue; // Validate grade

        $stmt = $pdo->prepare("UPDATE enrollments SET grade = ? WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$grade, $student_id, $selected_course]);
    }
    $success = "Notas actualizadas exitosamente.";
}
?>

<main class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Gestión de Notas</h2>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-xl font-semibold mb-4">Seleccionar Curso</h3>
        <form method="GET" class="mb-4">
            <select name="course" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                <option value="">Seleccione un curso</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['id']; ?>" <?php echo $selected_course == $course['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['name']); ?> (<?php echo htmlspecialchars($course['code']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($selected_course && $students): ?>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">Lista de Estudiantes</h3>
            <form method="POST">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left">Nombre del Estudiante</th>
                                <th class="px-4 py-2 text-left">Nota Actual</th>
                                <th class="px-4 py-2 text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr class="border-t">
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td class="px-4 py-2">
                                        <input type="number" name="grades[<?php echo $student['id']; ?>]" value="<?php echo $student['grade'] ?? ''; ?>" min="0" max="20" step="0.1" class="w-20 px-2 py-1 border border-gray-300 rounded">
                                    </td>
                                    <td class="px-4 py-2"><?php echo ucfirst($student['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <button type="submit" name="update_grades" class="bg-primary hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition duration-300">Actualizar Notas</button>
                </div>
            </form>
        </div>
    <?php elseif ($selected_course): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
            No hay estudiantes inscritos en este curso.
        </div>
    <?php endif; ?>
</main>

<?php include '../../templates/footer.php'; ?>