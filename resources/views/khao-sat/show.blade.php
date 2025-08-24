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
</style>
@endpush

@section('content')
    <div class="container mx-auto py-12 px-4 bg-gradient-to-br from-blue-200 to-slate-50 -z-10">
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
                    <p class="text-slate-500">
                        Hạn cuối: {{ \Carbon\Carbon::parse($dotKhaoSat->denngay)->format('d/m/Y') }}
                    </p>
                </div>

                <form id="formKhaoSat" method="POST" action="{{ route('khao-sat.store', $dotKhaoSat) }}" class="space-y-6">
                    @csrf
                    {!! \App\Http\Middleware\PreventDoubleSubmissions::tokenField() !!}
                    
                    <!-- Thông tin người trả lời -->
                    <div class="glass-effect">
                        <div class="bg-white/40 rounded-t-xl px-6 py-4 border-b border-white/30">
                            <h5 class="text-slate-800 font-bold text-lg m-0">Thông tin của bạn</h5>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <label class="block font-medium mb-1 text-slate-700">Mã số <span class="text-red-600">*</span></label>
                                <input type="text" class="form-input w-full rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500" name="ma_nguoi_traloi" required>
                            </div>
                            <div>
                                <label class="block font-medium mb-1 text-slate-700">Họ và tên <span class="text-red-600">*</span></label>
                                <input type="text" class="form-input w-full rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500" name="metadata[hoten]" required>
                            </div>
                            <div>
                                <label class="block font-medium mb-1 text-slate-700">Đơn vị/Khoa</label>
                                <input type="text" class="form-input w-full rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500" name="metadata[donvi]">
                            </div>
                            <div>
                                <label class="block font-medium mb-1 text-slate-700">Email</label>
                                <input type="email" class="form-input w-full rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500" name="metadata[email]">
                            </div>
                        </div>
                    </div>

                    <!-- Các card câu hỏi -->
                    @php $questionCounter = 0; @endphp
                    @forelse($mauKhaoSat->cauHoi->sortBy('thutu') as $cauHoi)
                        @php $questionCounter++; @endphp
                        <div class="question-card glass-effect" data-question-id="{{ $cauHoi->id }}">
                            <div class="p-6">
                                <label class="block font-bold text-slate-800 mb-3 text-lg">
                                    <span class="text-blue-600">Câu {{ $questionCounter }}:</span>
                                    {{ $cauHoi->noidung_cauhoi }}
                                    @if($cauHoi->batbuoc)
                                        <span class="text-red-600">*</span>
                                    @endif
                                </label>

                                @switch($cauHoi->loai_cauhoi)
                                    @case('single_choice')
                                        <div class="mt-2 space-y-3">
                                            @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                <label class="flex items-center p-3 rounded-lg bg-white/30 hover:bg-white/50 cursor-pointer transition">
                                                    <input type="radio" class="form-radio h-5 w-5 text-blue-600 focus:ring-blue-500 border-slate-400"
                                                           name="cau_tra_loi[{{ $cauHoi->id }}]" value="{{ $phuongAn->id }}"
                                                           {{ $cauHoi->batbuoc ? 'required' : '' }}>
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
                                                  {{ $cauHoi->batbuoc ? 'required' : '' }}></textarea>
                                        @break
                                    
                                     @case('likert')
                                        <div class="flex flex-wrap justify-between items-center mt-3 gap-2">
                                            @foreach($cauHoi->phuongAnTraLoi as $phuongAn)
                                                <label class="flex flex-col items-center flex-1 p-2 rounded-lg hover:bg-white/50 cursor-pointer transition min-w-[80px]">
                                                    <input type="radio" class="form-radio h-5 w-5 text-blue-600 focus:ring-blue-500"
                                                        name="cau_tra_loi[{{ $cauHoi->id }}]"
                                                        value="{{ $phuongAn->id }}"
                                                        {{ $cauHoi->batbuoc ? 'required' : '' }}>
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
                                                            {{ $cauHoi->batbuoc ? 'required' : '' }}>
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
                                            {{ $cauHoi->batbuoc ? 'required' : '' }}>
                                        @break

                                    @case('number')
                                        <input type="number" class="form-input mt-2 w-full  rounded-lg bg-white/50 border-slate-300 focus:ring-blue-500 focus:border-blue-500"
                                            name="cau_tra_loi[{{ $cauHoi->id }}]"
                                            placeholder="Nhập số..."
                                            {{ $cauHoi->batbuoc ? 'required' : '' }}>
                                        @break

                                @endswitch
                            </div>
                        </div>
                    @empty
                        <div class="glass-effect p-6 text-center text-slate-600">
                            Mẫu khảo sát này chưa có câu hỏi nào.
                        </div>
                    @endforelse

                     <!-- Captcha và nút Submit -->
                    <div class="glass-effect p-6">
                        <div class="mb-4 flex justify-center">
                            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div> 
                        </div>
                        <div class="flex justify-center gap-4">
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-lg font-semibold" id="submitBtn">
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
                                 id="progressBar" style="width: 0%;">0%</div>
                        </div>
                        <p class="text-slate-600 text-sm mb-0">
                            Đã trả lời: <span id="answeredCount">0</span>/{{ $questionCounter + 2 }} câu
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

    
@push('scripts')
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
                        location.reload(true); // submit rồi thì cần reload lại key submit
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
    <script>
        // Cập nhật hiển thị thời gian làm bài
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
@endpush
@endsection