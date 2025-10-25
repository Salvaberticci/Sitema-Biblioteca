-- Test Data Script for Sistema de Gestión ETC "Pedro García Leal"
-- This script provides comprehensive test data for all system modules
-- Run this after the main database.sql to populate with test data

-- =====================================================
-- USERS DATA
-- =====================================================

-- Additional Teachers
INSERT INTO users (username, password, email, name, role, created_at) VALUES
('maria.gonzalez.prof', '$2y$10$mH6D7lUIGNLxGI2rvwQrLONflA/dS0modlsu8qcQBHmOHyLUOChju', 'maria.gonzalez@etc.edu', 'María González Pérez', 'teacher', '2025-10-20 10:00:00'),
('carlos.rodriguez.prof', '$2y$10$mH6D7lUIGNLxGI2rvwQrLONflA/dS0modlsu8qcQBHmOHyLUOChju', 'carlos.rodriguez@etc.edu', 'Carlos Rodríguez Silva', 'teacher', '2025-10-20 10:15:00'),
('ana.lopez.prof', '$2y$10$mH6D7lUIGNLxGI2rvwQrLONflA/dS0modlsu8qcQBHmOHyLUOChju', 'ana.lopez@etc.edu', 'Ana López Martínez', 'teacher', '2025-10-20 10:30:00'),
('pedro.martinez.prof', '$2y$10$mH6D7lUIGNLxGI2rvwQrLONflA/dS0modlsu8qcQBHmOHyLUOChju', 'pedro.martinez@etc.edu', 'Pedro Martínez García', 'teacher', '2025-10-20 10:45:00'),
('laura.sanchez.prof', '$2y$10$mH6D7lUIGNLxGI2rvwQrLONflA/dS0modlsu8qcQBHmOHyLUOChju', 'laura.sanchez@etc.edu', 'Laura Sánchez Torres', 'teacher', '2025-10-20 11:00:00');

-- Additional Students (25 students with realistic Venezuelan names)
INSERT INTO users (username, password, email, name, role, created_at) VALUES
('juan.perez.alumno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'juan.perez@etc.edu', 'Juan Carlos Pérez González', 'student', '2025-10-20 11:00:00'),
('maria.garcia.alumna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'maria.garcia@etc.edu', 'María José García Rodríguez', 'student', '2025-10-20 11:15:00'),
('carlos.sanchez', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'carlos.sanchez@etc.edu', 'Carlos Alberto Sánchez López', 'student', '2025-10-20 11:30:00'),
('ana.torres', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'ana.torres@etc.edu', 'Ana María Torres Martínez', 'student', '2025-10-20 11:45:00'),
('luis.ramirez', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'luis.ramirez@etc.edu', 'Luis Fernando Ramírez Silva', 'student', '2025-10-20 12:00:00'),
('carmen.flores', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'carmen.flores@etc.edu', 'Carmen Rosa Flores Díaz', 'student', '2025-10-20 12:15:00'),
('miguel.herrera', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'miguel.herrera@etc.edu', 'Miguel Ángel Herrera Morales', 'student', '2025-10-20 12:30:00'),
('isabel.morales', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'isabel.morales@etc.edu', 'Isabel Cristina Morales Ruiz', 'student', '2025-10-20 12:45:00'),
('roberto.diaz', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'roberto.diaz@etc.edu', 'Roberto José Díaz Gómez', 'student', '2025-10-20 13:00:00'),
('patricia.ruiz', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'patricia.ruiz@etc.edu', 'Patricia Andrea Ruiz Vargas', 'student', '2025-10-20 13:15:00'),
('fernando.gomez', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'fernando.gomez@etc.edu', 'Fernando Antonio Gómez Castro', 'student', '2025-10-20 13:30:00'),
('gabriela.silva', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'gabriela.silva@etc.edu', 'Gabriela Isabel Silva Mendoza', 'student', '2025-10-20 13:45:00'),
('diego.vargas', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'diego.vargas@etc.edu', 'Diego Alejandro Vargas Ortega', 'student', '2025-10-20 14:00:00'),
('valentina.castro', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'valentina.castro@etc.edu', 'Valentina Sofía Castro Luna', 'student', '2025-10-20 14:15:00'),
('andres.mendoza', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'andres.mendoza@etc.edu', 'Andrés Felipe Mendoza Ríos', 'student', '2025-10-20 14:30:00'),
('camila.ortega', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'camila.ortega@etc.edu', 'Camila Andrea Ortega Aguilar', 'student', '2025-10-20 14:45:00'),
('sebastian.luna', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'sebastian.luna@etc.edu', 'Sebastián David Luna Navarro', 'student', '2025-10-20 15:00:00'),
('daniela.rios', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'daniela.rios@etc.edu', 'Daniela Valentina Ríos Peña', 'student', '2025-10-20 15:15:00'),
('mateo.aguilar', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'mateo.aguilar@etc.edu', 'Mateo Alejandro Aguilar Blanco', 'student', '2025-10-20 15:30:00'),
('sofia.navarro', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'sofia.navarro@etc.edu', 'Sofía Carolina Navarro Moreno', 'student', '2025-10-20 15:45:00'),
('alejandro.moreno', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'alejandro.moreno@etc.edu', 'Alejandro José Moreno Peña', 'student', '2025-10-20 16:00:00'),
('valeria.blanco', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'valeria.blanco@etc.edu', 'Valeria Isabel Blanco Romero', 'student', '2025-10-20 16:15:00'),
('emmanuel.peña', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'emmanuel.peña@etc.edu', 'Emmanuel David Peña Castillo', 'student', '2025-10-20 16:30:00'),
('mariana.romero', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'mariana.romero@etc.edu', 'Mariana Sofía Romero Castillo', 'student', '2025-10-20 16:45:00'),
('samuel.castillo', '$2y$10$0ALY30rmlorlQ5warvq3xOg/w3FdCpFEGQUVDPsM6X4w7gwfvWc/W', 'samuel.castillo@etc.edu', 'Samuel Andrés Castillo Medina', 'student', '2025-10-20 17:00:00');

-- =====================================================
-- COURSES DATA
-- =====================================================

INSERT INTO courses (code, name, credits, description) VALUES
('MAT101', 'Matemáticas Básicas', 3, 'Fundamentos de matemáticas para estudiantes de secundaria'),
('FIS101', 'Física General', 4, 'Introducción a los conceptos básicos de física'),
('QUI101', 'Química General', 4, 'Principios fundamentales de química'),
('BIO101', 'Biología General', 3, 'Estudio de los seres vivos y sus procesos'),
('HIS101', 'Historia Universal', 2, 'Historia de la humanidad desde la antigüedad'),
('GEO101', 'Geografía', 2, 'Estudio del planeta Tierra y sus características'),
('LEN101', 'Lenguaje y Literatura', 3, 'Desarrollo de habilidades lingüísticas y literarias'),
('ING101', 'Inglés Básico', 2, 'Introducción al idioma inglés'),
('ART101', 'Educación Artística', 2, 'Expresión artística y creatividad'),
('EDF101', 'Educación Física', 2, 'Actividades físicas y deportivas'),
('INF201', 'Informática Avanzada', 4, 'Programación y sistemas informáticos avanzados'),
('ELE201', 'Electrónica Básica', 3, 'Circuitos y componentes electrónicos'),
('MEC201', 'Mecánica Industrial', 4, 'Principios de máquinas y mecanismos'),
('CON201', 'Construcción Civil', 3, 'Materiales y técnicas de construcción'),
('ADM201', 'Administración de Empresas', 3, 'Principios de gestión empresarial'),
('CTA201', 'Contabilidad', 3, 'Fundamentos de contabilidad y finanzas');

-- =====================================================
-- CLASSROOMS DATA
-- =====================================================

INSERT INTO classrooms (name, capacity, location) VALUES
('Aula 103', 28, 'Edificio Principal - Planta Baja'),
('Aula 104', 32, 'Edificio Principal - Planta Baja'),
('Aula 105', 25, 'Edificio Principal - Planta Baja'),
('Laboratorio de Física', 20, 'Edificio de Ciencias - Planta 1'),
('Laboratorio de Química', 18, 'Edificio de Ciencias - Planta 1'),
('Laboratorio de Biología', 16, 'Edificio de Ciencias - Planta 2'),
('Laboratorio de Informática 1', 24, 'Edificio Técnico - Planta 1'),
('Laboratorio de Informática 2', 24, 'Edificio Técnico - Planta 1'),
('Sala de Dibujo Técnico', 15, 'Edificio Técnico - Planta 2'),
('Gimnasio', 50, 'Edificio Deportivo'),
('Biblioteca', 80, 'Edificio Administrativo'),
('Auditorio', 120, 'Edificio Administrativo');

-- =====================================================
-- SCHEDULES DATA
-- =====================================================

INSERT INTO schedules (classroom_id, course_id, teacher_id, day_of_week, start_time, end_time, semester, academic_year, status, notes, created_at, updated_at) VALUES
-- Lunes
(1, 2, 2, 'monday', '07:00:00', '08:00:00', '2025-1', '2025', 'active', 'Clase teórica', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),
(2, 3, 6, 'monday', '08:00:00', '09:30:00', '2025-1', '2025', 'active', 'Laboratorio práctico', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),
(3, 4, 7, 'monday', '09:45:00', '11:15:00', '2025-1', '2025', 'active', 'Clase magistral', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),
(4, 5, 8, 'monday', '11:30:00', '12:30:00', '2025-1', '2025', 'active', 'Sesión interactiva', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),

-- Martes
(5, 6, 2, 'tuesday', '07:00:00', '08:30:00', '2025-1', '2025', 'active', 'Práctica de laboratorio', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),
(6, 7, 6, 'tuesday', '08:45:00', '10:15:00', '2025-1', '2025', 'active', 'Clase teórica', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),
(7, 8, 7, 'tuesday', '10:30:00', '12:00:00', '2025-1', '2025', 'active', 'Sesión de ejercicios', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),

-- Miércoles
(8, 9, 8, 'wednesday', '07:00:00', '08:30:00', '2025-1', '2025', 'active', 'Clase práctica', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),
(1, 10, 2, 'wednesday', '08:45:00', '10:15:00', '2025-1', '2025', 'active', 'Actividad física', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),
(2, 11, 6, 'wednesday', '10:30:00', '12:00:00', '2025-1', '2025', 'active', 'Laboratorio de programación', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),

-- Jueves
(3, 12, 7, 'thursday', '07:00:00', '08:30:00', '2025-1', '2025', 'active', 'Taller electrónico', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),
(4, 13, 8, 'thursday', '08:45:00', '10:15:00', '2025-1', '2025', 'active', 'Práctica de taller', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),
(5, 14, 2, 'thursday', '10:30:00', '12:00:00', '2025-1', '2025', 'active', 'Clase teórica', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),

-- Viernes
(6, 15, 6, 'friday', '07:00:00', '08:30:00', '2025-1', '2025', 'active', 'Caso de estudio', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),
(7, 16, 7, 'friday', '08:45:00', '10:15:00', '2025-1', '2025', 'active', 'Ejercicios contables', '2025-10-20 16:00:00', '2025-10-20 16:00:00'),
(8, 17, 8, 'friday', '10:30:00', '12:00:00', '2025-1', '2025', 'active', 'Revisión y evaluación', '2025-10-20 16:00:00', '2025-10-20 16:00:00');

-- =====================================================
-- ENROLLMENTS DATA
-- =====================================================

-- Matricular estudiantes en cursos (distribución variada)
INSERT INTO enrollments (student_id, course_id, period, grade, status) VALUES
-- Estudiante 1 (student1) - varios cursos
(3, 2, '2025-1', 18.5, 'completed'),
(3, 3, '2025-1', 16.0, 'completed'),
(3, 4, '2025-1', 15.5, 'completed'),
(3, 5, '2025-1', NULL, 'enrolled'),

-- Estudiante 2 (student2)
(4, 2, '2025-1', 17.0, 'completed'),
(4, 6, '2025-1', 14.5, 'completed'),
(4, 7, '2025-1', NULL, 'enrolled'),

-- Estudiante 3 (student3)
(5, 3, '2025-1', 19.0, 'completed'),
(5, 8, '2025-1', 16.5, 'completed'),
(5, 9, '2025-1', NULL, 'enrolled'),

-- Más matrículas variadas
(6, 2, '2025-1', NULL, 'enrolled'),
(6, 4, '2025-1', NULL, 'enrolled'),
(7, 5, '2025-1', NULL, 'enrolled'),
(7, 6, '2025-1', NULL, 'enrolled'),
(8, 7, '2025-1', NULL, 'enrolled'),
(8, 8, '2025-1', NULL, 'enrolled'),
(9, 9, '2025-1', NULL, 'enrolled'),
(9, 10, '2025-1', NULL, 'enrolled'),
(10, 11, '2025-1', NULL, 'enrolled'),
(10, 12, '2025-1', NULL, 'enrolled'),
(11, 13, '2025-1', NULL, 'enrolled'),
(11, 14, '2025-1', NULL, 'enrolled'),
(12, 15, '2025-1', NULL, 'enrolled'),
(12, 16, '2025-1', NULL, 'enrolled'),
(13, 17, '2025-1', NULL, 'enrolled'),
(14, 2, '2025-1', NULL, 'enrolled'),
(15, 3, '2025-1', NULL, 'enrolled'),
(16, 4, '2025-1', NULL, 'enrolled'),
(17, 5, '2025-1', NULL, 'enrolled'),
(18, 6, '2025-1', NULL, 'enrolled'),
(19, 7, '2025-1', NULL, 'enrolled'),
(20, 8, '2025-1', NULL, 'enrolled'),
(21, 9, '2025-1', NULL, 'enrolled'),
(22, 10, '2025-1', NULL, 'enrolled'),
(23, 11, '2025-1', NULL, 'enrolled'),
(24, 12, '2025-1', NULL, 'enrolled');

-- =====================================================
-- ACTIVITIES DATA
-- =====================================================

INSERT INTO activities (title, description, course_id, teacher_id, due_date, file_path, created_at) VALUES
('Tarea 1: Introducción a la Informática', 'Investigación sobre la historia de la computación', 2, 2, '2025-11-15 23:59:00', NULL, '2025-10-20 17:00:00'),
('Laboratorio 1: Álgebra Básica', 'Ejercicios de ecuaciones lineales', 3, 3, '2025-11-10 23:59:00', NULL, '2025-10-20 17:15:00'),
('Ensayo: La Revolución Industrial', 'Redacción sobre impactos sociales', 5, 5, '2025-11-20 23:59:00', NULL, '2025-10-20 17:30:00'),
('Proyecto Final: Sistema Solar', 'Modelo 3D del sistema solar', 4, 4, '2025-12-01 23:59:00', NULL, '2025-10-20 17:45:00'),
('Presentación: Mercados Financieros', 'Análisis de tendencias económicas', 15, 3, '2025-11-25 23:59:00', NULL, '2025-10-20 18:00:00'),
('Examen Parcial: Programación Básica', 'Evaluación de conceptos fundamentales', 11, 3, '2025-11-12 10:00:00', NULL, '2025-10-20 18:15:00'),
('Taller: Circuitos Eléctricos', 'Construcción de circuitos básicos', 12, 4, '2025-11-18 23:59:00', NULL, '2025-10-20 18:30:00'),
('Investigación: Literatura Venezolana', 'Análisis de obras de autores nacionales', 7, 4, '2025-11-22 23:59:00', NULL, '2025-10-20 18:45:00');

-- =====================================================
-- SUBMISSIONS DATA
-- =====================================================

INSERT INTO submissions (activity_id, student_id, file_path, grade, comments, submitted_at) VALUES
(1, 3, 'uploads/submissions/1761410450_3_unnamed (1).png', 18.0, 'Excelente investigación, muy completo', '2025-11-14 20:00:00'),
(1, 4, NULL, 16.5, 'Buen trabajo, falta profundizar en algunos aspectos', '2025-11-14 19:30:00'),
(2, 5, NULL, 17.5, 'Ejercicios resueltos correctamente', '2025-11-09 21:00:00'),
(3, 3, NULL, 19.0, 'Ensayo muy bien estructurado', '2025-11-19 18:00:00'),
(4, 4, NULL, NULL, NULL, '2025-11-30 16:00:00'),
(5, 5, NULL, NULL, NULL, '2025-11-24 14:00:00');

-- =====================================================
-- ATTENDANCE DATA
-- =====================================================

-- Asistencia para diferentes clases (últimas 2 semanas)
INSERT INTO attendance (course_id, student_id, date, status) VALUES
-- Clase del lunes (curso 2)
(2, 3, '2025-10-21', 'present'),
(2, 4, '2025-10-21', 'present'),
(2, 6, '2025-10-21', 'late'),
(2, 14, '2025-10-21', 'absent'),

(2, 3, '2025-10-28', 'present'),
(2, 4, '2025-10-28', 'present'),
(2, 6, '2025-10-28', 'present'),
(2, 14, '2025-10-28', 'excused'),

-- Clase del martes (curso 3)
(3, 5, '2025-10-22', 'present'),
(3, 15, '2025-10-22', 'present'),
(3, 5, '2025-10-29', 'late'),
(3, 15, '2025-10-29', 'present'),

-- Clase del miércoles (curso 4)
(4, 3, '2025-10-23', 'present'),
(4, 16, '2025-10-23', 'present'),
(4, 3, '2025-10-30', 'present'),
(4, 16, '2025-10-30', 'absent');

-- =====================================================
-- LIBRARY RESOURCES DATA
-- =====================================================

INSERT INTO library_resources (title, author, type, subject, file_path, description, uploaded_by, upload_date) VALUES
('Manual de Física Cuántica', 'Richard Feynman', 'book', 'Física', 'uploads/library/1761163636_certificado curso python.pdf', 'Introducción completa a la física cuántica', 2, '2025-10-20 19:00:00'),
('Historia de Venezuela', 'Mariano Picón Salas', 'book', 'Historia', NULL, 'Historia política y social de Venezuela', 5, '2025-10-20 19:15:00'),
('Álgebra Lineal y sus Aplicaciones', 'Gilbert Strang', 'book', 'Matemáticas', NULL, 'Texto fundamental de álgebra lineal', 3, '2025-10-20 19:30:00'),
('Química Orgánica Básica', 'John McMurry', 'book', 'Química', NULL, 'Principios de química orgánica', 4, '2025-10-20 19:45:00'),
('Biología Molecular', 'Bruce Alberts', 'book', 'Biología', NULL, 'Fundamentos de biología molecular', 4, '2025-10-20 20:00:00'),
('Geografía de América Latina', 'Armando Hart', 'book', 'Geografía', NULL, 'Estudio geográfico de Latinoamérica', 5, '2025-10-20 20:15:00'),
('Literatura Hispanoamericana', 'Anderson Imbert', 'book', 'Literatura', NULL, 'Antología de literatura hispanoamericana', 4, '2025-10-20 20:30:00'),
('Inglés Técnico para Ingenieros', 'Oxford University Press', 'book', 'Idiomas', NULL, 'Vocabulario técnico en inglés', 2, '2025-10-20 20:45:00'),
('Historia del Arte', 'Ernst Gombrich', 'book', 'Arte', NULL, 'Historia completa del arte occidental', 4, '2025-10-20 21:00:00'),
('Educación Física y Salud', 'OMS', 'document', 'Educación Física', NULL, 'Guías de la Organización Mundial de la Salud', 2, '2025-10-20 21:15:00'),
('Tutorial de Programación en Python', 'Guido van Rossum', 'video', 'Informática', NULL, 'Curso completo de Python para principiantes', 3, '2025-10-20 21:30:00'),
('Revista National Geographic - Volumen 2025', 'National Geographic', 'article', 'Ciencias', NULL, 'Edición especial sobre cambio climático', 5, '2025-10-20 21:45:00'),
('Contabilidad para Empresas', 'Warren Buffett', 'document', 'Administración', NULL, 'Principios contables aplicados', 3, '2025-10-20 22:00:00'),
('Electrónica Digital', 'Morris Mano', 'book', 'Electrónica', NULL, 'Circuitos y sistemas digitales', 4, '2025-10-20 22:15:00'),
('Mecánica de Fluidos', 'Frank White', 'book', 'Ingeniería', NULL, 'Principios de mecánica de fluidos', 5, '2025-10-20 22:30:00');

-- =====================================================
-- PHYSICAL BOOKS DATA
-- =====================================================

INSERT INTO physical_books (title, author, isbn, category, description, total_copies, available_copies, location, added_date, status) VALUES
('Cálculo Integral', 'James Stewart', '978-607-526-123-4', 'Matemáticas', 'Texto completo de cálculo integral', 3, 3, 'Estante A-1', '2025-10-20 22:45:00', 'available'),
('Física Moderna', 'Serway & Jewett', '978-607-481-567-8', 'Física', 'Física para estudiantes de ingeniería', 2, 2, 'Estante B-2', '2025-10-20 23:00:00', 'available'),
('Química Analítica', 'Skoog', '978-849-908-901-2', 'Química', 'Métodos analíticos en química', 1, 1, 'Estante C-3', '2025-10-20 23:15:00', 'available'),
('Biología Celular', 'Alberts', '978-849-908-234-5', 'Biología', 'Biología celular y molecular', 2, 2, 'Estante D-1', '2025-10-20 23:30:00', 'available'),
('Historia Contemporánea', 'Eric Hobsbawm', '978-843-396-789-0', 'Historia', 'Historia del siglo XX', 1, 1, 'Estante E-2', '2025-10-20 23:45:00', 'available'),
('Geografía Humana', 'Yi-Fu Tuan', '978-843-396-456-7', 'Geografía', 'Perspectivas humanas en geografía', 1, 1, 'Estante F-1', '2025-10-21 00:00:00', 'available'),
('Gramática Española', 'Real Academia Española', '978-846-705-123-9', 'Lenguaje', 'Gramática completa del español', 2, 2, 'Estante G-3', '2025-10-21 00:15:00', 'available'),
('English Grammar in Use', 'Raymond Murphy', '978-052-118-906-4', 'Inglés', 'Gramática práctica del inglés', 3, 3, 'Estante H-1', '2025-10-21 00:30:00', 'available'),
('Teoría del Color', 'Johannes Itten', '978-843-430-567-1', 'Arte', 'Principios del color en el arte', 1, 1, 'Estante I-2', '2025-10-21 00:45:00', 'available'),
('Fisiología del Ejercicio', 'McArdle', '978-849-935-890-3', 'Educación Física', 'Fisiología aplicada al ejercicio', 1, 1, 'Estante J-1', '2025-10-21 01:00:00', 'available'),
('Estructuras de Datos en Java', 'Goodrich', '978-607-946-234-6', 'Informática', 'Algoritmos y estructuras de datos', 2, 2, 'Estante K-2', '2025-10-21 01:15:00', 'available'),
('Electrónica de Potencia', 'Rashid', '978-607-481-789-2', 'Electrónica', 'Sistemas de electrónica de potencia', 1, 1, 'Estante L-3', '2025-10-21 01:30:00', 'available'),
('Máquinas Herramienta', 'Krar', '978-607-526-456-8', 'Mecánica', 'Operación de máquinas herramienta', 1, 1, 'Estante M-1', '2025-10-21 01:45:00', 'available'),
('Materiales de Construcción', 'Neville', '978-849-908-567-4', 'Construcción', 'Propiedades de materiales de construcción', 2, 2, 'Estante N-2', '2025-10-21 02:00:00', 'available'),
('Principios de Mercadotecnia', 'Kotler', '978-607-481-890-1', 'Administración', 'Fundamentos del marketing moderno', 1, 1, 'Estante O-3', '2025-10-21 02:15:00', 'available'),
('Contabilidad Financiera', 'Warren', '978-607-526-789-3', 'Contabilidad', 'Estados financieros y análisis', 2, 2, 'Estante P-1', '2025-10-21 02:30:00', 'available');

-- =====================================================
-- BOOK LOANS DATA
-- =====================================================

INSERT INTO book_loans (book_id, user_id, loan_date, due_date, return_date, status, notes) VALUES
(1, 3, '2025-10-15 10:00:00', '2025-10-29 10:00:00', '2025-10-25 14:00:00', 'returned', 'Devuelto en buen estado'),
(2, 4, '2025-10-18 11:00:00', '2025-11-01 11:00:00', NULL, 'active', 'Préstamo activo'),
(3, 5, '2025-10-20 09:00:00', '2025-11-03 09:00:00', NULL, 'active', 'Estudiante de química'),
(4, 6, '2025-10-22 16:00:00', '2025-11-05 16:00:00', NULL, 'active', 'Proyecto de investigación'),
(1, 7, '2025-10-12 13:00:00', '2025-10-26 13:00:00', '2025-10-24 15:00:00', 'returned', 'Devuelto con retraso mínimo'),
(5, 8, '2025-10-19 08:00:00', '2025-11-02 08:00:00', NULL, 'overdue', 'Vencido - requiere seguimiento'),
(6, 9, '2025-10-21 12:00:00', '2025-11-04 12:00:00', NULL, 'active', 'Preparación para examen'),
(2, 10, '2025-10-17 14:00:00', '2025-10-31 14:00:00', '2025-10-30 16:00:00', 'returned', 'Devuelto justo a tiempo');

-- =====================================================
-- SUCCESS MESSAGE
-- =====================================================

SELECT 'Test data inserted successfully! The system now has comprehensive test data for all modules.' as message;