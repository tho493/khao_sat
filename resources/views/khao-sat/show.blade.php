@extends('layouts.home')

@section('title','Khảo sát ' . $dotKhaoSat->ten_dot)

@push('styles')
<style>
    .progress-section {
        position: sticky;
        top: 80px;
    }
    
    input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="date"],
        textarea,
        select,
        .form-input,
        .form-textarea {
            border: 1px solid #d1d5db !important;
        }
</style>
@endpush


@section('content')
    <div class="container mx-auto py-8 px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Main Content -->
            <div class="w-full lg:w-2/3">
                <div class="flex items-center justify-between mb-4">
                <!-- Breadcrumb navigation -->
                    <nav class="flex items-center text-sm text-gray-500 mb-2" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ url('/') }}" class="inline-flex items-center text-gray-500 hover:text-blue-600">
                                    <i class="bi bi-house-door-fill mr-1"></i> Trang chủ
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="bi bi-chevron-right mx-1"></i>
                                    <a href="{{ route('khao-sat.index') }}" class="text-gray-500 hover:text-blue-600">
                                        Khảo sát
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="bi bi-chevron-right mx-1"></i>
                                    <span class="text-blue-700 font-semibold" aria-current="page">
                                        {{ $dotKhaoSat->ten_dot }}
                                    </span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <!-- <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition" onclick="history.back()">
                        <i class="bi bi-arrow-left mr-2"></i> Quay lại
                    </button> -->
                </div>
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold mb-2">{{ $dotKhaoSat->ten_dot }}</h2>
                        <p class="text-gray-500 flex items-center gap-2 mb-2">
                            <i class="bi bi-tag"></i> {{ $mauKhaoSat->ten_mau }} | 
                            <i class="bi bi-calendar"></i> Hạn cuối: {{ \Carbon\Carbon::parse($dotKhaoSat->denngay)->format('d/m/Y') }}
                        </p>
                        @if($dotKhaoSat->mota)
                            <p class="text-gray-700">{{ $dotKhaoSat->mota }}</p>
                        @endif
                    </div>
                </div>

                <form id="formKhaoSat" method="POST" action="{{ route('khao-sat.store', $dotKhaoSat) }}">
                    @csrf
                    {!! \App\Http\Middleware\PreventDoubleSubmissions::tokenField() !!}
                    
                    <!-- Thông tin người trả lời -->
                    <div class="bg-white shadow rounded-lg mb-6">
                        <div class="bg-blue-600 rounded-t-lg px-6 py-3">
                            <h5 class="text-white font-semibold m-0">Thông tin của bạn</h5>
                        </div>
                        <div class="p-6">
                            <div class="flex flex-wrap -mx-2">
                                <div class="w-full md:w-1/2 px-2 mb-4">
                                    <label class="block font-medium mb-1">
                                        Mã số <span class="text-red-600">*</span>
                                    </label>
                                    <input type="text" class="form-input w-full rounded focus:ring-blue-500 focus:border-blue-500" name="ma_nguoi_traloi" required>
                                    <small class="text-gray-500">Mã sinh viên, mã nhân viên, mã doanh nghiệp...</small>
                                </div>
                                <div class="w-full md:w-1/2 px-2 mb-4">
                                    <label class="block font-medium mb-1">
                                        Họ và tên <span class="text-red-600">*</span>
                                    </label>
                                    <input type="text" class="form-input w-full rounded focus:ring-blue-500 focus:border-blue-500" name="metadata[hoten]" required>
                                </div>
                                <div class="w-full md:w-1/2 px-2 mb-4">
                                    <label class="block font-medium mb-1">Đơn vị/Khoa</label>
                                    <input type="text" class="form-input w-full rounded focus:ring-blue-500 focus:border-blue-500" name="metadata[donvi]">
                                </div>
                                <div class="w-full md:w-1/2 px-2 mb-4">
                                    <label class="block font-medium mb-1">Email</label>
                                    <input type="email" class="form-input w-full rounded focus:ring-blue-500 focus:border-blue-500" name="metadata[email]">
                                </div>
                                <input type="hidden" name="metadata[thoigian_batdau]" id="thoigian_batdau">
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        // Set current datetime in ISO format for datetime-local (without seconds)
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
                                    });
                                </script>
                            </div>
                        </div>
                    </div>

                    <!-- Câu hỏi khảo sát -->
                    @php $questionNumber = 0; @endphp
                    @if(!empty($mauKhaoSat->cauHoi) && is_iterable($mauKhaoSat->cauHoi))
                        @foreach($mauKhaoSat->cauHoi as $cauHoi)
                            @php $questionNumber++; @endphp
                            <div class="question-card bg-white shadow rounded-lg mb-4 border-l-4 border-blue-600" data-question-id="{{ $cauHoi->id }}">
                                <div class="p-6">
                                    <label class="block font-medium mb-2">
                                        <strong>Câu {{ $questionNumber }}:</strong>
                                        {{ $cauHoi->noidung_cauhoi }}
                                        @if($cauHoi->batbuoc)
                                            <span class="text-red-600">*</span>
                                        @endif
                                    </label>

                                    @switch($cauHoi->loai_cauhoi)
                                        @case('single_choice')
                                            <div class="mt-2 space-y-2">
                                                @if(!empty($cauHoi->phuongAnTraLoi) && is_iterable($cauHoi->phuongAnTraLoi))
                                                    @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                        <div class="flex items-center">
                                                            <input class="form-radio text-blue-600 focus:ring-blue-500" type="radio"
                                                                   name="cau_tra_loi[{{ $cauHoi->id }}]"
                                                                   value="{{ $phuongAn->id }}"
                                                                   id="pa_{{ $phuongAn->id }}"
                                                                   {{ $cauHoi->batbuoc ? 'required' : '' }}>
                                                            <label class="ml-2" for="pa_{{ $phuongAn->id }}">
                                                                {{ $phuongAn->noidung }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                            @break

                                        @case('multiple_choice')
                                            <div class="mt-2 space-y-2">
                                                @if(!empty($cauHoi->phuongAnTraLoi) && is_iterable($cauHoi->phuongAnTraLoi))
                                                    @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                        <div class="flex items-center">
                                                            <input class="form-checkbox text-blue-600 focus:ring-blue-500" type="checkbox"
                                                                   name="cau_tra_loi[{{ $cauHoi->id }}][]"
                                                                   value="{{ $phuongAn->id }}"
                                                                   id="pa_{{ $phuongAn->id }}">
                                                            <label class="ml-2" for="pa_{{ $phuongAn->id }}">
                                                                {{ $phuongAn->noidung }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                            @break

                                        @case('text')
                                            <textarea class="form-textarea mt-2 w-full rounded focus:ring-blue-500 focus:border-blue-500"
                                                  name="cau_tra_loi[{{ $cauHoi->id }}]"
                                                  rows="3"
                                                  placeholder="Nhập câu trả lời của bạn..."
                                                  {{ $cauHoi->batbuoc ? 'required' : '' }}></textarea>
                                            @break

                                        @case('likert')
                                            <div class="flex justify-between items-center mt-3 gap-2">
                                                @if(!empty($cauHoi->phuongAnTraLoi) && is_iterable($cauHoi->phuongAnTraLoi))
                                                    @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                        <div class="flex flex-col items-center flex-1">
                                                            <input type="radio" class="form-radio text-blue-600 focus:ring-blue-500"
                                                                   name="cau_tra_loi[{{ $cauHoi->id }}]"
                                                                   value="{{ $phuongAn->id }}"
                                                                   id="pa_{{ $phuongAn->id }}"
                                                                   {{ $cauHoi->batbuoc ? 'required' : '' }}>
                                                            <label class="mt-1 text-xs" for="pa_{{ $phuongAn->id }}">
                                                                {{ $phuongAn->noidung }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                            @break

                                        @case('rating')
                                            <div class="mt-2">
                                                <div class="flex space-x-2">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <input type="radio" class="btn-check" 
                                                            name="cau_tra_loi[{{ $cauHoi->id }}]" 
                                                            value="{{ $i }}" {{-- Giá trị là số --}}
                                                            id="rating_{{ $cauHoi->id }}_{{ $i }}"
                                                            {{ $cauHoi->batbuoc ? 'required' : '' }}>
                                                        <label class="btn btn-outline-primary" 
                                                            for="rating_{{ $cauHoi->id }}_{{ $i }}">
                                                            {{ $i }}
                                                        </label>
                                                    @endfor
                                                </div>
                                                <small class="text-gray-500 block mt-1">1 = Rất không hài lòng, 5 = Rất hài lòng</small>
                                            </div>
                                            @break

                                        @case('date')
                                            <input type="date" class="form-input mt-2 w-full rounded focus:ring-blue-500 focus:border-blue-500"
                                                   name="cau_tra_loi[{{ $cauHoi->id }}]"
                                                   {{ $cauHoi->batbuoc ? 'required' : '' }}>
                                            @break

                                        @case('number')
                                            <input type="number" class="form-input mt-2 w-full rounded focus:ring-blue-500 focus:border-blue-500"
                                                   name="cau_tra_loi[{{ $cauHoi->id }}]"
                                                   placeholder="Nhập số..."
                                                   {{ $cauHoi->batbuoc ? 'required' : '' }}>
                                            @break
                                    @endswitch
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <div class="mb-4 flex justify-center">
                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div> 
                    </div>

                    <!-- Submit button -->
                    <div class="flex justify-center my-8 gap-4">
                        <button type="button" class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition" onclick="history.back()">
                            <i class="bi bi-arrow-left mr-2"></i> Quay lại
                        </button>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition" id="submitBtn">
                            <i class="bi bi-send mr-2"></i> Gửi khảo sát
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar Progress -->
            <div class="w-full lg:w-1/3">
                <div class="progress-section">
                    <div class="bg-white shadow rounded-lg mb-4">
                        <div class="p-6 flex flex-col items-center">
                            <h6 class="font-semibold mb-4">Thời gian làm khảo sát</h6>
                            <div class="text-3xl font-bold text-blue-600" id="survey-timer">00:00</div>
                            <small class="text-gray-500 mt-2">Thời gian bạn đã làm khảo sát, tính từ khi mở khảo sát này</small>
                        </div>
                    </div>
                    <script>
                        let secondsElapsed = 0;
                        function pad(n) { return n < 10 ? '0' + n : n; }

                        function updateTimer() {
                            const el = document.getElementById('survey-timer');
                            if (!el) return;
                            secondsElapsed++;
                            const minutes = Math.floor(secondsElapsed / 60);
                            const seconds = secondsElapsed % 60;
                            el.textContent = pad(minutes) + ':' + pad(seconds);
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            setInterval(updateTimer, 1000);
                        });
                    </script>
                
                    <div class="bg-white shadow rounded-lg mb-4">
                        <div class="p-6">
                            <h6 class="font-semibold mb-4">Tiến độ hoàn thành</h6>
                            <div class="w-full bg-gray-200 rounded-full h-6 mb-3 overflow-hidden">
                                <div class="bg-blue-600 h-6 rounded-full flex items-center justify-center text-white text-sm font-semibold transition-all duration-300"
                                     id="progressBar" style="width: 0%;">0%</div>
                            </div>
                            <p class="text-gray-500 text-sm mb-0">
                                Đã trả lời: <span id="answeredCount">0</span>/{{ $questionNumber + 2}} câu
                            </p>
                        </div>
                    </div>

                    <div class="bg-white shadow rounded-lg mt-4">
                        <div class="p-6">
                            <h6 class="font-semibold mb-2">Lưu ý</h6>
                            <ul class="text-sm text-gray-700 list-disc pl-5 mb-0">
                                <li>Câu hỏi có dấu <span class="text-red-600">*</span> là bắt buộc</li>
                                <li>Vui lòng kiểm tra kỹ trước khi gửi</li>
                                <li>Mỗi người chỉ được tham gia 1 lần</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Update progress
            function updateProgress() {
                const totalQuestions = $('.question-card').length + 2;
                let answeredQuestions = 0;

                // Kiểm tra trạng thái mã số
                const maSoInput = $('input[name="ma_nguoi_traloi"]');
                if (maSoInput.length && maSoInput.val().trim() !== '') {
                    answeredQuestions++;
                    maSoInput.removeClass('border-red-500').addClass('border-green-500');
                } else {
                    maSoInput.removeClass('border-green-500').addClass('border-red-500');
                }

                // Kiểm tra trạng thái họ tên
                const hoTenInput = $('input[name="metadata[hoten]"]');
                if (hoTenInput.length && hoTenInput.val().trim() !== '') {
                    answeredQuestions++;
                    hoTenInput.removeClass('border-red-500').addClass('border-green-500');
                } else {
                    hoTenInput.removeClass('border-green-500').addClass('border-red-500');
                }

                // Kiểm tra trạng thái các câu hỏi
                $('.question-card').each(function() {
                    const questionId = $(this).data('question-id');
                    const inputs = $(this).find('input[name^="cau_tra_loi"], textarea[name^="cau_tra_loi"]');

                    let isAnswered = false;
                    inputs.each(function() {
                        if ($(this).is(':checkbox') || $(this).is(':radio')) {
                            if ($(this).is(':checked')) {
                                isAnswered = true;
                            }
                        } else if ($(this).val().trim() !== '') {
                            isAnswered = true;
                        }
                    });

                    if (isAnswered) {
                        answeredQuestions++;
                        $(this).removeClass('border-red-500').addClass('border-green-500');
                    } else {
                        $(this).removeClass('border-green-500').removeClass('border-red-500');
                    }
                });

                const progress = Math.round((answeredQuestions / totalQuestions) * 100);
                $('#progressBar').css('width', progress + '%').text(progress + '%');
                $('#answeredCount').text(answeredQuestions);
            }
            
            // Update progress on input change
            $('input, textarea').on('change keyup', updateProgress);
            
            // Form submission
            $('#formKhaoSat').on('submit', function(e) {
                e.preventDefault();
                
                // Validate required fields
                let isValid = true;
                $(this).find('[required]').each(function() {
                    if ($(this).is(':radio')) {
                        const name = $(this).attr('name');
                        if (!$(`input[name="${name}"]:checked`).length) {
                            isValid = false;
                            $(this).closest('.question-card').removeClass('border-green-500').addClass('border-red-500');
                        }
                    } else if (!$(this).val()) {
                        isValid = false;
                        $(this).closest('.question-card').removeClass('border-green-500').addClass('border-red-500');
                    }
                });
                
                if (!isValid) {
                    alert('Vui lòng trả lời tất cả câu hỏi bắt buộc!');
                    return;
                }
                
                // Disable submit button
                $('#submitBtn').prop('disabled', true)
                    .html('<span class="animate-spin mr-2 border-2 border-t-2 border-blue-600 border-t-transparent rounded-full w-4 h-4 inline-block align-middle"></span>Đang gửi...');
                
                // Submit form
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirect;
                        }
                    },
                    error: function(xhr) {
                        alert('Có lỗi xảy ra: ' + (xhr.responseJSON?.message || 'Vui lòng thử lại'));
                        $('#submitBtn').prop('disabled', false)
                            .html('<i class="bi bi-send mr-2"></i> Gửi khảo sát');
                    }
                });
            });
            
            // Initialize progress
            updateProgress();
        });
    </script>
    <script src="/js/autosave.js"></script>
@endsection