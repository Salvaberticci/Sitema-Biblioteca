<?php require_once '../../includes/config.php'; ?>
<?php requireRole('teacher'); ?>
<?php include '../../templates/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_activity'])) {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $course_id = (int) $_POST['course_id'];
        $due_date = $_POST['due_date'];

        // Handle file upload
        $file_path = '';
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $upload_dir = '../../uploads/activities/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = time() . '_' . basename($_FILES['file']['name']);
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                $file_path = 'uploads/activities/' . $file_name;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO activities (title, description, course_id, teacher_id, due_date, file_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $course_id, $user_id, $due_date, $file_path]);
        $success = "Actividad creada exitosamente.";
    } elseif (isset($_POST['grade_submission'])) {
        $submission_id = (int) $_POST['submission_id'];
        $grade = !empty($_POST['grade']) ? (float) $_POST['grade'] : null;
        $comments = sanitize($_POST['comments']);

        $stmt = $pdo->prepare("UPDATE submissions SET grade = ?, comments = ? WHERE id = ?");
        $stmt->execute([$grade, $comments, $submission_id]);
        $success = "Calificación guardada exitosamente.";
    } elseif (isset($_POST['delete_activity']) || isset($_POST['submit_delete_activity'])) {
        // CSRF check
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $error = "Error de seguridad. Intente nuevamente.";
        } else {
            $activity_id = (int) $_POST['activity_id'];

            // Ensure the activity belongs to the teacher
            $check_stmt = $pdo->prepare("SELECT id FROM activities WHERE id = ? AND teacher_id = ?");
            $check_stmt->execute([$activity_id, $user_id]);

            if ($check_stmt->fetch()) {
                $pdo->beginTransaction();
                try {
                    // Delete submissions first
                    $pdo->prepare("DELETE FROM submissions WHERE activity_id = ?")->execute([$activity_id]);
                    $pdo->prepare("DELETE FROM activities WHERE id = ?")->execute([$activity_id]);
                    $pdo->commit();
                    $success = "Actividad eliminada permanentemente junto con sus notas y entregas.";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = "Error al eliminar la actividad: " . $e->getMessage();
                }
            } else {
                $error = "No tienes permiso para eliminar esta actividad.";
            }
        }
    }
}

// Fetch courses taught by this teacher
$stmt = $pdo->prepare("SELECT DISTINCT c.* FROM courses c JOIN schedules s ON c.id = s.course_id WHERE s.teacher_id = ?");
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filtering logic
$filter_course = isset($_GET['filter_course']) ? (int) $_GET['filter_course'] : null;
$filter_month = isset($_GET['filter_month']) ? sanitize($_GET['filter_month']) : null;

// Fetch activities created by this teacher
$activities_query = "
    SELECT a.*, c.name as course_name, c.code as course_code,
           COUNT(s.id) as submission_count
    FROM activities a
    JOIN courses c ON a.course_id = c.id
    LEFT JOIN submissions s ON a.id = s.activity_id
    WHERE a.teacher_id = ?
";

if ($filter_course) {
    $activities_query .= " AND a.course_id = $filter_course";
}

if ($filter_month) {
    // filter_month is YYYY-MM
    $activities_query .= " AND a.due_date LIKE '$filter_month%'";
}

$activities_query .= " GROUP BY a.id ORDER BY a.due_date DESC";

$stmt = $pdo->prepare($activities_query);
$stmt->execute([$user_id]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected activity for detailed view
$selected_activity = null;
$submissions = [];
if (isset($_GET['activity_id'])) {
    $activity_id = (int) $_GET['activity_id'];
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as course_name, c.code as course_code
        FROM activities a
        JOIN courses c ON a.course_id = c.id
        WHERE a.id = ? AND a.teacher_id = ?
    ");
    $stmt->execute([$activity_id, $user_id]);
    $selected_activity = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selected_activity) {
        $stmt = $pdo->prepare("
            SELECT s.*, u.name as student_name, u.username
            FROM submissions s
            JOIN users u ON s.student_id = u.id
            WHERE s.activity_id = ?
            ORDER BY s.submitted_at DESC
        ");
        $stmt->execute([$activity_id]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-tasks mr-4 text-primary"></i>
        Gestión de Actividades
    </h2>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 animate-fade-in-up">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if (!$selected_activity): ?>
        <!-- Create Activity Form -->
        <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-4 flex items-center">
                <i class="fas fa-plus-circle mr-2 text-primary"></i>
                Crear Nueva Actividad
            </h3>
            <form method="POST" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-6">
                <input type="hidden" name="create_activity" value="1">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Título de la Actividad</label>
                    <input type="text" id="title" name="title" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
                <div>
                    <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">Mención</label>
                    <select id="course_id" name="course_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        <option value="">Seleccionar mención</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?>
                                (<?php echo htmlspecialchars($course['code']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"></textarea>
                </div>
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Entrega</label>
                    <input type="datetime-local" id="due_date" name="due_date" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Archivo Adjunto
                        (Opcional)</label>
                    <input type="file" id="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    <p class="text-sm text-gray-500 mt-1">Formatos permitidos: PDF, DOC, PPT, imágenes</p>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" name="create_activity"
                        class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Crear Actividad
                    </button>
                </div>
            </form>
        </div>

        <!-- Activities List -->
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6 gap-4">
                <h3 class="text-xl font-semibold flex items-center">
                    <i class="fas fa-list mr-2 text-primary"></i>
                    Mis Actividades (<?php echo count($activities); ?>)
                </h3>
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center">
                        <label for="filter_month" class="mr-3 text-sm font-medium text-gray-700">Mes/Año:</label>
                        <input type="month" id="filter_month" value="<?php echo $filter_month; ?>"
                            onchange="updateFilters()"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    </div>
                    <div class="flex items-center">
                        <label for="filter_course" class="mr-3 text-sm font-medium text-gray-700">Mención:</label>
                        <select id="filter_course" onchange="updateFilters()"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                            <option value="">Todas las menciones</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>" <?php echo $filter_course == $course['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <a href="activities.php" class="text-sm text-primary hover:underline">Limpiar Filtros</a>
                </div>
            </div>

            <script>
                function updateFilters() {
                    const course = document.getElementById('filter_course').value;
                    const month = document.getElementById('filter_month').value;
                    let url = 'activities.php?';
                    if (course) url += 'filter_course=' + course + '&';
                    if (month) url += 'filter_month=' + month;
                    window.location.href = url;
                }
            </script>
            <div class="space-y-4">
                <?php foreach ($activities as $activity): ?>
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition duration-300">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-800 mb-2">
                                    <?php echo htmlspecialchars($activity['title']); ?>
                                </h4>
                                <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($activity['course_name']); ?>
                                    (<?php echo htmlspecialchars($activity['course_code']); ?>)</p>
                                <div class="flex items-center text-sm text-gray-500 mb-2">
                                    <i class="fas fa-calendar mr-2"></i>
                                    <span>Entrega: <?php echo date('d/m/Y H:i', strtotime($activity['due_date'])); ?></span>
                                    <span class="mx-4">•</span>
                                    <i class="fas fa-users mr-2"></i>
                                    <span><?php echo $activity['submission_count']; ?> entregas</span>
                                </div>
                                <p class="text-gray-700">
                                    <?php echo htmlspecialchars(substr($activity['description'], 0, 100)); ?>...
                                </p>
                            </div>
                            <div class="ml-4 flex flex-col gap-2">
                                <a href="?activity_id=<?php echo $activity['id']; ?>"
                                    class="bg-primary hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-eye mr-2"></i>
                                    Ver Detalles
                                </a>
                                <form method="POST"
                                    onsubmit="return confirm('¡ADVERTENCIA! Esta acción eliminará permanentemente la actividad, todas las entregas de los estudiantes y sus notas. Se recomienda tener un registro externo de las notas antes de proceder. ¿Está seguro de que desea eliminar esta actividad?')">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="activity_id" value="<?php echo $activity['id']; ?>">
                                    <input type="hidden" name="submit_delete_activity" value="1">
                                    <button type="submit" name="delete_activity"
                                        class="w-full bg-red-100 hover:bg-red-200 text-red-600 font-bold py-2 px-4 rounded transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-trash mr-2"></i>
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Activity Detail View -->
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up mb-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">
                        <?php echo htmlspecialchars($selected_activity['title']); ?>
                    </h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($selected_activity['course_name']); ?>
                        (<?php echo htmlspecialchars($selected_activity['course_code']); ?>)</p>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-calendar mr-1"></i>
                        Fecha límite: <?php echo date('d/m/Y H:i', strtotime($selected_activity['due_date'])); ?>
                    </p>
                </div>
                <a href="activities.php"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-200 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>

            <div class="mb-6">
                <h4 class="text-lg font-semibold mb-2">Descripción</h4>
                <p class="text-gray-700 bg-gray-50 p-4 rounded-lg">
                    <?php echo nl2br(htmlspecialchars($selected_activity['description'])); ?>
                </p>
            </div>

            <?php if ($selected_activity['file_path']): ?>
                <div class="mb-6">
                    <h4 class="text-lg font-semibold mb-2">Archivo Adjunto</h4>
                    <a href="/biblioteca/<?php echo htmlspecialchars($selected_activity['file_path']); ?>" target="_blank"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                        <i class="fas fa-download mr-2"></i>
                        Descargar Archivo
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Submissions List -->
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-paper-plane mr-2 text-primary"></i>
                Entregas de Estudiantes (<?php echo count($submissions); ?>)
            </h3>

            <?php if (empty($submissions)): ?>
                <div class="text-center py-8">
                    <div class="text-6xl text-gray-300 mb-4">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <p class="text-gray-500 text-lg">Aún no hay entregas para esta actividad</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($submissions as $submission): ?>
                        <div class="border border-gray-200 rounded-lg p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($submission['student_name']); ?>
                                    </h4>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($submission['username']); ?></p>
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>
                                        Entregado: <?php echo date('d/m/Y H:i', strtotime($submission['submitted_at'])); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <?php if ($submission['grade'] !== null): ?>
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                            Calificado: <?php echo $submission['grade']; ?>/20
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                                            Pendiente de calificar
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($submission['file_path']): ?>
                                <div class="mb-4">
                                    <a href="/biblioteca/<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm inline-flex items-center">
                                        <i class="fas fa-download mr-2"></i>
                                        Descargar Entrega
                                    </a>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="bg-gray-50 p-4 rounded-lg">
                                <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                <input type="hidden" name="grade_submission" value="1">
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Calificación (0-20)</label>
                                        <input type="number" name="grade" min="0" max="20" step="0.1"
                                            value="<?php echo $submission['grade'] ?? ''; ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Comentarios</label>
                                        <textarea name="comments" rows="2"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo htmlspecialchars($submission['comments'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" name="grade_submission"
                                        class="bg-primary hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition duration-200">
                                        <i class="fas fa-save mr-2"></i>
                                        Guardar Calificación
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<?php include '../../templates/footer.php'; ?>