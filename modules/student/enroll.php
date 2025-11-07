<?php require_once '../../includes/config.php'; ?>
<?php requireRole('student'); ?>
<?php include '../../templates/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];

// Handle enrollment request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enroll_course'])) {
    $course_id = (int)$_POST['course_id'];
    $period = sanitize($_POST['period']) ?: date('Y-1');

    // Check if student is already enrolled
    $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    if ($stmt->fetch()) {
        $error = "Ya estás matriculado en esta mención.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id, period, status) VALUES (?, ?, ?, 'enrolled')");
        $stmt->execute([$user_id, $course_id, $period]);
        $success = "Te has matriculado exitosamente en la mención.";
    }
}

// Fetch available courses (not enrolled yet)
$stmt = $pdo->prepare("
    SELECT c.* FROM courses c
    WHERE c.id NOT IN (
        SELECT course_id FROM enrollments WHERE student_id = ?
    )
    ORDER BY c.name
");
$stmt->execute([$user_id]);
$available_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch current enrollments
$stmt = $pdo->prepare("
    SELECT e.id, e.period, e.status, e.grade,
           c.name, c.code, c.credits, c.description
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.student_id = ?
    ORDER BY c.name
");
$stmt->execute([$user_id]);
$current_enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-graduation-cap mr-4 text-primary"></i>
        Matrícula de Menciones
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

    <!-- Current Enrollments -->
    <?php if (!empty($current_enrollments)): ?>
        <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-book mr-2 text-primary"></i>
                Mis Menciones Matriculadas
            </h3>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($current_enrollments as $enrollment): ?>
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl shadow-lg border-2 border-blue-200">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h4 class="text-lg font-bold text-blue-800 mb-1">
                                    <?php echo htmlspecialchars($enrollment['name']); ?>
                                </h4>
                                <p class="text-blue-600 font-medium">
                                    <?php echo htmlspecialchars($enrollment['code']); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                    <?php echo $enrollment['credits']; ?> créditos
                                </span>
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm mb-4">
                            <?php echo htmlspecialchars(substr($enrollment['description'] ?? 'Sin descripción', 0, 100)); ?>...
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                <span class="font-medium">Periodo:</span> <?php echo htmlspecialchars($enrollment['period']); ?>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                <?php
                                if ($enrollment['status'] == 'enrolled') echo 'bg-green-100 text-green-800';
                                elseif ($enrollment['status'] == 'completed') echo 'bg-blue-100 text-blue-800';
                                elseif ($enrollment['status'] == 'failed') echo 'bg-red-100 text-red-800';
                                else echo 'bg-gray-100 text-gray-800';
                                ?>">
                                <?php echo ucfirst($enrollment['status']); ?>
                            </span>
                        </div>
                        <?php if ($enrollment['grade'] !== null): ?>
                            <div class="mt-3 pt-3 border-t border-blue-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">Calificación:</span>
                                    <span class="font-bold <?php echo $enrollment['grade'] >= 10 ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo $enrollment['grade']; ?>/20
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Available Courses -->
    <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-plus-circle mr-2 text-primary"></i>
            Menciones Disponibles para Matricular
        </h3>

        <?php if (!empty($available_courses)): ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($available_courses as $course): ?>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl shadow-lg border-2 border-green-200 hover:shadow-xl transition duration-300">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h4 class="text-lg font-bold text-green-800 mb-1">
                                    <?php echo htmlspecialchars($course['name']); ?>
                                </h4>
                                <p class="text-green-600 font-medium">
                                    <?php echo htmlspecialchars($course['code']); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                    <?php echo $course['credits']; ?> créditos
                                </span>
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm mb-4">
                            <?php echo htmlspecialchars(substr($course['description'] ?? 'Sin descripción', 0, 100)); ?>...
                        </p>
                        <form method="POST" class="mt-4">
                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                            <input type="hidden" name="period" value="<?php echo date('Y-1'); ?>">
                            <button type="submit" name="enroll_course" class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center justify-center">
                                <i class="fas fa-user-plus mr-2"></i>
                                Matricularme
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-check-circle text-6xl mb-4 text-green-400"></i>
                <h4 class="text-xl font-semibold mb-2">¡Felicitaciones!</h4>
                <p>Ya estás matriculado en todas las menciones disponibles.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>