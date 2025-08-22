<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMauKhaoSatRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'ten_mau' => 'required|string|max:255',
            'ma_doituong' => 'required|exists:doituong_khaosat,ma_doituong',
            'mota' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'ten_mau.required' => 'Vui lòng nhập tên mẫu khảo sát',
            'ma_doituong.required' => 'Vui lòng chọn đối tượng khảo sát',
            'ma_doituong.exists' => 'Đối tượng khảo sát không hợp lệ'
        ];
    }
}