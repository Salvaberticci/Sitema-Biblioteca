<?php require_once '../../includes/config.php'; ?>
<?php requireRole('admin'); ?>
<?php include '../../templates/header.php'; ?>

<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_user'])) {
        try {
            $username = sanitize($_POST['username']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $email = sanitize($_POST['email']);
            $name = sanitize($_POST['name']);
            $role = sanitize($_POST['role']);

            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, name, role) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([$username, $password, $email, $name, $role]);

            if ($result) {
                $success = "Usuario creado exitosamente.";
            } else {
                $error = "Error al crear el usuario.";
            }
        } catch (Exception $e) {
            $error = "Error al crear el usuario: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_user'])) {
        try {
            $id = (int) $_POST['id'];
            $username = sanitize($_POST['username']);
            $email = sanitize($_POST['email']);
            $name = sanitize($_POST['name']);
            $role = sanitize($_POST['role']);

            // Build query dynamically based on whether password is provided
            $updateFields = "username = ?, email = ?, name = ?, role = ?";
            $params = [$username, $email, $name, $role];

            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $updateFields .= ", password = ?";
                $params[] = $password;
            }

            $params[] = $id;

            $stmt = $pdo->prepare("UPDATE users SET $updateFields WHERE id = ?");
            $result = $stmt->execute($params);

            if ($result) {
                $success = "Usuario actualizado exitosamente.";
            } else {
                $error = "Error al actualizar el usuario.";
            }
        } catch (Exception $e) {
            $error = "Error al actualizar el usuario: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_user'])) {
        $id = (int) $_POST['id'];

        // Start transaction to ensure atomicity
        $pdo->beginTransaction();

        try {
            // Delete related records first to avoid foreign key constraints

            // Delete attendance records for this student
            $stmt = $pdo->prepare("DELETE FROM attendance WHERE student_id = ?");
            $stmt->execute([$id]);

            // Delete enrollments for this student
            $stmt = $pdo->prepare("DELETE FROM enrollments WHERE student_id = ?");
            $stmt->execute([$id]);

            // Delete submissions for this student
            $stmt = $pdo->prepare("DELETE FROM submissions WHERE student_id = ?");
            $stmt->execute([$id]);

            // Delete activities created by this teacher
            $stmt = $pdo->prepare("DELETE FROM activities WHERE teacher_id = ?");
            $stmt->execute([$id]);

            // Delete schedules for this teacher
            $stmt = $pdo->prepare("DELETE FROM schedules WHERE teacher_id = ?");
            $stmt->execute([$id]);

            // Delete teacher_course assignments for this teacher
            $stmt = $pdo->prepare("DELETE FROM teacher_courses WHERE teacher_id = ?");
            $stmt->execute([$id]);

            // Delete book loans for this user
            $stmt = $pdo->prepare("DELETE FROM book_loans WHERE user_id = ?");
            $stmt->execute([$id]);

            // Delete library resources uploaded by this user
            $stmt = $pdo->prepare("DELETE FROM library_resources WHERE uploaded_by = ?");
            $stmt->execute([$id]);

            // Finally, delete the user
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            // Commit the transaction
            $pdo->commit();
            $success = "Usuario eliminado exitosamente junto con todos sus registros relacionados.";
        } catch (Exception $e) {
            // Rollback on error
            $pdo->rollBack();
            $error = "Error al eliminar el usuario: " . $e->getMessage();
        }
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY name");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Gestión de Usuarios</h2>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Debug Information Panel - Commented out for production
    <div class="bg-gray-100 border border-gray-300 p-4 rounded mb-6">
        <h3 class="text-lg font-semibold mb-3 text-gray-800">🔍 Debug Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h4 class="font-medium text-gray-700">Request Info:</h4>
                <ul class="text-sm text-gray-600">
                    <li>Method: <code><?php echo htmlspecialchars($debug_info['request_method'] ?? 'Unknown'); ?></code></li>
                    <li>User Agent: <code><?php echo htmlspecialchars(substr($debug_info['user_agent'] ?? 'Unknown', 0, 50)); ?></code></li>
                    <li>Session: <?php echo isset($debug_info['session']) ? 'Active' : 'None'; ?></li>
                </ul>
            </div>
            <div>
                <h4 class="font-medium text-gray-700">Database:</h4>
                <ul class="text-sm text-gray-600">
                    <li>Connection: <span class="<?php echo strpos($debug_info['db_connection'] ?? '', 'successfully') !== false ? 'text-green-600' : 'text-red-600'; ?>"><?php echo htmlspecialchars($debug_info['db_connection'] ?? 'Unknown'); ?></span></li>
                    <li>Test Query: <?php echo ($debug_info['db_test_query'] ?? 'Unknown') === 'OK' ? '✅' : '❌'; ?></li>
                </ul>
            </div>
            <div>
                <h4 class="font-medium text-gray-700">POST Data:</h4>
                <pre class="text-xs bg-white p-2 rounded border overflow-auto max-h-32"><?php echo htmlspecialchars(print_r($debug_info['post_data'] ?? [], true)); ?></pre>
            </div>
        </div>

        <?php if (isset($debug_info['operation'])): ?>
        <div class="mt-4">
            <h4 class="font-medium text-gray-700">Operation: <?php echo htmlspecialchars($debug_info['operation']); ?></h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                <div>
                    <h5 class="text-sm font-medium text-gray-600">Input Data:</h5>
                    <pre class="text-xs bg-white p-2 rounded border overflow-auto max-h-24"><?php
                    $input_key = $debug_info['operation'] . '_data';
                    echo htmlspecialchars(print_r($debug_info[$input_key] ?? [], true));
                    ?></pre>
                </div>
                <div>
                    <h5 class="text-sm font-medium text-gray-600">Operation Status:</h5>
                    <span class="px-2 py-1 rounded text-xs font-medium <?php
                    echo match ($debug_info['operation_status'] ?? '') {
                        'success' => 'bg-green-100 text-green-800',
                        'failed' => 'bg-red-100 text-red-800',
                        'exception' => 'bg-yellow-100 text-yellow-800',
                        default => 'bg-gray-100 text-gray-800'
                    };
                    ?>">
                        <?php echo htmlspecialchars($debug_info['operation_status'] ?? 'unknown'); ?>
                    </span>
                    <?php if (isset($debug_info['exception_message'])): ?>
                        <p class="text-xs text-red-600 mt-1">Exception: <?php echo htmlspecialchars($debug_info['exception_message']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($debug_info['delete_steps'])): ?>
            <div class="mt-4">
                <h5 class="text-sm font-medium text-gray-600">Delete Steps:</h5>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs bg-white border rounded">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-2 py-1 border">Table</th>
                                <th class="px-2 py-1 border">SQL</th>
                                <th class="px-2 py-1 border">Result</th>
                                <th class="px-2 py-1 border">Affected Rows</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($debug_info['delete_steps'] as $table => $step): ?>
                            <tr>
                                <td class="px-2 py-1 border"><?php echo htmlspecialchars($table); ?></td>
                                <td class="px-2 py-1 border"><?php echo htmlspecialchars($step['sql']); ?></td>
                                <td class="px-2 py-1 border"><?php echo $step['result'] ? '✅' : '❌'; ?></td>
                                <td class="px-2 py-1 border"><?php echo htmlspecialchars($step['affected']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($debug_info['sql_query'])): ?>
            <div class="mt-2">
                <h5 class="text-sm font-medium text-gray-600">SQL Query:</h5>
                <code class="text-xs bg-white p-1 rounded border block"><?php echo htmlspecialchars($debug_info['sql_query']); ?></code>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    -->

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-xl font-semibold mb-4">Crear Nuevo Usuario</h3>
        <form method="POST" class="grid md:grid-cols-2 gap-4">
            <input type="hidden" name="create_user" value="1">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Usuario</label>
                <input type="text" id="username" name="username" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" id="password" name="password" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                <input type="text" id="name" name="name" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Rol</label>
                <select id="role" name="role" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                    <option value="student">Estudiante</option>
                    <option value="teacher">Docente</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <button type="submit" name="create_user"
                    class="bg-primary hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition duration-300">Crear
                    Usuario</button>
            </div>
        </form>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4">Lista de Usuarios</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left">Nombre</th>
                        <th class="px-4 py-2 text-left">Usuario</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Rol</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-4 py-2"><?php echo ucfirst($user['role']); ?></td>
                            <td class="px-4 py-2">
                                <button
                                    onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['name']); ?>', '<?php echo $user['role']; ?>')"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-sm mr-2">Editar</button>
                                <form method="POST" class="inline"
                                    onsubmit="return confirm('¿Está seguro de eliminar este usuario?')">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="delete_user" value="1">
                                    <button type="submit"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-sm">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Edit User Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Usuario</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="update_user" value="1">
                <input type="hidden" id="edit_id" name="id">
                <div>
                    <label for="edit_username" class="block text-sm font-medium text-gray-700">Usuario</label>
                    <input type="text" id="edit_username" name="username" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label for="edit_email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="edit_email" name="email"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label for="edit_name" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                    <input type="text" id="edit_name" name="name" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label for="edit_password" class="block text-sm font-medium text-gray-700">Nueva Contraseña (dejar
                        vacío para mantener la actual)</label>
                    <input type="password" id="edit_password" name="password"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label for="edit_role" class="block text-sm font-medium text-gray-700">Rol</label>
                    <select id="edit_role" name="role" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="student">Estudiante</option>
                        <option value="teacher">Docente</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal()"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancelar</button>
                    <button type="submit" name="update_user"
                        class="bg-primary hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editUser(id, username, email, name, role) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_password').value = ''; // Clear password field
        document.getElementById('edit_role').value = role;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>

<?php include '../../templates/footer.php'; ?>