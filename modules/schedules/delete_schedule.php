<?php
require_once '../../includes/config.php';
requireRole('admin');

header('Content-Type: application/json');

// Get POST data
$schedule_id = $_POST['schedule_id'] ?? null;

if (!$schedule_id) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de horario no proporcionado'
    ]);
    exit;
}

try {
    // Verify the schedule exists
    $stmt = $pdo->prepare("SELECT id FROM schedules WHERE id = ?");
    $stmt->execute([$schedule_id]);

    if ($stmt->rowCount() == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Horario no encontrado'
        ]);
        exit;
    }

    // Delete the schedule
    $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
    $result = $stmt->execute([$schedule_id]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Horario eliminado exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar el horario'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>