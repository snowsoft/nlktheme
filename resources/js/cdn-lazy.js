/**
 * cdn-lazy.js — CDN Lazy Loading (LQIP blur-up pattern)
 *
 * .cdn-lazy sınıflı img elemanlarını IntersectionObserver ile izler.
 * data-src / data-srcset yüklenince blur kaldırılır.
 *
 * Yerleştirme: @scripts içinde veya layout'un </body> öncesi.
 */
(function () {
  'use strict';

  if (!('IntersectionObserver' in window)) {
    // Fallback: tüm resimleri hemen yükle
    document.querySelectorAll('img.cdn-lazy').forEach(loadImg);
    return;
  }

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        loadImg(entry.target);
        observer.unobserve(entry.target);
      }
    });
  }, {
    rootMargin: '200px 0px',
    threshold: 0.01
  });

  document.querySelectorAll('img.cdn-lazy').forEach(function (img) {
    observer.observe(img);
  });

  function loadImg(img) {
    if (img.dataset.src) {
      img.src = img.dataset.src;
      img.removeAttribute('data-src');
    }
    if (img.dataset.srcset) {
      img.srcset = img.dataset.srcset;
      img.removeAttribute('data-srcset');
    }
    img.style.filter = 'none';
    img.classList.remove('cdn-lazy');
    img.classList.add('cdn-loaded');
  }
})();
