<!-- Chatbot Component for ETC Pedro García Leal -->
<div id="chatbot-container" class="fixed bottom-6 right-6 z-50" style="z-index: 9999;">
    <!-- Chat Button -->
    <div id="chatbot-button" class="w-16 h-16 bg-gradient-to-r from-primary to-secondary rounded-full shadow-2xl cursor-pointer flex items-center justify-center text-white hover:shadow-xl transition-all duration-300 animate-bounce-in border-4 border-white">
        <i class="fas fa-robot text-4xl"></i>
    </div>

    <!-- Chat Window -->
    <div id="chatbot-window" class="hidden absolute bottom-24 right-0 w-80 h-96 bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden" style="z-index: 10000;">
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

/* Smooth transitions for chat messages */
#chatbot-messages .space-y-3 > div {
    animation: slide-in-message 0.3s ease-out;
}

@keyframes slide-in-message {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Custom scrollbar for chat messages */
#chatbot-messages::-webkit-scrollbar {
    width: 4px;
}

#chatbot-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

#chatbot-messages::-webkit-scrollbar-thumb {
    background: linear-gradient(45deg, #D4AF37, #8B4513);
    border-radius: 2px;
}

#chatbot-messages::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(45deg, #B8860B, #654321);
}
</style>
