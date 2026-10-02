<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Admin {

	public function register_hooks(): void {
		add_action( 'admin_menu',            [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function register_menu(): void {
		add_menu_page(
			'Programm-Pusher',
			'Programm-Pusher',
			'manage_options',
			'programm-pusher',
			[ $this, 'render_settings_page' ],
			'dashicons-media-text',
			30
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( ! str_contains( $hook, 'programm-pusher' ) ) return;
		// WP-Media-Library für Logo-Upload
		wp_enqueue_media();
		wp_enqueue_style( 'pp-admin', PP_URL . 'assets/css/admin.css', [], PP_VERSION );
		wp_enqueue_script( 'pp-admin', PP_URL . 'assets/js/admin.js', [], PP_VERSION, true );
		wp_localize_script( 'pp-admin', 'PP_ADMIN', [
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'pp_admin_nonce' ),
			'siteUrl'      => home_url(),
			'frontendUrl'  => home_url( '/' . PP_Settings::get_frontend_slug() . '/' ),
			'logoUrl'      => PP_Settings::get_logo_url(),
			'confirmReset' => 'Master-Prompt wirklich auf Standard zurücksetzen? Alle Änderungen gehen verloren.',
			'errSlug'      => 'Der Slug darf nicht leer sein.',
			'mediaTitle'   => 'Logo auswählen',
			'mediaButton'  => 'Dieses Bild verwenden',
		] );
	}

	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$slug         = PP_Settings::get_frontend_slug();
		$username     = PP_Settings::get_frontend_username();
		$prompt       = PP_Settings::get_master_prompt();
		$logo_url     = PP_Settings::get_logo_url();
		$fe_url       = home_url( '/' . $slug . '/' );
		$placeholders = PP_Placeholder_Resolver::get_all_placeholder_keys();
		?>
		<div class="wrap pp-admin-wrap">
			<h1>⚙ Programm-Pusher – Einstellungen</h1>

			<!-- Frontend-URL Banner -->
			<div class="pp-frontend-url">
				<strong>Frontend-URL:</strong>
				<a id="pp-frontend-url-link" href="<?php echo esc_url( $fe_url ); ?>" target="_blank">
					<?php echo esc_html( $fe_url ); ?>
				</a>
			</div>

			<!-- Logo-Upload -->
			<div class="pp-admin-card">
				<h2>Logo</h2>
				<p class="description">Das Logo erscheint in der Anmeldemaske und im App-Header – zusätzlich zu den vorhandenen Icons.</p>
				<div class="pp-logo-uploader">
					<div class="pp-logo-preview" id="pp-logo-preview">
						<?php if ( ! empty( $logo_url ) ) : ?>
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" id="pp-logo-img">
						<?php else : ?>
						<span class="pp-logo-placeholder" id="pp-logo-placeholder">Kein Logo hinterlegt</span>
						<?php endif; ?>
					</div>
					<input type="hidden" id="pp-logo-url" value="<?php echo esc_attr( $logo_url ); ?>">
					<div class="pp-logo-actions">
						<button type="button" id="pp-logo-upload-btn" class="button button-secondary">
							🖼 Logo hochladen / ändern
						</button>
						<button type="button" id="pp-logo-remove-btn" class="button button-link-delete"
							<?php echo empty( $logo_url ) ? 'style="display:none;"' : ''; ?>>
							✕ Logo entfernen
						</button>
					</div>
					<p class="description">Empfohlen: PNG oder SVG mit transparentem Hintergrund, max. 400 × 100 px.</p>
				</div>
			</div>

			<!-- Zugangsdaten -->
			<div class="pp-admin-card">
				<h2>Frontend-Zugangsdaten</h2>
				<table class="form-table">
					<tr>
						<th><label for="pp-frontend-slug">URL-Slug</label></th>
						<td>
							<input type="text" id="pp-frontend-slug"
								value="<?php echo esc_attr( $slug ); ?>"
								placeholder="programm-pusher">
							<p class="description">
								<?php echo esc_html( home_url( '/' ) ); ?><strong><?php echo esc_html( $slug ); ?></strong>/
								— nach Änderung bitte Permalinks in WP einmalig neu speichern.
							</p>
						</td>
					</tr>
					<tr>
						<th><label for="pp-username">Benutzername</label></th>
						<td>
							<input type="text" id="pp-username"
								value="<?php echo esc_attr( $username ); ?>"
								autocomplete="off">
						</td>
					</tr>
					<tr>
						<th><label for="pp-password">Passwort</label></th>
						<td>
							<input type="password" id="pp-password"
								value="" placeholder="Leer lassen = nicht ändern"
								autocomplete="new-password">
							<p class="description">Nur ausfüllen, wenn das Passwort geändert werden soll.</p>
						</td>
					</tr>
				</table>
			</div>

			<!-- Master-Prompt -->
			<div class="pp-admin-card">
				<h2>Globaler Master-Prompt</h2>
				<p class="description">
					Dieser Prompt wird für alle Projekte verwendet.
					Platzhalter in <code>{{GROSSBUCHSTABEN}}</code> werden durch Projektdaten ersetzt.
				</p>
				<div class="pp-prompt-wrap">
					<textarea id="pp-master-prompt" rows="30"><?php echo esc_textarea( $prompt ); ?></textarea>
				</div>

				<!-- Placeholder tags -->
				<div class="pp-placeholder-list" style="margin-top:12px;">
					<?php foreach ( $placeholders as $ph ) : ?>
					<span class="pp-placeholder-tag">{{<?php echo esc_html( $ph ); ?>}}</span>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Action Bar -->
			<div class="pp-admin-actions">
				<button id="pp-btn-save" class="button button-primary button-large">💾 Speichern</button>
				<button id="pp-btn-reset" class="button button-secondary">↩ Standard-Prompt</button>
				<span class="spinner" id="pp-spinner"></span>
				<span id="pp-save-msg" class="pp-save-msg"></span>
			</div>

			<!-- Hinweis -->
			<div class="pp-admin-card" style="margin-top:20px;">
				<h2>So funktioniert es</h2>
				<ol style="margin: 0 0 0 1.2em; line-height: 1.9;">
					<li>Logo (optional), Slug, Benutzername und Passwort festlegen und speichern.</li>
					<li>Frontend unter der angezeigten URL aufrufen.</li>
					<li>Mit Benutzername + Passwort einloggen.</li>
					<li>Projekte anlegen, Showdaten eingeben, Prompt generieren und kopieren.</li>
				</ol>
				<p class="description" style="margin-top:10px;">Das Plugin sendet keine Daten an externe Dienste.</p>
			</div>

			<!-- Plugin aktualisieren -->
			<div class="pp-admin-card" style="margin-top:20px; border-left: 4px solid #2271b1;">
				<h2>🔄 Plugin aktualisieren — Update-Anleitung</h2>
				<p><strong>So installierst du eine neue Version, ohne Daten zu verlieren:</strong></p>
				<ol style="margin: 0 0 0 1.2em; line-height: 2;">
					<li>WordPress-Admin → <strong>Plugins → Plugin installieren → Plugin hochladen</strong></li>
					<li>Das neue ZIP auswählen → <strong>„Aktuell installierte Version ersetzen"</strong> klicken</li>
					<li>Fertig — alle Projekte und Einstellungen bleiben erhalten ✓</li>
				</ol>
				<p class="description" style="margin-top:10px; color:#d63638; font-weight:600;">
					⚠ NIEMALS das Plugin erst löschen und dann neu installieren!
					Beim Löschen werden je nach Einstellung (s. unten) alle Daten entfernt.
				</p>
			</div>

			<!-- Datenschutz & Deinstallation -->
			<div class="pp-admin-card" style="margin-top:20px; border-left: 4px solid #d63638;">
				<h2>🗑 Datenschutz &amp; Deinstallation</h2>
				<p class="description">
					Standardmäßig bleiben beim Löschen des Plugins alle Projekte und Einstellungen erhalten
					(Schutz vor versehentlichem Datenverlust). Aktiviere die Option unten nur, wenn du das
					Plugin wirklich komplett entfernen und <strong>alle Daten unwiderruflich löschen</strong> möchtest.
				</p>
				<label style="display:flex; align-items:center; gap:10px; margin-top:14px; cursor:pointer;">
					<input type="checkbox" id="pp-uninstall-cleanup"
						<?php checked( get_option( 'pp_uninstall_cleanup', 'no' ), 'yes' ); ?>>
					<span style="color:#d63638; font-weight:600;">
						Alle Daten beim Deinstallieren unwiderruflich löschen
					</span>
				</label>
				<p class="description" style="margin-top:6px;">
					Diese Einstellung wird zusammen mit dem Speichern-Button übernommen.
				</p>
			</div>

			<!-- Datenbank-Diagnose & Reparatur -->
			<div class="pp-admin-card pp-diag-card" style="margin-top:20px;">
				<h2>🔧 Datenbank-Diagnose &amp; Projektreparatur</h2>
				<p class="description">
					Zeigt alle Projekte direkt aus der Datenbank – unabhängig vom gespeicherten Status.
					Mit <strong>Projekte reparieren</strong> werden alle Projekte mit falschem Status
					(z. B. <code>draft</code>, <code>pending</code>) automatisch auf
					<code>publish</code> gesetzt und damit im Frontend wieder sichtbar.
				</p>

				<?php
				global $wpdb;
				$all_rows = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT ID, post_title, post_status, post_modified
						   FROM {$wpdb->posts}
						  WHERE post_type = %s
						    AND post_status != 'auto-draft'
						  ORDER BY post_modified DESC",
						PP_CPT
					)
				);
				$bad_count = 0;
				?>

				<?php if ( empty( $all_rows ) ) : ?>
				<p style="color:#c00;font-weight:600;">
					⚠ Keine Projekte in der Datenbank gefunden. Möglicherweise wurden sie beim
					Löschen einer früheren Plugin-Version mitgelöscht.
				</p>
				<?php else : ?>
				<table class="widefat striped pp-diag-table" style="margin-top:12px;">
					<thead>
						<tr>
							<th>ID</th>
							<th>Titel</th>
							<th>Status</th>
							<th>Zuletzt geändert</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $all_rows as $row ) :
						$bad = ( $row->post_status !== 'publish' && $row->post_status !== 'trash' );
						if ( $bad ) $bad_count++;
					?>
					<tr <?php echo $bad ? 'style="background:#fff3cd;"' : ''; ?>>
						<td><?php echo (int) $row->ID; ?></td>
						<td><strong><?php echo esc_html( $row->post_title ?: '(ohne Titel)' ); ?></strong></td>
						<td>
							<?php if ( $bad ) : ?>
							<span style="color:#856404;font-weight:600;">
								⚠ <?php echo esc_html( $row->post_status ); ?>
							</span>
							<?php else : ?>
							<span style="color:#0a7c00;">✓ <?php echo esc_html( $row->post_status ); ?></span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( wp_date( 'd.m.Y H:i', strtotime( $row->post_modified ) ) ); ?></td>
					</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p style="margin-top:8px;color:#666;">
					<?php echo count( $all_rows ); ?> Projekte in der Datenbank.
					<?php if ( $bad_count ) : ?>
					<strong style="color:#856404;"><?php echo $bad_count; ?> davon mit falschem Status.</strong>
					<?php endif; ?>
				</p>
				<?php endif; ?>

				<div style="margin-top:12px;">
					<button id="pp-btn-repair" class="button button-secondary">
						🔧 Projekte reparieren (Status → publish)
					</button>
					<span id="pp-repair-msg" class="pp-save-msg" style="margin-left:10px;"></span>
				</div>
			</div>
		</div>
		<?php
	}
}
