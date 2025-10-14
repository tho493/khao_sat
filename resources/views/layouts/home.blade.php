<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> @yield('title', "Trang chủ") - Hệ thống khảo sát trực tuyến </title>
    <meta name="description"
        content="Hệ thống khảo sát trực tuyến - Nền tảng khảo sát hiện đại, bảo mật và dễ sử dụng." />
    <meta name="keywords" content="khảo sát, survey, trực tuyến, online, hệ thống, khảo sát trực tuyến" />
    <meta name="author" content="Hệ thống khảo sát trực tuyến" />
    <meta name="robots" content="index, follow" />
    <meta property="og:title" content="@yield('title', 'Trang chủ') - Hệ thống khảo sát trực tuyến" />
    <meta property="og:description"
        content="Hệ thống khảo sát trực tuyến - Nền tảng khảo sát hiện đại, bảo mật và dễ sử dụng." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:image" content="/image/logo.png" />
    <link rel="stylesheet" href="/css/splash-screen.css">

    <!-- <script disable-devtool-auto src='https://cdn.jsdelivr.net/npm/disable-devtool'></script> -->

    {{-- CSS for Glassmorphism & Improvements --}}
    <link rel="stylesheet" href="/css/home.css">

    <!-- CSS NProgress -->
    <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />

    {{-- CSS SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    {{-- Tailwind CSS & Scripts --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('styles')
</head>

<body>
    <!-- Splash (overlay) -->
    @include('layouts.splash-screen')
    <script src="/js/splash-screen.js"></script>

    <div class="bg-gradient-to-br from-blue-500 to-slate-50 text-slate-800">
        {{-- Main Content Wrapper --}}
        <div id="main-content" style="visibility: hidden;">

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
                            <a href="https://saodo.edu.vn/vi/about/Gioi-thieu-ve-truong-Dai-hoc-Sao-Do.html"
                                target="_blank"
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
            <footer
                class="relative text-white pt-16 pb-8 overflow-hidden bg-gradient-to-br from-[#174a7e] to-[#1f66b3]">
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
                                <p class="text-white/80 text-sm">Chất lượng toàn diện - Hợp tác sâu rộng - Phát triển bền vững</p>
                            </div>
                        </div>
                        <p class="text-white/70 text-sm mb-6">
                            Hệ thống khảo sát trực tuyến nhằm nâng cao chất lượng đào tạo và dịch vụ, lắng nghe ý kiến
                            đóng
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
                            <a href="mailto:info@saodo.edu.vn"
                                class="text-white/70 hover:text-white transition text-2xl" title="Email">
                                <i class="bi bi-envelope-fill"></i>
                            </a>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <h5 class="font-bold text-lg mb-4 tracking-wider">THÔNG TIN LIÊN HỆ</h5>
                        <div class="text-white/80 space-y-3 text-sm">
                            <p class="flex items-start">
                                <i class="bi bi-geo-alt-fill mr-3 mt-1 flex-shrink-0"></i>
                                <span>Số 76, Nguyễn Thị Duệ, Thái Học 2, phường Chu Văn An, thành phố Hải Phòng.</span>
                            </p>
                            <p class="flex items-start">
                                <i class="bi bi-telephone-fill mr-3 mt-1 flex-shrink-0"></i>
                                <span>Điện thoại: (0220) 3882 402</span>
                            </p>
                            <p class="flex items-start">
                                <i class="bi bi-printer-fill mr-3 mt-1 flex-shrink-0"></i>
                                <span>Fax: (0220) 3882 921</span>
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
                        <div class="rounded-lg overflow-hidden shadow-lg">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3722.080211255413!2d106.39125117529709!3d21.10936808500497!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31357909df4b3bff%3A0xd8784721e55d91ca!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBTYW8gxJDhu48!5e0!3m2!1svi!2s!4v1757063624491!5m2!1svi!2s"
                                class="w-full h-full" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade" title="Bản đồ vị trí Trường Đại học Sao Đỏ">
                            </iframe>
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
    </div>
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
    <script>
        NProgress.start();

        window.addEventListener('load', function () {
            NProgress.done();
        });
        document.addEventListener('ajax:send', () => NProgress.start());
        document.addEventListener('ajax:complete', () => NProgress.done());
        if (window.jQuery) {
            $(document).on('ajaxStart', () => NProgress.start());
            $(document).on('ajaxStop', () => NProgress.done());
        }
        
        // nút lướt lên đầu
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

        $(document).ready(function () {
            const chatbotToggler = $('.chatbot-toggler');
            const chatbotContainer = $('.chatbot-container');
            const chatInput = $('.chat-input textarea');
            const sendChatBtn = $('#send-btn');
            const chatbox = $('.chatbox');
            const API_URL = "/api/ask";

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

            sendChatBtn.on('click', function (e) {
                e.preventDefault();
                const userMessage = chatInput.val().trim();
                if (userMessage) {
                    const userLi = createChatLi(userMessage, "outgoing");
                    chatbox.append(userLi);
                    scrollToBottom();
                    chatInput.val('');
                    generateResponse(userMessage);
                }
            });

            // Gửi khi nhấn Enter (không phải Shift+Enter)
            chatInput.on('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendChatBtn.click();
                }
            });

            function handleAiAction(actionData) {
                console.log("Executing AI Action:", actionData);
                let feedbackMessage = null; // Mặc định không có phản hồi nếu hành động thành công
                let error = false;

                if (!actionData || !actionData.action) {
                    console.error("Dữ liệu hành động không hợp lệ:", actionData);
                    return;
                }

                let targetElementContainer = null;
                if (actionData.selector) {
                    targetElementContainer = $(actionData.selector).first().closest('.glass-effect, .question-card');
                }

                switch (actionData.action) {
                    case 'show_message':
                        feedbackMessage = actionData.message;
                        break;
                    case 'fill_text': {
                        const element = $(actionData.selector);
                        if (element.length) {
                            element.val(actionData.value).trigger('change');
                            feedbackMessage = `Đã điền "<strong>${actionData.value}</strong>" giúp bạn.`;
                        } else {
                            error = true;
                            feedbackMessage = `Lỗi: Không tìm thấy ô nhập liệu với selector: ${actionData.selector}`;
                        }
                        break;
                    }

                    case 'select_single': {
                        const selector = `${actionData.selector}[value="${actionData.value}"]`;
                        const radioElement = $(selector);

                        if (radioElement.length) {
                            radioElement.prop('checked', true).trigger('change');
                            feedbackMessage = `Đã chọn giúp bạn.`;
                        } else {
                            error = true;
                            feedbackMessage = `Lỗi: Không tìm thấy lựa chọn với selector: ${selector}`;
                        }
                        break;
                    }
                    
                    case 'select_multiple': {
                        const checkboxGroup = $(actionData.selector);
                        if (checkboxGroup.length) {
                            const values = Array.isArray(actionData.values) ? actionData.values : [actionData.values];
                            
                            checkboxGroup.each(function() {
                                if (!values.includes($(this).val())) {
                                    $(this).prop('checked', false);
                                }
                            });

                            values.forEach(val => {
                                checkboxGroup.filter(`[value="${val}"]`).prop('checked', true);
                            });

                            checkboxGroup.first().trigger('change');
                            feedbackMessage = `Đã chọn các phương án giúp bạn.`;
                        } else {
                            error = true;
                            feedbackMessage = `Lỗi: Không tìm thấy nhóm lựa chọn với selector: ${actionData.selector}`;
                        }
                        break;
                    }
                        
                    case 'scroll_to_question': {
                        const qNumber = parseInt(actionData.question_number);
                        if (isNaN(qNumber) || qNumber < 1) {
                            feedbackMessage = `Số câu hỏi không hợp lệ.`;
                            break;
                        }
                        const questionCard = $(`.question-card:eq(${qNumber - 1})`);
                        if (questionCard.length) {
                            targetElementContainer = questionCard;
                            questionCard[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                            feedbackMessage = `Ok, đã chuyển đến câu hỏi số <strong>${qNumber}</strong>.`;
                        } else {
                            error = true;
                            feedbackMessage = `Lỗi: Không tìm thấy câu hỏi số ${qNumber}.`;
                        }
                        break;
                    }

                    case 'check_missing': {
                        const missingRequired = [];
                        $('#formKhaoSat [required]').each(function() {
                            let isMissing = false;
                            const name = $(this).attr('name');
                            if ($(this).is(':radio')) {
                                if ($(`input[name="${name}"]:checked`).length === 0) isMissing = true;
                            } else if (!$(this).val() || $(this).val().trim() === '') {
                                isMissing = true;
                            }
                            if (isMissing) {
                                const label = $(this).closest('.p-6, .grid, .mb-4').find('label').first().text().replace('*', '').trim();
                                if (label && !missingRequired.includes(label)) {
                                    missingRequired.push(label);
                                }
                            }
                        });

                        if (missingRequired.length > 0) {
                            feedbackMessage = 'Bạn còn thiếu các câu bắt buộc sau:<ul class="list-disc ps-4 mt-2 text-start">';
                            missingRequired.forEach(label => feedbackMessage += `<li>${label}</li>`);
                            feedbackMessage += '</ul>';
                        } else {
                            feedbackMessage = 'Tuyệt vời! Bạn đã trả lời tất cả các câu hỏi bắt buộc.';
                        }
                        break;
                    }
                }

                if (!error && targetElementContainer && targetElementContainer.length) {
                    targetElementContainer.addClass('flash-effect');
                    setTimeout(() => targetElementContainer.removeClass('flash-effect'), 2000);
                }

                if (feedbackMessage) {
                    const botMessageLi = createChatLi(feedbackMessage, "incoming");
                    chatbox.append(botMessageLi);
                    scrollToBottom();
                }
            }
        });
    </script>

    @stack('scripts')

</body>

</html>