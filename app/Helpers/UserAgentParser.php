<?php

namespace App\Helpers;

class UserAgentParser
{
    /**
     * Phân tích chuỗi User Agent thô thành các thông tin chi tiết.
     *
     * @param string|null $userAgent
     * @return array
     */
    public static function parse(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return [
                'device' => 'Unknown',
                'os' => 'Unknown',
                'browser' => 'Unknown',
                'app' => null,
                'summary' => 'N/A'
            ];
        }

        $userAgentLower = strtolower($userAgent);

        // 1. Phân tích Hệ điều hành
        $os = 'Unknown OS';
        if (str_contains($userAgentLower, 'windows')) {
            $os = 'Windows';
        } elseif (str_contains($userAgentLower, 'android')) {
            $os = 'Android';
        } elseif (str_contains($userAgentLower, 'iphone') || str_contains($userAgentLower, 'ipad') || str_contains($userAgentLower, 'ipod')) {
            $os = 'iOS';
        } elseif (str_contains($userAgentLower, 'macintosh') || str_contains($userAgentLower, 'mac os x')) {
            $os = 'macOS';
        } elseif (str_contains($userAgentLower, 'linux')) {
            $os = 'Linux';
        }

        // 2. Phân tích Loại thiết bị
        $device = 'Desktop';
        if (str_contains($userAgentLower, 'mobile') || str_contains($userAgentLower, 'phone') || str_contains($userAgentLower, 'ipod')) {
            $device = 'Mobile';
        } elseif (str_contains($userAgentLower, 'ipad') || str_contains($userAgentLower, 'tablet') || (str_contains($userAgentLower, 'android') && !str_contains($userAgentLower, 'mobile'))) {
            $device = 'Tablet';
        } elseif (str_contains($userAgentLower, 'bot') || str_contains($userAgentLower, 'crawler') || str_contains($userAgentLower, 'spider')) {
            $device = 'Bot';
        }

        // 3. Phân tích Ứng dụng tích hợp (WebView)
        $app = null;
        if (str_contains($userAgentLower, 'zalo')) {
            $app = 'Zalo';
        } elseif (str_contains($userAgentLower, 'fbav') || str_contains($userAgentLower, 'fb_iab')) {
            $app = 'Facebook';
        } elseif (str_contains($userAgentLower, 'instagram')) {
            $app = 'Instagram';
        } elseif (str_contains($userAgentLower, 'messenger') || str_contains($userAgentLower, 'fbms')) {
            $app = 'Messenger';
        }

        // 4. Phân tích Trình duyệt
        $browser = 'Unknown';
        if (str_contains($userAgentLower, 'edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgentLower, 'opr/') || str_contains($userAgentLower, 'opera')) {
            $browser = 'Opera';
        } elseif (str_contains($userAgentLower, 'firefox') || str_contains($userAgentLower, 'fxios')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgentLower, 'chrome') || str_contains($userAgentLower, 'crios')) {
            $browser = 'Chrome';
        } elseif ((str_contains($userAgentLower, 'safari') || str_contains($userAgentLower, 'applewebkit')) && !str_contains($userAgentLower, 'chrome')) {
            $browser = 'Safari';
        }

        // Tạo summary
        $summary = $browser . ' / ' . $os;
        if ($app) {
            $summary .= ' (' . $app . ' App)';
        }

        return [
            'device' => $device,
            'os' => $os,
            'browser' => $browser,
            'app' => $app,
            'summary' => $summary
        ];
    }
}
