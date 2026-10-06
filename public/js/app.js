/**
 * DMS ERP — core front-end behaviour
 * - Dark mode toggle (persisted in localStorage, applied before paint via
 *   the inline script in <head> to avoid a flash of wrong theme)
 * - Mobile sidebar open/close
 * - Auto-dismiss alerts
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    initThemeToggle();
    initSidebarToggle();
    initAutoDismissAlerts();
  });

  function initThemeToggle() {
    var toggleBtns = document.querySelectorAll('[data-theme-toggle]');
    var html = document.documentElement;

    function updateIcons() {
      var isDark = html.getAttribute('data-bs-theme') === 'dark';
      document.querySelectorAll('[data-theme-icon]').forEach(function (el) {
        el.classList.toggle('bi-sun-fill', isDark);
        el.classList.toggle('bi-moon-stars-fill', !isDark);
      });
    }

    toggleBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var current = html.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
        var next = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        try {
          localStorage.setItem('dms-theme', next);
        } catch (e) { /* storage unavailable — theme just won't persist */ }
        updateIcons();
      });
    });

    updateIcons();
  }

  function initSidebarToggle() {
    var sidebar = document.querySelector('.sidebar');
    var backdrop = document.querySelector('.sidebar-backdrop');
    var toggleBtns = document.querySelectorAll('[data-sidebar-toggle]');

    if (!sidebar) return;

    function close() {
      sidebar.classList.remove('show');
      if (backdrop) backdrop.classList.remove('show');
    }

    toggleBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        sidebar.classList.toggle('show');
        if (backdrop) backdrop.classList.toggle('show');
      });
    });

    if (backdrop) {
      backdrop.addEventListener('click', close);
    }

    // Close sidebar automatically when a nav link is tapped on mobile
    sidebar.querySelectorAll('.nav-link').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth < 992) close();
      });
    });
  }

  function initAutoDismissAlerts() {
    document.querySelectorAll('.alert[data-auto-dismiss]').forEach(function (alertEl) {
      setTimeout(function () {
        var alert = bootstrap.Alert.getOrCreateInstance(alertEl);
        alert.close();
      }, 5000);
    });
  }
})();
