<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Báo cáo khảo sát: {{ $dotKhaoSat->ten_dot }}</title>
    <style>
        /* CSS được nhúng trực tiếp để DOMPDF có thể đọc */
        body {
            font-family: DejaVu Sans, sans-serif;
            /* Font hỗ trợ Unicode (tiếng Việt) */
            font-size: 12px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        h1,
        h2,
        h3 {
            margin: 5px 0;
            font-weight: bold;
        }

        h1 {
            font-size: 22px;
        }

        h2 {
            font-size: 18px;
        }

        h3 {
            font-size: 14px;
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
            /* Tránh ngắt trang giữa chừng một câu hỏi */
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

        .stats-table {
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
                {{ \Carbon\Carbon::parse($dotKhaoSat->tungay)->format('d/m/Y') ?? 'N/A' }}
                -
                {{ \Carbon\Carbon::parse($dotKhaoSat->denngay)->format('d/m/Y') ?? 'N/A' }}
            </td>
        </tr>
        <tr>
            <td>Số phiếu đã hoàn thành</td>
            <td>{{ $tongQuan['phieu_hoan_thanh'] }} / {{ $tongQuan['tong_phieu'] }} ({{ $tongQuan['ty_le'] }}%)</td>
        </tr>
        <tr>
            <td>Thời gian trả lời trung bình</td>
            <td>{{ $tongQuan['thoi_gian_tb'] ?? 'N/A' }}</td>
        </tr>
    </table>

    <h3>II. KẾT QUẢ CHI TIẾT</h3>
    @foreach($dotKhaoSat->mauKhaoSat->cauHoi->sortBy('thutu') as $index => $cauHoi)
        <div class="question-block">
            <h4>Câu {{ $index + 1 }}: {{ $cauHoi->noidung_cauhoi }}</h4>
            <p style="font-style: italic; color: #555;">(Tổng số: {{ $thongKeCauHoi[$cauHoi->id]['total'] }} lượt trả lời)
            </p>

            @php $stats = $thongKeCauHoi[$cauHoi->id]; @endphp

            @if($stats['type'] == 'chart' && !$stats['data']->isEmpty())
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
                <table class="stats-table">
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
            @elseif($stats['type'] == 'text' && !$stats['data']->isEmpty())
                <ul style="padding-left: 20px;">
                    @foreach($stats['data'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                @if($stats['total'] > 20)
                    <p style="font-style: italic;">... và {{ $stats['total'] - 20 }} câu trả lời khác.</p>
                @endif
            @else
                <p style="color: #888;">Chưa có dữ liệu cho câu hỏi này.</p>
            @endif
        </div>
    @endforeach
</body>

</html>