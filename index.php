<?php require_once 'includes/config.php'; ?>
<?php include 'templates/header.php'; ?>

<?php
// Check for logout success message
$logout_message = '';
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $logout_message = 'Has cerrado sesión exitosamente.';
}
?>

<?php if ($logout_message): ?>
        <div
            class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 animate-fade-in-up text-center max-w-md mx-auto">
            <i class="fas fa-check-circle mr-2"></i><?php echo $logout_message; ?>
        </div>
<?php endif; ?>

<main>
    <!-- Hero Section -->
    <section class="container mx-auto px-6 py-20 text-center">
        <div class="animate-bounce-in">
            <h2 class="text-5xl font-bold text-gray-800 mb-6 text-shadow">Bienvenidos a ETC "Pedro García Leal"</h2>
            <p class="text-xl text-gray-600 mb-12 max-w-2xl mx-auto">Escuela Técnica Comercial - Excelencia en educación
                técnica y formación integral para jóvenes venezolanos</p>

            <div class="flex justify-center space-x-6 mb-16">
                <?php if (!isLoggedIn()): ?>
                        <a href="login.php"
                            class="btn-gradient text-white font-bold py-4 px-8 rounded-full shadow-2xl transform hover:scale-105 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Iniciar Sesión</span>
                        </a>
                <?php else: ?>
                        <a href="dashboard.php"
                            class="bg-secondary hover:bg-dark text-white font-bold py-4 px-8 rounded-full shadow-2xl transform hover:scale-105 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Ir al Dashboard</span>
                        </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <div class="bg-white p-8 rounded-2xl shadow-xl animate-fade-in-up glow">
                <div class="text-4xl text-primary mb-4 float"><i class="fas fa-graduation-cap"></i></div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Formación Técnica</h3>
                <p class="text-gray-600">Educación técnico-comercial integral para el desarrollo profesional</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-xl animate-fade-in-up glow" style="animation-delay: 0.2s">
                <div class="text-4xl text-primary mb-4 float"><i class="fas fa-chalkboard-teacher"></i></div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Docentes Calificados</h3>
                <p class="text-gray-600">Profesionales comprometidos con la excelencia educativa</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-xl animate-fade-in-up glow" style="animation-delay: 0.4s">
                <div class="text-4xl text-primary mb-4 float"><i class="fas fa-award"></i></div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Excelencia Académica</h3>
                <p class="text-gray-600">Compromiso con los más altos estándares de calidad educativa</p>
            </div>
        </div>
    </section>

    <!-- Academic Programs Section -->
    <section class="py-20 hero-bg">
        <div class="container mx-auto px-6">
            <h3
                class="text-4xl font-bold text-center text-gray-800 mb-16 animate-slide-in-left flex items-center justify-center">
                <i class="fas fa-graduation-cap mr-4 text-primary"></i>
                Nuestra Oferta Educativa
            </h3>
            <div class="max-w-4xl mx-auto">
                <div class="bg-white p-8 rounded-2xl shadow-xl animate-fade-in-up">
                    <div class="text-center mb-8">
                        <div class="text-6xl text-primary mb-4"><i class="fas fa-cogs"></i></div>
                        <h4 class="text-3xl font-bold text-gray-800 mb-4">Formación Integral</h4>
                        <p class="text-lg text-gray-600">La ETC "Pedro García Leal" ofrece una formación
                            técnico-comercial completa que combina conocimientos teóricos con habilidades prácticas para
                            el mundo laboral.</p>
                    </div>
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-4xl text-primary mb-3"><i class="fas fa-chart-line"></i></div>
                            <h5 class="text-xl font-bold text-gray-800 mb-2">Gestión Administrativa</h5>
                            <p class="text-gray-600">Principios de administración, contabilidad y gestión empresarial
                            </p>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl text-primary mb-3"><i class="fas fa-laptop"></i></div>
                            <h5 class="text-xl font-bold text-gray-800 mb-2">Tecnología de la Información</h5>
                            <p class="text-gray-600">Herramientas informáticas y sistemas de gestión digital</p>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl text-primary mb-3"><i class="fas fa-users"></i></div>
                            <h5 class="text-xl font-bold text-gray-800 mb-2">Desarrollo Personal</h5>
                            <p class="text-gray-600">Habilidades sociales, ética profesional y emprendimiento</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Institution Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="animate-slide-in-left">
                    <h3 class="text-4xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-school mr-4 text-primary"></i>
                        Sobre Nuestra Institución
                    </h3>
                    <div class="space-y-4 text-gray-600">
                        <p class="text-lg leading-relaxed">
                            La <strong>Escuela Técnica Comercial "Pedro García Leal"</strong> es una institución
                            educativa comprometida con la formación integral de jóvenes venezolanos, ofreciendo
                            educación técnico-comercial de excelencia.
                        </p>
                        <p>
                            Nuestra institución combina conocimientos teóricos con habilidades prácticas, preparando a
                            nuestros estudiantes para los desafíos del mundo laboral actual. Contamos con docentes
                            altamente calificados y una infraestructura moderna que facilita el aprendizaje.
                        </p>
                        <div class="grid md:grid-cols-2 gap-4 mt-6">
                            <div class="bg-gradient-to-r from-primary to-secondary p-4 rounded-lg text-white">
                                <h4 class="font-bold flex items-center mb-2">
                                    <i class="fas fa-graduation-cap mr-2"></i>
                                    Formación Técnica
                                </h4>
                                <p class="text-sm opacity-90">Educación técnico-comercial integral con enfoque práctico
                                    y profesional.</p>
                            </div>
                            <div class="bg-gradient-to-r from-secondary to-dark p-4 rounded-lg text-white">
                                <h4 class="font-bold flex items-center mb-2">
                                    <i class="fas fa-users mr-2"></i>
                                    Comunidad Educativa
                                </h4>
                                <p class="text-sm opacity-90">Estudiantes y docentes trabajando juntos por la
                                    excelencia.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="animate-bounce-in">
                    <img src="assets/images/entrada-liceo.jpg" alt="Entrada del Liceo Pedro García Leal"
                        class="w-full h-96 object-cover rounded-2xl shadow-2xl glow">
                </div>
            </div>
        </div>
    </section>

    <!-- System Features Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <h3
                class="text-4xl font-bold text-center text-gray-800 mb-16 animate-slide-in-left flex items-center justify-center">
                <i class="fas fa-cogs mr-4 text-primary"></i>
                Módulos del Sistema
            </h3>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div
                    class="bg-gradient-to-br from-blue-500 to-blue-600 p-8 rounded-2xl shadow-xl card-hover text-white animate-fade-in-up">
                    <div class="text-5xl mb-6"><i class="fas fa-clipboard-list"></i></div>
                    <h4 class="text-2xl font-bold mb-3">Gestión Administrativa</h4>
                    <p class="opacity-90">Registro, modificación y validación de información administrativa y académica
                        con mecanismos de seguridad.</p>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 p-8 rounded-2xl shadow-xl card-hover text-white animate-fade-in-up"
                    style="animation-delay: 0.1s">
                    <div class="text-5xl mb-6"><i class="fas fa-book"></i></div>
                    <h4 class="text-2xl font-bold mb-3">Biblioteca Virtual</h4>
                    <p class="opacity-90">Catálogo digital con ingreso, búsqueda y préstamo de recursos educativos.</p>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-8 rounded-2xl shadow-xl card-hover text-white animate-fade-in-up"
                    style="animation-delay: 0.2s">
                    <div class="text-5xl mb-6"><i class="fas fa-calendar-alt"></i></div>
                    <h4 class="text-2xl font-bold mb-3">Horarios de Aulas</h4>
                    <p class="opacity-90">Programación y reserva de horarios de uso de salones escolares.</p>
                </div>
                <div class="bg-gradient-to-br from-red-500 to-red-600 p-8 rounded-2xl shadow-xl card-hover text-white animate-fade-in-up"
                    style="animation-delay: 0.3s">
                    <div class="text-5xl mb-6"><i class="fas fa-tasks"></i></div>
                    <h4 class="text-2xl font-bold mb-3">Actividades</h4>
                    <p class="opacity-90">Creación, asignación y subida de actividades y tareas académicas.</p>
                </div>
            </div>

            <div class="mt-16 grid md:grid-cols-3 gap-8">
                <div class="bg-gradient-to-r from-primary to-secondary p-6 rounded-2xl text-white animate-fade-in-up"
                    style="animation-delay: 0.4s">
                    <h4 class="text-xl font-bold mb-3 flex items-center">
                        <i class="fas fa-database mr-3"></i>
                        Base de Datos Segura
                    </h4>
                    <p class="opacity-90">Almacenamiento organizado de información administrativa y académica con
                        medidas de seguridad avanzadas.</p>
                </div>
                <div class="bg-gradient-to-r from-secondary to-dark p-6 rounded-2xl text-white animate-fade-in-up"
                    style="animation-delay: 0.5s">
                    <h4 class="text-xl font-bold mb-3 flex items-center">
                        <i class="fas fa-chart-bar mr-3"></i>
                        Reportes y Estadísticas
                    </h4>
                    <p class="opacity-90">Generación de reportes útiles para la toma de decisiones institucionales y
                        análisis de rendimiento.</p>
                </div>
                <div class="bg-gradient-to-r from-accent to-primary p-6 rounded-2xl text-gray-800 animate-fade-in-up"
                    style="animation-delay: 0.6s">
                    <h4 class="text-xl font-bold mb-3 flex items-center">
                        <i class="fas fa-mobile-alt mr-3"></i>
                        Acceso Multiplataforma
                    </h4>
                    <p class="text-gray-600">Interfaz intuitiva compatible con navegadores modernos y accesible desde
                        diferentes dispositivos.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Facilities Section -->
    <section class="py-20 gradient-bg text-white">
        <div class="container mx-auto px-6">
            <h3 class="text-4xl font-bold text-center mb-16 animate-slide-in-left flex items-center justify-center">
                <i class="fas fa-building mr-4"></i>
                Nuestras Instalaciones
            </h3>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center animate-fade-in-up">
                    <div class="mb-6">
                        <img src="assets/images/aula.webp" alt="Aula Moderna"
                            class="w-full h-48 object-cover rounded-2xl shadow-2xl mx-auto">
                    </div>
                    <div class="text-5xl mb-4 text-yellow-300"><i class="fas fa-school"></i></div>
                    <h4 class="text-xl font-bold mb-2">Aulas Modernas</h4>
                    <p class="opacity-90">Espacios equipados con tecnología de vanguardia para un aprendizaje óptimo</p>
                </div>
                <div class="text-center animate-fade-in-up" style="animation-delay: 0.1s">
                    <div class="mb-6">
                        <img src="assets/images/biblioteca.jfif" alt="Biblioteca"
                            class="w-full h-48 object-cover rounded-2xl shadow-2xl mx-auto">
                    </div>
                    <div class="text-5xl mb-4 text-yellow-300"><i class="fas fa-books"></i></div>
                    <h4 class="text-xl font-bold mb-2">Biblioteca</h4>
                    <p class="opacity-90">Recursos bibliográficos actualizados y acceso a materiales digitales</p>
                </div>
                <div class="text-center animate-fade-in-up" style="animation-delay: 0.2s">
                    <div class="mb-6">
                        <img src="assets/images/comunidad.png" alt="Comunidad Educativa"
                            class="w-full h-48 object-cover rounded-2xl shadow-2xl mx-auto">
                    </div>
                    <div class="text-5xl mb-4 text-yellow-300"><i class="fas fa-users"></i></div>
                    <h4 class="text-xl font-bold mb-2">Comunidad</h4>
                    <p class="opacity-90">Ambiente educativo colaborativo y motivador para todos nuestros estudiantes
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-6 text-center">
            <div class="animate-pulse-slow">
                <h3 class="text-4xl font-bold text-gray-800 mb-6">Únete a Nuestra Comunidad Educativa</h3>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    La Escuela Técnica Comercial "Pedro García Leal" te ofrece una formación integral con las mejores
                    herramientas tecnológicas para tu desarrollo profesional. Forma parte de nuestra institución y
                    construye tu futuro.
                </p>
                <div class="bg-gradient-to-r from-primary to-secondary p-8 rounded-2xl text-white mb-8">
                    <h4 class="text-2xl font-bold mb-4">Nuestros Compromisos</h4>
                    <div class="grid md:grid-cols-2 gap-6 text-left">
                        <ul class="space-y-2">
                            <li class="flex items-center"><i class="fas fa-graduation-cap mr-3"></i>Educación
                                técnico-comercial de calidad</li>
                            <li class="flex items-center"><i class="fas fa-users mr-3"></i>Formación integral de
                                estudiantes</li>
                            <li class="flex items-center"><i class="fas fa-chalkboard-teacher mr-3"></i>Docentes
                                altamente calificados</li>
                        </ul>
                        <ul class="space-y-2">
                            <li class="flex items-center"><i class="fas fa-laptop mr-3"></i>Tecnología educativa moderna
                            </li>
                            <li class="flex items-center"><i class="fas fa-balance-scale mr-3"></i>Valores éticos y
                                morales</li>
                            <li class="flex items-center"><i class="fas fa-handshake mr-3"></i>Compromiso con la
                                excelencia</li>
                        </ul>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6">
                    <a href="login.php"
                        class="bg-gradient-to-r from-primary to-secondary text-white font-bold py-4 px-8 rounded-full shadow-2xl hover:shadow-xl transition duration-300 transform hover:scale-105 inline-flex items-center space-x-2">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Acceder al Sistema</span>
                    </a>
                    <a href="modules/library/index.php"
                        class="border-2 border-primary text-primary hover:bg-primary hover:text-white font-bold py-4 px-8 rounded-full transition duration-300 inline-flex items-center space-x-2">
                        <i class="fas fa-book"></i>
                        <span>Biblioteca Virtual</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'templates/footer.php'; ?>