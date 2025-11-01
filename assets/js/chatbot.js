/**
 * Chatbot JavaScript for ETC Pedro García Leal
 * Handles chat interface interactions and API communication
 */

class ChatbotManager {
    constructor() {
        this.apiUrl = '/biblioteca/api/chatbot.php';
        this.isOpen = false;
        this.messageHistory = [];
        this.init();
    }

    init() {
        // Get DOM elements
        this.chatbotButton = document.getElementById('chatbot-button');
        this.chatbotWindow = document.getElementById('chatbot-window');
        this.chatbotClose = document.getElementById('chatbot-close');
        this.chatbotInput = document.getElementById('chatbot-input');
        this.chatbotSend = document.getElementById('chatbot-send');
        this.chatbotMessages = document.getElementById('chatbot-messages');
        this.typingIndicator = document.getElementById('typing-indicator');

        // Bind events
        this.bindEvents();

        // Always start fresh - no message history loading
        // Add initial welcome message
        this.addWelcomeMessage();
    }

    bindEvents() {
        // Toggle chat window
        this.chatbotButton.addEventListener('click', () => this.toggleChat());

        // Close chat window
        this.chatbotClose.addEventListener('click', () => this.closeChat());

        // Send message on button click
        this.chatbotSend.addEventListener('click', () => this.sendMessage());

        // Send message on Enter key
        this.chatbotInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Enable/disable send button based on input
        this.chatbotInput.addEventListener('input', () => {
            this.toggleSendButton();
        });

        // Close chat when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.chatbotWindow.contains(e.target) && !this.chatbotButton.contains(e.target)) {
                this.closeChat();
            }
        });
    }

    toggleChat() {
        this.isOpen = !this.isOpen;

        if (this.isOpen) {
            this.chatbotWindow.classList.remove('hidden');
            this.chatbotButton.classList.add('scale-110');
            this.chatbotInput.focus();
            this.scrollToBottom();
        } else {
            this.closeChat();
        }
    }

    closeChat() {
        this.isOpen = false;
        this.chatbotWindow.classList.add('hidden');
        this.chatbotButton.classList.remove('scale-110');
    }

    toggleSendButton() {
        const message = this.chatbotInput.value.trim();
        this.chatbotSend.disabled = message.length === 0;
    }

    async sendMessage() {
        const message = this.chatbotInput.value.trim();

        if (!message) return;

        // Add user message to chat
        this.addMessage(message, 'user');

        // Clear input
        this.chatbotInput.value = '';
        this.toggleSendButton();

        // Show typing indicator
        this.showTypingIndicator();

        try {
            // Send to API
            const response = await this.callAPI(message);

            // Hide typing indicator
            this.hideTypingIndicator();

            // Add bot response
            this.addMessage(response.response || response.error, 'bot');

        } catch (error) {
            console.error('Chatbot error:', error);
            this.hideTypingIndicator();
            this.addMessage('Lo siento, ha ocurrido un error. Por favor, intenta de nuevo.', 'bot');
        }
    }

    async callAPI(message) {
        const response = await fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                timestamp: new Date().toISOString()
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    }

    addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start space-x-2 mb-3';

        if (sender === 'user') {
            // User message (right aligned)
            messageDiv.className = 'flex items-start space-x-2 mb-3 justify-end';
            messageDiv.innerHTML = `
                <div class="bg-gradient-to-r from-primary to-secondary text-white rounded-2xl rounded-tr-md px-4 py-2 shadow-sm max-w-xs">
                    <p class="text-sm">${this.escapeHtml(text)}</p>
                </div>
                <div class="w-8 h-8 bg-gradient-to-r from-primary to-secondary rounded-full flex items-center justify-center text-white text-xs">
                    <i class="fas fa-user"></i>
                </div>
            `;
        } else {
            // Bot message (left aligned)
            messageDiv.innerHTML = `
                <div class="w-8 h-8 bg-gradient-to-r from-primary to-secondary rounded-full flex items-center justify-center text-white text-xs">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="bg-white rounded-2xl rounded-tl-md px-4 py-2 shadow-sm max-w-xs">
                    <p class="text-sm text-gray-800">${this.escapeHtml(text)}</p>
                </div>
            `;
        }

        this.chatbotMessages.appendChild(messageDiv);
        this.scrollToBottom();

        // Save to history
        this.saveMessage(text, sender);
    }

    showTypingIndicator() {
        this.typingIndicator.classList.remove('hidden');
        this.scrollToBottom();
    }

    hideTypingIndicator() {
        this.typingIndicator.classList.add('hidden');
    }

    scrollToBottom() {
        setTimeout(() => {
            this.chatbotMessages.scrollTop = this.chatbotMessages.scrollHeight;
        }, 100);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    saveMessage(text, sender) {
        // Keep messages in memory only for current session
        this.messageHistory.push({
            text: text,
            sender: sender,
            timestamp: new Date().toISOString()
        });

        // Keep only last 50 messages in memory
        if (this.messageHistory.length > 50) {
            this.messageHistory = this.messageHistory.slice(-50);
        }

        // No longer save to session storage - messages reset on page reload
    }

    // Removed loadMessageHistory method - no longer needed

    // Public method to programmatically open chat
    openChat() {
        if (!this.isOpen) {
            this.toggleChat();
        }
    }

    // Add initial welcome message
    addWelcomeMessage() {
        const welcomeMessage = '¡Hola! Soy el asistente virtual de la ETC Pedro García Leal. ¿En qué puedo ayudarte hoy?';
        this.addMessage(welcomeMessage, 'bot');
        // Don't save welcome message to avoid persistence
    }

    // Public method to send a welcome message
    sendWelcomeMessage() {
        setTimeout(() => {
            this.addMessage('¡Hola! Soy el asistente virtual de la ETC Pedro García Leal. ¿En qué puedo ayudarte hoy?', 'bot');
        }, 1000);
    }
}

// Initialize chatbot when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.chatbotManager = new ChatbotManager();
});

// Export for global access
window.ChatbotManager = ChatbotManager;