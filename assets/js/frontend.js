/**
 * Programm-Pusher Frontend – SPA-Logik
 * Kein jQuery, reines Vanilla JS
 */

'use strict';

const PP = {
  csrf: null,
  currentProjectId: null,
  songIndex: 0,
  ztIndex: 0,

  // ============================================================
  // Init
  // ============================================================
  init() {
    this.bindLogin();
    this.bindLogout();
    this.bindDashboard();
    this.bindProject();
    this.bindPrompt();
    this.bindNavHighlight();
    // Klick außerhalb von Karten-Menüs schließt alle offenen Menüs (einmalig registriert)
    document.addEventListener('click', () => this.closeAllCardMenus());
    this.checkAuth();
  },

  // ============================================================
  // AJAX-Helfer
  // ============================================================
  async ajax(action, data = {}) {
    const body = new URLSearchParams({ action, ...data });
    const resp = await fetch(PP_AJAX, { method: 'POST', body, credentials: 'same-origin' });
    const text = await resp.text();
    try {
      return JSON.parse(text);
    } catch {
      // Server hat kein valides JSON geliefert (z. B. PHP-Notice vor dem JSON)
      return { success: false, data: { message: 'Ungültige Server-Antwort. Bitte Seite neu laden.' } };
    }
  },

  // ============================================================
  // Views
  // ============================================================
  showView(name) {
    // Ladescreen beim ersten View-Wechsel ausblenden
    const loader = document.getElementById('pp-app-loader');
    if (loader) loader.style.display = 'none';
    document.querySelectorAll('.pp-view').forEach(v => {
      v.classList.remove('pp-view-active');
    });
    const v = document.getElementById('pp-view-' + name);
    if (v) v.classList.add('pp-view-active');
  },

  // ============================================================
  // Auth-Check beim Laden
  // ============================================================
  async checkAuth() {
    try {
      const r = await this.ajax('pp_check_auth');
      if (r.success && r.data.authenticated) {
        this.csrf = r.data.token;
        this.showView('dashboard');
        this.loadProjects();
      } else {
        this.showView('login');
      }
    } catch {
      this.showView('login');
    }
  },

  // ============================================================
  // Login
  // ============================================================
  bindLogin() {
    const form = document.getElementById('pp-login-form');
    if (!form) return;
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('pp-login-btn');
      const err = document.getElementById('pp-login-error');
      const username = document.getElementById('pp-username').value.trim();
      const password = document.getElementById('pp-password').value;

      btn.disabled = true;
      btn.textContent = 'Anmelden…';
      err.style.display = 'none';

      try {
        const r = await this.ajax('pp_login', { username, password });
        if (r.success) {
          this.csrf = r.data.token;
          document.getElementById('pp-password').value = '';
          this.showView('dashboard');
          this.loadProjects();
        } else {
          err.textContent = r.data.message || 'Ungültige Zugangsdaten.';
          err.style.display = 'block';
        }
      } catch {
        err.textContent = 'Verbindungsfehler.';
        err.style.display = 'block';
      } finally {
        btn.disabled = false;
        btn.textContent = 'Einloggen';
      }
    });
  },

  // ============================================================
  // Logout
  // ============================================================
  bindLogout() {
    ['pp-logout-btn', 'pp-logout-btn-2'].forEach(id => {
      const btn = document.getElementById(id);
      if (!btn) return;
      btn.addEventListener('click', async () => {
        await this.ajax('pp_logout');
        this.csrf = null;
        this.showView('login');
      });
    });
  },

  // ============================================================
  // Dashboard
  // ============================================================
  bindDashboard() {
    const btn = document.getElementById('pp-new-project-btn');
    if (btn) btn.addEventListener('click', () => this.createProject());
  },

  async loadProjects() {
    const grid = document.getElementById('pp-projects-list');
    if (!grid) return;
    grid.innerHTML = '<div class="pp-loading">Projekte werden geladen…</div>';
    try {
      const r = await this.ajax('pp_get_projects');
      if (!r.success) { grid.innerHTML = '<div class="pp-empty">Fehler beim Laden.</div>'; return; }
      const projects = r.data.projects;
      if (!projects.length) {
        grid.innerHTML = '<div class="pp-empty">Noch keine Projekte vorhanden.<br>Lege dein erstes Projekt an.</div>';
        return;
      }
      grid.innerHTML = projects.map(p => `
        <div class="pp-project-card" data-id="${p.id}">
          <div class="pp-project-card-title">${this.esc(p.title)}</div>
          <div class="pp-project-card-date">Zuletzt geändert: ${this.esc(p.modified)}</div>
          <div class="pp-project-card-actions">
            <button class="pp-btn pp-btn-secondary pp-open-btn" data-id="${p.id}">Öffnen</button>
          </div>
          <button class="pp-card-menu-btn" data-id="${p.id}" title="Optionen">⋯</button>
        </div>
      `).join('');

      // Karte öffnen per Klick
      grid.querySelectorAll('.pp-open-btn').forEach(btn => {
        btn.addEventListener('click', (e) => { e.stopPropagation(); this.openProject(+btn.dataset.id); });
      });
      // Drei-Punkte-Menü
      grid.querySelectorAll('.pp-card-menu-btn').forEach(btn => {
        btn.addEventListener('click', (e) => { e.stopPropagation(); this.toggleCardMenu(+btn.dataset.id, btn); });
      });
    } catch {
      grid.innerHTML = '<div class="pp-empty">Verbindungsfehler.</div>';
    }
  },

  // ============================================================
  // Projektkarten-Menü
  // ============================================================
  _activeMenuId: null,

  closeAllCardMenus() {
    document.querySelectorAll('.pp-card-dropdown').forEach(d => d.remove());
    document.querySelectorAll('.pp-card-menu-btn.active').forEach(b => b.classList.remove('active'));
    this._activeMenuId = null;
  },

  toggleCardMenu(id, btn) {
    if (this._activeMenuId === id) { this.closeAllCardMenus(); return; }
    this.closeAllCardMenus();
    this._activeMenuId = id;
    btn.classList.add('active');

    const card  = btn.closest('.pp-project-card');
    const title = card.querySelector('.pp-project-card-title').textContent;
    const menu  = document.createElement('div');
    menu.className = 'pp-card-dropdown';
    menu.innerHTML = `
      <button class="pp-dd-edit"   data-id="${id}">✏️ Bearbeiten</button>
      <button class="pp-dd-rename" data-id="${id}">🔤 Umbenennen</button>
      <button class="pp-dd-dup"    data-id="${id}">📋 Duplizieren</button>
      <button class="pp-dd-delete" data-id="${id}">🗑 Löschen</button>
    `;
    card.appendChild(menu);

    menu.querySelector('.pp-dd-edit').addEventListener('click', (e) => {
      e.stopPropagation(); this.closeAllCardMenus(); this.openProject(id);
    });
    menu.querySelector('.pp-dd-rename').addEventListener('click', (e) => {
      e.stopPropagation(); this.closeAllCardMenus(); this.showInlineRename(id, card, title);
    });
    menu.querySelector('.pp-dd-dup').addEventListener('click', (e) => {
      e.stopPropagation(); this.closeAllCardMenus(); this.duplicateProject(id);
    });
    menu.querySelector('.pp-dd-delete').addEventListener('click', (e) => {
      e.stopPropagation(); this.closeAllCardMenus(); this.showInlineDeleteConfirm(id, card);
    });
    menu.addEventListener('click', (e) => e.stopPropagation());
  },

  showInlineRename(id, card, currentTitle) {
    // Bestehende Rename-Widgets entfernen
    card.querySelectorAll('.pp-card-rename-wrap').forEach(el => el.remove());
    const wrap = document.createElement('div');
    wrap.className = 'pp-card-rename-wrap';
    wrap.innerHTML = `
      <input class="pp-card-rename-input" type="text" value="${this.esc(currentTitle)}" maxlength="120">
      <button class="pp-card-rename-ok">✓</button>
      <button class="pp-card-rename-cancel">✕</button>
    `;
    card.appendChild(wrap);
    const input  = wrap.querySelector('.pp-card-rename-input');
    const okBtn  = wrap.querySelector('.pp-card-rename-ok');
    const canBtn = wrap.querySelector('.pp-card-rename-cancel');
    input.focus(); input.select();

    const doRename = () => {
      const newTitle = input.value.trim();
      if (!newTitle) { input.focus(); return; }
      this.renameProject(id, newTitle, card);
      wrap.remove();
    };
    okBtn.addEventListener('click', doRename);
    canBtn.addEventListener('click', () => wrap.remove());
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') doRename();
      if (e.key === 'Escape') wrap.remove();
    });
    wrap.addEventListener('click', (e) => e.stopPropagation());
  },

  showInlineDeleteConfirm(id, card) {
    card.querySelectorAll('.pp-card-confirm-delete').forEach(el => el.remove());
    const box = document.createElement('div');
    box.className = 'pp-card-confirm-delete';
    box.innerHTML = `
      <p>Projekt wirklich löschen?<br>Diese Aktion kann nicht rückgängig gemacht werden.</p>
      <div class="pp-card-confirm-actions">
        <button class="pp-card-confirm-yes">Ja, löschen</button>
        <button class="pp-card-confirm-no">Abbrechen</button>
      </div>
    `;
    card.appendChild(box);
    box.querySelector('.pp-card-confirm-yes').addEventListener('click', (e) => {
      e.stopPropagation(); this.deleteProject(id);
    });
    box.querySelector('.pp-card-confirm-no').addEventListener('click', (e) => {
      e.stopPropagation(); box.remove();
    });
    box.addEventListener('click', (e) => e.stopPropagation());
  },

  async renameProject(id, newTitle, card) {
    if (!this.csrf) await this.refreshCsrf();
    try {
      const r = await this.ajax('pp_rename_project', { id, title: newTitle, csrf: this.csrf });
      if (r.success) {
        const titleEl = card.querySelector('.pp-project-card-title');
        if (titleEl) titleEl.textContent = r.data.title;
      }
    } catch { /* ignorieren */ }
  },

  async duplicateProject(id) {
    if (!this.csrf) await this.refreshCsrf();
    try {
      const r = await this.ajax('pp_duplicate_project', { id, csrf: this.csrf });
      if (r.success) this.loadProjects();
    } catch { alert('Duplizieren fehlgeschlagen.'); }
  },

  async createProject() {
    const btn = document.getElementById('pp-new-project-btn');
    const origText = btn ? btn.textContent : '';
    if (btn) { btn.disabled = true; btn.textContent = '⏳ …'; }

    if (!this.csrf) {
      try { await this.refreshCsrf(); } catch {
        if (btn) { btn.disabled = false; btn.textContent = origText; }
        alert('Sitzung abgelaufen. Bitte Seite neu laden.');
        return;
      }
    }

    try {
      const r = await this.ajax('pp_new_project', { csrf: this.csrf });
      if (r.success) {
        this.openProject(r.data.id);
      } else {
        alert((r.data && r.data.message) || 'Projekt konnte nicht erstellt werden.');
      }
    } catch {
      alert('Verbindungsfehler. Bitte Seite neu laden.');
    } finally {
      if (btn) { btn.disabled = false; btn.textContent = origText; }
    }
  },

  async deleteProject(id) {
    if (!this.csrf) await this.refreshCsrf();
    try {
      const r = await this.ajax('pp_delete_project', { id, csrf: this.csrf });
      if (r.success) this.loadProjects();
    } catch { alert('Löschen fehlgeschlagen.'); }
  },

  // ============================================================
  // Projekt öffnen / laden
  // ============================================================
  async openProject(id) {
    this.currentProjectId = id;
    this.showView('project');
    try {
      const r = await this.ajax('pp_get_project', { id });
      if (!r.success) { alert('Projekt konnte nicht geladen werden.'); return; }
      const p = r.data.project;
      this.populateForm(p);
    } catch { alert('Verbindungsfehler.'); }
  },

  // ============================================================
  // Formular befüllen
  // ============================================================
  populateForm(p) {
    const form = document.getElementById('pp-project-form');
    if (!form) return;

    document.getElementById('pp-project-id').value = p.id || '';
    document.getElementById('pp-project-title-display').textContent = p.title || 'Projekt';

    // Einfache Text/Select/Textarea-Felder
    const simple = [
      'kuenstlername','programmname','showdauer','sprache_der_show','programmkontext',
      'sprachstil','sprachstil_custom','energielevel','publikumsnaehe',
      'kuenstlerpersoenlichkeit_custom',
      'charakterzuege','no_go_kuenstlerwirkung','referenzen',
      'zielpublikum','altersstruktur','interaktionsgrad',
      'tonlage_ggue_publikum','tonlage_custom','publikumsbesonderheiten',
      'gewichtung_humor_tiefe','gewichtung_custom',
      'humortyp_custom',
      'roter_faden','zentrale_themen','emotionale_kurve','gesamtwirkung','no_go_inhalte',
      'form_der_zwischenteile_custom','moderationsart_custom','sketchart_custom',
      'running_gag_details','publikumsinteraktion_details',
      'umgang_mit_vorhandenem_material','vorhandene_moderationen',
    ];
    simple.forEach(name => {
      const el = form.querySelector(`[name="${name}"]`);
      if (el && p[name] != null) el.value = p[name];
    });

    // Radio-Buttons
    ['running_gags','publikumsinteraktion','vorhandenes_material_ja_nein'].forEach(name => {
      const val = p[name] || (name === 'running_gags' || name === 'publikumsinteraktion' || name === 'vorhandenes_material_ja_nein' ? 'Nein' : '');
      const radio = form.querySelector(`[name="${name}"][value="${val}"]`);
      if (radio) radio.checked = true;
    });

    // Checkboxen
    ['kuenstlerpersoenlichkeit','humortyp','form_der_zwischenteile','moderationsart','sketchart'].forEach(name => {
      const group = form.querySelector(`.pp-checkgroup[data-name="${name}"]`);
      if (!group) return;
      const selected = Array.isArray(p[name]) ? p[name] : [];
      group.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.checked = selected.includes(cb.value);
        cb.closest('.pp-check').classList.toggle('pp-checked', cb.checked);
      });
    });

    // Songs
    const songsContainer = document.getElementById('songs-container');
    songsContainer.innerHTML = '';
    this.songIndex = 0;
    const songs = Array.isArray(p.songs) ? p.songs : [];
    songs.forEach(s => this.addSong(s));

    // Zwischenteile
    const ztContainer = document.getElementById('zt-container');
    ztContainer.innerHTML = '';
    this.ztIndex = 0;
    const zt = Array.isArray(p.zwischenteile) ? p.zwischenteile : [];
    zt.forEach(z => this.addZt(z));

    // Prompt
    const promptArea = document.getElementById('pp-prompt-output');
    if (promptArea) promptArea.value = p.generated_prompt || '';
  },

  // ============================================================
  // Formular-Daten sammeln
  // ============================================================
  collectFormData() {
    const form = document.getElementById('pp-project-form');
    const data = {};

    // Alle Inputs/Selects/Textareas
    form.querySelectorAll('input:not([type="checkbox"]):not([type="radio"]), select, textarea').forEach(el => {
      if (!el.name || el.name === 'id') return;
      data[el.name] = el.value;
    });

    // Radio
    ['running_gags','publikumsinteraktion','vorhandenes_material_ja_nein'].forEach(name => {
      const checked = form.querySelector(`[name="${name}"]:checked`);
      data[name] = checked ? checked.value : 'Nein';
    });

    // Checkboxen
    ['kuenstlerpersoenlichkeit','humortyp','form_der_zwischenteile','moderationsart','sketchart'].forEach(name => {
      const group = form.querySelector(`.pp-checkgroup[data-name="${name}"]`);
      if (!group) { data[name] = []; return; }
      data[name] = [...group.querySelectorAll('input:checked')].map(cb => cb.value);
    });

    // Songs
    data.songs = [];
    document.querySelectorAll('#songs-container .pp-rep-item').forEach(item => {
      data.songs.push({
        title: item.querySelector('[data-field="title"]')?.value || '',
        mood:  item.querySelector('[data-field="mood"]')?.value  || '',
        note:  item.querySelector('[data-field="note"]')?.value  || '',
      });
    });

    // Zwischenteile
    data.zwischenteile = [];
    document.querySelectorAll('#zt-container .pp-rep-item').forEach(item => {
      data.zwischenteile.push({
        title:           item.querySelector('[data-field="title"]')?.value          || '',
        position:        item.querySelector('[data-field="position"]')?.value       || '',
        funktion:        item.querySelector('[data-field="funktion"]')?.value       || '',
        funktion_custom: item.querySelector('[data-field="funktion_custom"]')?.value|| '',
        timing:          item.querySelector('[data-field="timing"]')?.value         || '',
        text:            item.querySelector('[data-field="text"]')?.value           || '',
        hinweis:         item.querySelector('[data-field="hinweis"]')?.value        || '',
      });
    });

    return data;
  },

  // ============================================================
  // Speichern
  // ============================================================
  bindProject() {
    const saveBtn = document.getElementById('pp-save-btn');
    if (saveBtn) saveBtn.addEventListener('click', () => this.saveProject());

    const backBtn = document.getElementById('pp-back-btn');
    if (backBtn) backBtn.addEventListener('click', () => {
      this.showView('dashboard');
      this.loadProjects();
    });

    // Song-Buttons
    const addSong = document.getElementById('add-song-btn');
    if (addSong) addSong.addEventListener('click', () => this.addSong());

    const addZt = document.getElementById('add-zt-btn');
    if (addZt) addZt.addEventListener('click', () => this.addZt());

    // Checkbox-Styling
    document.addEventListener('change', (e) => {
      if (e.target.type === 'checkbox' && e.target.closest('.pp-checkgroup')) {
        e.target.closest('.pp-check').classList.toggle('pp-checked', e.target.checked);
      }
    });
  },

  async saveProject() {
    if (!this.csrf) await this.refreshCsrf();
    const id = this.currentProjectId;
    if (!id) return;

    const saveBtn = document.getElementById('pp-save-btn');
    const status  = document.getElementById('pp-save-status');

    saveBtn.disabled = true;
    saveBtn.textContent = '⏳ Speichern…';

    const formData = this.collectFormData();

    try {
      const r = await this.ajax('pp_save_project', {
        id,
        csrf: this.csrf,
        data: JSON.stringify(formData),
      });

      if (r.success) {
        document.getElementById('pp-project-title-display').textContent = r.data.title;
        const promptArea = document.getElementById('pp-prompt-output');
        if (promptArea && typeof r.data.prompt === 'string') promptArea.value = r.data.prompt;
        this.showStatus(status, '✓ Gespeichert', 'success');
      } else {
        this.showStatus(status, '✗ Fehler: ' + (r.data?.message || 'Unbekannt'), 'error');
      }
    } catch {
      this.showStatus(status, '✗ Verbindungsfehler', 'error');
    } finally {
      saveBtn.disabled = false;
      saveBtn.textContent = '💾 Speichern';
    }
  },

  showStatus(el, msg, type) {
    el.textContent = msg;
    el.className = 'pp-save-status ' + type;
    el.style.display = 'block';
    // Fehler bleiben 9 Sekunden sichtbar, Erfolg 4 Sekunden
    const duration = (type === 'error') ? 9000 : 4000;
    clearTimeout(el._hideTimer);
    el._hideTimer = setTimeout(() => { el.style.display = 'none'; }, duration);
  },

  // ============================================================
  // Prompt
  // ============================================================
  bindPrompt() {
    const regenBtn = document.getElementById('pp-regenerate-btn');
    const copyBtn  = document.getElementById('pp-copy-btn');
    const dlBtn    = document.getElementById('pp-download-btn');

    if (regenBtn) regenBtn.addEventListener('click', () => this.generatePrompt());
    if (copyBtn)  copyBtn.addEventListener('click',  () => this.copyPrompt());
    if (dlBtn)    dlBtn.addEventListener('click',    () => this.downloadPrompt());
  },

  async generatePrompt() {
    if (!this.csrf) await this.refreshCsrf();
    const id  = this.currentProjectId;
    const btn = document.getElementById('pp-regenerate-btn');
    const msg = document.getElementById('pp-prompt-msg');

    btn.disabled = true;
    btn.textContent = '⏳ Generiere…';
    msg.textContent = '';
    msg.className = 'pp-prompt-msg';

    try {
      const r = await this.ajax('pp_generate_prompt', { id, csrf: this.csrf });
      if (r.success) {
        document.getElementById('pp-prompt-output').value = r.data.prompt;
        msg.textContent = '✓ Prompt generiert';
        msg.className = 'pp-prompt-msg success';
      } else {
        msg.textContent = '✗ ' + (r.data?.message || 'Fehler');
        msg.className = 'pp-prompt-msg error';
      }
    } catch {
      msg.textContent = '✗ Verbindungsfehler';
      msg.className = 'pp-prompt-msg error';
    } finally {
      btn.disabled = false;
      btn.textContent = '🔄 Prompt neu generieren';
      setTimeout(() => { msg.textContent = ''; }, 4000);
    }
  },

  copyPrompt() {
    const area = document.getElementById('pp-prompt-output');
    const msg  = document.getElementById('pp-prompt-msg');
    const text = area.value;
    if (!text) { msg.textContent = 'Kein Prompt vorhanden.'; msg.className = 'pp-prompt-msg error'; return; }

    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(() => {
        msg.textContent = '✓ In Zwischenablage kopiert!';
        msg.className = 'pp-prompt-msg success';
        setTimeout(() => { msg.textContent = ''; }, 3000);
      });
    } else {
      area.select();
      document.execCommand('copy');
      msg.textContent = '✓ Kopiert!';
      msg.className = 'pp-prompt-msg success';
      setTimeout(() => { msg.textContent = ''; }, 3000);
    }
  },

  downloadPrompt() {
    const text = document.getElementById('pp-prompt-output').value;
    if (!text) return;
    const title = document.getElementById('pp-project-title-display').textContent || 'prompt';
    const fname = title.replace(/[^a-z0-9äöüß\s-]/gi, '').trim().replace(/\s+/g, '-') + '.txt';
    const blob  = new Blob([text], { type: 'text/plain;charset=utf-8' });
    const url   = URL.createObjectURL(blob);
    const a     = document.createElement('a');
    a.href = url; a.download = fname;
    document.body.appendChild(a); a.click();
    document.body.removeChild(a); URL.revokeObjectURL(url);
  },

  // ============================================================
  // Repeatable Songs
  // ============================================================
  addSong(data = {}) {
    const idx = this.songIndex++;
    const item = document.createElement('div');
    item.className = 'pp-rep-item';
    item.innerHTML = `
      <div class="pp-rep-handle" title="Verschieben">☰</div>
      <div class="pp-rep-fields">
        <div class="pp-rep-field">
          <label>Songtitel *</label>
          <input type="text" data-field="title" value="${this.esc(data.title || '')}" placeholder="Songtitel">
        </div>
        <div class="pp-rep-field">
          <label>Stimmung / Funktion im Set</label>
          <input type="text" data-field="mood" value="${this.esc(data.mood || '')}" placeholder="z. B. ruhiger Einstieg, humorvolle Entlastung">
        </div>
        <div class="pp-rep-field">
          <label>Zusatznotiz</label>
          <textarea data-field="note" rows="2">${this.esc(data.note || '')}</textarea>
        </div>
      </div>
      <div class="pp-rep-actions">
        <button type="button" class="pp-rep-btn pp-move-up" title="Nach oben">▲</button>
        <button type="button" class="pp-rep-btn pp-move-down" title="Nach unten">▼</button>
        <button type="button" class="pp-rep-btn pp-rep-btn-del" title="Entfernen">✕</button>
      </div>`;
    this.bindRepItem(item, 'songs-container');
    document.getElementById('songs-container').appendChild(item);
    this.updateZtPositions();
  },

  // ============================================================
  // Repeatable Zwischenteile
  // ============================================================
  addZt(data = {}) {
    const idx = this.ztIndex++;
    const songs = this.getSongTitles();
    const positions = this.buildPositions(songs);
    const funcs = ['atmosphärischer Übergang','humoristische Entlastung','emotionaler Tiefgang','Verbindung zweier Themenwelten','Auflockerung','Publikumsnähe','Vorbereitung eines starken Songs','Nachklang eines Songs','Verdichtung des roten Fadens','Running Gag aufnehmen','Reflexion','Überraschungsmoment'];

    const item = document.createElement('div');
    item.className = 'pp-rep-item';
    const posOpts = positions.map(p => `<option value="${this.esc(p)}" ${data.position === p ? 'selected' : ''}>${this.esc(p)}</option>`).join('');
    const funcOpts = ['<option value="">– Funktion wählen –</option>', ...funcs.map(f => `<option value="${this.esc(f)}" ${data.funktion === f ? 'selected' : ''}>${this.esc(f)}</option>`)].join('');

    item.innerHTML = `
      <div class="pp-rep-handle" title="Verschieben">☰</div>
      <div class="pp-rep-fields">
        <div class="pp-rep-field">
          <label>Interner Abschnittstitel (optional)</label>
          <input type="text" data-field="title" value="${this.esc(data.title || '')}" placeholder="z. B. Eröffnung, Bruch, Finale">
        </div>
        <div class="pp-rep-field">
          <label>Position *</label>
          <select data-field="position">${posOpts}</select>
        </div>
        <div class="pp-rep-field">
          <label>Funktionshinweis</label>
          <select data-field="funktion">${funcOpts}</select>
          <input type="text" data-field="funktion_custom" value="${this.esc(data.funktion_custom || '')}" placeholder="Oder Freitext-Funktionshinweis" style="margin-top:6px">
        </div>
        <div class="pp-rep-field">
          <label>Timing-Vorgabe (optional)</label>
          <input type="text" data-field="timing" value="${this.esc(data.timing || '')}" placeholder="z. B. ca. 3 Minuten">
        </div>
        <div class="pp-rep-field">
          <label>Vorhandener Text (optional)</label>
          <textarea data-field="text" rows="3">${this.esc(data.text || '')}</textarea>
        </div>
        <div class="pp-rep-field">
          <label>Zusatzhinweis</label>
          <textarea data-field="hinweis" rows="2">${this.esc(data.hinweis || '')}</textarea>
        </div>
      </div>
      <div class="pp-rep-actions">
        <button type="button" class="pp-rep-btn pp-move-up" title="Nach oben">▲</button>
        <button type="button" class="pp-rep-btn pp-move-down" title="Nach unten">▼</button>
        <button type="button" class="pp-rep-btn pp-rep-btn-del" title="Entfernen">✕</button>
      </div>`;
    this.bindRepItem(item, 'zt-container');
    document.getElementById('zt-container').appendChild(item);
  },

  bindRepItem(item, containerId) {
    item.querySelector('.pp-move-up').addEventListener('click', () => {
      const prev = item.previousElementSibling;
      if (prev) item.parentNode.insertBefore(item, prev);
    });
    item.querySelector('.pp-move-down').addEventListener('click', () => {
      const next = item.nextElementSibling;
      if (next) item.parentNode.insertBefore(next, item);
    });
    item.querySelector('.pp-rep-btn-del').addEventListener('click', () => {
      item.remove();
      if (containerId === 'songs-container') this.updateZtPositions();
    });
  },

  // ============================================================
  // Positionsoptionen dynamisch aus Songliste
  // ============================================================
  getSongTitles() {
    return [...document.querySelectorAll('#songs-container [data-field="title"]')]
      .map(i => i.value.trim()).filter(Boolean);
  },

  buildPositions(songs) {
    const opts = ['frei (keine feste Zuordnung)'];
    if (!songs.length) {
      opts.push('vor Song 1', 'nach dem letzten Song');
      return opts;
    }
    opts.push('vor Song 1' + (songs[0] ? ' – ' + songs[0] : ''));
    for (let i = 0; i < songs.length - 1; i++) {
      opts.push(`zwischen Song ${i+1} und Song ${i+2} – ${songs[i]} / ${songs[i+1]}`);
    }
    opts.push('nach dem letzten Song' + (songs[songs.length-1] ? ' – ' + songs[songs.length-1] : ''));
    return opts;
  },

  updateZtPositions() {
    const songs = this.getSongTitles();
    const positions = this.buildPositions(songs);
    document.querySelectorAll('#zt-container [data-field="position"]').forEach(sel => {
      const current = sel.value;
      sel.innerHTML = positions.map(p => `<option value="${this.esc(p)}" ${p === current ? 'selected' : ''}>${this.esc(p)}</option>`).join('');
    });
  },

  // ============================================================
  // Nav Highlight beim Scrollen
  // ============================================================
  bindNavHighlight() {
    const main = document.querySelector('.pp-project-main');
    if (!main) return;
    main.addEventListener('scroll', () => {
      const links = document.querySelectorAll('.pp-nav-link');
      const sections = document.querySelectorAll('.pp-block');
      let current = '';
      sections.forEach(s => {
        if (s.getBoundingClientRect().top < 100) current = '#' + s.id;
      });
      links.forEach(l => l.classList.toggle('active', l.getAttribute('href') === current));
    });
    // Smooth scroll auf Nav-Klick
    document.querySelectorAll('.pp-nav-link').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const target = document.querySelector(link.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  },

  // ============================================================
  // CSRF-Token erneuern (falls Session abgelaufen war)
  // ============================================================
  async refreshCsrf() {
    const r = await this.ajax('pp_check_auth');
    if (!r.success || !r.data.authenticated) {
      this.showView('login');
      throw new Error('Nicht eingeloggt.');
    }
    this.csrf = r.data.token;
  },

  // ============================================================
  // HTML escapen
  // ============================================================
  esc(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  },

  // ============================================================
  // HILFE-OVERLAY
  // ============================================================
  bindHelp() {
    const overlay  = document.getElementById('pp-help-overlay');
    const closeBtn = document.getElementById('pp-help-close');
    const navLinks = document.querySelectorAll('.pp-hn');
    const sections = document.querySelectorAll('.pp-hs');

    const openHelp = () => {
      overlay.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    };
    const closeHelp = () => {
      overlay.style.display = 'none';
      document.body.style.overflow = '';
    };

    // Help-Buttons in beiden Headern
    ['pp-help-btn', 'pp-help-btn-2'].forEach(function(id) {
      const btn = document.getElementById(id);
      if (btn) btn.addEventListener('click', openHelp);
    });

    if (closeBtn) closeBtn.addEventListener('click', closeHelp);
    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) closeHelp();
    });
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && overlay.style.display !== 'none') closeHelp();
    });

    // Hilfssection aktivieren
    const showSection = function(targetId) {
      sections.forEach(function(s) { s.classList.remove('active'); });
      navLinks.forEach(function(l) { l.classList.remove('active'); });
      const target = document.getElementById(targetId);
      if (target) target.classList.add('active');
      const activeLink = document.querySelector('.pp-hn[href="#' + targetId + '"]');
      if (activeLink) activeLink.classList.add('active');
      const content = document.getElementById('pp-help-content');
      if (content) content.scrollTop = 0;
    };

    // Erste Sektion initial aktiv
    showSection('h-start');

    navLinks.forEach(function(link) {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        showSection(link.getAttribute('href').replace('#', ''));
      });
    });
  },
};

document.addEventListener('DOMContentLoaded', function() {
  PP.init();
  PP.bindHelp();
});
