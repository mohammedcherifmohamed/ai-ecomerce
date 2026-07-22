@extends('layouts.admin')

@section('title', 'AI Chat - Analytics')

@section('page-title', 'AI Analytics Assistant')

@section('styles')
<style>
    .ai-chat-wrapper {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 180px);
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
        overflow: hidden;
    }
    .ai-chat-header {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        padding: 14px 20px;
        font-weight: 600;
    }
    .ai-chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .ai-chat-msg { display: flex; }
    .ai-chat-msg-user { justify-content: flex-end; }
    .ai-chat-msg-bot { justify-content: flex-start; }
    .ai-chat-msg-text {
        max-width: 75%;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: .95rem;
        line-height: 1.5;
        word-wrap: break-word;
        white-space: pre-wrap;
    }
    .ai-chat-msg-user .ai-chat-msg-text {
        background: #2563eb;
        color: #fff;
        border-bottom-right-radius: 4px;
    }
    .ai-chat-msg-bot .ai-chat-msg-text {
        background: #f1f5f9;
        color: #1e293b;
        border-bottom-left-radius: 4px;
    }
    .ai-chat-msg-loading .ai-chat-msg-text {
        color: #94a3b8;
        font-style: italic;
    }
    .ai-chat-input-area {
        display: flex;
        padding: 14px 20px;
        border-top: 1px solid #e2e8f0;
        gap: 10px;
        background: #f8fafc;
    }
    .ai-chat-input-area input {
        flex: 1;
        border: 1px solid #cbd5e1;
        border-radius: 24px;
        padding: 10px 18px;
        font-size: .95rem;
        outline: none;
    }
    .ai-chat-input-area input:focus { border-color: #2563eb; }
    .ai-chat-input-area button {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: none;
        background: #2563eb;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ai-chat-input-area button:disabled { opacity: .5; }
    .ai-chat-welcome h5 { color: #2563eb; margin-bottom: 4px; }
    .ai-chat-welcome p { color: #64748b; font-size: .9rem; margin-bottom: 2px; }
</style>
@endsection

@section('content')
<div class="ai-chat-wrapper">
    <div class="ai-chat-header">
        <i class="bi bi-robot"></i> AI Analytics Assistant
        <span class="badge bg-light text-dark ms-2" style="font-size:.7rem;">Admin</span>
    </div>
    <div id="ai-chat-messages" class="ai-chat-messages">
        <div class="ai-chat-msg ai-chat-msg-bot">
            <div class="ai-chat-msg-text ai-chat-welcome">
                <h5>Hi, Admin!</h5>
                <p>Ask me about customers, orders, inquiries, or trends. I'll retrieve the data and present it as bullet points.</p>
                <p class="mt-2" style="font-size:.85rem;color:#94a3b8;">
                    Examples: "Show me recent customer inquiries" &bull; "Summary of customer #5" &bull; "Monthly order trends this year" &bull; "Analyze support tickets by category"
                </p>
            </div>
        </div>
    </div>
    <form id="ai-chat-form" class="ai-chat-input-area">
        <input id="ai-chat-input" type="text" placeholder="Ask about customers, orders, inquiries..." maxlength="5000" autocomplete="off">
        <button id="ai-chat-send" type="submit"><i class="bi bi-send"></i></button>
    </form>
</div>
@endsection

@section('scripts')
<script>
(function() {
    const form = document.getElementById('ai-chat-form');
    const input = document.getElementById('ai-chat-input');
    const sendBtn = document.getElementById('ai-chat-send');
    const messages = document.getElementById('ai-chat-messages');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const question = input.value.trim();
        if (!question) return;

        appendMessage(question, 'user');
        input.value = '';
        sendBtn.disabled = true;

        const loadingEl = appendMessage('Processing...', 'bot loading');

        try {
            const res = await fetch('{{ route("admin.ai.chat.ask") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ question }),
            });

            const data = await res.json();

            if (data.request_id) {
                loadingEl.querySelector('.ai-chat-msg-text').textContent = 'AI is analyzing...';
                pollForResult(data.request_id, loadingEl);
            } else if (data.answer) {
                loadingEl.remove();
                appendMessage(data.answer, 'bot');
            } else {
                loadingEl.remove();
                appendMessage('Sorry, something went wrong.', 'bot');
            }
        } catch (err) {
            loadingEl.remove();
            appendMessage('Failed to connect. Please try again.', 'bot');
        }

        sendBtn.disabled = false;
        input.focus();
    });

    async function pollForResult(requestId, loadingEl) {
        const maxAttempts = 150;
        const intervalMs = 2000;

        for (let i = 0; i < maxAttempts; i++) {
            await sleep(intervalMs);

            try {
                const res = await fetch('{{ url("admin/ai/chat/result") }}/' + requestId);
                const data = await res.json();

                if (data.status === 'completed') {
                    loadingEl.remove();
                    if (data.answer) {
                        appendMessage(data.answer, 'bot');
                    } else {
                        appendMessage('Sorry, something went wrong.', 'bot');
                    }
                    return;
                }
            } catch (err) {
                console.warn('Poll attempt ' + i + ' failed', err);
            }
        }

        loadingEl.remove();
        appendMessage('Request timed out. Please try again.', 'bot');
    }

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function appendMessage(text, type) {
        const div = document.createElement('div');
        div.className = 'ai-chat-msg ai-chat-msg-' + type.split(' ')[0];
        div.innerHTML = '<div class="ai-chat-msg-text">' + escapeHtml(text) + '</div>';
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
        return div;
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }
})();
</script>
@endsection
