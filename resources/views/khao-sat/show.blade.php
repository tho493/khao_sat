@extends('layouts.home')

@section('title','Khảo sát ' . $dotKhaoSat->ten_dot)

@push('styles')
<style>
    .progress-section {
        position: sticky;
        top: 100px;
    }
    
    @media (max-width: 1023px) {
        .progress-section {
            position: static;
        }
    }

    .form-input, .form-textarea, .form-radio, .form-checkbox {
        transition: all 0.2s ease-in-out;
    }

     .flash-effect {
            position: relative;
            z-index: 1;
        }

    .flash-effect::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: inherit;
        z-index: -1;
        animation: flashAnimation 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) 3 forwards;
    }

    @keyframes flashAnimation {
        0% {
            box-shadow: 0 0 0 0px rgba(79, 70, 229, 0.4);
        }
        100% {
            box-shadow: 0 0 0 20px rgba(79, 70, 229, 0);
        }
    }

    .flash-effect-input {
        animation: flashInputAnimation 1s ease-out 3;
    }

    @keyframes flashInputAnimation {
        0% {
            box-shadow: 0 0 0 0px rgba(79, 70, 229, 0);
        }
        25% {
            box-shadow: 0 0 0 5px rgba(79, 70, 229, 0.3);
        }
        100% {
            box-shadow: 0 0 0 0px rgba(79, 70, 229, 0);
        }
    }
</style>
@endpush

@php
    $conditionalMap = $mauKhaoSat->cauHoi
        ->whereNotNull('cau_dieukien_id')
        ->mapWithKeys(function ($item) use ($mauKhaoSat) {
            $condition = json_decode($item->dieukien_hienthi, true);
            $parentQuestion = $mauKhaoSat->cauHoi->firstWhere('id', $item->cau_dieukien_id);
            return [$item->id => [
                'parentId' => $item->cau_dieukien_id,
                'parentType' => $parentQuestion ? $parentQuestion->loai_cauhoi : null,
                'requiredValue' => (string)($condition['value'] ?? null),
                'isOriginallyRequired' => (bool)$item->batbuoc
            ]];
        });
    $questionCounterGlobal = 0;
@endphp

@section('content')
    @if(!empty($adminModeWarning))
        <div id="admin-warning" class="mb-4 sm:mb-6 px-3 sm:px-0" style="position: sticky; top:70px; z-index: 50;">
            <div class="glass-effect bg-yellow-100/70 border-l-4 border-yellow-300 text-yellow-800 p-3 sm:p-4 rounded shadow flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-0 backdrop-blur-md">
                <div class="flex items-start gap-2 flex-1">
                    <i class="bi bi-exclamation-triangle-fill mt-0.5 flex-shrink-0"></i>
                    <span class="text-sm sm:text-base">{{ $adminModeWarning }}</span>
                </div>
                <div class="ml-auto flex gap-2 w-full sm:w-auto">
                    <a href="{{ route('admin.dot-khao-sat.show', $dotKhaoSat->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition flex-1 sm:flex-initial">
                        <i class="bi bi-speedometer2 mr-1"></i> <span class="hidden xs:inline">Về trang quản trị</span><span class="xs:hidden">Quản trị</span>
                    </a>
                    <button type="button" onclick="document.getElementById('admin-warning').style.display='none';" class="inline-flex items-center justify-center px-2 py-1.5 bg-gray-200 text-gray-700 text-xs font-semibold rounded hover:bg-gray-300 transition flex-shrink-0" title="Ẩn cảnh báo">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Màn hình đã hoàn thành (Ẩn mặc định) -->
    <div id="survey-completed-screen" class="hidden container mx-auto py-12 px-4">
        <div class="max-w-2xl mx-auto glass-effect p-8 sm:p-12 text-center rounded-2xl shadow-2xl relative overflow-hidden">
             <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-blue-50 -z-10 opacity-50"></div>
            
            <div class="mb-6 relative">
                 <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto animate-bounce-slow">
                    <i class="bi bi-check-lg text-5xl text-green-600"></i>
                 </div>
            </div>

            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-800 mb-4">
                Bạn đã hoàn thành khảo sát này!
            </h1>
            <p class="text-slate-600 text-lg mb-8">
                Cảm ơn bạn đã đóng góp ý kiến quý báu. Câu trả lời của bạn đã được hệ thống ghi nhận.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                 <a id="btn-review-answers" href="#" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200/50">
                    <i class="bi bi-eye mr-2"></i> Xem lại đáp án
                </a>
                
                 <button id="btn-redo-survey" type="button" class="inline-flex items-center justify-center px-6 py-3 bg-white text-slate-700 border border-slate-200 font-bold rounded-xl hover:bg-slate-50 hover:text-red-600 transition shadow-sm">
                    <i class="bi bi-arrow-repeat mr-2"></i> Làm lại
                </button>

                <a href="{{ route('khao-sat.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition">
                    <i class="bi bi-house mr-2"></i> Trang chủ
                </a>
            </div>
            
             <p class="mt-8 text-xs text-slate-400">
                Mã phiếu: <span id="completed-submission-id" class="font-mono bg-slate-100 px-2 py-1 rounded"></span>
            </p>
        </div>
    </div>

    <div id="survey-main-container" class="container mx-auto py-6 sm:py-8 md:py-12 px-3 sm:px-4 md:px-8">
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-4 sm:gap-6 lg:gap-8 xl:gap-12">

                <!-- Nội dung khảo sát -->
                <div class="w-full lg:w-2/3 space-y-6">
                    
                    {{-- Header của khảo sát --}}
                    <div class="glass-effect p-4 sm:p-6 text-center">
                        <nav class="text-xs sm:text-sm text-slate-600 mb-3 sm:mb-4 flex flex-wrap justify-center items-center gap-1">
                            <a href="{{ url('/') }}" class="hover:text-blue-700">Trang chủ</a>
                            <span>/</span>
                            <a href="{{ route('khao-sat.index') }}" class="hover:text-blue-700">Khảo sát</a>
                            <span>/</span>
                            <span class="font-semibold text-slate-800">{{ Str::limit($dotKhaoSat->ten_dot, 30) }}</span>
                        </nav>
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-800 mb-2 px-2">{{ $dotKhaoSat->ten_dot }}</h1>
                        <h3 class="text-sm sm:text-base text-slate-600 mb-2 px-2 text-justify">{{ $dotKhaoSat->mota ? $dotKhaoSat->mota : "Khảo sát này không có mô tả" }}</h3>
                        <p class="text-xs sm:text-sm text-slate-500">
                            Hạn cuối: {{ $dotKhaoSat->denngay }}
                        </p>
                    </div>

                    <form id="formKhaoSat" method="POST" action="{{ route('khao-sat.store', $dotKhaoSat) }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="metadata[thoigian_batdau]" id="thoigian_batdau">

                        <!-- Thông tin người trả lời -->
                        <div class="glass-effect">
                            <div class="bg-white/40 rounded-t-xl px-4 sm:px-6 py-3 sm:py-4 border-b border-white/30">
                                <h5 class="text-slate-800 font-bold text-base sm:text-lg m-0">Thông tin của bạn</h5>
                            </div>
                            <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                                @if(isset($personalInfoQuestions) && $personalInfoQuestions->count())
                                    @foreach($personalInfoQuestions as $cauHoi)
                                        @php
                                            $questionCounterGlobal++;
                                            $isConditionalChild = isset($conditionalMap[$cauHoi->id]);
                                            $isRequired = $cauHoi->batbuoc;
                                        @endphp
                                        <div class="question-card bg-white/30 p-3 sm:p-4 rounded-lg border border-white/30"
                                             id="question-{{ $cauHoi->id }}"
                                             data-question-id="{{ $cauHoi->id }}"
                                             data-originally-required="{{ $isRequired ? 'true' : 'false' }}"
                                             @if($isConditionalChild)
                                                        data-conditional-parent-id="{{ $conditionalMap[$cauHoi->id]['parentId'] }}"
                                                        data-conditional-required-value="{{ $conditionalMap[$cauHoi->id]['requiredValue'] }}"
                                                @endif>
                                            <label class="block font-bold text-slate-800 mb-2 sm:mb-3 text-base sm:text-lg">
                                                <span class="text-blue-600">Câu {{ $questionCounterGlobal }}:</span>
                                                {{ $cauHoi->noidung_cauhoi }}
                                                @if($isRequired)<span class="text-red-600">*</span>@endif
                                            </label>
                                            @switch($cauHoi->loai_cauhoi)
                                                @case('single_choice')
                                                    <div 
                                                        class="flex flex-col justify-center items-stretch mt-3 gap-2"
                                                        x-data="{
                                                            selected: '',
                                                            setSelected(val) { 
                                                                this.selected = val;
                                                            },
                                                        }">
                                                        @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                            <label
                                                                @click="setSelected('{{ $phuongAn->id }}')"
                                                                class="selectable-likert-card flex flex-row items-center p-4 rounded-xl border cursor-pointer transition w-full min-h-[56px] text-center bg-white/50 border-blue-200 hover:ring-2 hover:ring-blue-300"
                                                                :class="selected == '{{ $phuongAn->id }}' 
                                                                    ? 'bg-blue-100/80 ring-4 ring-blue-400/40 text-blue-800' 
                                                                    : 'text-slate-700'">
                                                                <input 
                                                                    type="radio" 
                                                                    class="hidden" 
                                                                    name="cau_tra_loi[{{ $cauHoi->id }}]" 
                                                                    value="{{ $phuongAn->id }}" 
                                                                    x-model="selected"
                                                                    {{ $isRequired ? 'required' : '' }}>
                                                                
                                                                <div 
                                                                    class="flex items-center justify-center w-5 h-5 sm:w-7 sm:h-7 rounded-full border-2 transition-all mr-4"
                                                                    :class="selected == '{{ $phuongAn->id }}'
                                                                        ? 'bg-blue-500 border-blue-600' 
                                                                        : 'bg-white/80 border-blue-300'">
                                                                    <template x-if="selected == '{{ $phuongAn->id }}'">
                                                                        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                            <circle cx="10" cy="10" r="5" />
                                                                        </svg>
                                                                    </template>
                                                                </div>
                                                                
                                                                <span 
                                                                    class="text-[14px] sm:text-base leading-tight line-clamp-2 text-left flex-1"
                                                                    :class="selected == '{{ $phuongAn->id }}' ? 'font-bold' : 'font-normal'">
                                                                    {{ $phuongAn->noidung }}
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    @break

                                                @case('multiple_choice')
                                                    <div 
                                                        class="flex flex-col justify-center items-stretch mt-3 gap-2"
                                                        x-data="{
                                                            selected: [],
                                                        }"
                                                    >
                                                        @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                            <label
                                                                class="selectable-likert-card flex flex-row items-center p-4 rounded-xl border cursor-pointer transition w-full min-h-[56px] text-center bg-white/50 border-blue-200 hover:ring-2 hover:ring-blue-300"
                                                                :class="selected.includes('{{ $phuongAn->id }}')
                                                                    ? 'bg-blue-100/80 ring-4 ring-blue-400/40 text-blue-800' 
                                                                    : 'text-slate-700'">
                                                                <input 
                                                                    type="checkbox" 
                                                                    class="hidden"
                                                                    name="cau_tra_loi[{{ $cauHoi->id }}][]" 
                                                                    value="{{ $phuongAn->id }}"
                                                                    x-model="selected"
                                                                    @click.stop
                                                                >
                                                                <div 
                                                                    class="flex items-center justify-center w-5 h-5 sm:w-7 sm:h-7 rounded-md border-2 transition-all mr-4"
                                                                    :class="selected.includes('{{ $phuongAn->id }}')
                                                                        ? 'bg-blue-500 border-blue-600' 
                                                                        : 'bg-white/80 border-blue-300'">
                                                                    <template x-if="selected.includes('{{ $phuongAn->id }}')">
                                                                        <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l4 4 6-7" />
                                                                        </svg>
                                                                    </template>
                                                                </div>
                                                                
                                                                <span 
                                                                    class="text-[14px] sm:text-base leading-tight line-clamp-2 text-left flex-1"
                                                                    :class="selected.includes('{{ $phuongAn->id }}') ? 'font-bold' : 'font-normal'">
                                                                    {{ $phuongAn->noidung }}
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    @break

                                                @case('text')
                                                    <textarea class="form-textarea mt-2 w-full rounded-lg bg-[#FFFFFF] border-slate-300 focus:ring-blue-500 focus:border-blue-500"
                                                            name="cau_tra_loi[{{ $cauHoi->id }}]" rows="4"
                                                            placeholder="Nhập câu trả lời của bạn..."
                                                            {{ $isRequired ? 'required' : '' }}></textarea>
                                                    @break

                                                @case('likert')
                                                    <div 
                                                            class="flex flex-col justify-center items-stretch mt-3 gap-2"
                                                            x-data="{
                                                                selected: '{{ $isRequired && isset($cauHoi->phuongAnTraLoi) ? ($cauHoi->phuongAnTraLoi->last()->id ?? '') : '' }}',
                                                                setSelected(val) { this.selected = val },
                                                            }">
                                                            @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                                <label
                                                                    @click="setSelected('{{ $phuongAn->id }}')"
                                                                    class="selectable-likert-card flex flex-row items-center p-4 rounded-xl border cursor-pointer transition w-full min-h-[56px] text-center bg-white/50 border-blue-200 hover:ring-2 hover:ring-blue-300"
                                                                    :class="selected == '{{ $phuongAn->id }}' 
                                                                        ? 'bg-blue-100/80 ring-4 ring-blue-400/40 text-blue-800' 
                                                                        : 'text-slate-700'">
                                                                    <input type="radio" class="hidden" name="cau_tra_loi[{{ $cauHoi->id }}]" value="{{ $phuongAn->id }}" x-model="selected"
                                                                        {{ $isRequired ? 'required' : '' }}>
                                                                    
                                                                    <div 
                                                                        class="flex items-center justify-center w-5 h-5 sm:w-7 sm:h-7 rounded-full border-2 transition-all mr-4"
                                                                        :class="selected == '{{ $phuongAn->id }}'
                                                                            ? 'bg-blue-500 border-blue-600' 
                                                                            : 'bg-white/80 border-blue-300'">
                                                                        <template x-if="selected == '{{ $phuongAn->id }}'">
                                                                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                                <circle cx="10" cy="10" r="5" />
                                                                            </svg>
                                                                        </template>
                                                                    </div>
                                                                    
                                                                    <span 
                                                                        class="text-[14px] sm:text-base leading-tight line-clamp-2 text-left flex-1"
                                                                        :class="selected == '{{ $phuongAn->id }}' ? 'font-bold' : 'font-normal'">
                                                                        {{ $phuongAn->noidung }}
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    @break

                                                @case('rating')
                                                    <div 
                                                        class="flex flex-col justify-center items-stretch mt-3 gap-2"
                                                        x-data="{
                                                            selected: '',
                                                            setSelected(val) { this.selected = val },
                                                        }">
                                                        <div class="flex items-center justify-between gap-2 w-full">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <label
                                                                    @click="setSelected('{{ $i }}')"
                                                                    class="selectable-likert-card flex flex-row items-center justify-center w-12 h-12 rounded-xl border cursor-pointer transition bg-white/50 border-blue-200 hover:ring-2 hover:ring-blue-300"
                                                                    :class="selected == '{{ $i }}' 
                                                                        ? 'bg-blue-100/80 ring-4 ring-blue-400/40 text-blue-800 font-bold' 
                                                                        : 'text-slate-700 font-normal'">
                                                                    <input 
                                                                        type="radio" 
                                                                        class="hidden" 
                                                                        name="cau_tra_loi[{{ $cauHoi->id }}]" 
                                                                        value="{{ $i }}"
                                                                        x-model="selected"
                                                                        {{ $isRequired ? 'required' : '' }}>
                                                                    <div 
                                                                        class="flex items-center justify-center w-8 h-8 rounded-full border-2 transition-all"
                                                                        :class="selected == '{{ $i }}'
                                                                            ? 'bg-blue-500 border-blue-600 text-white' 
                                                                            : 'bg-white/80 border-blue-300 text-slate-600'">
                                                                        {{ $i }}
                                                                    </div>
                                                                </label>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    @break

                                                @case('date')
                                                    <input type="date" class="form-input mt-2 w-full rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500"
                                                                name="cau_tra_loi[{{ $cauHoi->id }}]"
                                                                {{ $isRequired ? 'required' : '' }}>
                                                    @break

                                                @case('number')
                                                    <input inputmode="decimal" pattern="[0-9]*" type="text" 
                                                        class="form-input mt-2 w-full rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500"
                                                        name="cau_tra_loi[{{ $cauHoi->id }}]"
                                                        placeholder="Nhập số..."
                                                        {{ $isRequired ? 'required' : '' }}
                                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                                    @break

                                                @case('custom_select')
                                                     <div class="flex justify-center">
                                                            <select class="form-input mt-2 w-full max-w-2xl rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500"
                                                                    name="cau_tra_loi[{{ $cauHoi->id }}]" {{ $isRequired ? 'required' : '' }}>
                                                                <option value="">-- {{ $cauHoi->noidung_cauhoi }} --</option>
                                                                @if($cauHoi->dataSource)
                                                                    @foreach($cauHoi->dataSource->values as $value)
                                                                        <option value="{{ $value->value }}">{{ $value->label }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    @break

                                            @endswitch
                                        </div>
                                    @endforeach
                                @else
                                    <div class="glass-effect p-6 text-center text-slate-600">
                                        Trang khảo sát này không yêu cầu thông tin cá nhân.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Phần câu hỏi khảo sát -->
                        <div id="survey-pages-container">
                            @forelse($questionsByPage as $pageNumber => $questionsOnPage)
                                <div class="survey-page" id="survey-page-{{ $pageNumber }}" style="{{ !$loop->first ? 'display: none;' : '' }}">
                                    <div class="glass-effect">
                                        <div class="bg-white/40 rounded-t-xl px-4 sm:px-6 py-3 sm:py-4 border-b border-white/30">
                                            <h5 class="text-slate-800 font-bold text-base sm:text-lg m-0">Phần {{ $pageNumber }}/{{ $questionsByPage->count() }}</h5>
                                        </div>
                                        <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                                            @foreach($questionsOnPage as $cauHoi)
                                                @php
                                                    $questionCounterGlobal++;
                                                    $isConditionalChild = isset($conditionalMap[$cauHoi->id]);
                                                    $isRequired = $cauHoi->batbuoc;
                                                @endphp
                                                <div class="question-card bg-white/30 p-3 sm:p-4 rounded-lg border border-white/30"
                                                     id="question-{{ $cauHoi->id }}"
                                                     data-question-id="{{ $cauHoi->id }}"
                                                     data-originally-required="{{ $isRequired ? 'true' : 'false' }}"
                                                     @if($isConditionalChild)
                                                        data-conditional-parent-id="{{ $conditionalMap[$cauHoi->id]['parentId'] }}"
                                                        data-conditional-required-value="{{ $conditionalMap[$cauHoi->id]['requiredValue'] }}"
                                                     @endif>
                                                    <label class="block font-bold text-slate-800 mb-2 sm:mb-3 text-base sm:text-lg">
                                                        <span class="text-blue-600">Câu {{ $questionCounterGlobal }}:</span>
                                                        {{ $cauHoi->noidung_cauhoi }}
                                                        @if($isRequired)<span class="text-red-600">*</span>@endif
                                                    </label>
                                                    @switch($cauHoi->loai_cauhoi)
                                                        @case('single_choice')
                                                    <div 
                                                        class="flex flex-col justify-center items-stretch mt-3 gap-2"
                                                        x-data="{
                                                            selected: '',
                                                            setSelected(val) { 
                                                                this.selected = val;
                                                            },
                                                        }">
                                                        @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                            <label
                                                                @click="setSelected('{{ $phuongAn->id }}')"
                                                                class="selectable-likert-card flex flex-row items-center p-4 rounded-xl border cursor-pointer transition w-full min-h-[56px] text-center bg-white/50 border-blue-200 hover:ring-2 hover:ring-blue-300"
                                                                :class="selected == '{{ $phuongAn->id }}' 
                                                                    ? 'bg-blue-100/80 ring-4 ring-blue-400/40 text-blue-800' 
                                                                    : 'text-slate-700'">
                                                                <input 
                                                                    type="radio" 
                                                                    class="hidden" 
                                                                    name="cau_tra_loi[{{ $cauHoi->id }}]" 
                                                                    value="{{ $phuongAn->id }}" 
                                                                    x-model="selected"
                                                                    {{ $isRequired ? 'required' : '' }}>
                                                                
                                                                <div 
                                                                    class="flex items-center justify-center w-5 h-5 sm:w-7 sm:h-7 rounded-full border-2 transition-all mr-4"
                                                                    :class="selected == '{{ $phuongAn->id }}'
                                                                        ? 'bg-blue-500 border-blue-600' 
                                                                        : 'bg-white/80 border-blue-300'">
                                                                    <template x-if="selected == '{{ $phuongAn->id }}'">
                                                                        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                            <circle cx="10" cy="10" r="5" />
                                                                        </svg>
                                                                    </template>
                                                                </div>
                                                                
                                                                <span 
                                                                    class="text-[14px] sm:text-base leading-tight line-clamp-2 text-left flex-1"
                                                                    :class="selected == '{{ $phuongAn->id }}' ? 'font-bold' : 'font-normal'">
                                                                    {{ $phuongAn->noidung }}
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    @break

                                                    @case('multiple_choice')
                                                        <div 
                                                            class="flex flex-col justify-center items-stretch mt-3 gap-2"
                                                            x-data="{
                                                                selected: [],
                                                            }">
                                                            @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                                <label
                                                                    class="selectable-likert-card flex flex-row items-center p-4 rounded-xl border cursor-pointer transition w-full min-h-[56px] text-center bg-white/50 border-blue-200 hover:ring-2 hover:ring-blue-300"
                                                                    :class="selected.includes('{{ $phuongAn->id }}')
                                                                        ? 'bg-blue-100/80 ring-4 ring-blue-400/40 text-blue-800' 
                                                                        : 'text-slate-700'">
                                                                    <input 
                                                                        type="checkbox" 
                                                                        class="hidden"
                                                                        name="cau_tra_loi[{{ $cauHoi->id }}][]" 
                                                                        value="{{ $phuongAn->id }}"
                                                                        x-model="selected"
                                                                        @click.stop
                                                                    >
                                                                    <div 
                                                                        class="flex items-center justify-center w-5 h-5 sm:w-7 sm:h-7 rounded-md border-2 transition-all mr-4"
                                                                        :class="selected.includes('{{ $phuongAn->id }}')
                                                                            ? 'bg-blue-500 border-blue-600' 
                                                                            : 'bg-white/80 border-blue-300'">
                                                                        <template x-if="selected.includes('{{ $phuongAn->id }}')">
                                                                            <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l4 4 6-7" />
                                                                            </svg>
                                                                        </template>
                                                                    </div>
                                                                    
                                                                    <span 
                                                                        class="text-[14px] sm:text-base leading-tight line-clamp-2 text-left flex-1"
                                                                        :class="selected.includes('{{ $phuongAn->id }}') ? 'font-bold' : 'font-normal'">
                                                                        {{ $phuongAn->noidung }}
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                        @break
                                                            
                                                        @case('text')
                                                            <textarea class="form-textarea mt-2 w-full rounded-lg bg-white border-slate-300 focus:ring-blue-500 focus:border-blue-500"
                                                                    name="cau_tra_loi[{{ $cauHoi->id }}]" rows="4"
                                                                    placeholder="Nhập câu trả lời của bạn..."
                                                                    {{ $isRequired ? 'required' : '' }}></textarea>
                                                            @break
                                                        
                                                        @case('likert')
                                                        <div 
                                                            class="flex flex-col justify-center items-stretch mt-3 gap-2"
                                                            x-data="{
                                                                selected: '{{ $isRequired && isset($cauHoi->phuongAnTraLoi) ? ($cauHoi->phuongAnTraLoi->last()->id ?? '') : '' }}',
                                                                setSelected(val) { this.selected = val },
                                                            }">
                                                            @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                                <label
                                                                    @click="setSelected('{{ $phuongAn->id }}')"
                                                                    class="selectable-likert-card flex flex-row items-center p-4 rounded-xl border cursor-pointer transition w-full min-h-[56px] text-center bg-white/50 border-blue-200 hover:ring-2 hover:ring-blue-300"
                                                                    :class="selected == '{{ $phuongAn->id }}' 
                                                                        ? 'bg-blue-100/80 ring-4 ring-blue-400/40 text-blue-800' 
                                                                        : 'text-slate-700'">
                                                                    <input type="radio" class="hidden" name="cau_tra_loi[{{ $cauHoi->id }}]" value="{{ $phuongAn->id }}" x-model="selected"
                                                                        {{ $isRequired ? 'required' : '' }}>
                                                                    
                                                                    <div 
                                                                        class="flex items-center justify-center w-5 h-5 sm:w-7 sm:h-7 rounded-full border-2 transition-all mr-4"
                                                                        :class="selected == '{{ $phuongAn->id }}'
                                                                            ? 'bg-blue-500 border-blue-600' 
                                                                            : 'bg-white/80 border-blue-300'">
                                                                        <template x-if="selected == '{{ $phuongAn->id }}'">
                                                                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                                <circle cx="10" cy="10" r="5" />
                                                                            </svg>
                                                                        </template>
                                                                    </div>
                                                                    
                                                                    <span 
                                                                        class="text-[14px] sm:text-base leading-tight line-clamp-2 text-left flex-1"
                                                                        :class="selected == '{{ $phuongAn->id }}' ? 'font-bold' : 'font-normal'">
                                                                        {{ $phuongAn->noidung }}
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                        @break
                
                                                        @case('rating')
                                                            <div 
                                                                class="flex flex-col justify-center items-stretch mt-3 gap-2"
                                                                x-data="{
                                                                    selected: '',
                                                                    setSelected(val) { this.selected = val },
                                                                }">
                                                                <div class="flex items-center justify-between gap-2 w-full">
                                                                    @for($i = 1; $i <= 5; $i++)
                                                                        <label
                                                                            @click="setSelected('{{ $i }}')"
                                                                            class="selectable-likert-card flex flex-row items-center justify-center w-12 h-12 rounded-xl border cursor-pointer transition bg-white/50 border-blue-200 hover:ring-2 hover:ring-blue-300"
                                                                            :class="selected == '{{ $i }}' 
                                                                                ? 'bg-blue-100/80 ring-4 ring-blue-400/40 text-blue-800 font-bold' 
                                                                                : 'text-slate-700 font-normal'">
                                                                            <input 
                                                                                type="radio" 
                                                                                class="hidden" 
                                                                                name="cau_tra_loi[{{ $cauHoi->id }}]" 
                                                                                value="{{ $i }}"
                                                                                x-model="selected"
                                                                                {{ $isRequired ? 'required' : '' }}>
                                                                            <div 
                                                                                class="flex items-center justify-center w-8 h-8 rounded-full border-2 transition-all"
                                                                                :class="selected == '{{ $i }}'
                                                                                    ? 'bg-blue-500 border-blue-600 text-white' 
                                                                                    : 'bg-white/80 border-blue-300 text-slate-600'">
                                                                                {{ $i }}
                                                                            </div>
                                                                        </label>
                                                                    @endfor
                                                                </div>
                                                            </div>
                                                            @break
                
                                                        @case('date')
                                                            <input type="date" class="form-input mt-2 w-full rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500"
                                                                name="cau_tra_loi[{{ $cauHoi->id }}]"
                                                                {{ $isRequired ? 'required' : '' }}>
                                                            @break
                
                                                        @case('number')
                                                            <input inputmode="decimal" pattern="[0-9]*" type="text" 
                                                                class="form-input mt-2 w-full rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500"
                                                                name="cau_tra_loi[{{ $cauHoi->id }}]"
                                                                placeholder="Nhập số..."
                                                                {{ $isRequired ? 'required' : '' }}
                                                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                                            @break
                
                                                        @case('custom_select')
                                                            <div class="flex justify-center">
                                                                <select class="form-input mt-2 w-full max-w-2xl rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500"
                                                                        name="cau_tra_loi[{{ $cauHoi->id }}]" {{ $isRequired ? 'required' : '' }}>
                                                                    <option value="">-- {{ $cauHoi->noidung_cauhoi }} --</option>
                                                                    @if($cauHoi->dataSource)
                                                                        @foreach($cauHoi->dataSource->values as $value)
                                                                            <option value="{{ $value->value }}">{{ $value->label }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                            @break

                                                    @endswitch
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="glass-effect p-6 text-center text-slate-600">
                                    Trang khảo sát này chưa có câu hỏi nào.
                                </div>
                            @endforelse
                        </div>

                        <!-- Captcha, nút Submit và điều hướng -->
                        <div class="glass-effect p-4 sm:p-6">
                            {{-- Captcha --}}
                            <!-- <div id="captcha-container" class="mb-4 flex justify-center" style="display: none;">
                                <div class="g-recaptcha transform scale-90 sm:scale-100" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div> 
                            </div> -->
                            <div id="captcha-container" class="mb-4 flex justify-center" style="display: none;">
                                <div id="html_element"></div> 
                            </div>

                            <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
                            
                            {{-- Nút điều hướng --}}
                            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
                                <button type="button" class="btn-nav btn-prev inline-flex items-center justify-center px-4 sm:px-6 py-2.5 sm:py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm sm:text-base" id="prevBtn" style="display: none;">
                                    <i class="bi bi-arrow-left mr-2"></i> <span class="hidden xs:inline">Quay lại</span><span class="xs:hidden">Trước</span>
                                </button>
                                
                                {{-- Placeholder để giữ layout cân bằng --}}
                                <div id="prev-placeholder" class="hidden sm:block"></div>

                                <button type="button" class="btn-nav btn-next inline-flex items-center justify-center px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm sm:text-base font-medium" id="nextBtn">
                                    <span class="hidden xs:inline">Tiếp theo</span><span class="xs:hidden">Tiếp</span> <i class="bi bi-arrow-right ml-2"></i>
                                </button>
                                
                                <button type="submit" class="inline-flex items-center justify-center px-6 sm:px-8 py-2.5 sm:py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-lg font-semibold text-sm sm:text-base" id="submitBtn" style="display: none;">
                                    <i class="bi bi-send mr-2"></i> <span class="hidden xs:inline">Gửi khảo sát</span><span class="xs:hidden">Gửi</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Sidebar Progress -->
                <div class="w-full lg:w-1/3 order-first lg:order-last">
                    <div class="progress-section space-y-4 sm:space-y-6">
                        <!-- Thời gian -->
                        <div class="glass-effect p-4 sm:p-6 flex flex-col items-center">
                            <h6 class="font-bold text-slate-800 mb-2 sm:mb-3 text-sm sm:text-base">Thời gian</h6>
                            <div class="text-3xl sm:text-4xl font-extrabold text-blue-600" id="survey-timer">00:00</div>
                        </div>
                    
                        <!-- Tiến độ -->
                        <div id="progress-container" class="glass-effect p-4 sm:p-6 transition-all duration-300 ease-in-out lg:sticky lg:top-24 origin-top z-50">
    
                            <div id="progress-content" class="transition-opacity duration-200">
                                
                                <div id="btn-collapse-view" class="flex justify-between items-center mb-3 sm:mb-4 group cursor-pointer lg:cursor-default">
                                    <h6 class="font-bold text-slate-800 text-sm sm:text-base group-hover:text-blue-600 transition-colors">
                                        Tiến độ hoàn thành
                                    </h6>
                                    <i class="bi bi-chevron-up text-slate-500 bg-slate-100 hover:bg-slate-200 p-1 rounded-full text-xs transition-colors hidden" id="icon-collapse"></i>
                                </div>

                                <div class="w-full bg-white/40 rounded-full h-5 sm:h-6 mb-2 sm:mb-3 overflow-hidden border border-white/50">
                                    <div class="progress-bar-dynamic h-5 sm:h-6 rounded-full flex items-center justify-center text-white text-xs sm:text-sm font-semibold transition-all duration-300"
                                        id="progressBar" style="width: 0%; background-color: #f59e42;">
                                    </div>
                                </div>
                                
                                <div class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm">
                                    <p class="text-slate-600 mb-1">
                                        <strong>Đã trả lời:</strong> <span id="answeredCount">0</span>/<span id="totalCount">0</span> câu
                                    </p>
                                    <div class="flex flex-col xs:flex-row justify-between gap-1 xs:gap-0 text-[10px] sm:text-xs">
                                        <span class="text-red-600"><i class="bi bi-asterisk"></i> Bắt buộc: <span id="requiredCount">0</span></span>
                                        <span class="text-slate-500"><i class="bi bi-circle"></i> Không bắt buộc: <span id="optionalCount">0</span></span>
                                    </div>
                                </div>
                            </div>

                            <div id="progress-collapsed" 
                                class="hidden flex items-center justify-between gap-2 cursor-pointer select-none animate-fade-in
                                        backdrop-blur-md shadow-lg rounded-full px-4 py-2 ring-1 ring-slate-900/5">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="flex items-center gap-1 text-xs font-bold text-blue-600 border-r border-slate-300 pr-3">
                                        <i class="bi bi-clock"></i>
                                        <span id="survey-timer-collapsed" class="min-w-[35px]">00:00</span>
                                    </div>
                                    <div class="w-24 bg-white/40 rounded-full h-5 overflow-hidden border border-white/50 flex-shrink-0">
                                        <div class="progress-bar-dynamic h-5 rounded-full flex items-center justify-center text-white text-xs font-semibold transition-all duration-300"
                                            id="progressBarCollapsed" style="width: 0%; background-color: #f59e42;">
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700 whitespace-nowrap">
                                        <span id="answeredCountCollapsed">0</span>/<span id="totalCountCollapsed">0</span> câu
                                    </span>
                                </div>
                                <i class="bi bi-chevron-down text-slate-500"></i>
                            </div>
                        </div>

                        <div id="progress-placeholder" class="hidden"></div>

                        <style>
                            @keyframes fadeInDown {
                                from { opacity: 0; transform: translateY(-10px); }
                                to { opacity: 1; transform: translateY(0); }
                            }
                            .animate-fade-in {
                                animation: fadeInDown 0.3s ease-out forwards;
                            }
                             #progressBar, #progressBarCollapsed {
                                background-color: #f59e42; /* Orange */
                            }

                            #progressBar.progress-almost, #progressBarCollapsed.progress-almost {
                                background-color: #2563eb !important; /* blue-600 */
                            }

                            #progressBar.progress-done, #progressBarCollapsed.progress-done {
                                background-color: #16a34a !important; /* Green */
                            }
                        </style>
                        <script>
                           document.addEventListener('DOMContentLoaded', function() {
                                // Lấy các element
                                const container = document.getElementById('progress-container');
                                const placeholder = document.getElementById('progress-placeholder');
                                const content = document.getElementById('progress-content');
                                const collapsed = document.getElementById('progress-collapsed');
                                const btnCollapseView = document.getElementById('btn-collapse-view');
                                const iconCollapse = document.getElementById('icon-collapse');
                                
                                let initialHeight = container.offsetHeight;
                                let isSticky = false;
                                let isExpanded = false;

                                window.addEventListener('resize', () => {
                                    if (!isSticky) initialHeight = container.offsetHeight;
                                });

                                function syncData() {
                                    const fullBar = document.getElementById('progressBar');
                                    const collapsedBar = document.getElementById('progressBarCollapsed');
                                    if (fullBar && collapsedBar) {
                                        collapsedBar.style.width = fullBar.style.width;
                                        
                                        // Sync color classes
                                        collapsedBar.classList.toggle('progress-almost', fullBar.classList.contains('progress-almost'));
                                        collapsedBar.classList.toggle('progress-done', fullBar.classList.contains('progress-done'));
                                        
                                        collapsedBar.innerHTML = ""; 
                                    }
                                    const ans = document.getElementById('answeredCount');
                                    const total = document.getElementById('totalCount');
                                    if(ans && total) {
                                        document.getElementById('answeredCountCollapsed').textContent = ans.textContent;
                                        document.getElementById('totalCountCollapsed').textContent = total.textContent;
                                    }
                                }
                                const observer = new MutationObserver(syncData);
                                const targets = [document.getElementById('progressBar'), document.getElementById('answeredCount')];
                                targets.forEach(el => { if(el) observer.observe(el, { attributes: true, childList: true, subtree: true }); });


                                function clearContainerStyles() {
                                    container.classList.remove(
                                        'glass-effect', 'p-4', 'sm:p-6', // Style gốc
                                         'backdrop-blur-xl', 'shadow-2xl', 'border-blue-200', 'rounded-2xl', // Style Popup
                                        'bg-transparent', 'py-2', 'px-4' // Style Wrapper rỗng
                                    );
                                }

                                function expandPopup() {
                                    isExpanded = true;
                                    collapsed.classList.add('hidden');
                                    content.classList.remove('hidden');
                                    iconCollapse.classList.remove('hidden');

                                    clearContainerStyles();
                                    container.classList.add('backdrop-blur-xl', 'shadow-2xl', 'border-blue-200', 'p-4', 'sm:p-6', 'rounded-2xl');
                                }

                                function collapsePopup() {
                                    isExpanded = false;
                                    content.classList.add('hidden');
                                    collapsed.classList.remove('hidden');
                                    iconCollapse.classList.add('hidden');

                                    clearContainerStyles();
                                    container.classList.add('bg-transparent', 'py-2', 'px-4');
                                }

                                collapsed.addEventListener('click', expandPopup);
                                btnCollapseView.addEventListener('click', function() {
                                    if (isSticky && window.innerWidth < 1024) collapsePopup();
                                });

                                window.addEventListener('scroll', function() {
                                    if (window.innerWidth >= 1024) {
                                        if (isSticky) deactivateSticky();
                                        return;
                                    }

                                    const threshold = 120;

                                    if (window.scrollY > threshold) {
                                        if (!isSticky) activateSticky();
                                    } else {
                                        if (isSticky) deactivateSticky();
                                    }
                                });

                                function activateSticky() {
                                    isSticky = true;
                                    placeholder.style.height = initialHeight + 'px';
                                    placeholder.classList.remove('hidden');

                                    container.classList.remove('lg:sticky');
                                    container.classList.add('fixed', 'top-[70px]', 'left-4', 'right-4', 'z-50');
                                    
                                    collapsePopup();
                                }

                                function deactivateSticky() {
                                    isSticky = false;
                                    isExpanded = false;
                                    placeholder.classList.add('hidden');

                                    container.classList.remove('fixed', 'top-[70px]', 'left-4', 'right-4', 'z-50');
                                    container.classList.add('lg:sticky');

                                    clearContainerStyles();
                                    container.classList.add('glass-effect', 'p-4', 'sm:p-6');

                                    content.classList.remove('hidden');
                                    collapsed.classList.add('hidden');
                                    iconCollapse.classList.add('hidden');
                                }
                            });
                        </script>

                        <!-- Lưu ý -->
                        <div class="glass-effect p-4 sm:p-6">
                            <h6 class="font-bold text-slate-800 mb-2 text-sm sm:text-base">Lưu ý</h6>
                            <ul class="text-xs sm:text-sm text-slate-700 list-disc pl-4 sm:pl-5 space-y-1 mb-0">
                                <li>Câu hỏi có dấu <span class="text-red-600">*</span> là bắt buộc.</li>
                                <li>Tiến trình của bạn được tự động lưu.</li>
                                <li>Vui lòng kiểm tra kỹ trước khi gửi.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://unpkg.com/alpinejs" defer></script>
@push('scripts')
<script>
     function getCurrentLocalDateTime() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }
    
    document.getElementById('thoigian_batdau').value = getCurrentLocalDateTime();
    const surveyConditionalMap = @json($conditionalMap);

     $(document).ready(function() {
        const surveyForm = $('#formKhaoSat');
        const submitBtn = $('#submitBtn');
        const storageKey = `survey_progress_{{ $dotKhaoSat->id }}`;
        let debounceTimer;

        function updateUI() {
            saveProgress();
            updateProgress();
            checkAllConditions();
        }

        // --- CÁC HÀM CHỨC NĂNG ---
        function saveProgress() {
        const formData = surveyForm.serializeArray();
        let data = {};
        
        $.each(formData, function(i, field) {
            if (field.name === '_token' || field.name === '_submission_token') {
                return; // Bỏ qua
            }

            if (field.name.endsWith('[]')) {
                const cleanName = field.name.slice(0, -2);
                if (!data[cleanName]) {
                    data[cleanName] = [];
                }
                data[cleanName].push(field.value);
            } else {
                data[field.name] = field.value;
            }
        });

        if (Object.keys(data).length > 0) {
            localStorage.setItem(storageKey, JSON.stringify(data));
            console.log("AutoSave Success");
        }
    }

        function loadProgress() {
            const savedData = localStorage.getItem(storageKey);
            
            if (savedData) {
                Swal.fire({
                    title: 'Tìm thấy dữ liệu chưa hoàn thành!',
                    text: "Bạn có muốn khôi phục lại các câu trả lời từ lần làm việc trước không?",
                    icon: 'question',
                    showDenyButton: true,
                    confirmButtonText: '<i class="bi bi-arrow-clockwise"></i> Khôi phục',
                    denyButtonText: '<i class="bi bi-trash"></i> Xóa & Bắt đầu lại',
                    confirmButtonColor: '#3085d6',
                    denyButtonColor: '#d33',
                }).then((result) => {
                    if (result.isConfirmed) {
                        fillFormWithData(savedData);
                        // Swal.fire('Đã khôi phục!', 'Các câu trả lời của bạn đã được tải lại.', 'success');
                    } else if (result.isDenied) {
                        clearProgress();
                        // Swal.fire('Đã xóa!', 'Bạn có thể bắt đầu lại từ đầu.', 'info');
                    }
                });
            }
        }
        
        function fillFormWithData(jsonData) {
            try {
                const data = JSON.parse(jsonData);
                
                for (const name in data) {
                    const value = data[name];
                    const element = surveyForm.find(`[name="${name}"]`);

                    if (Array.isArray(value)) {
                        const checkboxGroup = surveyForm.find(`[name="${name}[]"]`);
                        checkboxGroup.prop('checked', false);
                        value.forEach(val => {
                            checkboxGroup.filter(`[value="${val}"]`).prop('checked', true);
                        });
                    } else if (element.is(':radio')) {
                        surveyForm.find(`[name="${name}"][value="${value}"]`).prop('checked', true);
                    } else {
                        element.val(value);
                    }
                }
                
                if (typeof updateUI === 'function') {
                    updateUI();
                }
                console.log("Load question success")
            } catch (e) {
                console.error('Lỗi khi tải dữ liệu từ LocalStorage:', e);
                clearProgress();
            }
        }

        function clearProgress() {
            localStorage.removeItem(storageKey);
            console.log('Survey progress cleared.');
        }
        
        window.updateProgress = function() {
            let answeredQuestions = 0;
            let totalVisibleQuestions = 0;
            let totalRequiredQuestions = 0;
            let totalOptionalQuestions = 0;

            $('.question-card').each(function() {
                const $card = $(this);
                totalVisibleQuestions++;

                const isRequiredOriginally =
                    $card.data('originally-required') === true ||
                    $card.data('originally-required') === 'true';

                if (isRequiredOriginally) {
                    totalRequiredQuestions++;
                } else {
                    totalOptionalQuestions++;
                }

                const $inputs = $card.find(
                    'input[name^="cau_tra_loi"], textarea[name^="cau_tra_loi"], select[name^="cau_tra_loi"]'
                );

                let isAnswered = false;

                $inputs.each(function() {
                    const $input = $(this);

                    if ($input.is(':radio') || $input.is(':checkbox'))  {
                        if ($input.is(':checked')) {
                            isAnswered = true;
                            return false;
                        }
                    } else if ($input.is('select')) {
                        if ($input.val() && $input.val().toString().trim() !== '') {
                            isAnswered = true;
                            return false;
                        }
                    } else {
                        if ($input.val().trim() !== '') {
                            isAnswered = true;
                            return false;
                        }
                    }
                });

                if (isAnswered) {
                    answeredQuestions++;
                }
            });

            const progress =
                totalVisibleQuestions > 0
                    ? Math.round((answeredQuestions / totalVisibleQuestions) * 100)
                    : 0;

            const bar = document.getElementById('progressBar');
            bar.classList.remove('progress-almost', 'progress-done');
            if (progress >= 80) {
                bar.classList.add('progress-done');
            } else if (progress >= 45) {
                bar.classList.add('progress-almost');
            }

            $('#progressBar')
                .css('width', progress + '%')
                .text(progress + '%');

            $('#answeredCount').text(answeredQuestions);
            $('#totalCount').text(totalVisibleQuestions);
            $('#requiredCount').text(totalRequiredQuestions);
            $('#optionalCount').text(totalOptionalQuestions);
        };

        function checkAllConditions() {
            $('.question-card[data-conditional-parent-id]').each(function() {
                const $childCard = $(this);
                const childId = $childCard.data('question-id');

                const condCfg = surveyConditionalMap[childId];
                if (!condCfg) return;

                const parentId = condCfg.parentId;
                const parentType = condCfg.parentType;
                const requiredValue = String(condCfg.requiredValue ?? '');
                const isOriginallyRequired = !!condCfg.isOriginallyRequired;

                let shouldShow = false;

                if (parentType === 'multiple_choice') {
                    const $checked = $(`#question-${parentId} input[type="checkbox"]:checked`);
                    const selectedVals = $checked.map(function(){ return $(this).val(); }).get();
                    if (selectedVals.includes(requiredValue)) {
                        shouldShow = true;
                    }
                } else {
                    const $checked = $(`#question-${parentId} input:checked`);
                    const val = $checked.length ? $checked.val() : null;
                    if (val !== null && String(val) === requiredValue) {
                        shouldShow = true;
                    }
                }

                const $childInputs = $childCard.find('input[name^="cau_tra_loi"], textarea[name^="cau_tra_loi"], select[name^="cau_tra_loi"]');

                if (shouldShow) {
                    if (!$childCard.is(':visible')) {
                        $childCard.slideDown(200);
                    }
                    if (isOriginallyRequired) {
                        $childInputs.prop('required', true);
                    }
                } else {
                    if ($childCard.is(':visible')) {
                        $childCard.slideUp(200, function() {
                            clearQuestionValues($childCard);
                        });
                    }
                    $childInputs.prop('required', false);
                }
            });

            // Sau khi ẩn/hiện thì cập nhật tiến độ
            if (typeof updateProgress === 'function') {
                updateProgress();
            }
        }
        
        /**
         * Helper: Xóa câu trả lời của một card câu hỏi.
         * @param {jQuery} questionCard - Đối tượng jQuery của .question-card
         */
        function clearQuestionValues($questionCard) {
            $questionCard.find('input[type="radio"], input[type="checkbox"]').prop('checked', false);
            $questionCard.find('input[type="text"], input[type="number"], input[type="date"], textarea').val('');
            $questionCard.find('select').val('');
        }

        // Check trạng thái đã hoàn thành
        function checkCompletionStatus() {
            const completedKey = 'survey_completed_{{ $dotKhaoSat->id }}';
            const completedData = localStorage.getItem(completedKey);

            if (completedData) {
                try {
                    const data = JSON.parse(completedData);
                    // Ẩn form chính
                    $('#survey-main-container').hide();
                    // Hiện màn hình hoàn thành
                    $('#survey-completed-screen').removeClass('hidden');
                    
                    // Cập nhật link xem lại
                    if (data.id) {
                        let reviewUrl = '{{ route('khao-sat.index') }}/review-history/' + data.id;
                        if (data.token) {
                            reviewUrl += '?token=' + data.token;
                        }
                        $('#btn-review-answers').attr('href', reviewUrl);
                        $('#completed-submission-id').text('#' + data.id);
                    } else {
                         $('#btn-review-answers').hide(); // Fallback nếu không có ID
                    }

                    // Xử lý nút làm lại
                    $('#btn-redo-survey').on('click', function() {
                        Swal.fire({
                            title: 'Làm lại khảo sát?',
                            text: "Bạn có chắc chắn muốn xóa trạng thái hoàn thành và làm lại khảo sát này không?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Đồng ý, làm lại!',
                            cancelButtonText: 'Hủy'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                localStorage.removeItem(completedKey);
                                location.reload();
                            }
                        });
                    });

                    return true; // Đã hoàn thành
                } catch(e) {
                    console.error("Lỗi parse completion data", e);
                    localStorage.removeItem(completedKey);
                }
            }
            return false; // Chưa hoàn thành
        }

        if (!checkCompletionStatus()) {
            // Chạy lần đầu khi load nếu chưa hoàn thành
            loadProgress();
            checkAllConditions();
            updateProgress();
            $('.question-card[data-conditional-parent-id]').hide();
        }
        
        surveyForm.on('input change', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(updateUI, 250);
        });

        // Thời gian làm bài
        let secondsElapsed = 0;
        function pad(n) { return n < 10 ? '0' + n : n; }
        setInterval(function() {
            secondsElapsed++;
            const minutes = Math.floor(secondsElapsed / 60);
            const seconds = secondsElapsed % 60;
            const timeStr = pad(minutes) + ':' + pad(seconds);
            $('#survey-timer').text(timeStr);
            $('#survey-timer-collapsed').text(timeStr);
        }, 1000);

        // ===========================================================
        // ==     XỬ LÝ SUBMIT FORM BẰNG AJAX                       ==
        // ===========================================================
        surveyForm.on('submit', function(e) {
            e.preventDefault(); 
            
            if (submitBtn.prop('disabled')) return;

            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }
            
            submitBtn.prop('disabled', true);
            submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Đang gửi...');
            
            $.ajax({
                url: $(this).attr('action'),
                method: $(this).attr('method'),
                data: $(this).serialize(),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        clearProgress();
                        
                        // Lưu trạng thái hoàn thành vào localStorage
                        const completionData = {
                            id: response.submission_id,
                            token: response.token,
                            timestamp: new Date().getTime()
                        };
                        localStorage.setItem('survey_completed_{{ $dotKhaoSat->id }}', JSON.stringify(completionData));

                        window.location.href = response.redirect;
                    } else {
                        alert(response.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                        submitBtn.prop('disabled', false).html('<i class="bi bi-send mr-2"></i> Gửi khảo sát');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Đã có lỗi không mong muốn xảy ra.';
                    if (xhr.status === 422 && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        const firstErrorKey = Object.keys(errors)[0];
                        errorMessage = errors[firstErrorKey][0];
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    alert(errorMessage);

                    // Làm mới recaptcha
                    if (xhr.responseJSON.errors && xhr.responseJSON.errors['g-recaptcha-response']) {
                        grecaptcha.reset();
                    }
                    
                    submitBtn.prop('disabled', false).html('<i class="bi bi-send mr-2"></i> Gửi khảo sát');
                }
            });
        });

    // Hàm điều khiển trang khảo sát
    let currentPage = 1;
    const totalPages = $('.survey-page').length;

// Biến kiểm tra trạng thái
var isCaptchaRendered = false;

var onloadCallback = function() {
    console.log("grecaptcha is ready!");
};

function updateNavigationButtons() {
    // Ẩn/hiện nút Quay lại
    $('#prevBtn').toggle(currentPage > 1);
    $('#prev-placeholder').toggle(currentPage <= 1);

    if (currentPage === totalPages) {
        $('#nextBtn').hide();
        $('#submitBtn').show();
        
        // Hiện container trước
        $('#captcha-container').show(); 

        if (!isCaptchaRendered) {
            grecaptcha.render('html_element', {
                'sitekey' : "{{ env('RECAPTCHA_SITE_KEY') }}" 
            });
            isCaptchaRendered = true;
        }

    } else {
        $('#nextBtn').show();
        $('#captcha-container').hide();
        $('#submitBtn').hide();
    }
}

    function goToPage(pageNumber) {
        if (pageNumber < 1 || pageNumber > totalPages) return;

        let isValid = true;

        const checkedNames = new Set();
        $(`#survey-page-${currentPage} [required]:visible`).each(function() {
            const $input = $(this);
            const inputType = $input.attr('type');
            const inputName = $input.attr('name');

            if ((inputType === 'radio' || inputType === 'checkbox') && !checkedNames.has(inputName)) {
                checkedNames.add(inputName);
                if (
                    $(`#survey-page-${currentPage} input[name="${inputName}"]:checked`).length === 0 &&
                    pageNumber > currentPage
                ) {
                    isValid = false;
                    return false;
                }
            } else if (inputType !== 'radio' && inputType !== 'checkbox') {
                if (!this.checkValidity() && pageNumber > currentPage) {
                    isValid = false;
                }
            }
        });

        if (!isValid) {
            alert('Vui lòng hoàn thành tất cả các câu hỏi bắt buộc trong trang này trước khi tiếp tục.');
            return;
        }

        $('.survey-page').hide();
        $(`#survey-page-${pageNumber}`).fadeIn();
        currentPage = pageNumber;
        updateNavigationButtons();
        const container = document.getElementById('survey-pages-container');
        if (container) {
            container.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Gắn sự kiện
    $('#nextBtn').on('click', () => goToPage(currentPage + 1));
    $('#prevBtn').on('click', () => goToPage(currentPage - 1));

    updateNavigationButtons();
    });
</script>
@endpush
@endsection