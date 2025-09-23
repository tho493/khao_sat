@extends('layouts.home')

@section('title','Khảo sát ' . $dotKhaoSat->ten_dot)

@push('styles')
<style>
    .progress-section {
        position: sticky;
        top: 100px;
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
        ->mapWithKeys(function ($item) {
            $condition = json_decode($item->dieukien_hienthi, true);
            return [$item->id => [
                'parentId' => $item->cau_dieukien_id,
                'requiredValue' => (string)($condition['value'] ?? null),
                'isOriginallyRequired' => (bool)$item->batbuoc
            ]];
        });
    $questionCounterGlobal = 0;
@endphp

@section('content')
    <div class="container mx-auto py-12 px-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8 xl:gap-12">

                <!-- Nội dung khảo sát -->
                <div class="w-full lg:w-2/3 space-y-6">
                    
                    {{-- Header của khảo sát --}}
                    <div class="glass-effect p-6 text-center">
                        <nav class="text-sm text-slate-600 mb-4">
                            <a href="{{ url('/') }}" class="hover:text-blue-700">Trang chủ</a>
                            <span class="mx-2">/</span>
                            <a href="{{ route('khao-sat.index') }}" class="hover:text-blue-700">Khảo sát</a>
                            <span class="mx-2">/</span>
                            <span class="font-semibold text-slate-800">{{ Str::limit($dotKhaoSat->ten_dot, 30) }}</span>
                        </nav>
                        <h1 class="text-3xl font-extrabold text-slate-800 mb-2">{{ $dotKhaoSat->ten_dot }}</h1>
                        <h3 class="text-xl font-extrabold text-slate-700 mb-2">{{ $dotKhaoSat->mota ? $dotKhaoSat->mota : "Khảo sát này không có mô tả" }}</h3>
                        <p class="text-slate-500">
                            Hạn cuối: {{ \Carbon\Carbon::parse($dotKhaoSat->denngay)->format('d/m/Y') }}
                        </p>
                    </div>

                    <form id="formKhaoSat" method="POST" action="{{ route('khao-sat.store', $dotKhaoSat) }}" class="space-y-6">
                        @csrf
                        {!! \App\Http\Middleware\PreventDoubleSubmissions::tokenField() !!}
                        <input type="hidden" name="metadata[thoigian_batdau]" id="thoigian_batdau">

                        <!-- Thông tin người trả lời -->
                        <div class="glass-effect">
                            <div class="bg-white/40 rounded-t-xl px-6 py-4 border-b border-white/30">
                                <h5 class="text-slate-800 font-bold text-lg m-0">Thông tin của bạn</h5>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div>
                                    <label class="block font-medium mb-1 text-slate-700">Mã số <span class="text-red-600">*</span></label>
                                    <input type="text" class="form-input w-full rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500" name="ma_nguoi_traloi" required>
                                    @error('ma_nguoi_traloi')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block font-medium mb-1 text-slate-700">Họ và tên <span class="text-red-600">*</span></label>
                                    <input type="text" class="form-input w-full rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500" name="metadata[hoten]" required>
                                    @error('ma_nguoi_traloi')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block font-medium mb-1 text-slate-700">Đơn vị/Khoa</label>
                                    <input type="text" class="form-input w-full rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500" name="metadata[donvi]">
                                    @error('ma_nguoi_traloi')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block font-medium mb-1 text-slate-700">Email</label>
                                    <input type="email" class="form-input w-full rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500" name="metadata[email]">
                                    @error('ma_nguoi_traloi')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div id="survey-pages-container">
                            @forelse($questionsByPage as $pageNumber => $questionsOnPage)
                                <div class="survey-page" id="survey-page-{{ $pageNumber }}" style="{{ !$loop->first ? 'display: none;' : '' }}">
                                    <div class="glass-effect">
                                        <div class="bg-white/40 rounded-t-xl px-6 py-4 border-b border-white/30">
                                            <h5 class="text-slate-800 font-bold text-lg m-0">Phần {{ $pageNumber }} / {{ $questionsByPage->count() }}</h5>
                                        </div>
                                        <div class="p-6 space-y-6">
                                            @foreach($questionsOnPage as $cauHoi)
                                                @php
                                                    $questionCounterGlobal++;
                                                    $isConditionalChild = isset($conditionalMap[$cauHoi->id]);
                                                    $isRequired = $cauHoi->batbuoc && !$isConditionalChild;
                                                @endphp
                                                <div class="question-card bg-white/30 p-4 rounded-lg border border-white/30"
                                                     id="question-{{ $cauHoi->id }}"
                                                     data-question-id="{{ $cauHoi->id }}"
                                                     data-originally-required="{{ $cauHoi->batbuoc ? 'true' : 'false' }}"
                                                     @if($isConditionalChild)
                                                        data-conditional-parent-id="{{ $conditionalMap[$cauHoi->id]['parentId'] }}"
                                                        data-conditional-required-value="{{ $conditionalMap[$cauHoi->id]['requiredValue'] }}"
                                                     @endif>
                                                    
                                                    <label class="block font-bold text-slate-800 mb-3 text-lg">
                                                        <span class="text-blue-600">Câu {{ $questionCounterGlobal }}:</span>
                                                        {{ $cauHoi->noidung_cauhoi }}
                                                        @if($cauHoi->batbuoc)<span class="text-red-600">*</span>@endif
                                                    </label>
                                                    @switch($cauHoi->loai_cauhoi)
                                                        @case('single_choice')
                                                            <div class="mt-2 space-y-3">
                                                                @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                                    <label class="flex items-center p-3 rounded-lg bg-white/30 hover:bg-white/50 cursor-pointer transition">
                                                                        <input type="radio" class="form-radio h-5 w-5 text-blue-600 focus:ring-blue-500 border-slate-400"
                                                                            name="cau_tra_loi[{{ $cauHoi->id }}]" value="{{ $phuongAn->id }}"
                                                                            {{ $isRequired ? 'required' : '' }}>
                                                                        <span class="ml-3 text-slate-700">{{ $phuongAn->noidung }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                            @break
                                                        
                                                        @case('multiple_choice')
                                                            <div class="mt-2 space-y-3">
                                                                @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                                    <label class="flex items-center p-3 rounded-lg bg-white/30 hover:bg-white/50 cursor-pointer transition">
                                                                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 focus:ring-blue-500 rounded border-slate-400"
                                                                            name="cau_tra_loi[{{ $cauHoi->id }}][]" value="{{ $phuongAn->id }}">
                                                                        <span class="ml-3 text-slate-700">{{ $phuongAn->noidung }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                            @break
                                                            
                                                        @case('text')
                                                            <textarea class="form-textarea mt-2 w-full rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500"
                                                                    name="cau_tra_loi[{{ $cauHoi->id }}]" rows="4"
                                                                    placeholder="Nhập câu trả lời của bạn..."
                                                                    {{ $isRequired ? 'required' : '' }}></textarea>
                                                            @break
                                                        
                                                        @case('likert')
                                                            <div class="flex flex-wrap justify-between items-center mt-3 gap-2">
                                                                @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                                    <label class="flex flex-col items-center flex-1 p-2 rounded-lg hover:bg-white/50 cursor-pointer transition min-w-[80px]">
                                                                        <input type="radio" class="form-radio h-5 w-5 text-blue-600 focus:ring-blue-500"
                                                                            name="cau_tra_loi[{{ $cauHoi->id }}]"
                                                                            value="{{ $phuongAn->id }}"
                                                                            {{ $isRequired ? 'required' : '' }}>
                                                                        <span class="mt-2 text-xs text-center text-slate-600">{{ $phuongAn->noidung }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                            @break
                
                                                        @case('rating')
                                                            <div class="mt-3">
                                                                <div class="flex items-center justify-center space-x-2" role="group">
                                                                    @for($i = 1; $i <= 5; $i++)
                                                                        <div class="rating-item">
                                                                            <input type="radio" class="sr-only peer" 
                                                                                name="cau_tra_loi[{{ $cauHoi->id }}]" 
                                                                                value="{{ $i }}"
                                                                                id="rating_{{ $cauHoi->id }}_{{ $i }}"
                                                                                {{ $isRequired ? 'required' : '' }}>
                                                                            
                                                                            <label for="rating_{{ $cauHoi->id }}_{{ $i }}"
                                                                                class="flex items-center justify-center w-12 h-12 rounded-full border border-slate-300 bg-white/40
                                                                                        cursor-pointer transition text-slate-600 font-bold text-lg
                                                                                        hover:bg-blue-200 hover:border-blue-400
                                                                                        peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600">
                                                                                {{ $i }}
                                                                            </label>
                                                                        </div>
                                                                    @endfor
                                                                </div>
                                                                <div class="flex justify-between text-xs text-slate-500 mt-2 px-1">
                                                                    <span>Rất không hài lòng</span>
                                                                    <span>Rất hài lòng</span>
                                                                </div>
                                                            </div>
                                                            @break
                
                                                        @case('date')
                                                            <input type="date" class="form-input mt-2 w-full md:w-1/2 rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500"
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
                        <div class="glass-effect p-6">
                            {{-- Captcha --}}
                            <div id="captcha-container" class="mb-4 flex justify-center" style="display: none;">
                                <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div> 
                            </div>
                            
                            {{-- Nút điều hướng --}}
                            <div class="flex justify-between items-center">
                                <button type="button" class="btn-nav btn-prev inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition" id="prevBtn" style="display: none;">
                                    <i class="bi bi-arrow-left mr-2"></i> Quay lại
                                </button>
                                
                                {{-- Placeholder để giữ layout cân bằng --}}
                                <div id="prev-placeholder"></div>

                                <button type="button" class="btn-nav btn-next inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition" id="nextBtn">
                                    Tiếp theo <i class="bi bi-arrow-right ml-2"></i>
                                </button>
                                
                                <button type="submit" class="inline-flex items-center px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-lg font-semibold" id="submitBtn" style="display: none;">
                                    <i class="bi bi-send mr-2"></i> Gửi khảo sát
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Sidebar Progress -->
                <div class="w-full lg:w-1/3">
                    <div class="progress-section space-y-6">
                        <!-- Thời gian -->
                        <div class="glass-effect p-6 flex flex-col items-center">
                            <h6 class="font-bold text-slate-800 mb-3">Thời gian làm bài</h6>
                            <div class="text-4xl font-extrabold text-blue-600" id="survey-timer">00:00</div>
                        </div>
                    
                        <!-- Tiến độ -->
                        <div class="glass-effect p-6">
                            <h6 class="font-bold text-slate-800 mb-4">Tiến độ hoàn thành</h6>
                            <div class="w-full bg-white/40 rounded-full h-6 mb-3 overflow-hidden border border-white/50">
                                <div class="bg-blue-600 h-6 rounded-full flex items-center justify-center text-white text-sm font-semibold transition-all duration-300"
                                    id="progressBar" style="width: 0%;"></div>
                            </div>
                            <p class="text-slate-600 text-sm mb-0">
                                Đã trả lời: <span id="answeredCount">0</span> câu
                            </p>
                        </div>

                        <!-- Lưu ý -->
                        <div class="glass-effect p-6">
                            <h6 class="font-bold text-slate-800 mb-2">Lưu ý</h6>
                            <ul class="text-sm text-slate-700 list-disc pl-5 space-y-1 mb-0">
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
        const totalQuestions = $('.question-card').length + 2; // +2 cho Mã số và Họ tên
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
        
        function updateProgress() {
            let answeredQuestions = 0;

            surveyForm.find('input[name="ma_nguoi_traloi"][required]').each(function() {
                if ($(this).val().trim() !== '') answeredQuestions++;
            });
            surveyForm.find('input[name="metadata[hoten]"][required]').each(function() {
                if ($(this).val().trim() !== '') answeredQuestions++;
            });
            
            $('.question-card').each(function() {
                const inputs = $(this).find('input[name^="cau_tra_loi"], textarea[name^="cau_tra_loi"]');
                let isAnswered = false;
                inputs.each(function() {
                    if (($(this).is(':radio') || $(this).is(':checkbox'))) {
                        if ($(this).is(':checked')) isAnswered = true;
                    } else {
                        if ($(this).val().trim() !== '') isAnswered = true;
                    }
                });
                if (isAnswered) answeredQuestions++;
            });

            const progress = totalQuestions > 0 ? Math.round((answeredQuestions / totalQuestions) * 100) : 0;
            $('#progressBar').css('width', progress + '%').text(progress + '%');
            $('#answeredCount').text(`${answeredQuestions}/${totalQuestions}`);
        }

        function checkAllConditions() {
            $('.question-card[data-conditional-parent-id]').each(function() {
                const childCard = $(this);
                const parentId = childCard.data('conditional-parent-id');
                const requiredValue = String(childCard.data('conditional-required-value'));
                const isOriginallyRequired = childCard.data('originally-required');

                const parentCheckedInput = $(`#question-${parentId} input:checked`);
                let parentSelectedValue = parentCheckedInput.length ? parentCheckedInput.val() : null;

                let shouldShow = false;
                if (parentSelectedValue !== null && String(parentSelectedValue) === requiredValue) {
                    shouldShow = true;
                }

                const childInputs = childCard.find('input[name^="cau_tra_loi"], textarea[name^="cau_tra_loi"]');

                if (shouldShow) {
                    if (!childCard.is(':visible')) {
                        childCard.slideDown(300);
                    }
                    if (isOriginallyRequired) {
                        childInputs.prop('required', true);
                    }
                } else {
                    if (childCard.is(':visible')) {
                        childCard.slideUp(300, function() {
                            clearQuestionValues($(this));
                        });
                    }
                    childInputs.prop('required', false);
                }
            });
            
            if (typeof updateProgress === 'function') {
                updateProgress();
            }
        }


        /**
         * Helper: Xóa câu trả lời của một card câu hỏi.
         * @param {jQuery} questionCard - Đối tượng jQuery của .question-card
         */
        function clearQuestionValues(questionCard) {
            questionCard.find('input:checked').prop('checked', false);
            questionCard.find('textarea, input[type="text"], input[type="number"], input[type="date"]').val('');
        }

        // Chạy lần đầu khi load
        loadProgress();
        checkAllConditions();
        updateProgress();
        $('.question-card[data-conditional-parent-id]').hide();
        
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
            $('#survey-timer').text(pad(minutes) + ':' + pad(seconds));
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

    function updateNavigationButtons() {
        // Ẩn/hiện nút Quay lại
        $('#prevBtn').toggle(currentPage > 1);
        $('#prev-placeholder').toggle(currentPage <= 1);

        if (currentPage === totalPages) {
            $('#nextBtn').hide();
            $('#captcha-container').show();
            $('#submitBtn').show();
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
        $(`#survey-page-${currentPage} [required]`).each(function() {
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
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Gắn sự kiện
    $('#nextBtn').on('click', () => goToPage(currentPage + 1));
    $('#prevBtn').on('click', () => goToPage(currentPage - 1));

    updateNavigationButtons();
    });
</script>
@endpush
@endsection