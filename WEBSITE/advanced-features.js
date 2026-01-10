/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Advanced AI Features v10.0
 * ═══════════════════════════════════════════════════════════════════════════════
 */

// Global AI state
const aiState = {
    enabled: false,
    suggestions: [],
    trending: [],
    analyzing: false
};

// Check if AI features are available - now always available with local fallback
async function checkAIAvailability() {
    try {
        const response = await fetch('ai-helper.php?action=available');
        const data = await response.json();
        // AI is always enabled with local fallback
        aiState.enabled = true;
        console.log('🤖 AI Features: ENABLED (with local fallback)');
        initAIFeatures();
    } catch (error) {
        console.error('AI check failed, using local fallback:', error);
        // Still enable AI features with local fallback
        aiState.enabled = true;
        initAIFeatures();
    }
}

// Show notice that AI features are disabled (once per session)
function showAIDisabledNoticeOnce() {
    // Only show once per session
    if (sessionStorage.getItem('aiNoticeShown')) return;
    
    // Mark as shown immediately to prevent race conditions
    sessionStorage.setItem('aiNoticeShown', 'true');
    
    const notice = document.createElement('div');
    notice.className = 'ai-disabled-notice';
    notice.innerHTML = `
        <div class="notice-content">
            <span class="notice-icon">💡</span>
            <span class="notice-text">AI features are currently disabled. Contact administrator to enable AI-powered suggestions.</span>
            <button class="notice-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    notice.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(20, 20, 30, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 16px;
        max-width: 400px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        backdrop-filter: blur(10px);
    `;
    
    const closeBtn = notice.querySelector('.notice-close');
    if (closeBtn) {
        closeBtn.style.cssText = `
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            margin-left: 12px;
        `;
    }
    
    document.body.appendChild(notice);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notice.parentElement) {
            notice.remove();
        }
    }, 5000);
}

// Initialize AI-powered features
function initAIFeatures() {
    // Add AI search suggestions
    const searchInput = document.getElementById('searchInput');
    if (searchInput && aiState.enabled) {
        searchInput.addEventListener('input', debounce(async (e) => {
            const query = e.target.value.trim();
            if (query.length > 2) {
                await getAISuggestions(query);
            }
        }, 500));
    }
    
    // Load trending topics
    loadTrendingTopics();
    
    // Add AI styles
    addAIStyles();
    
    // Show AI indicator
    showAIIndicator();
}

// Get AI-powered search suggestions
async function getAISuggestions(query) {
    if (!aiState.enabled) return;
    
    try {
        const response = await fetch(`ai-helper.php?action=suggestions&query=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success && data.suggestions && data.suggestions.suggestions) {
            aiState.suggestions = data.suggestions.suggestions;
            displayAISuggestions(aiState.suggestions);
        }
    } catch (error) {
        console.error('AI suggestions error:', error);
    }
}

// Display AI suggestions
function displayAISuggestions(suggestions) {
    if (!suggestions || suggestions.length === 0) return;
    
    let container = document.getElementById('aiSuggestions');
    if (!container) {
        container = document.createElement('div');
        container.id = 'aiSuggestions';
        container.className = 'ai-suggestions';
        
        const searchContainer = document.querySelector('.search-container');
        if (searchContainer) {
            searchContainer.appendChild(container);
        }
    }
    
    container.innerHTML = `
        <div class="ai-suggestions-header">
            <span class="ai-icon">🤖</span>
            <span class="ai-label">AI Suggestions</span>
        </div>
        <div class="ai-suggestions-list">
            ${suggestions.map(s => `
                <button class="ai-suggestion-item" onclick="applyAISuggestion('${escapeHtml(s)}')">
                    <span class="suggestion-icon">💡</span>
                    <span class="suggestion-text">${escapeHtml(s)}</span>
                </button>
            `).join('')}
        </div>
    `;
    
    container.style.display = 'block';
}

// Apply AI suggestion
function applyAISuggestion(suggestion) {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.value = suggestion;
        if (typeof performSearch === 'function') {
            performSearch();
        }
    }
    
    // Hide suggestions
    const container = document.getElementById('aiSuggestions');
    if (container) {
        container.style.display = 'none';
    }
}

// Load trending topics
async function loadTrendingTopics() {
    if (!aiState.enabled) return;
    
    try {
        const response = await fetch('ai-helper.php?action=trending');
        const data = await response.json();
        
        if (data.success && data.trending) {
            aiState.trending = data.trending;
            displayTrendingTopics(data.trending);
        }
    } catch (error) {
        console.error('Trending topics error:', error);
    }
}

// Display trending topics
function displayTrendingTopics(trending) {
    if (!trending || trending.length === 0) return;
    
    const hero = document.querySelector('.hero');
    if (!hero) return;
    
    let container = document.getElementById('trendingTopics');
    if (!container) {
        container = document.createElement('div');
        container.id = 'trendingTopics';
        container.className = 'trending-topics';
        hero.appendChild(container);
    }
    
    container.innerHTML = `
        <div class="trending-header">
            <span class="trending-icon">🔥</span>
            <h3 class="trending-title">Trending Now</h3>
            <span class="ai-badge">AI Powered</span>
        </div>
        <div class="trending-list">
            ${trending.slice(0, 8).map(topic => `
                <button class="trending-item" onclick="quickSearch('${escapeHtml(topic)}')">
                    ${escapeHtml(topic)}
                </button>
            `).join('')}
        </div>
    `;
}

// Analyze torrent with AI
async function analyzeWithAI(index) {
    if (!aiState.enabled) {
        if (typeof showToast === 'function') {
            showToast('AI features not available', 'warning');
        }
        return;
    }
    
    const result = state.results[index];
    if (!result) return;
    
    aiState.analyzing = true;
    if (typeof showToast === 'function') {
        showToast('🤖 Analyzing with AI...', 'info');
    }
    
    try {
        const response = await fetch(`ai-helper.php?action=analyze&name=${encodeURIComponent(result.name)}`);
        const data = await response.json();
        
        if (data.success && data.analysis) {
            displayAnalysisResult(result.name, data.analysis);
        } else {
            if (typeof showToast === 'function') {
                showToast('Analysis failed', 'error');
            }
        }
    } catch (error) {
        console.error('AI analysis error:', error);
        if (typeof showToast === 'function') {
            showToast('Analysis error', 'error');
        }
    } finally {
        aiState.analyzing = false;
    }
}

// Display analysis result
function displayAnalysisResult(torrentName, analysis) {
    const modal = document.createElement('div');
    modal.className = 'ai-analysis-modal active';
    modal.innerHTML = `
        <div class="modal-overlay" onclick="this.parentElement.remove()"></div>
        <div class="analysis-content">
            <div class="analysis-header">
                <h3>🤖 AI Analysis</h3>
                <button class="modal-close" onclick="this.closest('.ai-analysis-modal').remove()">×</button>
            </div>
            <div class="analysis-body">
                <div class="analysis-torrent-name">
                    <strong>Torrent:</strong> ${escapeHtml(torrentName)}
                </div>
                <div class="analysis-results">
                    ${analysis.genre ? `
                        <div class="analysis-item">
                            <span class="analysis-label">Genre:</span>
                            <span class="analysis-value">${escapeHtml(analysis.genre)}</span>
                        </div>
                    ` : ''}
                    ${analysis.quality ? `
                        <div class="analysis-item">
                            <span class="analysis-label">Quality:</span>
                            <span class="analysis-value">${escapeHtml(analysis.quality)}</span>
                        </div>
                    ` : ''}
                    ${analysis.type ? `
                        <div class="analysis-item">
                            <span class="analysis-label">Content Type:</span>
                            <span class="analysis-value">${escapeHtml(analysis.type)}</span>
                        </div>
                    ` : ''}
                    ${analysis.year ? `
                        <div class="analysis-item">
                            <span class="analysis-label">Year:</span>
                            <span class="analysis-value">${escapeHtml(analysis.year)}</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Add AI styles
function addAIStyles() {
    if (!aiState.enabled) return;
    
    const style = document.createElement('style');
    style.textContent = `
        .ai-analyze-btn {
            padding: 6px 12px;
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }
        
        .ai-analyze-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(147, 51, 234, 0.4);
        }
        
        .ai-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(20, 20, 30, 0.98);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 16px;
            margin-top: 8px;
            backdrop-filter: blur(20px);
            z-index: 1000;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .ai-suggestions-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .ai-icon {
            font-size: 18px;
        }
        
        .ai-label {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }
        
        .ai-suggestions-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .ai-suggestion-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            color: #fff;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
        }
        
        .ai-suggestion-item:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(147, 51, 234, 0.5);
            transform: translateX(4px);
        }
        
        .suggestion-icon {
            font-size: 16px;
        }
        
        .trending-topics {
            margin: 32px 0;
            padding: 24px;
            background: linear-gradient(135deg, rgba(147, 51, 234, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
            border: 1px solid rgba(147, 51, 234, 0.2);
            border-radius: 16px;
            backdrop-filter: blur(10px);
        }
        
        .trending-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .trending-icon {
            font-size: 24px;
        }
        
        .trending-title {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            margin: 0;
            flex: 1;
        }
        
        .ai-badge {
            padding: 4px 12px;
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .trending-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .trending-item {
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .trending-item:hover {
            background: rgba(147, 51, 234, 0.2);
            border-color: rgba(147, 51, 234, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(147, 51, 234, 0.3);
        }
        
        .ai-analysis-modal {
            position: fixed;
            inset: 0;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }
        
        .analysis-content {
            position: relative;
            max-width: 600px;
            width: 100%;
            background: linear-gradient(135deg, rgba(30, 30, 50, 0.98) 0%, rgba(15, 15, 30, 0.98) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.6);
            animation: modalSlideIn 0.4s ease;
        }
        
        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        
        .analysis-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .analysis-header h3 {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 32px;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .modal-close:hover {
            opacity: 1;
        }
        
        .analysis-torrent-name {
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .analysis-results {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .analysis-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 16px;
            background: rgba(147, 51, 234, 0.1);
            border: 1px solid rgba(147, 51, 234, 0.2);
            border-radius: 10px;
        }
        
        .analysis-label {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .analysis-value {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
        }
        
        .ai-indicator {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 12px 20px;
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
            border-radius: 30px;
            box-shadow: 0 8px 25px rgba(147, 51, 234, 0.4);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 9999;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 8px 25px rgba(147, 51, 234, 0.4); }
            50% { box-shadow: 0 8px 35px rgba(147, 51, 234, 0.6); }
        }
        
        .ai-indicator-icon {
            font-size: 20px;
        }
        
        .ai-indicator-text {
            font-size: 13px;
            font-weight: 700;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .voice-search-btn {
            position: absolute;
            right: 120px;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            border: none;
            border-radius: 12px;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }
        
        .voice-search-btn:hover {
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.5);
        }
    `;
    document.head.appendChild(style);
}

// Show AI indicator
function showAIIndicator() {
    if (!aiState.enabled) return;
    
    const indicator = document.createElement('div');
    indicator.className = 'ai-indicator';
    indicator.innerHTML = `
        <span class="ai-indicator-icon">🤖</span>
        <span class="ai-indicator-text">AI Powered</span>
    `;
    document.body.appendChild(indicator);
}

// Voice Search (Web Speech API)
function initVoiceSearch() {
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        console.log('Voice search not supported');
        return;
    }
    
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';
    
    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = transcript;
            if (typeof performSearch === 'function') {
                performSearch();
            }
            if (typeof showToast === 'function') {
                showToast(`🎤 Voice search: "${transcript}"`, 'success');
            }
        }
    };
    
    recognition.onerror = (event) => {
        console.error('Voice recognition error:', event.error);
        if (typeof showToast === 'function') {
            showToast('Voice search error', 'error');
        }
    };
    
    // Add voice search button
    const searchContainer = document.querySelector('.search-container');
    if (searchContainer) {
        const voiceBtn = document.createElement('button');
        voiceBtn.className = 'voice-search-btn';
        voiceBtn.innerHTML = '🎤';
        voiceBtn.title = 'Voice Search';
        voiceBtn.onclick = () => {
            recognition.start();
            if (typeof showToast === 'function') {
                showToast('🎤 Listening...', 'info');
            }
        };
        searchContainer.appendChild(voiceBtn);
    }
    
    window.startVoiceSearch = () => recognition.start();
}

// Initialize all advanced features
document.addEventListener('DOMContentLoaded', () => {
    checkAIAvailability();
    initVoiceSearch();
});

// Expose functions globally
window.analyzeWithAI = analyzeWithAI;
window.applyAISuggestion = applyAISuggestion;
window.checkAIAvailability = checkAIAvailability;
