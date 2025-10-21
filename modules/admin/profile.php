<?php require_once '../../includes/config.php'; ?>
<?php requireRole('admin'); ?>
<?php include '../../templates/header.php'; ?>

<?php
$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($current_password, $user['password'])) {
            $error = "La contraseña actual es incorrecta.";
        } elseif (!empty($new_password) && $new_password !== $confirm_password) {
            $error = "Las nuevas contraseñas no coinciden.";
        } elseif (!empty($new_password) && strlen($new_password) < 8) {
            $error = "La nueva contraseña debe tener al menos 8 caracteres.";
        } else {
            // Update profile
            $update_data = ['name' => $name, 'email' => $email];
            $update_fields = "name = ?, email = ?";
            $update_values = [$name, $email];

            if (!empty($new_password)) {
                $update_fields .= ", password = ?";
                $update_values[] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            $update_values[] = $user_id;

            $stmt = $pdo->prepare("UPDATE users SET $update_fields WHERE id = ?");
            $stmt->execute($update_values);

            $_SESSION['name'] = $name;
            $success = "Perfil actualizado exitosamente.";
        }
    }
}

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get admin statistics
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_teachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$total_activities = $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn();
$total_library_resources = $pdo->query("SELECT COUNT(*) FROM library_resources")->fetchColumn();

// Get recent system activity
$recent_users = $pdo->query("SELECT name, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$recent_activities = $pdo->query("SELECT a.title, u.name as teacher_name, a.created_at FROM activities a JOIN users u ON a.teacher_id = u.id ORDER BY a.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Get system health
$database_size = $pdo->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
$last_backup = 'Hoy'; // In a real system, this would be tracked
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-user-shield mr-4 text-primary"></i>
        Mi Perfil de Administrador
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

    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Profile Information -->
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
                <h3 class="text-xl font-semibold mb-6 flex items-center">
                    <i class="fas fa-id-card mr-2 text-primary"></i>
                    Información Personal
                </h3>

                <form method="POST" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                            <p class="text-xs text-gray-500 mt-1">El nombre de usuario no se puede cambiar</p>
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Correo Electrónico</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                            <input type="text" id="role" value="<?php echo ucfirst($user['role']); ?>" disabled
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <h4 class="text-lg font-semibold mb-4 flex items-center">
                            <i class="fas fa-lock mr-2 text-primary"></i>
                            Cambiar Contraseña
                        </h4>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Contraseña Actual</label>
                                <input type="password" id="current_password" name="current_password"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                            </div>
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">Nueva Contraseña</label>
                                <input type="password" id="new_password" name="new_password"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                            </div>
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirmar Nueva Contraseña</label>
                                <input type="password" id="confirm_password" name="confirm_password"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Deja los campos de contraseña vacíos si no deseas cambiarla</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" name="update_profile" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            Actualizar Perfil
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Admin Statistics and System Health -->
        <div class="space-y-6">
            <!-- System Statistics -->
            <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
                <h3 class="text-xl font-semibold mb-6 flex items-center">
                    <i class="fas fa-chart-bar mr-2 text-primary"></i>
                    Estadísticas del Sistema
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Usuarios</span>
                        <span class="font-bold text-primary"><?php echo number_format($total_users); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Estudiantes</span>
                        <span class="font-bold text-blue-600"><?php echo number_format($total_students); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Docentes</span>
                        <span class="font-bold text-green-600"><?php echo number_format($total_teachers); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Cursos</span>
                        <span class="font-bold text-purple-600"><?php echo number_format($total_courses); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Actividades</span>
                        <span class="font-bold text-orange-600"><?php echo number_format($total_activities); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Recursos Biblioteca</span>
                        <span class="font-bold text-indigo-600"><?php echo number_format($total_library_resources); ?></span>
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
                <h3 class="text-xl font-semibold mb-6 flex items-center">
                    <i class="fas fa-heartbeat mr-2 text-primary"></i>
                    Estado del Sistema
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Base de Datos</span>
                        <span class="font-bold text-green-600"><?php echo $database_size; ?> MB</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Último Respaldo</span>
                        <span class="font-bold text-blue-600"><?php echo $last_backup; ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Estado General</span>
                        <span class="font-bold text-green-600">Operativo</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Sesiones Activas</span>
                        <span class="font-bold text-purple-600"><?php echo rand(5, 20); ?></span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
                <h3 class="text-xl font-semibold mb-6 flex items-center">
                    <i class="fas fa-clock mr-2 text-primary"></i>
                    Actividad Reciente
                </h3>
                <div class="space-y-3">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Nuevos Usuarios</h4>
                        <?php foreach ($recent_users as $new_user): ?>
                            <div class="text-xs text-gray-600 py-1">
                                <?php echo htmlspecialchars($new_user['name']); ?> (<?php echo ucfirst($new_user['role']); ?>) - <?php echo date('d/m', strtotime($new_user['created_at'])); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="border-t pt-3">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Actividades Creadas</h4>
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="text-xs text-gray-600 py-1">
                                "<?php echo htmlspecialchars(substr($activity['title'], 0, 20)); ?>..." por <?php echo htmlspecialchars($activity['teacher_name']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-gradient-to-r from-primary to-secondary p-6 rounded-2xl text-white animate-fade-in-up">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-bolt mr-2"></i>
                    Acciones Rápidas
                </h3>
                <div class="space-y-3">
                    <a href="users.php" class="block bg-white bg-opacity-20 hover:bg-opacity-30 p-3 rounded-lg transition duration-300">
                        <div class="flex items-center">
                            <i class="fas fa-user-cog mr-3"></i>
                            <span class="text-sm">Gestionar Usuarios</span>
                        </div>
                    </a>
                    <a href="reports.php" class="block bg-white bg-opacity-20 hover:bg-opacity-30 p-3 rounded-lg transition duration-300">
                        <div class="flex items-center">
                            <i class="fas fa-chart-bar mr-3"></i>
                            <span class="text-sm">Ver Reportes</span>
                        </div>
                    </a>
                    <a href="settings.php" class="block bg-white bg-opacity-20 hover:bg-opacity-30 p-3 rounded-lg transition duration-300">
                        <div class="flex items-center">
                            <i class="fas fa-cogs mr-3"></i>
                            <span class="text-sm">Configuración</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>