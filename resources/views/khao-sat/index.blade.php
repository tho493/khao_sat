@extends('layouts.home')

@section('title', 'Trang chủ')

@section('content')
    {{-- Banner --}}
    <section class="relative overflow-hidden bg-gradient-to-r from-[#1f66b3] via-[#2a76c9] to-[#6aa8f7]">
        <div class="mx-auto container-narrow px-4 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center py-10 md:py-12">
                <div class="order-2 md:order-1 text-center md:text-left">
                    <h1 class="text-white drop-shadow text-2xl md:text-4xl font-extrabold leading-tight">
                        HỆ THỐNG KHẢO SÁT TRỰC TUYẾN
                    </h1>
                    <p class="text-white text-lg md:text-2xl font-semibold mt-2">
                        TRƯỜNG ĐẠI HỌC SAO ĐỎ
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
        {{-- Decorative circle at right like the watermark in the screenshot --}}
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
                @foreach ($dotKhaoSats as $dot)
                    <article class="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-soft flex flex-col">
                        <div class="overflow-hidden">
                            {{-- <img src="{{ asset($dot->hinh_anh ?? 'image/logo.png') }}" alt="{{ $dot->ten_dot }}"
                                class="w-full h-56 object-cover hover:scale-[1.02] transition" />
                            --}}

                            <img src="{{ asset($dot->image ?? 'image/logo.png') }}"
                                class="card-img-top w-full h-56 object-cover hover:scale-[1.02] transition"
                                alt="{{ $dot->ten_dot }}" style="height: 180px; object-fit: cover;">
                        </div>
                        <div class="p-5 flex-1 flex flex-col">
                            <h3 class="font-semibold text-slate-800 leading-relaxed mb-2">
                                {{ $dot->ten_dot }}
                            </h3>
                            <p class="text-sm text-slate-500 mb-2">
                                <i class="bi bi-calendar"></i>
                                Từ {{ \Carbon\Carbon::parse($dot->tungay)->format('d/m/Y') }}
                                đến {{ \Carbon\Carbon::parse($dot->denngay)->format('d/m/Y') }}
                            </p>
                            @if($dot->mota)
                                <p class="text-slate-600 text-sm mb-4 line-clamp-3">{{ $dot->mota }}</p>
                            @endif
                            <div class="mt-auto">
                                <a href="{{ route('khao-sat.show', $dot->id) }}"
                                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2 bg-[#d83b44] text-white font-semibold shadow hover:opacity-95 focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-current">
                                        <path
                                            d="M2 6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v.217l-10 5.555-10-5.555V6Zm0 2.383V18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8.383l-9.445 5.24a2 2 0 0 1-2.11 0L2 8.383Z" />
                                    </svg>
                                    Bắt đầu khảo sát
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="text-center text-slate-500 py-12">
                Hiện tại chưa có khảo sát nào đang diễn ra.
            </div>
        @endif
    </section>
@endsection