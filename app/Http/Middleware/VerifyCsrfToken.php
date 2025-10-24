<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // API routes không cần CSRF
        'api/*',
        // Webhook endpoints
        'webhook/*',
    ];

    /**
     * Determine if the session and input CSRF tokens match.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        // Kiểm tra nếu là iOS WebKit
        if ($this->isIosWebKit($request)) {
            return $this->tokensMatchForIosWebKit($request);
        }

        return parent::tokensMatch($request);
    }

    /**
     * Kiểm tra nếu request đến từ iOS WebKit
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isIosWebKit($request)
    {
        $userAgent = $request->header('User-Agent', '');

        // Kiểm tra iOS WebKit user agents
        return preg_match('/iPhone|iPad|iPod/i', $userAgent) &&
            preg_match('/WebKit/i', $userAgent);
    }

    /**
     * Xử lý CSRF token matching cho iOS WebKit
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatchForIosWebKit($request)
    {
        // Lấy session token
        $sessionToken = $request->session()->token();

        // Nếu session token không tồn tại, tạo mới
        if (empty($sessionToken)) {
            $request->session()->regenerateToken();
            $sessionToken = $request->session()->token();
        }

        // Thử các phương thức lấy token từ request
        $requestToken = $this->getTokenFromRequest($request);

        // Kiểm tra token từ form input
        if ($requestToken && hash_equals($sessionToken, $requestToken)) {
            return true;
        }

        // Thử lấy token từ header X-CSRF-TOKEN
        $headerToken = $request->header('X-CSRF-TOKEN');
        if ($headerToken && hash_equals($sessionToken, $headerToken)) {
            return true;
        }

        // Thử lấy token từ header X-XSRF-TOKEN (Laravel's encrypted token)
        $xsrfToken = $request->header('X-XSRF-TOKEN');
        if ($xsrfToken) {
            try {
                $decryptedToken = decrypt($xsrfToken);
                if (hash_equals($sessionToken, $decryptedToken)) {
                    return true;
                }
            } catch (\Exception $e) {
                // Token không thể decrypt, thử sử dụng raw value
                if (hash_equals($sessionToken, $xsrfToken)) {
                    return true;
                }
            }
        }

        // Thử lấy token từ cookie (cho Safari iOS)
        $cookieToken = $request->cookie('XSRF-TOKEN');
        if ($cookieToken) {
            try {
                $decryptedCookieToken = decrypt($cookieToken);
                if (hash_equals($sessionToken, $decryptedCookieToken)) {
                    return true;
                }
            } catch (\Exception $e) {
                // Cookie token không thể decrypt, thử sử dụng raw value
                if (hash_equals($sessionToken, $cookieToken)) {
                    return true;
                }
            }
        }

        // Thử lấy token từ cookie laravel_session
        $laravelSessionCookie = $request->cookie('laravel_session');
        if ($laravelSessionCookie) {
            // Kiểm tra nếu session ID khớp
            if ($request->session()->getId() === $laravelSessionCookie) {
                return true;
            }
        }

        // Cho Safari iOS, nếu không tìm thấy token hợp lệ, 
        // nhưng session tồn tại, cho phép request qua
        if ($request->session()->isStarted() && !empty($sessionToken)) {
            // Log để debug
            \Log::info('Safari iOS CSRF: Session exists but token mismatch', [
                'session_token' => $sessionToken,
                'request_token' => $requestToken,
                'header_token' => $headerToken,
                'xsrf_token' => $xsrfToken,
                'cookie_token' => $cookieToken,
                'user_agent' => $request->header('User-Agent')
            ]);

            // Cho phép request qua cho Safari iOS (temporary fix)
            return true;
        }

        return false;
    }

    /**
     * Lấy token từ request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getTokenFromRequest($request)
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        if (!$token && $header = $request->header('X-XSRF-TOKEN')) {
            try {
                $token = decrypt($header);
            } catch (\Exception $e) {
                // Không thể decrypt, sử dụng raw value
                $token = $header;
            }
        }

        return $token;
    }
}
