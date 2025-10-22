<?php require_once '../../includes/config.php'; ?>
<?php include '../../templates/header.php'; ?>

<?php
// Handle success messages from redirects
$success_message = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'loan') {
        $success_message = 'Préstamo solicitado exitosamente.';
    } elseif ($_GET['success'] == 'return') {
        $success_message = 'Recurso devuelto exitosamente.';
    }
    // Clear the GET parameter by redirecting without it
    if (!empty($success_message)) {
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit();
    }
}
?>

<?php
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$subject = isset($_GET['subject']) ? sanitize($_GET['subject']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'recent';
$advanced_search = isset($_GET['advanced']) ? true : false;

// Advanced search parameters
$author_filter = isset($_GET['author']) ? sanitize($_GET['author']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$query = "SELECT lr.*, u.name as uploader_name FROM library_resources lr LEFT JOIN users u ON lr.uploaded_by = u.id WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (title LIKE ? OR author LIKE ? OR description LIKE ? OR subject LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($type) {
    $query .= " AND type = ?";
    $params[] = $type;
}

if ($subject) {
    $query .= " AND subject LIKE ?";
    $params[] = "%$subject%";
}

if ($advanced_search) {
    if ($author_filter) {
        $query .= " AND author LIKE ?";
        $params[] = "%$author_filter%";
    }

    if ($date_from) {
        $query .= " AND upload_date >= ?";
        $params[] = $date_from;
    }

    if ($date_to) {
        $query .= " AND upload_date <= ?";
        $params[] = $date_to;
    }
}

// Sorting options
switch ($sort) {
    case 'title':
        $query .= " ORDER BY title ASC";
        break;
    case 'author':
        $query .= " ORDER BY author ASC";
        break;
    case 'type':
        $query .= " ORDER BY type ASC";
        break;
    case 'oldest':
        $query .= " ORDER BY upload_date ASC";
        break;
    case 'recent':
    default:
        $query .= " ORDER BY upload_date DESC";
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique subjects and types for filters
$subjects_stmt = $pdo->query("SELECT DISTINCT subject FROM library_resources WHERE subject IS NOT NULL ORDER BY subject");
$subjects = $subjects_stmt->fetchAll(PDO::FETCH_COLUMN);

$types_stmt = $pdo->query("SELECT DISTINCT type FROM library_resources ORDER BY type");
$types = $types_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get statistics
$total_resources = $pdo->query("SELECT COUNT(*) FROM library_resources")->fetchColumn();
$resources_by_type = $pdo->query("SELECT type, COUNT(*) as count FROM library_resources GROUP BY type ORDER BY count DESC")->fetchAll(PDO::FETCH_ASSOC);
$recent_uploads = $pdo->query("SELECT COUNT(*) FROM library_resources WHERE upload_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-books mr-4 text-primary"></i>
        Biblioteca Virtual
    </h2>

    <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 animate-fade-in-up">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <!-- Library Statistics -->
    <div class="grid md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Recursos</p>
                    <p class="text-3xl font-bold text-primary"><?php echo number_format($total_resources); ?></p>
                </div>
                <div class="text-4xl text-primary opacity-70">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.1s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Subidas Recientes</p>
                    <p class="text-3xl font-bold text-green-500"><?php echo number_format($recent_uploads); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Últimos 30 días</p>
                </div>
                <div class="text-4xl text-green-500 opacity-70">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.2s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Tipos de Recursos</p>
                    <p class="text-3xl font-bold text-blue-500"><?php echo count($resources_by_type); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Categorías disponibles</p>
                </div>
                <div class="text-4xl text-blue-500 opacity-70">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up" style="animation-delay: 0.3s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Más Popular</p>
                    <p class="text-3xl font-bold text-purple-500"><?php echo isset($resources_by_type[0]) ? ucfirst($resources_by_type[0]['type']) : 'N/A'; ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?php echo isset($resources_by_type[0]) ? $resources_by_type[0]['count'] : 0; ?> recursos</p>
                </div>
                <div class="text-4xl text-purple-500 opacity-70">
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold flex items-center">
                <i class="fas fa-search mr-2 text-primary"></i>
                Buscar Recursos
            </h3>
            <button onclick="toggleAdvancedSearch()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center">
                <i class="fas fa-sliders-h mr-2"></i>
                Búsqueda Avanzada
            </button>
        </div>

        <form method="GET" class="space-y-6">
            <!-- Basic Search -->
            <div class="grid md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Título, autor, descripción, asignatura..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                    <select id="type" name="type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        <option value="">Todos los tipos</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?php echo $t; ?>" <?php echo $type == $t ? 'selected' : ''; ?>><?php echo ucfirst($t); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Asignatura</label>
                    <select id="subject" name="subject" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        <option value="">Todas las asignaturas</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $subject == $s ? 'selected' : ''; ?>><?php echo htmlspecialchars($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">Ordenar por</label>
                    <select id="sort" name="sort" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        <option value="recent" <?php echo $sort == 'recent' ? 'selected' : ''; ?>>Más recientes</option>
                        <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Más antiguos</option>
                        <option value="title" <?php echo $sort == 'title' ? 'selected' : ''; ?>>Título A-Z</option>
                        <option value="author" <?php echo $sort == 'author' ? 'selected' : ''; ?>>Autor A-Z</option>
                        <option value="type" <?php echo $sort == 'type' ? 'selected' : ''; ?>>Tipo</option>
                    </select>
                </div>
            </div>

            <!-- Advanced Search (Hidden by default) -->
            <div id="advanced-search" class="hidden border-t pt-6">
                <h4 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-filter mr-2 text-primary"></i>
                    Filtros Avanzados
                </h4>
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label for="author" class="block text-sm font-medium text-gray-700 mb-2">Autor específico</label>
                        <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($author_filter); ?>" placeholder="Nombre del autor" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    </div>
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Fecha desde</label>
                        <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Fecha hasta</label>
                        <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    </div>
                </div>
                <input type="hidden" name="advanced" value="1">
            </div>

            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <span><?php echo count($resources); ?> recursos encontrados</span>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                        <i class="fas fa-search mr-2"></i>
                        Buscar
                    </button>
                    <a href="index.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 flex items-center">
                        <i class="fas fa-times mr-2"></i>
                        Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Resources Grid -->
    <?php if (!empty($resources)): ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            <?php foreach ($resources as $resource): ?>
                <div class="bg-white p-6 rounded-2xl shadow-xl card-hover animate-fade-in-up">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h4 class="text-lg font-bold text-gray-800 mb-2 line-clamp-2"><?php echo htmlspecialchars($resource['title']); ?></h4>
                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                <i class="fas fa-user mr-1"></i>
                                <span><?php echo htmlspecialchars($resource['author'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                <i class="fas fa-tag mr-1"></i>
                                <span><?php echo ucfirst($resource['type']); ?></span>
                            </div>
                            <?php if ($resource['subject']): ?>
                                <div class="flex items-center text-sm text-gray-600 mb-3">
                                    <i class="fas fa-graduation-cap mr-1"></i>
                                    <span><?php echo htmlspecialchars($resource['subject']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php
                                switch($resource['type']) {
                                    case 'book': echo 'bg-blue-100 text-blue-800'; break;
                                    case 'article': echo 'bg-green-100 text-green-800'; break;
                                    case 'video': echo 'bg-red-100 text-red-800'; break;
                                    case 'document': echo 'bg-purple-100 text-purple-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <i class="fas fa-<?php
                                    switch($resource['type']) {
                                        case 'book': echo 'book'; break;
                                        case 'article': echo 'file-alt'; break;
                                        case 'video': echo 'video'; break;
                                        case 'document': echo 'file-pdf'; break;
                                        default: echo 'file';
                                    }
                                ?> mr-1"></i>
                                <?php echo ucfirst($resource['type']); ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($resource['description']): ?>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?php echo htmlspecialchars($resource['description']); ?></p>
                    <?php endif; ?>

                    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                        <span>
                            <i class="fas fa-calendar mr-1"></i>
                            <?php echo date('d/m/Y', strtotime($resource['upload_date'])); ?>
                        </span>
                        <?php if ($resource['uploader_name']): ?>
                            <span>
                                <i class="fas fa-user mr-1"></i>
                                <?php echo htmlspecialchars($resource['uploader_name']); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="flex space-x-2">
                        <?php
                        // Check if user has active loan for this resource
                        $loan_check = $pdo->prepare("SELECT id, status FROM loans WHERE resource_id = ? AND user_id = ? AND status IN ('active', 'overdue')");
                        $loan_check->execute([$resource['id'], $_SESSION['user_id'] ?? 0]);
                        $active_loan = $loan_check->fetch(PDO::FETCH_ASSOC);
                        ?>

                        <?php if ($active_loan): ?>
                            <?php if ($active_loan['status'] == 'active'): ?>
                                <button onclick="returnLoan(<?php echo $active_loan['id']; ?>)" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300 transform hover:scale-105 flex items-center justify-center text-lg">
                                    <i class="fas fa-undo mr-3 text-xl"></i>
                                    <span>Devolver</span>
                                </button>
                            <?php else: ?>
                                <span class="flex-1 bg-red-500 text-white font-bold py-3 px-6 rounded-lg flex items-center justify-center text-lg">
                                    <i class="fas fa-exclamation-triangle mr-3 text-xl"></i>
                                    <span>Vencido</span>
                                </span>
                            <?php endif; ?>
                        <?php elseif (isLoggedIn() && getUserRole() == 'student'): ?>
                            <button onclick="loanResource(<?php echo $resource['id']; ?>)" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300 transform hover:scale-105 flex items-center justify-center text-lg">
                                <i class="fas fa-hand-holding mr-3 text-xl"></i>
                                <span>Pedir Prestado</span>
                            </button>
                        <?php else: ?>
                            <?php if ($resource['file_path']): ?>
                                <a href="/biblioteca/<?php echo htmlspecialchars($resource['file_path']); ?>" target="_blank" class="flex-1 bg-gradient-to-r from-primary to-secondary text-white font-bold py-2 px-4 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center justify-center">
                                    <i class="fas fa-download mr-2"></i>
                                    <span>Descargar</span>
                                </a>
                            <?php else: ?>
                                <span class="flex-1 bg-gray-300 text-gray-500 font-bold py-2 px-4 rounded-lg flex items-center justify-center cursor-not-allowed">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <span>No disponible</span>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <button onclick="showResourceDetails(<?php echo $resource['id']; ?>)" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-3 rounded-lg transition duration-300" title="Ver detalles">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination (if needed in future) -->
        <div class="text-center">
            <p class="text-gray-600">Mostrando <?php echo count($resources); ?> recursos</p>
        </div>
    <?php else: ?>
        <div class="bg-white p-12 rounded-2xl shadow-xl text-center animate-fade-in-up">
            <div class="text-6xl text-gray-300 mb-4">
                <i class="fas fa-search"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-4">No se encontraron recursos</h3>
            <p class="text-gray-600 mb-6">No hay recursos que coincidan con los criterios de búsqueda especificados.</p>
            <div class="flex justify-center space-x-4">
                <a href="index.php" class="bg-primary hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300 flex items-center">
                    <i class="fas fa-times mr-2"></i>
                    Limpiar filtros
                </a>
                <?php if (isLoggedIn() && getUserRole() == 'admin'): ?>
                    <a href="manage.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Agregar recurso
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include '../../templates/footer.php'; ?>
<script>
function toggleAdvancedSearch() {
    const advancedSearch = document.getElementById('advanced-search');
    const button = event.target.closest('button');

    if (advancedSearch.classList.contains('hidden')) {
        advancedSearch.classList.remove('hidden');
        button.innerHTML = '<i class="fas fa-times mr-2"></i>Ocultar Avanzada';
        button.classList.remove('bg-gray-100', 'hover:bg-gray-200');
        button.classList.add('bg-primary', 'hover:bg-yellow-600', 'text-white');
    } else {
        advancedSearch.classList.add('hidden');
        button.innerHTML = '<i class="fas fa-sliders-h mr-2"></i>Búsqueda Avanzada';
        button.classList.remove('bg-primary', 'hover:bg-yellow-600', 'text-white');
        button.classList.add('bg-gray-100', 'hover:bg-gray-200');
    }
}

function showResourceDetails(resourceId) {
    // This would open a modal with full resource details
    // For now, just show an alert
    alert('Funcionalidad de detalles completa próximamente disponible. ID del recurso: ' + resourceId);
}

function loanResource(resourceId) {
    if (confirm('¿Está seguro de que desea pedir prestado este recurso? Tendrá 7 días para devolverlo.')) {
        fetch('loans.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=loan&resource_id=' + resourceId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Préstamo solicitado exitosamente. Fecha de devolución: ' + data.due_date);
                // Redirect to prevent form resubmission
                window.location.href = 'index.php?success=loan';
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
                // Redirect to prevent form resubmission
                window.location.href = 'index.php?success=return';
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
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-clamp: 2;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-clamp: 3;
}
</style>