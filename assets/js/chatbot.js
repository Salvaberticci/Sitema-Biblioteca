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
        // Wait for DOM to be fully loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeElements());
        } else {
            this.initializeElements();
        }
    }

    initializeElements() {
        // Get DOM elements
        this.chatbotButton = document.getElementById('chatbot-button');
        this.chatbotWindow = document.getElementById('chatbot-window');
        this.chatbotClose = document.getElementById('chatbot-close');
        this.chatbotInput = document.getElementById('chatbot-input');
        this.chatbotSend = document.getElementById('chatbot-send');
        this.chatbotMessages = document.getElementById('chatbot-messages');
        this.typingIndicator = document.getElementById('typing-indicator');

        // Debug: Check if elements exist
        console.log('Chatbot initialization - elements found:', {
            button: !!this.chatbotButton,
            window: !!this.chatbotWindow,
            close: !!this.chatbotClose,
            input: !!this.chatbotInput,
            send: !!this.chatbotSend,
            messages: !!this.chatbotMessages,
            typing: !!this.typingIndicator
        });

        // If button doesn't exist, try again in a moment (for dynamically loaded content)
        if (!this.chatbotButton) {
            console.log('Chatbot button not found, retrying in 100ms...');
            setTimeout(() => this.initializeElements(), 100);
            return;
        }

        // Bind events
        this.bindEvents();

        // Always start fresh - no message history loading
        // Add initial welcome message
        this.addWelcomeMessage();

        console.log('Chatbot fully initialized');
    }

    bindEvents() {
        // Check if elements exist before binding events
        if (!this.chatbotButton) {
            console.error('Chatbot button not found!');
            return;
        }

        // Toggle chat window
        this.chatbotButton.addEventListener('click', (e) => {
            console.log('Chatbot button clicked');
            e.preventDefault();
            e.stopPropagation();
            this.toggleChat();
        });

        // Close chat window
        if (this.chatbotClose) {
            this.chatbotClose.addEventListener('click', (e) => {
                console.log('Chatbot close clicked');
                e.preventDefault();
                this.closeChat();
            });
        }

        // Send message on button click
        if (this.chatbotSend) {
            this.chatbotSend.addEventListener('click', () => this.sendMessage());
        }

        // Send message on Enter key
        if (this.chatbotInput) {
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
        }

        // Close chat when clicking outside
        document.addEventListener('click', (e) => {
            if (this.chatbotWindow && this.chatbotButton &&
                !this.chatbotWindow.contains(e.target) &&
                !this.chatbotButton.contains(e.target)) {
                this.closeChat();
            }
        });
    }

    toggleChat() {
        console.log('Toggling chat, current state:', this.isOpen, 'Window element:', this.chatbotWindow);
        this.isOpen = !this.isOpen;

        if (this.isOpen) {
            console.log('Opening chat window');
            if (this.chatbotWindow) {
                this.chatbotWindow.classList.remove('hidden');
                this.chatbotWindow.style.display = 'flex';
                this.chatbotWindow.style.position = 'fixed';
                // Responsive positioning
                if (window.innerWidth >= 768) {
                    this.chatbotWindow.style.bottom = '96px'; // 24px * 4 (bottom-24)
                    this.chatbotWindow.style.right = '24px';
                    this.chatbotWindow.style.width = '480px';
                    this.chatbotWindow.style.height = '550px';
                } else {
                    // Mobile: let CSS handle full screen
                    this.chatbotWindow.style.bottom = '0';
                    this.chatbotWindow.style.right = '0';
                    this.chatbotWindow.style.width = '100vw';
                    this.chatbotWindow.style.height = '100vh';
                }
                console.log('Chat window should now be visible');
            } else {
                console.error('Chatbot window element not found!');
            }
            if (this.chatbotButton) {
                this.chatbotButton.classList.add('scale-110');
            }
            if (this.chatbotInput) {
                this.chatbotInput.focus();
            }
            this.scrollToBottom();
            // Hide notification badge when opening
            this.hideNotificationBadge();
        } else {
            console.log('Closing chat window');
            this.closeChat();
        }
    }

    closeChat() {
        console.log('Closing chat');
        this.isOpen = false;
        if (this.chatbotWindow) {
            this.chatbotWindow.classList.add('hidden');
            this.chatbotWindow.style.display = 'none';
        }
        if (this.chatbotButton) {
            this.chatbotButton.classList.remove('scale-110');
        }
        // Show notification badge when closing
        this.showNotificationBadge();
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

            // Show notification badge if chat is closed and it's a bot message
            if (!this.isOpen && sender === 'bot') {
                this.showNotificationBadge();
            }
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

    // Show notification badge
    showNotificationBadge() {
        const notification = document.getElementById('chatbot-notification');
        if (notification) {
            notification.classList.remove('hidden');
        }
    }

    // Hide notification badge
    hideNotificationBadge() {
        const notification = document.getElementById('chatbot-notification');
        if (notification) {
            notification.classList.add('hidden');
        }
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
    console.log('DOM loaded, initializing chatbot...');
    window.chatbotManager = new ChatbotManager();
});

// Also try to initialize on window load as fallback
window.addEventListener('load', function() {
    console.log('Window loaded, checking chatbot...');
    if (!window.chatbotManager || !window.chatbotManager.chatbotButton) {
        console.log('Re-initializing chatbot on window load...');
        window.chatbotManager = new ChatbotManager();
    }
});

// Export for global access
window.ChatbotManager = ChatbotManager;