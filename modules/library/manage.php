<?php require_once '../../includes/config.php'; ?>
<?php requireRole('admin'); ?>
<?php include '../../templates/header.php'; ?>

<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['upload_resource'])) {
        $title = sanitize($_POST['title']);
        $author = sanitize($_POST['author']);
        $type = sanitize($_POST['type']);
        $subject = sanitize($_POST['subject']);
        $description = sanitize($_POST['description']);

        // Handle file upload
        $file_path = '';
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $upload_dir = '../../uploads/library/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = time() . '_' . basename($_FILES['file']['name']);
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                $file_path = 'uploads/library/' . $file_name;
            } else {
                $error = "Error al subir el archivo.";
            }
        }

        if (!isset($error)) {
            $stmt = $pdo->prepare("INSERT INTO library_resources (title, author, type, subject, description, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $author, $type, $subject, $description, $file_path, $_SESSION['user_id']]);
            $success = "Recurso subido exitosamente.";
        }
    } elseif (isset($_POST['delete_resource'])) {
        $id = (int)$_POST['id'];

        // Get file path to delete physical file
        $stmt = $pdo->prepare("SELECT file_path FROM library_resources WHERE id = ?");
        $stmt->execute([$id]);
        $resource = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resource && $resource['file_path']) {
            $full_path = '../../' . $resource['file_path'];
            if (file_exists($full_path)) {
                unlink($full_path);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM library_resources WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Recurso eliminado exitosamente.";
    }
}

// Fetch all resources
$stmt = $pdo->query("SELECT lr.*, u.name as uploader_name FROM library_resources lr LEFT JOIN users u ON lr.uploaded_by = u.id ORDER BY lr.upload_date DESC");
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-books mr-4 text-primary"></i>
        Gestión de Biblioteca Virtual
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

    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-upload mr-2 text-primary"></i>
            Subir Nuevo Recurso
        </h3>
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
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
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Tipo de Recurso</label>
                    <select id="type" name="type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        <option value="book">Libro</option>
                        <option value="article">Artículo</option>
                        <option value="video">Video</option>
                        <option value="document">Documento</option>
                    </select>
                </div>
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Asignatura</label>
                    <input type="text" id="subject" name="subject" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                <textarea id="description" name="description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"></textarea>
            </div>
            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Archivo</label>
                <input type="file" id="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.avi,.jpg,.png,.gif" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                <p class="text-sm text-gray-500 mt-1">Formatos permitidos: PDF, DOC, PPT, MP4, imágenes</p>
            </div>
            <div>
                <button type="submit" name="upload_resource" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>
                    Subir Recurso
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-list mr-2 text-primary"></i>
            Recursos en Biblioteca (<?php echo count($resources); ?>)
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Título</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Autor</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Tipo</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Asignatura</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Subido por</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Fecha</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($resources as $resource): ?>
                        <tr class="hover:bg-gray-50 transition duration-200">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($resource['title']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($resource['author'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="px-2 py-1 text-xs rounded-full <?php
                                    echo $resource['type'] == 'book' ? 'bg-blue-100 text-blue-800' :
                                         ($resource['type'] == 'article' ? 'bg-green-100 text-green-800' :
                                          ($resource['type'] == 'video' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800'));
                                ?>">
                                    <?php echo ucfirst($resource['type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($resource['subject'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($resource['uploader_name'] ?? 'Sistema'); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($resource['upload_date'])); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <?php if ($resource['file_path']): ?>
                                    <a href="/biblioteca/<?php echo htmlspecialchars($resource['file_path']); ?>" target="_blank" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded text-xs mr-2 transition duration-200">
                                        <i class="fas fa-download"></i>
                                    </a>
                                <?php endif; ?>
                                <form method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este recurso?')">
                                    <input type="hidden" name="id" value="<?php echo $resource['id']; ?>">
                                    <button type="submit" name="delete_resource" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded text-xs transition duration-200">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>