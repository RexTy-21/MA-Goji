(function () {
  var $ = function (s) { return document.querySelector(s); };
  var $$ = function (s) { return Array.prototype.slice.call(document.querySelectorAll(s)); };

  var side = $('#side'), backdrop = $('#backdrop');

  /* ---------- Toast ---------- */
  var toastEl = $('#toast'), toastTimer;
  function toast(msg) {
    if (!toastEl) return;
    toastEl.textContent = msg; toastEl.classList.add('show');
    clearTimeout(toastTimer); toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, 2200);
  }

  /* ---------- Sidebar ---------- */
  function isMobile() { return window.matchMedia('(max-width: 900px)').matches; }
  function closeMobile() { side.classList.remove('open-m'); backdrop.hidden = true; }
  function toggleSide() {
    if (isMobile()) {
      side.classList.toggle('open-m'); backdrop.hidden = !side.classList.contains('open-m');
    } else {
      side.classList.toggle('closed');
    }
  }
  var burger = $('#burger');
  if (burger) burger.addEventListener('click', toggleSide);
  if (backdrop) backdrop.addEventListener('click', closeMobile);

  var nav = $('#nav');
  if (nav) {
    nav.addEventListener('click', function (e) {
      var toggle = e.target.closest('[data-toggle]');
      if (toggle) {
        var item = toggle.closest('.nav-item');
        var open = !item.classList.contains('open');
        item.classList.toggle('open', open);
        item.setAttribute('data-open', open ? '1' : '0');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        return;
      }
      var link = e.target.closest('a.nav-btn, a.sub-btn');
      if (link) {
        if (link.getAttribute('href') === '#') {
          e.preventDefault();
          toast('Halaman ' + link.textContent.trim() + ' belum dibuat (prototipe)');
        }
        if (isMobile()) closeMobile();
      }
    });
  }

  /* ---------- Cari menu ---------- */
  var q = $('#q');
  function filterNav() {
    var term = q.value.trim().toLowerCase(), shown = 0;
    $$('#nav .nav-item').forEach(function (item) {
      var parent = item.querySelector('.nav-btn');
      var subs = $$('#nav .nav-item').length ? Array.prototype.slice.call(item.querySelectorAll('.sub-btn')) : [];
      var parentHit = !term || parent.textContent.toLowerCase().indexOf(term) > -1;
      var subHit = false;
      subs.forEach(function (s) {
        var hit = !term || parentHit || s.textContent.toLowerCase().indexOf(term) > -1;
        s.hidden = !hit;
        if (hit && term && !parentHit) subHit = true;
      });
      var visible = parentHit || subHit;
      item.hidden = !visible;
      if (visible) shown++;
      if (subs.length) item.classList.toggle('open', term ? visible : item.getAttribute('data-open') === '1');
    });
    var empty = $('#navEmpty');
    if (empty) empty.hidden = shown > 0;
  }
  if (q) q.addEventListener('input', filterNav);

  /* ---------- Menu pengguna ---------- */
  var userBtn = $('#userBtn'), userMenu = $('#userMenu');
  function setUserMenu(open) {
    if (!userMenu) return;
    userMenu.hidden = !open; userBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  if (userBtn) {
    userBtn.addEventListener('click', function (e) { e.stopPropagation(); setUserMenu(userMenu.hidden); });
    document.addEventListener('click', function (e) { if (!userMenu.hidden && !userMenu.contains(e.target)) setUserMenu(false); });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { setUserMenu(false); if (side) closeMobile(); return; }
    var tag = (document.activeElement && document.activeElement.tagName) || '';
    if (e.key === '/' && tag !== 'INPUT' && tag !== 'SELECT' && q) {
      e.preventDefault();
      if (isMobile()) { side.classList.add('open-m'); backdrop.hidden = false; } else { side.classList.remove('closed'); }
      q.focus();
    }
  });

  /* ---------- Jam ---------- */
  var MON = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  var clock = $('#clock');
  function two(n) { return n < 10 ? '0' + n : '' + n; }
  function tick() {
    var d = new Date();
    clock.textContent = d.getDate() + ' ' + MON[d.getMonth()] + ' ' + d.getFullYear() + ', ' + two(d.getHours()) + ':' + two(d.getMinutes()) + ':' + two(d.getSeconds());
  }
  if (clock) { tick(); setInterval(tick, 1000); }

  /* ---------- Alur approval mutasi ---------- */
  var STAGES = ['Diajukan', 'Supervisor', 'Manager', 'Direktur'];
  var STAGE_COLORS = ['#0a8cff', '#12a4d6', '#14b8a6', '#22c55e'];
  var deptSel = $('#dept'), stagesEl = $('#stages'), data = window.SIMPLE_STAGES;
  if (deptSel && stagesEl && data) {
    deptSel.innerHTML = Object.keys(data).map(function (k) {
      return '<option>' + k.replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</option>';
    }).join('');
    var renderStages = function () {
      var arr = data[deptSel.value] || [0, 0, 0, 0], max = Math.max.apply(null, arr.concat([1]));
      stagesEl.innerHTML = STAGES.map(function (s, i) {
        return '<div class="stage"><span>' + s + '</span><div class="track"><div class="fill" style="width:' +
          (arr[i] / max * 100).toFixed(1) + '%;background:' + STAGE_COLORS[i] + '"></div></div><b>' + arr[i] + '</b></div>';
      }).join('');
    };
    deptSel.addEventListener('change', renderStages);
    renderStages();
  }
})();
