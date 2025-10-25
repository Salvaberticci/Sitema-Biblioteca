<?php require_once '../../includes/config.php'; ?>
<?php requireRole('student'); ?>
<?php include '../../templates/header.php'; ?>

<main class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Portal del Estudiante</h2>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="enroll.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Matrícula de Cursos</h3>
            <p class="text-gray-600">Matricularme en cursos disponibles y gestionar mis matrículas académicas.</p>
        </a>
        <a href="grades.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Mis Notas</h3>
            <p class="text-gray-600">Consultar calificaciones y promedio académico.</p>
        </a>
        <a href="history.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Historial Académico</h3>
            <p class="text-gray-600">Ver historial completo de cursos y notas.</p>
        </a>
        <a href="activities.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Actividades y Tareas</h3>
            <p class="text-gray-600">Ver y subir actividades asignadas.</p>
        </a>
        <a href="../library/index.php" class="bg-white p-6 rounded-lg shadow-md card-hover">
            <h3 class="text-xl font-semibold text-primary mb-2">Biblioteca Virtual</h3>
            <p class="text-gray-600">Buscar, descargar y pedir prestados recursos educativos.</p>
        </a>
        <a href="../library/loans.php" class="bg-gradient-to-br from-blue-50 to-blue-100 p-8 rounded-lg shadow-lg card-hover border-2 border-blue-200">
            <div class="flex items-center mb-4">
                <div class="text-4xl text-blue-600 mr-4">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-blue-800 mb-1">Mis Préstamos</h3>
                    <p class="text-blue-600 font-medium">Gestionar recursos prestados</p>
                </div>
            </div>
            <p class="text-gray-700 mb-4">Ver y devolver recursos prestados. Controla tus fechas de entrega y evita multas.</p>
            <div class="flex items-center text-blue-700 font-semibold">
                <i class="fas fa-arrow-right mr-2"></i>
                <span>Administrar préstamos</span>
            </div>
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