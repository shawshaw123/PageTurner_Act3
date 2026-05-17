@extends('layouts.app')

@section('title', 'PageTurner AI - Bookstore Assistant')

@push('styles')
<style>
    body, html {
        overflow-x: hidden;
    }

    /* Premium Chat Container */
    .chat-container {
        display: grid;
        grid-template-columns: 280px 1fr;
        height: 75vh;
        min-height: 600px;
        max-height: 75vh;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 35px rgba(49, 71, 46, 0.06);
        border: 1px solid rgba(49, 71, 46, 0.08);
        overflow: hidden;
    }

    @media (max-width: 768px) {
        .chat-container {
            grid-template-columns: 1fr;
        }
        .chat-sidebar {
            display: none;
        }
    }

    /* Sidebar */
    .chat-sidebar {
        background: #31472E;
        color: #ffffff;
        padding: 28px 24px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border-right: 1px solid rgba(255, 255, 255, 0.08);
    }

    .sidebar-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 18px;
        margin-bottom: 24px;
    }

    .sidebar-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #FFBF00;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sidebar-subtitle {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.6);
        margin-top: 4px;
    }

    .sidebar-features {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-bottom: 24px;
    }

    .feature-item {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        padding: 14px;
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.85);
        transition: all 0.2s;
    }

    .feature-item:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.12);
        transform: translateX(2px);
    }

    .new-chat-btn {
        background: #FFBF00;
        color: #31472E;
        border: none;
        padding: 14px 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(255, 191, 0, 0.2);
    }

    .new-chat-btn:hover {
        background: #ffffff;
        color: #31472E;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(255, 191, 0, 0.35);
    }

    /* Main Chat Area */
    .chat-main {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        background: #fdfdfd;
        overflow: hidden;
    }

    /* Header */
    .chat-header {
        background: #ffffff;
        border-bottom: 1px solid rgba(49, 71, 46, 0.06);
        padding: 16px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chat-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ai-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #31472E, #233621);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        box-shadow: 0 4px 10px rgba(49, 71, 46, 0.12);
    }

    .ai-info h2 {
        color: #31472E;
        font-size: 1rem;
        font-weight: 700;
    }

    .ai-info p {
        color: #6b7280;
        font-size: 0.75rem;
        margin-top: 1px;
        display: flex;
        align-items: center;
    }

    .status-dot {
        width: 7px;
        height: 7px;
        background: #10b981;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
        animation: blink 1.5s ease-in-out infinite;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    @keyframes bounce {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-8px); }
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }

    .btn-icon {
        background: rgba(49, 71, 46, 0.05);
        border: 1px solid rgba(49, 71, 46, 0.1);
        color: #31472E;
        padding: 8px 14px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .btn-icon:hover {
        background: #31472E;
        color: #ffffff;
        border-color: #31472E;
    }

    /* Messages Area */
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        background: #fafbfa;
        scroll-behavior: smooth;
        min-height: 0;
    }

    .chat-messages::-webkit-scrollbar { width: 8px; }
    .chat-messages::-webkit-scrollbar-track { background: transparent; }
    .chat-messages::-webkit-scrollbar-thumb { background: rgba(49, 71, 46, 0.25); border-radius: 4px; }
    .chat-messages::-webkit-scrollbar-thumb:hover { background: rgba(49, 71, 46, 0.4); }

    /* Welcome message */
    .welcome-msg {
        text-align: center;
        padding: 40px 20px;
        max-width: 600px;
        margin: auto;
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .welcome-icon {
        font-size: 3.5rem;
        margin-bottom: 16px;
    }

    .welcome-msg h3 {
        color: #31472E;
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .welcome-msg p {
        color: #6b7280;
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 24px;
    }

    .quick-suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }

    .suggestion-chip {
        background: rgba(49, 71, 46, 0.04);
        border: 1px solid rgba(49, 71, 46, 0.08);
        color: #31472E;
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .suggestion-chip:hover {
        background: #31472E;
        color: #ffffff;
        border-color: #31472E;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(49, 71, 46, 0.12);
    }

    /* Message bubbles */
    .message {
        display: flex;
        gap: 12px;
        max-width: 80%;
        animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .message.user {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .message.assistant {
        align-self: flex-start;
    }

    .msg-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }

    .message.assistant .msg-avatar {
        background: #31472E;
        color: #ffffff;
    }

    .message.user .msg-avatar {
        background: #FFBF00;
        color: #31472E;
    }

    .msg-bubble {
        padding: 14px 18px;
        border-radius: 18px;
        font-size: 0.9rem;
        line-height: 1.6;
        word-break: break-word;
        box-shadow: 0 2px 6px rgba(0,0,0,0.01);
    }

    .message.assistant .msg-bubble {
        background: #ffffff;
        border: 1px solid rgba(49, 71, 46, 0.06);
        color: #1f2937;
        border-radius: 4px 18px 18px 18px;
    }

    .message.user .msg-bubble {
        background: #31472E;
        color: #ffffff;
        border-radius: 18px 4px 18px 18px;
        font-weight: 500;
    }

    .msg-meta {
        font-size: 0.7rem;
        color: #9ca3af;
        margin-top: 6px;
        padding: 0 4px;
    }

    .message.user .msg-meta { text-align: right; }

    /* Typing indicator */
    .typing-indicator {
        display: none;
        align-self: flex-start;
    }

    .typing-indicator.active {
        display: flex;
        gap: 12px;
    }

    .typing-dots {
        background: #ffffff;
        border: 1px solid rgba(49, 71, 46, 0.06);
        border-radius: 4px 18px 18px 18px;
        padding: 14px 20px;
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .typing-dots span {
        width: 8px;
        height: 8px;
        background: #31472E;
        opacity: 0.4;
        border-radius: 50%;
        animation: bounce 1.4s ease-in-out infinite;
    }

    .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.4s; }

    /* Input Area */
    .chat-input-area {
        background: #ffffff;
        border-top: 1px solid rgba(49, 71, 46, 0.06);
        padding: 18px 24px;
    }

    .input-container {
        display: flex;
        gap: 12px;
        align-items: flex-end;
    }

    .chat-input {
        flex: 1;
        background: #f9fafb;
        border: 1px solid rgba(49, 71, 46, 0.15);
        border-radius: 14px;
        padding: 12px 18px;
        color: #1f2937;
        font-size: 0.9rem;
        outline: none;
        resize: none;
        min-height: 48px;
        max-height: 120px;
        line-height: 1.5;
        transition: all 0.2s;
    }

    .chat-input::placeholder { color: #9ca3af; }
    .chat-input:focus {
        border-color: #31472E;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(49, 71, 46, 0.08);
    }

    .send-btn {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #31472E;
        border: none;
        color: #FFBF00;
        font-size: 1.2rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(49, 71, 46, 0.1);
        flex-shrink: 0;
    }

    .send-btn:hover {
        background: #FFBF00;
        color: #31472E;
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(255, 191, 0, 0.35);
    }

    .send-btn:active { transform: scale(0.97); }
    .send-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    .input-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 12px;
    }

    .powered-by {
        color: #9ca3af;
        font-size: 0.75rem;
    }

    .provider-badge {
        background: rgba(49, 71, 46, 0.04);
        border: 1px solid rgba(49, 71, 46, 0.08);
        color: #31472E;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .error-toast {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #ef4444;
        color: #ffffff;
        padding: 12px 24px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 500;
        box-shadow: 0 10px 25px rgba(239, 68, 68, 0.15);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        z-index: 9999;
    }

    .error-toast.show { transform: translateX(-50%) translateY(0); }
</style>
@endpush

@section('content')
<div class="chat-container">
    <!-- Sidebar -->
    <div class="chat-sidebar">
        <div>
            <div class="sidebar-header">
                <div class="sidebar-title">
                    <span>📚</span> PageTurner AI
                </div>
                <div class="sidebar-subtitle">
                    Smart Bookstore Assistant
                </div>
            </div>
            
            <div class="sidebar-features">
                <div class="feature-item">
                    <strong>💡 Personalized Suggestions</strong>
                    <p class="text-xs text-white/60 mt-1">Get custom recommendations based on genres you love.</p>
                </div>
                <div class="feature-item">
                    <strong>📦 Order & Catalog Queries</strong>
                    <p class="text-xs text-white/60 mt-1">Check current inventory, prices, and book details instantly.</p>
                </div>
                <div class="feature-item">
                    <strong>⚡ Smart Instant Search</strong>
                    <p class="text-xs text-white/60 mt-1">Ask questions about authors, releases, or budget options.</p>
                </div>
            </div>
        </div>

        <button class="new-chat-btn" onclick="newConversation()">
            <span>➕</span> New Chat
        </button>
    </div>

    <!-- Main Chat Area -->
    <div class="chat-main">
        <!-- Header -->
        <div class="chat-header">
            <div class="chat-header-left">
                <div class="ai-avatar">🤖</div>
                <div class="ai-info">
                    <h2>PageTurner AI Assistant</h2>
                    <p><span class="status-dot"></span>Online • Powered by OpenAI GPT-4o-mini <span id="demoModeBadge" style="display:none; margin-left:8px; background:#FFBF00; color:#31472E; padding:2px 8px; border-radius:4px; font-size:0.65rem; font-weight:700;">DEMO MODE</span></p>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn-icon md:hidden" onclick="newConversation()">New Chat</button>
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.ai.dashboard') }}" class="btn-icon">Dashboard</a>
                    @endif
                @endauth
            </div>
        </div>

        <!-- Messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="welcome-msg" id="welcomeMsg">
                <div class="welcome-icon">📚</div>
                <h3>Hello! I'm your PageTurner AI Assistant</h3>
                <p>I can help you find books, get recommendations, check stock, and answer any questions about our bookstore.</p>
                <div class="quick-suggestions">
                    <button class="suggestion-chip" onclick="sendQuick('Recommend me a good fiction book')">📖 Recommend fiction</button>
                    <button class="suggestion-chip" onclick="sendQuick('What books do you have on programming?')">💻 Programming books</button>
                    <button class="suggestion-chip" onclick="sendQuick('How many books are in stock?')">📊 Check inventory</button>
                    <button class="suggestion-chip" onclick="sendQuick('Find books under $15')">💰 Budget books</button>
                </div>
            </div>

            <!-- Typing indicator -->
            <div class="typing-indicator" id="typingIndicator">
                <div class="msg-avatar">🤖</div>
                <div class="typing-dots">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="chat-input-area">
            <div class="input-container">
                <textarea
                    class="chat-input"
                    id="messageInput"
                    placeholder="Ask me anything about books..."
                    rows="1"
                    onkeydown="handleKeyDown(event)"
                    oninput="autoResize(this)"
                ></textarea>
                <button class="send-btn" id="sendBtn" onclick="sendMessage()">➤</button>
            </div>
            <div class="input-footer">
                <span class="powered-by">PageTurner AI • Lab Activity 8</span>
                <span class="provider-badge" id="providerBadge">OpenAI GPT-4o-mini</span>
            </div>
        </div>
    </div>
</div>

<div class="error-toast" id="errorToast"></div>
@endsection

@push('scripts')
<script>
    const sessionId = '{{ session()->getId() }}';
    let isLoading = false;

    // Load existing history on page load
    window.addEventListener('DOMContentLoaded', () => {
        fetch(`/ai/chat/history?session_id=${sessionId}`)
            .then(r => r.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    const welcome = document.getElementById('welcomeMsg');
                    if (welcome) welcome.remove();
                    data.messages.forEach(msg => appendMessage(msg.role, msg.content, msg.timestamp));
                    scrollBottom();
                }
            });
    });

    function handleKeyDown(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    }

    function autoResize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 120) + 'px';
    }

    function sendQuick(text) {
        document.getElementById('messageInput').value = text;
        sendMessage();
    }

    async function sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        if (!message || isLoading) return;

        // Remove welcome message
        const welcome = document.getElementById('welcomeMsg');
        if (welcome) welcome.remove();

        // Show user message
        appendMessage('user', message, 'Just now');
        input.value = '';
        input.style.height = 'auto';

        // Show typing indicator
        setLoading(true);

        try {
            const response = await fetch('/ai/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message, session_id: sessionId })
            });

            const data = await response.json();

            if (data.success) {
                appendMessage('assistant', data.data.message, 'Just now', data.data.provider, data.data.response_time);

                // Update provider badge
                const badge = document.getElementById('providerBadge');
                const demoBadge = document.getElementById('demoModeBadge');
                if (data.data.provider === 'openai') {
                    badge.textContent = `OpenAI GPT-4o-mini • ${data.data.response_time}s`;
                    demoBadge.style.display = 'none';
                } else if (data.data.provider === 'gemini') {
                    badge.textContent = `Google Gemini • ${data.data.response_time}s`;
                    demoBadge.style.display = 'none';
                } else if (data.data.provider === 'mock') {
                    badge.textContent = `Demo Mode (Mock) • ${data.data.response_time}s`;
                    demoBadge.style.display = 'inline';
                } else {
                    badge.textContent = `Ollama (Local) • ${data.data.response_time}s`;
                    demoBadge.style.display = 'none';
                }
            } else {
                showError(data.message || 'Something went wrong. Please try again.');
            }
        } catch (err) {
            showError('Connection error. Please check your internet connection.');
        } finally {
            setLoading(false);
        }
    }

    function appendMessage(role, content, time = '', provider = '', responseTime = '') {
        const messages = document.getElementById('chatMessages');
        const indicator = document.getElementById('typingIndicator');

        const div = document.createElement('div');
        div.className = `message ${role}`;

        const avatar = role === 'assistant' ? '🤖' : '👤';
        const metaText = role === 'assistant' && provider
            ? `${time} • via ${provider}${responseTime ? ' • ' + responseTime + 's' : ''}`
            : time;

        div.innerHTML = `
            <div class="msg-avatar">${avatar}</div>
            <div>
                <div class="msg-bubble">${formatMessage(content)}</div>
                <div class="msg-meta">${metaText}</div>
            </div>
        `;

        messages.insertBefore(div, indicator);
        scrollBottom();
    }

    function formatMessage(text) {
        // Convert markdown-style formatting to HTML
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>')
            .replace(/`(.*?)`/g, '<code class="bg-gray-100 px-1.5 py-0.5 rounded text-sm font-mono">$1</code>');
    }

    function setLoading(loading) {
        isLoading = loading;
        const indicator = document.getElementById('typingIndicator');
        const sendBtn = document.getElementById('sendBtn');
        indicator.classList.toggle('active', loading);
        sendBtn.disabled = loading;
        if (loading) scrollBottom();
    }

    function scrollBottom() {
        const messages = document.getElementById('chatMessages');
        setTimeout(() => messages.scrollTop = messages.scrollHeight, 100);
    }

    function showError(message) {
        const toast = document.getElementById('errorToast');
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    }

    async function newConversation() {
        await fetch('/ai/chat/new', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ session_id: sessionId })
        });

        // Clear chat UI
        const messages = document.getElementById('chatMessages');
        const indicator = document.getElementById('typingIndicator');
        messages.innerHTML = '';
        messages.appendChild(indicator);

        const welcomeDiv = document.createElement('div');
        welcomeDiv.id = 'welcomeMsg';
        welcomeDiv.className = 'welcome-msg';
        welcomeDiv.innerHTML = `
            <div class="welcome-icon">📚</div>
            <h3>New Conversation Started</h3>
            <p>How can I help you today?</p>
            <div class="quick-suggestions">
                <button class="suggestion-chip" onclick="sendQuick('Recommend me a good fiction book')">📖 Recommend fiction</button>
                <button class="suggestion-chip" onclick="sendQuick('What books do you have on programming?')">💻 Programming books</button>
                <button class="suggestion-chip" onclick="sendQuick('How many books are in stock?')">📊 Check inventory</button>
                <button class="suggestion-chip" onclick="sendQuick('Find books under \$15')">💰 Budget books</button>
            </div>
        `;
        messages.insertBefore(welcomeDiv, indicator);
    }
</script>
@endpush
