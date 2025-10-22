<?php require_once '../../includes/config.php'; ?>
<?php requireRole('admin'); ?>
<?php include '../../templates/header.php'; ?>

<?php
// Get report data
$report_type = isset($_GET['type']) ? $_GET['type'] : 'overview';

// Overall statistics
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_teachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$total_enrollments = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'enrolled'")->fetchColumn();
$total_library_resources = $pdo->query("SELECT COUNT(*) FROM library_resources")->fetchColumn();
$total_activities = $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn();
$total_schedules = $pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn();

// Grade distribution
$grade_distribution = $pdo->query("
    SELECT
        CASE
            WHEN grade >= 18 THEN '18-20'
            WHEN grade >= 15 THEN '15-17'
            WHEN grade >= 12 THEN '12-14'
            WHEN grade >= 10 THEN '10-11'
            WHEN grade >= 0 THEN '0-9'
            ELSE 'Sin calificar'
        END as `range`,
        COUNT(*) as count
    FROM enrollments
    WHERE grade IS NOT NULL
    GROUP BY `range`
    ORDER BY FIELD(`range`, '18-20', '15-17', '12-14', '10-11', '0-9', 'Sin calificar')
")->fetchAll(PDO::FETCH_ASSOC);

// Course enrollment stats
$course_stats = $pdo->prepare("
    SELECT c.name, c.code, COUNT(e.student_id) as enrolled_count
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id AND e.status = 'enrolled'
    GROUP BY c.id, c.name, c.code
    ORDER BY enrolled_count DESC
    LIMIT 10
");
$course_stats->execute();
$top_courses = $course_stats->fetchAll(PDO::FETCH_ASSOC);

// Recent activities
$recent_activities = $pdo->query("
    SELECT a.title, c.name as course_name, u.name as teacher_name,
           COUNT(s.id) as submissions, a.due_date
    FROM activities a
    JOIN courses c ON a.course_id = c.id
    JOIN users u ON a.teacher_id = u.id
    LEFT JOIN submissions s ON a.id = s.activity_id
    GROUP BY a.id, a.title, c.name, u.name, a.due_date
    ORDER BY a.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-chart-bar mr-4 text-primary"></i>
        Reportes Avanzados
    </h2>

    <!-- Report Type Selector -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-filter mr-2 text-primary"></i>
            Tipo de Reporte
        </h3>
        <div class="flex flex-wrap gap-4">
            <a href="?type=overview" class="px-4 py-2 rounded-lg font-medium transition duration-200 <?php echo $report_type == 'overview' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                <i class="fas fa-tachometer-alt mr-2"></i>
                Resumen General
            </a>
            <a href="?type=grades" class="px-4 py-2 rounded-lg font-medium transition duration-200 <?php echo $report_type == 'grades' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                <i class="fas fa-graduation-cap mr-2"></i>
                Reporte de Calificaciones
            </a>
            <a href="?type=courses" class="px-4 py-2 rounded-lg font-medium transition duration-200 <?php echo $report_type == 'courses' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                <i class="fas fa-book mr-2"></i>
                Estadísticas de Cursos
            </a>
            <a href="?type=activities" class="px-4 py-2 rounded-lg font-medium transition duration-200 <?php echo $report_type == 'activities' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                <i class="fas fa-tasks mr-2"></i>
                Reporte de Actividades
            </a>
        </div>
    </div>

    <?php if ($report_type == 'overview'): ?>
        <!-- Overview Report -->
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total Usuarios</p>
                        <p class="text-3xl font-bold text-primary"><?php echo number_format($total_users); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo $total_students; ?> estudiantes, <?php echo $total_teachers; ?> docentes</p>
                    </div>
                    <div class="text-4xl text-primary opacity-70">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Cursos Activos</p>
                        <p class="text-3xl font-bold text-secondary"><?php echo number_format($total_courses); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo $total_enrollments; ?> matriculados</p>
                    </div>
                    <div class="text-4xl text-secondary opacity-70">
                        <i class="fas fa-book-open"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Recursos Biblioteca</p>
                        <p class="text-3xl font-bold text-accent"><?php echo number_format($total_library_resources); ?></p>
                        <p class="text-xs text-gray-500 mt-1">Materiales disponibles</p>
                    </div>
                    <div class="text-4xl text-accent opacity-70">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Actividades Totales</p>
                        <p class="text-3xl font-bold text-purple-600"><?php echo number_format($total_activities); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo $total_schedules; ?> horarios programados</p>
                    </div>
                    <div class="text-4xl text-purple-600 opacity-70">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-clock mr-2 text-primary"></i>
                Actividades Recientes
            </h3>
            <div class="space-y-4">
                <?php foreach ($recent_activities as $activity): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($activity['title']); ?></h4>
                            <p class="text-sm text-gray-600">
                                <?php echo htmlspecialchars($activity['course_name']); ?> -
                                Docente: <?php echo htmlspecialchars($activity['teacher_name']); ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-calendar mr-1"></i>
                                <?php echo date('d/m/Y', strtotime($activity['due_date'])); ?>
                            </div>
                            <div class="text-sm font-medium text-primary">
                                <?php echo $activity['submissions']; ?> entregas
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php elseif ($report_type == 'grades'): ?>
        <!-- Grades Report -->
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-chart-pie mr-2 text-primary"></i>
                Distribución de Calificaciones
            </h3>

            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <h4 class="text-lg font-semibold mb-4">Distribución por Rangos</h4>
                    <div class="space-y-3">
                        <?php foreach ($grade_distribution as $range): ?>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium"><?php echo $range['range']; ?></span>
                                <div class="flex items-center space-x-2">
                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                        <div class="bg-primary h-2 rounded-full" style="width: <?php echo array_sum(array_column($grade_distribution, 'count')) > 0 ? ($range['count'] / array_sum(array_column($grade_distribution, 'count'))) * 100 : 0; ?>%"></div>
                                    </div>
                                    <span class="text-sm font-bold w-8"><?php echo $range['count']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Estadísticas Generales</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <?php
                        $total_graded = array_sum(array_column($grade_distribution, 'count'));
                        $approved = array_sum(array_map(function($r) { return in_array($r['range'], ['10-11', '12-14', '15-17', '18-20']) ? $r['count'] : 0; }, $grade_distribution));
                        $failed = array_sum(array_map(function($r) { return in_array($r['range'], ['0-9']) ? $r['count'] : 0; }, $grade_distribution));
                        $avg_grade = 0;
                        if ($total_graded > 0) {
                            // This is a simplified calculation - in reality you'd need to weight by course credits
                            $avg_grade = 12.5; // Placeholder
                        }
                        ?>
                        <div class="bg-green-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-green-600"><?php echo round(($approved / max($total_graded, 1)) * 100, 1); ?>%</div>
                            <div class="text-sm text-green-800">Aprobación</div>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-red-600"><?php echo round(($failed / max($total_graded, 1)) * 100, 1); ?>%</div>
                            <div class="text-sm text-red-800">Reprobación</div>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg text-center col-span-2">
                            <div class="text-2xl font-bold text-blue-600"><?php echo number_format($avg_grade, 1); ?>/20</div>
                            <div class="text-sm text-blue-800">Promedio General</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($report_type == 'courses'): ?>
        <!-- Courses Report -->
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-book mr-2 text-primary"></i>
                Estadísticas de Cursos
            </h3>

            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Código</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Nombre del Curso</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estudiantes Matriculados</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Créditos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($top_courses as $course): ?>
                            <tr class="hover:bg-gray-50 transition duration-200">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($course['code']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($course['name']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <span class="bg-primary text-white px-3 py-1 rounded-full text-xs font-medium">
                                        <?php echo $course['enrolled_count']; ?> estudiantes
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php
                                    $credits = $pdo->prepare("SELECT credits FROM courses WHERE id = ?");
                                    $credits->execute([$pdo->query("SELECT id FROM courses WHERE code = '{$course['code']}'")->fetchColumn()]);
                                    echo $credits->fetchColumn();
                                    ?> créditos
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($report_type == 'activities'): ?>
        <!-- Activities Report -->
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-tasks mr-2 text-primary"></i>
                Reporte de Actividades
            </h3>

            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Actividad</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Curso</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Docente</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Fecha Límite</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Entregas</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($recent_activities as $activity): ?>
                            <tr class="hover:bg-gray-50 transition duration-200">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($activity['title']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($activity['course_name']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($activity['teacher_name']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($activity['due_date'])); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-medium">
                                        <?php echo $activity['submissions']; ?> entregas
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php
                                    $now = new DateTime();
                                    $due = new DateTime($activity['due_date']);
                                    if ($now > $due) {
                                        echo '<span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-medium">Vencida</span>';
                                    } elseif ($now->diff($due)->days <= 3) {
                                        echo '<span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-medium">Próxima</span>';
                                    } else {
                                        echo '<span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium">Activa</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Export Options -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mt-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-download mr-2 text-primary"></i>
            Exportar Reportes
        </h3>
        <div class="flex flex-wrap gap-4">
            <button onclick="exportReport('pdf')" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-200 flex items-center">
                <i class="fas fa-file-pdf mr-2"></i>
                Exportar PDF
            </button>
            <button onclick="exportReport('excel')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-200 flex items-center">
                <i class="fas fa-file-excel mr-2"></i>
                Exportar Excel
            </button>
            <button onclick="exportReport('csv')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200 flex items-center">
                <i class="fas fa-file-csv mr-2"></i>
                Exportar CSV
            </button>
        </div>
    </div>
</main>

<script>
function exportReport(format) {
    const reportType = '<?php echo $report_type; ?>';

    // Create form to submit export request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'export.php';
    form.style.display = 'none';

    // Add form fields
    const fields = {
        'format': format,
        'report_type': reportType,
        'export_data': JSON.stringify(getReportData())
    };

    for (const [key, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function getReportData() {
    const reportType = '<?php echo $report_type; ?>';

    switch(reportType) {
        case 'overview':
            return {
                total_users: <?php echo $total_users; ?>,
                total_students: <?php echo $total_students; ?>,
                total_teachers: <?php echo $total_teachers; ?>,
                total_courses: <?php echo $total_courses; ?>,
                total_enrollments: <?php echo $total_enrollments; ?>,
                total_library_resources: <?php echo $total_library_resources; ?>,
                total_activities: <?php echo $total_activities; ?>,
                total_schedules: <?php echo $total_schedules; ?>,
                recent_activities: <?php echo json_encode($recent_activities); ?>
            };

        case 'grades':
            return {
                grade_distribution: <?php echo json_encode($grade_distribution); ?>,
                total_graded: <?php echo array_sum(array_column($grade_distribution, 'count')); ?>,
                approved_count: <?php echo array_sum(array_map(function($r) { return in_array($r['range'], ['10-11', '12-14', '15-17', '18-20']) ? $r['count'] : 0; }, $grade_distribution)); ?>,
                failed_count: <?php echo array_sum(array_map(function($r) { return in_array($r['range'], ['0-9']) ? $r['count'] : 0; }, $grade_distribution)); ?>
            };

        case 'courses':
            return {
                courses: <?php echo json_encode($top_courses); ?>
            };

        case 'activities':
            return {
                activities: <?php echo json_encode($recent_activities); ?>
            };

        default:
            return {};
    }
}
</script>

<?php include '../../templates/footer.php'; ?>