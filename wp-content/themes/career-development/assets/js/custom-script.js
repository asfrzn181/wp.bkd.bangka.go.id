jQuery(document).ready(function ($) {
  $(window).scroll(function () {
    if ($(this).scrollTop() > 100) {
      $(".back-to-top a").fadeIn();
    } else {
      $(".back-to-top a").fadeOut();
    }
  });

  $(".back-to-top a").click(function () {
    $("html, body").animate({ scrollTop: 0 }, 800);
    return false;
  });
});

/* ============================================================
   Mobile Hamburger Menu — Submenu Accordion Enhancement
   Bekerja di atas WP Navigation Block built-in JS.
   Hanya aktif di mobile (< 768px). Desktop tidak tersentuh.
   ============================================================ */
(function () {
  'use strict';

  var MOBILE_BP = 768;

  /* ── Pastikan hanya jalan di mobile ─────────────────────── */
  function isMobile() {
    return window.innerWidth < MOBILE_BP;
  }

  /* ── Observasi: tunggu overlay mobile terbuka ───────────── */
  function watchOverlay() {
    var overlay = document.querySelector(
      '.main-header .wp-block-navigation__responsive-container'
    );
    if (!overlay) return;

    /* MutationObserver: deteksi class "is-menu-open" ditambahkan */
    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (m) {
        if (m.type === 'attributes' && m.attributeName === 'class') {
          if (overlay.classList.contains('is-menu-open') && isMobile()) {
            initMobileSubmenu(overlay);
          }
        }
      });
    });

    observer.observe(overlay, { attributes: true });
  }

  /* ── Init accordion submenu di dalam overlay ────────────── */
  function initMobileSubmenu(overlay) {
    /* Semua item yang punya submenu */
    var parentItems = overlay.querySelectorAll(
      '.wp-block-navigation-submenu, .wp-block-navigation-item.has-child'
    );

    parentItems.forEach(function (item) {
      var link    = item.querySelector(':scope > .wp-block-navigation-item__content');
      var submenu = item.querySelector(':scope > .wp-block-navigation__submenu-container');

      if (!link || !submenu) return;

      /* Tandai sudah di-init agar tidak dobel */
      if (item.dataset.bkslMobileInit) return;
      item.dataset.bkslMobileInit = '1';

      /* Default: tutup submenu di mobile */
      if (isMobile()) {
        collapseSubmenu(submenu, item);
      }

      /* Klik pada link utama → toggle submenu */
      link.addEventListener('click', function (e) {
        if (!isMobile()) return; /* biarkan desktop jalan normal */

        var isOpen = item.classList.contains('bksl-sub-open');

        /* Tutup semua sibling submenu dulu */
        var siblings = item.parentElement
          ? item.parentElement.querySelectorAll(
              '.wp-block-navigation-submenu.bksl-sub-open,' +
              '.wp-block-navigation-item.has-child.bksl-sub-open'
            )
          : [];
        siblings.forEach(function (sib) {
          if (sib !== item) {
            collapseSubmenu(
              sib.querySelector(':scope > .wp-block-navigation__submenu-container'),
              sib
            );
          }
        });

        /* Toggle item ini */
        if (isOpen) {
          collapseSubmenu(submenu, item);
        } else {
          e.preventDefault(); /* cegah navigasi saat expand */
          expandSubmenu(submenu, item);
        }
      });
    });
  }

  function expandSubmenu(submenu, item) {
    if (!submenu) return;
    submenu.style.display = 'block';
    submenu.style.maxHeight = submenu.scrollHeight + 'px';
    item.classList.add('bksl-sub-open');
  }

  function collapseSubmenu(submenu, item) {
    if (!submenu) return;
    submenu.style.maxHeight = '0';
    item.classList.remove('bksl-sub-open');
    /* Sembunyikan setelah transisi selesai */
    setTimeout(function () {
      if (!item.classList.contains('bksl-sub-open')) {
        submenu.style.display = 'none';
      }
    }, 300);
  }

  /* ── Reset saat resize ke desktop ───────────────────────── */
  var resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      if (!isMobile()) {
        /* Bersihkan semua state mobile agar desktop tidak terganggu */
        document.querySelectorAll('[data-bksl-mobile-init]').forEach(function (item) {
          delete item.dataset.bkslMobileInit;
          item.classList.remove('bksl-sub-open');
          var sub = item.querySelector('.wp-block-navigation__submenu-container');
          if (sub) {
            sub.style.display = '';
            sub.style.maxHeight = '';
          }
        });
      }
    }, 200);
  });

  /* ── Boot ───────────────────────────────────────────────── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', watchOverlay);
  } else {
    watchOverlay();
  }
}());