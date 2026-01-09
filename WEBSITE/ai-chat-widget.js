/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - AI Chat Widget
 * ═══════════════════════════════════════════════════════════════════════════════
 * 
 * Universal AI chatbot widget that can be embedded on any page
 */

class LegendAIChat {
    constructor(options = {}) {
        this.conversationId = null;
        this.context = options.context || 'general';
        this.isOpen = false;
        this.isMinimized = false;
        this.messages = [];
        this.isLoading = false;
        
        this.init();
    }
    
    init() {
        // Check if AI is available
        this.checkAvailability().then(available => {
            if (available) {
                this.createWidget();
                this.attachEventListeners();
            }
        });
    }
    
    async checkAvailability() {
        try {
            const response = await fetch('ai-chat.php?action=available');
            const data = await response.json();
            return data.success && data.available;
        } catch (error) {
            console.error('AI availability check failed:', error);
            return false;
        }
    }
    
    createWidget() {
        const widgetHTML = `
            <div id="legend-ai-widget" class="legend-ai-widget">
                <!-- Chat Button -->
                <button id="ai-chat-button" class="ai-chat-button" title="AI Assistant">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span class="ai-badge">AI</span>
                </button>
                
                <!-- Chat Window -->
                <div id="ai-chat-window" class="ai-chat-window" style="display: none;">
                    <div class="ai-chat-header">
                        <div class="ai-header-content">
                            <div class="ai-avatar">🤖</div>
                            <div>
                                <div class="ai-title">Legend AI Assistant</div>
                                <div class="ai-status">Online • Powered by Blackbox</div>
                            </div>
                        </div>
                        <div class="ai-header-actions">
                            <button id="ai-minimize" class="ai-icon-btn" title="Minimize">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                            <button id="ai-close" class="ai-icon-btn" title="Close">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="ai-chat-messages" id="ai-chat-messages">
                        <div class="ai-welcome-message">
                            <div class="ai-message ai-message-assistant">
                                <div class="ai-message-content">
                                    <p>👋 Hello! I'm your AI assistant for Legend House.</p>
                                    <p>I can help you with:</p>
                                    <ul>
                                        <li>🔍 Google dorking and search techniques</li>
                                        <li>📥 Finding and managing torrents</li>
                                        <li>🛠️ Using platform features</li>
                                        <li>💡 General tech support</li>
                                    </ul>
                                    <p>How can I assist you today?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ai-chat-input-container">
                        <div class="ai-input-wrapper">
                            <textarea 
                                id="ai-chat-input" 
                                class="ai-chat-input" 
                                placeholder="Ask me anything..." 
                                rows="1"
                            ></textarea>
                            <button id="ai-send-button" class="ai-send-button" title="Send message">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                        <div class="ai-context-selector">
                            <select id="ai-context" class="ai-context-select">
                                <option value="general">General Help</option>
                                <option value="dorking">Google Dorking</option>
                                <option value="torrents">Torrents</option>
                                <option value="search">Search Tips</option>
                                <option value="technical">Technical Support</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', widgetHTML);
        this.injectStyles();
    }
    
    injectStyles() {
        const styles = `
            .legend-ai-widget {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 9999;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }
            
            .ai-chat-button {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                color: white;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                transition: all 0.3s ease;
            }
            
            .ai-chat-button:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
            }
            
            .ai-badge {
                position: absolute;
                top: -5px;
                right: -5px;
                background: #ef4444;
                color: white;
                font-size: 10px;
                font-weight: bold;
                padding: 2px 6px;
                border-radius: 10px;
            }
            
            .ai-chat-window {
                position: absolute;
                bottom: 80px;
                right: 0;
                width: 400px;
                max-width: calc(100vw - 40px);
                height: 600px;
                max-height: calc(100vh - 120px);
                background: white;
                border-radius: 16px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }
            
            .ai-chat-window.minimized {
                height: 60px;
            }
            
            .ai-chat-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 16px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .ai-header-content {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            
            .ai-avatar {
                width: 36px;
                height: 36px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
            }
            
            .ai-title {
                font-weight: 600;
                font-size: 16px;
            }
            
            .ai-status {
                font-size: 12px;
                opacity: 0.9;
            }
            
            .ai-header-actions {
                display: flex;
                gap: 8px;
            }
            
            .ai-icon-btn {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                width: 32px;
                height: 32px;
                border-radius: 8px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s;
            }
            
            .ai-icon-btn:hover {
                background: rgba(255, 255, 255, 0.3);
            }
            
            .ai-chat-messages {
                flex: 1;
                overflow-y: auto;
                padding: 16px;
                background: #f9fafb;
            }
            
            .ai-message {
                margin-bottom: 16px;
                animation: slideIn 0.3s ease;
            }
            
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .ai-message-user {
                display: flex;
                justify-content: flex-end;
            }
            
            .ai-message-user .ai-message-content {
                background: #667eea;
                color: white;
                border-radius: 18px 18px 4px 18px;
                padding: 12px 16px;
                max-width: 80%;
            }
            
            .ai-message-assistant .ai-message-content {
                background: white;
                border-radius: 18px 18px 18px 4px;
                padding: 12px 16px;
                max-width: 80%;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }
            
            .ai-message-content p {
                margin: 0 0 8px 0;
            }
            
            .ai-message-content p:last-child {
                margin-bottom: 0;
            }
            
            .ai-message-content ul {
                margin: 8px 0;
                padding-left: 20px;
            }
            
            .ai-message-content li {
                margin: 4px 0;
            }
            
            .ai-typing-indicator {
                display: flex;
                gap: 4px;
                padding: 12px 16px;
            }
            
            .ai-typing-dot {
                width: 8px;
                height: 8px;
                background: #667eea;
                border-radius: 50%;
                animation: typing 1.4s infinite;
            }
            
            .ai-typing-dot:nth-child(2) {
                animation-delay: 0.2s;
            }
            
            .ai-typing-dot:nth-child(3) {
                animation-delay: 0.4s;
            }
            
            @keyframes typing {
                0%, 60%, 100% {
                    transform: translateY(0);
                }
                30% {
                    transform: translateY(-10px);
                }
            }
            
            .ai-chat-input-container {
                border-top: 1px solid #e5e7eb;
                background: white;
                padding: 12px;
            }
            
            .ai-input-wrapper {
                display: flex;
                gap: 8px;
                align-items: flex-end;
            }
            
            .ai-chat-input {
                flex: 1;
                border: 2px solid #e5e7eb;
                border-radius: 12px;
                padding: 12px;
                font-size: 14px;
                resize: none;
                max-height: 120px;
                font-family: inherit;
            }
            
            .ai-chat-input:focus {
                outline: none;
                border-color: #667eea;
            }
            
            .ai-send-button {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                background: #667eea;
                border: none;
                color: white;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s;
            }
            
            .ai-send-button:hover {
                background: #5568d3;
            }
            
            .ai-send-button:disabled {
                background: #9ca3af;
                cursor: not-allowed;
            }
            
            .ai-context-selector {
                margin-top: 8px;
            }
            
            .ai-context-select {
                width: 100%;
                padding: 8px 12px;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                font-size: 12px;
                background: white;
                cursor: pointer;
            }
            
            @media (max-width: 480px) {
                .ai-chat-window {
                    width: calc(100vw - 40px);
                    height: calc(100vh - 100px);
                    bottom: 80px;
                }
            }
        `;
        
        const styleEl = document.createElement('style');
        styleEl.textContent = styles;
        document.head.appendChild(styleEl);
    }
    
    attachEventListeners() {
        const button = document.getElementById('ai-chat-button');
        const closeBtn = document.getElementById('ai-close');
        const minimizeBtn = document.getElementById('ai-minimize');
        const sendBtn = document.getElementById('ai-send-button');
        const input = document.getElementById('ai-chat-input');
        const contextSelect = document.getElementById('ai-context');
        
        button.addEventListener('click', () => this.toggleChat());
        closeBtn.addEventListener('click', () => this.closeChat());
        minimizeBtn.addEventListener('click', () => this.minimizeChat());
        sendBtn.addEventListener('click', () => this.sendMessage());
        
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        input.addEventListener('input', () => {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 120) + 'px';
        });
        
        contextSelect.addEventListener('change', (e) => {
            this.context = e.target.value;
        });
    }
    
    toggleChat() {
        this.isOpen = !this.isOpen;
        const window = document.getElementById('ai-chat-window');
        window.style.display = this.isOpen ? 'flex' : 'none';
        
        if (this.isOpen) {
            document.getElementById('ai-chat-input').focus();
        }
    }
    
    closeChat() {
        this.isOpen = false;
        document.getElementById('ai-chat-window').style.display = 'none';
    }
    
    minimizeChat() {
        this.isMinimized = !this.isMinimized;
        const window = document.getElementById('ai-chat-window');
        window.classList.toggle('minimized', this.isMinimized);
    }
    
    async sendMessage() {
        const input = document.getElementById('ai-chat-input');
        const message = input.value.trim();
        
        if (!message || this.isLoading) return;
        
        // Add user message to UI
        this.addMessage('user', message);
        input.value = '';
        input.style.height = 'auto';
        
        // Show typing indicator
        this.showTypingIndicator();
        this.isLoading = true;
        
        try {
            const response = await fetch('ai-chat.php?action=chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: message,
                    conversation_id: this.conversationId,
                    context: this.context
                })
            });
            
            const data = await response.json();
            
            this.hideTypingIndicator();
            
            if (data.success) {
                this.conversationId = data.conversation_id;
                this.addMessage('assistant', data.response);
            } else {
                this.addMessage('assistant', '❌ Sorry, I encountered an error: ' + (data.error || 'Unknown error'));
            }
        } catch (error) {
            this.hideTypingIndicator();
            this.addMessage('assistant', '❌ Network error. Please check your connection and try again.');
        } finally {
            this.isLoading = false;
        }
    }
    
    addMessage(role, content) {
        const messagesContainer = document.getElementById('ai-chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `ai-message ai-message-${role}`;
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'ai-message-content';
        contentDiv.innerHTML = this.formatMessage(content);
        
        messageDiv.appendChild(contentDiv);
        messagesContainer.appendChild(messageDiv);
        
        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    formatMessage(content) {
        // Convert markdown-like formatting
        content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        content = content.replace(/\*(.*?)\*/g, '<em>$1</em>');
        content = content.replace(/`(.*?)`/g, '<code style="background:#f3f4f6;padding:2px 6px;border-radius:4px;">$1</code>');
        content = content.replace(/\n/g, '<br>');
        return content;
    }
    
    showTypingIndicator() {
        const messagesContainer = document.getElementById('ai-chat-messages');
        const indicator = document.createElement('div');
        indicator.id = 'ai-typing-indicator';
        indicator.className = 'ai-message ai-message-assistant';
        indicator.innerHTML = `
            <div class="ai-message-content">
                <div class="ai-typing-indicator">
                    <div class="ai-typing-dot"></div>
                    <div class="ai-typing-dot"></div>
                    <div class="ai-typing-dot"></div>
                </div>
            </div>
        `;
        messagesContainer.appendChild(indicator);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    hideTypingIndicator() {
        const indicator = document.getElementById('ai-typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }
}

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Get context from page if specified
    const pageContext = document.body.dataset.aiContext || 'general';
    window.legendAI = new LegendAIChat({ context: pageContext });
});
