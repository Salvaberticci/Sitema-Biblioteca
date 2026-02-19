<?php require_once '../../includes/config.php'; ?>
<?php requireRole('admin'); ?>
<?php include '../../templates/header.php'; ?>

<?php
// Debug output (visible on page)
$debug_info = [];
$debug_info[] = "Request Method: " . $_SERVER['REQUEST_METHOD'];
if (!empty($_POST)) {
    $debug_info[] = "All POST keys: " . implode(', ', array_keys($_POST));
} else {
    $debug_info[] = "No POST data received";
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['enroll_student'])) {
        $debug_info[] = "Processing enroll_student request";
        $student_id = (int) $_POST['student_id'];
        $course_id = (int) $_POST['course_id'];
        $period = sanitize($_POST['period']) ?: date('Y-1');

        // Check if student is already enrolled
        $debug_info[] = "Checking enrollment for student $student_id in course $course_id";
        $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$student_id, $course_id]);
        $existing = $stmt->fetch();
        $debug_info[] = "Existing enrollment check result: " . ($existing ? 'found' : 'not found');

        if ($existing) {
            $error = "El estudiante ya está matriculado en este curso.";
            $debug_info[] = "Enrollment failed: already enrolled";
        } else {
            $debug_info[] = "Inserting enrollment for student $student_id in course $course_id with period $period";
            $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id, period, status) VALUES (?, ?, ?, 'enrolled')");
            $result = $stmt->execute([$student_id, $course_id, $period]);
            $debug_info[] = "Insert result: " . ($result ? 'success' : 'failed');
            if ($result) {
                $success = "Estudiante matriculado exitosamente.";
            } else {
                $error = "Error al insertar la matrícula en la base de datos.";
                $debug_info[] = "Insert failed without exception";
            }
        }
    } elseif (isset($_POST['enrollment_id'])) {
        $enrollment_id = (int) $_POST['enrollment_id'];

        // Start transaction to ensure atomicity
        $pdo->beginTransaction();

        try {
            // Delete related records first to avoid foreign key constraints

            // Delete attendance records for this enrollment
            $stmt = $pdo->prepare("DELETE FROM attendance WHERE student_id IN (SELECT student_id FROM enrollments WHERE id = ?) AND course_id IN (SELECT course_id FROM enrollments WHERE id = ?)");
            $stmt->execute([$enrollment_id, $enrollment_id]);

            // Delete submissions for this enrollment
            $stmt = $pdo->prepare("DELETE FROM submissions WHERE student_id IN (SELECT student_id FROM enrollments WHERE id = ?) AND activity_id IN (SELECT id FROM activities WHERE course_id IN (SELECT course_id FROM enrollments WHERE id = ?))");
            $stmt->execute([$enrollment_id, $enrollment_id]);

            // Finally, delete the enrollment
            $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ?");
            $stmt->execute([$enrollment_id]);

            // Commit the transaction
            $pdo->commit();
            $success = "Estudiante desmatriculado exitosamente junto con todos sus registros relacionados.";
        } catch (Exception $e) {
            // Rollback on error
            $pdo->rollBack();
            $error = "Error al desmatricular el estudiante: " . $e->getMessage();
        }
    }
}

// Fetch all enrollments with student and course info
try {
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
} catch (PDOException $e) {
    $debug_info[] = "Error fetching enrollments: " . $e->getMessage();
    $enrollments = [];
}

// Fetch students and courses for enrollment form
try {
    $students = $pdo->query("SELECT id, name, username FROM users WHERE role = 'student' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $courses = $pdo->query("SELECT id, name, code FROM courses ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $debug_info[] = "Error fetching students/courses: " . $e->getMessage();
    $students = [];
    $courses = [];
}
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
            <!-- Searchable Student Field -->
            <div class="relative" id="student-wrapper">
                <label class="block text-sm font-medium text-gray-700 mb-2">Estudiante</label>
                <input type="hidden" id="student_id" name="student_id" required>
                <div class="relative">
                    <i
                        class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                    <input type="text" id="student_search" placeholder="Buscar por nombre o usuario..."
                        autocomplete="off"
                        class="w-full pl-9 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 text-sm"
                        oninput="filterDropdown('student')" onfocus="openDropdown('student')">
                </div>
                <ul id="student_dropdown"
                    class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto hidden mt-1">
                    <?php foreach ($students as $student): ?>
                        <li class="student-option px-4 py-2.5 cursor-pointer hover:bg-primary hover:text-white transition duration-150 text-sm"
                            data-id="<?php echo $student['id']; ?>"
                            data-label="<?php echo htmlspecialchars($student['name'] . ' (' . $student['username'] . ')'); ?>"
                            data-search="<?php echo strtolower(htmlspecialchars($student['name'] . ' ' . $student['username'])); ?>"
                            onclick="selectOption('student', this)">
                            <span class="font-medium"><?php echo htmlspecialchars($student['name']); ?></span>
                            <span
                                class="text-xs opacity-70 ml-1">@<?php echo htmlspecialchars($student['username']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Searchable Course Field -->
            <div class="relative" id="course-wrapper">
                <label class="block text-sm font-medium text-gray-700 mb-2">Curso</label>
                <input type="hidden" id="course_id" name="course_id" required>
                <div class="relative">
                    <i
                        class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                    <input type="text" id="course_search" placeholder="Buscar por nombre de curso..." autocomplete="off"
                        class="w-full pl-9 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 text-sm"
                        oninput="filterDropdown('course')" onfocus="openDropdown('course')">
                </div>
                <ul id="course_dropdown"
                    class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto hidden mt-1">
                    <?php foreach ($courses as $course): ?>
                        <li class="course-option px-4 py-2.5 cursor-pointer hover:bg-primary hover:text-white transition duration-150 text-sm"
                            data-id="<?php echo $course['id']; ?>"
                            data-label="<?php echo htmlspecialchars($course['name'] . ' (' . $course['code'] . ')'); ?>"
                            data-search="<?php echo strtolower(htmlspecialchars($course['name'] . ' ' . $course['code'])); ?>"
                            onclick="selectOption('course', this)">
                            <span class="font-medium"><?php echo htmlspecialchars($course['name']); ?></span>
                            <span class="text-xs opacity-70 ml-1">(<?php echo htmlspecialchars($course['code']); ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <label for="period" class="block text-sm font-medium text-gray-700 mb-2">Periodo</label>
                <input type="text" id="period" name="period" value="<?php echo date('Y-1'); ?>" placeholder="2025-1"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-8 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                    <i class="fas fa-user-plus mr-2"></i>
                    Matricular
                </button>
                <input type="hidden" name="enroll_student" value="1">
            </div>
        </form>
    </div>

    <!-- Enrollments List -->
    <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-list mr-2 text-primary"></i>
            Lista de Matrículas
        </h3>

        <!-- Search bar -->
        <div class="mb-5 flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            <div class="relative flex-1 max-w-md">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="enrollmentSearch" placeholder="Buscar por nombre o usuario..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200 text-sm"
                    oninput="filterEnrollments(this.value)">
            </div>
            <span id="enrollmentCount" class="text-sm text-gray-500 whitespace-nowrap">
                <?php echo count($enrollments); ?> matrícula(s)
            </span>
        </div>
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
                    <?php if (isset($enrollments) && is_array($enrollments)): ?>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <tr class="hover:bg-gray-50 transition duration-200 enrollment-row"
                                data-name="<?php echo strtolower(htmlspecialchars($enrollment['student_name'])); ?>"
                                data-username="<?php echo strtolower(htmlspecialchars($enrollment['student_username'])); ?>">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($enrollment['student_name']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($enrollment['student_username']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php echo htmlspecialchars($enrollment['course_name']); ?>
                                    (<?php echo htmlspecialchars($enrollment['course_code']); ?>)
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($enrollment['period']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
                                    <?php
                                    if ($enrollment['status'] == 'enrolled')
                                        echo 'bg-green-100 text-green-800';
                                    elseif ($enrollment['status'] == 'completed')
                                        echo 'bg-blue-100 text-blue-800';
                                    elseif ($enrollment['status'] == 'failed')
                                        echo 'bg-red-100 text-red-800';
                                    else
                                        echo 'bg-gray-100 text-gray-800';
                                    ?>">
                                        <?php echo ucfirst($enrollment['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($enrollment['grade'] !== null): ?>
                                        <span
                                            class="font-bold <?php echo $enrollment['grade'] >= 10 ? 'text-green-600' : 'text-red-600'; ?>">
                                            <?php echo $enrollment['grade']; ?>/20
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-500 italic">Sin calificar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <form method="POST" class="inline"
                                        onsubmit="return confirm('¿Está seguro de desmatricular a este estudiante?')">
                                        <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['id']; ?>">
                                        <button type="submit" name="unenroll_student"
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200 flex items-center"
                                            title="Desmatricular">
                                            <i class="fas fa-user-minus mr-1"></i>
                                            <span class="hidden sm:inline">Desmatricular</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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

<script>
    function filterEnrollments(query) {
        const q = query.trim().toLowerCase();
        const rows = document.querySelectorAll('.enrollment-row');
        let visible = 0;

        rows.forEach(function (row) {
            const name = row.getAttribute('data-name') || '';
            const username = row.getAttribute('data-username') || '';
            const matches = name.includes(q) || username.includes(q);
            row.style.display = matches ? '' : 'none';
            if (matches) visible++;
        });

        // Update counter
        const counter = document.getElementById('enrollmentCount');
        if (counter) {
            counter.textContent = visible + ' matrícula' + (visible !== 1 ? 's' : '') + (q ? ' encontrada' + (visible !== 1 ? 's' : '') : '');
        }

        // Show/hide the "no results" row
        let noResults = document.getElementById('no-results-row');
        if (visible === 0 && rows.length > 0) {
            if (!noResults) {
                const tbody = document.querySelector('.enrollment-row').closest('tbody');
                const tr = document.createElement('tr');
                tr.id = 'no-results-row';
                tr.innerHTML = '<td colspan="7" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-search mr-2"></i>No se encontraron matrículas para "<strong>' + query.replace(/</g, '&lt;') + '</strong>"</td>';
                tbody.appendChild(tr);
            } else {
                noResults.style.display = '';
                noResults.querySelector('strong').textContent = query;
            }
        } else if (noResults) {
            noResults.style.display = 'none';
        }
    }

    /* ── Searchable dropdown functions ── */
    function openDropdown(type) {
        document.getElementById(type + '_dropdown').classList.remove('hidden');
        filterDropdown(type);
    }

    function filterDropdown(type) {
        const q = document.getElementById(type + '_search').value.trim().toLowerCase();
        const items = document.querySelectorAll('.' + type + '-option');
        let anyVisible = false;
        items.forEach(function (item) {
            const match = item.getAttribute('data-search').includes(q);
            item.style.display = match ? '' : 'none';
            if (match) anyVisible = true;
        });
        // Clear hidden id if user is typing again
        if (q === '') {
            document.getElementById(type + '_id').value = '';
        }
        // Show "no options" hint
        const dd = document.getElementById(type + '_dropdown');
        let none = dd.querySelector('.no-options-hint');
        if (!anyVisible) {
            if (!none) {
                none = document.createElement('li');
                none.className = 'no-options-hint px-4 py-3 text-sm text-gray-400 italic';
                none.textContent = 'Sin resultados';
                dd.appendChild(none);
            }
            none.style.display = '';
        } else if (none) {
            none.style.display = 'none';
        }
        dd.classList.remove('hidden');
    }

    function selectOption(type, li) {
        document.getElementById(type + '_id').value = li.getAttribute('data-id');
        document.getElementById(type + '_search').value = li.getAttribute('data-label');
        document.getElementById(type + '_dropdown').classList.add('hidden');
        // Remove any validation error styling
        document.getElementById(type + '_search').classList.remove('border-red-500');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function (e) {
        ['student', 'course'].forEach(function (type) {
            const wrapper = document.getElementById(type + '-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                document.getElementById(type + '_dropdown').classList.add('hidden');
                // If user typed but didn't select, clear input to prevent invalid submission
                if (!document.getElementById(type + '_id').value) {
                    document.getElementById(type + '_search').value = '';
                }
            }
        });
    });

    // Validate hidden inputs before form submit
    document.querySelector('form[method="POST"]').addEventListener('submit', function (e) {
        let valid = true;
        ['student', 'course'].forEach(function (type) {
            const hidden = document.getElementById(type + '_id');
            const search = document.getElementById(type + '_search');
            if (!hidden.value) {
                search.classList.add('border-red-500', 'ring-2', 'ring-red-300');
                search.placeholder = '\u26a0 Selecciona una opci\u00f3n v\u00e1lida';
                valid = false;
            }
        });
        if (!valid) e.preventDefault();
    });
</script>

<?php include '../../templates/footer.php'; ?>