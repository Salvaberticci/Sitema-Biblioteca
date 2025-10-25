-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-10-2025 a las 20:05:01
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `biblioteca`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `due_date` datetime NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `classrooms`
--

CREATE TABLE `classrooms` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `classrooms`
--

INSERT INTO `classrooms` (`id`, `name`, `capacity`, `location`) VALUES
(1, 'Aula 101', 30, 'Edificio Principal'),
(2, 'Aula 102', 25, 'Edificio Principal'),
(3, 'Aula 103', 35, 'Edificio Principal'),
(4, 'Aula 104', 28, 'Edificio Principal'),
(5, 'Laboratorio de Informática', 20, 'Edificio de Tecnología'),
(6, 'Laboratorio de Física', 24, 'Edificio de Ciencias'),
(7, 'Laboratorio de Química', 22, 'Edificio de Ciencias'),
(8, 'Gimnasio', 50, 'Edificio Deportivo'),
(9, 'Sala de Música', 15, 'Edificio de Artes'),
(10, 'Biblioteca', 100, 'Edificio Central');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `credits` int(11) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `courses`
--

INSERT INTO `courses` (`id`, `code`, `name`, `credits`, `description`) VALUES
(2, 'INF101', 'Informática Básica', 4, 'Introducción a la computación'),
(3, 'MAT101', 'Matemáticas Básicas', 4, 'Fundamentos de matemáticas para estudiantes de secundaria'),
(4, 'FIS101', 'Física General', 4, 'Principios fundamentales de la física'),
(5, 'QUI101', 'Química General', 4, 'Introducción a la química y sus aplicaciones'),
(6, 'BIO101', 'Biología General', 4, 'Estudio de los seres vivos y sus procesos'),
(7, 'HIS101', 'Historia Universal', 3, 'Historia de la humanidad desde la antigüedad'),
(8, 'GEO101', 'Geografía General', 3, 'Estudio del planeta Tierra y sus características'),
(9, 'LEN101', 'Lengua y Literatura', 3, 'Desarrollo de habilidades lingüísticas y literarias'),
(10, 'ING101', 'Inglés Básico', 3, 'Aprendizaje del idioma inglés para principiantes'),
(11, 'ART101', 'Educación Artística', 2, 'Expresión artística y creatividad'),
(12, 'EDF101', 'Educación Física', 2, 'Actividades físicas y deportivas'),
(13, 'ELE101', 'Electrónica Básica', 4, 'Introducción a circuitos y componentes electrónicos'),
(14, 'MEC101', 'Mecánica Industrial', 4, 'Principios de máquinas y mecanismos'),
(15, 'CON101', 'Contabilidad Básica', 3, 'Fundamentos de contabilidad y finanzas'),
(16, 'ADM101', 'Administración General', 3, 'Principios de administración y gestión'),
(17, 'CTA101', 'Contabilidad Avanzada', 4, 'Técnicas contables para empresas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `period` varchar(20) NOT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `status` enum('enrolled','completed','failed') DEFAULT 'enrolled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `library_resources`
--

CREATE TABLE `library_resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `type` enum('book','article','video','document') NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `physical_books`
--

CREATE TABLE `physical_books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_copies` int(11) NOT NULL DEFAULT 1,
  `available_copies` int(11) NOT NULL DEFAULT 1,
  `location` varchar(100) DEFAULT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('available','maintenance','lost') NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `book_loans`
--

CREATE TABLE `book_loans` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `loan_date` datetime NOT NULL DEFAULT current_timestamp(),
  `due_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `status` enum('active','returned','overdue') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `classroom_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `semester` varchar(20) DEFAULT '2025-1',
  `academic_year` varchar(10) DEFAULT '2025',
  `status` enum('active','cancelled','completed') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `schedules`
--

INSERT INTO `schedules` (`id`, `classroom_id`, `course_id`, `teacher_id`, `day_of_week`, `start_time`, `end_time`, `semester`, `academic_year`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 2, 'monday', '08:00:00', '09:30:00', '2025-1', '2025', 'active', 'Clase de Informática Básica', '2025-10-22 00:14:26', '2025-10-22 00:14:26'),
(2, 1, 3, 4, 'monday', '09:45:00', '11:15:00', '2025-1', '2025', 'active', 'Matemáticas Básicas', '2025-10-22 00:14:26', '2025-10-22 00:14:26'),
(3, 2, 4, 5, 'monday', '13:00:00', '14:30:00', '2025-1', '2025', 'active', 'Física General', '2025-10-22 00:19:10', '2025-10-22 00:19:10'),
(4, 2, 5, 6, 'monday', '14:45:00', '16:15:00', '2025-1', '2025', 'active', 'Química General', '2025-10-22 00:23:56', '2025-10-22 00:23:56'),
(5, 3, 6, 7, 'tuesday', '08:00:00', '09:30:00', '2025-1', '2025', 'active', 'Biología General', '2025-10-22 00:25:15', '2025-10-22 00:25:15'),
(6, 3, 7, 8, 'tuesday', '09:45:00', '11:15:00', '2025-1', '2025', 'active', 'Historia Universal', '2025-10-22 00:25:15', '2025-10-22 00:25:15'),
(7, 4, 8, 4, 'tuesday', '13:00:00', '14:30:00', '2025-1', '2025', 'active', 'Geografía General', '2025-10-22 00:25:15', '2025-10-22 00:25:15'),
(8, 4, 9, 5, 'tuesday', '14:45:00', '16:15:00', '2025-1', '2025', 'active', 'Lengua y Literatura', '2025-10-22 00:25:15', '2025-10-22 00:25:15'),
(9, 5, 10, 6, 'wednesday', '08:00:00', '09:30:00', '2025-1', '2025', 'active', 'Inglés Básico', '2025-10-22 00:25:15', '2025-10-22 00:25:15'),
(10, 6, 13, 7, 'wednesday', '09:45:00', '11:15:00', '2025-1', '2025', 'active', 'Electrónica Básica', '2025-10-22 00:25:15', '2025-10-22 00:25:15'),
(11, 7, 14, 8, 'wednesday', '13:00:00', '14:30:00', '2025-1', '2025', 'active', 'Mecánica Industrial', '2025-10-22 00:25:15', '2025-10-22 00:25:15'),
(12, 1, 15, 4, 'thursday', '08:00:00', '09:30:00', '2025-1', '2025', 'active', 'Contabilidad Básica', '2025-10-22 00:25:15', '2025-10-22 00:25:15'),
(13, 2, 16, 5, 'thursday', '09:45:00', '11:15:00', '2025-1', '2025', 'active', 'Administración General', '2025-10-22 00:25:15', '2025-10-22 00:25:15'),
(14, 3, 17, 6, 'thursday', '13:00:00', '14:30:00', '2025-1', '2025', 'active', 'Contabilidad Avanzada', '2025-10-22 00:25:15', '2025-10-22 00:25:15'),
(15, 8, 12, 7, 'friday', '08:00:00', '09:30:00', '2025-1', '2025', 'active', 'Educación Física', '2025-10-22 00:25:15', '2025-10-22 00:25:15'),
(16, 9, 11, 8, 'friday', '09:45:00', '11:15:00', '2025-1', '2025', 'active', 'Educación Artística', '2025-10-22 00:25:15', '2025-10-22 00:25:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedule_conflicts`
--

CREATE TABLE `schedule_conflicts` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `conflict_type` enum('teacher','classroom','time') NOT NULL,
  `conflict_with` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `detected_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('admin','teacher','student','staff') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `name`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$gCaJ3XJEgbt3DYmPQR0dF.qr52IsXc6gF5d1PlnWoarXc8fyPf/pO', 'admin@etc.edu', 'Administrador', 'admin', '2025-10-15 03:04:03'),
(2, 'teacher1', '$2y$10$mH6D7lUIGNLxGI2rvwQrLONflA/dS0modlsu8qcQBHmOHyLUOChju', 'teacher@etc.edu', 'Profesor Ejemplo', 'teacher', '2025-10-15 03:04:03'),
(3, 'student1', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'student@etc.edu', 'Estudiante Ejemplo', 'student', '2025-10-15 03:04:03'),
(4, 'maria.gonzalez.prof', '$2y$10$mH6D7lUIGNLxGI2rvwQrLONflA/dS0modlsu8qcQBHmOHyLUOChju', 'maria.gonzalez@etc.edu', 'María González Pérez', 'teacher', '2025-10-20 10:00:00'),
(5, 'carlos.rodriguez.prof', '$2y$10$mH6D7lUIGNLxGI2rvwQrLONflA/dS0modlsu8qcQBHmOHyLUOChju', 'carlos.rodriguez@etc.edu', 'Carlos Rodríguez Silva', 'teacher', '2025-10-20 10:15:00'),
(6, 'ana.lopez.prof', '$2y$10$mH6D7lUIGNLxGI2rvwQrLONflA/dS0modlsu8qcQBHmOHyLUOChju', 'ana.lopez@etc.edu', 'Ana López Martínez', 'teacher', '2025-10-20 10:30:00'),
(7, 'pedro.martinez.prof', '$2y$10$mH6D7lUIGNLxGI2rvwQrLONflA/dS0modlsu8qcQBHmOHyLUOChju', 'pedro.martinez@etc.edu', 'Pedro Martínez García', 'teacher', '2025-10-20 10:45:00'),
(8, 'laura.sanchez.prof', '$2y$10$mH6D7lUIGNLxGI2rvwQrLONflA/dS0modlsu8qcQBHmOHyLUOChju', 'laura.sanchez@etc.edu', 'Laura Sánchez Torres', 'teacher', '2025-10-20 11:00:00'),
(9, 'juan.perez.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'juan.perez@etc.edu', 'Juan Carlos Pérez González', 'student', '2025-10-20 11:00:00'),
(10, 'maria.garcia.alumna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'maria.garcia@etc.edu', 'María José García Rodríguez', 'student', '2025-10-20 11:15:00'),
(11, 'carlos.sanchez.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'carlos.sanchez@etc.edu', 'Carlos Alberto Sánchez López', 'student', '2025-10-20 11:30:00'),
(12, 'ana.torres.alumna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'ana.torres@etc.edu', 'Ana María Torres Martínez', 'student', '2025-10-20 11:45:00'),
(13, 'luis.ramirez.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'luis.ramirez@etc.edu', 'Luis Fernando Ramírez Silva', 'student', '2025-10-20 12:00:00'),
(14, 'carmen.flores.alumna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'carmen.flores@etc.edu', 'Carmen Rosa Flores Díaz', 'student', '2025-10-20 12:15:00'),
(15, 'miguel.herrera.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'miguel.herrera@etc.edu', 'Miguel Ángel Herrera Morales', 'student', '2025-10-20 12:30:00'),
(16, 'isabel.morales.alumna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'isabel.morales@etc.edu', 'Isabel Cristina Morales Ruiz', 'student', '2025-10-20 12:45:00'),
(17, 'roberto.diaz.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'roberto.diaz@etc.edu', 'Roberto José Díaz Gómez', 'student', '2025-10-20 13:00:00'),
(18, 'patricia.ruiz.alumna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'patricia.ruiz@etc.edu', 'Patricia Andrea Ruiz Vargas', 'student', '2025-10-20 13:15:00'),
(19, 'fernando.gomez.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'fernando.gomez@etc.edu', 'Fernando Antonio Gómez Castro', 'student', '2025-10-20 13:30:00'),
(20, 'gabriela.silva.alumna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'gabriela.silva@etc.edu', 'Gabriela Isabel Silva Mendoza', 'student', '2025-10-20 13:45:00'),
(21, 'diego.vargas.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'diego.vargas@etc.edu', 'Diego Alejandro Vargas Ortega', 'student', '2025-10-20 14:00:00'),
(22, 'valentina.castro.alumna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'valentina.castro@etc.edu', 'Valentina Sofía Castro Luna', 'student', '2025-10-20 14:15:00'),
(23, 'andres.mendoza.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'andres.mendoza@etc.edu', 'Andrés Felipe Mendoza Ríos', 'student', '2025-10-20 14:30:00'),
(24, 'camila.ortega.alumna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'camila.ortega@etc.edu', 'Camila Andrea Ortega Aguilar', 'student', '2025-10-20 14:45:00'),
(25, 'sebastian.luna.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'sebastian.luna@etc.edu', 'Sebastián David Luna Navarro', 'student', '2025-10-20 15:00:00'),
(26, 'daniela.rios.alumna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'daniela.rios@etc.edu', 'Daniela Valentina Ríos Peña', 'student', '2025-10-20 15:15:00'),
(27, 'mateo.aguilar.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'mateo.aguilar@etc.edu', 'Mateo Alejandro Aguilar Blanco', 'student', '2025-10-20 15:30:00'),
(28, 'sofia.navarro.alumna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'sofia.navarro@etc.edu', 'Sofía Carolina Navarro Moreno', 'student', '2025-10-20 15:45:00'),
(29, 'alejandro.moreno.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'alejandro.moreno@etc.edu', 'Alejandro José Moreno Peña', 'student', '2025-10-20 16:00:00'),
(30, 'valeria.blanco.alumna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'valeria.blanco@etc.edu', 'Valeria Isabel Blanco Romero', 'student', '2025-10-20 16:15:00'),
(31, 'emmanuel.pena.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'emmanuel.pena@etc.edu', 'Emmanuel David Peña Castillo', 'student', '2025-10-20 16:30:00'),
(32, 'mariana.romero.alumna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'mariana.romero@etc.edu', 'Mariana Sofía Romero Castillo', 'student', '2025-10-20 16:45:00'),
(33, 'samuel.castillo.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'samuel.castillo@etc.edu', 'Samuel Andrés Castillo Medina', 'student', '2025-10-20 17:00:00');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indices de la tabla `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indices de la tabla `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indices de la tabla `library_resources`
--
ALTER TABLE `library_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indices de la tabla `physical_books`
--
ALTER TABLE `physical_books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`);

--
-- Indices de la tabla `book_loans`
--
ALTER TABLE `book_loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `classroom_id` (`classroom_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indices de la tabla `schedule_conflicts`
--
ALTER TABLE `schedule_conflicts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `conflict_with` (`conflict_with`);

--
-- Indices de la tabla `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_id` (`activity_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `library_resources`
--
ALTER TABLE `library_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `physical_books`
--
ALTER TABLE `physical_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `book_loans`
--
ALTER TABLE `book_loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `schedule_conflicts`
--
ALTER TABLE `schedule_conflicts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `activities_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Filtros para la tabla `library_resources`
--
ALTER TABLE `library_resources`
  ADD CONSTRAINT `library_resources_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `book_loans`
--
ALTER TABLE `book_loans`
  ADD CONSTRAINT `book_loans_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `physical_books` (`id`),
  ADD CONSTRAINT `book_loans_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`),
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `schedules_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `schedule_conflicts`
--
ALTER TABLE `schedule_conflicts`
  ADD CONSTRAINT `schedule_conflicts_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_conflicts_ibfk_2` FOREIGN KEY (`conflict_with`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`),
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
