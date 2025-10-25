<?php require_once '../../includes/config.php'; ?>
<?php requireRole('teacher'); ?>
<?php include '../../templates/header.php'; ?>

<main class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Panel del Docente</h2>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="enrollments.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Gestión de Matrículas</h3>
            <p class="text-gray-600">Matricular y desmatricular estudiantes en tus cursos.</p>
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