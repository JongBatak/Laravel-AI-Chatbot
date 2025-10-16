<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot AI</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .chat-container {
            max-height: 80vh;
            overflow-y: auto;
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none; /* Firefox */
        }
        .chat-container::-webkit-scrollbar {
            display: none; /* Chrome, Safari, and Opera */
        }
        /* Animasi titik-titik (typing) */
        .typing-indicator span {
            animation: pulse 1.4s infinite ease-in-out;
            animation-fill-mode: both;
        }
        .typing-indicator span:nth-child(1) {
            animation-delay: 0s;
        }
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        @keyframes pulse {
            0%, 80%, 100% {
                transform: scale(0);
            }
            40% {
                transform: scale(1);
            }
        }
        pre {
            background-color: #2d3748;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-lg bg-white rounded-lg shadow-xl overflow-hidden flex flex-col h-[70vh] md:h-[80vh]">
        <!-- Header -->
        <div class="bg-blue-600 text-white p-4 text-center rounded-t-lg shadow-md">
            <h1 class="text-2xl font-bold">Zwingli.AI</h1>
        </div>

        <!-- Chat Area -->
        <div id="chat-box" class="flex-grow p-4 chat-container space-y-4">
            <!-- Pesan akan muncul di sini -->
        </div>

        <!-- Input Section -->
        <div class="bg-gray-200 p-4 border-t border-gray-300">
            <form id="chat-form" class="flex items-center space-x-2">
                <input type="text" id="user-input" placeholder="Ketik pesan Anda di sini..." class="flex-grow p-3 rounded-full border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300">
                <button type="submit" class="bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.493 12M6 12L3.269 20.874A59.768 59.768 0 0121.493 12M6 12h6" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('chat-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const userInput = document.getElementById('user-input');
            const userMessage = userInput.value.trim();
            if (!userMessage) return;

            const chatBox = document.getElementById('chat-box');

            // Tampilkan pesan pengguna
            const userBubble = document.createElement('div');
            userBubble.className = 'flex justify-end';
            userBubble.innerHTML = `<div class="bg-blue-500 text-white p-3 rounded-l-2xl rounded-tr-2xl shadow-lg max-w-[80%]">${userMessage}</div>`;
            chatBox.appendChild(userBubble);
            chatBox.scrollTop = chatBox.scrollHeight;

            // Buat gelembung chat bot
            const botBubble = document.createElement('div');
            botBubble.className = 'flex justify-start';
            
            // Container untuk pesan bot, yang akan menampung teks dan kode secara terpisah
            const botMessageContainer = document.createElement('div');
            botMessageContainer.className = 'bg-gray-300 text-gray-800 p-3 rounded-r-2xl rounded-tl-2xl shadow-lg max-w-[80%]';
            botBubble.appendChild(botMessageContainer);
            chatBox.appendChild(botBubble);

            // Tampilkan indikator mengetik
            const typingIndicator = document.createElement('div');
            typingIndicator.className = 'typing-indicator flex space-x-1';
            typingIndicator.innerHTML = `
                <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
            `;
            botMessageContainer.appendChild(typingIndicator);

            // Bersihkan input
            userInput.value = '';

            try {
                const response = await fetch('{{ route('chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ prompt: userMessage })
                });

                const result = await response.json();
                const botReply = result.reply;

                // Hapus indikator mengetik
                typingIndicator.remove();
                
                // Pisahkan respons menjadi teks biasa dan blok kode
                const parts = botReply.split('```');

                // Fungsi untuk mengetik bagian teks
                const typeText = (textPart) => {
                    return new Promise(resolve => {
                        const textElement = document.createElement('span');
                        botMessageContainer.appendChild(textElement);
                        let charIndex = 0;
                        const typingEffect = setInterval(() => {
                            if (charIndex < textPart.length) {
                                textElement.textContent += textPart.charAt(charIndex);
                                charIndex++;
                                chatBox.scrollTop = chatBox.scrollHeight;
                            } else {
                                clearInterval(typingEffect);
                                resolve();
                            }
                        }, 20); // Kecepatan mengetik
                    });
                };

                // Fungsi untuk menyisipkan blok kode
                const insertCode = (codePart) => {
                    const preElement = document.createElement('pre');
                    const codeElement = document.createElement('code');
                    codeElement.textContent = codePart.trim();
                    preElement.appendChild(codeElement);
                    botMessageContainer.appendChild(preElement);
                };

                // Proses setiap bagian secara berurutan
                for (let i = 0; i < parts.length; i++) {
                    const part = parts[i];
                    if (i % 2 === 1) { // Bagian dengan indeks ganjil adalah kode
                        insertCode(part);
                    } else { // Bagian dengan indeks genap adalah teks
                        await typeText(part);
                    }
                }

                // Gulir otomatis setelah semua konten selesai dimuat
                chatBox.scrollTop = chatBox.scrollHeight;

            } catch (error) {
                console.error('Error:', error);
                typingIndicator.remove();
                const errorBubble = document.createElement('div');
                errorBubble.className = 'flex justify-start';
                errorBubble.innerHTML = `<div class="bg-red-200 text-red-800 p-3 rounded-r-2xl rounded-tl-2xl shadow-lg max-w-[80%]">Maaf, terjadi kesalahan saat menghubungi AI.</div>`;
                chatBox.appendChild(errorBubble);
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
    </script>
</body>
</html>
