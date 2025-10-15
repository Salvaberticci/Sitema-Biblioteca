<?php require_once '../../includes/config.php'; ?>
<?php include '../../templates/header.php'; ?>

<?php
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$subject = isset($_GET['subject']) ? sanitize($_GET['subject']) : '';

$query = "SELECT * FROM library_resources WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (title LIKE ? OR author LIKE ? OR description LIKE ?)";
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

$query .= " ORDER BY upload_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique subjects and types for filters
$subjects_stmt = $pdo->query("SELECT DISTINCT subject FROM library_resources WHERE subject IS NOT NULL ORDER BY subject");
$subjects = $subjects_stmt->fetchAll(PDO::FETCH_COLUMN);

$types_stmt = $pdo->query("SELECT DISTINCT type FROM library_resources ORDER BY type");
$types = $types_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<main class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Biblioteca Virtual</h2>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-xl font-semibold mb-4">Buscar Recursos</h3>
        <form method="GET" class="grid md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Título, autor, descripción..." class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Tipo</label>
                <select id="type" name="type" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                    <option value="">Todos los tipos</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?php echo $t; ?>" <?php echo $type == $t ? 'selected' : ''; ?>><?php echo ucfirst($t); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700">Asignatura</label>
                <select id="subject" name="subject" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                    <option value="">Todas las asignaturas</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo $subject == $s ? 'selected' : ''; ?>><?php echo htmlspecialchars($s); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-primary hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition duration-300">Buscar</button>
            </div>
        </form>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($resources as $resource): ?>
            <div class="bg-white p-6 rounded-lg shadow-md card-hover">
                <h4 class="text-lg font-semibold text-primary mb-2"><?php echo htmlspecialchars($resource['title']); ?></h4>
                <p class="text-gray-600 mb-2"><strong>Autor:</strong> <?php echo htmlspecialchars($resource['author'] ?? 'N/A'); ?></p>
                <p class="text-gray-600 mb-2"><strong>Tipo:</strong> <?php echo ucfirst($resource['type']); ?></p>
                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($resource['description'] ?? '', 0, 100)); ?>...</p>
                <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" target="_blank" class="bg-secondary hover:bg-brown-700 text-white font-bold py-2 px-4 rounded transition duration-300 inline-block">Descargar</a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($resources)): ?>
        <div class="text-center py-8">
            <p class="text-gray-500">No se encontraron recursos que coincidan con los criterios de búsqueda.</p>
        </div>
    <?php endif; ?>
</main>

<?php include '../../templates/footer.php'; ?>