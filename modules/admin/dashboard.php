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
            <button class="bg-gradient-to-r from-primary to-secondary text-white p-4 rounded-xl hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center justify-center space-x-3">
                <i class="fas fa-plus-circle text-2xl"></i>
                <span>Crear Usuario</span>
            </button>
            <button class="bg-gradient-to-r from-green-500 to-teal-500 text-white p-4 rounded-xl hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center justify-center space-x-3">
                <i class="fas fa-upload text-2xl"></i>
                <span>Subir Recurso</span>
            </button>
            <button class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white p-4 rounded-xl hover:shadow-lg transition duration-300 transform hover:scale-105 flex items-center justify-center space-x-3">
                <i class="fas fa-chart-pie text-2xl"></i>
                <span>Ver Estadísticas</span>
            </button>
        </div>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>