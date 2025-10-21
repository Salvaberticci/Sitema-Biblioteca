<?php
// API REST básica para el sistema de gestión ETC
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../includes/config.php';

// Get the request method and path
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$endpoint = $request[0] ?? '';
$id = $request[1] ?? null;

// Authentication check for protected endpoints
function requireAuth() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit();
    }
}

// Route handling
switch ($endpoint) {
    case 'courses':
        handleCourses($method, $id);
        break;
    case 'users':
        handleUsers($method, $id);
        break;
    case 'library':
        handleLibrary($method, $id);
        break;
    case 'activities':
        handleActivities($method, $id);
        break;
    case 'attendance':
        handleAttendance($method, $id);
        break;
    case 'schedules':
        handleSchedules($method, $id);
        break;
    case 'reports':
        handleReports($method, $id);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}

function handleCourses($method, $id) {
    global $pdo;

    switch ($method) {
        case 'GET':
            if ($id) {
                // Get specific course
                $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
                $stmt->execute([$id]);
                $course = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($course) {
                    echo json_encode($course);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Course not found']);
                }
            } else {
                // Get all courses
                $stmt = $pdo->query("SELECT * FROM courses ORDER BY name");
                $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($courses);
            }
            break;

        case 'POST':
            requireAuth();
            requireRole('admin');

            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['code']) || !isset($data['name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                return;
            }

            $stmt = $pdo->prepare("INSERT INTO courses (code, name, credits, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                sanitize($data['code']),
                sanitize($data['name']),
                (int)($data['credits'] ?? 0),
                sanitize($data['description'] ?? '')
            ]);

            echo json_encode(['id' => $pdo->lastInsertId(), 'message' => 'Course created successfully']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleUsers($method, $id) {
    global $pdo;

    switch ($method) {
        case 'GET':
            requireAuth();
            requireRole('admin');

            if ($id) {
                $stmt = $pdo->prepare("SELECT id, username, name, email, role, created_at FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    echo json_encode($user);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'User not found']);
                }
            } else {
                $stmt = $pdo->query("SELECT id, username, name, email, role, created_at FROM users ORDER BY name");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($users);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleLibrary($method, $id) {
    global $pdo;

    switch ($method) {
        case 'GET':
            $search = $_GET['search'] ?? '';
            $type = $_GET['type'] ?? '';
            $limit = (int)($_GET['limit'] ?? 50);

            $query = "SELECT lr.*, u.name as uploader_name FROM library_resources lr LEFT JOIN users u ON lr.uploaded_by = u.id WHERE 1=1";
            $params = [];

            if ($search) {
                $query .= " AND (title LIKE ? OR author LIKE ? OR description LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            if ($type) {
                $query .= " AND type = ?";
                $params[] = $type;
            }

            $query .= " ORDER BY upload_date DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($resources);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleActivities($method, $id) {
    global $pdo;

    switch ($method) {
        case 'GET':
            requireAuth();

            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM activities WHERE id = ?");
                $stmt->execute([$id]);
                $activity = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($activity) {
                    echo json_encode($activity);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Activity not found']);
                }
            } else {
                $user_id = $_SESSION['user_id'];
                $role = getUserRole();

                if ($role == 'student') {
                    $stmt = $pdo->prepare("
                        SELECT a.*, c.name as course_name
                        FROM activities a
                        JOIN courses c ON a.course_id = c.id
                        JOIN enrollments e ON c.id = e.course_id
                        WHERE e.student_id = ? AND e.status = 'enrolled'
                        ORDER BY a.due_date ASC
                    ");
                    $stmt->execute([$user_id]);
                } elseif ($role == 'teacher') {
                    $stmt = $pdo->prepare("
                        SELECT a.*, c.name as course_name
                        FROM activities a
                        JOIN courses c ON a.course_id = c.id
                        WHERE a.teacher_id = ?
                        ORDER BY a.due_date ASC
                    ");
                    $stmt->execute([$user_id]);
                } else {
                    $stmt = $pdo->query("SELECT a.*, c.name as course_name FROM activities a JOIN courses c ON a.course_id = c.id ORDER BY a.due_date ASC");
                }

                $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($activities);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleAttendance($method, $id) {
    global $pdo;

    switch ($method) {
        case 'GET':
            requireAuth();

            $course_id = $_GET['course_id'] ?? null;
            $date = $_GET['date'] ?? null;

            if (!$course_id || !$date) {
                http_response_code(400);
                echo json_encode(['error' => 'Course ID and date are required']);
                return;
            }

            $stmt = $pdo->prepare("
                SELECT a.*, u.name as student_name
                FROM attendance a
                JOIN users u ON a.student_id = u.id
                WHERE a.course_id = ? AND a.date = ?
                ORDER BY u.name
            ");
            $stmt->execute([$course_id, $date]);
            $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($attendance);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleSchedules($method, $id) {
    global $pdo;

    switch ($method) {
        case 'GET':
            requireAuth();

            $user_id = $_SESSION['user_id'];
            $role = getUserRole();

            if ($role == 'student') {
                $stmt = $pdo->prepare("
                    SELECT s.*, c.name as classroom_name, co.name as course_name, co.code as course_code, u.name as teacher_name
                    FROM schedules s
                    JOIN classrooms c ON s.classroom_id = c.id
                    JOIN courses co ON s.course_id = co.id
                    JOIN users u ON s.teacher_id = u.id
                    JOIN enrollments e ON co.id = e.course_id
                    WHERE e.student_id = ? AND e.status = 'enrolled'
                    ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time
                ");
                $stmt->execute([$user_id]);
            } elseif ($role == 'teacher') {
                $stmt = $pdo->prepare("
                    SELECT s.*, c.name as classroom_name, co.name as course_name, co.code as course_code, u.name as teacher_name
                    FROM schedules s
                    JOIN classrooms c ON s.classroom_id = c.id
                    JOIN courses co ON s.course_id = co.id
                    JOIN users u ON s.teacher_id = u.id
                    WHERE s.teacher_id = ?
                    ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time
                ");
                $stmt->execute([$user_id]);
            } else {
                $stmt = $pdo->query("
                    SELECT s.*, c.name as classroom_name, co.name as course_name, co.code as course_code, u.name as teacher_name
                    FROM schedules s
                    JOIN classrooms c ON s.classroom_id = c.id
                    JOIN courses co ON s.course_id = co.id
                    JOIN users u ON s.teacher_id = u.id
                    ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time
                ");
            }

            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($schedules);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleReports($method, $id) {
    global $pdo;

    switch ($method) {
        case 'GET':
            requireAuth();
            requireRole('admin');

            $type = $_GET['type'] ?? 'overview';

            switch ($type) {
                case 'users':
                    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                    $students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
                    $teachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
                    echo json_encode([
                        'total_users' => $total_users,
                        'students' => $students,
                        'teachers' => $teachers,
                        'admins' => $total_users - $students - $teachers
                    ]);
                    break;

                case 'courses':
                    $stmt = $pdo->query("SELECT c.name, c.code, COUNT(e.student_id) as enrolled FROM courses c LEFT JOIN enrollments e ON c.id = e.course_id AND e.status = 'enrolled' GROUP BY c.id ORDER BY enrolled DESC");
                    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode($courses);
                    break;

                case 'activities':
                    $total_activities = $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn();
                    $total_submissions = $pdo->query("SELECT COUNT(*) FROM submissions")->fetchColumn();
                    echo json_encode([
                        'total_activities' => $total_activities,
                        'total_submissions' => $total_submissions
                    ]);
                    break;

                default:
                    echo json_encode([
                        'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                        'total_courses' => $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
                        'total_activities' => $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn(),
                        'total_library_resources' => $pdo->query("SELECT COUNT(*) FROM library_resources")->fetchColumn()
                    ]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}
?>