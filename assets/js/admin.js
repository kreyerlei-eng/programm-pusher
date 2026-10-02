/* ============================================================
   Programm-Pusher – Admin JS
   ============================================================ */
/* global PP_ADMIN, wp */

(function () {
  'use strict';

  var AJAX_URL = PP_ADMIN.ajaxUrl;
  var NONCE    = PP_ADMIN.nonce;

  /* ---- Helpers ---- */
  function post(action, data) {
    var body = new URLSearchParams(data);
    body.set('action', action);
    body.set('nonce',  NONCE);
    return fetch(AJAX_URL, { method: 'POST', body: body })
      .then(function (r) { return r.json(); });
  }

  function showMsg(el, text, type) {
    el.textContent = text;
    el.className   = 'pp-save-msg ' + type;
    setTimeout(function () { el.textContent = ''; el.className = 'pp-save-msg'; }, 4000);
  }

  /* ---- Save Settings ---- */
  var btnSave  = document.getElementById('pp-btn-save');
  var msgEl    = document.getElementById('pp-save-msg');
  var spinner  = document.getElementById('pp-spinner');

  if (btnSave) {
    btnSave.addEventListener('click', function () {
      var slug            = document.getElementById('pp-frontend-slug').value.trim();
      var username        = document.getElementById('pp-username').value.trim();
      var password        = document.getElementById('pp-password').value;
      var prompt          = document.getElementById('pp-master-prompt').value;
      var logoUrl         = document.getElementById('pp-logo-url')
                              ? document.getElementById('pp-logo-url').value
                              : '';
      var cleanupCheckbox = document.getElementById('pp-uninstall-cleanup');
      var uninstallCleanup = (cleanupCheckbox && cleanupCheckbox.checked) ? 'yes' : 'no';

      if (!slug) {
        showMsg(msgEl, PP_ADMIN.errSlug || 'Slug darf nicht leer sein.', 'error');
        return;
      }

      btnSave.disabled = true;
      spinner.classList.add('is-active');

      post('pp_save_settings', {
        slug:              slug,
        username:          username,
        password:          password,
        prompt:            prompt,
        logo_url:          logoUrl,
        uninstall_cleanup: uninstallCleanup,
      }).then(function (res) {
        if (res.success) {
          showMsg(msgEl, (res.data && res.data.message) || 'Gespeichert.', 'success');
          document.getElementById('pp-password').value = '';
          var frontendUrl = res.data && res.data.frontend_url;
          if (frontendUrl) {
            var link = document.getElementById('pp-frontend-url-link');
            if (link) {
              link.href        = frontendUrl;
              link.textContent = frontendUrl;
            }
          }
        } else {
          showMsg(msgEl, (res.data && res.data.message) || 'Fehler beim Speichern.', 'error');
        }
      }).catch(function () {
        showMsg(msgEl, 'Netzwerkfehler.', 'error');
      }).finally(function () {
        btnSave.disabled = false;
        spinner.classList.remove('is-active');
      });
    });
  }

  /* ---- Reset Prompt ---- */
  var btnReset = document.getElementById('pp-btn-reset');

  if (btnReset) {
    btnReset.addEventListener('click', function () {
      if (!confirm(PP_ADMIN.confirmReset || 'Standard-Prompt wirklich wiederherstellen? Alle Änderungen gehen verloren.')) {
        return;
      }
      btnReset.disabled = true;

      post('pp_reset_prompt', {}).then(function (res) {
        if (res.success && res.data && res.data.prompt) {
          document.getElementById('pp-master-prompt').value = res.data.prompt;
          showMsg(msgEl, (res.data && res.data.message) || 'Standard-Prompt wiederhergestellt.', 'success');
        } else {
          showMsg(msgEl, (res.data && res.data.message) || 'Fehler beim Zurücksetzen.', 'error');
        }
      }).catch(function () {
        showMsg(msgEl, 'Netzwerkfehler.', 'error');
      }).finally(function () {
        btnReset.disabled = false;
      });
    });
  }

  /* ---- Slug preview ---- */
  var slugInput = document.getElementById('pp-frontend-slug');
  var slugBase  = PP_ADMIN.siteUrl ? PP_ADMIN.siteUrl.replace(/\/$/, '') : '';

  if (slugInput) {
    slugInput.addEventListener('input', function () {
      var slug = this.value.replace(/[^a-z0-9\-_]/gi, '').toLowerCase();
      var link = document.getElementById('pp-frontend-url-link');
      if (link && slugBase) {
        link.href        = slugBase + '/' + (slug || 'programm-pusher') + '/';
        link.textContent = slugBase + '/' + (slug || 'programm-pusher') + '/';
      }
    });
  }

  /* ---- Logo Upload (WP Media Library) ---- */
  var uploadBtn  = document.getElementById('pp-logo-upload-btn');
  var removeBtn  = document.getElementById('pp-logo-remove-btn');
  var logoInput  = document.getElementById('pp-logo-url');
  var logoPreview = document.getElementById('pp-logo-preview');
  var mediaPicker = null;

  function setLogoPreview(url) {
    if (!logoPreview) return;
    if (url) {
      logoPreview.innerHTML = '<img src="' + url + '" alt="Logo" id="pp-logo-img">';
      if (removeBtn) removeBtn.style.display = '';
    } else {
      logoPreview.innerHTML = '<span class="pp-logo-placeholder" id="pp-logo-placeholder">Kein Logo hinterlegt</span>';
      if (removeBtn) removeBtn.style.display = 'none';
    }
    if (logoInput) logoInput.value = url || '';
  }

  if (uploadBtn) {
    uploadBtn.addEventListener('click', function () {
      if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        alert('WordPress Media Library nicht verfügbar.');
        return;
      }
      if (!mediaPicker) {
        mediaPicker = wp.media({
          title:    PP_ADMIN.mediaTitle  || 'Logo auswählen',
          button:   { text: PP_ADMIN.mediaButton || 'Dieses Bild verwenden' },
          multiple: false,
          library:  { type: 'image' },
        });
        mediaPicker.on('select', function () {
          var attachment = mediaPicker.state().get('selection').first().toJSON();
          setLogoPreview(attachment.url);
        });
      }
      mediaPicker.open();
    });
  }

  if (removeBtn) {
    removeBtn.addEventListener('click', function () {
      setLogoPreview('');
    });
  }

  /* ---- Projekt-Reparatur ---- */
  var btnRepair  = document.getElementById('pp-btn-repair');
  var repairMsg  = document.getElementById('pp-repair-msg');

  if (btnRepair) {
    btnRepair.addEventListener('click', function () {
      btnRepair.disabled = true;
      btnRepair.textContent = '⏳ Repariere…';

      post('pp_repair_projects', {}).then(function (res) {
        if (res.success) {
          if (repairMsg) showMsg(repairMsg, res.data.message || 'Reparatur abgeschlossen.', 'success');
          // Seite neu laden damit Diagnosetabelle aktualisiert wird
          setTimeout(function () { location.reload(); }, 2000);
        } else {
          if (repairMsg) showMsg(repairMsg, res.data && res.data.message ? res.data.message : 'Fehler.', 'error');
          btnRepair.disabled = false;
          btnRepair.textContent = '🔧 Projekte reparieren (Status → publish)';
        }
      }).catch(function () {
        if (repairMsg) showMsg(repairMsg, 'Netzwerkfehler.', 'error');
        btnRepair.disabled = false;
        btnRepair.textContent = '🔧 Projekte reparieren (Status → publish)';
      });
    });
  }

})();
