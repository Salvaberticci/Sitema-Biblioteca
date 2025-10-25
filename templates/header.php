<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Sistema de Gestión ETC Pedro García Leal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#D4AF37', // Yellow
                        secondary: '#8B4513', // Brown
                        accent: '#F4E4BC', // Light yellow
                        dark: '#654321', // Dark brown
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.6s ease-out',
                        'slide-in-left': 'slideInLeft 0.5s ease-out',
                        'bounce-in': 'bounceIn 0.8s ease-out',
                        'pulse-slow': 'pulse 3s infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideInLeft: {
                            '0%': { opacity: '0', transform: 'translateX(-30px)' },
                            '100%': { opacity: '1', transform: 'translateX(0)' },
                        },
                        bounceIn: {
                            '0%': { opacity: '0', transform: 'scale(0.3)' },
                            '50%': { opacity: '1', transform: 'scale(1.05)' },
                            '70%': { transform: 'scale(0.9)' },
                            '100%': { opacity: '1', transform: 'scale(1)' },
                        },
                    },
                }
            }
        }
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-gray-50 to-accent min-h-screen flex flex-col">
    <header class="bg-gradient-to-r from-primary to-secondary text-white shadow-2xl sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4 animate-slide-in-left">
                    <img src="/biblioteca/logo.png" alt="Logo ETC" class="h-16 w-16 rounded-full border-4 border-white shadow-lg">
                    <div>
                        <h1 class="text-2xl font-bold tracking-wide">Sistema de Gestión</h1>
                        <p class="text-sm opacity-90">ETC Pedro García Leal</p>
                    </div>
                </div>
                <nav class="hidden md:block">
                    <ul class="flex space-x-8">
                        <?php if (!isLoggedIn()): ?>
                            <li><a href="/biblioteca/index.php" class="flex items-center space-x-2 hover:text-accent transition duration-300 transform hover:scale-105"><i class="fas fa-home"></i><span>Inicio</span></a></li>
                        <?php endif; ?>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="/biblioteca/dashboard.php" class="flex items-center space-x-2 hover:text-accent transition duration-300 transform hover:scale-105"><i class="fas fa-tachometer-alt"></i><span>Panel de Administración</span></a></li>
                            <li><a href="/biblioteca/logout.php" class="flex items-center space-x-2 hover:text-accent transition duration-300 transform hover:scale-105"><i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span></a></li>
                        <?php else: ?>
                            <li><a href="/biblioteca/login.php" class="flex items-center space-x-2 bg-white text-primary px-4 py-2 rounded-full hover:bg-accent transition duration-300 transform hover:scale-105 shadow-lg"><i class="fas fa-sign-in-alt"></i><span>Iniciar Sesión</span></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <button class="md:hidden text-white focus:outline-none" id="menu-toggle">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
            <nav class="md:hidden mt-4 hidden" id="mobile-menu">
                <ul class="flex flex-col space-y-2">
                    <?php if (!isLoggedIn()): ?>
                        <li><a href="/biblioteca/index.php" class="flex items-center space-x-2 hover:text-accent transition duration-300 py-2"><i class="fas fa-home"></i><span>Inicio</span></a></li>
                    <?php endif; ?>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="/biblioteca/dashboard.php" class="flex items-center space-x-2 hover:text-accent transition duration-300 py-2"><i class="fas fa-tachometer-alt"></i><span>Panel de Administración</span></a></li>
                        <li><a href="/biblioteca/logout.php" class="flex items-center space-x-2 hover:text-accent transition duration-300 py-2"><i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span></a></li>
                    <?php else: ?>
                        <li><a href="/biblioteca/login.php" class="flex items-center space-x-2 bg-white text-primary px-4 py-2 rounded-full hover:bg-accent transition duration-300 shadow-lg"><i class="fas fa-sign-in-alt"></i><span>Iniciar Sesión</span></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <div class="flex-grow">
    </header>