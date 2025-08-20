document.addEventListener('DOMContentLoaded', function () {
  // Bootstrap toast for flash messages
  try {
    var toastEl = document.getElementById('liveToast');
    if (toastEl && window.bootstrap && bootstrap.Toast) {
      var t = new bootstrap.Toast(toastEl, { delay: 4500 });
      t.show();
    }
  } catch (e) {}

  // Live search for homepage posts
  (function () {
    const input = document.getElementById('searchInput');
    if (!input) return; // Only on homepage

    const items = Array.from(document.querySelectorAll('.post-item'));
    const countBadge = document.getElementById('searchCount');
    const noResultsAlert = document.getElementById('noResultsAlert');

    const filter = () => {
      const q = (input.value || '').trim().toLowerCase();
      let visible = 0;
      items.forEach((el) => {
        const text = el.textContent.toLowerCase();
        const match = q === '' || text.includes(q);
        el.classList.toggle('d-none', !match);
        if (match) visible++;
      });
      if (countBadge) countBadge.textContent = String(visible);
      if (noResultsAlert) noResultsAlert.classList.toggle('d-none', visible !== 0);
    };

    input.addEventListener('input', filter);
    filter();
  })();

  // --- Admin: dashboard search ---
  (function () {
    const input = document.getElementById('adminSearch');
    if (!input) return;
    const rows = Array.from(document.querySelectorAll('#adminTableBody .admin-post-row'));
    const countEl = document.getElementById('adminSearchCount');
    const noResults = document.getElementById('adminNoResults');
    const filter = () => {
      const q = (input.value || '').trim().toLowerCase();
      let visible = 0;
      rows.forEach((tr) => {
        const text = tr.textContent.toLowerCase();
        const match = q === '' || text.includes(q);
        tr.classList.toggle('d-none', !match);
        if (match) visible++;
      });
      if (countEl) countEl.textContent = String(visible);
      if (noResults) noResults.classList.toggle('d-none', visible !== 0);
    };
    input.addEventListener('input', filter);
    filter();
  })();

  // --- Admin: delete confirmation modal ---
  (function () {
    const modalEl = document.getElementById('deleteConfirmModal');
    if (!modalEl || !window.bootstrap) return;
    const bsModal = new bootstrap.Modal(modalEl);
    const titleEl = document.getElementById('deletePostTitle');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    let targetForm = null;
    document.querySelectorAll('.js-delete-btn').forEach((btn) => {
      btn.addEventListener('click', function () {
        const form = this.closest('form');
        targetForm = form;
        if (titleEl) titleEl.textContent = this.getAttribute('data-post-title') || '';
        bsModal.show();
      });
    });
    if (confirmBtn) {
      confirmBtn.addEventListener('click', function () {
        if (!targetForm) return;
        this.disabled = true;
        const submitBtn = targetForm.querySelector('button[type="submit"], .js-delete-btn');
        if (submitBtn) submitBtn.disabled = true;
        // Switch the button to submit mode if needed
        const explicitSubmit = targetForm.querySelector('button[type="submit"]');
        if (explicitSubmit) {
          explicitSubmit.click();
        } else {
          targetForm.submit();
        }
      });
    }
  })();

  // --- Admin: cover image preview ---
  (function () {
    const fileInput = document.querySelector('input[name="cover_image"]');
    const img = document.getElementById('coverPreview');
    if (!fileInput || !img) return;
    fileInput.addEventListener('change', function () {
      const f = this.files && this.files[0];
      if (!f) { img.classList.add('d-none'); img.removeAttribute('src'); return; }
      const url = URL.createObjectURL(f);
      img.src = url;
      img.classList.remove('d-none');
    });
  })();

  // --- Admin: video URL preview (YouTube/Vimeo/MP4) ---
  (function () {
    const input = document.querySelector('input[name="video_url"]');
    const preview = document.getElementById('videoPreview');
    if (!input || !preview) return;
    const render = () => {
      const val = (input.value || '').trim();
      preview.innerHTML = '';
      if (!val) return;
      let iframe = null;
      let video = null;
      // YouTube patterns
      const ytMatch = val.match(/(?:youtu\.be\/([\w-]{6,})|youtube\.com\/(?:watch\?v=|embed\/|shorts\/)([\w-]{6,}))/i);
      if (ytMatch) {
        const id = ytMatch[1] || ytMatch[2];
        iframe = document.createElement('iframe');
        iframe.width = '560'; iframe.height = '315';
        iframe.src = 'https://www.youtube.com/embed/' + encodeURIComponent(id);
        iframe.title = 'YouTube video preview';
        iframe.frameBorder = '0';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
      }
      // Vimeo pattern
      const vmMatch = !iframe && val.match(/vimeo\.com\/(?:video\/)?(\d+)/i);
      if (vmMatch) {
        const id = vmMatch[1];
        iframe = document.createElement('iframe');
        iframe.width = '560'; iframe.height = '315';
        iframe.src = 'https://player.vimeo.com/video/' + encodeURIComponent(id);
        iframe.title = 'Vimeo video preview';
        iframe.frameBorder = '0';
        iframe.allow = 'autoplay; fullscreen; picture-in-picture';
        iframe.allowFullscreen = true;
      }
      // Direct MP4
      const mp4Match = !iframe && /\.mp4(\?|$)/i.test(val);
      if (mp4Match) {
        video = document.createElement('video');
        video.controls = true; video.width = 560; video.height = 315;
        const src = document.createElement('source');
        src.src = val; src.type = 'video/mp4';
        video.appendChild(src);
      }
      if (iframe) preview.appendChild(iframe);
      else if (video) preview.appendChild(video);
    };
    input.addEventListener('input', render);
    render();
  })();

  // --- Login: password visibility toggle ---
  (function () {
    const btn = document.getElementById('togglePassword');
    const input = document.getElementById('passwordInput');
    if (!btn || !input) return;
    btn.addEventListener('click', function () {
      const isText = input.getAttribute('type') === 'text';
      input.setAttribute('type', isText ? 'password' : 'text');
      btn.setAttribute('aria-pressed', String(!isText));
      btn.textContent = isText ? 'Show' : 'Hide';
    });
  })();
});
