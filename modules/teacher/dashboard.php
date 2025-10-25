<?php require_once '../../includes/config.php'; ?>
<?php requireRole('teacher'); ?>
<?php include '../../templates/header.php'; ?>

<main class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Panel del Docente</h2>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="enrollments.php" class="bg-gradient-to-br from-purple-50 to-purple-100 p-8 rounded-lg shadow-lg card-hover border-2 border-purple-200">
            <div class="flex items-center mb-4">
                <div class="text-4xl text-purple-600 mr-4">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-purple-800 mb-1">Matrículas</h3>
                    <p class="text-purple-600 font-medium">Gestionar estudiantes</p>
                </div>
            </div>
            <p class="text-gray-700 mb-4">Matricular y desmatricular estudiantes en tus cursos.</p>
            <div class="flex items-center text-purple-700 font-semibold">
                <i class="fas fa-arrow-right mr-2"></i>
                <span>Gestionar matrículas</span>
            </div>
        </a>
        <a href="grades.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Gestión de Notas</h3>
            <p class="text-gray-600">Registrar y modificar calificaciones de estudiantes.</p>
        </a>
        <a href="attendance.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Control de Asistencia</h3>
            <p class="text-gray-600">Marcar asistencia de estudiantes en clases.</p>
        </a>
        <a href="activities.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Actividades y Tareas</h3>
            <p class="text-gray-600">Crear, asignar y calificar actividades.</p>
        </a>
        <a href="../library/index.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Biblioteca Virtual</h3>
            <p class="text-gray-600">Acceder a recursos de la biblioteca.</p>
        </a>
        <a href="../schedules/view.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Horarios de Aulas</h3>
            <p class="text-gray-600">Consultar disponibilidad de aulas.</p>
        </a>
        <a href="profile.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Mi Perfil</h3>
            <p class="text-gray-600">Actualizar información personal.</p>
        </a>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>