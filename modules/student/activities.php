<?php require_once '../../includes/config.php'; ?>
<?php requireRole('student'); ?>
<?php include '../../templates/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_activity'])) {
    $activity_id = (int)$_POST['activity_id'];

    // Handle file upload
    $file_path = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $upload_dir = '../../uploads/submissions/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . '_' . $user_id . '_' . basename($_FILES['file']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            $file_path = 'uploads/submissions/' . $file_name;
        } else {
            $error = "Error al subir el archivo.";
        }
    }

    if (!isset($error)) {
        // Check if student already submitted
        $stmt = $pdo->prepare("SELECT id FROM submissions WHERE activity_id = ? AND student_id = ?");
        $stmt->execute([$activity_id, $user_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update existing submission
            $stmt = $pdo->prepare("UPDATE submissions SET file_path = ?, submitted_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$file_path, $existing['id']]);
        } else {
            // Create new submission
            $stmt = $pdo->prepare("INSERT INTO submissions (activity_id, student_id, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$activity_id, $user_id, $file_path]);
        }
        $success = "Actividad enviada exitosamente.";
    }
}

// Fetch activities for enrolled courses
$stmt = $pdo->prepare("
    SELECT a.*, c.name as course_name, c.code as course_code, u.name as teacher_name,
           s.id as submission_id, s.grade, s.comments, s.submitted_at, s.file_path as submission_file
    FROM activities a
    JOIN courses c ON a.course_id = c.id
    JOIN users u ON a.teacher_id = u.id
    JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN submissions s ON a.id = s.activity_id AND s.student_id = ?
    WHERE e.student_id = ? AND e.status = 'enrolled'
    ORDER BY a.due_date ASC
");
$stmt->execute([$user_id, $user_id]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate pending and completed activities
$pending_activities = [];
$completed_activities = [];

foreach ($activities as $activity) {
    if ($activity['submission_id']) {
        $completed_activities[] = $activity;
    } else {
        $pending_activities[] = $activity;
    }
}
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-tasks mr-4 text-primary"></i>
        Mis Actividades
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

    <!-- Summary Cards -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Actividades Pendientes</p>
                    <p class="text-3xl font-bold text-orange-500"><?php echo count($pending_activities); ?></p>
                </div>
                <div class="text-4xl text-orange-500 opacity-70">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.1s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Actividades Enviadas</p>
                    <p class="text-3xl font-bold text-blue-500"><?php echo count($completed_activities); ?></p>
                </div>
                <div class="text-4xl text-blue-500 opacity-70">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.2s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Actividades Calificadas</p>
                    <p class="text-3xl font-bold text-green-500">
                        <?php echo count(array_filter($completed_activities, function($a) { return $a['grade'] !== null; })); ?>
                    </p>
                </div>
                <div class="text-4xl text-green-500 opacity-70">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Activities -->
    <?php if (!empty($pending_activities)): ?>
        <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-exclamation-triangle mr-2 text-orange-500"></i>
                Actividades Pendientes (<?php echo count($pending_activities); ?>)
            </h3>
            <div class="space-y-6">
                <?php foreach ($pending_activities as $activity): ?>
                    <div class="border-l-4 border-orange-500 bg-orange-50 p-6 rounded-r-lg">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($activity['title']); ?></h4>
                                <p class="text-gray-600 mb-2">
                                    <i class="fas fa-graduation-cap mr-1"></i>
                                    <?php echo htmlspecialchars($activity['course_name']); ?> (<?php echo htmlspecialchars($activity['course_code']); ?>)
                                </p>
                                <p class="text-gray-600 mb-2">
                                    <i class="fas fa-user mr-1"></i>
                                    Docente: <?php echo htmlspecialchars($activity['teacher_name']); ?>
                                </p>
                                <div class="flex items-center text-sm text-gray-500 mb-3">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    <span>Fecha límite: <?php echo date('d/m/Y H:i', strtotime($activity['due_date'])); ?></span>
                                    <?php
                                    $now = new DateTime();
                                    $due_date = new DateTime($activity['due_date']);
                                    $interval = $now->diff($due_date);
                                    $days_left = $interval->days;

                                    if ($now > $due_date) {
                                        echo '<span class="ml-4 text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>Vencida</span>';
                                    } elseif ($days_left <= 1) {
                                        echo '<span class="ml-4 text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>¡Último día!</span>';
                                    } elseif ($days_left <= 3) {
                                        echo '<span class="ml-4 text-orange-600 font-medium"><i class="fas fa-clock mr-1"></i>' . $days_left . ' días restantes</span>';
                                    } else {
                                        echo '<span class="ml-4 text-green-600 font-medium"><i class="fas fa-clock mr-1"></i>' . $days_left . ' días restantes</span>';
                                    }
                                    ?>
                                </div>
                                <p class="text-gray-700"><?php echo htmlspecialchars(substr($activity['description'], 0, 150)); ?>...</p>
                            </div>
                        </div>

                        <?php if ($activity['file_path']): ?>
                            <div class="mb-4">
                                <a href="/biblioteca/<?php echo htmlspecialchars($activity['file_path']); ?>" target="_blank" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm inline-flex items-center">
                                    <i class="fas fa-download mr-2"></i>
                                    Descargar Archivo de la Actividad
                                </a>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded-lg border">
                            <input type="hidden" name="activity_id" value="<?php echo $activity['id']; ?>">
                            <input type="hidden" name="submit_activity" value="1">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subir tu entrega</label>
                                <input type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png,.zip" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                                <p class="text-sm text-gray-500 mt-1">Formatos permitidos: PDF, DOC, PPT, imágenes, ZIP</p>
                            </div>
                            <button type="submit" name="submit_activity" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Enviar Actividad
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Completed Activities -->
    <?php if (!empty($completed_activities)): ?>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-check-circle mr-2 text-green-500"></i>
                Actividades Enviadas (<?php echo count($completed_activities); ?>)
            </h3>
            <div class="space-y-4">
                <?php foreach ($completed_activities as $activity): ?>
                    <div class="border border-gray-200 rounded-lg p-6 <?php echo $activity['grade'] !== null ? 'bg-green-50 border-green-200' : 'bg-blue-50 border-blue-200'; ?>">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($activity['title']); ?></h4>
                                <p class="text-gray-600 mb-2">
                                    <i class="fas fa-graduation-cap mr-1"></i>
                                    <?php echo htmlspecialchars($activity['course_name']); ?> (<?php echo htmlspecialchars($activity['course_code']); ?>)
                                </p>
                                <div class="flex items-center text-sm text-gray-500 mb-2">
                                    <i class="fas fa-calendar-check mr-2"></i>
                                    <span>Enviado: <?php echo date('d/m/Y H:i', strtotime($activity['submitted_at'])); ?></span>
                                    <span class="mx-4">•</span>
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    <span>Límite: <?php echo date('d/m/Y H:i', strtotime($activity['due_date'])); ?></span>
                                </div>
                            </div>
                            <div class="text-right">
                                <?php if ($activity['grade'] !== null): ?>
                                    <div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg font-bold text-lg">
                                        <?php echo $activity['grade']; ?>/20
                                    </div>
                                <?php else: ?>
                                    <div class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-lg font-medium">
                                        Pendiente de calificar
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($activity['comments']): ?>
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <h5 class="font-semibold text-gray-800 mb-2">Comentarios del docente:</h5>
                                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($activity['comments'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($activity['submission_file']): ?>
                            <div>
                                <a href="/biblioteca/<?php echo htmlspecialchars($activity['submission_file']); ?>" target="_blank" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm inline-flex items-center">
                                    <i class="fas fa-download mr-2"></i>
                                    Ver mi entrega
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($pending_activities) && empty($completed_activities)): ?>
        <div class="bg-white p-12 rounded-2xl shadow-xl text-center animate-fade-in-up">
            <div class="text-6xl text-gray-300 mb-4">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-4">No hay actividades disponibles</h3>
            <p class="text-gray-600">En este momento no tienes actividades asignadas en tus menciones matriculadas.</p>
        </div>
    <?php endif; ?>
</main>

<?php include '../../templates/footer.php'; ?>