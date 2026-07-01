<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class Recaptcha implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Cho phép bypass trên localhost hoặc sử dụng Dummy Keys để phát triển/kiểm thử thuận tiện
        if ($value === '1x00000000000000000000AA' || 
            config('services.recaptcha.secret_key') === '1x00000000000000000000000000000000AA' ||
            in_array(request()->ip(), ['127.0.0.1', '::1'])
        ) {
            return true;
        }

        // Gửi request đến Cloudflare Turnstile để xác thực
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        // Kiểm tra kết quả trả về
        return (bool) $response->json('success');
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Xác thực bảo mật Turnstile không thành công. Vui lòng thử lại.';
    }
}