# Base de Conocimiento - Sistema de Biblioteca y Gestión Académica

Este documento contiene información detallada sobre el funcionamiento del sistema para usuarios y administradores.

## Preguntas Generales

### ¿Para qué sirve la página?
Es un sistema integral de gestión institucional que permite la administración de recursos bibliográficos (físicos y digitales), control de horarios de clase, gestión de menciones académicas (especialidades), seguimiento de actividades de estudiantes y registro de calificaciones y asistencia.

### ¿Cómo me puedo registrar?
El registro de nuevos usuarios (estudiantes, profesores, administradores) es gestionado exclusivamente por el administrador del sistema en el módulo de `Usuarios`. No existe una opción de auto-registro por motivos de seguridad institucional.

---

## Estudiantes

### ¿Cómo puedo enviar mis tareas?
En el módulo de estudiantes (`Mis Actividades`), selecciona la actividad correspondiente y utiliza el botón de "Subir Entrega" para adjuntar tu archivo.

### ¿Cómo me puedo matricular en una mención?
En el panel de estudiante, accede a `Matrícula de Menciones`. Allí verás las menciones disponibles; pulsa "Matricularme" en la que desees cursar.

### ¿Cómo puedo acceder a los horarios de clase?
En la sección `Mi Horario`, podrás ver las clases programadas para tus menciones matriculadas, organizadas por día y hora.

### ¿Cómo me asigno a una mención (especialidad) disponible?
Mediante el módulo de `Matrícula` en el panel de estudiante.

### ¿Puedo cambiarme de mención durante el ciclo?
No directamente desde el panel. Debes solicitar al administrador que anule tu matrícula actual para poder inscribirte en otra.

---

## Biblioteca

### ¿Cómo puedo registrar un préstamo de libro?
En la `Biblioteca Virtual`, busca el libro físico deseado. Si hay copias disponibles, haz clic en el botón "Pedir Préstamo". El sistema registrará el préstamo automáticamente si no has excedido tu límite.

### ¿Puedo buscar libros por autor, título o tema?
Sí, utiliza la barra de búsqueda en la Biblioteca Virtual ingresando cualquiera de estos criterios. También puedes usar la búsqueda avanzada para filtrar por fechas o tipos específicos.

### ¿Cómo descargo o visualizo un recurso digital?
En la Biblioteca Virtual, los recursos marcados como digitales tienen un botón de "Descargar". Al pulsarlo, el archivo se bajará a tu dispositivo.

### ¿Cuántos libros puedo llevarme al mismo tiempo?
El límite máximo es de **3 libros físicos** simultáneamente.

### ¿Por cuánto tiempo puedo tener un libro en préstamo?
El periodo estándar de préstamo es de **14 días** (2 semanas).

### ¿Puedo renovar un préstamo? ¿Cómo?
Actualmente el sistema no cuenta con renovación automática. Debes devolver el libro y volverlo a pedir si sigue habiendo disponibilidad.

### ¿Qué pasa si entrego un libro tarde?
El estado del préstamo cambiará automáticamente a "Vencido" (overdue). Esto queda registrado en tu historial y podría afectar tu prioridad para futuros préstamos.

### ¿Qué hago si un recurso no está disponible o no puedo abrirlo?
Verifica tu conexión a internet. Si el problema persiste, contacta al administrador para verificar la integridad del archivo en el servidor.

### ¿La biblioteca tiene acceso a periódicos o revistas actuales?
Sí, los administradores pueden subir periódicos y revistas como recursos de tipo "artículo" o "documento".

### ¿Qué debo hacer si pierdo o daño un recurso prestado?
Debes notificarlo formalmente a la administración o al personal de la biblioteca para coordinar la reposición o sanción correspondiente.

### ¿Hay salas de estudio grupal y cómo se reservan?
Actualmente el sistema no gestiona la reserva de espacios físicos como salas de estudio.

### ¿No sé qué leer, qué me recomiendas?
Explora la sección de "Recursos más populares" o filtra por tu asignatura de estudio para encontrar materiales recomendados por tus docentes.

---

## Profesores y Calificaciones

### ¿Cómo califico las tareas entregadas por los estudiantes?
En el módulo `Mis Actividades`, selecciona una actividad y verás la lista de entregas. Pulsa "Calificar" e ingresa la nota y comentarios pedagógicos.

### ¿Los estudiantes pueden ver sus calificaciones de tareas?
Sí, en cuanto el profesor guarda la calificación, el estudiante puede verla junto con los comentarios en su panel personal.

### ¿Cómo marco la asistencia diaria de mis estudiantes?
Accede a `Control de Asistencia`, selecciona la mención y la fecha, y marca el estado de cada estudiante (Presente, Ausente, Tarde o Justificado).

### ¿Puedo tomar asistencia de manera masiva o por lista?
Sí, el sistema carga la lista completa de estudiantes matriculados para que marques a todos en el mismo formulario y guardes los cambios de una vez.

### ¿Qué hago si marqué asistencia incorrectamente?
Puedes volver a seleccionar la misma fecha y curso, corregir los estados y guardar de nuevo. El sistema sobrescribirá los datos anteriores.

### ¿Cómo registro las calificaciones finales?
Utiliza el módulo `Gestión de Calificaciones` para ingresar las notas definitivas de la mención.

### ¿Cómo ingreso notas parciales o definitivas?
Las notas de actividades actúan como parciales; la nota cargada en el módulo de "Calificaciones" se considera la definitiva del periodo.

### ¿Puedo modificar una nota después de haberla guardado?
Sí, el profesor tiene permisos para actualizar las notas en el panel de calificaciones mientras el periodo esté abierto.

### ¿Qué hago si el sistema no me deja registrar una nota?
Asegúrate de que la nota esté en el rango de **0 a 20**. Si el error persiste, verifica que el estudiante siga matriculado activamente.

### ¿Cómo puedo exportar las notas de todo el curso a Excel?
Los administradores pueden generar este reporte en la sección de `Reportes` y exportarlo directamente a Excel.

---

## Gestión de Menciones (Administración)

### ¿Qué son las “menciones” en el sistema y para qué sirven?
Las menciones representan las especialidades, carreras o cursos académicos. Sirven para agrupar estudiantes, asignar docentes y organizar horarios.

### ¿Cómo configuro una nueva mención o especialidad?
Desde el panel de Administrador en `Gestión de Menciones`, pulsa "Crear Nueva Mención" e ingresa el código, nombre y créditos correspondientes.

### ¿Cómo asocio asignaturas a una mención específica?
En la arquitectura actual, la mención es la unidad principal. Las clases y horarios se asocian directamente a la mención creada.

### ¿Es posible modificar el plan de estudios de una mención?
Sí, puedes editar el nombre, descripción y créditos de cualquier mención desde la lista de gestión administrativa.

### ¿Cómo consulto qué estudiantes están inscritos en una mención?
En el panel de Administrador o Profesor, accede a `Matrículas` y utiliza el filtro por mención para obtener la lista completa.

---

## Reportes y Datos Personales

### ¿Qué tipos de reportes puedo generar?
El sistema genera reportes de:
*   Matrículas por curso y periodo.
*   Asistencia detallada.
*   Cuadros de calificaciones.
*   Movimientos de biblioteca (préstamos/devoluciones).

### ¿Cómo puedo filtrar un reporte?
Puedes filtrar por rangos de fecha (Desde/Hasta), por mención/curso, por estudiante específico o por estado del registro.

### ¿Cómo exporto un reporte a Excel o PDF?
Tras generar el reporte en pantalla, utiliza los botones `Exportar a Excel` o `Exportar a PDF` ubicados sobre la tabla de resultados.

### ¿Cómo actualizo mi información personal?
Entra a tu `Perfil` en la barra de navegación. Podrás actualizar tu teléfono, correo electrónico y otros datos de contacto.

### ¿Puedo cambiar mi contraseña desde el panel?
Sí, en la sección de `Perfil` encontrarás los campos necesarios para actualizar tu contraseña de acceso.
