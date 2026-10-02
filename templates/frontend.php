<?php
/**
 * Programm-Pusher Frontend – vollständige HTML-App
 * Wird direkt als Template ausgeliefert (kein Theme).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$fe_slug   = PP_Settings::get_frontend_slug();
$ajax_url  = admin_url( 'admin-ajax.php' );
$asset_url = PP_URL . 'assets/';
$logo_url  = PP_Settings::get_logo_url();
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Programm-Pusher</title>
<link rel="stylesheet" href="<?php echo esc_url( $asset_url . 'css/frontend.css?v=' . PP_VERSION ); ?>">
</head>
<body class="pp-app">

<!-- Initialer Ladescreen – verschwindet sobald checkAuth() fertig ist -->
<div id="pp-app-loader" class="pp-app-loader">
  <div class="pp-app-loader-inner">
    <div class="pp-app-loader-spinner"></div>
  </div>
</div>

<!-- ============================================================
     LOGIN-VIEW
     ============================================================ -->
<div id="pp-view-login" class="pp-view">
  <div class="pp-login-box">
    <?php if ( ! empty( $logo_url ) ) : ?>
    <div class="pp-login-logo-img">
      <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="pp-logo-img pp-logo-login">
    </div>
    <?php endif; ?>
    <div class="pp-login-logo">🎭</div>
    <h1 class="pp-login-title">Programm-Pusher</h1>
    <p class="pp-login-sub">Bühnenkonzept-Generator</p>
    <form id="pp-login-form" class="pp-login-form" autocomplete="off">
      <div class="pp-form-group">
        <label for="pp-username">Benutzername</label>
        <input type="text" id="pp-username" name="username" autocomplete="username" required>
      </div>
      <div class="pp-form-group">
        <label for="pp-password">Passwort</label>
        <input type="password" id="pp-password" name="password" autocomplete="current-password" required>
      </div>
      <div id="pp-login-error" class="pp-error" style="display:none;"></div>
      <button type="submit" class="pp-btn pp-btn-primary pp-btn-full" id="pp-login-btn">
        Einloggen
      </button>
    </form>
  </div>
</div>

<!-- ============================================================
     DASHBOARD-VIEW
     ============================================================ -->
<div id="pp-view-dashboard" class="pp-view">
  <header class="pp-header">
    <div class="pp-header-inner">
      <div class="pp-header-brand">
        <?php if ( ! empty( $logo_url ) ) : ?>
        <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="pp-logo-img pp-logo-header">
        <?php endif; ?>
        <div class="pp-header-title">🎭 Programm-Pusher</div>
      </div>
      <div class="pp-header-actions">
        <button class="pp-btn pp-btn-primary" id="pp-new-project-btn">+ Neues Projekt</button>
        <button class="pp-btn pp-btn-ghost pp-help-btn" id="pp-help-btn" title="Hilfe öffnen" aria-label="Hilfe">?</button>
        <button class="pp-btn pp-btn-ghost" id="pp-logout-btn">Abmelden</button>
      </div>
    </div>
  </header>
  <main class="pp-main">
    <div class="pp-container">
      <h2 class="pp-section-title">Meine Projekte</h2>
      <div id="pp-projects-list" class="pp-projects-grid">
        <div class="pp-loading">Projekte werden geladen…</div>
      </div>
    </div>
  </main>
</div>

<!-- ============================================================
     PROJEKT-VIEW
     ============================================================ -->
<div id="pp-view-project" class="pp-view">
  <header class="pp-header">
    <div class="pp-header-inner">
      <div class="pp-header-left">
        <button class="pp-btn pp-btn-ghost pp-back-btn" id="pp-back-btn">← Zurück</button>
        <div class="pp-header-brand">
          <?php if ( ! empty( $logo_url ) ) : ?>
          <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="pp-logo-img pp-logo-header">
          <?php endif; ?>
          <div class="pp-header-title" id="pp-project-title-display">Projekt</div>
        </div>
      </div>
      <div class="pp-header-actions">
        <button class="pp-btn pp-btn-primary" id="pp-save-btn">💾 Speichern</button>
        <button class="pp-btn pp-btn-ghost pp-help-btn" id="pp-help-btn-2" title="Hilfe öffnen" aria-label="Hilfe">?</button>
        <button class="pp-btn pp-btn-ghost" id="pp-logout-btn-2">Abmelden</button>
      </div>
    </div>
  </header>

  <div class="pp-project-layout">
    <!-- Sidebar Navigation -->
    <nav class="pp-sidenav">
      <ul>
        <li><a href="#block-a" class="pp-nav-link active">A – Grunddaten</a></li>
        <li><a href="#block-b" class="pp-nav-link">B – Künstlerprofil</a></li>
        <li><a href="#block-c" class="pp-nav-link">C – Zielpublikum</a></li>
        <li><a href="#block-d" class="pp-nav-link">D – Humor &amp; Dramaturgie</a></li>
        <li><a href="#block-e" class="pp-nav-link">E – Zwischenteile</a></li>
        <li><a href="#block-f" class="pp-nav-link">F – Vorh. Material</a></li>
        <li><a href="#block-g" class="pp-nav-link">G – Songs</a></li>
        <li><a href="#block-h" class="pp-nav-link">H – Positionierung</a></li>
        <li><a href="#block-prompt" class="pp-nav-link pp-nav-prompt">🎯 Prompt</a></li>
      </ul>
    </nav>

    <!-- Hauptinhalt -->
    <main class="pp-project-main">
      <div id="pp-save-status" class="pp-save-status" style="display:none;"></div>
      <form id="pp-project-form">
        <input type="hidden" id="pp-project-id" name="id" value="">

        <!-- ====================================================
             BLOCK A – Grunddaten
             ==================================================== -->
        <section class="pp-block" id="block-a">
          <h2 class="pp-block-title">A – Grunddaten</h2>
          <div class="pp-fields">
            <div class="pp-field">
              <label>Künstlername <span class="req">*</span></label>
              <input type="text" name="kuenstlername" id="f-kuenstlername" placeholder="Name des Künstlers oder der Band">
            </div>
            <div class="pp-field">
              <label>Programmname / Tourname <span class="req">*</span></label>
              <input type="text" name="programmname" id="f-programmname" placeholder="Titel des Programms oder der Tour">
            </div>
            <div class="pp-field pp-field-half">
              <label>Geplante Gesamtdauer <span class="req">*</span></label>
              <input type="text" name="showdauer" id="f-showdauer" placeholder="z. B. 120 Minuten">
            </div>
            <div class="pp-field pp-field-half">
              <label>Sprache der Show <span class="req">*</span></label>
              <select name="sprache_der_show" id="f-sprache">
                <option value="Deutsch">Deutsch</option>
                <option value="Österreichisches Deutsch">Österreichisches Deutsch</option>
                <option value="Schweizerdeutsch">Schweizerdeutsch</option>
                <option value="Bayerisch">Bayerisch</option>
                <option value="Englisch">Englisch</option>
                <option value="Andere">Andere</option>
              </select>
            </div>
            <div class="pp-field">
              <label>Optionaler Kontext / besondere Rahmung</label>
              <textarea name="programmkontext" id="f-programmkontext" rows="3"
                placeholder="z. B. Jubiläumstournee, Open-Air-Special, Weihnachtsprogramm"></textarea>
            </div>
          </div>
        </section>

        <!-- ====================================================
             BLOCK B – Künstlerprofil
             ==================================================== -->
        <section class="pp-block" id="block-b">
          <h2 class="pp-block-title">B – Künstlerprofil</h2>
          <div class="pp-fields">
            <div class="pp-field">
              <label>Bühnenpersönlichkeit <span class="req">*</span></label>
              <p class="pp-hint">Mehrfachauswahl möglich</p>
              <div class="pp-checkgroup" data-name="kuenstlerpersoenlichkeit">
                <?php
                $pers_opts = ['locker','nahbar','ironisch','poetisch','tiefgründig','wild','energetisch','melancholisch','trocken','provokant','feinfühlig','charismatisch','direkt','zurückhaltend','selbstironisch'];
                foreach ( $pers_opts as $o ) : ?>
                <label class="pp-check"><input type="checkbox" value="<?php echo esc_attr($o); ?>"> <?php echo esc_html($o); ?></label>
                <?php endforeach; ?>
              </div>
              <input type="text" name="kuenstlerpersoenlichkeit_custom" class="pp-custom-field"
                placeholder="Weitere Eigenschaften (Freitext)">
            </div>
            <div class="pp-field pp-field-half">
              <label>Sprachstil auf der Bühne <span class="req">*</span></label>
              <select name="sprachstil">
                <?php foreach (['direkt','erzählerisch','philosophisch','schnörkellos','volkstümlich','elegant','rau','alltagsnah','bildhaft','literarisch'] as $o) : ?>
                <option value="<?php echo esc_attr($o); ?>"><?php echo esc_html($o); ?></option>
                <?php endforeach; ?>
              </select>
              <input type="text" name="sprachstil_custom" class="pp-custom-field" placeholder="Ergänzung (optional)">
            </div>
            <div class="pp-field pp-field-half">
              <label>Energie / Bühnenwirkung <span class="req">*</span></label>
              <select name="energielevel">
                <?php foreach (['ruhig','ausgewogen','hochenergetisch','wechselhaft','intensiv'] as $o) : ?>
                <option value="<?php echo esc_attr($o); ?>"><?php echo esc_html($o); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="pp-field pp-field-half">
              <label>Nähe zum Publikum <span class="req">*</span></label>
              <select name="publikumsnaehe">
                <?php foreach (['distanziert-inszeniert','ausgewogen','sehr nahbar','dialogisch'] as $o) : ?>
                <option value="<?php echo esc_attr($o); ?>"><?php echo esc_html($o); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="pp-field">
              <label>Wichtige Charakterzüge oder Besonderheiten</label>
              <textarea name="charakterzuege" rows="3"
                placeholder="Was macht diesen Künstler unverwechselbar?"></textarea>
            </div>
            <div class="pp-field">
              <label>Was der Künstler auf keinen Fall verkörpern soll</label>
              <textarea name="no_go_kuenstlerwirkung" rows="3"
                placeholder="Klare Ausschlüsse: welche Wirkung soll auf keinen Fall entstehen?"></textarea>
            </div>
            <div class="pp-field">
              <label>Referenzen / Einflüsse / Vergleichsgrößen</label>
              <textarea name="referenzen" rows="3"
                placeholder="Andere Künstler, Stile oder Vorbilder"></textarea>
            </div>
          </div>
        </section>

        <!-- ====================================================
             BLOCK C – Zielpublikum
             ==================================================== -->
        <section class="pp-block" id="block-c">
          <h2 class="pp-block-title">C – Zielpublikum</h2>
          <div class="pp-fields">
            <div class="pp-field">
              <label>Zielpublikum</label>
              <textarea name="zielpublikum" rows="3"
                placeholder="Wer kommt typischerweise zu diesen Shows?"></textarea>
            </div>
            <div class="pp-field pp-field-half">
              <label>Altersstruktur</label>
              <input type="text" name="altersstruktur" placeholder="z. B. 25–55 Jahre, Schwerpunkt 35–45">
            </div>
            <div class="pp-field pp-field-half">
              <label>Interaktionsgrad</label>
              <select name="interaktionsgrad">
                <?php foreach (['kaum Interaktion','gezielte einzelne Interaktionen','regelmäßig interaktiv','stark publikumsbezogen'] as $o) : ?>
                <option value="<?php echo esc_attr($o); ?>"><?php echo esc_html($o); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="pp-field pp-field-half">
              <label>Tonlage gegenüber dem Publikum</label>
              <select name="tonlage_ggue_publikum">
                <?php foreach (['locker','respektvoll','kumpelhaft','elegant','provokationsarm','charmant-direkt','warm','humorvoll-nahbar'] as $o) : ?>
                <option value="<?php echo esc_attr($o); ?>"><?php echo esc_html($o); ?></option>
                <?php endforeach; ?>
              </select>
              <input type="text" name="tonlage_custom" class="pp-custom-field" placeholder="Ergänzung (optional)">
            </div>
            <div class="pp-field">
              <label>Besondere Erwartungen / Empfindlichkeiten / kulturelle Kontexte</label>
              <textarea name="publikumsbesonderheiten" rows="3"
                placeholder="z. B. regionale Eigenheiten, kulturelle Sensibilitäten"></textarea>
            </div>
          </div>
        </section>

        <!-- ====================================================
             BLOCK D – Humor, Tiefe & Dramaturgie
             ==================================================== -->
        <section class="pp-block" id="block-d">
          <h2 class="pp-block-title">D – Humor, Tiefe &amp; Dramaturgie</h2>
          <div class="pp-fields">
            <div class="pp-field">
              <label>Bevorzugter Humortyp <span class="req">*</span></label>
              <p class="pp-hint">Mehrfachauswahl möglich</p>
              <div class="pp-checkgroup" data-name="humortyp">
                <?php foreach (['trocken','selbstironisch','absurd','erzählerisch','schwarz','fein','publikumsnah','situativ','beobachtend','hintergründig'] as $o) : ?>
                <label class="pp-check"><input type="checkbox" value="<?php echo esc_attr($o); ?>"> <?php echo esc_html($o); ?></label>
                <?php endforeach; ?>
              </div>
              <input type="text" name="humortyp_custom" class="pp-custom-field" placeholder="Weiterer Humortyp (Freitext)">
            </div>
            <div class="pp-field pp-field-half">
              <label>Gewichtung Humor / Tiefgang <span class="req">*</span></label>
              <select name="gewichtung_humor_tiefe">
                <?php foreach (['70% Humor / 30% Tiefgang','60% Humor / 40% Tiefgang','50% Humor / 50% Tiefgang','40% Humor / 60% Tiefgang','emotional mit humoristischer Entlastung','humorvoll mit einzelnen tiefen Spitzen'] as $o) : ?>
                <option value="<?php echo esc_attr($o); ?>"><?php echo esc_html($o); ?></option>
                <?php endforeach; ?>
              </select>
              <input type="text" name="gewichtung_custom" class="pp-custom-field" placeholder="Ergänzung (optional)">
            </div>
            <div class="pp-field">
              <label>Gewünschter roter Faden <span class="req">*</span></label>
              <textarea name="roter_faden" rows="4"
                placeholder="Was ist das übergeordnete Thema oder die innere Linie des Abends?"></textarea>
            </div>
            <div class="pp-field">
              <label>Zentrale Themen</label>
              <textarea name="zentrale_themen" rows="3"
                placeholder="Welche Themen sollen sich durch den Abend ziehen?"></textarea>
            </div>
            <div class="pp-field">
              <label>Gewünschte emotionale Entwicklung der Show</label>
              <textarea name="emotionale_kurve" rows="3"
                placeholder="z. B. leicht → tief → triumphierend"></textarea>
            </div>
            <div class="pp-field">
              <label>Gesamte Wirkung der Show</label>
              <textarea name="gesamtwirkung" rows="3"
                placeholder="Welches Gefühl soll das Publikum beim Verlassen haben?"></textarea>
            </div>
            <div class="pp-field">
              <label>Was vermieden werden soll</label>
              <textarea name="no_go_inhalte" rows="3"
                placeholder="Themen, Witze oder Strukturen, die keinen Platz haben sollen"></textarea>
            </div>
          </div>
        </section>

        <!-- ====================================================
             BLOCK E – Form der Zwischenteile
             ==================================================== -->
        <section class="pp-block" id="block-e">
          <h2 class="pp-block-title">E – Form der Zwischenteile</h2>
          <div class="pp-fields">
            <div class="pp-field">
              <label>Erlaubte / gewünschte Formen <span class="req">*</span></label>
              <p class="pp-hint">Mehrfachauswahl möglich</p>
              <div class="pp-checkgroup" data-name="form_der_zwischenteile">
                <?php foreach (['Monolog','Mini-Szene','imaginärer Dialog','Publikumsansprache','nachdenklicher Übergang','Running Gag','erzählter Rückblick','verbindender Kommentar','humoristische Entlastung'] as $o) : ?>
                <label class="pp-check"><input type="checkbox" value="<?php echo esc_attr($o); ?>"> <?php echo esc_html($o); ?></label>
                <?php endforeach; ?>
              </div>
              <input type="text" name="form_der_zwischenteile_custom" class="pp-custom-field" placeholder="Weitere Form (Freitext)">
            </div>
            <div class="pp-field">
              <label>Bevorzugte Art der Moderationen</label>
              <div class="pp-checkgroup" data-name="moderationsart">
                <?php foreach (['direkt','erzählend','reflektierend','pointiert','poetisch','verdichtend'] as $o) : ?>
                <label class="pp-check"><input type="checkbox" value="<?php echo esc_attr($o); ?>"> <?php echo esc_html($o); ?></label>
                <?php endforeach; ?>
              </div>
              <input type="text" name="moderationsart_custom" class="pp-custom-field" placeholder="Weitere Art (Freitext)">
            </div>
            <div class="pp-field">
              <label>Bevorzugte Art der Sketche</label>
              <div class="pp-checkgroup" data-name="sketchart">
                <?php foreach (['kurzer Monolog-Sketch','gespielte Szene','Figurenmoment','Meta-Kommentar','absurde Zuspitzung','dialogische Improvisationsanmutung'] as $o) : ?>
                <label class="pp-check"><input type="checkbox" value="<?php echo esc_attr($o); ?>"> <?php echo esc_html($o); ?></label>
                <?php endforeach; ?>
              </div>
              <input type="text" name="sketchart_custom" class="pp-custom-field" placeholder="Weitere Art (Freitext)">
            </div>
            <div class="pp-field pp-field-row">
              <label>Running Gags einbauen? <span class="req">*</span></label>
              <div class="pp-radio-group">
                <label class="pp-radio"><input type="radio" name="running_gags" value="Ja"> Ja</label>
                <label class="pp-radio"><input type="radio" name="running_gags" value="Nein" checked> Nein</label>
              </div>
            </div>
            <div class="pp-field">
              <label>Wenn ja: gewünschte Form oder Idee</label>
              <textarea name="running_gag_details" rows="3"
                placeholder="Beschreibe den geplanten Running Gag oder die Idee"></textarea>
            </div>
            <div class="pp-field pp-field-row">
              <label>Publikumsinteraktion? <span class="req">*</span></label>
              <div class="pp-radio-group">
                <label class="pp-radio"><input type="radio" name="publikumsinteraktion" value="Ja"> Ja</label>
                <label class="pp-radio"><input type="radio" name="publikumsinteraktion" value="Nein" checked> Nein</label>
              </div>
            </div>
            <div class="pp-field">
              <label>Wenn ja: Intensität / Form</label>
              <textarea name="publikumsinteraktion_details" rows="3"
                placeholder="Wie soll die Publikumsinteraktion gestaltet sein?"></textarea>
            </div>
          </div>
        </section>

        <!-- ====================================================
             BLOCK F – Vorhandenes Material
             ==================================================== -->
        <section class="pp-block" id="block-f">
          <h2 class="pp-block-title">F – Vorhandenes Material</h2>
          <div class="pp-fields">
            <div class="pp-field pp-field-row">
              <label>Vorhandenes Material vorhanden? <span class="req">*</span></label>
              <div class="pp-radio-group">
                <label class="pp-radio"><input type="radio" name="vorhandenes_material_ja_nein" value="Ja"> Ja</label>
                <label class="pp-radio"><input type="radio" name="vorhandenes_material_ja_nein" value="Nein" checked> Nein</label>
              </div>
            </div>
            <div class="pp-field pp-field-half">
              <label>Umgang mit vorhandenem Material</label>
              <select name="umgang_mit_vorhandenem_material">
                <option value="1:1 übernehmen">1:1 übernehmen</option>
                <option value="stilistisch überarbeiten">stilistisch überarbeiten</option>
                <option value="nur als Inspirationsbasis nutzen" selected>nur als Inspirationsbasis nutzen</option>
              </select>
            </div>
            <div class="pp-field">
              <label>Vorhandene Moderationstexte / Textbausteine</label>
              <textarea name="vorhandene_moderationen" rows="8"
                placeholder="Füge hier vorhandene Texte, Ideen oder Bausteine ein"></textarea>
            </div>
          </div>
        </section>

        <!-- ====================================================
             BLOCK G – Songs
             ==================================================== -->
        <section class="pp-block" id="block-g">
          <h2 class="pp-block-title">G – Songs (Showreihenfolge)</h2>
          <p class="pp-hint">Die Reihenfolge entspricht der Showreihenfolge. Songs per ▲▼ sortieren oder ziehen.</p>
          <div id="songs-container" class="pp-repeatable"></div>
          <button type="button" id="add-song-btn" class="pp-btn pp-btn-secondary">+ Song hinzufügen</button>
        </section>

        <!-- ====================================================
             BLOCK H – Positionierung der Zwischenteile
             ==================================================== -->
        <section class="pp-block" id="block-h">
          <h2 class="pp-block-title">H – Positionierung der Zwischenteile</h2>
          <p class="pp-hint">Speichere das Projekt zuerst, damit die Positionen auf deiner Songliste basieren.</p>
          <div id="zt-container" class="pp-repeatable"></div>
          <button type="button" id="add-zt-btn" class="pp-btn pp-btn-secondary">+ Zwischenteil hinzufügen</button>
        </section>

        <!-- ====================================================
             PROMPT-AUSGABE
             ==================================================== -->
        <section class="pp-block pp-block-prompt" id="block-prompt">
          <h2 class="pp-block-title">🎯 Generierter Prompt</h2>
          <div class="pp-prompt-actions">
            <button type="button" id="pp-regenerate-btn" class="pp-btn pp-btn-primary">🔄 Prompt neu generieren</button>
            <button type="button" id="pp-copy-btn" class="pp-btn pp-btn-secondary">📋 In Zwischenablage</button>
            <button type="button" id="pp-download-btn" class="pp-btn pp-btn-ghost">⬇ Als TXT</button>
            <span id="pp-prompt-msg" class="pp-prompt-msg"></span>
          </div>
          <textarea id="pp-prompt-output" class="pp-prompt-textarea" readonly
            placeholder="Speichere das Projekt – der Prompt wird automatisch generiert und erscheint hier."></textarea>
        </section>

      </form><!-- /pp-project-form -->
    </main><!-- /pp-project-main -->
  </div><!-- /pp-project-layout -->
</div><!-- /pp-view-project -->

<!-- ============================================================
     HILFE-OVERLAY
     ============================================================ -->
<div id="pp-help-overlay" class="pp-help-overlay" aria-modal="true" role="dialog" aria-label="Hilfe" style="display:none;">
  <div class="pp-help-panel">
    <div class="pp-help-header">
      <span class="pp-help-header-title">❓ Hilfe &amp; Erste Schritte</span>
      <button class="pp-help-close" id="pp-help-close" aria-label="Schließen">✕</button>
    </div>
    <div class="pp-help-body">
      <nav class="pp-help-nav">
        <ul>
          <li><a href="#h-start"    class="pp-hn active">🚀 Erste Schritte</a></li>
          <li><a href="#h-dash"     class="pp-hn">📁 Dashboard</a></li>
          <li><a href="#h-a"        class="pp-hn">A – Grunddaten</a></li>
          <li><a href="#h-b"        class="pp-hn">B – Künstlerprofil</a></li>
          <li><a href="#h-c"        class="pp-hn">C – Zielpublikum</a></li>
          <li><a href="#h-d"        class="pp-hn">D – Humor &amp; Dramaturgie</a></li>
          <li><a href="#h-e"        class="pp-hn">E – Zwischenteile</a></li>
          <li><a href="#h-f"        class="pp-hn">F – Vorh. Material</a></li>
          <li><a href="#h-g"        class="pp-hn">G – Songs</a></li>
          <li><a href="#h-h"        class="pp-hn">H – Positionierung</a></li>
          <li><a href="#h-prompt"   class="pp-hn">🎯 Prompt</a></li>
          <li><a href="#h-tips"     class="pp-hn">💡 Tipps</a></li>
        </ul>
      </nav>
      <div class="pp-help-content" id="pp-help-content">

        <!-- ERSTE SCHRITTE -->
        <section class="pp-hs" id="h-start">
          <h2>🚀 Erste Schritte</h2>
          <p>Willkommen beim <strong>Programm-Pusher</strong> – deinem persönlichen Bühnenkonzept-Generator. Hier erfährst du, wie du in wenigen Minuten deinen ersten fertigen KI-Prompt erstellst.</p>

          <div class="pp-help-step">
            <div class="pp-help-step-num">1</div>
            <div class="pp-help-step-text">
              <strong>Neues Projekt anlegen</strong><br>
              Klicke im Dashboard auf <em>+ Neues Projekt</em>. Das Projekt wird sofort gespeichert und bekommt nach dem ersten Speichern automatisch einen Titel aus Künstlername und Programmname.
            </div>
          </div>
          <div class="pp-help-step">
            <div class="pp-help-step-num">2</div>
            <div class="pp-help-step-text">
              <strong>Pflichtfelder ausfüllen (Block A &amp; B)</strong><br>
              Starte mit <em>A – Grunddaten</em>: Künstlername, Programmname, Dauer und Sprache sind die wichtigsten Felder. In <em>B – Künstlerprofil</em> definierst du, wie der Künstler auf der Bühne wirkt.
            </div>
          </div>
          <div class="pp-help-step">
            <div class="pp-help-step-num">3</div>
            <div class="pp-help-step-text">
              <strong>Songs eintragen (Block G)</strong><br>
              Trage die Songs in der geplanten Showreihenfolge ein. Diese Liste bildet die Grundlage für die Positionierung der Zwischenteile in Block H.
            </div>
          </div>
          <div class="pp-help-step">
            <div class="pp-help-step-num">4</div>
            <div class="pp-help-step-text">
              <strong>Speichern &amp; Prompt generieren</strong><br>
              Klicke auf <em>💾 Speichern</em>. Der Prompt wird automatisch generiert. Du findest ihn am Ende der Seite unter <em>🎯 Generierter Prompt</em>.
            </div>
          </div>
          <div class="pp-help-step">
            <div class="pp-help-step-num">5</div>
            <div class="pp-help-step-text">
              <strong>Prompt kopieren und in eine KI einfügen</strong><br>
              Nutze <em>📋 In Zwischenablage</em> und füge den Text in ChatGPT, Claude oder ein anderes KI-System ein. Das war's – dein Showkonzept wird entwickelt.
            </div>
          </div>

          <div class="pp-help-tip">
            <strong>💡 Tipp:</strong> Je mehr Felder du ausfüllst, desto individueller und treffsicherer wird der generierte Prompt. Pflichtfelder sind mit <span class="req">*</span> markiert.
          </div>
        </section>

        <!-- DASHBOARD -->
        <section class="pp-hs" id="h-dash">
          <h2>📁 Dashboard</h2>
          <p>Das Dashboard ist deine Projektzentrale. Hier siehst du alle gespeicherten Projekte auf einen Blick.</p>
          <ul class="pp-help-list">
            <li><strong>+ Neues Projekt</strong> — Legt ein leeres Projekt an und öffnet es sofort zur Bearbeitung.</li>
            <li><strong>Projekt öffnen</strong> — Klicke auf eine Projektkarte, um das Projekt mit allen gespeicherten Daten zu öffnen.</li>
            <li><strong>Projekt löschen</strong> — Das Mülleimer-Symbol auf der Projektkarte löscht das Projekt unwiderruflich nach einer Bestätigung.</li>
            <li><strong>Zuletzt geändert</strong> — Jede Karte zeigt Datum und Uhrzeit der letzten Speicherung.</li>
            <li><strong>Abmelden</strong> — Beendet deine Sitzung. Alle Projekte bleiben gespeichert.</li>
          </ul>
          <div class="pp-help-tip">
            <strong>💡 Tipp:</strong> Projekte werden in der WordPress-Datenbank gesichert und sind beim nächsten Login sofort wieder verfügbar.
          </div>
        </section>

        <!-- BLOCK A -->
        <section class="pp-hs" id="h-a">
          <h2>A – Grunddaten</h2>
          <p>Die Grunddaten liefern den Rahmen für die gesamte Show. Diese Angaben erscheinen prominent im Prompt und sind für jede andere Information der Ausgangspunkt.</p>
          <ul class="pp-help-list">
            <li><strong>Künstlername <span class="req">*</span></strong> — Name des Künstlers, der Band oder des Ensembles. Wird auch als Projekt-Titel verwendet.</li>
            <li><strong>Programmname / Tourname <span class="req">*</span></strong> — Titel des Bühnenprogramms oder der Tour. Gibt dem Abend eine Identität.</li>
            <li><strong>Geplante Gesamtdauer <span class="req">*</span></strong> — Die KI berechnet auf Basis dieser Angabe, wie lang die Moderationen und Zwischenteile sein dürfen. Beispiel: <em>90 Minuten</em>, <em>120 Minuten inkl. Pause</em>.</li>
            <li><strong>Sprache der Show <span class="req">*</span></strong> — Legt fest, in welcher Sprache und Varietät der Prompt ausgegeben wird.</li>
            <li><strong>Optionaler Kontext</strong> — Besondere Rahmenbedingungen: Jubiläum, Open-Air-Setting, Gastspiel im Ausland, saisonales Programm usw.</li>
          </ul>
        </section>

        <!-- BLOCK B -->
        <section class="pp-hs" id="h-b">
          <h2>B – Künstlerprofil</h2>
          <p>Das Künstlerprofil ist das Herzstück des Prompts. Es sorgt dafür, dass der KI-Output nicht generisch klingt, sondern wirklich nach diesem Künstler.</p>
          <ul class="pp-help-list">
            <li><strong>Bühnenpersönlichkeit <span class="req">*</span></strong> — Mehrfachauswahl aus Eigenschaften wie <em>locker, ironisch, tiefgründig</em> usw. Du kannst im Freitextfeld eigene Begriffe ergänzen.</li>
            <li><strong>Sprachstil <span class="req">*</span></strong> — Wie spricht der Künstler auf der Bühne? <em>Direkt, erzählerisch, bildhaft</em> – wähle das Passendste und ergänze optional.</li>
            <li><strong>Energie / Bühnenwirkung <span class="req">*</span></strong> — Von <em>ruhig</em> bis <em>hochenergetisch</em>: die Grundstimmung der Bühnenpräsenz.</li>
            <li><strong>Nähe zum Publikum <span class="req">*</span></strong> — Wie nah geht der Künstler ans Publikum heran? Beeinflusst Schreibstil der Moderationen maßgeblich.</li>
            <li><strong>Charakterzüge</strong> — Freitext für alles, was den Künstler unverwechselbar macht und in keiner Checkbox steht.</li>
            <li><strong>No-Gos</strong> — Was soll auf keinen Fall entstehen? Bestimmte Wirkungen, Klischees oder Assoziationen, die vermieden werden sollen.</li>
            <li><strong>Referenzen</strong> — Andere Künstler, Stile oder Genres, die als Orientierung dienen. Die KI nutzt diese als Stilanker.</li>
          </ul>
        </section>

        <!-- BLOCK C -->
        <section class="pp-hs" id="h-c">
          <h2>C – Zielpublikum</h2>
          <p>Ein guter Prompt muss wissen, für wen die Show gemacht wird. Diese Angaben beeinflussen Tonlage, Referenzen und den Umgang mit Humor.</p>
          <ul class="pp-help-list">
            <li><strong>Zielpublikum</strong> — Freitext: Wer kommt typischerweise zu diesen Shows? Fans, Gelegenheitsbesucher, Familien, Fachpublikum?</li>
            <li><strong>Altersstruktur</strong> — Grobe Angabe zur Altersverteilung, z. B. <em>25–55 Jahre, Schwerpunkt 35–45</em>.</li>
            <li><strong>Interaktionsgrad</strong> — Wie viel direkten Kontakt soll der Künstler zum Publikum suchen? Von <em>kaum Interaktion</em> bis <em>stark publikumsbezogen</em>.</li>
            <li><strong>Tonlage</strong> — Wie redet der Künstler mit dem Publikum? <em>Kumpelhaft, warm, charmant-direkt</em> – beeinflusst direkt die Schreibweise der Moderationen.</li>
            <li><strong>Besonderheiten</strong> — Regionale Besonderheiten, kulturelle Sensibilitäten, spezielle Erwartungen oder Konventionen des Publikums.</li>
          </ul>
        </section>

        <!-- BLOCK D -->
        <section class="pp-hs" id="h-d">
          <h2>D – Humor, Tiefe &amp; Dramaturgie</h2>
          <p>Dieser Block definiert die innere Logik der Show: was sie zusammenhält, wohin sie führt und was sie hinterlassen soll.</p>
          <ul class="pp-help-list">
            <li><strong>Humortyp <span class="req">*</span></strong> — Mehrfachauswahl: welche Art von Humor soll dominieren? <em>Trocken, selbstironisch, absurd, erzählerisch</em> usw.</li>
            <li><strong>Gewichtung Humor / Tiefgang <span class="req">*</span></strong> — Prozentuales Verhältnis zwischen Leichtigkeit und Tiefe. Beeinflusst die emotionale Balance aller Texte.</li>
            <li><strong>Roter Faden <span class="req">*</span></strong> — Das übergeordnete Thema oder die innere Linie des Abends. Ohne roten Faden wirken Moderationen beliebig.</li>
            <li><strong>Zentrale Themen</strong> — Konkrete Themen, die sich durch den Abend ziehen sollen (z. B. Heimat, Verlust, Neuanfang).</li>
            <li><strong>Emotionale Entwicklung</strong> — Wie soll sich die Show emotional entwickeln? Beispiel: <em>leicht und locker → nachdenklich → befreiend</em>.</li>
            <li><strong>Gesamtwirkung</strong> — Mit welchem Gefühl soll das Publikum den Saal verlassen?</li>
            <li><strong>No-Go-Inhalte</strong> — Themen, Witze, Strukturen oder Vergleiche, die explizit nicht vorkommen sollen.</li>
          </ul>
        </section>

        <!-- BLOCK E -->
        <section class="pp-hs" id="h-e">
          <h2>E – Form der Zwischenteile</h2>
          <p>Hier legst du fest, welche Arten von Zwischenteilen zwischen den Songs stehen dürfen – und welche Formen Humor oder Interaktion annehmen sollen.</p>
          <ul class="pp-help-list">
            <li><strong>Erlaubte Formen <span class="req">*</span></strong> — Mehrfachauswahl: Monolog, Mini-Szene, imaginärer Dialog, Running Gag usw. Schränkt oder erweitert die kreativen Möglichkeiten der KI.</li>
            <li><strong>Moderationsart</strong> — Stil der verbindenden Texte zwischen den Songs: direkt, erzählend, poetisch usw.</li>
            <li><strong>Sketchart</strong> — Wenn Sketche vorkommen: welche Form bevorzugt? Von kurzem Monolog-Sketch bis zur gespielten Szene.</li>
            <li><strong>Running Gags</strong> — Ja/Nein. Wenn Ja: beschreibe im Freitextfeld die Idee oder den Wunsch-Gag.</li>
            <li><strong>Publikumsinteraktion</strong> — Ja/Nein. Wenn Ja: in welcher Intensität und Form sollen Zuschauer einbezogen werden?</li>
          </ul>
        </section>

        <!-- BLOCK F -->
        <section class="pp-hs" id="h-f">
          <h2>F – Vorhandenes Material</h2>
          <p>Wenn du bereits Texte, Ideen oder Moderationsbausteine hast, kannst du sie hier einpflegen. Die KI integriert sie entsprechend deiner Vorgabe.</p>
          <ul class="pp-help-list">
            <li><strong>Vorhandenes Material vorhanden?</strong> — Ja/Nein. Aktiviert die relevanten Felder.</li>
            <li><strong>Umgang mit vorhandenem Material</strong> —
              <ul class="pp-help-list" style="margin-top:6px;">
                <li><em>1:1 übernehmen</em>: Die Texte bleiben inhaltlich unverändert, werden nur dramaturgisch eingebettet.</li>
                <li><em>stilistisch überarbeiten</em>: Aussage bleibt, Sprache und Bühnenwirkung werden optimiert.</li>
                <li><em>nur als Inspirationsbasis</em>: Ideen und Stimmungen werden genutzt, aber die Texte werden neu formuliert.</li>
              </ul>
            </li>
            <li><strong>Vorhandene Texte / Bausteine</strong> — Füge hier alle vorhandenen Moderationstexte, Gag-Ideen oder Textbausteine ein, die verarbeitet werden sollen.</li>
          </ul>
        </section>

        <!-- BLOCK G -->
        <section class="pp-hs" id="h-g">
          <h2>G – Songs (Setlist)</h2>
          <p>Die Setlist in der geplanten Showreihenfolge. Jeder Song kann mit Stimmungsangabe und Hinweis versehen werden – das hilft der KI beim Schreiben passender Übergänge.</p>
          <ul class="pp-help-list">
            <li><strong>+ Song hinzufügen</strong> — Legt einen neuen Song-Eintrag am Ende der Liste an.</li>
            <li><strong>Titel</strong> — Name des Songs. Wird für die Positionierung in Block H verwendet.</li>
            <li><strong>Stimmung / Energie</strong> — Kurze Angabe zur Wirkung des Songs, z. B. <em>melancholisch, energetisch, leise und intim</em>.</li>
            <li><strong>Notiz / Hinweis</strong> — Alles, was die KI über diesen Song wissen soll: besonderer Moment, Textzitat, unerwarteter Tonwechsel.</li>
            <li><strong>▲ ▼ Sortieren</strong> — Verschiebe Songs in der Reihenfolge nach oben oder unten.</li>
            <li><strong>✕ Löschen</strong> — Entfernt den Song aus der Liste.</li>
          </ul>
          <div class="pp-help-tip">
            <strong>💡 Tipp:</strong> Speichere das Projekt nach dem Eintragen der Songs, bevor du Block H ausfüllst – die Positionsauswahl in Block H basiert auf dieser Liste.
          </div>
        </section>

        <!-- BLOCK H -->
        <section class="pp-hs" id="h-h">
          <h2>H – Positionierung der Zwischenteile</h2>
          <p>Hier planst du, wo im Ablauf Moderationen, Sketche oder andere Zwischenteile eingefügt werden sollen und was ihre Funktion ist.</p>
          <ul class="pp-help-list">
            <li><strong>+ Zwischenteil hinzufügen</strong> — Legt einen neuen Eintrag an.</li>
            <li><strong>Bezeichnung</strong> — Optionaler Name für diesen Zwischenteil (z. B. <em>Opener-Moderation</em>, <em>Schlussgag</em>).</li>
            <li><strong>Position im Ablauf</strong> — Auswahl aus der aktuellen Songliste: vor welchem Song, nach welchem Song oder am Anfang / Ende der Show.</li>
            <li><strong>Funktion</strong> — Was soll dieser Zwischenteil leisten? Auswahl aus gängigen Funktionen plus Freitext.</li>
            <li><strong>Timing / Dauer</strong> — Optionale Längenangabe, z. B. <em>ca. 3 Minuten</em>.</li>
            <li><strong>Vorhandener Text</strong> — Falls du konkrete Textideen für diesen Abschnitt hast, füge sie hier ein.</li>
            <li><strong>Hinweis an die KI</strong> — Besondere Anweisungen für diesen Abschnitt, z. B. <em>muss auf den vorherigen Song eingehen</em>.</li>
          </ul>
        </section>

        <!-- PROMPT -->
        <section class="pp-hs" id="h-prompt">
          <h2>🎯 Generierter Prompt</h2>
          <p>Der fertige Prompt fasst alle deine Angaben in einem strukturierten Text zusammen, der direkt in eine KI eingefügt werden kann.</p>
          <ul class="pp-help-list">
            <li><strong>Automatische Generierung beim Speichern</strong> — Jedes Mal wenn du auf <em>💾 Speichern</em> klickst, wird der Prompt automatisch neu generiert.</li>
            <li><strong>🔄 Prompt neu generieren</strong> — Regeneriert den Prompt manuell ohne zusätzliches Speichern.</li>
            <li><strong>📋 In Zwischenablage</strong> — Kopiert den gesamten Prompt in die Zwischenablage. Danach direkt in ChatGPT, Claude oder eine andere KI einfügen.</li>
            <li><strong>⬇ Als TXT</strong> — Lädt den Prompt als Textdatei herunter – praktisch zum Archivieren oder für spätere Verwendung.</li>
          </ul>
          <div class="pp-help-tip">
            <strong>💡 So verwendest du den Prompt:</strong> Öffne ChatGPT, Claude oder ein anderes KI-System, erstelle eine neue Konversation, füge den Prompt ein (Strg+V / Cmd+V) und starte die Anfrage. Die KI entwickelt daraufhin ein vollständiges Bühnenkonzept inklusive Moderationstexten, Übergängen und Dramaturgie.
          </div>
        </section>

        <!-- TIPPS -->
        <section class="pp-hs" id="h-tips">
          <h2>💡 Tipps für bessere Ergebnisse</h2>
          <ul class="pp-help-list">
            <li><strong>Mehr Details = besserer Prompt.</strong> Je mehr Felder ausgefüllt sind, desto individueller und treffsicherer wird das KI-Ergebnis. Besonders wichtig: roter Faden, Humortyp und Bühnenpersönlichkeit.</li>
            <li><strong>Freitextfelder nutzen.</strong> Die Checkboxen und Dropdowns decken Standardfälle ab. Was deinen Künstler wirklich ausmacht, steht oft im Freitext – nutze diese Felder großzügig.</li>
            <li><strong>Songs mit Stimmung versehen.</strong> Auch kurze Angaben wie <em>„melancholisch, sehr leise"</em> oder <em>„großer Showmoment, Publikum singt mit"</em> verbessern die Übergänge enorm.</li>
            <li><strong>No-Gos ernst nehmen.</strong> Klare Ausschlüsse helfen der KI genauso wie positive Vorgaben. Was explizit nicht passieren soll, wird im Prompt direkt berücksichtigt.</li>
            <li><strong>Projekt mehrfach bearbeiten.</strong> Speichere ein erstes Ergebnis, probiere den Prompt aus, und verfeinere dann die Angaben für eine zweite Version.</li>
            <li><strong>Verschiedene Projekte für verschiedene Versionen.</strong> Du kannst ein Projekt duplizieren, indem du es öffnest, ein neues Projekt anlegst und die Daten manuell überträgst – so entstehen Varianten für verschiedene Setlists oder Konzepte.</li>
          </ul>
          <div class="pp-help-tip">
            <strong>Hinweis:</strong> Der Programm-Pusher sendet keine Daten an externe Dienste. Alle Projektdaten werden ausschließlich in deiner WordPress-Datenbank gespeichert.
          </div>
        </section>

      </div><!-- /pp-help-content -->
    </div><!-- /pp-help-body -->
  </div><!-- /pp-help-panel -->
</div><!-- /pp-help-overlay -->

<script>
var PP_AJAX     = '<?php echo esc_js( $ajax_url ); ?>';
var PP_SLUG     = '<?php echo esc_js( $fe_slug ); ?>';
var PP_LOGO_URL = '<?php echo esc_js( $logo_url ); ?>';
</script>
<script src="<?php echo esc_url( $asset_url . 'js/frontend.js?v=' . PP_VERSION ); ?>"></script>
</body>
</html>
