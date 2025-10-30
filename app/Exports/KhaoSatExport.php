<?php

namespace App\Exports;

use App\Models\DotKhaoSat;
use App\Models\PhieuKhaoSat;
use App\Models\DataSource;
use App\Models\DataSourceValue;
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
    protected $personalInfoQuestions;
    protected $nonPersonalQuestions;
    protected $filteredSurveyIds;

    public function __construct(DotKhaoSat $dotKhaoSat, $filteredSurveyIds = null)
    {
        // Tải trước tất cả dữ liệu cần thiết để tối ưu
        $this->dotKhaoSat = $dotKhaoSat->load([
            'mauKhaoSat.cauHoi' => function ($query) {
                $query->orderBy('thutu');
            },
            'mauKhaoSat.cauHoi.dataSource.values',
            'phieuKhaoSat' => function ($query) {
                $query->where('trangthai', 'completed')
                    ->with(['chiTiet.phuongAn']);
            },
            'hiddenQuestions'
        ]);

        $this->filteredSurveyIds = $filteredSurveyIds;

        // Phân nhóm câu hỏi và tạo header
        $hiddenQuestionIds = $this->dotKhaoSat->hiddenQuestions->pluck('id')->all();
        $allQuestions = $this->dotKhaoSat->mauKhaoSat->cauHoi->whereNotIn('id', $hiddenQuestionIds);
        $this->personalInfoQuestions = $allQuestions->where('is_personal_info', true)->values();
        $this->nonPersonalQuestions = $allQuestions->where('is_personal_info', false)->values();

        // Header cho câu hỏi thông tin cá nhân (đặt lên trước)
        foreach ($this->personalInfoQuestions as $cauHoi) {
            $this->cauHoiHeaders[] = '[TT cá nhân] ' . $cauHoi->noidung_cauhoi;
        }
        // Header cho các câu hỏi còn lại
        foreach ($this->nonPersonalQuestions as $index => $cauHoi) {
            $this->cauHoiHeaders[] = "Câu " . ($index + 1) . ": " . $cauHoi->noidung_cauhoi;
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = PhieuKhaoSat::where('dot_khaosat_id', $this->dotKhaoSat->id)
            ->where('trangthai', 'completed')
            ->where('is_duplicate', 0)
            ->with(['chiTiet.phuongAn']);

        if ($this->filteredSurveyIds) {
            $query->whereIn('id', $this->filteredSurveyIds);
        }

        return $query->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Ghép các header cố định với header câu hỏi
        return array_merge([
            'ID Phiếu',
            'Thời gian bắt đầu',
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

        // Điền câu trả lời cho nhóm TT cá nhân trước
        foreach ($this->personalInfoQuestions as $cauHoi) {
            $rowAnswers[] = $this->formatAnswerForQuestion($answersByQuestionId, $cauHoi);
        }
        // Sau đó là các câu hỏi còn lại
        foreach ($this->nonPersonalQuestions as $cauHoi) {
            $rowAnswers[] = $this->formatAnswerForQuestion($answersByQuestionId, $cauHoi);
        }

        // Ghép thông tin phiếu với các câu trả lời
        return array_merge([
            $phieu->id,
            $phieu->thoigian_batdau ? $phieu->thoigian_batdau->format('d/m/Y H:i') : '',
            $phieu->thoigian_hoanthanh ? $phieu->thoigian_hoanthanh->format('d/m/Y H:i') : '',
        ], $rowAnswers);
    }

    /**
     * Trả về giá trị hiển thị cho từng câu hỏi, riêng 'custom_select' thì lấy value -> label từ DataSource
     */
    private function formatAnswerForQuestion($answersByQuestionId, $cauHoi)
    {
        $cellValue = '';
        $answersForThisQuestion = $answersByQuestionId->get($cauHoi->id);

        // Nếu câu hỏi là 'custom_select' thì lấy giá trị và tra label từ DataSource
        if ($cauHoi->loai_cauhoi === 'custom_select') {
            if ($answersForThisQuestion && $answersForThisQuestion->count() > 0) {
                $firstAnswer = $answersForThisQuestion->first();
                $value = $firstAnswer->giatri_text ?? $firstAnswer->giatri_number ?? null;
                if ($value !== null && $cauHoi->dataSource && $cauHoi->dataSource->values) {
                    $option = $cauHoi->dataSource->values->firstWhere('value', $value);
                    $cellValue = $option->label ?? $value;
                } else {
                    $cellValue = $value ?? '';
                }
            }
        } else if ($answersForThisQuestion && $answersForThisQuestion->count() > 0) {
            if ($cauHoi->loai_cauhoi === 'multiple_choice') {
                $cellValue = $answersForThisQuestion
                    ->map(fn($answer) => $answer->phuongAn->noidung ?? '')
                    ->filter()
                    ->implode('; ');
            } else {
                $firstAnswer = $answersForThisQuestion->first();
                if ($firstAnswer->phuongan_id) {
                    $cellValue = $firstAnswer->phuongAn->noidung ?? '';
                } elseif (!empty($firstAnswer->giatri_text)) {
                    $cellValue = $firstAnswer->giatri_text;
                } elseif (!is_null($firstAnswer->giatri_number)) {
                    $cellValue = (string) $firstAnswer->giatri_number;
                } elseif (!empty($firstAnswer->giatri_date)) {
                    $cellValue = (string) $firstAnswer->giatri_date;
                }
            }
        }
        return $cellValue;
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