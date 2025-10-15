    <footer class="bg-gradient-to-r from-dark to-secondary text-white py-12 mt-8 relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="container mx-auto px-6 relative z-10">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="animate-fade-in-up">
                    <h3 class="text-xl font-bold mb-4 flex items-center"><i class="fas fa-school mr-2"></i>ETC Pedro García Leal</h3>
                    <p class="text-accent">Excelencia educativa y formación técnica para el futuro.</p>
                </div>
                <div class="animate-fade-in-up" style="animation-delay: 0.2s">
                    <h4 class="text-lg font-semibold mb-4 flex items-center"><i class="fas fa-link mr-2"></i>Enlaces Rápidos</h4>
                    <ul class="space-y-2">
                        <li><a href="/biblioteca/index.php" class="hover:text-primary transition duration-300 flex items-center"><i class="fas fa-home mr-2"></i>Inicio</a></li>
                        <li><a href="/biblioteca/modules/library/index.php" class="hover:text-primary transition duration-300 flex items-center"><i class="fas fa-book mr-2"></i>Biblioteca</a></li>
                        <li><a href="/biblioteca/modules/schedules/view.php" class="hover:text-primary transition duration-300 flex items-center"><i class="fas fa-calendar mr-2"></i>Horarios</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="/biblioteca/logout.php" class="hover:text-primary transition duration-300 flex items-center"><i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión</a></li>
                        <?php else: ?>
                            <li><a href="/biblioteca/login.php" class="hover:text-primary transition duration-300 flex items-center"><i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="animate-fade-in-up" style="animation-delay: 0.4s">
                    <h4 class="text-lg font-semibold mb-4 flex items-center"><i class="fas fa-phone mr-2"></i>Contacto</h4>
                    <ul class="space-y-2">
                        <li class="flex items-center"><i class="fas fa-map-marker-alt mr-2"></i>Dirección: Calle Principal, Ciudad</li>
                        <li class="flex items-center"><i class="fas fa-phone mr-2"></i>Teléfono: (123) 456-7890</li>
                        <li class="flex items-center"><i class="fas fa-envelope mr-2"></i>Email: info@etc.edu</li>
                    </ul>
                </div>
                <div class="animate-fade-in-up" style="animation-delay: 0.6s">
                    <h4 class="text-lg font-semibold mb-4 flex items-center"><i class="fas fa-info-circle mr-2"></i>Acerca de</h4>
                    <p class="text-accent text-sm">Sistema integral de gestión académica y administrativa para estudiantes, docentes y personal de la institución.</p>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-2xl hover:text-primary transition duration-300"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-2xl hover:text-primary transition duration-300"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-2xl hover:text-primary transition duration-300"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-accent mt-8 pt-8 text-center">
                <p class="text-accent">&copy; 2025 Escuela Técnica Comercial "Pedro García Leal". Todos los derechos reservados. Desarrollado con <i class="fas fa-heart text-red-400"></i> para la educación.</p>
            </div>
        </div>
    </footer>
    <script src="assets/js/main.js"></script>
    <script>
        // Mobile menu toggle
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>
</body>
</html>