<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateEmail extends Model
{
    protected $table = 'template_email';

    protected $fillable = [
        'ma_template',
        'ten_template',
        'tieude',
        'noidung',
        'bien_template',
        'trangthai'
    ];

    protected $casts = [
        'bien_template' => 'array',
        'trangthai' => 'boolean'
    ];

    /**
     * Render template với dữ liệu
     */
    public function render(array $data)
    {
        $content = $this->noidung;
        $subject = $this->tieude;

        foreach ($data as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
            $subject = str_replace('{' . $key . '}', $value, $subject);
        }

        return [
            'subject' => $subject,
            'content' => $content
        ];
    }

    /**
     * Kiểm tra biến template hợp lệ
     */
    public function validateVariables(array $data)
    {
        $requiredVars = $this->bien_template ?? [];
        $missingVars = array_diff($requiredVars, array_keys($data));

        return empty($missingVars);
    }
}