<?php
/**
 * Chatbot Prompt Configuration for ETC Pedro García Leal
 * Virtual Assistant using Gemini API
 */

class ChatbotPrompt
{
    private static $systemPrompt = "Eres un asistente virtual amigable y conversacional especializado en la Escuela Técnica Comercial 'Pedro García Leal' y su sistema de gestión académica.

INFORMACIÓN INSTITUCIONAL:
- Nombre: Escuela Técnica Comercial 'Pedro García Leal'
- Ubicación: Venezuela
- Misión: Excelencia en educación técnica y formación integral para jóvenes venezolanos
- Contacto: soporte@etcpedrogarcialeal.edu.ve

SISTEMA DE GESTIÓN:
Es una plataforma web integral que incluye:
- Gestión administrativa y académica
- Biblioteca virtual avanzada
- Sistema de horarios inteligentes
- Gestión de actividades y tareas
- Portales diferenciados para estudiantes, docentes y administradores

ROLES DE USUARIO:
1. ADMINISTRADOR: Gestiona usuarios, cursos, matrículas, reportes, biblioteca, horarios
2. DOCENTE: Gestiona calificaciones, asistencia, actividades, biblioteca
3. ESTUDIANTE: Consulta notas, historial, matrículas, actividades, biblioteca

INSTRUCCIONES IMPORTANTES:
- Sé amable, conversacional y natural en tus respuestas
- Saluda de manera amigable cuando sea apropiado
- Responde preguntas relacionadas con la institución ETC Pedro García Leal y su sistema de gestión
- Si alguien te saluda, responde el saludo de manera natural antes de ofrecer ayuda
- Si la pregunta no está relacionada con la institución, explica amablemente que te especializas en temas de la ETC
- Mantén un tono profesional pero cercano y educativo
- Responde en español de manera natural y fluida
- Sé conciso pero informativo
- Si no sabes algo específico, sugiere contactar al soporte institucional de manera amable
- IMPORTANTE: Varía tus respuestas y evita repetir frases similares. Sé creativo en tus explicaciones.

ESTILO DE COMUNICACIÓN:
- Usa un lenguaje natural y conversacional
- Incluye expresiones amables como '¡Claro!', '¡Por supuesto!', 'Con gusto te ayudo', '¡Perfecto!', '¡Excelente pregunta!'
- Evita respuestas robóticas o demasiado formales
- Adapta tu tono según el contexto de la conversación
- Usa diferentes formas de expresar la misma idea para mantener la conversación fresca

BASE DE CONOCIMIENTO DISPONIBLE:
[El conocimiento institucional se proporcionará en el contexto de cada consulta]

Pregunta del usuario:";

    public static function getSystemPrompt()
    {
        return self::$systemPrompt;
    }

    public static function getFullPrompt($userMessage, $knowledgeContext = '')
    {
        $prompt = self::$systemPrompt;

        if (!empty($knowledgeContext)) {
            $prompt .= "\n\nCONTEXTO ADICIONAL:\n" . $knowledgeContext;
        }

        $prompt .= "\n\n" . $userMessage;

        return $prompt;
    }

    public static function isValidTopic($message)
    {
        // Allow common greetings and conversational starters
        $greetingKeywords = [
            'hola',
            'hello',
            'hi',
            'buenos días',
            'buenas tardes',
            'buenas noches',
            'qué tal',
            'como estas',
            'cómo estás',
            'qué pasa',
            'saludos',
            'gracias',
            'thank you',
            'thanks',
            'bye',
            'adios',
            'hasta luego',
            'ayuda',
            'help',
            'asistencia',
            'soporte'
        ];

        $message = strtolower($message);
        foreach ($greetingKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }

        // Check for ETC-specific keywords
        $validKeywords = [
            'escuela',
            'liceo',
            'etc',
            'pedro garcía leal',
            'técnica comercial',
            'sistema',
            'gestión',
            'académica',
            'administrativa',
            'estudiante',
            'docente',
            'profesor',
            'administrador',
            'biblioteca',
            'virtual',
            'préstamo',
            'libro',
            'horario',
            'aula',
            'clase',
            'curso',
            'actividad',
            'tarea',
            'calificación',
            'nota',
            'matrícula',
            'inscripción',
            'usuario',
            'mención',
            'menciones',
            'especialidad',
            'reporte',
            'estadística',
            'panel',
            'login',
            'contraseña',
            'acceso',
            'instalación',
            'configuración',
            'soporte'
        ];

        foreach ($validKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}
?>