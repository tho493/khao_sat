@extends('layouts.home')

@section('title', 'Trang chủ')

@section('content')
    <section class="relative overflow-hidden bg-gradient-to-r from-[#1f66b3] via-[#2a76c9] to-[#6aa8f7]">
        <div class="absolute inset-0 z-0">
            <div class="absolute -right-24 -top-24 w-[420px] h-[420px] md:w-[520px] md:h-[520px] rounded-full bg-white/10">
            </div>
        </div>

        <div class="mx-auto px-4 relative z-10" style="max-width: 90%;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center py-12 md:py-16">
                <div class="order-2 md:order-1 text-center md:text-left reveal-banner-text">
                    <h1
                        class="text-white drop-shadow-lg text-3xl md:text-5xl font-extrabold leading-tight mb-3 tracking-wider"  style="line-height: 1.3;">
                        HỆ THỐNG KHẢO SÁT TRỰC TUYẾN
                    </h1>
                    <p class="text-white/90 text-xl md:text-2xl font-semibold">
                        TRƯỜNG ĐẠI HỌC SAO&nbsp;ĐỎ
                    </p>
                </div>
                <div class="order-1 md:order-2 reveal-banner-image">
                    <div class="glass-effect p-3">
                        <div class="aspect-[4/3] w-full bg-slate-100 rounded-lg overflow-hidden">
                            <img src="{{ asset('image/img_sdu.jpg') }}" alt="Hình ảnh trường Đại học Sao Đỏ"
                                class="w-full h-full object-cover object-center">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="relative overflow-hidden py-16 md:py-20">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-300 to-slate-50 -z-10"></div>

        <div class="mx-auto px-4" style="max-width: 90%;">
            <div class="text-center mb-12 reveal-section-title">
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-800 tracking-wide">
                    Các Khảo sát Đang diễn ra
                </h2>
                <p class="mt-3 text-lg text-slate-500 max-w-2xl mx-auto">
                    Hãy chọn khảo sát phù hợp để chia sẻ ý kiến và đóng góp của bạn.
                </p>
            </div>

            @if(isset($dotKhaoSats) && $dotKhaoSats->isNotEmpty())
                <div class="grid gap-8 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($dotKhaoSats as $dot)
                        <a href="{{ route('khao-sat.show', $dot->id) }}"
                            class="group relative rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 ease-in-out aspect-[4/5] reveal-survey-card">

                            <img src="{{ $dot->image }}" alt="{{ $dot->ten_dot }}"
                                class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />

                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/40 to-transparent"></div>

                            <div class="relative p-6 flex flex-col h-full text-white justify-end">
                                <div class="bg-white/10 backdrop-blur-xs rounded-xl p-4 border border-white/20 shadow-soft">
                                    <div class="flex justify-between items-start gap-3 mb-2">
                                        <h3 class="font-bold text-xl md:text-xl leading-tight">
                                            {{ Str::limit($dot->ten_dot, 50) }}
                                        </h3>
                                        <!-- <span
                                            class="inline-block bg-red-600/60 text-white text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0 mt-1">
                                            {{-- $dot->mauKhaoSat->ten_mau ?? 'Khảo sát' --}} Làm khảo sát
                                        </span> -->
                                    </div>

                                    <div class="flex items-center text-sm text-gray-200 opacity-90">
                                        <i class="bi bi-hourglass-split me-2"></i>
                                        <span>
                                            @php
        $now = now();
        $startDate = $dot->tungay;
        $endDate = $dot->denngay;
                                            @endphp
                                            @if($dot->isClosed())
                                                <span class="font-semibold text-gray-400">
                                                    Đợt khảo sát này đã kết thúc
                                                </span>
                                            @elseif($now->lt($startDate))
                                                <span class="font-semibold text-cyan-300">
                                                    {{ $startDate->diffForHumans(now(), null, true, 2) }} sẽ bắt đầu
                                                </span>
                                            @elseif($now->between($startDate, $endDate))
                                                <span class="font-semibold text-yellow-300">
                                                    {{ $endDate->diffForHumans(now(), null, true, 2) }} sẽ kết thúc
                                                </span>
                                            @else
                                                <span class="font-semibold text-red-400">
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
                <div class="text-center text-slate-500 py-16 glass-effect">
                    <i class="bi bi-cloud-drizzle text-6xl text-slate-400 mb-4"></i>
                    <h3 class="text-2xl font-semibold text-slate-700">Không có khảo sát nào.</h3>
                    <p class="mt-2">Hiện tại không có đợt khảo sát nào đang diễn ra. Vui lòng quay lại sau.</p>
                </div>
            @endif
    </section>
@endsection