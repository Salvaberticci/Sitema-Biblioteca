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

        // First, get all enrolled students for this course (using DISTINCT to avoid duplicates)
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.id, u.name
            FROM users u
            JOIN enrollments e ON u.id = e.student_id
            WHERE e.course_id = ? AND e.status = 'enrolled' AND u.role = 'student'
        ");
        $stmt->execute([$course_id]);
        $enrolled_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Start transaction for data integrity
        $pdo->beginTransaction();
        try {
            // Insert or update attendance for each student
            foreach ($enrolled_students as $student) {
                $status = $attendance_data[$student['id']] ?? 'absent';
                
                // First, delete any existing record for this student on this date to ensure uniqueness
                $deleteStmt = $pdo->prepare("DELETE FROM attendance WHERE course_id = ? AND student_id = ? AND date = ?");
                $deleteStmt->execute([$course_id, $student['id'], $date]);
                
                // Then insert the new status
                $insertStmt = $pdo->prepare("INSERT INTO attendance (course_id, student_id, date, status) VALUES (?, ?, ?, ?)");
                $insertStmt->execute([$course_id, $student['id'], $date, $status]);
            }
            $pdo->commit();
            $success = "Asistencia registrada exitosamente.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al registrar asistencia: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_attendance'])) {
        $course_id = (int)$_POST['course_id'];
        $date = $_POST['date'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM attendance WHERE course_id = ? AND date = ?");
            $stmt->execute([$course_id, $date]);
            $success = "Registros de asistencia del " . date('d/m/Y', strtotime($date)) . " eliminados correctamente.";
        } catch (Exception $e) {
            $error = "Error al eliminar asistencia: " . $e->getMessage();
        }
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
    // Fetch enrolled students (using subquery and GROUP BY to ensure absolute uniqueness)
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.username,
               (SELECT status FROM attendance WHERE student_id = u.id AND course_id = ? AND date = ? ORDER BY id DESC LIMIT 1) as attendance_status
        FROM users u
        WHERE u.id IN (SELECT DISTINCT student_id FROM enrollments WHERE course_id = ? AND status = 'enrolled')
        AND u.role = 'student'
        GROUP BY u.id, u.name, u.username
        ORDER BY u.name
    ");
    $stmt->execute([$selected_course, $selected_date, $selected_course]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert null status to 'not_marked'
    foreach ($students as &$student) {
        if ($student['attendance_status'] === null) {
            $student['attendance_status'] = 'not_marked';
        }
    }
}
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-user-check mr-4 text-primary"></i>
        Control de Asistencia
    </h2>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 animate-fade-in-up">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

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
                <label for="course" class="block text-sm font-medium text-gray-700 mb-2">Mención</label>
                <select id="course" name="course" onchange="this.form.submit()" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    <option value="">Seleccionar mención</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo $selected_course == $course['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['name']); ?> (<?php echo htmlspecialchars($course['code']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                <input type="date" id="date" name="date" value="<?php echo $selected_date; ?>" onchange="this.form.submit()" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
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
                <input type="hidden" name="mark_attendance" value="1">

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
                    <div class="flex space-x-4 items-center">
                        <a href="export_attendance.php?type=daily&course_id=<?php echo $selected_course; ?>&date=<?php echo $selected_date; ?>" target="_blank" class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300 flex items-center shadow-md">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Reporte Diario
                        </a>
                        <button type="submit" name="mark_attendance" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-8 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Asistencia
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Attendance History Table -->
        <div class="bg-white p-6 rounded-2xl shadow-xl mt-8 animate-fade-in-up">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold flex items-center">
                    <i class="fas fa-history mr-2 text-primary"></i>
                    Historial de Asistencia (Mención)
                </h3>
                <a href="export_attendance.php?type=history&course_id=<?php echo $selected_course; ?>" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300 flex items-center text-sm shadow-md">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Exportar Historial Completo
                </a>
            </div>
            
            <div class="mb-4">
                <input type="text" id="historySearch" placeholder="Buscar estudiante..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>

            <div class="overflow-y-auto max-h-96">
                <table class="min-w-full table-auto" id="historyTable">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Fecha de Clase</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600">Presentes</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600">Ausentes</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600">Tarde/Justif.</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        // Query to group by date and count statuses
                        $stmt = $pdo->prepare("
                            SELECT 
                                date,
                                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as count_present,
                                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as count_absent,
                                SUM(CASE WHEN status IN ('late', 'excused') THEN 1 ELSE 0 END) as count_other,
                                COUNT(*) as total
                            FROM attendance
                            WHERE course_id = ?
                            GROUP BY date
                            ORDER BY date DESC
                            LIMIT 50
                        ");
                        $stmt->execute([$selected_course]);
                        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($history as $record): ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                    <i class="fas fa-calendar-alt mr-2 text-primary"></i>
                                    <?php echo date('d/m/Y', strtotime($record['date'])); ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        <?php echo $record['count_present']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        <?php echo $record['count_absent']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        <?php echo $record['count_other']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <button type="button" 
                                                onclick="openEditModal('<?php echo $record['date']; ?>')"
                                                class="text-primary hover:text-secondary p-1" title="Editar esta fecha">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="export_attendance.php?type=daily&course_id=<?php echo $selected_course; ?>&date=<?php echo $record['date']; ?>" 
                                           target="_blank" class="text-red-500 hover:text-red-700 p-1" title="Exportar PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <button type="button" 
                                                onclick="setTimeout(() => openDeleteModal('<?php echo $record['date']; ?>', '<?php echo date('d/m/Y', strtotime($record['date'])); ?>'), 50)"
                                                class="text-gray-400 hover:text-red-600 p-1" title="Eliminar registro">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($history)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500 italic">No hay registros previos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <script>
            function openDeleteModal(date, formattedDate) {
                document.getElementById('deleteDate').value = date;
                document.getElementById('displayDate').textContent = formattedDate;
                document.getElementById('deleteModal').classList.remove('hidden');
                document.getElementById('deleteModal').classList.add('flex');
            }

            function closeDeleteModal() {
                document.getElementById('deleteModal').classList.add('hidden');
                document.getElementById('deleteModal').classList.remove('flex');
            }

            function closeEditModal() {
                document.getElementById('editModal').classList.add('hidden');
                document.getElementById('editModal').classList.remove('flex');
            }

            function openEditModal(date) {
                document.getElementById('editDateDisplay').textContent = date.split('-').reverse().join('/');
                document.getElementById('editDate').value = date;
                document.getElementById('editModal').classList.remove('hidden');
                document.getElementById('editModal').classList.add('flex');
                
                const container = document.getElementById('editListContainer');
                container.innerHTML = '<div class="flex justify-center p-8"><i class="fas fa-spinner fa-spin text-4xl text-primary"></i></div>';

                fetch(`../../api/get_attendance.php?course_id=<?php echo $selected_course; ?>&date=${date}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.error) throw new Error(data.error);
                        
                        let html = '<table class="w-full text-sm"><thead><tr class="bg-gray-50 text-left"><th class="p-2">Estudiante</th><th class="p-2 text-center">P</th><th class="p-2 text-center">A</th><th class="p-2 text-center">T</th><th class="p-2 text-center">J</th></tr></thead><tbody>';
                        data.students.forEach(s => {
                            html += `<tr class="border-t hover:bg-gray-50">
                                <td class="p-2 font-medium">${s.name}</td>
                                <td class="p-2 text-center"><input type="radio" name="attendance[${s.id}]" value="present" ${s.status === 'present' ? 'checked' : ''} class="w-4 h-4 text-green-600 focus:ring-green-500"></td>
                                <td class="p-2 text-center"><input type="radio" name="attendance[${s.id}]" value="absent" ${s.status === 'absent' || s.status === 'not_marked' ? 'checked' : ''} class="w-4 h-4 text-red-600 focus:ring-red-500"></td>
                                <td class="p-2 text-center"><input type="radio" name="attendance[${s.id}]" value="late" ${s.status === 'late' ? 'checked' : ''} class="w-4 h-4 text-yellow-600 focus:ring-yellow-500"></td>
                                <td class="p-2 text-center"><input type="radio" name="attendance[${s.id}]" value="excused" ${s.status === 'excused' ? 'checked' : ''} class="w-4 h-4 text-blue-600 focus:ring-blue-500"></td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                        container.innerHTML = html;
                    })
                    .catch(err => {
                        container.innerHTML = `<div class="p-4 text-red-600">Error: ${err.message}</div>`;
                    });
            }

            document.getElementById('historySearch').addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('#historyTable tbody tr');
                
                tableRows.forEach(row => {
                    const dateText = row.children[0].textContent.toLowerCase();
                    if (dateText.includes(searchValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        </script>

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
            No hay estudiantes matriculados en esta mención.
        </div>
    <?php endif; ?>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[100] p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full animate-bounce-in overflow-hidden">
            <div class="bg-red-600 p-6 text-white text-center">
                <i class="fas fa-exclamation-triangle text-5xl mb-4"></i>
                <h3 class="text-2xl font-bold">¿Eliminar Asistencia?</h3>
            </div>
            <div class="p-8 text-center text-gray-700">
                <p class="mb-4 text-lg">Estás a punto de eliminar todos los registros de asistencia del día <span id="displayDate" class="font-bold"></span>.</p>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 text-left">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong class="block mb-1">Recomendación:</strong>
                                Asegúrate de haber descargado el reporte en PDF antes de continuar. Una vez eliminado, no podrás recuperar estos datos.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col space-y-3">
                    <form method="POST">
                        <input type="hidden" name="course_id" value="<?php echo $selected_course; ?>">
                        <input type="hidden" id="deleteDate" name="date" value="">
                        <input type="hidden" name="delete_attendance" value="1">
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                            Confirmar Eliminación
                        </button>
                    </form>
                    
                    <button onclick="closeDeleteModal()" class="text-gray-500 hover:text-gray-700 font-medium py-2">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[100] p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full animate-bounce-in overflow-hidden">
            <div class="bg-primary p-6 text-white flex justify-between items-center">
                <h3 class="text-xl font-bold"><i class="fas fa-edit mr-2"></i> Editar Asistencia: <span id="editDateDisplay"></span></h3>
                <button onclick="closeEditModal()" class="text-white hover:text-gray-200 text-2xl">&times;</button>
            </div>
            <form method="POST">
                <div class="p-6 max-h-[60vh] overflow-y-auto" id="editListContainer">
                    <!-- Loaded via AJAX -->
                </div>
                <div class="p-6 bg-gray-50 border-t flex justify-end space-x-3">
                    <input type="hidden" name="course_id" value="<?php echo $selected_course; ?>">
                    <input type="hidden" id="editDate" name="date" value="">
                    <input type="hidden" name="mark_attendance" value="1">
                    <button type="button" onclick="closeEditModal()" class="px-6 py-2 text-gray-600 hover:text-gray-800 font-medium">Cancelar</button>
                    <button type="submit" class="px-6 py-2 bg-primary hover:bg-yellow-600 text-white font-bold rounded-lg transition duration-200">
                        Actualizar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>