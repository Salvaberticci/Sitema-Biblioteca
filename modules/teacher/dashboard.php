<?php require_once '../../includes/config.php'; ?>
<?php requireRole('teacher'); ?>
<?php include '../../templates/header.php'; ?>

<main class="container mx-auto px-6 py-12">
    <!-- Welcome Section -->
    <div
        class="bg-gradient-to-r from-primary to-secondary text-white p-8 rounded-2xl shadow-2xl mb-12 animate-bounce-in">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2 flex items-center">
                    <i class="fas fa-chalkboard-teacher mr-4 text-yellow-300"></i>
                    Panel del Docente
                </h1>
                <p class="text-accent text-lg">Bienvenido, <?php echo htmlspecialchars($_SESSION['name']); ?> - Gestiona
                    tus clases y estudiantes</p>
            </div>
            <div class="hidden md:block">
                <div class="text-6xl opacity-20">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Actions Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <a href="enrollments.php"
            class="group bg-gradient-to-br from-blue-500 to-blue-600 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Gestión de Matrículas</h3>
                <p class="opacity-90">Matricular y desmatricular estudiantes en tus menciones.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Administrar matrículas</span>
                </div>
            </div>
        </a>

        <a href="grades.php"
            class="group bg-gradient-to-br from-green-500 to-green-600 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-file-signature"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Gestión de Notas</h3>
                <p class="opacity-90">Registrar y modificar calificaciones de estudiantes.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Subir notas</span>
                </div>
            </div>
        </a>

        <a href="attendance.php"
            class="group bg-gradient-to-br from-purple-500 to-purple-600 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Control de Asistencia</h3>
                <p class="opacity-90">Marcar asistencia de estudiantes en clases.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Tomar asistencia</span>
                </div>
            </div>
        </a>

        <a href="activities.php"
            class="group bg-gradient-to-br from-red-500 to-pink-500 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Actividades y Tareas</h3>
                <p class="opacity-90">Crear, asignar y calificar actividades.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Gestionar tareas</span>
                </div>
            </div>
        </a>

        <a href="assignments.php"
            class="group bg-gradient-to-br from-yellow-500 to-orange-500 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Asignación de Menciones</h3>
                <p class="opacity-90">Asignarte menciones disponibles de manera autónoma.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Ver menciones</span>
                </div>
            </div>
        </a>

        <a href="../library/manage.php"
            class="group bg-gradient-to-br from-teal-500 to-cyan-500 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-books"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Biblioteca Virtual</h3>
                <p class="opacity-90">Acceder a recursos de la biblioteca.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Ver recursos</span>
                </div>
            </div>
        </a>

        <a href="schedules.php"
            class="group bg-gradient-to-br from-indigo-500 to-purple-600 p-8 rounded-2xl shadow-xl card-hover text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            <div class="relative z-10">
                <div class="text-5xl mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3 class="text-2xl font-bold mb-3">Mis Horarios</h3>
                <p class="opacity-90">Visualizar y gestionar tus horarios de clases por mención.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Consultar horarios</span>
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
                <p class="opacity-90">Actualizar información personal y profesional.</p>
                <div class="mt-4 flex items-center text-sm">
                    <i class="fas fa-arrow-right mr-2"></i>
                    <span>Ver perfil</span>
                </div>
            </div>
        </a>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>