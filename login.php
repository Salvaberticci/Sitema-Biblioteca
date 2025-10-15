<?php require_once 'includes/config.php'; ?>

<?php
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errors[] = "Por favor, complete todos los campos.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            redirect('dashboard.php');
        } else {
            $errors[] = "Credenciales incorrectas.";
        }
    }
}
?>

<?php include 'templates/header.php'; ?>

<main class="min-h-screen flex items-center justify-center hero-bg py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 animate-bounce-in">
        <div class="text-center">
            <div class="mx-auto h-20 w-20 bg-gradient-to-r from-primary to-secondary rounded-full flex items-center justify-center shadow-2xl pulse-glow">
                <i class="fas fa-user-lock text-3xl text-white"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Iniciar Sesión</h2>
            <p class="mt-2 text-sm text-gray-600">Accede a tu cuenta institucional</p>
        </div>

        <div class="bg-white py-8 px-6 shadow-2xl rounded-2xl glow">
            <?php if ($errors): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 animate-slide-in">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <ul class="text-sm text-red-700">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 flex items-center">
                        <i class="fas fa-user mr-2 text-primary"></i>
                        Usuario
                    </label>
                    <input type="text" id="username" name="username" required
                           class="mt-1 block w-full px-4 py-3 border-2 border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition duration-300"
                           placeholder="Ingresa tu usuario">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 flex items-center">
                        <i class="fas fa-lock mr-2 text-primary"></i>
                        Contraseña
                    </label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full px-4 py-3 border-2 border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition duration-300"
                           placeholder="Ingresa tu contraseña">
                </div>
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-medium rounded-xl text-white btn-gradient focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transform hover:scale-105 transition duration-300 shadow-xl">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt text-white group-hover:text-accent"></i>
                        </span>
                        Iniciar Sesión
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    ¿Olvidaste tu contraseña?
                    <a href="#" class="font-medium text-primary hover:text-secondary transition duration-300">
                        Contacta al administrador
                    </a>
                </p>
            </div>
        </div>

        <!-- Demo credentials -->
        <div class="bg-gradient-to-r from-primary to-secondary p-6 rounded-xl text-white text-center shadow-xl">
            <h3 class="font-bold mb-2 flex items-center justify-center">
                <i class="fas fa-info-circle mr-2"></i>
                Credenciales de Prueba
            </h3>
            <div class="grid grid-cols-1 gap-2 text-sm">
                <div><strong>Admin:</strong> admin / admin</div>
                <div><strong>Docente:</strong> teacher1 / admin</div>
                <div><strong>Estudiante:</strong> student1 / admin</div>
            </div>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>