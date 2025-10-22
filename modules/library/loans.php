<?php require_once '../../includes/config.php'; ?>
<?php include '../../templates/header.php'; ?>

<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Generate CSRF token if not exists
    generateCSRFToken();

    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = "Error de seguridad. Intente nuevamente.";
    } else {
        if (isset($_POST['add_book'])) {
            $title = sanitize($_POST['title']);
            $author = sanitize($_POST['author']);
            $isbn = sanitize($_POST['isbn']);
            $category = sanitize($_POST['category']);
            $description = sanitize($_POST['description']);
            $total_copies = (int)$_POST['total_copies'];
            $location = sanitize($_POST['location']);

            if (!isset($error)) {
                $stmt = $pdo->prepare("INSERT INTO physical_books (title, author, isbn, category, description, total_copies, available_copies, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $author, $isbn, $category, $description, $total_copies, $total_copies, $location]);
                $success = "Libro físico agregado exitosamente.";
                regenerateCSRFToken();
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=add_book");
                exit();
            }
        } elseif (isset($_POST['loan_book'])) {
            $book_id = (int)$_POST['book_id'];
            $user_id = $_SESSION['user_id'];

            // Check if user is student
            if (getUserRole() != 'student') {
                $error = "Solo estudiantes pueden pedir préstamos";
            } else {
                // Check if book is available
                $book_stmt = $pdo->prepare("SELECT available_copies FROM physical_books WHERE id = ? AND status = 'available'");
                $book_stmt->execute([$book_id]);
                $book = $book_stmt->fetch(PDO::FETCH_ASSOC);

                if (!$book || $book['available_copies'] <= 0) {
                    $error = "Este libro no está disponible";
                } else {
                    // Check if user already has this book loaned
                    $check_stmt = $pdo->prepare("SELECT id FROM book_loans WHERE book_id = ? AND user_id = ? AND status IN ('active', 'overdue')");
                    $check_stmt->execute([$book_id, $user_id]);
                    if ($check_stmt->fetch()) {
                        $error = "Ya tienes este libro prestado";
                    } else {
                        // Check loan limit (max 3 active loans per student)
                        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM book_loans WHERE user_id = ? AND status IN ('active', 'overdue')");
                        $count_stmt->execute([$user_id]);
                        if ($count_stmt->fetchColumn() >= 3) {
                            $error = "Ha alcanzado el límite máximo de préstamos (3)";
                        } else {
                            // Create loan (14 days from now for physical books)
                            $due_date = date('Y-m-d H:i:s', strtotime('+14 days'));
                            $stmt = $pdo->prepare("INSERT INTO book_loans (book_id, user_id, due_date) VALUES (?, ?, ?)");
                            $stmt->execute([$book_id, $user_id, $due_date]);

                            // Update available copies
                            $pdo->prepare("UPDATE physical_books SET available_copies = available_copies - 1 WHERE id = ?")->execute([$book_id]);

                            $success = "Libro prestado exitosamente. Fecha de devolución: " . date('d/m/Y', strtotime($due_date));
                            regenerateCSRFToken();
                            header("Location: " . $_SERVER['PHP_SELF'] . "?success=loan");
                            exit();
                        }
                    }
                }
            }
        } elseif (isset($_POST['return_book'])) {
            $loan_id = (int)$_POST['loan_id'];

            // Get book_id from loan
            $loan_stmt = $pdo->prepare("SELECT book_id FROM book_loans WHERE id = ?");
            $loan_stmt->execute([$loan_id]);
            $loan = $loan_stmt->fetch(PDO::FETCH_ASSOC);

            if ($loan) {
                // Return loan
                $stmt = $pdo->prepare("UPDATE book_loans SET status = 'returned', return_date = NOW() WHERE id = ?");
                $stmt->execute([$loan_id]);

                // Update available copies
                $pdo->prepare("UPDATE physical_books SET available_copies = available_copies + 1 WHERE id = ?")->execute([$loan['book_id']]);

                $success = "Libro devuelto exitosamente.";
                regenerateCSRFToken();
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=return");
                exit();
            } else {
                $error = "Préstamo no encontrado";
            }
        } elseif (isset($_POST['delete_book'])) {
            $book_id = (int)$_POST['book_id'];

            // Check if book has active loans
            $loan_check = $pdo->prepare("SELECT COUNT(*) FROM book_loans WHERE book_id = ? AND status IN ('active', 'overdue')");
            $loan_check->execute([$book_id]);
            if ($loan_check->fetchColumn() > 0) {
                $error = "No se puede eliminar un libro con préstamos activos";
            } else {
                $stmt = $pdo->prepare("DELETE FROM physical_books WHERE id = ?");
                $stmt->execute([$book_id]);
                $success = "Libro eliminado exitosamente.";
                regenerateCSRFToken();
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=delete");
                exit();
            }
        }
    }
}

// Handle success messages from redirects
$success_message = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'add_book') {
        $success_message = "Libro físico agregado exitosamente.";
    } elseif ($_GET['success'] == 'loan') {
        $success_message = "Libro prestado exitosamente.";
    } elseif ($_GET['success'] == 'return') {
        $success_message = "Libro devuelto exitosamente.";
    } elseif ($_GET['success'] == 'delete') {
        $success_message = "Libro eliminado exitosamente.";
    }
}

// Update overdue loans
$pdo->exec("UPDATE book_loans SET status = 'overdue' WHERE due_date < NOW() AND status = 'active'");

// Get all physical books
$books = $pdo->query("SELECT * FROM physical_books ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get user's loans
$user_loans = [];
if (isLoggedIn()) {
    $stmt = $pdo->prepare("
        SELECT bl.*, pb.title, pb.author, pb.isbn
        FROM book_loans bl
        JOIN physical_books pb ON bl.book_id = pb.id
        WHERE bl.user_id = ?
        ORDER BY bl.loan_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all loans for admin
$all_loans = [];
if (isLoggedIn() && getUserRole() == 'admin') {
    $stmt = $pdo->query("
        SELECT bl.*, pb.title, pb.author, pb.isbn, u.name as user_name
        FROM book_loans bl
        JOIN physical_books pb ON bl.book_id = pb.id
        JOIN users u ON bl.user_id = u.id
        ORDER BY bl.loan_date DESC
    ");
    $all_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-book mr-4 text-primary"></i>
        Gestión de Libros Físicos
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

    <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 animate-fade-in-up" id="success-message">
            <?php echo $success_message; ?>
        </div>
        <script>
            // Auto-hide success message after 5 seconds and clean URL
            setTimeout(function() {
                const messageDiv = document.getElementById('success-message');
                if (messageDiv) {
                    messageDiv.style.transition = 'opacity 0.5s';
                    messageDiv.style.opacity = '0';
                    setTimeout(function() {
                        messageDiv.remove();
                        // Clean URL by removing success parameter
                        const url = new URL(window.location);
                        url.searchParams.delete('success');
                        window.history.replaceState({}, '', url);
                    }, 500);
                }
            }, 5000);
        </script>
    <?php endif; ?>

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
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Libro</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Autor</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">ISBN</th>
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
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($loan['author'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php echo htmlspecialchars($loan['isbn'] ?? 'N/A'); ?>
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
                                            <form method="POST" class="inline" onsubmit="return confirm('¿Está seguro de que desea devolver este libro?')">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                                <button type="submit" name="return_book" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200">
                                                    <i class="fas fa-undo mr-1"></i>
                                                    Devolver
                                                </button>
                                            </form>
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
                    <p class="text-gray-600">Solicite préstamos de libros físicos disponibles.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Available Books for Loan -->
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-book mr-2 text-primary"></i>
                Libros Disponibles para Préstamo
            </h3>

            <?php if (!empty($books)): ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($books as $book): ?>
                        <?php if ($book['available_copies'] > 0 && $book['status'] == 'available'): ?>
                            <div class="bg-gray-50 p-6 rounded-xl border-2 border-gray-200 hover:border-primary transition duration-300">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($book['title']); ?></h4>
                                        <div class="text-sm text-gray-600 mb-2">
                                            <i class="fas fa-user mr-1"></i>
                                            <?php echo htmlspecialchars($book['author'] ?? 'N/A'); ?>
                                        </div>
                                        <?php if ($book['isbn']): ?>
                                            <div class="text-sm text-gray-600 mb-2">
                                                <i class="fas fa-hashtag mr-1"></i>
                                                ISBN: <?php echo htmlspecialchars($book['isbn']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($book['category']): ?>
                                            <div class="text-sm text-gray-600 mb-3">
                                                <i class="fas fa-tag mr-1"></i>
                                                <?php echo htmlspecialchars($book['category']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Disponible
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <?php echo $book['available_copies']; ?>/<?php echo $book['total_copies']; ?> copias
                                        </div>
                                    </div>
                                </div>

                                <?php if ($book['description']): ?>
                                    <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($book['description']); ?></p>
                                <?php endif; ?>

                                <form method="POST" onsubmit="return confirm('¿Solicitar préstamo de este libro? Tendrá 14 días para devolverlo.')">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" name="loan_book" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300 transform hover:scale-105 flex items-center justify-center">
                                        <i class="fas fa-hand-holding mr-3 text-xl"></i>
                                        <span>Solicitar Préstamo</span>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="text-6xl text-gray-300 mb-4">
                        <i class="fas fa-books"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">No hay libros disponibles</h3>
                    <p class="text-gray-600">No hay libros físicos registrados en el sistema.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (isLoggedIn() && getUserRole() == 'admin'): ?>
        <!-- Admin Book Management Section -->
        <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-plus-circle mr-2 text-primary"></i>
                Agregar Nuevo Libro Físico
            </h3>
            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Título</label>
                        <input type="text" id="title" name="title" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    </div>
                    <div>
                        <label for="author" class="block text-sm font-medium text-gray-700 mb-2">Autor</label>
                        <input type="text" id="author" name="author" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    </div>
                    <div>
                        <label for="isbn" class="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
                        <input type="text" id="isbn" name="isbn" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                        <input type="text" id="category" name="category" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    </div>
                    <div>
                        <label for="total_copies" class="block text-sm font-medium text-gray-700 mb-2">Total de Copias</label>
                        <input type="number" id="total_copies" name="total_copies" min="1" value="1" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    </div>
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Ubicación</label>
                        <input type="text" id="location" name="location" placeholder="Ej: Estante A-1" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    </div>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea id="description" name="description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"></textarea>
                </div>
                <div>
                    <button type="submit" name="add_book" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Agregar Libro
                    </button>
                </div>
            </form>
        </div>

        <!-- Books Inventory -->
        <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-books mr-2 text-primary"></i>
                Inventario de Libros Físicos (<?php echo count($books); ?>)
            </h3>

            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Título</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Autor</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">ISBN</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Categoría</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Copias</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Estado</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($books as $book): ?>
                            <tr class="hover:bg-gray-50 transition duration-200">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($book['title']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($book['author'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($book['category'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <span class="font-medium"><?php echo $book['available_copies']; ?>/<?php echo $book['total_copies']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        <?php echo $book['status'] == 'available' ? 'bg-green-100 text-green-800' :
                                                 ($book['status'] == 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                        <?php echo $book['status'] == 'available' ? 'Disponible' : ($book['status'] == 'maintenance' ? 'Mantenimiento' : 'Perdido'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex space-x-2">
                                        <button onclick="editBook(<?php echo $book['id']; ?>)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200">
                                            <i class="fas fa-edit mr-1"></i>
                                            Editar
                                        </button>
                                        <form method="POST" class="inline" onsubmit="return confirm('¿Eliminar este libro? Asegúrese de que no tenga préstamos activos.')">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                            <button type="submit" name="delete_book" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200">
                                                <i class="fas fa-trash mr-1"></i>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- All Loans Management Section -->
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <h3 class="text-xl font-semibold mb-6 flex items-center">
                <i class="fas fa-hand-holding mr-2 text-primary"></i>
                Todos los Préstamos (<?php echo count($all_loans); ?>)
            </h3>

            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Usuario</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Libro</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">ISBN</th>
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
                                <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($loan['isbn'] ?? 'N/A'); ?></td>
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
                                            <form method="POST" class="inline" onsubmit="return confirm('¿Marcar este préstamo como devuelto?')">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                                <button type="submit" name="return_book" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Marcar Devuelto
                                                </button>
                                            </form>
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
function editBook(bookId) {
    // This would open a modal to edit book details
    alert('Funcionalidad de edición próximamente disponible. ID del libro: ' + bookId);
}

function viewLoanDetails(loanId) {
    // This would open a modal with loan details
    alert('Funcionalidad de detalles próximamente disponible. ID del préstamo: ' + loanId);
}
</script>

<?php include '../../templates/footer.php'; ?>