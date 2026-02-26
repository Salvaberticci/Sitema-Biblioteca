<?php
require_once '../../includes/config.php';
requireRole('teacher');

$user_id = $_SESSION['user_id'];
$type = $_GET['type'] ?? 'daily';
$course_id = (int) ($_GET['course_id'] ?? 0);
$date = $_GET['date'] ?? date('Y-m-d');

if (!$course_id) {
    die("ID de mención inválida.");
}

// Verify teacher teaches this course
$stmt = $pdo->prepare("SELECT c.* FROM courses c JOIN schedules s ON c.id = s.course_id WHERE c.id = ? AND s.teacher_id = ?");
$stmt->execute([$course_id, $user_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    die("No tienes permiso para ver esta mención.");
}

$attendance_records = [];

try {
    if ($type == 'daily') {
        // Fetch students and their attendance for a specific day
        $stmt = $pdo->prepare("
            SELECT u.name, u.username, COALESCE(a.status, 'not_marked') as status
            FROM users u
            LEFT JOIN attendance a ON u.id = a.student_id AND a.course_id = ? AND a.date = ?
            WHERE u.id IN (SELECT DISTINCT student_id FROM enrollments WHERE course_id = ? AND status = 'enrolled')
            AND u.role = 'student'
            ORDER BY u.name
        ");
        $stmt->execute([$course_id, $date, $course_id]);
        $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $report_title = "Reporte Diario de Asistencia - " . $course['name'];
        $sub_title = "Fecha: " . date('d/m/Y', strtotime($date));
    } else {
        // Fetch full history for the course
        $stmt = $pdo->prepare("
            SELECT u.name, u.username, a.date, a.status
            FROM attendance a
            JOIN users u ON a.student_id = u.id
            WHERE a.course_id = ?
            ORDER BY a.date DESC, u.name ASC
        ");
        $stmt->execute([$course_id]);
        $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $report_title = "Historial Completo de Asistencia - " . $course['name'];
        $sub_title = "Generado el: " . date('d/m/Y H:i:s');
    }
} catch (Exception $e) {
    die("Error al generar el reporte: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo $report_title; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 40px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #D4AF37;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
        }

        h1 {
            color: #8B4513;
            margin: 0;
            font-size: 24px;
        }

        h2 {
            color: #666;
            margin: 5px 0;
            font-size: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #F4E4BC;
            color: #8B4513;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.8em;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .status-present {
            color: #2e7d32;
        }

        .status-absent {
            color: #c62828;
        }

        .status-late {
            color: #f9a825;
        }

        .status-excused {
            color: #1565c0;
        }

        .status-not_marked {
            color: #757575;
            font-weight: normal;
            font-style: italic;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 20px;
            }

            button {
                display: none;
            }
        }

        .actions {
            margin-bottom: 20px;
            text-align: right;
        }

        .btn {
            background: #8B4513;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }

        .btn:hover {
            background: #654321;
        }

        .summary {
            display: flex;
            justify-content: space-around;
            background: #fdfae5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #D4AF37;
        }

        .stat-box {
            text-align: center;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="actions no-print">
        <button onclick="window.print()" class="btn">Imprimir / Guardar como PDF</button>
        <a href="attendance.php?course=<?php echo $course_id; ?>" class="btn"
            style="background: #777; margin-left:10px;">Volver</a>
    </div>

    <div class="header">
        <img src="../../logo.png" alt="Logo" class="logo">
        <h1><?php echo $report_title; ?></h1>
        <h2><?php echo $sub_title; ?></h2>
        <p>Mención: <strong><?php echo htmlspecialchars($course['name']); ?>
                (<?php echo htmlspecialchars($course['code']); ?>)</strong></p>
    </div>

    <div class="summary">
        <?php
        $total = count($attendance_records);
        $present = count(array_filter($attendance_records, function ($r) {
            return $r['status'] == 'present';
        }));
        $absent = count(array_filter($attendance_records, function ($r) {
            return $r['status'] == 'absent';
        }));
        $other = $total - $present - $absent;
        $percent = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        ?>
        <div class="stat-box"><strong>Total Estudiantes:</strong> <?php echo $total; ?></div>
        <div class="stat-box"><strong>Presentes:</strong> <?php echo $present; ?> (<?php echo $percent; ?>%)</div>
        <div class="stat-box"><strong>Ausentes:</strong> <?php echo $absent; ?></div>
        <div class="stat-box"><strong>Otros:</strong> <?php echo $other; ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Estudiante</th>
                <th>Usuario</th>
                <?php if ($type == 'history'): ?>
                    <th>Fecha</th>
                <?php endif; ?>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($attendance_records as $record): ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['name']); ?></td>
                    <td><?php echo htmlspecialchars($record['username']); ?></td>
                    <?php if ($type == 'history'): ?>
                        <td><?php echo date('d/m/Y', strtotime($record['date'])); ?></td>
                    <?php endif; ?>
                    <td>
                        <span class="status status-<?php echo $record['status']; ?>">
                            <?php
                            $labels = [
                                'present' => 'Presente',
                                'absent' => 'Ausente',
                                'late' => 'Tarde',
                                'excused' => 'Justificado',
                                'not_marked' => 'Sin marcar'
                            ];
                            echo $labels[$record['status']] ?? $record['status'];
                            ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> Escuela Técnica Comercial "Pedro García Leal" - Sistema de Gestión Académica
        </p>
    </div>
</body>

</html>