<?php
require_once '../../includes/config.php';
requireRole('admin');

// Prevent direct access
if (!isset($_POST['format'])) {
    header('Location: reports.php');
    exit();
}

$format = $_POST['format'];
$report_type = $_POST['report_type'];
$export_data = json_decode($_POST['export_data'], true);

function generateCSV($data, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Write headers based on data type
    if (isset($data['courses'])) {
        fputcsv($output, ['Código', 'Nombre del Curso', 'Estudiantes Matriculados', 'Créditos']);
        foreach ($data['courses'] as $course) {
            fputcsv($output, [
                $course['code'],
                $course['name'],
                $course['enrolled_count'],
                'N/A' // Credits would need to be fetched separately
            ]);
        }
    } elseif (isset($data['activities'])) {
        fputcsv($output, ['Actividad', 'Curso', 'Docente', 'Fecha Límite', 'Entregas']);
        foreach ($data['activities'] as $activity) {
            fputcsv($output, [
                $activity['title'],
                $activity['course_name'],
                $activity['teacher_name'],
                date('d/m/Y', strtotime($activity['due_date'])),
                $activity['submissions']
            ]);
        }
    } elseif (isset($data['grade_distribution'])) {
        fputcsv($output, ['Rango de Calificación', 'Cantidad']);
        foreach ($data['grade_distribution'] as $range) {
            fputcsv($output, [$range['range'], $range['count']]);
        }
    } else {
        // Overview data
        fputcsv($output, ['Métrica', 'Valor']);
        fputcsv($output, ['Total Usuarios', $data['total_users']]);
        fputcsv($output, ['Estudiantes', $data['total_students']]);
        fputcsv($output, ['Docentes', $data['total_teachers']]);
        fputcsv($output, ['Cursos Activos', $data['total_courses']]);
        fputcsv($output, ['Inscripciones Activas', $data['total_enrollments']]);
        fputcsv($output, ['Recursos Biblioteca', $data['total_library_resources']]);
        fputcsv($output, ['Actividades Totales', $data['total_activities']]);
        fputcsv($output, ['Horarios Programados', $data['total_schedules']]);
    }

    fclose($output);
    exit();
}

function generateExcel($data, $filename) {
    // For Excel, we'll generate a more detailed HTML table that Excel can open
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head>';
    echo '<body>';

    if (isset($data['courses'])) {
        echo '<table border="1">';
        echo '<tr><th>Código</th><th>Nombre del Curso</th><th>Estudiantes Matriculados</th><th>Créditos</th></tr>';
        foreach ($data['courses'] as $course) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($course['code']) . '</td>';
            echo '<td>' . htmlspecialchars($course['name']) . '</td>';
            echo '<td>' . $course['enrolled_count'] . '</td>';
            echo '<td>N/A</td>';
            echo '</tr>';
        }
        echo '</table>';
    } elseif (isset($data['activities'])) {
        echo '<table border="1">';
        echo '<tr><th>Actividad</th><th>Curso</th><th>Docente</th><th>Fecha Límite</th><th>Entregas</th></tr>';
        foreach ($data['activities'] as $activity) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($activity['title']) . '</td>';
            echo '<td>' . htmlspecialchars($activity['course_name']) . '</td>';
            echo '<td>' . htmlspecialchars($activity['teacher_name']) . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($activity['due_date'])) . '</td>';
            echo '<td>' . $activity['submissions'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } elseif (isset($data['grade_distribution'])) {
        echo '<table border="1">';
        echo '<tr><th>Rango de Calificación</th><th>Cantidad</th><th>Porcentaje</th></tr>';
        $total = array_sum(array_column($data['grade_distribution'], 'count'));
        foreach ($data['grade_distribution'] as $range) {
            $percentage = $total > 0 ? round(($range['count'] / $total) * 100, 1) : 0;
            echo '<tr>';
            echo '<td>' . htmlspecialchars($range['range']) . '</td>';
            echo '<td>' . $range['count'] . '</td>';
            echo '<td>' . $percentage . '%</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        // Overview data
        echo '<table border="1">';
        echo '<tr><th>Métrica</th><th>Valor</th></tr>';
        echo '<tr><td>Total Usuarios</td><td>' . $data['total_users'] . '</td></tr>';
        echo '<tr><td>Estudiantes</td><td>' . $data['total_students'] . '</td></tr>';
        echo '<tr><td>Docentes</td><td>' . $data['total_teachers'] . '</td></tr>';
        echo '<tr><td>Cursos Activos</td><td>' . $data['total_courses'] . '</td></tr>';
        echo '<tr><td>Inscripciones Activas</td><td>' . $data['total_enrollments'] . '</td></tr>';
        echo '<tr><td>Recursos Biblioteca</td><td>' . $data['total_library_resources'] . '</td></tr>';
        echo '<tr><td>Actividades Totales</td><td>' . $data['total_activities'] . '</td></tr>';
        echo '<tr><td>Horarios Programados</td><td>' . $data['total_schedules'] . '</td></tr>';
        echo '</table>';
    }

    echo '</body></html>';
    exit();
}

function generatePDF($data, $filename) {
    // For PDF, we'll create a simple HTML that can be saved as PDF
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo '<!DOCTYPE html>';
    echo '<html>';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<title>Reporte - ' . ucfirst($_POST['report_type']) . '</title>';
    echo '<style>';
    echo 'body { font-family: Arial, sans-serif; margin: 20px; }';
    echo 'h1 { color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }';
    echo 'table { width: 100%; border-collapse: collapse; margin: 20px 0; }';
    echo 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
    echo 'th { background-color: #f8f9fa; font-weight: bold; }';
    echo 'tr:nth-child(even) { background-color: #f8f9fa; }';
    echo '.stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }';
    echo '.stat-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; }';
    echo '.stat-value { font-size: 2em; font-weight: bold; color: #2563eb; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';

    echo '<h1>Reporte de ' . ucfirst($_POST['report_type']) . '</h1>';
    echo '<p>Generado el ' . date('d/m/Y H:i:s') . '</p>';

    if (isset($data['courses'])) {
        echo '<h2>Estadísticas de Cursos</h2>';
        echo '<table>';
        echo '<tr><th>Código</th><th>Nombre del Curso</th><th>Estudiantes Matriculados</th><th>Créditos</th></tr>';
        foreach ($data['courses'] as $course) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($course['code']) . '</td>';
            echo '<td>' . htmlspecialchars($course['name']) . '</td>';
            echo '<td>' . $course['enrolled_count'] . '</td>';
            echo '<td>N/A</td>';
            echo '</tr>';
        }
        echo '</table>';
    } elseif (isset($data['activities'])) {
        echo '<h2>Reporte de Actividades</h2>';
        echo '<table>';
        echo '<tr><th>Actividad</th><th>Curso</th><th>Docente</th><th>Fecha Límite</th><th>Entregas</th></tr>';
        foreach ($data['activities'] as $activity) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($activity['title']) . '</td>';
            echo '<td>' . htmlspecialchars($activity['course_name']) . '</td>';
            echo '<td>' . htmlspecialchars($activity['teacher_name']) . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($activity['due_date'])) . '</td>';
            echo '<td>' . $activity['submissions'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } elseif (isset($data['grade_distribution'])) {
        echo '<h2>Distribución de Calificaciones</h2>';
        echo '<div class="stats">';
        $total = array_sum(array_column($data['grade_distribution'], 'count'));
        $approved = $data['approved_count'];
        $failed = $data['failed_count'];

        echo '<div class="stat-card">';
        echo '<div class="stat-value">' . round(($approved / max($total, 1)) * 100, 1) . '%</div>';
        echo '<div>Aprobación</div>';
        echo '</div>';

        echo '<div class="stat-card">';
        echo '<div class="stat-value">' . round(($failed / max($total, 1)) * 100, 1) . '%</div>';
        echo '<div>Reprobación</div>';
        echo '</div>';

        echo '<div class="stat-card">';
        echo '<div class="stat-value">' . $total . '</div>';
        echo '<div>Total Calificados</div>';
        echo '</div>';
        echo '</div>';

        echo '<table>';
        echo '<tr><th>Rango de Calificación</th><th>Cantidad</th><th>Porcentaje</th></tr>';
        foreach ($data['grade_distribution'] as $range) {
            $percentage = $total > 0 ? round(($range['count'] / $total) * 100, 1) : 0;
            echo '<tr>';
            echo '<td>' . htmlspecialchars($range['range']) . '</td>';
            echo '<td>' . $range['count'] . '</td>';
            echo '<td>' . $percentage . '%</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        // Overview data
        echo '<h2>Resumen General del Sistema</h2>';
        echo '<div class="stats">';
        echo '<div class="stat-card">';
        echo '<div class="stat-value">' . $data['total_users'] . '</div>';
        echo '<div>Total Usuarios</div>';
        echo '</div>';

        echo '<div class="stat-card">';
        echo '<div class="stat-value">' . $data['total_courses'] . '</div>';
        echo '<div>Cursos Activos</div>';
        echo '</div>';

        echo '<div class="stat-card">';
        echo '<div class="stat-value">' . $data['total_library_resources'] . '</div>';
        echo '<div>Recursos Biblioteca</div>';
        echo '</div>';

        echo '<div class="stat-card">';
        echo '<div class="stat-value">' . $data['total_activities'] . '</div>';
        echo '<div>Actividades</div>';
        echo '</div>';
        echo '</div>';

        echo '<table>';
        echo '<tr><th>Métrica</th><th>Valor</th></tr>';
        echo '<tr><td>Estudiantes</td><td>' . $data['total_students'] . '</td></tr>';
        echo '<tr><td>Docentes</td><td>' . $data['total_teachers'] . '</td></tr>';
        echo '<tr><td>Inscripciones Activas</td><td>' . $data['total_enrollments'] . '</td></tr>';
        echo '<tr><td>Horarios Programados</td><td>' . $data['total_schedules'] . '</td></tr>';
        echo '</table>';
    }

    echo '</body>';
    echo '</html>';
    exit();
}

// Generate filename
$timestamp = date('Y-m-d_H-i-s');
$filename = 'reporte_' . $report_type . '_' . $timestamp;

switch ($format) {
    case 'csv':
        $filename .= '.csv';
        generateCSV($export_data, $filename);
        break;

    case 'excel':
        $filename .= '.xls';
        generateExcel($export_data, $filename);
        break;

    case 'pdf':
        $filename .= '.html'; // HTML that can be saved as PDF
        generatePDF($export_data, $filename);
        break;

    default:
        header('Location: reports.php?error=invalid_format');
        exit();
}
?>