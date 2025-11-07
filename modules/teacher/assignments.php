<?php require_once '../../includes/config.php'; ?>
<?php requireRole('teacher'); ?>
<?php include '../../templates/header.php'; ?>

<?php
$teacher_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debug output to browser console
    echo "<script>console.log('DEBUG: POST request received:', " . json_encode($_POST) . ");</script>";
    echo "<script>console.log('DEBUG: Teacher ID:', " . json_encode($teacher_id) . ");</script>";

    if (isset($_POST['assign_course'])) {
        $course_id = (int)$_POST['course_id'];
        echo "<script>console.log('DEBUG: Attempting to assign course_id:', $course_id);</script>";

        // Check if course is already assigned to this teacher
        $stmt = $pdo->prepare("SELECT id FROM teacher_courses WHERE teacher_id = ? AND course_id = ? AND status = 'active'");
        $stmt->execute([$teacher_id, $course_id]);
        $existing_count = $stmt->rowCount();
        echo "<script>console.log('DEBUG: Existing assignments for this teacher and course:', $existing_count);</script>";

        if ($existing_count == 0) {
            // Check if course is already assigned to another teacher
            $stmt = $pdo->prepare("SELECT u.name FROM teacher_courses tc JOIN users u ON tc.teacher_id = u.id WHERE tc.course_id = ? AND tc.status = 'active'");
            $stmt->execute([$course_id]);
            $conflict_count = $stmt->rowCount();
            echo "<script>console.log('DEBUG: Conflicts with other teachers:', $conflict_count);</script>";

            if ($conflict_count > 0) {
                $assigned_teacher = $stmt->fetch(PDO::FETCH_ASSOC);
                $error = "Esta mención ya está asignada al docente: " . htmlspecialchars($assigned_teacher['name']);
                echo "<script>console.log('DEBUG: Conflict detected with teacher:', '" . htmlspecialchars($assigned_teacher['name']) . "');</script>";
            } else {
                // Assign course to teacher
                try {
                    $stmt = $pdo->prepare("INSERT INTO teacher_courses (teacher_id, course_id, status) VALUES (?, ?, 'active')");
                    $result = $stmt->execute([$teacher_id, $course_id]);
                    echo "<script>console.log('DEBUG: Insert result:', " . ($result ? 'true' : 'false') . ");</script>";
                    if ($result) {
                        $success = "Mención asignada exitosamente.";
                        echo "<script>console.log('DEBUG: Assignment successful');</script>";
                    } else {
                        $error = "Error al asignar la mención.";
                        echo "<script>console.log('DEBUG: Assignment failed - no result');</script>";
                    }
                } catch (PDOException $e) {
                    echo "<script>console.log('DEBUG: PDO Exception:', '" . $e->getMessage() . "');</script>";
                    $error = "Error de base de datos: " . $e->getMessage();
                }
            }
        } else {
            $error = "Esta mención ya está asignada a usted.";
            echo "<script>console.log('DEBUG: Course already assigned to this teacher');</script>";
        }
    } elseif (isset($_POST['remove_assignment'])) {
        $assignment_id = (int)$_POST['assignment_id'];
        echo "<script>console.log('DEBUG: Attempting to remove assignment_id:', $assignment_id);</script>";

        // Verify the assignment belongs to this teacher
        $stmt = $pdo->prepare("SELECT id FROM teacher_courses WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$assignment_id, $teacher_id]);
        $assignment_exists = $stmt->rowCount();
        echo "<script>console.log('DEBUG: Assignment exists for this teacher:', $assignment_exists);</script>";

        if ($assignment_exists > 0) {
            // Check if there are active schedules for this course and teacher
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE course_id IN (SELECT course_id FROM teacher_courses WHERE id = ?) AND teacher_id = ? AND status = 'active'");
            $stmt->execute([$assignment_id, $teacher_id]);
            $active_schedules = $stmt->fetchColumn();
            echo "<script>console.log('DEBUG: Active schedules for this assignment:', $active_schedules);</script>";

            if ($active_schedules > 0) {
                $error = "No se puede remover la asignación porque hay horarios activos para esta mención.";
                echo "<script>console.log('DEBUG: Cannot remove - active schedules exist');</script>";
            } else {
                // Remove assignment (delete from database)
                try {
                    $stmt = $pdo->prepare("DELETE FROM teacher_courses WHERE id = ? AND teacher_id = ?");
                    $result = $stmt->execute([$assignment_id, $teacher_id]);
                    echo "<script>console.log('DEBUG: Delete result:', " . ($result ? 'true' : 'false') . ");</script>";
                    if ($result) {
                        $success = "Asignación removida exitosamente.";
                        echo "<script>console.log('DEBUG: Deletion successful');</script>";
                    } else {
                        $error = "Error al remover la asignación.";
                        echo "<script>console.log('DEBUG: Deletion failed - no result');</script>";
                    }
                } catch (PDOException $e) {
                    echo "<script>console.log('DEBUG: PDO Exception during deletion:', '" . $e->getMessage() . "');</script>";
                    $error = "Error de base de datos: " . $e->getMessage();
                }
            }
        } else {
            $error = "Asignación no encontrada.";
            echo "<script>console.log('DEBUG: Assignment not found for this teacher');</script>";
        }
    }
}

// Get current assignments
$stmt = $pdo->prepare("
    SELECT tc.id, c.name, c.code, tc.assigned_at
    FROM teacher_courses tc
    JOIN courses c ON tc.course_id = c.id
    WHERE tc.teacher_id = ? AND tc.status = 'active'
    ORDER BY c.name
");
$stmt->execute([$teacher_id]);
$current_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available courses (not assigned to any teacher)
$stmt = $pdo->query("
    SELECT c.id, c.name, c.code, c.credits
    FROM courses c
    WHERE c.id NOT IN (
        SELECT course_id FROM teacher_courses WHERE status = 'active'
    )
    ORDER BY c.name
");
$available_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-user-check mr-4 text-primary"></i>
        Asignación de Menciones
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

    <!-- Current Assignments -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-list-check mr-2 text-primary"></i>
            Mis Asignaciones Actuales (<?php echo count($current_assignments); ?>)
        </h3>

        <?php if (empty($current_assignments)): ?>
            <div class="text-center py-8">
                <div class="text-6xl text-gray-300 mb-4">
                    <i class="fas fa-book-open"></i>
                </div>
                <p class="text-gray-500 text-lg">No tiene menciones asignadas actualmente</p>
                <p class="text-gray-400 text-sm mt-2">Seleccione una mención disponible para asignársela</p>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($current_assignments as $assignment): ?>
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border-l-4 border-primary">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($assignment['name']); ?></h4>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($assignment['code']); ?></p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Asignada: <?php echo date('d/m/Y', strtotime($assignment['assigned_at'])); ?>
                                </p>
                            </div>
                            <form method="POST" class="ml-2" onsubmit="removeAssignment(event, <?php echo $assignment['id']; ?>)">
                                <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                <input type="hidden" name="remove_assignment" value="1">
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200 flex items-center" title="Remover asignación">
                                    <i class="fas fa-times mr-1"></i>
                                    <span class="hidden sm:inline">Remover</span>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Available Courses -->
    <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-plus-circle mr-2 text-primary"></i>
            Menciones Disponibles (<?php echo count($available_courses); ?>)
        </h3>

        <?php if (empty($available_courses)): ?>
            <div class="text-center py-8">
                <div class="text-6xl text-gray-300 mb-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <p class="text-gray-500 text-lg">Todas las menciones están asignadas</p>
                <p class="text-gray-400 text-sm mt-2">No hay menciones disponibles para asignar</p>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($available_courses as $course): ?>
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-lg border-l-4 border-green-500">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($course['name']); ?></h4>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($course['code']); ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?php echo $course['credits']; ?> créditos</p>
                            </div>
                            <form method="POST" class="ml-2" onsubmit="assignCourse(event, <?php echo $course['id']; ?>)">
                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                <input type="hidden" name="assign_course" value="1">
                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200 flex items-center" title="Asignar mención">
                                    <i class="fas fa-plus mr-1"></i>
                                    <span class="hidden sm:inline">Asignar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function assignCourse(event, courseId) {
    event.preventDefault();

    console.log('DEBUG: Starting AJAX assignment for course_id:', courseId);

    // Create FormData from the form
    const form = event.target;
    const formData = new FormData(form);

    // Add the assign_course field
    formData.append('assign_course', '1');

    // Send AJAX request
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('DEBUG: Response status:', response.status);
        return response.text();
    })
    .then(data => {
        console.log('DEBUG: Response received, reloading page...');
        // Reload the page to show updated data
        window.location.reload();
    })
    .catch(error => {
        console.error('DEBUG: AJAX Error:', error);
    });
}

function removeAssignment(event, assignmentId) {
    event.preventDefault();

    if (!confirm('¿Está seguro de remover esta asignación?')) {
        return;
    }

    console.log('DEBUG: Starting AJAX removal for assignment_id:', assignmentId);

    // Create FormData from the form
    const form = event.target;
    const formData = new FormData(form);

    // Add the remove_assignment field
    formData.append('remove_assignment', '1');

    // Send AJAX request
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('DEBUG: Remove response status:', response.status);
        return response.text();
    })
    .then(data => {
        console.log('DEBUG: Remove response received, reloading page...');
        // Reload the page to show updated data
        window.location.reload();
    })
    .catch(error => {
        console.error('DEBUG: Remove AJAX Error:', error);
    });
}
</script>

<?php include '../../templates/footer.php'; ?>