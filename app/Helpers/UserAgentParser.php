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
                'device'     => 'Unknown',
                'os'         => 'Unknown',
                'os_version' => null,
                'browser'    => 'Unknown',
                'browser_version' => null,
                'app'        => null,
                'source'     => 'Trực tiếp',
                'summary'    => 'N/A',
            ];
        }

        $ua    = $userAgent;
        $ual   = strtolower($userAgent);

        // -------------------------------------------------------
        // 1. APP / WebView (phát hiện trước, ưu tiên cao nhất)
        // -------------------------------------------------------
        $app = null;
        if (str_contains($ual, 'zalo')) {
            $app = 'Zalo';
        } elseif (str_contains($ual, 'fbav') || str_contains($ual, 'fb_iab') || str_contains($ual, 'fban/')) {
            $app = 'Facebook';
        } elseif (str_contains($ual, 'instagram')) {
            $app = 'Instagram';
        } elseif (preg_match('/\bfbms\b|messenger/i', $ua)) {
            $app = 'Messenger';
        } elseif (str_contains($ual, 'tiktok') || str_contains($ual, 'musically')) {
            $app = 'TikTok';
        } elseif (str_contains($ual, 'twitter')) {
            $app = 'Twitter';
        } elseif (str_contains($ual, 'youtube') || str_contains($ual, 'com.google.android.youtube')) {
            $app = 'YouTube';
        }

        // -------------------------------------------------------
        // 2. Hệ điều hành & Phiên bản
        // -------------------------------------------------------
        $os        = 'Unknown OS';
        $osVersion = null;

        if (str_contains($ual, 'windows phone')) {
            // Windows Phone phải kiểm tra trước Windows
            $os = 'Windows Phone';
            if (preg_match('/windows phone(?:\s+os)?\s+([0-9\.]+)/i', $ua, $m)) {
                $osVersion = $m[1];
            }
        } elseif (str_contains($ual, 'windows')) {
            $os = 'Windows';
            if (preg_match('/windows\s+nt\s+([0-9\.]+)/i', $ua, $m)) {
                $ntMap = [
                    '10.0' => '10/11',
                    '6.3'  => '8.1',
                    '6.2'  => '8',
                    '6.1'  => '7',
                    '6.0'  => 'Vista',
                    '5.2'  => 'XP x64',
                    '5.1'  => 'XP',
                    '5.0'  => '2000',
                ];
                $osVersion = $ntMap[$m[1]] ?? $m[1];
            }
        } elseif (str_contains($ual, 'android')) {
            // Android trước iOS vì một số UA có cả "android" lẫn "linux"
            $os = 'Android';
            if (preg_match('/android\s+([0-9]+(?:\.[0-9]+)*)/i', $ua, $m)) {
                // Chỉ lấy major.minor, bỏ patch nhỏ không cần thiết
                $parts     = explode('.', $m[1]);
                $osVersion = $parts[0] . (isset($parts[1]) ? '.' . $parts[1] : '');
            }
        } elseif (preg_match('/ip(?:hone|ad|od)/i', $ua)) {
            $os = 'iOS';
            // iOS mới: Version/18.0 ở cuối UA khớp với phiên bản iOS thực tế
            if (preg_match('/version\/([0-9]+(?:\.[0-9]+)*)/i', $ua, $m)) {
                $parts     = explode('.', $m[1]);
                $osVersion = $parts[0] . (isset($parts[1]) ? '.' . $parts[1] : '');
            } elseif (preg_match('/os\s+([0-9_]+)\s+like\s+mac/i', $ua, $m)) {
                // Fallback: "CPU iPhone OS 18_0 like Mac OS X" (WebView App thường không có Version/)
                $raw       = str_replace('_', '.', $m[1]);
                $parts     = explode('.', $raw);
                $osVersion = $parts[0] . (isset($parts[1]) ? '.' . $parts[1] : '');
            }
        } elseif (str_contains($ual, 'macintosh') || str_contains($ual, 'mac os x')) {
            $os = 'macOS';
            if (preg_match('/mac\s+os\s+x\s+([0-9_\.]+)/i', $ua, $m)) {
                $raw   = str_replace('_', '.', $m[1]);
                $parts = explode('.', $raw);
                // macOS 10.16+ → 11+
                if ((int)$parts[0] === 10 && isset($parts[1])) {
                    $minor     = (int)$parts[1];
                    $macMap    = [
                        16 => '11 Big Sur',
                        15 => '10.15 Catalina',
                        14 => '10.14 Mojave',
                        13 => '10.13 High Sierra',
                        12 => '10.12 Sierra',
                        11 => '10.11 El Capitan',
                    ];
                    $osVersion = $macMap[$minor] ?? $raw;
                } else {
                    $macNewMap = [
                        11 => '11 Big Sur',
                        12 => '12 Monterey',
                        13 => '13 Ventura',
                        14 => '14 Sonoma',
                        15 => '15 Sequoia',
                    ];
                    $osVersion = $macNewMap[(int)$parts[0]] ?? $raw;
                }
            }
        } elseif (str_contains($ual, 'cros')) {
            $os = 'Chrome OS';
        } elseif (str_contains($ual, 'linux')) {
            $os = 'Linux';
            if (str_contains($ual, 'ubuntu')) {
                $osVersion = 'Ubuntu';
            } elseif (str_contains($ual, 'fedora')) {
                $osVersion = 'Fedora';
            } elseif (str_contains($ual, 'debian')) {
                $osVersion = 'Debian';
            }
        }

        // -------------------------------------------------------
        // 3. Loại thiết bị
        // -------------------------------------------------------
        $device = 'Desktop';
        $ualForDevice = $ual;

        // Bot/Crawler
        if (preg_match('/bot|crawler|spider|slurp|bingbot|googlebot|facebookexternalhit|linkedinbot|twitterbot|whatsapp|discordbot/i', $ua)) {
            $device = 'Bot';
        } elseif (preg_match('/ip(?:hone|od)/i', $ua) || str_contains($ualForDevice, 'mobile')) {
            $device = 'Mobile';
        } elseif (preg_match('/ipad/i', $ua) || str_contains($ualForDevice, 'tablet')
            || (str_contains($ualForDevice, 'android') && !str_contains($ualForDevice, 'mobile'))) {
            $device = 'Tablet';
        }

        // -------------------------------------------------------
        // 4. Trình duyệt & Phiên bản
        // -------------------------------------------------------
        $browser        = 'Unknown';
        $browserVersion = null;

        // Thứ tự quan trọng: các UA phái sinh Chrome phải kiểm tra trước Chrome
        if (preg_match('/coc_coc_browser\/([0-9]+(?:\.[0-9]+)*)/i', $ua, $m)) {
            $browser        = 'Cốc Cốc';
            $browserVersion = $m[1];
        } elseif (preg_match('/brave\/([0-9]+(?:\.[0-9]+)*)/i', $ua, $m) || str_contains($ual, 'brave')) {
            $browser        = 'Brave';
            if (preg_match('/chrome\/([0-9]+(?:\.[0-9]+)*)/i', $ua, $m)) {
                $browserVersion = $m[1];
            }
        } elseif (preg_match('/samsungbrowser\/([0-9]+(?:\.[0-9]+)*)/i', $ua, $m)) {
            $browser        = 'Samsung Browser';
            $browserVersion = $m[1];
        } elseif (preg_match('/ucbrowser\/([0-9]+(?:\.[0-9]+)*)/i', $ua, $m)) {
            $browser        = 'UC Browser';
            $browserVersion = $m[1];
        } elseif (preg_match('/opr\/([0-9]+(?:\.[0-9]+)*)/i', $ua, $m) || preg_match('/opera\/([0-9]+)/i', $ua, $m)) {
            $browser        = 'Opera';
            $browserVersion = $m[1];
        } elseif (preg_match('/edg(?:e|a|ios)?\/([0-9]+(?:\.[0-9]+)*)/i', $ua, $m)) {
            $browser        = 'Edge';
            $browserVersion = $m[1];
        } elseif (preg_match('/firefox\/([0-9]+(?:\.[0-9]+)*)|fxios\/([0-9]+(?:\.[0-9]+)*)/i', $ua, $m)) {
            $browser        = 'Firefox';
            $browserVersion = $m[1] ?: $m[2];
        } elseif (preg_match('/crios\/([0-9]+(?:\.[0-9]+)*)/i', $ua, $m)) {
            // Chrome on iOS
            $browser        = 'Chrome';
            $browserVersion = $m[1];
        } elseif (preg_match('/chrome\/([0-9]+(?:\.[0-9]+)*)/i', $ua, $m) && !str_contains($ual, 'chromium')) {
            $browser        = 'Chrome';
            $browserVersion = $m[1];
        } elseif (str_contains($ual, 'chromium')) {
            $browser = 'Chromium';
            if (preg_match('/chromium\/([0-9]+)/i', $ua, $m)) {
                $browserVersion = $m[1];
            }
        } elseif ((str_contains($ual, 'safari') || str_contains($ual, 'applewebkit')) && !str_contains($ual, 'chrome')) {
            $browser = 'Safari';
            if (preg_match('/version\/([0-9]+(?:\.[0-9]+)*)/i', $ua, $m)) {
                $browserVersion = $m[1];
            }
        }

        // -------------------------------------------------------
        // 5. Nguồn truy cập
        // -------------------------------------------------------
        $source = $app ? $app . ' App' : 'Trực tiếp';

        // -------------------------------------------------------
        // 6. Summary
        // -------------------------------------------------------
        $osPart     = $os . ($osVersion ? ' ' . $osVersion : '');
        $browserPart = $browser . ($browserVersion ? ' ' . $browserVersion : '');
        $summary    = $browserPart . ' / ' . $osPart;
        if ($app) {
            $summary .= ' (' . $app . ' App)';
        }

        return [
            'device'          => $device,
            'os'              => $os,
            'os_version'      => $osVersion,
            'browser'         => $browser,
            'browser_version' => $browserVersion,
            'app'             => $app,
            'source'          => $source,
            'summary'         => $summary,
        ];
    }
}
