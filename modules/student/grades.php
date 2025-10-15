<?php require_once '../../includes/config.php'; ?>
<?php requireRole('student'); ?>
<?php include '../../templates/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];

// Fetch student's enrollments with grades
$stmt = $pdo->prepare("
    SELECT c.name, c.code, c.credits, e.grade, e.status, e.period
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.student_id = ?
    ORDER BY e.period DESC, c.name
");
$stmt->execute([$user_id]);
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate GPA
$total_credits = 0;
$weighted_sum = 0;
foreach ($enrollments as $enrollment) {
    if ($enrollment['grade'] !== null && $enrollment['status'] == 'completed') {
        $total_credits += $enrollment['credits'];
        $weighted_sum += $enrollment['grade'] * $enrollment['credits'];
    }
}
$gpa = $total_credits > 0 ? round($weighted_sum / $total_credits, 2) : 0;
?>

<main class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Mis Notas</h2>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-xl font-semibold mb-4">Resumen Académico</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <p class="text-gray-600">Promedio General:</p>
                <p class="text-2xl font-bold text-primary"><?php echo $gpa; ?>/20</p>
            </div>
            <div>
                <p class="text-gray-600">Créditos Completados:</p>
                <p class="text-2xl font-bold text-secondary"><?php echo $total_credits; ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4">Historial de Notas</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left">Período</th>
                        <th class="px-4 py-2 text-left">Código</th>
                        <th class="px-4 py-2 text-left">Asignatura</th>
                        <th class="px-4 py-2 text-left">Créditos</th>
                        <th class="px-4 py-2 text-left">Nota</th>
                        <th class="px-4 py-2 text-left">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $enrollment): ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($enrollment['period']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($enrollment['code']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($enrollment['name']); ?></td>
                            <td class="px-4 py-2"><?php echo $enrollment['credits']; ?></td>
                            <td class="px-4 py-2">
                                <?php if ($enrollment['grade'] !== null): ?>
                                    <span class="<?php echo $enrollment['grade'] >= 10 ? 'text-green-600' : 'text-red-600'; ?> font-semibold">
                                        <?php echo $enrollment['grade']; ?>/20
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-500">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2"><?php echo ucfirst($enrollment['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>