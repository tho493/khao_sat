@extends('layouts.home')

@section('title', 'Trang chủ')

@section('content')
    <section class="relative overflow-hidden bg-gradient-to-r from-[#1f66b3] via-[#2a76c9] to-[#6aa8f7]">
        <div class="absolute inset-0 z-0">
            <div class="absolute -right-24 -top-24 w-[420px] h-[420px] md:w-[520px] md:h-[520px] rounded-full bg-white/10">
            </div>
        </div>

        <div class="mx-auto px-3 sm:px-4 relative z-10" style="max-width: 90%;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8 items-center py-10 sm:py-12 md:py-16">
                <div class="order-2 md:order-1 text-center md:text-left reveal-banner-text">
                    <h1 class="text-white drop-shadow-lg text-2xl sm:text-3xl md:text-5xl font-extrabold leading-tight mb-2 sm:mb-3 tracking-wider"
                        style="line-height: 1.3;">
                        HỆ THỐNG KHẢO SÁT TRỰC TUYẾN
                    </h1>
                    <p class="text-white/90 text-lg sm:text-xl md:text-2xl font-semibold">
                        TRƯỜNG ĐẠI HỌC SAO&nbsp;ĐỎ
                    </p>
                    <div class="mt-6 flex justify-center md:justify-start">
                        <a href="#survey-list"
                            class="inline-block px-7 py-3 rounded-full bg-yellow-400 hover:bg-yellow-300 text-blue-900 font-bold text-lg shadow-lg shadow-yellow-200/40 transition transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 animate-bounce-slow"
                            onclick="event.preventDefault(); document.querySelector('#survey-list').scrollIntoView({ behavior: 'smooth' });">
                            <i class="bi bi-list-stars me-2 text-xl align-middle"></i>
                            Xem danh sách khảo sát
                        </a>
                    </div>
                </div>
                <div class="order-1 md:order-2 reveal-banner-image">
                    <div class="relative">
                        <div class="absolute inset-0 rounded-2xl bg-white/30 backdrop-blur-xl ring-1 ring-white/30 shadow-2xl z-0"
                            style="box-shadow:0 16px 40px 0 rgba(51,97,201,0.24),0 2.5px 22px 0 rgba(72,182,236,0.18);">
                        </div>
                        <div class="relative glass-effect p-2 sm:p-3 rounded-2xl z-10"
                            style="box-shadow: 0 10px 35px 0 rgba(50,78,135,0.25), 0 3px 12px 0 rgba(124,181,244,0.15);">
                            <div
                                class="aspect-[4/3] w-full bg-slate-100 rounded-xl overflow-hidden transition-shadow shadow-2xl shadow-blue-900/40 hover:shadow-[0_25px_60px_0_rgba(44,140,255,0.30)]">
                                <img src="{{ asset('image/img_sdu.jpg') }}" alt="Hình ảnh trường Đại học Sao Đỏ"
                                    class="w-full h-full object-cover object-center" draggable="false">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="relative overflow-hidden py-12 sm:py-16 md:py-20" id="survey-list">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-300 to-slate-50 -z-10"></div>

        <div class="mx-auto px-3 sm:px-4" style="max-width: 90%;">
            <div class="text-center mb-8 sm:mb-12 reveal-section-title">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-800 tracking-wide">
                    Các Khảo sát Đang diễn ra
                </h2>
                <p class="mt-2 sm:mt-3 text-base sm:text-lg text-slate-500 max-w-2xl mx-auto px-2">
                    Hãy chọn khảo sát phù hợp để chia sẻ ý kiến và đóng góp của bạn.
                </p>
            </div>

            @if(isset($dotKhaoSats) && $dotKhaoSats->isNotEmpty())
                <div class="grid gap-6 sm:gap-8 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($dotKhaoSats as $dot)
                        <a href="{{ route('khao-sat.show', $dot->id) }}" data-survey-id="{{ $dot->id }}"
                            class="group relative rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 ease-in-out aspect-[4/5] reveal-survey-card survey-card-link">

                            <img src="{{ $dot->image }}" alt="{{ $dot->ten_dot }}"
                                class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />

                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/40 to-transparent"></div>

                            <div class="relative p-4 sm:p-6 flex flex-col h-full text-white justify-end">
                                <div
                                    class="relative z-10 bg-white/20 backdrop-blur-sm rounded-xl p-3 sm:p-4 border border-white/30 shadow-lg">
                                    <div class="flex justify-between items-start gap-2 sm:gap-3 mb-2">
                                        <h3
                                            class="font-extrabold text-2xl sm:text-2xl leading-tight drop-shadow-xl text-white tracking-wider">
                                            {{ Str::limit($dot->ten_dot, 50) }}
                                        </h3>
                                    </div>

                                    <div
                                        class="flex items-center text-xs sm:text-sm text-gray-100 opacity-100 font-semibold drop-shadow">
                                        <i class="bi bi-hourglass-split me-2 flex-shrink-0 text-lg"></i>
                                        <span class="break-words">
                                            @php
                                                $now = now();
                                                $startDate = $dot->tungay;
                                                $endDate = $dot->denngay;
                                            @endphp
                                            @if($dot->isClosed())
                                                <span class="font-semibold text-gray-300 drop-shadow">
                                                    Đợt khảo sát này đã kết thúc
                                                </span>
                                            @elseif($now->lt($startDate))
                                                <span class="font-semibold text-cyan-300 drop-shadow">
                                                    {{ $startDate->diffForHumans(now(), null, true, 2) }} sẽ bắt đầu
                                                </span>
                                            @elseif($now->between($startDate, $endDate))
                                                <span class="font-semibold text-yellow-300 drop-shadow">
                                                    {{ $endDate->diffForHumans(now(), null, true, 2) }} sẽ kết thúc
                                                </span>
                                            @else
                                                <span class="font-semibold text-red-400 drop-shadow">
                                                    {{ $endDate->diffForHumans(now(), null, true, 2) }} đã kết thúc
                                                </span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center text-slate-500 py-12 sm:py-16 glass-effect mx-2">
                    <i class="bi bi-cloud-drizzle text-4xl sm:text-6xl text-slate-400 mb-3 sm:mb-4"></i>
                    <h3 class="text-xl sm:text-2xl font-semibold text-slate-700">Không có khảo sát nào.</h3>
                    <p class="mt-2 text-sm sm:text-base px-2">Hiện tại không có đợt khảo sát nào đang diễn ra. Vui lòng quay lại
                        sau.</p>
                </div>
            @endif
    </section>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const surveyCards = document.querySelectorAll('.survey-card-link');

            surveyCards.forEach(card => {
                const surveyId = card.getAttribute('data-survey-id');
                const completedKey = `survey_completed_${surveyId}`;
                const completedData = localStorage.getItem(completedKey);

                if (completedData) {
                    // Create Badge
                    const badge = document.createElement('div');
                    badge.className = 'absolute top-3 right-3 z-20 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg border border-green-400 flex items-center gap-1 animate-fade-in-up';
                    badge.innerHTML = '<i class="bi bi-check-circle-fill"></i> Đã khảo sát';

                    // Add visual completed state to card
                    card.appendChild(badge);

                    // Optional: Add grayscale or dimming effect to image
                    const img = card.querySelector('img');
                    if (img) {
                        // img.style.filter = 'grayscale(100%)';
                    }
                }
            });
        });
    </script>
@endsection