<!-- Chatbot Component for ETC Pedro García Leal -->
<div id="chatbot-container" class="fixed bottom-6 right-6 z-50" style="z-index: 9999;">
<!-- Debug: Chatbot HTML loaded -->
<script>console.log('Chatbot HTML template loaded on page:', window.location.pathname);</script>
    <!-- Chat Button -->
    <div id="chatbot-button" class="w-20 h-20 bg-gradient-to-r from-primary to-secondary rounded-full shadow-2xl cursor-pointer flex items-center justify-center text-white hover:shadow-xl transition-all duration-300 animate-bounce-in border-4 border-white pulse-glow relative" style="position: fixed; bottom: 24px; right: 24px; z-index: 10000;" onclick="console.log('Chatbot button clicked directly');">
        <i class="fas fa-robot text-4xl"></i>
        <!-- Notification badge -->
        <div id="chatbot-notification" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full border-2 border-white animate-ping hidden"></div>
    </div>

    <!-- Chat Window -->
    <div id="chatbot-window" class="hidden absolute bottom-24 right-0 w-[480px] h-[550px] bg-gradient-to-br from-white via-blue-50 to-indigo-50 rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden chatbot-slide-in glow-effect" style="z-index: 10001; position: fixed; background-image: radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.1) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.1) 0%, transparent 50%);">
        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-primary to-secondary text-white p-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center overflow-hidden">
                        <img src="/biblioteca/logo.png" alt="Logo" class="h-full w-full object-cover">
                    </div>
                    <div>
                        <h3 class="font-bold text-sm">Asistente Virtual</h3>
                        <p class="text-xs opacity-90">ETC Pedro García Leal</p>
                    </div>
                </div>
                <button id="chatbot-close" class="text-white hover:text-accent transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="chatbot-messages" class="flex-1 p-4 overflow-y-auto bg-gray-50">
            <div class="space-y-3" id="chatbot-messages-container">
                <!-- Messages will be added here dynamically -->
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="typing-indicator" class="hidden px-4 py-2">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-gradient-to-r from-primary to-secondary rounded-full flex items-center justify-center text-white text-xs">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="bg-white rounded-2xl rounded-tl-md px-4 py-2 shadow-sm">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-primary rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="p-4 bg-white border-t border-gray-200">
            <div class="flex space-x-2">
                <input
                    type="text"
                    id="chatbot-input"
                    placeholder="Escribe tu pregunta..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                    maxlength="500"
                >
                <button
                    id="chatbot-send"
                    class="w-10 h-10 bg-gradient-to-r from-primary to-secondary text-white rounded-full hover:shadow-lg transition-all duration-300 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled
                >
                    <i class="fas fa-paper-plane text-sm"></i>
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-2 text-center">
                Solo respondo preguntas sobre la ETC y su sistema
            </p>
        </div>
    </div>
</div>

<style>
/* Custom animations for chatbot */
@keyframes bounce-in {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.05); opacity: 1; }
    70% { transform: scale(0.9); }
    100% { transform: scale(1); opacity: 1; }
}

.animate-bounce-in {
    animation: bounce-in 0.6s ease-out;
}

/* Slide-in animation for chat window */
@keyframes chatbot-slide-in {
    from { opacity: 0; transform: translateX(100%) scale(0.9); }
    to { opacity: 1; transform: translateX(0) scale(1); }
}

.chatbot-slide-in {
    animation: chatbot-slide-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Glow effect for window */
.glow-effect {
    box-shadow: 0 0 40px rgba(212, 175, 55, 0.3), 0 0 80px rgba(212, 175, 55, 0.1);
    transition: box-shadow 0.3s ease;
}

.glow-effect:hover {
    box-shadow: 0 0 60px rgba(212, 175, 55, 0.4), 0 0 120px rgba(212, 175, 55, 0.2);
}

/* Enhanced pulse glow for button */
@keyframes enhanced-pulse {
    0%, 100% {
        box-shadow: 0 0 20px rgba(212, 175, 55, 0.4), 0 0 40px rgba(212, 175, 55, 0.2);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 30px rgba(212, 175, 55, 0.6), 0 0 60px rgba(212, 175, 55, 0.3);
        transform: scale(1.05);
    }
}

.pulse-glow {
    animation: enhanced-pulse 2s infinite;
}

/* Smooth transitions for chat messages with bounce */
#chatbot-messages .space-y-3 > div {
    animation: bounce-in-message 0.4s ease-out;
}

@keyframes bounce-in-message {
    0% { opacity: 0; transform: translateY(20px) scale(0.8); }
    50% { opacity: 1; transform: translateY(-5px) scale(1.05); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}

/* Enhanced message bubbles - WhatsApp/Messenger style with more effects */
#chatbot-messages .flex {
    margin-bottom: 16px;
    transition: all 0.2s ease;
}

#chatbot-messages .flex:hover {
    transform: translateY(-1px);
}

#chatbot-messages .flex .bg-white {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border: 1px solid #90caf9;
    color: #1565c0;
    max-width: 320px;
    word-wrap: break-word;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

#chatbot-messages .flex .bg-white:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

#chatbot-messages .flex .bg-gradient-to-r {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 50%, #004085 100%);
    max-width: 320px;
    word-wrap: break-word;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

#chatbot-messages .flex .bg-gradient-to-r:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

/* Typing indicator enhancement with animation */
#typing-indicator .bg-white {
    background: linear-gradient(135deg, #f5f5f5 0%, #e8eaf6 100%);
    border: 1px solid #e0e0e0;
    animation: typing-pulse 1.5s infinite;
}

@keyframes typing-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

/* Custom scrollbar for chat messages with enhanced styling */
#chatbot-messages::-webkit-scrollbar {
    width: 8px;
}

#chatbot-messages::-webkit-scrollbar-track {
    background: linear-gradient(180deg, #f1f1f1 0%, #e8eaf6 100%);
    border-radius: 4px;
}

#chatbot-messages::-webkit-scrollbar-thumb {
    background: linear-gradient(45deg, #D4AF37, #8B4513, #D4AF37);
    border-radius: 4px;
    box-shadow: inset 0 0 5px rgba(0,0,0,0.2);
}

#chatbot-messages::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(45deg, #B8860B, #654321, #B8860B);
    box-shadow: inset 0 0 8px rgba(0,0,0,0.3);
}

/* Notification badge animation */
@keyframes notification-bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-3px); }
    60% { transform: translateY(-2px); }
}

#chatbot-notification {
    animation: notification-bounce 2s infinite;
}

/* Responsive design for mobile */
@media (max-width: 768px) {
    #chatbot-container {
        bottom: 0 !important;
        right: 0 !important;
    }

    #chatbot-button {
        bottom: 10px !important;
        right: 10px !important;
        width: 60px !important;
        height: 60px !important;
    }

    #chatbot-window {
        width: 100vw !important;
        height: 100vh !important;
        bottom: 0 !important;
        right: 0 !important;
        border-radius: 0 !important;
        max-width: none !important;
    }

    #chatbot-messages .flex .bg-white,
    #chatbot-messages .flex .bg-gradient-to-r {
        max-width: 280px;
    }

    .glow-effect {
        box-shadow: none !important;
    }
}
</style>
