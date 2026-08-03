<div id="splash-screen">
    <div class="splash-content">
        <div class="splash-brand-row">
            <div class="splash-logo-wrapper">
                <div id="splash-logo-container" class="splash-logo-svg-wrap">
                    {!! file_get_contents(public_path('image/logo.svg')) !!}
                </div>
            </div>

            <div class="splash-text-group">
                <div id="splash-title-line" class="splash-svg-wrap">
                    <svg id="splash-title-svg" class="splash-title-svg" aria-label="Hệ thống Khảo sát Trực tuyến"
                        role="img" viewBox="0 0 420 50" preserveAspectRatio="xMinYMin meet">
                        <text id="splash-svg-text" x="0" y="0" dominant-baseline="hanging"
                            font-family="'Be Vietnam Pro', system-ui, sans-serif" font-weight="800"
                            font-style="normal">Hệ thống Khảo sát Trực tuyến</text>
                    </svg>
                    <h1 class="sr-only">Hệ thống Khảo sát Trực tuyến</h1>
                </div>
            </div>
        </div>

        <noscript>
            <div id="splash-noscript-warning">Trình duyệt của bạn đang không bật Javascript. Bạn cần bật nó để website
                có thể hoạt động.</div>
        </noscript>
    </div>

    <!-- Progress bar -->
    <div id="splash-progress-track">
        <div id="splash-progress"></div>
    </div>
</div>