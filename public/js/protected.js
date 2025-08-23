(() => {
  'use strict';

  const blocker   = document.getElementById('devtools-blocker');
  const main      = document.getElementById('main-content');
  if (!blocker || !main) return;

  // ======= Tùy chỉnh =======
  const CHECK_INTERVAL   = 900;     // ms
  const REQUIRED_HITS    = 3;       // cần N lần liên tiếp để block
  const RELEASE_HITS     = 2;       // cần M lần “an toàn” để mở khóa
  const SIZE_PX_MIN      = 250;     // ngưỡng px tối thiểu
  const SIZE_RATIO_MIN   = 0.16;    // ngưỡng tỉ lệ (16% của inner size)
  const LAG_THRESHOLD    = 180;     // ms event-loop lag
  const WORKER_TIMEOUT   = 2000;    // ms không nhận ping từ worker coi là nghi vấn
  // ==========================

  let hits = 0, safe = 0, blocked = false, lastTick = performance.now();
  let workerOK = true;

  function canvasize(node) {
    node.style.filter = 'blur(7px)';
  }

  function block() {
    if (blocked) return;
    blocked = true;
    blocker.style.display = 'flex';
    main.style.pointerEvents = 'none';
    main.style.userSelect = 'none';
    canvasize(main);
    document.body.style.overflow = 'hidden';
    try {
      Object.freeze(console);
      Object.freeze(Object.prototype);
    } catch {}
  }

  function unblock() {
    if (!blocked) return;
    blocked = false;
    blocker.style.display = 'none';
    main.style.pointerEvents = '';
    main.style.userSelect = '';
    main.style.filter = '';
    document.body.style.overflow = '';
  }

  // Heuristic A: console‑getter
  function isOpenByConsole() {
    let opened = false;
    const bait = {};
    Object.defineProperty(bait, 'id', {
      get() { opened = true; return 'x'; }
    });
    console.log(bait);
    console.dir(bait);
    return opened;
  }

  function isOpenBySize() {
    const ow = window.outerWidth  || 0;
    const iw = window.innerWidth  || 0;
    const oh = window.outerHeight || 0;
    const ih = window.innerHeight || 0;
    if (!ow || !oh || !iw || !ih) return false;

    const wd = Math.abs(ow - iw);
    const hd = Math.abs(oh - ih);
    const wr = wd / Math.max(1, iw);
    const hr = hd / Math.max(1, ih);

    const bigDiff = (wd > SIZE_PX_MIN || hd > SIZE_PX_MIN);
    const bigRatio = (wr > SIZE_RATIO_MIN || hr > SIZE_RATIO_MIN);
    return bigDiff && bigRatio;
  }

  function isOpenByLag() {
    const now = performance.now();
    const delta = now - lastTick - CHECK_INTERVAL;
    lastTick = now;
    return delta > LAG_THRESHOLD;
  }

  let worker, workerPingAt = Date.now();
  function startWorker() {
    const blob = new Blob([`
      let last = Date.now();
      function ping() {
        const now = Date.now();
        postMessage({ ok: true, dt: now - last });
        last = now;
        setTimeout(ping, 700);
      }
      ping();
      onmessage = ()=>{};
    `], { type: 'application/javascript' });
    const url = URL.createObjectURL(blob);
    worker = new Worker(url);
    worker.onmessage = (e) => {
      workerPingAt = Date.now();
      if (e.data && e.data.dt > LAG_THRESHOLD) {
        workerOK = false;
      } else {
        workerOK = true;
      }
    };
  }
  startWorker();

  function isOpenByWorker() {
    return !workerOK || (Date.now() - workerPingAt > WORKER_TIMEOUT);
  }

  const scriptSignature = (function(){
    try {
      const s = (document.currentScript && document.currentScript.textContent) || '';
      let sum = 0; for (let i=0;i<s.length;i++) sum = (sum + s.charCodeAt(i)) % 65536;
      return sum;
    } catch { return 0; }
  })();

  function tampered() {
    try {
      const s = (document.currentScript && document.currentScript.textContent) || '';
      let sum = 0; for (let i=0;i<s.length;i++) sum = (sum + s.charCodeAt(i)) % 65536;
      return scriptSignature && sum && sum !== scriptSignature;
    } catch { return false; }
  }

  function check() {
    let signals = 0;
    if (isOpenByConsole()) signals++;
    if (isOpenBySize())    signals++;
    if (isOpenByLag())     signals++;
    if (isOpenByWorker())  signals++;
    if (tampered())        signals += 2;

    console.clear();
    const open = signals >= 2;

    if (open) {
      hits++; safe = 0;
      if (hits >= REQUIRED_HITS) block();
    } else {
      hits = 0; safe++;
      if (safe >= RELEASE_HITS) unblock();
    }
  }

  setInterval(check, CHECK_INTERVAL);
  window.addEventListener('resize',  () => setTimeout(check, 120));
  window.addEventListener('focus',   () => setTimeout(check,  60));

})();
