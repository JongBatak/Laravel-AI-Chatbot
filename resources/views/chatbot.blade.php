<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zwingli.AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('Pictures/favicon.ico') }}?v=2" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('Pictures/favicon.ico') }}?v=2" type="image/x-icon">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #01170c;
            color: #e5e7eb;
        }
        .particle-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background: radial-gradient(circle at 15% 20%, rgba(74, 222, 128, 0.12), transparent 45%),
                        radial-gradient(circle at 80% 10%, rgba(16, 185, 129, 0.25), transparent 40%),
                        linear-gradient(180deg, #0b3b25, #02160f);
        }
        .particle-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
            mix-blend-mode: screen;
        }
        .particle-canvas span {
            position: absolute;
            top: var(--top, 50%);
            left: var(--left, 50%);
            width: var(--size, 3px);
            height: var(--size, 3px);
            background: rgba(255, 255, 255, 0.85);
            border-radius: 999px;
            filter: blur(0.35px);
            opacity: var(--opacity, 0.6);
            animation: drift var(--duration, 14s) linear infinite;
            animation-delay: var(--delay, 0s);
        }
        @keyframes drift {
            0% {
                transform: translate3d(0, 0, 0) scale(1);
                opacity: 0.35;
            }
            50% {
                transform: translate3d(var(--move-x, 0px), var(--move-y, 0px), 0) scale(1.4);
                opacity: 1;
            }
            100% {
                transform: translate3d(0, 0, 0) scale(1);
                opacity: 0.35;
            }
        }
        ::-webkit-scrollbar {
            width: 0px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(148,163,184,0.4);
            border-radius: 999px;
        }
        #chat-box.hide-scroll {
            overflow-y: hidden;
            scrollbar-width: none;
        }
        #chat-box.hide-scroll::-webkit-scrollbar {
            width: 0;
        }
        .formatted-response {
            line-height: 1.7;
            font-size: 0.95rem;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .formatted-response strong {
            color: #f8fafc;
        }
        .reply-heading {
            text-transform: uppercase;
            letter-spacing: 0.25em;
            font-size: 0.7rem;
            color: #94a3b8;
            margin-bottom: 0.35rem;
            display: block;
        }
        .math-inline {
            font-family: 'Fira Code', monospace;
            background-color: rgba(20, 184, 166, 0.1);
            border-radius: 0.35rem;
            padding: 0 0.3rem;
            color: #34d399;
        }
        pre {
            background-color: #1f2937;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 0.75rem;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .chat-bubble {
            animation: fadeInUp .35s ease both;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .typing-indicator span {
            animation: pulse 1.4s infinite ease-in-out;
            animation-fill-mode: both;
        }
        .typing-indicator span:nth-child(2) { animation-delay: .2s; }
        .typing-indicator span:nth-child(3) { animation-delay: .4s; }
        @keyframes pulse {
            0%, 80%, 100% { transform: scale(0); opacity: .3; }
            40% { transform: scale(1); opacity: 1; }
        }
        .chat-layout {
            width: 100%;
        }
        .chat-layout.sidebar-collapsed aside {
            width: 0;
            padding: 0;
            border: none;
            overflow: hidden;
            transition: width 0.25s ease, padding 0.25s ease;
        }
        .chat-layout.sidebar-collapsed aside > * {
            opacity: 0;
        }
        .sidebar-toggle {
            background: rgba(15, 118, 110, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ecfdf5;
        }
    </style>
</head>
<body class="h-full relative">
    <div class="particle-bg"></div>
    <div id="particle-canvas" class="particle-canvas"></div>
    <div class="min-h-screen w-full bg-transparent px-0 relative z-10">
        <div class="chat-layout flex h-full min-h-screen w-full max-w-full">
            <aside class="hidden md:flex w-64 flex-col bg-white/5 border border-white/10 rounded-3xl backdrop-blur p-5">
                <button id="new-chat" class="flex items-center gap-2 px-4 py-3 rounded-2xl bg-emerald-500 text-slate-900 font-semibold shadow hover:bg-emerald-400 transition">
                    <span>＋</span>
                    New Chat
                </button>
                <div class="mt-6 text-slate-400 text-xs uppercase tracking-[0.35em]">Riwayat</div>
                <div id="history-placeholder" class="mt-3 flex-1 text-sm text-slate-500 leading-relaxed">
                    Sesi sebelumnya belum disimpan. Mulai percakapan baru dengan tombol di atas.
                </div>
            </aside>

            <div class="flex-1 flex flex-col gap-4">
                <header class="bg-white/5 border border-white/10 rounded-3xl px-6 py-4 flex items-center justify-between backdrop-blur">
                    <div class="flex items-center gap-3">
                        <button id="sidebar-toggle" class="sidebar-toggle flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.4em] transition hover:bg-emerald-400 hover:text-slate-900">
                            <span>☰</span>
                            Sidebar
                        </button>
                        <div>
                            <p class="text-sm uppercase tracking-[0.25em] text-slate-400">Assistant</p>
                            <h1 class="text-2xl font-semibold">Zwingli.AI</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-slate-300">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        Terhubung ke Internet
                    </div>
                </header>

                <main class="flex-1 bg-white/5 border border-white/10 rounded-3xl backdrop-blur flex flex-col overflow-hidden">
                    <div id="chat-box" class="flex-1 overflow-y-auto px-6 py-8 space-y-6"></div>
                    <div class="border-top border-white/10 px-6 py-4 border-t">
                        <div class="rounded-2xl bg-gradient-to-r from-slate-900/70 to-slate-900/50 border border-white/10 p-4 mb-4">
                            <div class="flex items-center justify-between mb-2 text-xs uppercase tracking-[0.35em] text-slate-500">
                                <span>Command Box</span>
                                <span>Rumus / Kode</span>
                            </div>
                            <p class="text-[12px] text-slate-400 mb-3">Gunakan area ini untuk merangkai rumus matematika atau potongan kode agar tetap rapi sebelum mengirim.</p>
                            <div class="flex flex-wrap gap-2 items-center mb-2">
                                <button type="button" data-format="bold" class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.3em] text-slate-200 hover:bg-white/20">Bold</button>
                                <button type="button" data-format="italic" class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.3em] text-slate-200 hover:bg-white/20">Italic</button>
                                <button type="button" data-format="code" class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.3em] text-slate-200 hover:bg-white/20">Code</button>
                                <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.25em] text-slate-400">
                                    Font
                                    <select id="command-font" class="rounded-full bg-slate-900/60 border border-white/10 px-2 py-1 text-xs text-slate-100">
                                        <option value="mono" selected>Monospace</option>
                                        <option value="serif">Serif</option>
                                        <option value="sans">Sans</option>
                                    </select>
                                </label>
                            </div>
                            <textarea id="command-input" rows="2" placeholder="Misal: integrate(f(x), x) atau function hitung() { }" class="w-full resize-none rounded-xl bg-slate-900/70 border border-white/10 px-3 py-2 text-slate-100 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-400/60"></textarea>
                            <div class="mt-2 flex flex-wrap gap-2 text-[10px] text-slate-400">
                                <button type="button" data-symbol="^2" class="rounded-full bg-white/10 px-3 py-1 uppercase tracking-[0.3em] text-[10px] font-semibold text-slate-200 hover:bg-white/20">Pangkat ²</button>
                                <button type="button" data-symbol="^3" class="rounded-full bg-white/10 px-3 py-1 uppercase tracking-[0.3em] text-[10px] font-semibold text-slate-200 hover:bg-white/20">Pangkat ³</button>
                                <button type="button" data-prefix="\sqrt{" data-suffix="}" class="rounded-full bg-white/10 px-3 py-1 uppercase tracking-[0.3em] text-[10px] font-semibold text-slate-200 hover:bg-white/20">Akar</button>
                                <button type="button" data-symbol="°" class="rounded-full bg-white/10 px-3 py-1 uppercase tracking-[0.3em] text-[10px] font-semibold text-slate-200 hover:bg-white/20">Derajat</button>
                                <button type="button" data-symbol="f∘g" class="rounded-full bg-white/10 px-3 py-1 uppercase tracking-[0.3em] text-[10px] font-semibold text-slate-200 hover:bg-white/20" title="Komposisi f∘g">Komposisi</button>
                                <button type="button" data-power class="rounded-full bg-white/10 px-3 py-1 uppercase tracking-[0.3em] text-[10px] font-semibold text-slate-200 hover:bg-white/20">Pangkat</button>
                                <button type="button" data-fraction class="rounded-full bg-white/10 px-3 py-1 uppercase tracking-[0.3em] text-[10px] font-semibold text-slate-200 hover:bg-white/20">Pecahan</button>
                            </div>
                            <div class="mt-1 flex justify-between items-center">
                                <button id="use-command" class="text-[12px] uppercase tracking-[0.4em] text-emerald-400 hover:text-emerald-300 transition">Gunakan di Chat ➜</button>
                                <span class="text-[10px] uppercase tracking-[0.4em] text-slate-500">Pilih formatting dulu</span>
                            </div>
                        </div>
                        <form id="chat-form" class="flex items-end gap-3">
                            <textarea id="user-input" rows="1" placeholder="Ketik pesan dan tekan Enter..." class="flex-1 resize-none rounded-2xl bg-white/5 border border-white/10 px-4 py-3 text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-400/60"></textarea>
                            <button id="send-button" type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-900 rounded-2xl px-6 py-3 font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Kirim
                            </button>
                        </form>
                        <p class="text-[11px] text-center text-slate-500 mt-3">Menggunakan model AI melalui n8n webhook.</p>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        const chatBox = document.getElementById('chat-box');
        const chatForm = document.getElementById('chat-form');
        const userInput = document.getElementById('user-input');
        const sendButton = document.getElementById('send-button');
        const newChatButton = document.getElementById('new-chat');
        const commandInput = document.getElementById('command-input');
        const useCommand = document.getElementById('use-command');
        const formatButtons = document.querySelectorAll('[data-format]');
        const commandFont = document.getElementById('command-font');
        const commandSymbolButtons = document.querySelectorAll('[data-symbol], [data-prefix]');
        const powerButton = document.querySelector('[data-power]');
        const fractionButton = document.querySelector('[data-fraction]');
        const chatLayout = document.querySelector('.chat-layout');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const webhookUrl = 'https://8ranpo-n8n.bocindonesia.com/webhook/zwingliganteng';
        const aiAvatarUrl = "{{ asset('Pictures/Gemini_Generated_Image_9eqa7i9eqa7i9eqa.png') }}";
        const particleCanvas = document.getElementById('particle-canvas');

        const randomBetween = (min, max) => Math.random() * (max - min) + min;

        if (particleCanvas) {
            const fragment = document.createDocumentFragment();
            for (let i = 0; i < 90; i++) {
                const particle = document.createElement('span');
                particle.style.setProperty('--top', `${randomBetween(0, 100)}%`);
                particle.style.setProperty('--left', `${randomBetween(0, 100)}%`);
                particle.style.setProperty('--size', `${randomBetween(1.5, 4.2)}px`);
                particle.style.setProperty('--duration', `${randomBetween(12, 26)}s`);
                particle.style.setProperty('--delay', `${-randomBetween(0, 26)}s`);
                particle.style.setProperty('--move-x', `${randomBetween(-50, 50)}px`);
                particle.style.setProperty('--move-y', `${randomBetween(-60, 60)}px`);
                particle.style.setProperty('--opacity', `${randomBetween(0.35, 0.85)}`);
                fragment.appendChild(particle);
            }
            particleCanvas.appendChild(fragment);
        }

        const autoResize = (element) => {
            element.style.height = 'auto';
            element.style.height = `${element.scrollHeight}px`;
        };

        userInput.addEventListener('input', () => autoResize(userInput));
        userInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                if (!sendButton.disabled) {
                    chatForm.requestSubmit();
                }
            }
        });

        const wrapCommandSelection = (prefix, suffix = '') => {
            if (!commandInput) return;
            const start = commandInput.selectionStart;
            const end = commandInput.selectionEnd;
            const value = commandInput.value;
            const selected = value.substring(start, end) || prefix.trim();
            const wrapped = `${prefix}${selected}${suffix || prefix}`;
            commandInput.value = value.slice(0, start) + wrapped + value.slice(end);
            commandInput.focus();
            const newPosition = start + wrapped.length;
            commandInput.setSelectionRange(newPosition, newPosition);
        };

        formatButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const type = button.getAttribute('data-format');
                if (!commandInput) return;
                if (type === 'bold') wrapCommandSelection('**');
                if (type === 'italic') wrapCommandSelection('*');
                if (type === 'code') wrapCommandSelection('`');
            });
        });
        const superscriptMap = {
            '0': '⁰', '1': '¹', '2': '²', '3': '³', '4': '⁴',
            '5': '⁵', '6': '⁶', '7': '⁷', '8': '⁸', '9': '⁹',
            '+': '⁺', '-': '⁻', '=': '⁼', '(': '⁽', ')': '⁾',
            'n': 'ⁿ'
        };

        const toSuperscript = (value) => {
            return [...value].map(ch => superscriptMap[ch] || ch).join('');
        };

        const insertAtCaret = (value) => {
            if (!commandInput) return;
            const start = commandInput.selectionStart;
            const end = commandInput.selectionEnd;
            const text = commandInput.value;
            commandInput.value = text.slice(0, start) + value + text.slice(end);
            const cursor = start + value.length;
            commandInput.setSelectionRange(cursor, cursor);
            commandInput.focus();
        };

        commandSymbolButtons.forEach((button) => {
            button.addEventListener('click', () => {
                if (!commandInput) return;
                const symbol = button.getAttribute('data-symbol');
                const prefix = button.getAttribute('data-prefix');
                const suffix = button.getAttribute('data-suffix');
                if (symbol) {
                    insertAtCaret(symbol);
                } else if (prefix) {
                    const content = commandInput.value.substring(commandInput.selectionStart, commandInput.selectionEnd) || 'x';
                    insertAtCaret(`${prefix}${content}${suffix || ''}`);
                }
            });
        });

        powerButton?.addEventListener('click', () => {
            if (!commandInput) return;
            const start = commandInput.selectionStart;
            const end = commandInput.selectionEnd;
            const selection = commandInput.value.substring(start, end) || '2';
            const value = toSuperscript(selection);
            insertAtCaret(value);
        });

        fractionButton?.addEventListener('click', () => {
            if (!commandInput) return;
            const num = prompt('Masukkan pembilang (numerator)', '12');
            if (!num) return;
            const den = prompt('Masukkan penyebut (denominator)', '3');
            if (!den) return;
            insertAtCaret(`\frac{${num}}{${den}}`);
        });

        commandFont?.addEventListener('change', () => {
            const value = commandFont.value;
            if (!commandInput) return;
            commandInput.style.fontFamily = value === 'mono'
                ? "'Fira Code', 'JetBrains Mono', Consolas, monospace"
                : value === 'serif'
                    ? "'Times New Roman', 'Georgia', serif"
                    : "'Inter', sans-serif";
        });

        const addGreeting = () => {
            chatBox.innerHTML = '';
            const bubble = document.createElement('div');
            bubble.className = 'chat-bubble flex items-start gap-4';
            bubble.innerHTML = `
                <img src="{{ asset('Pictures/AI Logo.png') }}" alt="Zwingli.AI avatar" class="h-10 w-10 rounded-2xl object-cover" />
                <div class="flex-1 bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-slate-200">
                    Halo! Saya Zwingli.AI. Mulai percakapan baru kapan saja dan saya akan menjawab langsung dari workflow n8n.
                </div>
            `;
            chatBox.appendChild(bubble);
        };

        addGreeting();

        const scrollToBottom = () => {
            chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });
        };
        
        const updateScrollbarVisibility = () => {
            if (chatBox.scrollHeight <= chatBox.clientHeight + 2) {
                chatBox.classList.add('hide-scroll');
            } else {
                chatBox.classList.remove('hide-scroll');
            }
        };

        const createBubble = (role, content) => {
            const wrapper = document.createElement('div');
            wrapper.className = `chat-bubble flex items-start gap-4 ${role === 'user' ? 'flex-row-reverse text-right' : ''}`;

            const avatar = document.createElement('div');
            avatar.className = `h-10 w-10 rounded-2xl flex items-center justify-center font-semibold overflow-hidden ${role === 'user' ? 'bg-slate-300/20 text-slate-100' : 'bg-emerald-500/20 text-emerald-300'}`;
            if (role === 'user') {
                avatar.textContent = 'You';
            } else {
                const img = document.createElement('img');
                img.src = aiAvatarUrl;
                img.alt = 'Zwingli.AI avatar';
                img.className = 'h-full w-full object-cover';
                avatar.appendChild(img);
            }

            const bubble = document.createElement('div');
            bubble.className = `flex-1 rounded-2xl px-5 py-4 border ${role === 'user' ? 'bg-white/10 border-white/20 text-slate-100' : 'bg-white/5 border-white/10 text-slate-200'}`;

            bubble.appendChild(content);
            wrapper.append(avatar, bubble);
            chatBox.appendChild(wrapper);
            scrollToBottom();
            updateScrollbarVisibility();
        };

        const createTypingIndicator = () => {
            const indicator = document.createElement('div');
            indicator.className = 'flex items-center gap-2 typing-indicator';
            indicator.innerHTML = `
                <span class="w-2.5 h-2.5 bg-slate-400 rounded-full"></span>
                <span class="w-2.5 h-2.5 bg-slate-400 rounded-full"></span>
                <span class="w-2.5 h-2.5 bg-slate-400 rounded-full"></span>
            `;
            return indicator;
        };

        const escapeHtml = (value) => {
            return value.replace(/[&<>"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[char]));
        };

        const convertMarkdown = (text) => {
            const normalized = text.replace(/\r\n/g, '\n');
            let safe = escapeHtml(normalized);
            safe = safe.replace(/^#{1,3}\s*(.+)$/gm, '<span class="reply-heading">$1</span>');
            safe = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            safe = safe.replace(/\*(.+?)\*/g, '<em>$1</em>');
            safe = safe.replace(/`([^`]+)`/g, '<span class="math-inline">$1</span>');
            safe = safe.replace(/\$(.+?)\$/g, '<span class="math-inline">$1</span>');
            safe = safe.replace(/\n{2,}/g, '<br><br>');
            safe = safe.replace(/\n/g, '<br>');
            return safe;
        };

        const typeText = (container, text) => {
            return new Promise(resolve => {
                let i = 0;
                const typer = setInterval(() => {
                    container.textContent += text.charAt(i);
                    i++;
                    scrollToBottom();
                    if (i >= text.length) {
                        clearInterval(typer);
                        container.innerHTML = convertMarkdown(text);
                        resolve();
                    }
                }, 18);
            });
        };

        const insertCodeBlock = (container, code) => {
            const pre = document.createElement('pre');
            const codeEl = document.createElement('code');
            codeEl.textContent = code.trim();
            pre.appendChild(codeEl);
            container.appendChild(pre);
        };

        const renderBotReply = async (text, bubble) => {
            const segments = text.split('```');
            for (let i = 0; i < segments.length; i++) {
                const part = segments[i];
                if (i % 2 === 1) {
                    insertCodeBlock(bubble, part);
                } else if (part) {
                    const formatted = document.createElement('div');
                    formatted.className = 'formatted-response';
                    bubble.appendChild(formatted);
                    await typeText(formatted, part);
                }
            }
        };

        const handleError = (container, message) => {
            const errorText = document.createElement('p');
            errorText.className = 'text-rose-300';
            errorText.textContent = message;
            container.appendChild(errorText);
        };

        chatForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const message = userInput.value.trim();
            if (!message) return;

            sendButton.disabled = true;

            const userContent = document.createElement('p');
            userContent.textContent = message;
            createBubble('user', userContent);

            userInput.value = '';
            autoResize(userInput);

            const botContent = document.createElement('div');
            const typingIndicator = createTypingIndicator();
            botContent.appendChild(typingIndicator);
            createBubble('bot', botContent);

            try {
                const response = await fetch(webhookUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ content: message }),
                    mode: 'cors'
                });

                if (!response.ok) {
                    throw new Error(`n8n error: ${response.status}`);
                }

                const raw = await response.text();
                let payload;
                try {
                    payload = JSON.parse(raw);
                } catch (parseError) {
                    payload = { reply: raw };
                }

                const reply = (payload.reply || payload.response || payload.message || raw || '').trim();

                typingIndicator.remove();
                if (reply) {
                    await renderBotReply(reply, botContent);
                } else {
                    handleError(botContent, 'Workflow tidak mengirim respons. Pastikan n8n mengembalikan field reply.');
                }
                updateScrollbarVisibility();
            } catch (err) {
                typingIndicator.remove();
                handleError(botContent, 'Maaf, workflow n8n belum merespons. Coba lagi nanti.');
                updateScrollbarVisibility();
                console.error('Chatbot error:', err);
            } finally {
                sendButton.disabled = false;
                updateScrollbarVisibility();
                scrollToBottom();
            }
        });

        newChatButton?.addEventListener('click', () => {
            addGreeting();
            userInput.value = '';
            autoResize(userInput);
        });

        sidebarToggle?.addEventListener('click', () => {
            chatLayout?.classList.toggle('sidebar-collapsed');
        });

        const transferCommandToChat = (submit = false) => {
            const snippet = commandInput?.value.trim();
            if (!snippet) return;
            userInput.value = snippet;
            autoResize(userInput);
            userInput.focus();
            if (submit && !sendButton.disabled) {
                chatForm.requestSubmit();
            }
        };

        useCommand?.addEventListener('click', () => transferCommandToChat(false));
        commandInput?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                transferCommandToChat(true);
            }
        });
    </script>
</body>
</html>
