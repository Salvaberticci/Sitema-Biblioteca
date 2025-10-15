<?php require_once '../../includes/config.php'; ?>
<?php requireRole('student'); ?>
<?php include '../../templates/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];

// Fetch complete academic history
$stmt = $pdo->prepare("
    SELECT c.name, c.code, c.credits, e.grade, e.status, e.period,
           CASE
               WHEN e.grade >= 10 THEN 'Aprobado'
               WHEN e.grade < 10 AND e.grade IS NOT NULL THEN 'Reprobado'
               ELSE 'Sin calificar'
           END as result
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.student_id = ?
    ORDER BY e.period DESC, c.name ASC
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by period
$history_by_period = [];
foreach ($history as $record) {
    $history_by_period[$record['period']][] = $record;
}

// Calculate statistics
$total_courses = count($history);
$approved_courses = count(array_filter($history, function($h) { return $h['grade'] >= 10; }));
$failed_courses = count(array_filter($history, function($h) { return $h['grade'] < 10 && $h['grade'] !== null; }));
$pending_courses = count(array_filter($history, function($h) { return $h['grade'] === null; }));

$total_credits_attempted = array_sum(array_column($history, 'credits'));
$total_credits_earned = array_sum(array_map(function($h) {
    return ($h['grade'] >= 10) ? $h['credits'] : 0;
}, $history));

// Calculate GPA
$graded_courses = array_filter($history, function($h) { return $h['grade'] !== null; });
$gpa = 0;
if (!empty($graded_courses)) {
    $total_points = 0;
    $total_weighted_credits = 0;
    foreach ($graded_courses as $course) {
        $total_points += $course['grade'] * $course['credits'];
        $total_weighted_credits += $course['credits'];
    }
    $gpa = $total_weighted_credits > 0 ? round($total_points / $total_weighted_credits, 2) : 0;
}
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-history mr-4 text-primary"></i>
        Historial Académico
    </h2>

    <!-- Academic Summary -->
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Promedio General</p>
                    <p class="text-3xl font-bold text-primary"><?php echo $gpa; ?>/20</p>
                </div>
                <div class="text-4xl text-primary opacity-70">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.1s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Cursos Aprobados</p>
                    <p class="text-3xl font-bold text-green-500"><?php echo $approved_courses; ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?php echo $total_credits_earned; ?> créditos</p>
                </div>
                <div class="text-4xl text-green-500 opacity-70">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.2s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Cursos Reprobados</p>
                    <p class="text-3xl font-bold text-red-500"><?php echo $failed_courses; ?></p>
                </div>
                <div class="text-4xl text-red-500 opacity-70">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.3s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total de Cursos</p>
                    <p class="text-3xl font-bold text-blue-500"><?php echo $total_courses; ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?php echo $total_credits_attempted; ?> créditos intentados</p>
                </div>
                <div class="text-4xl text-blue-500 opacity-70">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Academic Progress Chart -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-chart-pie mr-2 text-primary"></i>
            Progreso Académico
        </h3>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="relative w-32 h-32 mx-auto mb-4">
                    <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
                        <path d="M18 2.0845
                              a 15.9155 15.9155 0 0 1 0 31.831
                              a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#E5E7EB" stroke-width="3"/>
                        <path d="M18 2.0845
                              a 15.9155 15.9155 0 0 1 0 31.831
                              a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#10B981" stroke-width="3"
                              stroke-dasharray="<?php echo ($approved_courses / max($total_courses, 1)) * 100; ?>, 100"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-bold text-green-600"><?php echo round(($approved_courses / max($total_courses, 1)) * 100); ?>%</span>
                    </div>
                </div>
                <p class="text-gray-600 font-medium">Aprobados</p>
            </div>
            <div class="text-center">
                <div class="relative w-32 h-32 mx-auto mb-4">
                    <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
                        <path d="M18 2.0845
                              a 15.9155 15.9155 0 0 1 0 31.831
                              a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#E5E7EB" stroke-width="3"/>
                        <path d="M18 2.0845
                              a 15.9155 15.9155 0 0 1 0 31.831
                              a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#F59E0B" stroke-width="3"
                              stroke-dasharray="<?php echo ($total_credits_earned / max($total_credits_attempted, 1)) * 100; ?>, 100"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-bold text-yellow-600"><?php echo round(($total_credits_earned / max($total_credits_attempted, 1)) * 100); ?>%</span>
                    </div>
                </div>
                <p class="text-gray-600 font-medium">Créditos Ganados</p>
            </div>
            <div class="text-center">
                <div class="relative w-32 h-32 mx-auto mb-4">
                    <div class="w-32 h-32 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                        <div class="text-center text-white">
                            <div class="text-2xl font-bold"><?php echo $gpa; ?></div>
                            <div class="text-sm opacity-90">/20</div>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600 font-medium">Promedio</p>
            </div>
        </div>
    </div>

    <!-- Academic History by Period -->
    <div class="space-y-6">
        <?php foreach ($history_by_period as $period => $courses): ?>
            <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
                <h3 class="text-xl font-semibold mb-6 flex items-center">
                    <i class="fas fa-calendar-alt mr-2 text-primary"></i>
                    Período: <?php echo htmlspecialchars($period); ?>
                    <span class="ml-4 bg-primary text-white px-3 py-1 rounded-full text-sm">
                        <?php echo count($courses); ?> cursos
                    </span>
                </h3>

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
                            <?php foreach ($courses as $course): ?>
                                <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($course['code']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($course['name']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?php echo $course['credits']; ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php if ($course['grade'] !== null): ?>
                                            <span class="font-bold <?php echo $course['grade'] >= 10 ? 'text-green-600' : 'text-red-600'; ?>">
                                                <?php echo $course['grade']; ?>/20
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-500 italic">Sin calificar</span>
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
                                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                            <?php
                                            if ($course['status'] == 'completed') echo 'bg-green-100 text-green-800';
                                            elseif ($course['status'] == 'enrolled') echo 'bg-blue-100 text-blue-800';
                                            else echo 'bg-gray-100 text-gray-800';
                                            ?>">
                                            <?php echo ucfirst($course['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Period Summary -->
                <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-3">Resumen del Período</h4>
                    <div class="grid md:grid-cols-4 gap-4 text-center">
                        <?php
                        $period_approved = count(array_filter($courses, function($c) { return $c['grade'] >= 10; }));
                        $period_failed = count(array_filter($courses, function($c) { return $c['grade'] < 10 && $c['grade'] !== null; }));
                        $period_credits = array_sum(array_column($courses, 'credits'));
                        $period_gpa = 0;
                        $graded_period_courses = array_filter($courses, function($c) { return $c['grade'] !== null; });
                        if (!empty($graded_period_courses)) {
                            $period_gpa = round(array_sum(array_map(function($c) { return $c['grade'] * $c['credits']; }, $graded_period_courses)) / array_sum(array_column($graded_period_courses, 'credits')), 2);
                        }
                        ?>
                        <div>
                            <p class="text-2xl font-bold text-green-600"><?php echo $period_approved; ?></p>
                            <p class="text-sm text-gray-600">Aprobados</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-red-600"><?php echo $period_failed; ?></p>
                            <p class="text-sm text-gray-600">Reprobados</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-blue-600"><?php echo $period_credits; ?></p>
                            <p class="text-sm text-gray-600">Créditos</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-primary"><?php echo $period_gpa; ?></p>
                            <p class="text-sm text-gray-600">Promedio</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($history)): ?>
        <div class="bg-white p-12 rounded-2xl shadow-xl text-center animate-fade-in-up">
            <div class="text-6xl text-gray-300 mb-4">
                <i class="fas fa-book-open"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-4">No hay historial académico</h3>
            <p class="text-gray-600">Aún no tienes cursos registrados en tu historial académico.</p>
        </div>
    <?php endif; ?>
</main>

<?php include '../../templates/footer.php'; ?>