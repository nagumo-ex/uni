/* ══════════════════════════════════════════
   GLOVAL S.A. — main.js
   ══════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  // ── SCROLL PROGRESS BAR ───────────────────────────────────────
  const scrollLine = document.getElementById('scroll-line');
  window.addEventListener('scroll', () => {
    const doc = document.documentElement;
    const pct = (doc.scrollTop / (doc.scrollHeight - doc.clientHeight)) * 100;
    scrollLine.style.width = pct + '%';
  }, { passive: true });


  // ── REVEAL ON SCROLL ─────────────────────────────────────────
  const revealEls = document.querySelectorAll('.reveal');
  const revealObs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
  }, { threshold: 0.1 });
  revealEls.forEach(el => revealObs.observe(el));


  // ── COUNT-UP ANIMATION ────────────────────────────────────────
  function countUp(el, target) {
    let val = 0;
    const dur = 1800, step = 16;
    const inc = target / (dur / step);
    const t = setInterval(() => {
      val += inc;
      if (val >= target) { el.textContent = target; clearInterval(t); }
      else el.textContent = Math.floor(val);
    }, step);
  }

  const statsObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.querySelectorAll('[data-target]').forEach(n =>
          countUp(n, parseInt(n.dataset.target))
        );
        statsObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.3 });

  const statsBar = document.querySelector('.stats-bar');
  if (statsBar) statsObs.observe(statsBar);


  // ── ACORDEÓN ─────────────────────────────────────────────────
  document.querySelectorAll('.accordion-header').forEach(header => {
    header.addEventListener('click', () => {
      const item    = header.closest('.accordion-item');
      const isOpen  = item.classList.contains('open');

      // Cerrar todos los del mismo acordeón
      const siblings = item.parentElement.querySelectorAll('.accordion-item');
      siblings.forEach(s => s.classList.remove('open'));

      // Abrir el clicado (toggle)
      if (!isOpen) item.classList.add('open');
    });
  });

  // Abrir el primer ítem de cada acordeón por defecto
  document.querySelectorAll('.accordion').forEach(acc => {
    const first = acc.querySelector('.accordion-item');
    if (first) first.classList.add('open');
  });


  // ── MENÚ HAMBURGUESA ─────────────────────────────────────────
  const hamburger  = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('nav-mobile');
  const mobileClose = document.getElementById('mobile-close');

  function openMenu() {
    hamburger.classList.add('open');
    mobileMenu.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeMenu() {
    hamburger.classList.remove('open');
    mobileMenu.classList.remove('open');
    document.body.style.overflow = '';
  }

  if (hamburger) hamburger.addEventListener('click', openMenu);
  if (mobileClose) mobileClose.addEventListener('click', closeMenu);

  // Cerrar al hacer click en un link del menú móvil
  if (mobileMenu) {
    mobileMenu.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', closeMenu);
    });
  }


  // ── NAV ACTIVE LINK ──────────────────────────────────────────
  const sections  = document.querySelectorAll('section[id]');
  const navAnchors = document.querySelectorAll('.nav-links a');

  window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(sec => {
      if (window.scrollY >= sec.offsetTop - 100) current = sec.id;
    });
    navAnchors.forEach(a => {
      a.classList.toggle('active', a.getAttribute('href') === '#' + current);
    });
  }, { passive: true });


  // ── FORMULARIO DE CONTACTO ────────────────────────────────────
  window.sendForm = function () {
    const name  = document.getElementById('fname')?.value.trim();
    const email = document.getElementById('femail')?.value.trim();
    const msg   = document.getElementById('fmsg')?.value.trim();

    if (!name || !email || !msg) {
      alert('Por favor completa todos los campos.');
      return;
    }

    const fb = document.getElementById('form-msg');
    fb.style.display = 'block';
    document.getElementById('fname').value  = '';
    document.getElementById('femail').value = '';
    document.getElementById('fmsg').value   = '';

    setTimeout(() => { fb.style.display = 'none'; }, 4500);
  };

});
