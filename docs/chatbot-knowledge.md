# Base de Conocimiento - Asistente Virtual ETC Pedro García Leal

## Información Institucional

### Escuela Técnica Comercial "Pedro García Leal"
- **Nombre completo**: Escuela Técnica Comercial "Pedro García Leal"
- **Tipo**: Institución educativa técnica comercial
- **Ubicación**: Venezuela
- **Misión**: Excelencia en educación técnica y formación integral para jóvenes venezolanos
- **Contacto**:
  - Email: ETCPedroGarciaLeal@gmail.com
  - Teléfono: 0271-8289378
  - Dirección: Calle 18 entre Av. 4 y Bolivariana Sector las Acacias.

### Sistema de Gestión
- **Nombre**: Sistema de Gestión ETC "Pedro García Leal"
- **Propósito**: Plataforma unificada para gestión académica y administrativa
- **Tecnologías**:
  - Backend: PHP 7.4+
  - Base de datos: MySQL
  - Frontend: HTML5, CSS3, JavaScript, Tailwind CSS
  - API: REST completa

## Roles de Usuario

### Administrador
**Funcionalidades principales**:
- Gestión completa de usuarios (crear, editar, eliminar)
- Gestión de cursos y asignaturas
- Gestión de matrículas (matricular/desmatricular estudiantes)
- Gestión de préstamos de biblioteca con estadísticas
- Generación de reportes avanzados con gráficos
- Configuración del sistema y parámetros globales
- Gestión de biblioteca virtual (subida, organización, eliminación de recursos)
- Programación de horarios de aulas con detección de conflictos
- Gestión de actividades académicas

### Docente
**Funcionalidades principales**:
- Gestión de matrículas en cursos asignados
- Registro y modificación de calificaciones
- Control de asistencia con reportes visuales
- Creación, asignación y calificación de actividades
- Acceso a biblioteca virtual
- Gestión de horarios de clases
- Perfil personal con estadísticas de enseñanza

### Estudiante
**Funcionalidades principales**:
- Auto-matrícula en cursos disponibles
- Consulta de notas y promedio académico
- Historial académico completo con GPA
- Visualización y subida de actividades asignadas
- Acceso a biblioteca virtual y sistema de préstamos
- Consulta de horarios de clases matriculados
- Gestión de perfil personal

## Módulos del Sistema

### 1. Gestión Administrativa y Académica
- Gestión de usuarios por roles
- Administración de cursos y asignaturas
- Sistema de matrículas
- Control de asistencia
- Gestión de calificaciones
- Reportes y estadísticas

### 2. Biblioteca Virtual Avanzada
- Catálogo digital completo de recursos educativos
- Búsqueda avanzada por autor, fecha, tipo, asignatura
- Sistema de préstamos con control de fechas y límites
- Tipos de recursos: libros, artículos, videos, documentos
- Estadísticas de uso y recursos disponibles
- Gestión de recursos multimedia

#### Recursos Disponibles en la Biblioteca
La biblioteca virtual contiene diversos recursos educativos organizados por categorías:

**Documentos y Certificados:**
- Certificados de cursos de Python (disponibles para descarga)
- Documentos técnicos y guías de estudio

**Tipos de Recursos:**
- Documentos PDF con certificados y materiales de estudio
- Recursos organizados por asignaturas técnicas
- Materiales actualizados regularmente por docentes

**Acceso a Recursos:**
- Todos los estudiantes pueden acceder a la biblioteca virtual
- Descarga directa de recursos disponibles
- Búsqueda por título, autor o asignatura
- Recursos organizados por tipo: documentos, libros, artículos

### 3. Gestión de Horarios Inteligentes
- Programación de horarios con detección automática de conflictos
- Filtros por semestre, año académico, estado
- Control de conflictos entre aulas y docentes
- Acciones masivas (activación, cancelación, eliminación)
- Estados: activo, cancelado, completado
- Estadísticas en tiempo real de ocupación

### 4. Sistema de Actividades y Tareas
- Creación y asignación de actividades por docentes
- Subida de entregas por estudiantes
- Revisión y calificación por docentes
- Tipos: subida de archivos, texto en línea, exámenes
- Seguimiento de estado (pendiente, entregado, calificado)

### 5. Portal del Estudiante
- Consulta de notas y promedios
- Historial académico
- Matrícula de cursos
- Gestión de actividades
- Biblioteca virtual
- Perfil personal

### 6. Panel del Docente
- Gestión de calificaciones
- Control de asistencia
- Creación de actividades
- Biblioteca
- Horarios de clases

### 7. Panel de Administración
- Gestión de usuarios
- Administración de cursos
- Reportes avanzados
- Configuración del sistema
- Gestión de biblioteca
- Control de horarios

## Base de Datos

### Tablas Principales
- **users**: Usuarios del sistema (admin, teacher, student)
- **courses**: Cursos y asignaturas
- **enrollments**: Inscripciones de estudiantes en cursos
- **classrooms**: Aulas y espacios educativos
- **schedules**: Horarios de clases
- **library_resources**: Recursos de la biblioteca virtual
- **loans**: Sistema de préstamos
- **activities**: Actividades y tareas académicas
- **submissions**: Entregas de actividades
- **attendance**: Control de asistencia

## API REST

### Endpoints Disponibles
- Cursos: `/api/courses`
- Usuarios: `/api/users`
- Biblioteca: `/api/library`
- Actividades: `/api/activities`
- Asistencia: `/api/attendance`
- Horarios: `/api/schedules`

### Características
- Autenticación basada en roles
- Respuestas en formato JSON
- Control de acceso granular

## Usuarios de Prueba

### Administrador
- Usuario: admin
- Contraseña: password

### Docente
- Usuario: teacher1
- Contraseña: password

### Estudiante
- Usuario: student1
- Contraseña: password

### Estudiantes Adicionales
- juan.perez.alumno, maria.garcia.alumna, carlos.sanchez, ana.torres, luis.ramirez, etc.

### Profesores Adicionales
- maria.gonzalez.prof, carlos.rodriguez.prof, ana.lopez.prof, pedro.martinez.prof, laura.sanchez.prof

## Instalación y Configuración

### Requisitos
- Servidor web (Apache/Nginx)
- PHP 7.4 o superior
- MySQL 5.7 o superior
- XAMPP/WAMP recomendado

### Pasos de Instalación
1. Clonar repositorio
2. Configurar entorno local
3. Crear base de datos 'biblioteca'
4. Ejecutar script database.sql
5. Configurar includes/config.php
6. Acceder vía http://localhost/biblioteca

## Características de Interfaz

### Diseño
- **Responsivo**: Compatible con móviles y desktop
- **Intuitivo**: Navegación clara y amigable
- **Animaciones**: Transiciones suaves y efectos visuales
- **Tema Personalizado**: Colores institucionales
- **Accesibilidad**: Contraste adecuado

### Paleta de Colores
- Primary: #D4AF37 (Amarillo dorado)
- Secondary: #8B4513 (Marrón)
- Accent: #F4E4BC (Amarillo claro)
- Dark: #654321 (Marrón oscuro)

## Funcionalidades Avanzadas

- Sistema de roles completo
- Validación de datos robusta
- Sesiones seguras
- Reportes en tiempo real
- Subida de archivos segura
- API REST integrada
- Interfaz responsiva
- Búsqueda avanzada
- Perfiles de usuario
- Estadísticas detalladas

## Soporte y Contacto

- **Email**: ETCPedroGarciaLeal@gmail.com
- **Documentación**: Disponible en panel de administración
- **Desarrolladores**: Contactar para soporte técnico

---

*Esta base de conocimiento está diseñada para proporcionar información completa sobre la institución ETC Pedro García Leal y su sistema de gestión. El asistente virtual debe limitar sus respuestas únicamente a temas relacionados con la institución y el sistema.*