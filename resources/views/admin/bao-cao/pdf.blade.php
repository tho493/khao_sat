<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Báo cáo: {{ $dotKhaoSat->ten_dot }}</title>
    <style>
        /* Toàn bộ CSS cho file PDF đã có ở các lần trước */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            margin: 5px 0;
            font-weight: bold;
        }

        /* Các style khác tuỳ biến đơn giản, đầy đủ khi cần */
        table {
            border-collapse: collapse;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px;
            border: 1px solid #ddd;
        }

        .info-table td:first-child {
            font-weight: bold;
            width: 30%;
            background-color: #f8f8f8;
        }

        .question-block {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .answer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .answer-table th,
        .answer-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        .answer-table th {
            background-color: #f2f2f2;
        }

        .progress-bar {
            background-color: #e9ecef;
            border-radius: .25rem;
            display: flex;
            height: 1rem;
            overflow: hidden;
            font-size: .75rem;
            line-height: 1rem;
        }

        .progress-bar-fill {
            background-color: #0d6efd;
            color: white;
            text-align: center;
        }

        .stats-table,
        .info-table {
            width: 70%;
            margin: 10px auto;
            border-collapse: collapse;
        }

        .stats-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        .stats-table td:first-child {
            text-align: left;
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .likert-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            margin-top: 15px;
        }

        .likert-table th,
        .likert-table td {
            text-align: center;
            border: 1px solid #999;
            padding: 6px;
        }

        .likert-table th {
            background-color: #f2f2f2;
        }

        .likert-table .question-content {
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>BÁO CÁO KẾT QUẢ KHẢO SÁT</h1>
        <h2>{{ $dotKhaoSat->ten_dot }}</h2>
    </div>

    <h3>I. THÔNG TIN TỔNG QUAN</h3>
    <table class="info-table">
        <tr>
            <td>Tên mẫu khảo sát</td>
            <td>{{ $dotKhaoSat->mauKhaoSat->ten_mau ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Tên đợt khảo sát</td>
            <td>{{ $dotKhaoSat->ten_dot ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Thời gian khảo sát</td>
            <td>
                @php
                    $tuNgay = $dotKhaoSat->tungay ? $dotKhaoSat->tungay : null;
                    $denNgay = $dotKhaoSat->denngay ? $dotKhaoSat->denngay : null;
                @endphp
                {{ $tuNgay ?? 'N/A' }} - {{ $denNgay ?? 'N/A' }}
            </td>
        </tr>
        <tr>
            <td>Số phiếu đã hoàn thành</td>
            <td>{{ $tongQuan['tong_phieu'] }}</td>
        </tr>
        <tr>
            <td>Thời gian trả lời trung bình</td>
            <td>{{ $tongQuan['thoi_gian_tb'] ?? 'N/A' }}</td>
        </tr>
    </table>

    <h3>II. KẾT QUẢ CHI TIẾT</h3>

    {{-- PHẦN 1: BẢNG TỔNG HỢP LIKERT --}}
    @if(isset($likertQuestions) && $likertQuestions->isNotEmpty())
        <h4>Bảng tổng hợp các tiêu chí theo thang đo Likert</h4>

        {{-- Chú thích mức độ --}}
        <div style="font-size: 9px; margin-bottom: 5px;">
            <strong>Mức độ:</strong>
            @foreach($likertOptions as $option)
                <span style="margin-right: 10px;"><strong>{{ $loop->iteration }}:</strong> {{ $option->noidung }}</span>
            @endforeach
        </div>

        <table class="likert-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 30px;">TT</th>
                    <th rowspan="2" class="question-content">Nội dung</th>
                    <th colspan="{{ $likertOptions->count() }}">Mức độ hài lòng</th>
                </tr>
                <tr>
                    @foreach($likertOptions as $option)
                        <th style="width: 40px;">{{ $loop->iteration }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($likertQuestions as $question)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="question-content">{{ $question->noidung_cauhoi }}</td>
                        @php $counts = $likertTableData->get($question->id); @endphp
                        @foreach($likertOptions as $option)
                            <td>{{ $counts[$option->thutu] ?? 0 }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- PHẦN 2: CÁC CÂU HỎI CÒN LẠI --}}
    @if(isset($otherQuestions) && $otherQuestions->isNotEmpty())
        <h4 style="margin-top: 25px;">Kết quả chi tiết các câu hỏi khác</h4>
        @foreach($otherQuestions as $cauHoi)
            <div class="question-block" style="page-break-inside: avoid;">
                <h5>Câu {{ $loop->iteration }}: {{ $cauHoi->noidung_cauhoi }}</h5>
                <p style="font-style: italic; color: #555;">
                    (Tổng số: {{ $thongKeCauHoiKhac[$cauHoi->id]['total'] ?? 0 }} lượt trả lời)
                </p>
                @php $stats = $thongKeCauHoiKhac[$cauHoi->id] ?? null; @endphp

                @if($stats && $stats['total'] > 0)
                    {{-- Xử lý các loại câu hỏi khác như text, rating, number --}}
                    @if($stats['type'] == 'chart')
                        <table class="answer-table">
                            <thead>
                                <tr>
                                    <th>Phương án</th>
                                    <th style="text-align: center; width: 15%;">Số lượng</th>
                                    <th style="width: 40%;">Tỷ lệ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['data'] as $item)
                                    <tr>
                                        <td>{{ $item->noidung ?? 'Không xác định' }}</td>
                                        <td style="text-align: center;">{{ $item->so_luong }}</td>
                                        <td>
                                            <div class="progress-bar">
                                                <div class="progress-bar-fill" style="width: {{ $item->ty_le }}%;">
                                                    {{ $item->ty_le }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @elseif($stats['type'] == 'number_stats')
                        @if(!empty($cauHoi->is_personal_info))
                            <ul style="padding-left: 20px; border: 1px solid #eee; padding: 10px;">
                                @foreach($stats['cauTraLoi'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @else
                            <table class="info-table">
                                <tr>
                                    <td>Giá trị Trung bình</td>
                                    <td>{{ number_format($stats['data']->avg, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Giá trị Nhỏ nhất (Min)</td>
                                    <td>{{ number_format($stats['data']->min) }}</td>
                                </tr>
                                <tr>
                                    <td>Giá trị Lớn nhất (Max)</td>
                                    <td>{{ number_format($stats['data']->max) }}</td>
                                </tr>
                                <tr>
                                    <td>Độ lệch chuẩn</td>
                                    <td>{{ number_format($stats['data']->stddev, 2) }}</td>
                                </tr>
                            </table>
                        @endif
                    @elseif($stats['type'] == 'list' || $stats['type'] == 'text')
                        <ul style="padding-left: 20px; border: 1px solid #eee; padding: 10px;">
                            @foreach($stats['data'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    <p style="color: #888;">Chưa có dữ liệu cho câu hỏi này.</p>
                @endif
            </div>
        @endforeach
    @endif
</body>

</html>