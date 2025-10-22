@extends('layouts.home')
@section('title', 'Kết quả khảo sát của bạn')

@section('content')
    <style>
        :root {
            --primary: #0d6efd;
            --primary-100: #e7f0ff;
            --text: #1f2937;
            --muted: #6b7280;
            --card: #ffffff;
            --border: #e5e7eb;
        }

        .review-wrap {
            background: #f5f9ff;
            min-height: 100vh;
            padding: 24px 0;
            color: var(--text);

            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .review-wrap .container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 0 16px;
        }

        .card-clean {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .04);
            overflow: hidden;
            margin: 0 auto;
            width: 100%;
        }

        .head {
            background: var(--primary);
            color: #fff;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            text-align: left;
        }

        .head .icon {
            font-size: 36px;
            line-height: 1;
            margin: 0;
            flex-shrink: 0;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, .18));
        }

        .head .title-wrap {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .head h1 {
            font-size: 20px;
            margin: 0;
            font-weight: 800;
            letter-spacing: .2px;
        }

        .head .subtitle {
            opacity: .95;
            font-size: 13px;
            margin: 0;
            font-weight: 600;
        }

        .head .kpis {
            margin-left: auto;
            display: grid;
            grid-auto-flow: column;
            gap: 8px;
        }

        .kpi-pill {
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            min-width: 110px;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .26);
            text-align: center;
            backdrop-filter: saturate(120%);
        }

        .kpi-pill .num {
            font-size: 16px;
            font-weight: 800;
            line-height: 1.1;
            color: #fff;
        }

        .kpi-pill .lbl {
            font-size: 11px;
            opacity: .9;
        }

        .block {
            padding: 20px;
        }

        .block-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .info-item {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px;
            background: #fff;
        }

        .info-item h6 {
            font-size: 12px;
            color: var(--muted);
            margin: 0 0 4px 0;
            font-weight: 600;
        }

        .info-item h5 {
            font-size: 16px;
            margin: 0;
            font-weight: 700;
            color: var(--text);
            word-break: break-word;
        }

        .table-clean {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }

        .table-clean td {
            padding: 10px 12px;
            border-top: 1px solid var(--border);
            vertical-align: top;
            font-size: 14px;
        }

        .table-clean tr:first-child td {
            border-top: none;
        }

        .table-clean td:first-child {
            width: 40%;
            background: var(--primary-100);
            font-weight: 600;
        }

        .table-clean td:last-child {
            color: var(--primary);
            font-style: italic;
        }

        .answer {
            border: 1px solid var(--border);
            border-left: 4px solid var(--primary);
            border-radius: 8px;
            background: #fff;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .answer-head {
            padding: 12px 12px 0 12px;
        }

        .qno {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            background: var(--primary);
            border-radius: 999px;
            padding: 2px 10px;
            margin-bottom: 6px;
        }

        .answer-head h5 {
            font-size: 16px;
            margin: 0 0 8px 0;
            font-weight: 700;
            color: var(--text);
        }

        .answer-body {
            padding: 12px;
            border-top: 1px solid var(--border);
            font-style: italic;
            color: var(--text);
        }

        .answer-body i {
            color: var(--primary);
        }

        .actions {
            padding: 16px;
            border-top: 1px solid var(--border);
            text-align: center;
            background: #fff;
        }

        .btn-main,
        .btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 600;
            border: 1px solid transparent;
            text-decoration: none;
        }

        .btn-main {
            background: var(--primary);
            color: #fff;
        }

        .btn-main:hover {
            filter: brightness(.95);
            color: #fff;
        }

        .btn-ghost {
            background: #fff;
            color: var(--text);
            border-color: var(--border);
        }

        .btn-ghost:hover {
            background: var(--primary-100);
            color: var(--text);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .review-wrap {
                padding: 16px 0;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .head {
                padding: 12px;
                flex-wrap: wrap;
            }

            .head .title-wrap {
                flex: 1 1 100%;
                text-align: left;
            }

            .head .kpis {
                grid-auto-flow: row;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                width: 100%;
                margin-top: 8px;
            }

            .head h1 {
                font-size: 20px;
            }
        }

        @media (prefers-reduced-motion: no-preference) {
            .answer {
                transition: transform .2s ease, box-shadow .2s ease;
            }

            .answer:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 18px rgba(0, 0, 0, .06);
            }
        }

        @media print {
            .review-wrap {
                background: #fff;
                padding: 0;
            }

            .actions {
                display: none !important;
            }

            .head {
                color: #000;
                background: #fff;
                border-bottom: 1px solid #000;
            }

            .kpi-pill {
                background: #fff;
                border-color: #000;
            }

            .kpi-pill .num {
                color: #000;
            }

            .qno {
                color: #000;
                background: #fff;
                border: 1px solid #000;
            }

            a[href]:after {
                content: "";
            }
        }
    </style>

    <div class="review-wrap">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card-clean" role="region" aria-labelledby="kh-sr-head">
                        <div class="head" role="region" aria-labelledby="kh-sr-head">
                            <div class="icon" aria-hidden="true">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>

                            <div class="title-wrap">
                                <h1 id="kh-sr-head">Khảo sát hoàn thành</h1>
                                <p class="subtitle">
                                    {{ $reviewData['phieu_info']['ten_dot'] ?? 'Đợt khảo sát' }} ·
                                    Cảm ơn bạn đã tham gia khảo sát
                                </p>
                            </div>

                            <div class="kpis">
                                <div class="kpi-pill">
                                    <div class="num">{{ $reviewData['total_questions'] ?? 0 }}</div>
                                    <div class="lbl">Tổng câu hỏi</div>
                                </div>
                                <div class="kpi-pill">
                                    <div class="num">{{ $reviewData['personal_info_count'] ?? 0 }}</div>
                                    <div class="lbl">Thông tin cá nhân</div>
                                </div>
                                <div class="kpi-pill">
                                    <div class="num">{{ $reviewData['survey_questions_count'] ?? 0 }}</div>
                                    <div class="lbl">Câu hỏi khảo sát</div>
                                </div>
                                {{-- <div class="kpi-pill">
                                    <div class="num">{{ $reviewData['phieu_info']['id'] ?? 'N/A' }}</div>
                                    <div class="lbl">Mã phiếu</div>
                                </div> --}}
                            </div>
                        </div>

                        <!-- Personal info compact -->
                        <div class="block" aria-labelledby="block-info">
                            <h3 class="block-title" id="block-info">
                                <i class="bi bi-person-badge"></i> Thông tin phiếu khảo sát
                            </h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <h6><i class="bi bi-hash me-1"></i>Tên đợt khảo sát</h6>
                                    <h5>{{ $reviewData['phieu_info']['ten_dot'] ?? 'N/A' }}</h5>
                                </div>
                                <div class="info-item">
                                    <h6><i class="bi bi-hash me-1"></i>Mã định danh</h6>
                                    <h5>{{ $reviewData['phieu_info']['id'] ?? 'N/A' }}</h5>
                                </div>
                                <div class="info-item">
                                    <h6><i class="bi bi-clock me-1"></i>Thời gian nộp</h6>
                                    <h5>{{ $reviewData['phieu_info']['thoi_gian_nop'] ?? 'N/A' }}</h5>
                                </div>
                                <div class="info-item">
                                    <h6><i class="bi bi-clock me-1"></i>Thời gian làm bài</h6>
                                    <h5>{{ $reviewData['phieu_info']['thoi_gian_lam_bai'] ?? 'N/A' }}s</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Personal info answers -->
                        @if(!empty($reviewData['personal_info_answers']))
                            <div class="block" aria-labelledby="block-pi">
                                <h3 class="block-title" id="block-pi">
                                    <i class="bi bi-person-circle"></i> Câu trả lời thông tin cá nhân
                                </h3>
                                <div class="table-responsive">
                                    <table class="table-clean">
                                        <tbody>
                                            @foreach($reviewData['personal_info_answers'] as $answer)
                                                <tr>
                                                    <td>{{ $answer['cau_hoi'] }}</td>
                                                    <td>{{ $answer['cau_tra_loi'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- Survey answers -->
                        @if(!empty($reviewData['survey_answers']))
                            <div class="block" aria-labelledby="block-survey">
                                <h3 class="block-title" id="block-survey">
                                    <i class="bi bi-card-checklist"></i> Câu trả lời khảo sát
                                </h3>
                                @foreach($reviewData['survey_answers'] as $index => $answer)
                                    <div class="answer">
                                        <div class="answer-head d-flex align-items-baseline gap-2">
                                            <span class="qno flex-shrink-0">Câu {{ $index + 1 }}: {{ $answer['cau_hoi'] }}</span>
                                        </div>
                                        <div class="answer-body">
                                            @if(is_array($answer['cau_tra_loi']))
                                                <ul class="mb-0 ps-3">
                                                    @forelse($answer['cau_tra_loi'] as $item)
                                                        <li><i class="bi bi-check2-square me-1"></i>{{ $item }}</li>
                                                    @empty
                                                        <li class="text-muted">(Không trả lời)</li>
                                                    @endforelse
                                                </ul>
                                            @else
                                                <p class="mb-0">
                                                    <i class="bi bi-chat-quote me-1"></i>{{ $answer['cau_tra_loi'] }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if(empty($reviewData['personal_info_answers']) && empty($reviewData['survey_answers']))
                            <div class="block text-center">
                                <i class="bi bi-inbox display-6 text-muted"></i>
                                <p class="text-muted mt-2 mb-0">Không có dữ liệu câu trả lời.</p>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="actions">
                            <button class="btn-main me-2" onclick="window.print()">
                                <i class="bi bi-printer"></i> In kết quả
                            </button>
                            <a href="{{ route('khao-sat.index') }}" class="btn-ghost">
                                <i class="bi bi-arrow-left"></i> Quay lại danh sách </a>
                        </div>
                    </div><!-- /card -->
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            const els = document.querySelectorAll('.answer');
            els.forEach((el, i) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(8px)';
                setTimeout(() => {
                    el.style.transition = 'all .2s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 60 * i);
            });
        });
    </script>
@endsection