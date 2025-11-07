<?php require_once '../../includes/config.php'; ?>
<?php requireRole('student'); ?>
<?php include '../../templates/header.php'; ?>

<main class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Portal del Estudiante</h2>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="enroll.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Matrícula de Menciones</h3>
            <p class="text-gray-600">Matricularme en menciones disponibles y gestionar mis matrículas académicas.</p>
        </a>
        <a href="grades.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Mis Notas</h3>
            <p class="text-gray-600">Consultar calificaciones y promedio académico.</p>
        </a>
        <a href="history.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Historial Académico</h3>
            <p class="text-gray-600">Ver historial completo de menciones y notas.</p>
        </a>
        <a href="activities.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Actividades y Tareas</h3>
            <p class="text-gray-600">Ver y subir actividades asignadas.</p>
        </a>
        <a href="../library/index.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Biblioteca Virtual</h3>
            <p class="text-gray-600">Buscar, descargar y pedir prestados recursos educativos.</p>
        </a>
        <a href="../library/loans.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Mis Préstamos</h3>
            <p class="text-gray-600">Ver y devolver recursos prestados de la biblioteca.</p>
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