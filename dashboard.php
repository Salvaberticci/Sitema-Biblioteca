<?php require_once 'includes/config.php'; ?>

<?php
if (!isLoggedIn()) {
    redirect('login.php');
}

$role = getUserRole();
switch ($role) {
    case 'admin':
        redirect('modules/admin/dashboard.php');
        break;
    case 'teacher':
        redirect('modules/teacher/dashboard.php');
        break;
    case 'student':
        redirect('modules/student/dashboard.php');
        break;
        break;
    default:
        redirect('index.php');
}
?>