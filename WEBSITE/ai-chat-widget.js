/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - AI Chat Widget v2.0
 * Professional Dark Theme with Enhanced Features
 * ═══════════════════════════════════════════════════════════════════════════════
 */

class LegendAIChat {
    constructor(options = {}) {
        this.conversationId = null;
        this.context = options.context || 'general';
        this.isOpen = false;
        this.isMinimized = false;
        this.messages = [];
        this.isLoading = false;
        
        // Detect base path
        const scriptTag = document.currentScript || document.querySelector('script[src*="ai-chat-widget.js"]');
        const scriptSrc = scriptTag ? scriptTag.src : '';
        this.basePath = scriptSrc.includes('../ai-chat-widget.js') ? '../' : '';
        
        this.init();
    }
    
    init() {
        this.createWidget();
        this.attachEventListeners();
        this.checkAvailability();
    }
    
    async checkAvailability() {
        try {
            const response = await fetch(this.basePath + 'ai-chat.php?action=available');
            const data = await response.json();
            if (!data.success || !data.available) {
                console.log('AI features disabled');
            }
        } catch (error) {
            console.log('AI availability check failed, using fallback');
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
                    <span class="ai-pulse"></span>
                </button>
                
                <!-- Chat Window -->
                <div id="ai-chat-window" class="ai-chat-window" style="display: none;">
                    <div class="ai-chat-header">
                        <div class="ai-header-content">
                            <div class="ai-avatar">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M12 16v-4"></path>
                                    <path d="M12 8h.01"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="ai-title">Legend AI</div>
                                <div class="ai-status">
                                    <span class="ai-status-dot"></span>
                                    Online
                                </div>
                            </div>
                        </div>
                        <div class="ai-header-actions">
                            <button id="ai-new-chat" class="ai-icon-btn" title="New Chat">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                            <button id="ai-minimize" class="ai-icon-btn" title="Minimize">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                            <button id="ai-close" class="ai-icon-btn" title="Close">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="ai-chat-messages" id="ai-chat-messages">
                        <div class="ai-welcome-message">
                            <div class="ai-message ai-message-assistant">
                                <div class="ai-message-avatar">🤖</div>
                                <div class="ai-message-content">
                                    <p><strong>Hello!</strong> 👋 I'm your Legend House AI assistant.</p>
                                    <p>I can help you with:</p>
                                    <div class="ai-quick-actions">
                                        <button class="ai-quick-btn" data-query="How do I use Google Dorker?">🔍 Dorking Help</button>
                                        <button class="ai-quick-btn" data-query="How do I search torrents?">🧲 Torrent Help</button>
                                        <button class="ai-quick-btn" data-query="How do proxy scrapers work?">🌐 Proxy Help</button>
                                        <button class="ai-quick-btn" data-query="What features do you have?">⚡ Features</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ai-chat-input-container">
                        <div class="ai-context-bar">
                            <span class="ai-context-label">Context:</span>
                            <select id="ai-context" class="ai-context-select">
                                <option value="general">💬 General Help</option>
                                <option value="dorking">🔍 Google Dorking</option>
                                <option value="torrents">🧲 Torrents</option>
                                <option value="search">🔎 Search Tips</option>
                                <option value="technical">🛠️ Technical Support</option>
                            </select>
                        </div>
                        <div class="ai-input-wrapper">
                            <textarea 
                                id="ai-chat-input" 
                                class="ai-chat-input" 
                                placeholder="Ask me anything..." 
                                rows="1"
                            ></textarea>
                            <button id="ai-send-button" class="ai-send-button" title="Send message">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', widgetHTML);
        this.injectStyles();
    }
    
    injectStyles() {
        const isDark = document.body.getAttribute('data-theme') === 'dark' || 
                       window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        const styles = `
            .legend-ai-widget {
                position: fixed;
                bottom: 24px;
                right: 24px;
                z-index: 10000;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            
            .ai-chat-button {
                width: 56px;
                height: 56px;
                border-radius: 50%;
                background: ${isDark ? '#ffffff' : '#0d1117'};
                border: none;
                color: ${isDark ? '#0d1117' : '#ffffff'};
                cursor: pointer;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .ai-chat-button:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 28px rgba(0, 0, 0, 0.4);
            }
            
            .ai-badge {
                position: absolute;
                top: -4px;
                right: -4px;
                background: #3fb950;
                color: white;
                font-size: 9px;
                font-weight: 700;
                padding: 3px 6px;
                border-radius: 8px;
                letter-spacing: 0.5px;
            }
            
            .ai-pulse {
                position: absolute;
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background: ${isDark ? '#ffffff' : '#0d1117'};
                animation: ai-pulse 2s ease-in-out infinite;
                opacity: 0;
                pointer-events: none;
            }
            
            @keyframes ai-pulse {
                0% { transform: scale(1); opacity: 0.5; }
                100% { transform: scale(1.5); opacity: 0; }
            }
            
            .ai-chat-window {
                position: absolute;
                bottom: 72px;
                right: 0;
                width: 400px;
                max-width: calc(100vw - 48px);
                height: 560px;
                max-height: calc(100vh - 120px);
                background: ${isDark ? '#161b22' : '#ffffff'};
                border: 1px solid ${isDark ? '#30363d' : '#d0d7de'};
                border-radius: 16px;
                box-shadow: 0 16px 48px rgba(0, 0, 0, 0.3);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                animation: slideUp 0.3s ease;
            }
            
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            .ai-chat-window.minimized {
                height: 56px;
            }
            
            .ai-chat-header {
                background: ${isDark ? '#21262d' : '#f6f8fa'};
                border-bottom: 1px solid ${isDark ? '#30363d' : '#d0d7de'};
                padding: 12px 16px;
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
                background: ${isDark ? '#30363d' : '#e6e8eb'};
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: ${isDark ? '#f0f6fc' : '#1f2328'};
            }
            
            .ai-title {
                font-weight: 600;
                font-size: 14px;
                color: ${isDark ? '#f0f6fc' : '#1f2328'};
            }
            
            .ai-status {
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 12px;
                color: ${isDark ? '#8b949e' : '#656d76'};
            }
            
            .ai-status-dot {
                width: 8px;
                height: 8px;
                background: #3fb950;
                border-radius: 50%;
                box-shadow: 0 0 8px #3fb950;
            }
            
            .ai-header-actions {
                display: flex;
                gap: 4px;
            }
            
            .ai-icon-btn {
                width: 32px;
                height: 32px;
                border: none;
                background: transparent;
                color: ${isDark ? '#8b949e' : '#656d76'};
                cursor: pointer;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s;
            }
            
            .ai-icon-btn:hover {
                background: ${isDark ? '#30363d' : '#e6e8eb'};
                color: ${isDark ? '#f0f6fc' : '#1f2328'};
            }
            
            .ai-chat-messages {
                flex: 1;
                overflow-y: auto;
                padding: 16px;
                background: ${isDark ? '#0d1117' : '#ffffff'};
            }
            
            .ai-message {
                display: flex;
                gap: 12px;
                margin-bottom: 16px;
                animation: fadeIn 0.3s ease;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            .ai-message-avatar {
                width: 32px;
                height: 32px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                flex-shrink: 0;
                background: ${isDark ? '#21262d' : '#f6f8fa'};
            }
            
            .ai-message-user {
                flex-direction: row-reverse;
            }
            
            .ai-message-user .ai-message-avatar {
                background: ${isDark ? '#ffffff' : '#0d1117'};
                color: ${isDark ? '#0d1117' : '#ffffff'};
                font-size: 12px;
                font-weight: 600;
            }
            
            .ai-message-content {
                flex: 1;
                padding: 12px 16px;
                border-radius: 12px;
                font-size: 14px;
                line-height: 1.5;
                max-width: 85%;
            }
            
            .ai-message-assistant .ai-message-content {
                background: ${isDark ? '#21262d' : '#f6f8fa'};
                color: ${isDark ? '#f0f6fc' : '#1f2328'};
                border-radius: 12px 12px 12px 4px;
            }
            
            .ai-message-user .ai-message-content {
                background: ${isDark ? '#ffffff' : '#0d1117'};
                color: ${isDark ? '#0d1117' : '#ffffff'};
                border-radius: 12px 12px 4px 12px;
            }
            
            .ai-message-content p {
                margin: 0 0 8px 0;
            }
            
            .ai-message-content p:last-child {
                margin-bottom: 0;
            }
            
            .ai-message-content strong {
                font-weight: 600;
            }
            
            .ai-message-content code {
                background: ${isDark ? '#30363d' : '#e6e8eb'};
                padding: 2px 6px;
                border-radius: 4px;
                font-family: monospace;
                font-size: 13px;
            }
            
            .ai-message-content a {
                color: #58a6ff;
                text-decoration: none;
            }
            
            .ai-message-content a:hover {
                text-decoration: underline;
            }
            
            .ai-quick-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 12px;
            }
            
            .ai-quick-btn {
                padding: 6px 12px;
                background: ${isDark ? '#30363d' : '#e6e8eb'};
                border: 1px solid ${isDark ? '#484f58' : '#d0d7de'};
                border-radius: 20px;
                font-size: 12px;
                color: ${isDark ? '#f0f6fc' : '#1f2328'};
                cursor: pointer;
                transition: all 0.2s;
            }
            
            .ai-quick-btn:hover {
                background: ${isDark ? '#484f58' : '#d0d7de'};
                border-color: ${isDark ? '#f0f6fc' : '#1f2328'};
            }
            
            .ai-typing-indicator {
                display: flex;
                gap: 4px;
                padding: 4px;
            }
            
            .ai-typing-dot {
                width: 8px;
                height: 8px;
                background: ${isDark ? '#8b949e' : '#656d76'};
                border-radius: 50%;
                animation: typing 1.4s infinite;
            }
            
            .ai-typing-dot:nth-child(2) { animation-delay: 0.2s; }
            .ai-typing-dot:nth-child(3) { animation-delay: 0.4s; }
            
            @keyframes typing {
                0%, 60%, 100% { transform: translateY(0); }
                30% { transform: translateY(-8px); }
            }
            
            .ai-chat-input-container {
                border-top: 1px solid ${isDark ? '#30363d' : '#d0d7de'};
                background: ${isDark ? '#161b22' : '#ffffff'};
                padding: 12px;
            }
            
            .ai-context-bar {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 8px;
            }
            
            .ai-context-label {
                font-size: 11px;
                color: ${isDark ? '#8b949e' : '#656d76'};
                font-weight: 500;
            }
            
            .ai-context-select {
                flex: 1;
                padding: 6px 10px;
                background: ${isDark ? '#21262d' : '#f6f8fa'};
                border: 1px solid ${isDark ? '#30363d' : '#d0d7de'};
                border-radius: 6px;
                font-size: 12px;
                color: ${isDark ? '#f0f6fc' : '#1f2328'};
                cursor: pointer;
            }
            
            .ai-input-wrapper {
                display: flex;
                gap: 8px;
                align-items: flex-end;
            }
            
            .ai-chat-input {
                flex: 1;
                padding: 12px;
                background: ${isDark ? '#21262d' : '#f6f8fa'};
                border: 1px solid ${isDark ? '#30363d' : '#d0d7de'};
                border-radius: 12px;
                font-size: 14px;
                color: ${isDark ? '#f0f6fc' : '#1f2328'};
                resize: none;
                max-height: 100px;
                font-family: inherit;
            }
            
            .ai-chat-input::placeholder {
                color: ${isDark ? '#6e7681' : '#8c959f'};
            }
            
            .ai-chat-input:focus {
                outline: none;
                border-color: ${isDark ? '#f0f6fc' : '#0d1117'};
            }
            
            .ai-send-button {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                background: ${isDark ? '#ffffff' : '#0d1117'};
                border: none;
                color: ${isDark ? '#0d1117' : '#ffffff'};
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s;
                flex-shrink: 0;
            }
            
            .ai-send-button:hover {
                transform: scale(1.05);
                opacity: 0.9;
            }
            
            .ai-send-button:disabled {
                opacity: 0.5;
                cursor: not-allowed;
                transform: none;
            }
            
            @media (max-width: 480px) {
                .legend-ai-widget {
                    bottom: 16px;
                    right: 16px;
                }
                
                .ai-chat-window {
                    width: calc(100vw - 32px);
                    height: calc(100vh - 100px);
                    bottom: 72px;
                    right: -8px;
                }
            }
        `;
        
        const styleEl = document.createElement('style');
        styleEl.id = 'legend-ai-styles';
        styleEl.textContent = styles;
        document.head.appendChild(styleEl);
    }
    
    attachEventListeners() {
        const button = document.getElementById('ai-chat-button');
        const closeBtn = document.getElementById('ai-close');
        const minimizeBtn = document.getElementById('ai-minimize');
        const newChatBtn = document.getElementById('ai-new-chat');
        const sendBtn = document.getElementById('ai-send-button');
        const input = document.getElementById('ai-chat-input');
        const contextSelect = document.getElementById('ai-context');
        
        button?.addEventListener('click', () => this.toggleChat());
        closeBtn?.addEventListener('click', () => this.closeChat());
        minimizeBtn?.addEventListener('click', () => this.minimizeChat());
        newChatBtn?.addEventListener('click', () => this.newChat());
        sendBtn?.addEventListener('click', () => this.sendMessage());
        
        input?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        input?.addEventListener('input', () => {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 100) + 'px';
        });
        
        contextSelect?.addEventListener('change', (e) => {
            this.context = e.target.value;
        });
        
        // Quick action buttons
        document.querySelectorAll('.ai-quick-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const query = btn.dataset.query;
                if (query) {
                    document.getElementById('ai-chat-input').value = query;
                    this.sendMessage();
                }
            });
        });
        
        // Theme change observer
        const observer = new MutationObserver(() => {
            const oldStyle = document.getElementById('legend-ai-styles');
            if (oldStyle) oldStyle.remove();
            this.injectStyles();
        });
        
        observer.observe(document.body, { attributes: true, attributeFilter: ['data-theme'] });
    }
    
    toggleChat() {
        this.isOpen = !this.isOpen;
        const chatWindow = document.getElementById('ai-chat-window');
        chatWindow.style.display = this.isOpen ? 'flex' : 'none';
        
        if (this.isOpen) {
            document.getElementById('ai-chat-input')?.focus();
        }
    }
    
    closeChat() {
        this.isOpen = false;
        document.getElementById('ai-chat-window').style.display = 'none';
    }
    
    minimizeChat() {
        this.isMinimized = !this.isMinimized;
        const chatWindow = document.getElementById('ai-chat-window');
        chatWindow.classList.toggle('minimized', this.isMinimized);
    }
    
    newChat() {
        this.conversationId = null;
        this.messages = [];
        
        const messagesContainer = document.getElementById('ai-chat-messages');
        messagesContainer.innerHTML = `
            <div class="ai-welcome-message">
                <div class="ai-message ai-message-assistant">
                    <div class="ai-message-avatar">🤖</div>
                    <div class="ai-message-content">
                        <p><strong>New conversation started!</strong> 🚀</p>
                        <p>How can I help you today?</p>
                        <div class="ai-quick-actions">
                            <button class="ai-quick-btn" data-query="How do I use Google Dorker?">🔍 Dorking Help</button>
                            <button class="ai-quick-btn" data-query="How do I search torrents?">🧲 Torrent Help</button>
                            <button class="ai-quick-btn" data-query="How do proxy scrapers work?">🌐 Proxy Help</button>
                            <button class="ai-quick-btn" data-query="What features do you have?">⚡ Features</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Re-attach quick action listeners
        document.querySelectorAll('.ai-quick-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const query = btn.dataset.query;
                if (query) {
                    document.getElementById('ai-chat-input').value = query;
                    this.sendMessage();
                }
            });
        });
    }
    
    async sendMessage() {
        const input = document.getElementById('ai-chat-input');
        const message = input.value.trim();
        
        if (!message || this.isLoading) return;
        
        // Clear input
        input.value = '';
        input.style.height = 'auto';
        
        // Get user initial for avatar
        const userInitial = 'U';
        
        // Add user message
        this.addMessage('user', message, userInitial);
        
        // Show typing indicator
        this.showTypingIndicator();
        this.isLoading = true;
        
        try {
            const response = await fetch(this.basePath + 'ai-chat.php?action=chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
                this.addMessage('assistant', '❌ ' + (data.error || 'Something went wrong. Please try again.'));
            }
        } catch (error) {
            this.hideTypingIndicator();
            this.addMessage('assistant', '❌ Network error. Please check your connection and try again.');
        } finally {
            this.isLoading = false;
        }
    }
    
    addMessage(role, content, userInitial = 'U') {
        const messagesContainer = document.getElementById('ai-chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `ai-message ai-message-${role}`;
        
        const avatar = role === 'assistant' ? '🤖' : userInitial;
        
        messageDiv.innerHTML = `
            <div class="ai-message-avatar">${avatar}</div>
            <div class="ai-message-content">${this.formatMessage(content)}</div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    formatMessage(content) {
        // Convert markdown-like formatting
        content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        content = content.replace(/\*(.*?)\*/g, '<em>$1</em>');
        content = content.replace(/`(.*?)`/g, '<code>$1</code>');
        content = content.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');
        content = content.replace(/\n/g, '<br>');
        
        // Convert bullet points
        content = content.replace(/• /g, '• ');
        
        return content;
    }
    
    showTypingIndicator() {
        const messagesContainer = document.getElementById('ai-chat-messages');
        const indicator = document.createElement('div');
        indicator.id = 'ai-typing-indicator';
        indicator.className = 'ai-message ai-message-assistant';
        indicator.innerHTML = `
            <div class="ai-message-avatar">🤖</div>
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
        if (indicator) indicator.remove();
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    const pageContext = document.body.dataset.aiContext || 'general';
    window.legendAI = new LegendAIChat({ context: pageContext });
});
