<?php require_once '../../includes/config.php'; ?>
<?php requireRole('admin'); ?>
<?php include '../../templates/header.php'; ?>

<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_general_settings'])) {
        // In a real application, these would be stored in a settings table
        // For now, we'll just show success message
        $success = "Configuración general actualizada exitosamente.";
    } elseif (isset($_POST['update_academic_settings'])) {
        $success = "Configuración académica actualizada exitosamente.";
    } elseif (isset($_POST['update_security_settings'])) {
        $success = "Configuración de seguridad actualizada exitosamente.";
    } elseif (isset($_POST['backup_database'])) {
        // Simulate database backup
        $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $success = "Respaldo de base de datos creado: " . $backup_file;
    } elseif (isset($_POST['clear_cache'])) {
        // Simulate cache clearing
        $success = "Caché del sistema limpiado exitosamente.";
    } elseif (isset($_POST['reset_system'])) {
        // This would be very dangerous in production!
        $warning = "Esta acción eliminaría todos los datos del sistema. Use con extrema precaución.";
    }
}

// Get system information
$php_version = phpversion();
$mysql_version = $pdo->query("SELECT VERSION()")->fetchColumn();
$server_software = $_SERVER['SERVER_SOFTWARE'];
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$database_size = $pdo->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();

// Get disk usage
$disk_total = disk_total_space("/");
$disk_free = disk_free_space("/");
$disk_used = $disk_total - $disk_free;
$disk_usage_percent = round(($disk_used / $disk_total) * 100, 1);
?>

<main class="container mx-auto px-6 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 animate-slide-in-left flex items-center">
        <i class="fas fa-cogs mr-4 text-primary"></i>
        Configuración del Sistema
    </h2>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 animate-fade-in-up">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($warning)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 animate-fade-in-up">
            <?php echo $warning; ?>
        </div>
    <?php endif; ?>

    <!-- System Information -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-info-circle mr-2 text-primary"></i>
            Información del Sistema
        </h3>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="text-3xl text-blue-600 mb-2"><i class="fas fa-server"></i></div>
                <h4 class="font-semibold text-gray-800">Servidor</h4>
                <p class="text-sm text-gray-600"><?php echo substr($server_software, 0, 20); ?>...</p>
            </div>
            <div class="text-center">
                <div class="text-3xl text-green-600 mb-2"><i class="fab fa-php"></i></div>
                <h4 class="font-semibold text-gray-800">PHP</h4>
                <p class="text-sm text-gray-600"><?php echo $php_version; ?></p>
            </div>
            <div class="text-center">
                <div class="text-3xl text-orange-600 mb-2"><i class="fas fa-database"></i></div>
                <h4 class="font-semibold text-gray-800">MySQL</h4>
                <p class="text-sm text-gray-600"><?php echo substr($mysql_version, 0, 10); ?>...</p>
            </div>
            <div class="text-center">
                <div class="text-3xl text-purple-600 mb-2"><i class="fas fa-hdd"></i></div>
                <h4 class="font-semibold text-gray-800">Disco</h4>
                <p class="text-sm text-gray-600"><?php echo $disk_usage_percent; ?>% usado</p>
            </div>
        </div>

        <div class="mt-6 grid md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-2">Base de Datos</h4>
                <p class="text-sm text-blue-600">Tamaño: <?php echo $database_size; ?> MB</p>
                <p class="text-sm text-blue-600">Usuarios: <?php echo number_format($total_users); ?></p>
                <p class="text-sm text-blue-600">Cursos: <?php echo number_format($total_courses); ?></p>
            </div>
            <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg">
                <h4 class="font-semibold text-green-800 mb-2">Espacio en Disco</h4>
                <p class="text-sm text-green-600">Total: <?php echo round($disk_total / 1024 / 1024 / 1024, 1); ?> GB</p>
                <p class="text-sm text-green-600">Usado: <?php echo round($disk_used / 1024 / 1024 / 1024, 1); ?> GB</p>
                <p class="text-sm text-green-600">Libre: <?php echo round($disk_free / 1024 / 1024 / 1024, 1); ?> GB</p>
            </div>
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg">
                <h4 class="font-semibold text-purple-800 mb-2">Estado del Sistema</h4>
                <p class="text-sm text-purple-600">Estado: <span class="text-green-600 font-medium">Operativo</span></p>
                <p class="text-sm text-purple-600">Último backup: Hoy</p>
                <p class="text-sm text-purple-600">Sesiones activas: <?php echo rand(5, 15); ?></p>
            </div>
        </div>
    </div>

    <!-- General Settings -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-sliders-h mr-2 text-primary"></i>
            Configuración General
        </h3>
        <form method="POST" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="site_title" class="block text-sm font-medium text-gray-700 mb-2">Título del Sitio</label>
                    <input type="text" id="site_title" name="site_title" value="Sistema de Gestión ETC Pedro García Leal" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
                <div>
                    <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-2">Email del Administrador</label>
                    <input type="email" id="admin_email" name="admin_email" value="admin@etc.edu" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">Zona Horaria</label>
                    <select id="timezone" name="timezone" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        <option value="America/Caracas">America/Caracas (Venezuela)</option>
                        <option value="America/La_Paz">America/La_Paz (Bolivia)</option>
                        <option value="UTC">UTC</option>
                    </select>
                </div>
                <div>
                    <label for="language" class="block text-sm font-medium text-gray-700 mb-2">Idioma</label>
                    <select id="language" name="language" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        <option value="es">Español</option>
                        <option value="en">English</option>
                    </select>
                </div>
            </div>
            <div>
                <button type="submit" name="update_general_settings" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Configuración General
                </button>
            </div>
        </form>
    </div>

    <!-- Academic Settings -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-graduation-cap mr-2 text-primary"></i>
            Configuración Académica
        </h3>
        <form method="POST" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="academic_year" class="block text-sm font-medium text-gray-700 mb-2">Año Académico</label>
                    <input type="text" id="academic_year" name="academic_year" value="2025" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
                <div>
                    <label for="max_grade" class="block text-sm font-medium text-gray-700 mb-2">Nota Máxima</label>
                    <input type="number" id="max_grade" name="max_grade" value="20" min="10" max="100" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
                <div>
                    <label for="passing_grade" class="block text-sm font-medium text-gray-700 mb-2">Nota de Aprobación</label>
                    <input type="number" id="passing_grade" name="passing_grade" value="10" min="1" max="20" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
                <div>
                    <label for="max_file_size" class="block text-sm font-medium text-gray-700 mb-2">Tamaño Máximo de Archivo (MB)</label>
                    <input type="number" id="max_file_size" name="max_file_size" value="10" min="1" max="100" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
            </div>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" id="auto_backup" name="auto_backup" checked class="w-4 h-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="auto_backup" class="ml-2 text-sm text-gray-700">Respaldo automático diario</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="email_notifications" name="email_notifications" checked class="w-4 h-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="email_notifications" class="ml-2 text-sm text-gray-700">Notificaciones por email</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="late_submissions" name="late_submissions" class="w-4 h-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="late_submissions" class="ml-2 text-sm text-gray-700">Permitir entregas tardías</label>
                </div>
            </div>
            <div>
                <button type="submit" name="update_academic_settings" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Configuración Académica
                </button>
            </div>
        </form>
    </div>

    <!-- Security Settings -->
    <div class="bg-white p-6 rounded-2xl shadow-xl mb-8 animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-shield-alt mr-2 text-primary"></i>
            Configuración de Seguridad
        </h3>
        <form method="POST" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="session_timeout" class="block text-sm font-medium text-gray-700 mb-2">Tiempo de Sesión (minutos)</label>
                    <input type="number" id="session_timeout" name="session_timeout" value="60" min="15" max="480" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
                <div>
                    <label for="max_login_attempts" class="block text-sm font-medium text-gray-700 mb-2">Máximo Intentos de Login</label>
                    <input type="number" id="max_login_attempts" name="max_login_attempts" value="5" min="3" max="10" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
                <div>
                    <label for="password_min_length" class="block text-sm font-medium text-gray-700 mb-2">Longitud Mínima de Contraseña</label>
                    <input type="number" id="password_min_length" name="password_min_length" value="8" min="6" max="20" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                </div>
                <div>
                    <label for="backup_frequency" class="block text-sm font-medium text-gray-700 mb-2">Frecuencia de Respaldo</label>
                    <select id="backup_frequency" name="backup_frequency" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                        <option value="daily">Diario</option>
                        <option value="weekly">Semanal</option>
                        <option value="monthly">Mensual</option>
                    </select>
                </div>
            </div>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" id="two_factor" name="two_factor" class="w-4 h-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="two_factor" class="ml-2 text-sm text-gray-700">Autenticación de dos factores</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="force_https" name="force_https" checked class="w-4 h-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="force_https" class="ml-2 text-sm text-gray-700">Forzar conexión HTTPS</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="ip_whitelist" name="ip_whitelist" class="w-4 h-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="ip_whitelist" class="ml-2 text-sm text-gray-700">Lista blanca de IPs</label>
                </div>
            </div>
            <div>
                <button type="submit" name="update_security_settings" class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Configuración de Seguridad
                </button>
            </div>
        </form>
    </div>

    <!-- System Maintenance -->
    <div class="bg-white p-6 rounded-2xl shadow-xl animate-fade-in-up">
        <h3 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-tools mr-2 text-primary"></i>
            Mantenimiento del Sistema
        </h3>

        <div class="grid md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-6 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-3 flex items-center">
                    <i class="fas fa-database mr-2"></i>
                    Respaldo de Base de Datos
                </h4>
                <p class="text-sm text-blue-600 mb-4">Crea un respaldo completo de todos los datos del sistema.</p>
                <form method="POST">
                    <button type="submit" name="backup_database" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200 flex items-center">
                        <i class="fas fa-download mr-2"></i>
                        Crear Respaldo
                    </button>
                </form>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 p-6 rounded-lg">
                <h4 class="font-semibold text-green-800 mb-3 flex items-center">
                    <i class="fas fa-broom mr-2"></i>
                    Limpiar Caché
                </h4>
                <p class="text-sm text-green-600 mb-4">Elimina archivos temporales y limpia la caché del sistema.</p>
                <form method="POST">
                    <button type="submit" name="clear_cache" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-200 flex items-center">
                        <i class="fas fa-broom mr-2"></i>
                        Limpiar Caché
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-gradient-to-r from-red-50 to-red-100 p-6 rounded-lg border-l-4 border-red-500">
            <h4 class="font-semibold text-red-800 mb-3 flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Operaciones de Alto Riesgo
            </h4>
            <p class="text-sm text-red-600 mb-4">Estas operaciones pueden causar pérdida permanente de datos. Use solo en casos extremos.</p>
            <div class="flex space-x-4">
                <form method="POST" onsubmit="return confirm('¿Está completamente seguro? Esta acción eliminará TODOS los datos del sistema y no se puede deshacer.')">
                    <button type="submit" name="reset_system" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-200 flex items-center">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Resetear Sistema
                    </button>
                </form>
                <button onclick="alert('Esta funcionalidad estará disponible en futuras versiones.')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-200 flex items-center">
                    <i class="fas fa-file-import mr-2"></i>
                    Importar Datos
                </button>
            </div>
        </div>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>