<?php require_once '../../includes/config.php'; ?>
<?php include '../../templates/header.php'; ?>

<?php
// Handle AJAX requests for loan/return actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
        exit;
    }

    if ($_POST['action'] == 'loan') {
        $resource_id = (int)$_POST['resource_id'];
        $user_id = $_SESSION['user_id'];

        // Check if user is student
        if (getUserRole() != 'student') {
            echo json_encode(['success' => false, 'message' => 'Solo estudiantes pueden pedir préstamos']);
            exit;
        }

        // Check if resource is already loaned by this user
        $check_stmt = $pdo->prepare("SELECT id FROM loans WHERE resource_id = ? AND user_id = ? AND status IN ('active', 'overdue')");
        $check_stmt->execute([$resource_id, $user_id]);
        if ($check_stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Ya tiene este recurso prestado']);
            exit;
        }

        // Check if resource is available (not loaned by others)
        $check_stmt = $pdo->prepare("SELECT id FROM loans WHERE resource_id = ? AND status IN ('active', 'overdue')");
        $check_stmt->execute([$resource_id]);
        if ($check_stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Este recurso ya está prestado']);
            exit;
        }

        // Check loan limit (max 3 active loans per student)
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ? AND status IN ('active', 'overdue')");
        $count_stmt->execute([$user_id]);
        if ($count_stmt->fetchColumn() >= 3) {
            echo json_encode(['success' => false, 'message' => 'Ha alcanzado el límite máximo de préstamos (3)']);
            exit;
        }

        // Create loan (7 days from now)
        $due_date = date('Y-m-d H:i:s', strtotime('+7 days'));
        $stmt = $pdo->prepare("INSERT INTO loans (resource_id, user_id, due_date) VALUES (?, ?, ?)");
        $stmt->execute([$resource_id, $user_id, $due_date]);

        echo json_encode(['success' => true, 'message' => 'Préstamo creado', 'due_date' => date('d/m/Y', strtotime($due_date))]);
        exit;
    }

    if ($_POST['action'] == 'return') {
        $loan_id = (int)$_POST['loan_id'];
        $user_id = $_SESSION['user_id'];

        // Check if loan belongs to user
        $check_stmt = $pdo->prepare("SELECT id FROM loans WHERE id = ? AND user_id = ? AND status IN ('active', 'overdue')");
        $check_stmt->execute([$loan_id, $user_id]);
        if (!$check_stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Préstamo no encontrado']);
            exit;
        }

        // Return loan
        $stmt = $pdo->prepare("UPDATE loans SET status = 'returned', return_date = NOW() WHERE id = ?");
        $stmt->execute([$loan_id]);

        echo json_encode(['success' => true, 'message' => 'Recurso devuelto']);
        exit;
    }

    if ($_POST['action'] == 'admin_return') {
        if (getUserRole() != 'admin') {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            exit;
        }

        $loan_id = (int)$_POST['loan_id'];

        // Return loan
        $stmt = $pdo->prepare("UPDATE loans SET status = 'returned', return_date = NOW() WHERE id = ?");
        $stmt->execute([$loan_id]);

        echo json_encode(['success' => true, 'message' => 'Préstamo marcado como devuelto']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

// Update overdue loans
$pdo->exec("UPDATE loans SET status = 'overdue' WHERE due_date < NOW() AND status = 'active'");

// Get user's loans
$user_loans = [];
if (isLoggedIn()) {
    $stmt = $pdo->prepare("
        SELECT l.*, lr.title, lr.author, lr.type
        FROM loans l
        JOIN library_resources lr ON l.resource_id = lr.id
        WHERE l.user_id = ?
        ORDER BY l.loan_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all loans for admin
$all_loans = [];
if (isLoggedIn() && getUserRole() == 'admin') {
    $stmt = $pdo->query("
        SELECT l.*, lr.title, lr.author, lr.type, u.name as user_name
        FROM loans l
        JOIN library_resources lr ON l.resource_id = lr.id
        JOIN users u ON l.user_id = u.id
        ORDER BY l.loan_date DESC
    ");
    $all_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-hand-holding mr-4 text-primary"></i>
        Gestión de Préstamos
    </h2>

    <?php if (isLoggedIn() && getUserRole() == 'student'): ?>
        <!-- Student Loans Section -->
        <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-book-reader mr-2 text-primary"></i>
                Mis Préstamos (<?php echo count($user_loans); ?>)
            </h3>

            <?php if (!empty($user_loans)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Recurso</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Tipo</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Fecha Préstamo</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Fecha Devolución</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estado</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($user_loans as $loan): ?>
                                <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($loan['title']); ?>
                                        <?php if ($loan['author']): ?>
                                            <br><small class="text-gray-500">por <?php echo htmlspecialchars($loan['author']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <span class="px-2 py-1 text-xs rounded-full
                                            <?php echo $loan['type'] == 'book' ? 'bg-blue-100 text-blue-800' :
                                                     ($loan['type'] == 'article' ? 'bg-green-100 text-green-800' :
                                                      ($loan['type'] == 'video' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800')); ?>">
                                            <?php echo ucfirst($loan['type']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($loan['loan_date'])); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($loan['due_date'])); ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full
                                            <?php echo $loan['status'] == 'active' ? 'bg-green-100 text-green-800' :
                                                     ($loan['status'] == 'returned' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo $loan['status'] == 'active' ? 'Activo' : ($loan['status'] == 'returned' ? 'Devuelto' : 'Vencido'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php if ($loan['status'] == 'active' || $loan['status'] == 'overdue'): ?>
                                            <button onclick="returnLoan(<?php echo $loan['id']; ?>)" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200">
                                                <i class="fas fa-undo mr-1"></i>
                                                Devolver
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="text-6xl text-gray-300 mb-4">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">No tiene préstamos activos</h3>
                    <p class="text-gray-600">Visite la biblioteca para pedir recursos prestados.</p>
                    <a href="index.php" class="mt-4 inline-block bg-primary hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                        <i class="fas fa-search mr-2"></i>
                        Explorar Biblioteca
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (isLoggedIn() && getUserRole() == 'admin'): ?>
        <!-- Admin Loans Management Section -->
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-cogs mr-2 text-primary"></i>
                Todos los Préstamos (<?php echo count($all_loans); ?>)
            </h3>

            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Usuario</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Recurso</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Tipo</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Fecha Préstamo</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Fecha Devolución</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estado</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($all_loans as $loan): ?>
                            <tr class="hover:bg-gray-50 transition duration-200">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($loan['user_name']); ?></td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($loan['title']); ?>
                                    <?php if ($loan['author']): ?>
                                        <br><small class="text-gray-500">por <?php echo htmlspecialchars($loan['author']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        <?php echo $loan['type'] == 'book' ? 'bg-blue-100 text-blue-800' :
                                                 ($loan['type'] == 'article' ? 'bg-green-100 text-green-800' :
                                                  ($loan['type'] == 'video' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800')); ?>">
                                        <?php echo ucfirst($loan['type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($loan['loan_date'])); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($loan['due_date'])); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        <?php echo $loan['status'] == 'active' ? 'bg-green-100 text-green-800' :
                                                 ($loan['status'] == 'returned' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'); ?>">
                                        <?php echo $loan['status'] == 'active' ? 'Activo' : ($loan['status'] == 'returned' ? 'Devuelto' : 'Vencido'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex space-x-2">
                                        <?php if ($loan['status'] == 'active' || $loan['status'] == 'overdue'): ?>
                                            <button onclick="markAsReturned(<?php echo $loan['id']; ?>)" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200">
                                                <i class="fas fa-check mr-1"></i>
                                                Marcar Devuelto
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="viewLoanDetails(<?php echo $loan['id']; ?>)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200">
                                            <i class="fas fa-eye mr-1"></i>
                                            Detalles
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
function returnLoan(loanId) {
    if (confirm('¿Está seguro de que desea devolver este recurso?')) {
        fetch('loans.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=return&loan_id=' + loanId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Recurso devuelto exitosamente.');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }
}

function markAsReturned(loanId) {
    if (confirm('¿Marcar este préstamo como devuelto?')) {
        fetch('loans.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=admin_return&loan_id=' + loanId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Préstamo marcado como devuelto.');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }
}

function viewLoanDetails(loanId) {
    // This would open a modal with loan details
    alert('Funcionalidad de detalles próximamente disponible. ID del préstamo: ' + loanId);
}
</script>

<?php include '../../templates/footer.php'; ?>