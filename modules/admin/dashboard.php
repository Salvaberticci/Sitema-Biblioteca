<?php require_once '../../includes/config.php'; ?>
<?php requireRole('admin'); ?>
<?php include '../../templates/header.php'; ?>

<?php
// Get real statistics from database
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalLibraryResources = $pdo->query("SELECT COUNT(*) FROM library_resources")->fetchColumn();
$totalClassrooms = $pdo->query("SELECT COUNT(*) FROM classrooms")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalTeachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$totalActivities = $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn();
?>

<main class="container mx-auto px-6 py-12">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-primary to-secondary text-white p-8 rounded-2xl shadow-2xl mb-12 animate-bounce-in">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2 flex items-center">
                    <i class="fas fa-crown mr-4 text-yellow-300"></i>
                    Panel de Administración
                </h1>
                <p class="text-accent text-lg">Bienvenido, <?php echo htmlspecialchars($_SESSION['name']); ?> - Gestiona el sistema con poder total</p>
            </div>
            <div class="hidden md:block">
                <div class="text-6xl opacity-20">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div class="bg-white p-6 rounded-2xl shadow-xl card-hover animate-fade-in-up">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Usuarios</p>
                    <p class="text-3xl font-bold text-primary"><?php echo number_format($totalUsers); ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?php echo $totalStudents; ?> estudiantes, <?php echo $totalTeachers; ?> docentes</p>
                </div>
                <div class="text-4xl text-primary opacity-70">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl card-hover animate-fade-in-up" style="animation-delay: 0.1s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Cursos Activos</p>
                    <p class="text-3xl font-bold text-secondary"><?php echo number_format($totalCourses); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Programas académicos</p>
                </div>
                <div class="text-4xl text-secondary opacity-70">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl card-hover animate-fade-in-up" style="animation-delay: 0.2s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Recursos Biblioteca</p>
                    <p class="text-3xl font-bold text-accent"><?php echo number_format($totalLibraryResources); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Materiales digitales</p>
                </div>
                <div class="text-4xl text-accent opacity-70">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl card-hover animate-fade-in-up" style="animation-delay: 0.3s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Aulas Disponibles</p>
                    <p class="text-3xl font-bold text-dark"><?php echo number_format($totalClassrooms); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Espacios educativos</p>
                </div>
                <div class="text-4xl text-dark opacity-70">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row -->
    <div class="grid md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white p-6 rounded-2xl shadow-xl card-hover animate-fade-in-up" style="animation-delay: 0.4s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Actividades Creadas</p>
                    <p class="text-3xl font-bold text-purple-600"><?php echo number_format($totalActivities); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Tareas y evaluaciones</p>
                </div>
                <div class="text-4xl text-purple-600 opacity-70">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl card-hover animate-fade-in-up" style="animation-delay: 0.5s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Inscripciones Activas</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo number_format($pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'enrolled'")->fetchColumn()); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Estudiantes matriculados</p>
                </div>
                <div class="text-4xl text-green-600 opacity-70">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-xl card-hover animate-fade-in-up" style="animation-delay: 0.6s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Horarios Programados</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo number_format($pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn()); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Clases agendadas</p>
                </div>
                <div class="text-4xl text-blue-600 opacity-70">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Actions Grid -->
    <h2 class="text-3xl font-bold text-gray-800 mb-8 animate-slide-in-left flex items-center">
        <i class="fas fa-tools mr-4 text-primary"></i>
        Herramientas de Administración
    </h2>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <a href="users.php" class="group bg-gradient-to-br from-blue-500 to-blue-600 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-user-cog"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Gestión de Usuarios</h3>
                <p class="opacity-90">Crear, editar y gestionar usuarios del sistema con control total.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Administrar ahora</span>
                </div>
            </div>
        </a>

        <a href="courses.php" class="group bg-gradient-to-br from-green-500 to-green-600 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Gestión de Cursos</h3>
                <p class="opacity-90">Administrar cursos, asignaturas y programas académicos.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Configurar cursos</span>
                </div>
            </div>
        </a>

        <a href="reports.php" class="group bg-gradient-to-br from-purple-500 to-purple-600 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Reportes Avanzados</h3>
                <p class="opacity-90">Generar reportes académicos y administrativos detallados.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Ver reportes</span>
                </div>
            </div>
        </a>

        <a href="../library/manage.php" class="group bg-gradient-to-br from-yellow-500 to-orange-500 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-books"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Biblioteca Virtual</h3>
                <p class="opacity-90">Gestionar recursos digitales y catálogo bibliográfico.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Administrar biblioteca</span>
                </div>
            </div>
        </a>

        <a href="../schedules/manage.php" class="group bg-gradient-to-br from-red-500 to-pink-500 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Horarios de Aulas</h3>
                <p class="opacity-90">Administrar horarios y reservas de espacios educativos.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Gestionar horarios</span>
                </div>
            </div>
        </a>

        <a href="settings.php" class="group bg-gradient-to-br from-indigo-500 to-purple-600 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Configuración</h3>
                <p class="opacity-90">Configurar parámetros globales y ajustes del sistema.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Ajustes del sistema</span>
                </div>
            </div>
        </a>
    </div>

    <!-- Quick Actions -->
    <div class="mt-12 bg-white p-8 rounded-2xl shadow-xl animate-fade-in-up">
        <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
            <i class="fas fa-bolt mr-3 text-yellow-500"></i>
            Acciones Rápidas
        </h3>
        <div class="grid md:grid-cols-3 gap-6">
            <a href="users.php" class="bg-gradient-to-r from-primary to-secondary text-white p-4 rounded-xl hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center justify-center space-x-3">
                <i class="fas fa-plus-circle text-2xl"></i>
                <span>Crear Usuario</span>
            </a>
            <a href="../library/manage.php" class="bg-gradient-to-r from-green-500 to-teal-500 text-white p-4 rounded-xl hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center justify-center space-x-3">
                <i class="fas fa-upload text-2xl"></i>
                <span>Subir Recurso</span>
            </a>
            <button onclick="showSystemStats()" class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white p-4 rounded-xl hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center justify-center space-x-3">
                <i class="fas fa-chart-pie text-2xl"></i>
                <span>Ver Estadísticas</span>
            </button>
        </div>
    </div>

    <!-- System Stats Modal -->
    <div id="statsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-chart-bar mr-3 text-primary"></i>
                        Estadísticas del Sistema
                    </h3>
                    <button onclick="closeStatsModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-6 rounded-lg text-center">
                        <div class="text-4xl text-blue-600 mb-2"><i class="fas fa-users"></i></div>
                        <div class="text-3xl font-bold text-blue-800"><?php echo number_format($totalUsers); ?></div>
                        <div class="text-sm text-blue-600">Total Usuarios</div>
                    </div>
                    <div class="bg-gradient-to-r from-green-50 to-green-100 p-6 rounded-lg text-center">
                        <div class="text-4xl text-green-600 mb-2"><i class="fas fa-graduation-cap"></i></div>
                        <div class="text-3xl font-bold text-green-800"><?php echo number_format($totalCourses); ?></div>
                        <div class="text-sm text-green-600">Cursos Activos</div>
                    </div>
                    <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-6 rounded-lg text-center">
                        <div class="text-4xl text-purple-600 mb-2"><i class="fas fa-book"></i></div>
                        <div class="text-3xl font-bold text-purple-800"><?php echo number_format($totalLibraryResources); ?></div>
                        <div class="text-sm text-purple-600">Recursos Biblioteca</div>
                    </div>
                    <div class="bg-gradient-to-r from-orange-50 to-orange-100 p-6 rounded-lg text-center">
                        <div class="text-4xl text-orange-600 mb-2"><i class="fas fa-tasks"></i></div>
                        <div class="text-3xl font-bold text-orange-800"><?php echo number_format($totalActivities); ?></div>
                        <div class="text-sm text-orange-600">Actividades</div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h4 class="text-lg font-semibold mb-4 flex items-center">
                            <i class="fas fa-user-graduate mr-2 text-blue-600"></i>
                            Distribución de Usuarios
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span>Estudiantes</span>
                                <span class="font-bold"><?php echo $totalStudents; ?> (<?php echo $totalUsers > 0 ? round(($totalStudents / $totalUsers) * 100, 1) : 0; ?>%)</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Docentes</span>
                                <span class="font-bold"><?php echo $totalTeachers; ?> (<?php echo $totalUsers > 0 ? round(($totalTeachers / $totalUsers) * 100, 1) : 0; ?>%)</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Administradores</span>
                                <span class="font-bold"><?php echo $totalUsers - $totalStudents - $totalTeachers; ?> (<?php echo $totalUsers > 0 ? round((($totalUsers - $totalStudents - $totalTeachers) / $totalUsers) * 100, 1) : 0; ?>%)</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h4 class="text-lg font-semibold mb-4 flex items-center">
                            <i class="fas fa-calendar-alt mr-2 text-green-600"></i>
                            Información del Sistema
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span>Horarios Programados</span>
                                <span class="font-bold"><?php echo number_format($pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn()); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Inscripciones Activas</span>
                                <span class="font-bold"><?php echo number_format($pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'enrolled'")->fetchColumn()); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Aulas Disponibles</span>
                                <span class="font-bold"><?php echo number_format($totalClassrooms); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button onclick="closeStatsModal()" class="bg-primary hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>
<script>
function showSystemStats() {
    document.getElementById('statsModal').classList.remove('hidden');
}

function closeStatsModal() {
    document.getElementById('statsModal').classList.add('hidden');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('statsModal');
    if (event.target === modal) {
        closeStatsModal();
    }
});
</script>