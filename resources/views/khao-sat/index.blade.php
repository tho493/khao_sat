@extends('layouts.home')

@section('title', 'Trang chủ')

@section('content')
    <section class="relative overflow-hidden w-full min-h-screen flex items-center justify-center pt-24 pb-12 lg:pt-20"
        style="background: radial-gradient(circle at top right, #2a76c9, #174a7e);">
        <!-- Font Inter -->
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@700&display=swap');

            .glow-nhe {
                box-shadow: 0 0 15px rgba(250, 204, 21, 0.4);
            }

            .glow-nhe:hover {
                box-shadow: 0 0 25px rgba(250, 204, 21, 0.6);
            }

            /* Responsive 3D Transform cho Dashboard */
            .dashboard-3d {
                transform: perspective(1000px) rotateY(0deg) rotateX(0deg) scale(1);
                transition: transform 0.5s ease;
            }

            @media (min-width: 1024px) {
                .dashboard-3d {
                    transform: perspective(1000px) rotateY(-8deg) rotateX(5deg) scale(1.02);
                }

                .dashboard-3d:hover {
                    transform: perspective(1000px) rotateY(0deg) rotateX(0deg) scale(1.05);
                }
            }

            /* Responsive Typography */
            .hero-title {
                font-family: 'Inter', sans-serif;
                font-weight: 700;
                line-height: 1.2;
                font-size: 38px;
            }

            @media (min-width: 640px) {
                .hero-title {
                    font-size: 48px;
                }
            }

            @media (min-width: 1024px) {
                .hero-title {
                    font-size: 56px;
                }
            }
        </style>

        <!-- BACKGROUND Shapes -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
            <!-- Blur circles -->
            <div
                class="absolute -top-[10%] -right-[5%] w-[300px] h-[300px] sm:w-[500px] sm:h-[500px] rounded-full bg-[#6aa8f7]/30 blur-[80px] sm:blur-[100px]">
            </div>
            <div
                class="absolute top-[50%] -left-[10%] w-[400px] h-[400px] sm:w-[600px] sm:h-[600px] rounded-full bg-white/10 blur-[90px] sm:blur-[120px]">
            </div>

            <!-- Floating shapes -->
            <div class="absolute top-[15%] left-[10%] w-10 h-10 sm:w-12 sm:h-12 bg-white/10 backdrop-blur-md rounded-xl border border-white/20 animate-bounce"
                style="animation-duration: 4s;"></div>
            <div class="absolute bottom-[10%] right-[10%] w-12 h-12 sm:w-16 sm:h-16 bg-white/10 backdrop-blur-md rounded-full border border-white/20 animate-bounce"
                style="animation-duration: 5s;"></div>
            <div class="absolute top-[40%] right-[20%] w-6 h-6 sm:w-8 sm:h-8 bg-white/10 backdrop-blur-md rounded-lg border border-white/20 animate-pulse"
                style="animation-duration: 3s; transform: rotate(45deg);"></div>
        </div>

        <div class="mx-auto px-4 sm:px-6 relative z-10 w-full" style="max-width: 100%; lg:max-w-[90%]">
            <!-- Grid 6/6 layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 sm:gap-10 lg:gap-12 items-center">

                <!-- LEFT CONTENT (col-span-6) -->
                <div
                    class="lg:col-span-6 reveal-banner-text text-center lg:text-left flex flex-col items-center lg:items-start space-y-5 sm:space-y-6 lg:space-y-8">

                    <!-- Heading -->
                    <h1 class="tracking-tight hero-title">
                        <span class="block"
                            style="background: linear-gradient(90deg, #ffffff, #70ccf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Hệ
                            thống khảo sát</span>
                        <span class="block"
                            style="background: linear-gradient(90deg, #ffffff, #70ccf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Trường
                            Đại học Sao Đỏ</span>
                    </h1>

                    <!-- Subtext -->
                    <p
                        class="text-base sm:text-[18px] max-w-xl text-center lg:text-left pb-1 sm:pb-2 font-medium px-2 lg:px-0 text-white/90">
                        Lắng nghe để thay đổi. Trải nghiệm khảo sát thông minh.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4 w-full sm:w-auto px-4 sm:px-0">
                        <!-- Primary Button -->
                        <a href="#survey-list"
                            class="w-full sm:w-auto text-[#1e3a8a] font-bold text-center glow-nhe transition-transform duration-300 hover:-translate-y-1 py-3 sm:py-4 px-6 sm:px-8 bg-gradient-to-r from-yellow-400 to-yellow-300 rounded-xl block"
                            onclick="event.preventDefault(); document.querySelector('#survey-list').scrollIntoView({ behavior: 'smooth' });">
                            Khám phá ngay
                        </a>

                        <!-- Secondary Button -->
                        <a href="https://saodo.edu.vn" target="_blank"
                            class="w-full sm:w-auto text-white font-semibold text-center transition-all duration-300 hover:bg-white/10 py-3 sm:py-4 px-6 sm:px-8 border border-white/30 rounded-xl block">
                            Tìm hiểu thêm
                        </a>
                    </div>


                </div>

                <!-- RIGHT CONTENT (Dashboard mockup / Chart animation) (col-span-6) -->
                <div class="lg:col-span-6 reveal-banner-image mt-8 sm:mt-10 lg:mt-0 relative w-full px-2 sm:px-0">
                    <!-- Dashboard container with 3D rotation effect -->
                    <div class="relative w-full max-w-lg lg:max-w-2xl mx-auto dashboard-3d">

                        <!-- Main Dashboard Frame -->
                        <div
                            class="rounded-xl sm:rounded-2xl overflow-hidden bg-white/10 backdrop-blur-xl border border-white/20 p-2 sm:p-3 lg:p-5 shadow-2xl">

                            <!-- Mac OS style top bar -->
                            <div class="flex gap-1.5 sm:gap-2 mb-2 sm:mb-4 px-1">
                                <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-red-400"></div>
                                <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-yellow-400"></div>
                                <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-green-400"></div>
                            </div>

                            <!-- Dashboard Content Area -->
                            <div
                                class="rounded-lg sm:rounded-xl overflow-hidden bg-white relative aspect-[4/3] shadow-inner flex flex-col">

                                <!-- Top nav mockup -->
                                <div
                                    class="h-10 sm:h-12 border-b border-gray-100 flex items-center px-3 sm:px-4 justify-between bg-slate-50/80">
                                    <div class="flex items-center gap-2 sm:gap-3">
                                        <div
                                            class="w-6 h-6 sm:w-8 sm:h-8 rounded bg-[#1f66b3]/10 flex items-center justify-center">
                                            <i class="bi bi-grid-fill text-[#1f66b3] text-[10px] sm:text-xs"></i>
                                        </div>
                                        <div class="w-16 sm:w-24 h-3 sm:h-4 bg-gray-200 rounded animate-pulse"></div>
                                    </div>
                                    <div class="flex gap-2">
                                        <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-gray-200 hidden sm:block"></div>
                                        <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-[#1f66b3]"></div>
                                    </div>
                                </div>

                                <!-- Main content mockup with Chart Animation -->
                                <div class="flex-1 p-3 sm:p-4 lg:p-6 flex flex-col gap-3 sm:gap-4 lg:gap-6 bg-slate-50">

                                    <!-- Stats Row -->
                                    <div class="grid grid-cols-3 gap-2 sm:gap-3 lg:gap-4">
                                        <div
                                            class="bg-white p-2 sm:p-3 rounded-md sm:rounded-lg shadow-sm border border-gray-100">
                                            <div class="w-6 sm:w-8 h-1.5 sm:h-2 bg-gray-200 rounded mb-2 sm:mb-3"></div>
                                            <div class="w-full h-6 sm:h-8 flex items-end gap-0.5 sm:gap-1">
                                                <div class="w-1/3 bg-[#6aa8f7] rounded-[1px] sm:rounded-sm h-1/2"></div>
                                                <div class="w-1/3 bg-[#2a76c9] rounded-[1px] sm:rounded-sm h-3/4"></div>
                                                <div class="w-1/3 bg-[#1f66b3] rounded-[1px] sm:rounded-sm h-full"></div>
                                            </div>
                                        </div>
                                        <div
                                            class="bg-white p-2 sm:p-3 rounded-md sm:rounded-lg shadow-sm border border-gray-100">
                                            <div class="w-8 sm:w-10 h-1.5 sm:h-2 bg-gray-200 rounded mb-2 sm:mb-3"></div>
                                            <div class="w-10 sm:w-12 h-3 sm:h-4 bg-yellow-400 rounded"></div>
                                        </div>
                                        <div
                                            class="bg-white p-2 sm:p-3 rounded-md sm:rounded-lg shadow-sm border border-gray-100">
                                            <div class="w-6 sm:w-8 h-1.5 sm:h-2 bg-gray-200 rounded mb-2 sm:mb-3"></div>
                                            <div class="w-10 sm:w-14 h-3 sm:h-4 bg-[#6aa8f7] rounded"></div>
                                        </div>
                                    </div>

                                    <!-- Chart Area -->
                                    <div
                                        class="flex-1 bg-white rounded-md sm:rounded-lg shadow-sm border border-gray-100 p-2 sm:p-4 relative flex items-end gap-1.5 sm:gap-2 lg:gap-4 justify-between min-h-[80px] sm:min-h-0 sm:h-32 overflow-hidden">
                                        <div class="absolute inset-x-0 top-1/4 h-[1px] bg-gray-100"></div>
                                        <div class="absolute inset-x-0 top-1/2 h-[1px] bg-gray-100"></div>
                                        <div class="absolute inset-x-0 top-3/4 h-[1px] bg-gray-100"></div>

                                        <!-- Animated Bars -->
                                        <div class="w-full bg-[#6aa8f7] rounded-t-sm animate-[h-grow_3s_ease-out_infinite] origin-bottom"
                                            style="height: 40%"></div>
                                        <div class="w-full bg-[#1f66b3] rounded-t-sm animate-[h-grow_3.2s_ease-out_infinite] origin-bottom"
                                            style="height: 65%"></div>
                                        <div class="w-full bg-yellow-400 rounded-t-sm animate-[h-grow_2.8s_ease-out_infinite] origin-bottom"
                                            style="height: 45%"></div>
                                        <div class="w-full bg-[#2a76c9] rounded-t-sm animate-[h-grow_3.5s_ease-out_infinite] origin-bottom"
                                            style="height: 85%"></div>
                                        <div class="w-full bg-emerald-400 rounded-t-sm animate-[h-grow_3.1s_ease-out_infinite] origin-bottom"
                                            style="height: 55%"></div>
                                        <div class="w-full bg-[#6aa8f7] rounded-t-sm animate-[h-grow_2.9s_ease-out_infinite] origin-bottom"
                                            style="height: 35%"></div>
                                        <div class="w-full bg-[#174a7e] rounded-t-sm animate-[h-grow_3.4s_ease-out_infinite] origin-bottom"
                                            style="height: 75%"></div>
                                    </div>
                                    <style>
                                        @keyframes h-grow {
                                            0% {
                                                transform: scaleY(0.8);
                                            }

                                            50% {
                                                transform: scaleY(1.05);
                                            }

                                            100% {
                                                transform: scaleY(0.8);
                                            }
                                        }
                                    </style>
                                </div>
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
                        @php
                            $now = now();
                            $startDate = $dot->tungay;
                            $endDate = $dot->denngay;
                            
                            $totalSeconds = $startDate->diffInSeconds($endDate);
                            $progress = 100;
                            if ($totalSeconds > 0) {
                                $passedSeconds = $startDate->diffInSeconds($now);
                                $progress = min(100, max(0, ($passedSeconds / $totalSeconds) * 100));
                            }
                            
                            $responsesCount = $dot->responses_count ?? 0;
                            
                            $daysLeft = (int) $now->diffInDays($endDate);
                            if ($daysLeft > 0) {
                                $timeText = "<strong>{$daysLeft}</strong> ngày còn lại";
                            } else {
                                $timeText = "<strong>" . $endDate->diffForHumans($now, null, true) . "</strong> còn lại";
                            }
                            if ($dot->isClosed() || $now->gt($endDate)) {
                                $timeText = '<strong class="text-red-400">Đã kết thúc</strong>';
                                $progress = 100;
                            } elseif ($now->lt($startDate)) {
                                $timeText = '<strong>Chưa bắt đầu</strong>';
                                $progress = 0;
                            }
                        @endphp
                        
                        <a href="{{ route('khao-sat.show', $dot->id) }}" data-survey-id="{{ $dot->id }}"
                            class="group relative rounded-[20px] overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 aspect-[4/5] reveal-survey-card survey-card-link flex flex-col justify-end">
                            
                            <img src="{{ $dot->image }}" alt="{{ $dot->ten_dot }}"
                                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />

                            <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/60 to-black/30 transition-opacity duration-300 group-hover:opacity-90"></div>

                            <div class="relative z-10 w-full h-full p-5 sm:p-6 flex flex-col justify-between">
                                <!-- Top Info -->
                                <div class="flex justify-between items-start w-full">
                                    @if($dot->isClosed() || $now->gt($endDate))
                                        <span class="px-3 py-[4px] bg-red-500/90 backdrop-blur-md text-white text-[10px] font-bold rounded-lg uppercase tracking-widest shadow-sm border border-red-400">Đã kết thúc</span>
                                    @elseif($now->lt($startDate))
                                        <span class="px-3 py-[4px] bg-black/50 backdrop-blur-md text-white text-[10px] font-bold rounded-lg uppercase tracking-widest border border-white/20 shadow-sm">Chưa bắt đầu</span>
                                    @else
                                        <span class="px-3 py-[4px] bg-blue-600/90 backdrop-blur-md text-white text-[10px] font-bold rounded-lg border border-white/20 uppercase tracking-widest shadow-sm">Đang diễn ra</span>
                                    @endif
                                    
                                    <div class="bg-black/40 backdrop-blur-md rounded-lg px-3 py-[4px] border border-white/20 flex items-center gap-1.5 shadow-sm">
                                        <i class="bi bi-calendar3 text-white/80 text-[10px]"></i>
                                        <span class="text-[11px] text-white font-medium tracking-wide">
                                            {{ $endDate->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Bottom Info -->
                                <div class="w-full flex flex-col justify-end mt-auto">
                                    <!-- Title -->
                                    <h3 class="font-bold text-[18px] sm:text-[20px] text-white drop-shadow-lg leading-snug mb-5 line-clamp-3 group-hover:text-blue-300 transition-colors">
                                        {{ $dot->ten_dot }}
                                    </h3>

                                    <!-- Progress Bar -->
                                    <div class="mb-5 flex flex-col justify-end">
                                        <div class="flex justify-between items-end text-xs font-semibold text-white/90 drop-shadow-sm mb-2">
                                            <span>Tiến độ</span>
                                            <span class="text-white drop-shadow-md leading-none">{{ round($progress) }}%</span>
                                        </div>
                                        <div class="w-full bg-white/20 backdrop-blur-sm rounded-full h-[6px] overflow-hidden border border-white/10 shadow-inner">
                                            <div class="bg-gradient-to-r from-[#4185d6] to-[#7fb2f0] h-full rounded-full transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(109,179,250,0.5)]" style="width: {{ $progress }}%"></div>
                                        </div>
                                    </div>

                                    <!-- Tiêu chí người tham gia & thời gian -->
                                    <div class="flex justify-between items-center mb-6 text-sm font-medium text-white/90 drop-shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white border border-white/30 shadow-sm">
                                                <i class="bi bi-people-fill text-[13px]"></i>
                                            </div>
                                            <span><strong class="text-white">{{ $responsesCount }}</strong> người</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-orange-500/30 backdrop-blur-md flex items-center justify-center text-orange-200 border border-orange-300/40 shadow-sm">
                                                <i class="bi bi-hourglass-bottom text-[13px]"></i>
                                            </div>
                                            <span>{!! $timeText !!}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Tham gia Button -->
                                    <div class="block w-full text-center bg-white text-[#2a76c9] hover:bg-[#2a76c9] hover:text-white font-bold py-[12px] rounded-xl transition-all duration-300 shadow-xl border border-transparent hover:border-white survey-action-btn">
                                        Tham gia khảo sát
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
                    const btn = card.querySelector('.survey-action-btn');
                    if (btn) {
                        btn.innerHTML = '<i class="bi bi-check-circle-fill me-1.5 text-sm"></i>Đã khảo sát';
                        btn.className = 'block w-full text-center bg-emerald-500/95 backdrop-blur-md text-white font-bold py-[12px] rounded-xl transition-all duration-300 shadow-lg border border-white/20 hover:bg-emerald-600 survey-action-btn';
                    }
                }
            });
        });
    </script>
@endsection