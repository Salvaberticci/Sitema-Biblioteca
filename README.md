# Sistema de Gestión ETC "Pedro García Leal"

Un sistema web integral para la gestión académica y administrativa de la Escuela Técnica Comercial "Pedro García Leal", desarrollado en PHP con MySQL.

## 📋 Descripción

Este proyecto es un sistema de gestión completo diseñado para instituciones educativas técnicas comerciales. Ofrece una plataforma unificada para estudiantes, docentes y administradores, facilitando la gestión de cursos, biblioteca virtual, horarios de aulas, actividades académicas y más.

## 🚀 Características Principales

### 👨‍💼 Panel de Administración
- **Gestión de Usuarios**: Crear, editar y gestionar usuarios del sistema (estudiantes, docentes, administradores)
- **Gestión de Cursos**: Administrar programas académicos y asignaturas
- **Reportes Avanzados**: Generar estadísticas y reportes académicos detallados
- **Configuración del Sistema**: Parámetros globales y ajustes institucionales

### 👨‍🏫 Panel del Docente
- **Gestión de Notas**: Registrar y modificar calificaciones de estudiantes
- **Control de Asistencia**: Marcar asistencia en clases
- **Actividades y Tareas**: Crear, asignar y calificar actividades académicas
- **Acceso a Biblioteca**: Recursos educativos digitales

### 👨‍🎓 Portal del Estudiante
- **Consulta de Notas**: Ver calificaciones y promedio académico
- **Historial Académico**: Historial completo de cursos y notas
- **Actividades**: Ver y subir tareas asignadas
- **Biblioteca Virtual**: Buscar y descargar recursos educativos

### 📚 Biblioteca Virtual
- Catálogo digital de recursos educativos
- Búsqueda y préstamo de materiales
- Gestión de recursos multimedia (libros, artículos, videos, documentos)

### 🏫 Horarios de Aulas
- Programación y reserva de horarios de salones escolares
- Gestión de disponibilidad de espacios educativos
- Visualización de horarios por aula y docente

### 📊 Base de Datos Segura
- Almacenamiento organizado de información administrativa y académica
- Medidas de seguridad avanzadas
- Integridad de datos con claves foráneas

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
├── index.php                 # Página principal
├── login.php                 # Sistema de autenticación
├── logout.php                # Cierre de sesión
├── dashboard.php             # Dashboard principal
├── database.sql              # Esquema de base de datos
├── assets/
│   ├── css/
│   │   └── style.css         # Estilos personalizados
│   ├── js/
│   │   └── main.js           # Scripts JavaScript
│   └── images/               # Imágenes del proyecto
├── includes/
│   ├── config.php            # Configuración de base de datos
│   └── functions.php         # Funciones utilitarias
├── modules/
│   ├── admin/                # Módulos de administración
│   │   ├── dashboard.php
│   │   ├── users.php
│   │   ├── courses.php
│   │   ├── reports.php
│   │   └── settings.php
│   ├── student/              # Módulos de estudiantes
│   │   ├── dashboard.php
│   │   ├── grades.php
│   │   ├── activities.php
│   │   └── history.php
│   ├── teacher/              # Módulos de docentes
│   │   ├── dashboard.php
│   │   ├── grades.php
│   │   ├── activities.php
│   │   └── attendance.php
│   ├── library/              # Biblioteca virtual
│   │   ├── index.php
│   │   └── manage.php
│   └── schedules/            # Horarios de aulas
│       ├── manage.php
│       └── view.php
└── templates/
    ├── header.php            # Plantilla de cabecera
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

## 📈 Funcionalidades Avanzadas

- **Sistema de Roles**: Control de acceso basado en roles de usuario
- **Validación de Datos**: Sanitización y validación de entradas
- **Gestión de Sesiones**: Seguridad de autenticación
- **Reportes en Tiempo Real**: Estadísticas actualizadas
- **Subida de Archivos**: Gestión segura de archivos multimedia

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
- Funcionalidades completas de gestión académica
- Sistema de biblioteca virtual
- Control de horarios y aulas

---

**Escuela Técnica Comercial "Pedro García Leal"**  
*Excelencia en educación técnica y formación integral para jóvenes venezolanos*