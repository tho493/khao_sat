<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PreventDoubleSubmissions
{
    /**
     * Tên của session key prefix.
     */
    const SESSION_KEY_PREFIX = '_submission_token_';

    /**
     * Tên của input field trong form.
     */
    const INPUT_NAME = '_submission_token';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $formName Tên định danh cho form
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $formName = null)
    {
        if (!$request->isMethod('POST') && !$request->isMethod('PUT') && !$request->isMethod('PATCH') && !$request->isMethod('DELETE')) {
            return $next($request);
        }

        if (is_null($formName)) {
            $formName = sha1($request->fullUrl());
        }

        $sessionKey = self::SESSION_KEY_PREFIX . $formName;

        $requestToken = $request->input(self::INPUT_NAME);
        $sessionToken = $request->session()->get($sessionKey);

        if (empty($requestToken) || empty($sessionToken) || $requestToken !== $sessionToken) {
            return back()->with('error', 'Thao tác đã được xử lý hoặc phiên làm việc đã hết hạn. Vui lòng thử lại.');
        }

        return $next($request);
    }

    /**
     * Helper tĩnh để tạo thẻ input.
     * @param string $formName Tên định danh cho form
     */
    public static function tokenField(?string $formName = null): string
    {
        if (is_null($formName)) {
            $formName = sha1(request()->fullUrl());
        }

        $sessionKey = self::SESSION_KEY_PREFIX . $formName;
        $token = Str::uuid()->toString();

        session()->put($sessionKey, $token);

        return '<input type="hidden" name="' . self::INPUT_NAME . '" value="' . $token . '">';
    }

    /**
     * Helper tĩnh để xóa token từ Controller.
     */
    public static function clearToken(?string $formName = null)
    {
        if (is_null($formName)) {
            $formName = sha1(request()->fullUrl());
        }
        $sessionKey = self::SESSION_KEY_PREFIX . $formName;
        request()->session()->forget($sessionKey);
    }

}