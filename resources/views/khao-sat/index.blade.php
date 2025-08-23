@extends('layouts.home')

@section('title', 'Trang chủ')

@section('content')
    {{-- Banner --}}
    <section class="relative overflow-hidden bg-gradient-to-r from-[#1f66b3] via-[#2a76c9] to-[#6aa8f7]">
        <div class="mx-auto container-narrow px-4 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center py-10 md:py-12">
                <div class="order-2 md:order-1 text-center md:text-left">
                    <h1 class="text-white drop-shadow text-2xl md:text-4xl font-extrabold leading-tight">
                        HỆ THỐNG KHẢO SÁT TRỰC&nbsp;TUYẾN
                    </h1>

                    <p class="text-white text-lg md:text-2xl font-semibold mt-2">
                        TRƯỜNG ĐẠI HỌC SAO&nbsp;ĐỎ
                    </p>
                </div>
                <div class="order-1 md:order-2">
                    {{-- School image placeholder --}}
                    <div class="bg-white/95 rounded-xl shadow-soft p-3">
                        <div class="aspect-[4/3] w-full bg-slate-100 rounded-lg grid place-items-center">
                            <img src="image/img_sdu.jpg" alt="Hình ảnh trường Đại học Sao Đỏ"
                                class="w-full h-72 md:h-96 object-cover object-center">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="absolute -right-24 -top-24 w-[420px] h-[420px] md:w-[520px] md:h-[520px] rounded-full bg-white/10 border border-white/30">
        </div>
    </section>

    <section class="mx-auto container-narrow px-4 py-10 md:py-12">
        <h2 class="text-center text-[#d83b44] text-xl md:text-2xl font-extrabold tracking-wide mb-6">
            CÁC KHẢO SÁT ĐANG DIỄN RA
        </h2>

        @if(isset($dotKhaoSats) && count($dotKhaoSats) > 0)
            <div class="mt-8 grid gap-6 md:gap-8 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($dotKhaoSats as $dot)
                    <a href="{{ route('khao-sat.show', $dot->id) }}"
                        class="group relative rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 ease-in-out h-96">">

                        <img src="{{ $dot->image }}" alt="{{ $dot->ten_dot }}"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />

                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-transparent"></div>

                        <div class="relative p-6 flex flex-col h-full text-white">

                            <div class="mt-auto">
                                <div class="flex justify-between items-start gap-3 mb-2">

                                    <h3 class="font-bold text-xl md:text-2xl leading-tight">
                                        {{ $dot->ten_dot }}
                                    </h3>
                                    <span
                                        class="inline-block bg-red-600/90 text-white text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0 mt-1">
                                        {{ $dot->mauKhaoSat->doiTuong->ten_doituong ?? 'Khảo sát' }}
                                    </span>

                                </div>

                                <div class="flex items-center text-sm text-gray-200 opacity-90 mb-4">
                                    {{-- Icon Lịch --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 flex-shrink-0" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                            clip-rule="evenodd" />
                                    </svg>

                                    {{-- Span chứa nội dung động --}}
                                    <span>
                                        @php
        $now = now();
        $startDate = \Carbon\Carbon::parse($dot->tungay)->startOfDay();
        $endDate = \Carbon\Carbon::parse($dot->denngay)->endOfDay();
        // diffInDays trả về số ngày tròn, không cần round()
        $daysRemaining = round($now->diffInDays($endDate, false), 1); 
                                        @endphp

                                        @if($dot->isClosed())
                                            {{-- Ưu tiên hiển thị trạng thái đã đóng trước --}}
                                            <span class="font-semibold text-gray-400">Đã kết thúc vào
                                                {{ $endDate->format('d/m/Y') }}</span>

                                        @elseif($now->lt($startDate))
                                            {{-- Trường hợp 1: Sắp diễn ra --}}
                                            <span class="font-semibold text-cyan-300">
                                                Bắt đầu sau {{ $startDate->diffForHumans(null, true, true) }}
                                                ({{ $startDate->format('d/m/Y') }})
                                            </span>

                                        @elseif($now->between($startDate, $endDate))
                                            {{-- Trường hợp 2: Đang diễn ra --}}
                                            @if($daysRemaining >= 1)
                                                <span class="font-semibold text-yellow-300">
                                                    Còn lại {{ $daysRemaining }} ngày
                                                </span>
                                            @else
                                                <span class="font-semibold text-red-400">
                                                    Hôm nay là ngày cuối!
                                                </span>
                                            @endif
                                            <span class="text-gray-400 text-xs ml-2">(Hạn cuối: {{ $endDate->format('d/m/Y') }})</span>

                                        @else
                                            {{-- Trường hợp 3: Đã hết hạn tự nhiên --}}
                                            <span class="font-semibold text-gray-400">Đã kết thúc</span>
                                        @endif
                                    </span>
                                </div>

                                @if($dot->mota)
                                    <p class="text-gray-300 text-sm mb-4 line-clamp-2">{{ $dot->mota }}</p>
                                @endif
                            </div>

                        </div>
                    </a>
                @empty
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-12">
                        <p class="text-slate-500">Hiện tại không có đợt khảo sát nào đang diễn ra.</p>
                    </div>
                @endforelse
            </div>
        @else
            <div class="text-center text-slate-500 py-12">
                Hiện tại chưa có khảo sát nào đang diễn ra.
            </div>
        @endif
    </section>
@endsection