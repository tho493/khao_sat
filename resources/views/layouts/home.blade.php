<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> @yield('title', "Trang chủ") - Hệ thống khảo sát trực tuyến </title>

    <!-- <script disable-devtool-auto src='https://cdn.jsdelivr.net/npm/disable-devtool'></script> -->


    {{-- Tailwind CSS & Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- CSS for Glassmorphism & Improvements --}}
    <style>
        :root {
            --primary-color: #2a76c9;
            --secondary-color: #1f66b3;
        }

        /* Sử dụng font chữ hiện đại hơn */
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #e8f1fe;
            /* background-image: linear-gradient(to top right, #1f66b3, #2a76c9, #6aa8f7); */
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        header.sticky-header {
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 0.5rem 0;
            background-color: #1f66b3;
            transition: background-color 0.4s ease-in-out, box-shadow 0.4s ease-in-out, backdrop-filter 0.4s ease-in-out;
        }

        header.sticky-header.scrolled {
            background-color: rgba(31, 102, 179, 0.35);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .background-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0));
        }

        .shape1 {
            width: 400px;
            height: 400px;
            top: -150px;
            left: -100px;
        }

        .shape2 {
            width: 300px;
            height: 300px;
            bottom: -100px;
            right: -50px;
        }

        footer {
            background-color: #ffffff;
        }

        #devtools-blocker {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(26, 32, 44, 0.95);
            z-index: 2147483647;

            display: none;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .blocker-content {
            max-width: 600px;
            opacity: 1;
        }

        .blocker-icon {
            font-size: 80px;
            color: #e53e3e;
            animation: pulse 1.5s infinite;
        }

        .blocker-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-top: 20px;
            color: #ffffff;
        }

        .blocker-message {
            font-size: 1.2rem;
            margin-top: 15px;

            color: #e2e8f0;
            opacity: 1;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Chatbot */
        .chatbot-toggler {
            position: fixed;
            bottom: 15px;
            right: 80px;
            width: 60px;
            height: 60px;
            background: var(--primary-color, #2a76c9);
            color: white;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .chatbot-toggler:hover {
            transform: scale(1.1);
        }

        .chatbot-container {
            position: fixed;
            bottom: 100px;
            right: 35px;
            width: 350px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 128px 0 rgba(0, 0, 0, 0.1), 0 32px 64px -48px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            transform: scale(0.5);
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .chatbot-container.show {
            transform: scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        .chatbot-header {
            background: var(--primary-color, #2a76c9);
            color: white;
            padding: 16px;
            text-align: center;
        }

        .chatbot-header h2 {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
        }

        .chatbox {
            height: 350px;
            overflow-y: auto;
            padding: 20px;
        }

        .chat {
            display: flex;
            margin-bottom: 15px;
        }

        .chat p {
            max-width: 75%;
            font-size: 0.95rem;
            padding: 12px;
            border-radius: 10px;
            background: #f2f2f2;
            margin: 0;
            word-wrap: break-word;
        }

        .chat.outgoing {
            justify-content: flex-end;
        }

        .chat.outgoing p {
            background: var(--primary-color, #2a76c9);
            color: white;
        }

        .chat-input {
            display: flex;
            gap: 5px;
            padding: 15px;
            border-top: 1px solid #ddd;
        }

        .chat-input textarea {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            resize: none;
            font-size: 0.95rem;
            max-height: 100px;
        }

        .chat-input button {
            border: none;
            background: none;
            font-size: 1.5rem;
            color: var(--primary-color, #2a76c9);
            cursor: pointer;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-gradient-to-br from-blue-500 to-slate-50 text-slate-800">
    <div id="devtools-blocker">
        <div class="blocker-content">
            <div class="blocker-icon">
                <img src="/image/mim_cry.gif" alt="Lỗi truy cập" loop=infinite>
            </div>
            <h1 class="blocker-title">CÓ BIẾN RỒI!!!</h1>
            <p id="blocker-message" class="blocker-message">
                Đóng DevTools và tải lại trang để tiếp tục.
            </p>
        </div>
    </div>

    {{-- Chatbot Container --}}
    <button class="chatbot-toggler">
        <i class="bi bi-chat-dots-fill"></i>
    </button>

    <div class="chatbot-container">
        <div class="chatbot-header">
            <h2>Trợ lý ảo</h2>
        </div>
        <ul class="chatbox list-unstyled">
            <li class="chat incoming">
                <p>Xin chào 👋<br>Tôi có thể giúp gì cho bạn về các vấn đề thường gặp trong khảo sát?</p>
            </li>
        </ul>
        <div class="chat-input">
            <textarea placeholder="Nhập câu hỏi của bạn..." required></textarea>
            <button id="send-btn"><i class="bi bi-send-fill"></i></button>
        </div>
    </div>

    {{-- Main Content Wrapper --}}
    <div id="main-content">
        <header class="sticky-header">
            <div class="mx-auto px-4" style="max-width: 90%;">
                <div class="flex items-center justify-between py-2">
                    <a href="{{ route('khao-sat.index') }}" class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full bg-white/95 grid place-items-center shadow-md p-1">
                            <img src="/image/logo.png" alt="Logo Trường Đại học Sao Đỏ"
                                class="h-full w-full object-contain">
                        </div>
                        <span class="hidden sm:block text-white font-bold text-lg">Đại học Sao Đỏ</span>
                    </a>
                    <nav class="flex items-center gap-4">
                        <a href="https://saodo.edu.vn/vi/about/Gioi-thieu-ve-truong-Dai-hoc-Sao-Do.html" target="_blank"
                            class="text-white/90 hover:text-white text-sm font-medium transition">GIỚI THIỆU</a>
                        <a href="{{ route('admin.dashboard') }}"
                            class="px-4 py-2 rounded-lg bg-white/20 text-white text-xs font-semibold hover:bg-white/30 transition backdrop-blur-sm"
                            title="Truy cập trang quản trị">
                            <i class="bi bi-shield-lock-fill mr-1"></i> Quản trị
                        </a>
                    </nav>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        @yield('content')

        {{-- Footer --}}
        <footer class="relative text-white pt-16 pb-8 overflow-hidden bg-gradient-to-br from-[#174a7e] to-[#1f66b3]">
            <!-- <div class="absolute inset-0 -z-10"></div> -->

            <div class="absolute -bottom-24 -left-24 w-72 h-72 rounded-full bg-white/5"></div>
            <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/5"></div>

            <div class="mx-auto px-4 grid grid-cols-1 lg:grid-cols-3 gap-12" style="max-width: 90%;">

                <div class="lg:col-span-1">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-14 w-14 rounded-full bg-white/95 grid place-items-center shadow-md p-1">
                            <img src="/image/logo.png" alt="Logo Trường Đại học Sao Đỏ"
                                class="h-full w-full object-contain">
                        </div>
                        <div>
                            <h4 class="font-extrabold text-xl">Trường Đại học Sao Đỏ</h4>
                            <p class="text-white/80 text-sm">Chất lượng - Hợp tác - Phát triển</p>
                        </div>
                    </div>
                    <p class="text-white/70 text-sm mb-6">
                        Hệ thống khảo sát trực tuyến nhằm nâng cao chất lượng đào tạo và dịch vụ, lắng nghe ý kiến đóng
                        góp từ các bên liên quan.
                    </p>
                    <div class="flex items-center gap-4">
                        <a href="https://www.facebook.com/truongdhsaodo" target="_blank"
                            class="text-white/70 hover:text-white transition text-2xl" title="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <!-- <a href="https://www.youtube.com/channel/UCiP2q-gYq8-Y-g-q" target="_blank"
                            class="text-white/70 hover:text-white transition text-2xl" title="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a> -->
                        <a href="mailto:info@saodo.edu.vn" class="text-white/70 hover:text-white transition text-2xl"
                            title="Email">
                            <i class="bi bi-envelope-fill"></i>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <h5 class="font-bold text-lg mb-4 tracking-wider">THÔNG TIN LIÊN HỆ</h5>
                    <div class="text-white/80 space-y-3 text-sm">
                        <p class="flex items-start">
                            <i class="bi bi-geo-alt-fill mr-3 mt-1 flex-shrink-0"></i>
                            <span>Số 76, Nguyễn Thị Duệ, P. Sao Đỏ, TP. Chí Linh, T. Hải Dương</span>
                        </p>
                        <p class="flex items-start">
                            <i class="bi bi-telephone-fill mr-3 mt-1 flex-shrink-0"></i>
                            <span>(0220) 3882 402</span>
                        </p>
                        <p class="flex items-start">
                            <i class="bi bi-globe2 mr-3 mt-1 flex-shrink-0"></i>
                            <a href="https://saodo.edu.vn" class="hover:text-white hover:underline transition"
                                target="_blank">https://saodo.edu.vn</a>
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <h5 class="font-bold text-lg mb-4 tracking-wider">BẢN ĐỒ</h5>
                    <div class="w-full h-full min-h-[200px] rounded-lg overflow-hidden shadow-lg">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3725.321049289255!2d106.4259737153359!3d20.97960339463567!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31350b0b8c2c8f6b%3A0x52c286a2e24f46e5!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBTYW8gxJDhu48!5e0!3m2!1svi!2s!4v1672322045678!5m2!1svi!2s"
                            class="w-full h-full border-0" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade" title="Bản đồ Trường Đại học Sao Đỏ"></iframe>
                    </div>
                </div>
            </div>

            <div class="mt-12 border-t border-white/20 pt-6 text-center text-white/60 text-sm">
                © {{ date('Y') }} Trường Đại học Sao Đỏ · Hệ thống khảo sát trực tuyến.
            </div>

            <button id="back-to-top" title="Cuộn lên đầu trang" class="hidden fixed bottom-5 right-5 w-12 h-12 rounded-full bg-blue-300/40 backdrop-blur-sm text-white text-2xl
                   hover:bg-white/30 focus:outline-none transition-all duration-300">
                <i class="bi bi-arrow-up-short"></i>
            </button>
        </footer>
    </div>

    <script> // nút lướt lên đầu
        const backToTopButton = document.getElementById('back-to-top');
        if (backToTopButton) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) { // Hiển thị nút khi cuộn xuống 300px
                    backToTopButton.classList.remove('hidden');
                } else {
                    backToTopButton.classList.add('hidden');
                }
            });

            backToTopButton.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    </script>
    <script>
        const header = document.querySelector('header.sticky-header');
        if (header) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        }
    </script>
    <!-- <script src="/js/protected.js"></script> -->
    <script src="https://unpkg.com/scrollreveal"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sr = ScrollReveal({
                origin: 'bottom',    // Xuất hiện từ phía dưới
                distance: '40px',    // Khoảng cách di chuyển
                duration: 800,       // Thời gian hiệu ứng (ms)
                delay: 200,          // Độ trễ trước khi bắt đầu (ms)
                opacity: 0,          // Bắt đầu với trạng thái trong suốt
                scale: 1,            // Không thay đổi kích thước
                easing: 'cubic-bezier(0.5, 0, 0, 1)',
                reset: false         // Chạy hiệu ứng lại
            });

            // Hiệu ứng cho Banner
            sr.reveal('.reveal-banner-text', { origin: 'left', distance: '40px', duration: 600 });
            sr.reveal('.reveal-banner-image', { origin: 'right', distance: '40px', duration: 600 });

            // Hiệu ứng cho tiêu đề section khảo sát
            sr.reveal('.reveal-section-title', { duration: 600, scale: 0.95 });

            // Hiệu ứng cho các card khảo sát (xuất hiện lần lượt)
            sr.reveal('.reveal-survey-card', { interval: 100 });
        });
    </script>

    <script>
        $(document).ready(function () {
            const chatbotToggler = $('.chatbot-toggler');
            const chatbotContainer = $('.chatbot-container');
            const chatInput = $('.chat-input textarea');
            const sendChatBtn = $('#send-btn');
            const chatbox = $('.chatbox');

            chatbotToggler.on('click', () => chatbotContainer.toggleClass('show'));

            const createChatLi = (message, className) => {
                const chatLi = $('<li>').addClass(`chat ${className}`);
                chatLi.append($('<p>').html(message));
                return chatLi;
            }

            const scrollToBottom = () => {
                chatbox.scrollTop(chatbox[0].scrollHeight);
            };

            const generateResponse = (userMessage) => {
                const API_URL = "{{ route('api.ask') }}";

                const surveyForm = $('#formKhaoSat');
                @php
                    $surveyIdForJs = null;
                    if (isset($dotKhaoSat) && $dotKhaoSat) {
                        $surveyIdForJs = $dotKhaoSat->id;
                    }
                @endphp
                const surveyId = surveyForm.length ? "{{ $surveyIdForJs }}" : null;

                const requestData = {
                    _token: '{{ csrf_token() }}',
                    message: userMessage
                };

                if (surveyId) {
                    requestData.survey_id = surveyId;
                }

                const thinkingLi = createChatLi("...", "incoming");
                chatbox.append(thinkingLi);
                scrollToBottom();

                $.ajax({
                    url: API_URL,
                    method: 'POST',
                    data: requestData,
                    success: function (response) {
                        if (response.success) {
                            if (response.type === 'action') {
                                thinkingLi.remove();
                                handleAiAction(response.data);
                            } else {
                                thinkingLi.find("p").html(response.answer);
                            }
                        } else {
                            thinkingLi.find("p").html(response.answer || "Có lỗi xảy ra.");
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log(xhr.responseText);
                        thinkingLi.find("p").html("Ôi! Server hỗ trợ có vẻ đang lỗi rồi. Bạn thông cảm mình chưa nói chuyện với nhau được đâu nhé.");
                    }
                });
            }

            function handleAiAction(actionData) {
                console.log("Executing AI Action:", actionData);
                let feedbackMessage = "Đã hiểu!";

                switch (actionData.action) {
                    case 'fill_input':
                        const inputElement = $(actionData.selector);
                        if (inputElement.length) {
                            inputElement.val(actionData.value).trigger('change');
                            inputElement.addClass('flash-effect-input');
                            setTimeout(() => inputElement.removeClass('flash-effect-input'), 4000);
                            feedbackMessage = `Đã điền '${actionData.value}' giúp bạn.`;
                        } else {
                            feedbackMessage = `Xin lỗi, tôi không tìm thấy ô để điền thông tin đó.`;
                        }
                        break;

                    case 'scroll_to_question':
                        const questionCard = $(`.question-card:eq(${actionData.question_number - 1})`);
                        if (questionCard.length) {
                            questionCard[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                            // Thêm hiệu ứng
                            questionCard.addClass('flash-effect');
                            setTimeout(() => questionCard.removeClass('flash-effect'), 4000);
                            feedbackMessage = `Ok, đã chuyển đến câu ${actionData.question_number}.`;
                        } else {
                            feedbackMessage = `Xin lỗi, tôi không tìm thấy câu hỏi số ${actionData.question_number}.`;
                        }
                        break;

                    case 'check_missing':
                        const missingRequired = [];
                        $('#formKhaoSat [required]').each(function () {
                            let isMissing = false;
                            const name = $(this).attr('name');
                            if ($(this).is(':radio')) {
                                if ($(`input[name="${name}"]:checked`).length === 0) isMissing = true;
                            } else if (!$(this).val().trim()) {
                                isMissing = true;
                            }

                            if (isMissing) {
                                const label = $(this).closest('.p-6, .grid').find('label').first().text().replace('*', '').trim();
                                if (label && !missingRequired.includes(label)) {
                                    missingRequired.push(label);
                                }
                            }
                        });

                        if (missingRequired.length > 0) {
                            feedbackMessage = 'Bạn còn thiếu các câu bắt buộc sau:<ul class="list-disc ps-4 mt-2">';
                            missingRequired.forEach(label => feedbackMessage += `<li>${label}</li>`);
                            feedbackMessage += '</ul>';
                        } else {
                            feedbackMessage = 'Tuyệt vời! Bạn đã trả lời tất cả các câu hỏi bắt buộc rồi.';
                        }
                        break;
                }

                // Hiển thị tin nhắn khi hoàn thành
                const botMessageLi = createChatLi(feedbackMessage, "incoming");
                chatbox.append(botMessageLi);
                chatbox.scrollTop(chatbox[0].scrollHeight);
            }

            const handleChat = () => {
                const userMessage = chatInput.val().trim();
                if (!userMessage) return;

                chatInput.val("");
                chatbox.append(createChatLi(userMessage, "outgoing"));
                chatbox.scrollTop(chatbox[0].scrollHeight);

                setTimeout(() => generateResponse(userMessage), 600);
            }

            sendChatBtn.on('click', handleChat);
            chatInput.on('keydown', (e) => {
                if (e.key === "Enter" && !e.shiftKey) {
                    e.preventDefault();
                    handleChat();
                }
            });
        });
    </script>

    @stack('scripts')

</body>

</html>