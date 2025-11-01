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

class ChatbotAPI {
    private $geminiApiKey = 'AIzaSyBAHLANDPjRD16-hKkcI6Tlky-GQWelnWE';
    private $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function handleRequest() {
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
            http_response_code(500);
            return [
                'error' => 'Lo siento, ha ocurrido un error. Por favor, intenta de nuevo más tarde.',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    private function getKnowledgeContext($userMessage) {
        // Load knowledge base
        $knowledgeFile = '../docs/chatbot-knowledge.md';

        if (!file_exists($knowledgeFile)) {
            return '';
        }

        $knowledge = file_get_contents($knowledgeFile);

        // Extract relevant sections based on user message keywords
        $context = '';

        // Check for specific topics and extract relevant sections
        $message = strtolower($userMessage);

        if (strpos($message, 'biblioteca') !== false || strpos($message, 'libro') !== false || strpos($message, 'préstamo') !== false) {
            $context .= $this->extractSection($knowledge, 'Biblioteca Virtual Avanzada');
        }

        if (strpos($message, 'horario') !== false || strpos($message, 'aula') !== false || strpos($message, 'clase') !== false) {
            $context .= $this->extractSection($knowledge, 'Gestión de Horarios Inteligentes');
        }

        if (strpos($message, 'actividad') !== false || strpos($message, 'tarea') !== false) {
            $context .= $this->extractSection($knowledge, 'Sistema de Actividades y Tareas');
        }

        if (strpos($message, 'estudiante') !== false || strpos($message, 'portal') !== false) {
            $context .= $this->extractSection($knowledge, 'Portal del Estudiante');
        }

        if (strpos($message, 'docente') !== false || strpos($message, 'profesor') !== false) {
            $context .= $this->extractSection($knowledge, 'Panel del Docente');
        }

        if (strpos($message, 'admin') !== false || strpos($message, 'administrador') !== false) {
            $context .= $this->extractSection($knowledge, 'Panel de Administración');
        }

        if (strpos($message, 'instalación') !== false || strpos($message, 'configuración') !== false) {
            $context .= $this->extractSection($knowledge, 'Instalación y Configuración');
        }

        if (strpos($message, 'usuario') !== false || strpos($message, 'login') !== false) {
            $context .= $this->extractSection($knowledge, 'Usuarios de Prueba');
        }

        // If no specific context found, provide general institutional info
        if (empty($context)) {
            $context = $this->extractSection($knowledge, 'Información Institucional');
        }

        return $context;
    }

    private function extractSection($content, $sectionTitle) {
        $lines = explode("\n", $content);
        $section = '';
        $inSection = false;

        foreach ($lines as $line) {
            if (strpos($line, $sectionTitle) !== false && strpos($line, '#') === 0) {
                $inSection = true;
                $section .= $line . "\n";
                continue;
            }

            if ($inSection) {
                if (strpos($line, '#') === 0 && !empty(trim($line))) {
                    // Next section starts
                    break;
                }
                $section .= $line . "\n";
            }
        }

        return trim($section);
    }

    private function callGeminiAPI($userMessage, $knowledgeContext) {
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
            CURLOPT_SSL_VERIFYPEER => true
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

    private function cleanResponse($text) {
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