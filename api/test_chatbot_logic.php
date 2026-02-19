<?php
// Mock ChatbotAPI parts
class MockChatbotAPI
{
    public function getKnowledgeContext($userMessage)
    {
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
            return 'KNOWLEDGE EMPTY';
        }

        $context = '';
        $message = strtolower($userMessage);

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
            if (!$inSection) {
                if (strpos($trimmedLine, '#') === 0 && stripos($trimmedLine, $sectionTitle) !== false) {
                    $inSection = true;
                    preg_match('/^(#+)/', $trimmedLine, $matches);
                    $startLevel = strlen($matches[1]);
                    $section .= $line . "\n";
                    continue;
                }
            } else {
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
}

$api = new MockChatbotAPI();
echo "TEST 1: 'hola'\n";
echo "Context: " . substr($api->getKnowledgeContext('hola'), 0, 100) . "...\n\n";

echo "TEST 2: 'mención'\n";
echo "Context: " . substr($api->getKnowledgeContext('mención'), 0, 100) . "...\n\n";

echo "TEST 3: 'biblioteca'\n";
echo "Context: " . substr($api->getKnowledgeContext('biblioteca'), 0, 100) . "...\n\n";
?>