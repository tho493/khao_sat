<?php

namespace App\Exports;

use App\Models\DotKhaoSat;
use App\Models\PhieuKhaoSat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KhaoSatExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $dotKhaoSat;
    protected $cauHoiHeaders = [];
    protected $cauHoiCollection;

    public function __construct(DotKhaoSat $dotKhaoSat)
    {
        // Tải trước tất cả dữ liệu cần thiết để tối ưu
        $this->dotKhaoSat = $dotKhaoSat->load([
            'mauKhaoSat.cauHoi' => function ($query) {
                $query->orderBy('thutu');
            },
            'phieuKhaoSat' => function ($query) {
                $query->where('trangthai', 'completed')
                    ->with(['chiTiet.phuongAn']);
            }
        ]);

        // Tạo mảng header cho các câu hỏi
        $this->cauHoiCollection = $this->dotKhaoSat->mauKhaoSat->cauHoi;
        foreach ($this->cauHoiCollection as $index => $cauHoi) {
            $this->cauHoiHeaders[] = "Câu " . ($index + 1) . ": " . $cauHoi->noidung_cauhoi;
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Trả về collection các phiếu khảo sát đã hoàn thành
        return $this->dotKhaoSat->phieuKhaoSat;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Ghép các header cố định với header câu hỏi
        return array_merge([
            'ID Phiếu',
            'Mã người trả lời',
            'Họ tên',
            'Đơn vị',
            'Email',
            'Thời gian hoàn thành',
        ], $this->cauHoiHeaders);
    }

    /**
     * @param mixed $phieu
     * @return array
     */
    public function map($phieu): array
    {
        // Nhóm các câu trả lời theo ID câu hỏi
        $answersByQuestionId = $phieu->chiTiet->groupBy('cauhoi_id');
        $rowAnswers = [];

        // Lặp qua danh sách câu hỏi CỐ ĐỊNH để đảm bảo các cột luôn đúng thứ tự
        foreach ($this->cauHoiCollection as $cauHoi) {
            $cellValue = ''; // Giá trị mặc định cho ô

            // Lấy ra các câu trả lời cho câu hỏi hiện tại
            $answersForThisQuestion = $answersByQuestionId->get($cauHoi->id);

            if ($answersForThisQuestion) {
                if ($cauHoi->loai_cauhoi === 'multiple_choice') {
                    // --- PHẦN SỬA LỖI ---
                    // Nếu là câu hỏi chọn nhiều, lấy nội dung của từng phương án và nối lại
                    $cellValue = $answersForThisQuestion
                        ->map(fn($answer) => $answer->phuongAn->noidung ?? '')
                        ->implode('; '); // Nối các câu trả lời bằng dấu '; '
                } else {
                    // Với các loại câu hỏi khác, chỉ có 1 câu trả lời
                    $firstAnswer = $answersForThisQuestion->first();
                    if ($firstAnswer) {
                        if ($firstAnswer->phuongan_id) {
                            $cellValue = $firstAnswer->phuongAn->noidung ?? '';
                        } elseif ($firstAnswer->giatri_text) {
                            $cellValue = $firstAnswer->giatri_text;
                        } elseif ($firstAnswer->giatri_number !== null) {
                            $cellValue = $firstAnswer->giatri_number;
                        } elseif ($firstAnswer->giatri_date) {
                            $cellValue = $firstAnswer->giatri_date;
                        }
                    }
                }
            }

            $rowAnswers[] = $cellValue;
        }

        // Ghép thông tin phiếu với các câu trả lời
        return array_merge([
            $phieu->id,
            $phieu->ma_nguoi_traloi,
            $phieu->metadata['hoten'] ?? '',
            $phieu->metadata['donvi'] ?? '',
            $phieu->metadata['email'] ?? '',
            $phieu->thoigian_hoanthanh ? $phieu->thoigian_hoanthanh->format('d/m/Y H:i') : '',
        ], $rowAnswers);
    }

    /**
     * Định dạng style cho file Excel.
     */
    public function styles(Worksheet $sheet)
    {
        // In đậm và tô màu nền cho dòng header
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDDDDDD'],
                ]
            ],
        ];
    }
}