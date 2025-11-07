<?php require_once '../../includes/config.php'; ?>
<?php requireRole('admin'); ?>
<?php include '../../templates/header.php'; ?>

<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_course'])) {
        $code = sanitize($_POST['code']);
        $name = sanitize($_POST['name']);
        $credits = (int)$_POST['credits'];
        $description = sanitize($_POST['description']);

        $stmt = $pdo->prepare("INSERT INTO courses (code, name, credits, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$code, $name, $credits, $description]);
        $success = "Curso creado exitosamente.";
    } elseif (isset($_POST['update_course'])) {
        $id = (int)$_POST['id'];
        $code = sanitize($_POST['code']);
        $name = sanitize($_POST['name']);
        $credits = (int)$_POST['credits'];
        $description = sanitize($_POST['description']);

        $stmt = $pdo->prepare("UPDATE courses SET code = ?, name = ?, credits = ?, description = ? WHERE id = ?");
        $stmt->execute([$code, $name, $credits, $description, $id]);
        $success = "Curso actualizado exitosamente.";
    } elseif (isset($_POST['delete_course'])) {
        $id = (int)$_POST['id'];

        // Start transaction to ensure atomicity
        $pdo->beginTransaction();

        try {
            // Delete related records first to avoid foreign key constraints

            // Delete attendance records for this course
            $stmt = $pdo->prepare("DELETE FROM attendance WHERE course_id = ?");
            $stmt->execute([$id]);

            // Delete enrollments for this course
            $stmt = $pdo->prepare("DELETE FROM enrollments WHERE course_id = ?");
            $stmt->execute([$id]);

            // Delete activities for this course
            $stmt = $pdo->prepare("DELETE FROM activities WHERE course_id = ?");
            $stmt->execute([$id]);

            // Delete schedules for this course
            $stmt = $pdo->prepare("DELETE FROM schedules WHERE course_id = ?");
            $stmt->execute([$id]);

            // Finally, delete the course
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$id]);

            // Commit the transaction
            $pdo->commit();
            $success = "Curso eliminado exitosamente junto con todos sus registros relacionados.";
        } catch (Exception $e) {
            // Rollback on error
            $pdo->rollBack();
            $error = "Error al eliminar el curso: " . $e->getMessage();
        }
    }
}

// Fetch all courses
$stmt = $pdo->query("SELECT * FROM courses ORDER BY name");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-graduation-cap mr-4 text-primary"></i>
        Gestión de Cursos
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
            <i class="fas fa-plus-circle mr-2 text-primary"></i>
            Crear Nuevo Curso
        </h3>
        <form method="POST" class="grid md:grid-cols-2 gap-6">
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Código del Curso</label>
                <input type="text" id="code" name="code" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
            </div>
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nombre del Curso</label>
                <input type="text" id="name" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
            </div>
            <div>
                <label for="credits" class="block text-sm font-medium text-gray-700 mb-2">Unidades de Crédito</label>
                <input type="number" id="credits" name="credits" min="1" max="12" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
            </div>
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                <textarea id="description" name="description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"></textarea>
            </div>
            <div class="md:col-span-2">
                <button type="submit" name="create_course" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Crear Curso
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-list mr-2 text-primary"></i>
            Lista de Cursos
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Código</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Nombre</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Créditos</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Descripción</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($courses as $course): ?>
                        <tr class="hover:bg-gray-50 transition duration-200">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($course['code']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($course['name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo $course['credits']; ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars(substr($course['description'] ?? '', 0, 50)); ?>...</td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex space-x-2">
                                    <button onclick="editCourse(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars($course['code']); ?>', '<?php echo htmlspecialchars($course['name']); ?>', <?php echo $course['credits']; ?>, '<?php echo htmlspecialchars($course['description'] ?? ''); ?>')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200 flex items-center" title="Editar curso">
                                        <i class="fas fa-edit mr-1"></i>
                                        <span class="hidden sm:inline">Editar</span>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este curso?')">
                                        <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                                        <button type="submit" name="delete_course" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200 flex items-center" title="Eliminar curso">
                                            <i class="fas fa-trash mr-1"></i>
                                            <span class="hidden sm:inline">Eliminar</span>
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
</main>

<!-- Edit Course Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                <i class="fas fa-edit mr-2 text-primary"></i>
                Editar Curso
            </h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" id="edit_id" name="id">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_code" class="block text-sm font-medium text-gray-700 mb-2">Código del Curso</label>
                        <input type="text" id="edit_code" name="code" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    </div>
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">Nombre del Curso</label>
                        <input type="text" id="edit_name" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    </div>
                    <div>
                        <label for="edit_credits" class="block text-sm font-medium text-gray-700 mb-2">Unidades de Crédito</label>
                        <input type="number" id="edit_credits" name="credits" min="1" max="12" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    </div>
                    <div class="md:col-span-2">
                        <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                        <textarea id="edit_description" name="description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-200">Cancelar</button>
                    <button type="submit" name="update_course" class="bg-primary hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition duration-200">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCourse(id, code, name, credits, description) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_code').value = code;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_credits').value = credits;
    document.getElementById('edit_description').value = description;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>

<?php include '../../templates/footer.php'; ?>