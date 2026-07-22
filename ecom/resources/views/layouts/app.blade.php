<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --primary-dark: #1d4ed8; }
        body { min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.08); }
        .hero { background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%); color: #fff; padding: 80px 0; }
        .card-product { transition: transform .2s, box-shadow .2s; }
        .card-product:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
        .price { color: var(--primary); font-weight: 700; font-size: 1.25rem; }
        .price-old { text-decoration: line-through; color: #9ca3af; font-size: .875rem; }
        .badge-stock { font-size: .75rem; }
        .sidebar-admin { min-height: calc(100vh - 56px); background: #1e293b; }
        .sidebar-admin .nav-link { color: #94a3b8; padding: 10px 20px; }
        .sidebar-admin .nav-link:hover, .sidebar-admin .nav-link.active { color: #fff; background: rgba(255,255,255,.1); }
        .stat-card { border-left: 4px solid; border-radius: 8px; }
        footer { background: #1e293b; color: #94a3b8; }

        .ai-chat-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            border: none;
            box-shadow: 0 4px 16px rgba(37,99,235,.4);
            font-size: 24px;
            cursor: pointer;
            z-index: 9999;
            transition: transform .2s, box-shadow .2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ai-chat-btn:hover { transform: scale(1.1); box-shadow: 0 6px 24px rgba(37,99,235,.5); }

        .ai-chat-modal {
            position: fixed;
            bottom: 92px;
            right: 24px;
            width: 380px;
            max-height: 520px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 32px rgba(0,0,0,.18);
            z-index: 9998;
            flex-direction: column;
            overflow: hidden;
        }
        .ai-chat-header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            padding: 14px 16px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ai-chat-close-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 22px;
            cursor: pointer;
            line-height: 1;
        }
        .ai-chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 360px;
        }
        .ai-chat-msg { display: flex; }
        .ai-chat-msg-user { justify-content: flex-end; }
        .ai-chat-msg-bot { justify-content: flex-start; }
        .ai-chat-msg-text {
            max-width: 80%;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: .9rem;
            line-height: 1.45;
            word-wrap: break-word;
        }
        .ai-chat-msg-user .ai-chat-msg-text { background: #2563eb; color: #fff; border-bottom-right-radius: 4px; }
        .ai-chat-msg-bot .ai-chat-msg-text { background: #f1f5f9; color: #1e293b; border-bottom-left-radius: 4px; }
        .ai-chat-msg-loading .ai-chat-msg-text { color: #94a3b8; font-style: italic; }
        .ai-chat-input-area {
            display: flex;
            padding: 10px 12px;
            border-top: 1px solid #e2e8f0;
            gap: 8px;
        }
        .ai-chat-input-area input {
            flex: 1;
            border: 1px solid #cbd5e1;
            border-radius: 24px;
            padding: 10px 16px;
            font-size: .9rem;
            outline: none;
        }
        .ai-chat-input-area input:focus { border-color: #2563eb; }
        .ai-chat-input-area button {
            width: 40px;
            height: 40px;
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
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                <i class="bi bi-shop"></i> {{ config('app.name') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('categories.index') }}">Categories</a></li>
                </ul>
                <ul class="navbar-nav">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Admin</a></li>
                        @endif
                        @if(auth()->user()->isEmployee())
                            <li class="nav-item"><a class="nav-link" href="{{ route('employee.orders.index') }}"><i class="bi bi-briefcase"></i> Employee</a></li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('customer.orders.index') }}">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item" type="submit">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="container mt-3">
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <main class="flex-grow-1">
        @yield('content')
    </main>

    <footer class="py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </footer>

    <!-- AI Chat Floating Button -->
    <button id="ai-chat-toggle" class="ai-chat-btn" title="Ask AI Assistant">
        <i class="bi bi-robot"></i>
    </button>

    <!-- AI Chat Modal -->
    <div id="ai-chat-modal" class="ai-chat-modal" style="display:none;">
        <div class="ai-chat-header">
            <span><i class="bi bi-robot"></i> AI Assistant</span>
            <button id="ai-chat-close" class="ai-chat-close-btn">&times;</button>
        </div>
        <div id="ai-chat-messages" class="ai-chat-messages">
            <div class="ai-chat-msg ai-chat-msg-bot">
                <div class="ai-chat-msg-text">Hi! Ask me anything about our products and policies.</div>
            </div>
        </div>
        <form id="ai-chat-form" class="ai-chat-input-area">
            <input id="ai-chat-input" type="text" placeholder="Type your question..." maxlength="5000" autocomplete="off">
            <button id="ai-chat-send" type="submit"><i class="bi bi-send"></i></button>
        </form>
    </div>

    <script>
    (function() {
        const toggle = document.getElementById('ai-chat-toggle');
        const modal = document.getElementById('ai-chat-modal');
        const closeBtn = document.getElementById('ai-chat-close');
        const form = document.getElementById('ai-chat-form');
        const input = document.getElementById('ai-chat-input');
        const sendBtn = document.getElementById('ai-chat-send');
        const messages = document.getElementById('ai-chat-messages');

        toggle.addEventListener('click', () => {
            modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
            if (modal.style.display === 'flex') input.focus();
        });

        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const question = input.value.trim();
            if (!question) return;

            appendMessage(question, 'user');
            input.value = '';
            sendBtn.disabled = true;

            const loadingEl = appendMessage('Thinking...', 'bot loading');

            try {
                const res = await fetch('{{ route("chat.ask") }}', {
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
                    loadingEl.querySelector('.ai-chat-msg-text').textContent = 'AI is thinking...';
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
                await new Promise(r => setTimeout(r, intervalMs));

                try {
                    const res = await fetch('{{ url("chat/result") }}/' + requestId);
                    const data = await res.json();

                    if (data.status === 'completed') {
                        loadingEl.remove();
                        appendMessage(data.answer || 'Sorry, something went wrong.', 'bot');
                        return;
                    }
                } catch (err) {
                    console.warn('Poll attempt ' + i + ' failed', err);
                }
            }

            loadingEl.remove();
            appendMessage('Request timed out. Please try again.', 'bot');
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
