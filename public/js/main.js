// Tema Master (Kurumsal/Hizmet) — Ana JS

// Navbar scroll efekti (saydam → dark geçiş)
(function(){
  const check = () => {
    const nav = document.querySelector('.navbar');
    if (!nav) return;
    nav.classList.toggle('scrolled', window.scrollY > 30);
  };
  window.addEventListener('scroll', check, { passive: true });
  document.addEventListener('DOMContentLoaded', check);
  check();
})();

// Sayaç animasyonu (istatistik bölümü)
function animateCounter(el) {
  const target = +el.dataset.count;
  const duration = 1800;
  const start = performance.now();
  function step(now) {
    const p = Math.min((now - start) / duration, 1);
    el.textContent = Math.floor(p * target).toLocaleString('tr-TR');
    if (p < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      animateCounter(e.target);
      counterObserver.unobserve(e.target);
    }
  });
}, { threshold: 0.4 });

document.querySelectorAll('[data-count]').forEach(el => counterObserver.observe(el));

// NOT: Buradaki tema demosu kaldırıldı. Sahte bir "mesajınız iletildi"
// yazısı gösterip formu preventDefault ile hiç göndermiyordu. İletişim
// formu sunucu tarafında işleniyor (iletisim.php) — JS'e ihtiyacı yok.

// Aktif menü (sayfa adına göre)
(() => {
  const path = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.navbar-nav .nav-link').forEach(l => {
    if (l.getAttribute('href') === path) l.classList.add('active');
  });
})();
