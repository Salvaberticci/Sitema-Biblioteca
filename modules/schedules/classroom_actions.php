<?php
require_once '../../includes/config.php';
requireRole('admin');

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $stmt = $pdo->query("SELECT * FROM classrooms ORDER BY name");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data['name'] || !$data['capacity']) {
            echo json_encode(['success' => false, 'message' => 'Nombre y capacidad son requeridos']);
            break;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO classrooms (name, capacity, location) VALUES (?, ?, ?)");
            $stmt->execute([$data['name'], $data['capacity'], $data['location'] ?? '']);
            echo json_encode(['success' => true, 'message' => 'Aula creada correctamente']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data['id'] || !$data['name'] || !$data['capacity']) {
            echo json_encode(['success' => false, 'message' => 'ID, nombre y capacidad son requeridos']);
            break;
        }

        try {
            $stmt = $pdo->prepare("UPDATE classrooms SET name = ?, capacity = ?, location = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['capacity'], $data['location'] ?? '', $data['id']]);
            echo json_encode(['success' => true, 'message' => 'Aula actualizada correctamente']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data['id']) {
            echo json_encode(['success' => false, 'message' => 'ID de aula requerido']);
            break;
        }

        try {
            // Check for active schedules
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE classroom_id = ? AND status = 'active'");
            $stmt->execute([$data['id']]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar el aula porque tiene horarios activos asignados']);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM classrooms WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true, 'message' => 'Aula eliminada correctamente']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
