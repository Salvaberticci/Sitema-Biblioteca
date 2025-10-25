# Sistema de Gestión ETC "Pedro García Leal"

Un sistema web integral para la gestión académica y administrativa de la Escuela Técnica Comercial "Pedro García Leal", desarrollado en PHP con MySQL.

## 📋 Descripción

Este proyecto es un sistema de gestión completo diseñado para instituciones educativas técnicas comerciales. Ofrece una plataforma unificada para estudiantes, docentes y administradores, facilitando la gestión de cursos, biblioteca virtual, horarios de aulas, actividades académicas y más.

## 🚀 Características Principales

### 👨‍💼 Panel de Administración
- **Gestión de Usuarios**: Crear, editar y gestionar usuarios del sistema (estudiantes, docentes, administradores)
- **Gestión de Cursos**: Administrar programas académicos y asignaturas
- **Gestión de Matrículas**: Matricular estudiantes en cursos, ver lista completa de matrículas y desmatricular estudiantes
- **Gestión de Préstamos**: Control completo de préstamos de biblioteca con estadísticas
- **Reportes Avanzados**: Generar estadísticas y reportes académicos detallados con gráficos
- **Configuración del Sistema**: Parámetros globales y ajustes institucionales
- **Perfil de Administrador**: Gestión personal de cuenta y estadísticas del sistema
- **Mantenimiento del Sistema**: Respaldos, limpieza de caché y operaciones de sistema

### 👨‍🏫 Panel del Docente
- **Gestión de Matrículas**: Matricular y desmatricular estudiantes en cursos asignados
- **Gestión de Notas**: Registrar y modificar calificaciones de estudiantes con estadísticas detalladas
- **Control de Asistencia**: Marcar asistencia en clases con reportes visuales
- **Actividades y Tareas**: Crear, asignar y calificar actividades académicas
- **Acceso a Biblioteca**: Recursos educativos digitales
- **Perfil de Docente**: Información personal y estadísticas de enseñanza
- **Horarios de Clases**: Visualización de horarios asignados

### 👨‍🎓 Portal del Estudiante
- **Matrícula de Cursos**: Auto-matriculación en cursos disponibles con vista de cursos matriculados
- **Consulta de Notas**: Ver calificaciones y promedio académico con análisis detallado
- **Historial Académico**: Historial completo de cursos y notas con GPA
- **Actividades**: Ver y subir tareas asignadas con estado de calificación
- **Biblioteca Virtual**: Buscar, descargar y gestionar préstamos de recursos educativos
- **Sistema de Préstamos**: Solicitar y devolver recursos prestados con seguimiento
- **Perfil de Estudiante**: Gestión de información personal y estadísticas académicas
- **Horarios de Clases**: Visualización personalizada de horarios matriculados

### 📚 Biblioteca Virtual Avanzada
- **Catálogo Digital Completo**: Recursos educativos organizados por tipo y asignatura
- **Búsqueda Avanzada**: Filtros por autor, fecha, tipo, asignatura y ordenamiento múltiple
- **Sistema de Préstamos**: Gestión completa de préstamos de recursos con control de fechas y límites
- **Estadísticas de Biblioteca**: Métricas de uso, recursos disponibles y préstamos activos
- **Gestión de Recursos**: Subida, organización y eliminación de materiales multimedia
- **Tipos de Recursos**: Libros, artículos, videos, documentos con iconografía distintiva
- **Control de Disponibilidad**: Verificación automática de recursos disponibles para préstamo
- **Historial de Préstamos**: Seguimiento completo de préstamos por usuario y recurso

### 🏫 Gestión Completa de Horarios Inteligentes
- **Programación con IA**: Creación de horarios con detección automática de conflictos
- **Gestión Avanzada**: Filtros por semestre, año académico y estado de horarios
- **Control de Conflictos**: Prevención de superposiciones entre aulas y docentes
- **Acciones Masivas**: Activación, cancelación y eliminación de múltiples horarios
- **Estados de Horarios**: Activo, cancelado, completado con seguimiento completo
- **Estadísticas en Tiempo Real**: Métricas de ocupación y conflictos diarios
- **Edición Completa**: Modificación de todos los parámetros con validaciones
- **Visualización Mejorada**: Información detallada con estado, notas y estadísticas
- **Base de Datos Extendida**: Campos adicionales para mejor gestión académica

### 📊 Sistema de Reportes Avanzado
- **Reportes por Categoría**: Usuarios, cursos, calificaciones, actividades
- **Estadísticas Visuales**: Gráficos de distribución y métricas
- **Exportación de Datos**: Preparado para futuras funcionalidades de exportación
- **Análisis en Tiempo Real**: Estadísticas actualizadas del sistema

### 🔐 Sistema de Perfiles de Usuario
- **Perfiles por Rol**: Interfaces personalizadas para admin, docente y estudiante
- **Gestión de Contraseñas**: Cambio seguro con validaciones
- **Estadísticas Personales**: Métricas específicas según rol
- **Información Personal**: Actualización de datos con validaciones

### 🌐 API REST
- **Endpoints Completos**: Cursos, usuarios, biblioteca, actividades, asistencia, horarios
- **Autenticación**: Control de acceso basado en roles
- **Formatos JSON**: Respuestas estructuradas para integraciones
- **Documentación Lista**: Preparado para futuras expansiones

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Tailwind CSS
- **Iconos**: Font Awesome
- **Animaciones**: CSS Animations

## 📁 Estructura del Proyecto

```
biblioteca/
├── index.php                 # Página principal con landing page
├── login.php                 # Sistema de autenticación
├── logout.php                # Cierre de sesión
├── dashboard.php             # Redireccionamiento según rol
├── database.sql              # Esquema completo de base de datos
├── api/
│   └── index.php             # API REST para integraciones
├── assets/
│   ├── css/
│   │   └── style.css         # Estilos Tailwind CSS personalizados
│   ├── js/
│   │   └── main.js           # Scripts JavaScript
│   └── images/               # Imágenes institucionales
├── includes/
│   ├── config.php            # Configuración de base de datos y sesiones
│   └── functions.php         # Funciones utilitarias y helpers
├── modules/
│   ├── admin/                # Panel completo de administración
│   │   ├── dashboard.php     # Dashboard con estadísticas
│   │   ├── users.php         # Gestión completa de usuarios
│   │   ├── courses.php       # Administración de cursos
│   │   ├── enrollments.php   # Gestión completa de matrículas
│   │   ├── reports.php       # Reportes avanzados con gráficos
│   │   ├── settings.php      # Configuración del sistema
│   │   └── profile.php       # Perfil de administrador
│   ├── student/              # Portal completo del estudiante
│   │   ├── dashboard.php     # Dashboard del estudiante
│   │   ├── enroll.php        # Sistema de matrícula de cursos
│   │   ├── grades.php        # Consulta de calificaciones
│   │   ├── activities.php    # Gestión de actividades
│   │   ├── history.php       # Historial académico completo
│   │   └── profile.php       # Perfil personal del estudiante
│   ├── teacher/              # Panel completo del docente
│   │   ├── dashboard.php     # Dashboard del docente
│   │   ├── enrollments.php   # Gestión de matrículas
│   │   ├── grades.php        # Gestión de calificaciones
│   │   ├── activities.php    # Gestión de actividades
│   │   ├── attendance.php    # Control de asistencia
│   │   └── profile.php       # Perfil personal del docente
│   ├── library/              # Biblioteca virtual avanzada
│   │   ├── index.php         # Catálogo con búsqueda avanzada
│   │   ├── loans.php         # Gestión de préstamos
│   │   └── manage.php        # Gestión de recursos (admin)
│   └── schedules/            # Sistema de horarios inteligentes
│       ├── manage.php        # Gestión de horarios (admin)
│       └── view.php          # Visualización por roles
└── templates/
    ├── header.php            # Plantilla de cabecera con navegación
    └── footer.php            # Plantilla de pie de página
```

## 🗄️ Esquema de Base de Datos

### Tablas Principales

- **users**: Usuarios del sistema (admin, teacher, student, staff)
- **courses**: Cursos y asignaturas
- **enrollments**: Inscripciones de estudiantes en cursos
- **classrooms**: Aulas y espacios educativos
- **schedules**: Horarios de clases
- **library_resources**: Recursos de la biblioteca virtual
- **loans**: Sistema de préstamos de recursos de biblioteca
- **activities**: Actividades y tareas académicas
- **submissions**: Entregas de actividades por estudiantes
- **attendance**: Control de asistencia

## ⚙️ Instalación y Configuración

### Prerrequisitos
- Servidor web (Apache/Nginx)
- PHP 7.4 o superior
- MySQL 5.7 o superior
- XAMPP/WAMP (recomendado para desarrollo local)

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/Salvaberticci/Sitema-Biblioteca.git
   cd Sitema-Biblioteca
   ```

2. **Configurar el entorno**
   - Copiar el proyecto a la carpeta `htdocs` de XAMPP
   - Iniciar Apache y MySQL en XAMPP

3. **Configurar la base de datos**
   - Crear base de datos MySQL llamada `biblioteca`
   - Ejecutar el script `database.sql` para crear las tablas
   - Verificar la configuración en `includes/config.php`

4. **Configurar permisos**
   - Asegurar que las carpetas `assets/` y `modules/` tengan permisos de escritura

5. **Acceder al sistema**
   - Abrir navegador en `http://localhost/biblioteca`
   - Credenciales por defecto:
     - Usuario: `admin`
     - Contraseña: `password`

## 🔐 Usuarios de Prueba

El sistema incluye usuarios de prueba preconfigurados:

- **Administrador**: admin / password
- **Docente**: teacher1 / password
- **Estudiante**: student1 / password

## 🎨 Características de Interfaz

- **Diseño Responsivo**: Compatible con dispositivos móviles y desktop
- **Interfaz Intuitiva**: Navegación clara y amigable
- **Animaciones**: Transiciones suaves y efectos visuales
- **Tema Personalizado**: Colores institucionales de la ETC "Pedro García Leal"
- **Accesibilidad**: Diseño inclusivo con contraste adecuado

## 🦶 Sticky Footer Implementation

The system incorporates a sticky footer design that ensures the footer remains anchored at the bottom of the viewport when page content is shorter than the screen height, maintaining a consistent and professional layout across all pages.

### Implementation Details

- **CSS Flexbox Layout**: Utilizes a flexbox container applied to the page structure with `min-height: 100vh` and `flex-direction: column` for vertical stacking.
- **Main Content Expansion**: The main content wrapper employs `flex-grow: 1` to dynamically fill available vertical space, effectively pushing the footer to the bottom.
- **Footer Positioning**: The footer is naturally positioned at the end of the flex container, guaranteeing it "sticks" to the bottom when content is minimal.
- **Responsive Compatibility**: Fully responsive implementation that adapts seamlessly to all device sizes and screen resolutions.

This technique enhances user experience by providing visual consistency and preventing awkward spacing on pages with limited content.

## 📈 Funcionalidades Avanzadas

- **Sistema de Roles Completo**: Control de acceso granular por roles (admin, teacher, student)
- **Validación de Datos Robusta**: Sanitización, validación y manejo de errores avanzado
- **Gestión de Sesiones Segura**: Autenticación con sesiones persistentes y logout automático
- **Reportes en Tiempo Real**: Estadísticas dinámicas con gráficos y métricas actualizadas
- **Subida de Archivos Segura**: Gestión de archivos multimedia con validación de tipos
- **API REST Integrada**: Endpoints para todas las entidades del sistema
- **Interfaz Responsiva**: Diseño adaptativo para dispositivos móviles y desktop
- **Búsqueda Avanzada**: Filtros múltiples y ordenamiento inteligente
- **Perfiles de Usuario**: Gestión personal completa para todos los roles
- **Estadísticas Detalladas**: Métricas específicas por rol y funcionalidad

## 🤝 Contribución

1. Fork el proyecto
2. Crear rama para nueva funcionalidad (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver archivo `LICENSE` para más detalles.

## 👥 Soporte

Para soporte técnico o consultas:
- Email: soporte@etcpedrogarcialeal.edu.ve
- Documentación interna disponible en el panel de administración

## 🔄 Versiones

- **v1.0.0**: Versión inicial con funcionalidades básicas
- **v1.1.0**: ✨ Grandes mejoras implementadas
  - ✅ Módulos de calificaciones completos para docentes y estudiantes
  - ✅ Sistema de perfiles de usuario para todos los roles
  - ✅ Búsqueda avanzada en biblioteca virtual con filtros múltiples
  - ✅ Sistema de préstamos de biblioteca con control completo
  - ✅ API REST completa para integraciones futuras
  - ✅ Interfaz mejorada con estadísticas detalladas
  - ✅ Validaciones robustas y manejo de errores
  - ✅ Funcionalidades completas de gestión académica
  - ✅ Sistema de biblioteca virtual avanzado
  - ✅ Control inteligente de horarios y aulas
- **v1.2.0**: 🎓 Sistema de Matrículas Implementado
  - ✅ Gestión completa de matrículas para administradores
  - ✅ Auto-matriculación para estudiantes con interfaz intuitiva
  - ✅ Validaciones robustas contra matrículas duplicadas
  - ✅ Vista integrada de cursos matriculados y disponibles
  - ✅ Control de acceso granular por roles
  - ✅ Integración completa con módulos de docentes (notas, asistencia)
  - ✅ Estadísticas actualizadas en dashboards administrativos
  - ✅ Interfaz responsiva con animaciones y diseño moderno

---

**Escuela Técnica Comercial "Pedro García Leal"**  
*Excelencia en educación técnica y formación integral para jóvenes venezolanos*