<?php require_once '../../includes/config.php'; ?>
<?php requireRole('student'); ?>
<?php include '../../templates/header.php'; ?>

<main class="container mx-auto px-6 py-12">
    <!-- Welcome Section -->
    <div
        class="bg-gradient-to-r from-primary to-secondary text-white p-8 rounded-2xl shadow-2xl mb-12 animate-bounce-in">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2 flex items-center">
                    <i class="fas fa-user-graduate mr-4 text-accent"></i>
                    Portal del Estudiante
                </h1>
                <p class="text-accent text-lg">Bienvenido, <?php echo htmlspecialchars($_SESSION['name']); ?> - Gestiona
                    tu progreso académico</p>
            </div>
            <div class="hidden md:block">
                <div class="text-6xl opacity-20">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <a href="enroll.php"
            class="group bg-gradient-to-br from-blue-500 to-blue-600 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Matrícula de Menciones</h3>
                <p class="opacity-90">Matricularme en menciones disponibles y gestionar mis matrículas académicas.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Inscribirme ahora</span>
                </div>
            </div>
        </a>

        <a href="grades.php"
            class="group bg-gradient-to-br from-yellow-500 to-orange-500 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-star"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Mis Notas</h3>
                <p class="opacity-90">Consultar calificaciones y promedio académico detallado por periodo.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Ver mis notas</span>
                </div>
            </div>
        </a>

        <a href="history.php"
            class="group bg-gradient-to-br from-green-500 to-green-600 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Historial Académico</h3>
                <p class="opacity-90">Ver historial completo de todas las menciones cursadas y notas obtenidas.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Ver historial</span>
                </div>
            </div>
        </a>

        <a href="activities.php"
            class="group bg-gradient-to-br from-purple-500 to-purple-600 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Actividades y Tareas</h3>
                <p class="opacity-90">Ver, realizar y subir actividades asignadas por tus docentes.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Ver pendientes</span>
                </div>
            </div>
        </a>

        <a href="../library/index.php"
            class="group bg-gradient-to-br from-teal-500 to-cyan-500 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-book"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Biblioteca Virtual</h3>
                <p class="opacity-90">Buscar, descargar y pedir prestados recursos educativos y libros digitales.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Explorar catálogo</span>
                </div>
            </div>
        </a>

        <a href="../library/loans.php"
            class="group bg-gradient-to-br from-indigo-500 to-purple-600 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Mis Préstamos</h3>
                <p class="opacity-90">Ver y devolver recursos prestados de la biblioteca institucional.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Gestionar préstamos</span>
                </div>
            </div>
        </a>

        <a href="../schedules/view.php"
            class="group bg-gradient-to-br from-red-500 to-pink-500 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Consulta de Horarios</h3>
                <p class="opacity-90">Ver tus horarios de clases actualizados y ubicación de aulas.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Ver mis horarios</span>
                </div>
            </div>
        </a>

        <a href="profile.php"
            class="group bg-gradient-to-br from-gray-600 to-gray-700 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Mi Perfil</h3>
                <p class="opacity-90">Actualizar tu información personal y configuración de cuenta.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Editar perfil</span>
                </div>
            </div>
        </a>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>