<?php
/**
 * Chatbot API Endpoint for ETC Pedro García Leal
 * Integrates with Gemini API to provide virtual assistant functionality
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Include required files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/chatbot-prompt.php';

class ChatbotAPI
{
    private $geminiApiKey = 'AIzaSyBAHLANDPjRD16-hKkcI6Tlky-GQWelnWE';
    private $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    private function isLibraryQuery($message)
    {
        $message = strtolower($message);

        // More specific patterns that indicate the user wants to see what's available
        $libraryQueryPatterns = [
            // Direct questions about what's available
            'qué hay en la biblioteca',
            'que hay en la biblioteca',
            'qué hay en biblioteca',
            'que hay en biblioteca',
            'qué recursos hay',
            'que recursos hay',
            'qué libros hay',
            'que libros hay',
            'qué materiales hay',
            'que materiales hay',
            'qué documentos hay',
            'que documentos hay',

            // Requests to show/list resources
            'muéstrame los recursos',
            'mostrarme los recursos',
            'lista de recursos',
            'ver recursos disponibles',
            'ver qué hay',
            'ver biblioteca',
            'mostrar biblioteca',

            // Questions about availability
            'qué está disponible',
            'que esta disponible',
            'qué puedo encontrar',
            'que puedo encontrar',
            'qué tienen disponible',
            'que tienen disponible',

            // Catalog requests
            'catálogo de biblioteca',
            'catalogo de biblioteca',
            'inventario biblioteca',
            'contenido biblioteca'
        ];

        foreach ($libraryQueryPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private function isSpecificResourceQuery($message)
    {
        $message = strtolower($message);

        // Patterns that indicate searching for specific resources
        $specificPatterns = [
            'tienen el libro',
            'tienen el documento',
            'tienen el material',
            'tienen el recurso',
            'hay algún libro',
            'hay algún documento',
            'hay algún material',
            'hay algún recurso',
            'busco el libro',
            'busco el documento',
            'busco el material',
            'busco el recurso',
            'está disponible el libro',
            'esta disponible el libro',
            'está disponible el documento',
            'esta disponible el documento',
            'tienen disponible',
            'hay disponible',
            'puedo encontrar',
            'dónde está el libro',
            'donde esta el libro'
        ];

        foreach ($specificPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }

        // Also check for questions that might contain book titles or subjects
        $questionWords = ['tienen', 'hay', 'está', 'esta', 'busco', 'necesito', 'quiero'];
        $libraryWords = ['libro', 'documento', 'material', 'recurso', 'pdf', 'texto'];

        foreach ($questionWords as $qWord) {
            foreach ($libraryWords as $lWord) {
                if (strpos($message, $qWord) !== false && strpos($message, $lWord) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function extractSearchTerm($message)
    {
        // Try to extract the search term from the message
        $message = strtolower($message);

        // Remove common question prefixes
        $prefixes = [
            'tienen el libro',
            'tienen el documento',
            'tienen el material',
            'tienen el recurso',
            'hay algún libro',
            'hay algún documento',
            'hay algún material',
            'hay algún recurso',
            'busco el libro',
            'busco el documento',
            'busco el material',
            'busco el recurso',
            'está disponible el libro',
            'esta disponible el libro',
            'está disponible el documento',
            'esta disponible el documento',
            'tienen disponible',
            'hay disponible',
            'puedo encontrar',
            'dónde está el libro',
            'donde esta el libro',
            'necesito el libro',
            'necesito el documento',
            'necesito el material',
            'quiero el libro',
            'quiero el documento',
            'quiero el material'
        ];

        foreach ($prefixes as $prefix) {
            if (strpos($message, $prefix) !== false) {
                $searchTerm = str_replace($prefix, '', $message);
                $searchTerm = trim($searchTerm, ' ?¿¡!.,');
                if (!empty($searchTerm)) {
                    return $searchTerm;
                }
            }
        }

        // If no specific prefix found, try to extract after library-related words
        $libraryWords = ['libro de', 'libro sobre', 'documento de', 'material de', 'recurso de'];
        foreach ($libraryWords as $word) {
            if (strpos($message, $word) !== false) {
                $searchTerm = str_replace($word, '', $message);
                $searchTerm = trim($searchTerm, ' ?¿¡!.,');
                if (!empty($searchTerm)) {
                    return $searchTerm;
                }
            }
        }

        // Last resort: return the whole message cleaned up
        return trim($message, ' ?¿¡!.,');
    }

    private function handleSpecificResourceQuery($message)
    {
        $searchTerm = $this->extractSearchTerm($message);

        if (empty($searchTerm)) {
            return [
                'response' => 'No pude identificar qué recurso estás buscando. ¿Puedes darme más detalles sobre el libro, documento o material que necesitas?',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        try {
            // Search for the specific resource
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/biblioteca/api/library.php?search=' . urlencode($searchTerm));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                return [
                    'response' => 'Lo siento, no pude buscar en la biblioteca en este momento. Por favor, intenta más tarde.',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            $data = json_decode($response, true);

            if (!$data || !isset($data['resources'])) {
                return [
                    'response' => "No encontré recursos que coincidan con '$searchTerm'. ¿Puedes verificar el título o darme más detalles?",
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            $resources = $data['resources'];

            if (empty($resources)) {
                return [
                    'response' => "Lo siento, no encontré ningún recurso que coincida con '$searchTerm' en nuestra biblioteca virtual. ¿Quizás esté bajo un título diferente o puedes darme más detalles para ayudarte mejor?",
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            // If we found resources, format them nicely
            if (count($resources) === 1) {
                // Single result
                $resource = $resources[0];
                $typeIcon = $this->getResourceTypeIcon($resource['type']);

                $response = "¡Sí! Encontré este recurso que podría ser lo que buscas:\n\n";
                $response .= "{$typeIcon} **{$resource['title']}**\n";
                if ($resource['author']) {
                    $response .= "👤 Autor: {$resource['author']}\n";
                }
                if ($resource['subject']) {
                    $response .= "📖 Asignatura: {$resource['subject']}\n";
                }
                $response .= "📅 Subido: " . date('d/m/Y', strtotime($resource['upload_date'])) . "\n";
                $response .= "🔗 Tipo: " . ucfirst($resource['type']) . "\n\n";

                $response .= "💡 Para descargarlo, ve a la Biblioteca Virtual y busca por el título.\n\n";
                $response .= "¿Es este el recurso que estabas buscando?";
            } else {
                // Multiple results
                $response = "Encontré varios recursos que podrían coincidir con '$searchTerm':\n\n";

                foreach (array_slice($resources, 0, 3) as $resource) { // Show max 3 results
                    $typeIcon = $this->getResourceTypeIcon($resource['type']);
                    $response .= "{$typeIcon} **{$resource['title']}**\n";
                    if ($resource['author']) {
                        $response .= "   👤 {$resource['author']}\n";
                    }
                    $response .= "   📖 " . ucfirst($resource['type']) . "\n\n";
                }

                if (count($resources) > 3) {
                    $response .= "...y " . (count($resources) - 3) . " recursos más.\n\n";
                }

                $response .= "💡 Te recomiendo ir a la Biblioteca Virtual y usar la búsqueda avanzada para encontrar exactamente lo que necesitas.\n\n";
                $response .= "¿Alguno de estos es el que buscas?";
            }

            return [
                'response' => $response,
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            error_log('Specific resource query error: ' . $e->getMessage());
            return [
                'response' => 'Lo siento, hubo un problema al buscar en la biblioteca. Por favor, intenta acceder directamente desde el menú principal.',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    private function handleLibraryQuery($message)
    {
        try {
            // Fetch library resources
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/biblioteca/api/library.php?list');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                return [
                    'response' => 'Lo siento, no pude acceder al catálogo de la biblioteca en este momento. Por favor, intenta más tarde.',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            $data = json_decode($response, true);

            if (!$data || !isset($data['resources']) || empty($data['resources'])) {
                return [
                    'response' => 'Actualmente no hay recursos disponibles en la biblioteca virtual. Los docentes están trabajando para agregar más materiales educativos.',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            $resources = $data['resources'];

            if (empty($resources)) {
                $response = "Actualmente no tenemos recursos disponibles en la biblioteca virtual, pero nuestros docentes están trabajando para agregar más materiales educativos. Te recomiendo revisar periódicamente o contactar a tu profesor para materiales específicos de tu asignatura.";
            } else {
                $response = "¡Claro! Aquí tienes los recursos que están disponibles en nuestra Biblioteca Virtual:\n\n";

                foreach ($resources as $resource) {
                    $typeIcon = $this->getResourceTypeIcon($resource['type']);
                    $response .= "{$typeIcon} **{$resource['title']}**\n";
                    if ($resource['author']) {
                        $response .= "👤 Autor: {$resource['author']}\n";
                    }
                    if ($resource['subject']) {
                        $response .= "📖 Asignatura: {$resource['subject']}\n";
                    }
                    $response .= "📅 Subido: " . date('d/m/Y', strtotime($resource['upload_date'])) . "\n";
                    $response .= "🔗 Tipo: " . ucfirst($resource['type']) . "\n\n";
                }

                $response .= "💡 **¿Cómo acceder?**\n";
                $response .= "Solo ve a la sección 'Biblioteca Virtual' en el menú principal, busca los recursos que te interesen y haz clic en 'Descargar'.\n\n";
                $response .= "¿Hay algún recurso específico que te gustaría encontrar o necesitas ayuda con algo en particular?";
            }

            return [
                'response' => $response,
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            error_log('Library query error: ' . $e->getMessage());
            return [
                'response' => 'Lo siento, hubo un problema al consultar la biblioteca. Por favor, intenta acceder directamente desde el menú principal.',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    private function getResourceTypeIcon($type)
    {
        switch ($type) {
            case 'book':
                return '📖';
            case 'article':
                return '📰';
            case 'video':
                return '🎥';
            case 'document':
                return '📄';
            default:
                return '📋';
        }
    }

    public function handleRequest()
    {
        try {
            // Get and validate input
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || !isset($input['message'])) {
                throw new Exception('Invalid request: message is required');
            }

            $userMessage = trim($input['message']);

            if (empty($userMessage)) {
                throw new Exception('Message cannot be empty');
            }

            if (strlen($userMessage) > 1000) {
                throw new Exception('Message too long (max 1000 characters)');
            }

            // Check for specific resource queries first
            if ($this->isSpecificResourceQuery($userMessage)) {
                return $this->handleSpecificResourceQuery($userMessage);
            }

            // Check for general library queries (what's available)
            if ($this->isLibraryQuery($userMessage)) {
                return $this->handleLibraryQuery($userMessage);
            }

            // Validate if message is related to ETC topics
            if (!ChatbotPrompt::isValidTopic($userMessage)) {
                return [
                    'response' => '¡Hola! 😊 Soy el asistente virtual de la ETC Pedro García Leal. Me especializo en ayudarte con información sobre nuestra institución y el sistema de gestión académica. ¿Hay algo específico sobre la ETC o el sistema en lo que pueda ayudarte?',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            // Get knowledge context
            $knowledgeContext = $this->getKnowledgeContext($userMessage);

            // Generate response using Gemini API
            $response = $this->callGeminiAPI($userMessage, $knowledgeContext);

            return [
                'response' => $response,
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            error_log('Chatbot API Error: ' . $e->getMessage());

            $errorMessage = $e->getMessage();

            // Handle Quota Exceeded (HTTP 429)
            if (strpos($errorMessage, 'HTTP 429') !== false) {
                http_response_code(429);
                return [
                    'error' => "La cuota gratuita de la API se ha agotado. Por favor, intenta de nuevo en unas horas o mañana.",
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            // Handle Forbidden/Leaked Key (HTTP 403)
            if (strpos($errorMessage, 'HTTP 403') !== false) {
                http_response_code(403);
                $isLeaked = strpos($errorMessage, 'reported as leaked') !== false;
                return [
                    'error' => $isLeaked
                        ? "La clave de API de Gemini ha sido reportada como filtrada (leaked) por Google y ha sido desactivada por seguridad. Es necesario configurar una nueva clave de API."
                        : "Acceso denegado a la API de Gemini. La configuración de la clave puede ser incorrecta o estar desactivada.",
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            // Handle Unauthorized (HTTP 401)
            if (strpos($errorMessage, 'HTTP 401') !== false) {
                http_response_code(401);
                return [
                    'error' => "La clave de API de Gemini es inválida o no tiene permisos. Por favor, verifica la configuración.",
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            // Default fallback for other errors
            http_response_code(500);
            return [
                'error' => 'Lo siento, el servicio de inteligencia artificial no está disponible en este momento por un error técnico. Por favor, intenta de nuevo más tarde.',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    private function getKnowledgeContext($userMessage)
    {
        // Source files
        $chatbotKnowledgeFile = '../docs/chatbot-knowledge.md';
        $generalKnowledgeFile = '../docs/KNOWLEDGE_BASE.md';

        $knowledge = '';
        if (file_exists($chatbotKnowledgeFile)) {
            $knowledge .= file_get_contents($chatbotKnowledgeFile) . "\n\n";
        }
        if (file_exists($generalKnowledgeFile)) {
            $knowledge .= file_get_contents($generalKnowledgeFile);
        }

        if (empty($knowledge)) {
            return '';
        }

        // Extract relevant sections based on user message keywords
        $context = '';
        $message = strtolower($userMessage);

        // Map keywords to section titles (searching in both files)
        $topicMap = [
            'biblioteca|libro|préstamo' => 'Biblioteca',
            'horario|aula|clase' => 'Horario',
            'actividad|tarea' => 'Actividades',
            'estudiante|portal' => 'Estudiante',
            'docente|profesor' => 'Docente',
            'admin|administrador' => 'Admin|Administración',
            'instalación|configuración' => 'Instalación',
            'usuario|login|contraseña' => 'Usuarios',
            'mención|especialidad|matrícula' => 'Mención|Matrícula'
        ];

        foreach ($topicMap as $keywords => $sectionSearch) {
            $pattern = '/(' . $keywords . ')/i';
            if (preg_match($pattern, $message)) {
                $sectionTitles = explode('|', $sectionSearch);
                foreach ($sectionTitles as $title) {
                    $context .= $this->extractSection($knowledge, $title);
                }
            }
        }

        // If no specific context found, provide general info
        if (empty($context)) {
            $context = $this->extractSection($knowledge, 'Información Institucional');
            if (empty($context)) {
                $context = $this->extractSection($knowledge, 'Preguntas Generales');
            }
        }

        return $context;
    }

    private function extractSection($content, $sectionTitle)
    {
        $lines = explode("\n", $content);
        $section = '';
        $inSection = false;
        $startLevel = 0;

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            // Detect section start
            if (!$inSection) {
                if (strpos($trimmedLine, '#') === 0 && stripos($trimmedLine, $sectionTitle) !== false) {
                    $inSection = true;
                    // Count starting #
                    preg_match('/^(#+)/', $trimmedLine, $matches);
                    $startLevel = strlen($matches[1]);
                    $section .= $line . "\n";
                    continue;
                }
            } else {
                // Section end detection: next header of same or higher level (fewer or equal #)
                if (strpos($trimmedLine, '#') === 0) {
                    preg_match('/^(#+)/', $trimmedLine, $matches);
                    $currentLevel = strlen($matches[1]);
                    if ($currentLevel <= $startLevel) {
                        break;
                    }
                }
                $section .= $line . "\n";
            }
        }

        return trim($section);
    }

    private function callGeminiAPI($userMessage, $knowledgeContext)
    {
        // Prepare the prompt
        $fullPrompt = ChatbotPrompt::getFullPrompt($userMessage, $knowledgeContext);

        // Note: Since we reset messages on page reload, we don't need conversation context
        // The generation config parameters will help create more varied responses

        // Prepare API request data with generation config to encourage variety
        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $fullPrompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
                'stopSequences' => []
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];

        // Initialize cURL
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->geminiUrl . '?key=' . $this->geminiApiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new Exception('API request failed: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new Exception('API returned HTTP ' . $httpCode . ': ' . $response);
        }

        // Parse response
        $responseData = json_decode($response, true);

        if (!$responseData || !isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Invalid API response format');
        }

        $generatedText = $responseData['candidates'][0]['content']['parts'][0]['text'];

        // Clean and format response
        return $this->cleanResponse($generatedText);
    }

    private function cleanResponse($text)
    {
        // Remove any unwanted formatting or prefixes
        $text = trim($text);

        // Ensure response is in Spanish and relevant
        if (empty($text)) {
            return 'Lo siento, no pude generar una respuesta adecuada. ¿Puedes reformular tu pregunta?';
        }

        // Limit response length
        if (strlen($text) > 2000) {
            $text = substr($text, 0, 2000) . '...';
        }

        return $text;
    }
}

// Handle the request
$chatbot = new ChatbotAPI();
$result = $chatbot->handleRequest();

echo json_encode($result);
?>