(function () {
    var _triggered = false;
    var _overlayEl = null;
    var _OVERLAY_ID = '__guard_' + Math.random().toString(36).slice(2);

    function _enforceOverlay() {
        if (!_triggered) return;

        if (_overlayEl && document.documentElement.contains(_overlayEl)) return;

        if (document.body) {
            document.body.innerHTML = '';
        }

        var el = document.createElement('div');
        el.id = _OVERLAY_ID;
        el.style.cssText = [
            'position:fixed', 'inset:0', 'z-index:2147483647',
            'background:#09090b', 'display:flex',
            'align-items:center', 'justify-content:center',
            'overflow:hidden', 'font-family:Inter,system-ui,sans-serif',
            'color:#fff',
        ].join(';');

        el.innerHTML = [
            '<style>',
            '@keyframes blink { 50% { opacity:0 } }',
            '.cursor { animation:blink .8s infinite; }',
            '.sub {',
            'margin-top:20px; color:#8b8b94;',
            'font-size:15px; transition:.4s; min-height:24px;',
            '}',
            'button {',
            'margin-top:40px; border:none; border-radius:14px;',
            'padding:14px 34px; font-size:15px; font-weight:600;',
            'cursor:pointer; background:#2563eb; color:#fff; transition:.2s;',
            'font-family:inherit;',
            '}',
            'button:hover { transform:translateY(-2px); background:#1d4ed8; }',
            '</style>',
            '<div style="text-align:center;max-width:760px;padding:40px">',
            '<div id="typing" style="',
            'margin-top:30px; font-size:56px; font-weight:800;',
            'line-height:1.2; min-height:140px;',
            '"></div>',
            '<div class="sub" id="sub">Tắt devtool và reload để tiếp tục</div>',
            '<button onclick="location.reload()">Reload</button>',
            '</div>',
        ].join('');

        document.body.appendChild(el);
        _overlayEl = el;

        var messages = [
            'Làm gì đấy... 👀',
            'Ơ... sao lại bật DevTools? 🤨',
            'Định debug em à? 😭',
            'Không có flag đâu 😅',
            'OK... bạn thì giỏi rồi 🥲',
            'Đẳng cấp quá, không dám bug 🤣',
            'Không tin à? Vào console gõ 10000 lần true',
        ];

        var typing = document.getElementById('typing');
        var msgIndex = 0;

        function typeText(text) {
            typing.innerHTML = '';
            var chars = Array.from(text);
            var i = 0;
            var timer = setInterval(function () {
                typing.textContent = chars.slice(0, i++).join('');
                var cur = document.createElement('span');
                cur.className = 'cursor';
                cur.textContent = '|';
                typing.appendChild(cur);
                if (i > chars.length) clearInterval(timer);
            }, 45);
        }

        function nextMessage() {
            typeText(messages[msgIndex]);
            msgIndex = (msgIndex + 1) % messages.length;
        }

        nextMessage();
        setInterval(nextMessage, 4500);
    }

    function showDevtoolWarning() {
        if (_triggered) return;
        _triggered = true;
        _enforceOverlay();
        setInterval(_enforceOverlay, 500);
    }

    window.__devtoolWarningFn = showDevtoolWarning;

    // Kỹ thuật 1: chênh lệch kích thước cửa sổ
    function _sizeDetect() {
        return (window.outerWidth - window.innerWidth > 160)
            || (window.outerHeight - window.innerHeight > 160);
    }

    // Kỹ thuật 2: Image.id getter
    var _img = new Image();
    var _imgHit = false;
    Object.defineProperty(_img, 'id', { get: function () { _imgHit = true; } });

    function _consoleDetect() {
        _imgHit = false;
        // eslint-disable-next-line no-console
        console.log('%c', _img);
        return _imgHit;
    }

    setInterval(function () {
        if (!_triggered && (_sizeDetect() || _consoleDetect())) {
            showDevtoolWarning();
        }
    }, 600);
})();

// disable-devtool CDN — lớp detect thứ 3 (nếu load được)
if (typeof DisableDevtool !== 'undefined') {
    DisableDevtool({
        ondevtoolopen: function (type, next) {
            window.__devtoolWarningFn && window.__devtoolWarningFn();
        },
        clearLog: true,
        disableMenu: true,
        stopIntervalTime: 400,
    });
}
