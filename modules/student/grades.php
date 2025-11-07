<?php require_once '../../includes/config.php'; ?>
<?php requireRole('student'); ?>
<?php include '../../templates/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];

// Fetch current grades for enrolled courses
$stmt = $pdo->prepare("
    SELECT c.name, c.code, c.credits, e.grade, e.status, e.period,
           CASE
               WHEN e.grade >= 10 THEN 'Aprobado'
               WHEN e.grade < 10 AND e.grade IS NOT NULL THEN 'Reprobado'
               ELSE 'Sin calificar'
           END as result
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.student_id = ? AND e.status = 'enrolled'
    ORDER BY c.name ASC
");
$stmt->execute([$user_id]);
$current_grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_courses = count($current_grades);
$graded_courses = count(array_filter($current_grades, function($c) { return $c['grade'] !== null; }));
$approved_courses = count(array_filter($current_grades, function($c) { return $c['grade'] >= 10; }));
$failed_courses = count(array_filter($current_grades, function($c) { return $c['grade'] < 10 && $c['grade'] !== null; }));

// Calculate current GPA
$current_gpa = 0;
if ($graded_courses > 0) {
    $total_points = 0;
    $total_weighted_credits = 0;
    foreach ($current_grades as $course) {
        if ($course['grade'] !== null) {
            $total_points += $course['grade'] * $course['credits'];
            $total_weighted_credits += $course['credits'];
        }
    }
    $current_gpa = $total_weighted_credits > 0 ? round($total_points / $total_weighted_credits, 2) : 0;
}
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-graduation-cap mr-4 text-primary"></i>
        Mis Calificaciones
    </h2>

    <!-- Current Grades Summary -->
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Promedio Actual</p>
                    <p class="text-3xl font-bold text-primary"><?php echo $current_gpa; ?>/20</p>
                </div>
                <div class="text-4xl text-primary opacity-70">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.1s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Menciones Calificadas</p>
                    <p class="text-3xl font-bold text-blue-500"><?php echo $graded_courses; ?>/<?php echo $total_courses; ?></p>
                </div>
                <div class="text-4xl text-blue-500 opacity-70">
                    <i class="fas fa-edit"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.2s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Menciones Aprobadas</p>
                    <p class="text-3xl font-bold text-green-500"><?php echo $approved_courses; ?></p>
                </div>
                <div class="text-4xl text-green-500 opacity-70">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.3s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Menciones Reprobadas</p>
                    <p class="text-3xl font-bold text-red-500"><?php echo $failed_courses; ?></p>
                </div>
                <div class="text-4xl text-red-500 opacity-70">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Grades Table -->
    <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up mb-8">
        <h3 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-list mr-2 text-primary"></i>
            Calificaciones del Período Actual
        </h3>

        <?php if (!empty($current_grades)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Código</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Asignatura</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Créditos</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Calificación</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Resultado</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($current_grades as $course): ?>
                            <tr class="hover:bg-gray-50 transition duration-200">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($course['code']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($course['name']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo $course['credits']; ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($course['grade'] !== null): ?>
                                        <span class="font-bold text-lg <?php echo $course['grade'] >= 10 ? 'text-green-600' : 'text-red-600'; ?>">
                                            <?php echo $course['grade']; ?>/20
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-500 italic">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
                                        <?php
                                        if ($course['grade'] >= 10) echo 'bg-green-100 text-green-800';
                                        elseif ($course['grade'] !== null) echo 'bg-red-100 text-red-800';
                                        else echo 'bg-yellow-100 text-yellow-800';
                                        ?>">
                                        <?php echo $course['result']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo ucfirst($course['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <div class="text-6xl text-gray-300 mb-4">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">No hay calificaciones disponibles</h3>
                <p class="text-gray-600">Aún no tienes menciones matriculadas o calificaciones registradas.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Grade Distribution Chart -->
    <?php if ($graded_courses > 0): ?>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up mb-8">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-chart-pie mr-2 text-primary"></i>
                Distribución de Calificaciones
            </h3>

            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <h4 class="text-lg font-semibold mb-4">Resumen por Categoría</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Excelente (18-20)</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: <?php
                                        $excellent = count(array_filter($current_grades, function($c) { return $c['grade'] >= 18; }));
                                        echo $graded_courses > 0 ? ($excellent / $graded_courses) * 100 : 0;
                                    ?>%"></div>
                                </div>
                                <span class="text-sm font-bold w-8"><?php echo count(array_filter($current_grades, function($c) { return $c['grade'] >= 18; })); ?></span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Muy Bueno (15-17)</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?php
                                        $very_good = count(array_filter($current_grades, function($c) { return $c['grade'] >= 15 && $c['grade'] < 18; }));
                                        echo $graded_courses > 0 ? ($very_good / $graded_courses) * 100 : 0;
                                    ?>%"></div>
                                </div>
                                <span class="text-sm font-bold w-8"><?php echo count(array_filter($current_grades, function($c) { return $c['grade'] >= 15 && $c['grade'] < 18; })); ?></span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Bueno (12-14)</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-600 h-2 rounded-full" style="width: <?php
                                        $good = count(array_filter($current_grades, function($c) { return $c['grade'] >= 12 && $c['grade'] < 15; }));
                                        echo $graded_courses > 0 ? ($good / $graded_courses) * 100 : 0;
                                    ?>%"></div>
                                </div>
                                <span class="text-sm font-bold w-8"><?php echo count(array_filter($current_grades, function($c) { return $c['grade'] >= 12 && $c['grade'] < 15; })); ?></span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Deficiente (10-11)</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-orange-600 h-2 rounded-full" style="width: <?php
                                        $deficient = count(array_filter($current_grades, function($c) { return $c['grade'] >= 10 && $c['grade'] < 12; }));
                                        echo $graded_courses > 0 ? ($deficient / $graded_courses) * 100 : 0;
                                    ?>%"></div>
                                </div>
                                <span class="text-sm font-bold w-8"><?php echo count(array_filter($current_grades, function($c) { return $c['grade'] >= 10 && $c['grade'] < 12; })); ?></span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Reprobado (0-9)</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-600 h-2 rounded-full" style="width: <?php
                                        $failed = count(array_filter($current_grades, function($c) { return $c['grade'] < 10 && $c['grade'] !== null; }));
                                        echo $graded_courses > 0 ? ($failed / $graded_courses) * 100 : 0;
                                    ?>%"></div>
                                </div>
                                <span class="text-sm font-bold w-8"><?php echo count(array_filter($current_grades, function($c) { return $c['grade'] < 10 && $c['grade'] !== null; })); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Estadísticas Generales</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-green-600"><?php echo round(($approved_courses / max($total_courses, 1)) * 100, 1); ?>%</div>
                            <div class="text-sm text-green-800">Tasa de Aprobación</div>
                        </div>
                        <div class="bg-gradient-to-r from-red-50 to-red-100 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-red-600"><?php echo round(($failed_courses / max($graded_courses, 1)) * 100, 1); ?>%</div>
                            <div class="text-sm text-red-800">Tasa de Reprobación</div>
                        </div>
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg text-center col-span-2">
                            <div class="text-2xl font-bold text-blue-600"><?php echo $current_gpa; ?>/20</div>
                            <div class="text-sm text-blue-800">Promedio Ponderado</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="bg-gradient-to-r from-primary to-secondary p-6 rounded-2xl text-white animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-bolt mr-2"></i>
            Acciones Rápidas
        </h3>
        <div class="grid md:grid-cols-2 gap-4">
            <a href="history.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 p-4 rounded-lg transition duration-300 flex items-center">
                <i class="fas fa-history mr-3 text-xl"></i>
                <div>
                    <div class="font-semibold">Ver Historial Completo</div>
                    <div class="text-sm opacity-90">Consulta todas tus calificaciones anteriores</div>
                </div>
            </a>
            <a href="activities.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 p-4 rounded-lg transition duration-300 flex items-center">
                <i class="fas fa-tasks mr-3 text-xl"></i>
                <div>
                    <div class="font-semibold">Ver Actividades</div>
                    <div class="text-sm opacity-90">Revisa tus tareas y entregas pendientes</div>
                </div>
            </a>
        </div>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>