<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Alle AJAX-Handler für das Frontend.
 * Alle Frontend-Aktionen laufen über wp_ajax_nopriv_ (keine WP-Anmeldung nötig).
 */
class PP_Ajax {

	public function register_hooks(): void {
		$actions = [
			'pp_login',
			'pp_logout',
			'pp_check_auth',
			'pp_get_projects',
			'pp_new_project',
			'pp_get_project',
			'pp_save_project',
			'pp_delete_project',
			'pp_generate_prompt',
			'pp_rename_project',
			'pp_duplicate_project',
			// Admin-Only
			'pp_save_settings',
			'pp_reset_prompt',
			'pp_repair_projects',
		];
		foreach ( $actions as $action ) {
			add_action( 'wp_ajax_nopriv_' . $action, [ $this, 'handle_' . $action ] );
			add_action( 'wp_ajax_' . $action,        [ $this, 'handle_' . $action ] );
		}
	}

	// -------------------------------------------------------------------------
	// Auth
	// -------------------------------------------------------------------------

	public function handle_pp_login(): void {
		$username = sanitize_text_field( wp_unslash( $_POST['username'] ?? '' ) );
		$password = wp_unslash( $_POST['password'] ?? '' );

		if ( empty( $username ) || empty( $password ) ) {
			wp_send_json_error( [ 'message' => 'Benutzername und Passwort erforderlich.' ] );
		}

		$stored_user = PP_Settings::get_frontend_username();
		if ( $username !== $stored_user ) {
			wp_send_json_error( [ 'message' => 'Ungültige Zugangsdaten.' ] );
		}
		if ( ! PP_Settings::verify_password( $password ) ) {
			wp_send_json_error( [ 'message' => 'Ungültige Zugangsdaten.' ] );
		}

		$token = PP_Session::login();
		wp_send_json_success( [ 'token' => $token ] );
	}

	public function handle_pp_logout(): void {
		PP_Session::logout();
		wp_send_json_success();
	}

	public function handle_pp_check_auth(): void {
		$authenticated = PP_Session::is_authenticated();
		wp_send_json_success( [
			'authenticated' => $authenticated,
			'token'         => $authenticated ? PP_Session::get_token() : '',
		] );
	}

	// -------------------------------------------------------------------------
	// Projekte
	// -------------------------------------------------------------------------

	public function handle_pp_get_projects(): void {
		$this->require_auth();
		global $wpdb;

		/*
		 * Direkter DB-Query statt get_posts():
		 * - Umgeht capability-basierte Filter im nopriv-Kontext
		 * - Findet Projekte aller Status (außer trash / auto-draft)
		 *   → repariert so auch Projekte älterer Versionen, die ggf. als
		 *     'draft' oder 'pending' gespeichert wurden
		 */
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, post_modified, post_status
				   FROM {$wpdb->posts}
				  WHERE post_type = %s
				    AND post_status NOT IN ('trash','auto-draft')
				  ORDER BY post_modified DESC",
				PP_CPT
			)
		);

		$list = [];
		foreach ( $rows as $row ) {
			// Nebenbei: Status auf 'publish' normalisieren, falls abweichend
			if ( $row->post_status !== 'publish' ) {
				$wpdb->update(
					$wpdb->posts,
					[ 'post_status' => 'publish' ],
					[ 'ID' => (int) $row->ID ],
					[ '%s' ],
					[ '%d' ]
				);
			}
			$list[] = [
				'id'       => (int) $row->ID,
				'title'    => $row->post_title ?: 'Unbenanntes Projekt',
				'modified' => wp_date( 'd.m.Y H:i', strtotime( $row->post_modified ) ),
			];
		}

		wp_send_json_success( [ 'projects' => $list ] );
	}

	public function handle_pp_new_project(): void {
		$this->require_auth();
		$this->verify_csrf();
		$post_id = wp_insert_post( [
			'post_type'   => PP_CPT,
			'post_status' => 'publish',
			'post_title'  => 'Neues Projekt',
		] );
		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( [ 'message' => 'Projekt konnte nicht erstellt werden.' ] );
		}
		wp_send_json_success( [ 'id' => $post_id, 'title' => 'Neues Projekt' ] );
	}

	public function handle_pp_get_project(): void {
		$this->require_auth();
		$post_id = absint( $_POST['id'] ?? 0 );
		if ( ! $post_id || get_post_type( $post_id ) !== PP_CPT ) {
			wp_send_json_error( [ 'message' => 'Projekt nicht gefunden.' ] );
		}
		$data = $this->read_project_data( $post_id );
		$data['generated_prompt'] = (string) get_post_meta( $post_id, '_pp_generated_prompt', true );
		wp_send_json_success( [ 'project' => $data ] );
	}

	public function handle_pp_save_project(): void {
		$this->require_auth();
		$this->verify_csrf();

		$post_id = absint( $_POST['id'] ?? 0 );
		if ( ! $post_id || get_post_type( $post_id ) !== PP_CPT ) {
			wp_send_json_error( [ 'message' => 'Ungültige Projekt-ID.' ] );
		}

		// Rohdaten aus POST holen (JSON-encoded im Feld "data")
		$raw = wp_unslash( $_POST['data'] ?? '' );
		$d   = json_decode( $raw, true );
		if ( ! is_array( $d ) ) {
			wp_send_json_error( [ 'message' => 'Ungültige Daten.' ] );
		}

		$this->write_project_data( $post_id, $d );

		// Titel aktualisieren
		$kuenstler = sanitize_text_field( $d['kuenstlername'] ?? '' );
		$programm  = sanitize_text_field( $d['programmname'] ?? '' );
		$title     = ! empty( $kuenstler )
			? $kuenstler . ( ! empty( $programm ) ? ' – ' . $programm : ' – Showprojekt' )
			: 'Unbenanntes Projekt';
		wp_update_post( [ 'ID' => $post_id, 'post_title' => $title ] );

		// Prompt neu generieren – direkt aus den empfangenen Rohdaten $d,
		// nicht aus der DB. Vermeidet Cache-Race-Conditions durch wp_update_post().
		$builder = new PP_Prompt_Builder();
		$prompt  = $builder->build( $post_id, $d );
		update_post_meta( $post_id, '_pp_generated_prompt', $prompt );

		wp_send_json_success( [
			'title'  => $title,
			'prompt' => $prompt,
		] );
	}

	public function handle_pp_delete_project(): void {
		$this->require_auth();
		$this->verify_csrf();
		$post_id = absint( $_POST['id'] ?? 0 );
		if ( ! $post_id || get_post_type( $post_id ) !== PP_CPT ) {
			wp_send_json_error( [ 'message' => 'Projekt nicht gefunden.' ] );
		}
		wp_delete_post( $post_id, true );
		wp_send_json_success();
	}

	public function handle_pp_generate_prompt(): void {
		$this->require_auth();
		$this->verify_csrf();
		$post_id = absint( $_POST['id'] ?? 0 );
		if ( ! $post_id || get_post_type( $post_id ) !== PP_CPT ) {
			wp_send_json_error( [ 'message' => 'Ungültige Projekt-ID.' ] );
		}
		$builder = new PP_Prompt_Builder();
		$prompt  = $builder->build( $post_id );
		update_post_meta( $post_id, '_pp_generated_prompt', $prompt );
		wp_send_json_success( [ 'prompt' => $prompt ] );
	}

	public function handle_pp_rename_project(): void {
		$this->require_auth();
		$this->verify_csrf();

		$post_id = absint( $_POST['id'] ?? 0 );
		$title   = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );

		if ( ! $post_id || get_post_type( $post_id ) !== PP_CPT ) {
			wp_send_json_error( [ 'message' => 'Projekt nicht gefunden.' ] );
		}
		if ( empty( $title ) ) {
			wp_send_json_error( [ 'message' => 'Titel darf nicht leer sein.' ] );
		}

		wp_update_post( [ 'ID' => $post_id, 'post_title' => $title ] );
		wp_send_json_success( [ 'title' => $title ] );
	}

	public function handle_pp_duplicate_project(): void {
		$this->require_auth();
		$this->verify_csrf();

		$post_id  = absint( $_POST['id'] ?? 0 );
		$original = $post_id ? get_post( $post_id ) : null;

		if ( ! $original || get_post_type( $post_id ) !== PP_CPT ) {
			wp_send_json_error( [ 'message' => 'Projekt nicht gefunden.' ] );
		}

		$new_title = $original->post_title . ' (Dublikat)';
		$new_id    = wp_insert_post( [
			'post_type'   => PP_CPT,
			'post_status' => 'publish',
			'post_title'  => $new_title,
		] );

		if ( is_wp_error( $new_id ) ) {
			wp_send_json_error( [ 'message' => 'Duplikat konnte nicht erstellt werden.' ] );
		}

		// Alle Meta-Daten des Originals kopieren
		$all_meta = get_post_meta( $post_id );
		foreach ( $all_meta as $meta_key => $meta_values ) {
			foreach ( $meta_values as $meta_value ) {
				update_post_meta( $new_id, $meta_key, maybe_unserialize( $meta_value ) );
			}
		}

		wp_send_json_success( [
			'id'       => $new_id,
			'title'    => $new_title,
			'modified' => wp_date( 'd.m.Y H:i' ),
		] );
	}

	// -------------------------------------------------------------------------
	// Admin: Einstellungen
	// -------------------------------------------------------------------------

	public function handle_pp_save_settings(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung.' ] );
		}
		check_ajax_referer( 'pp_admin_nonce', 'nonce' );

		// admin.js sends: slug, username, password, prompt, logo_url, uninstall_cleanup
		$slug             = sanitize_title( wp_unslash( $_POST['slug'] ?? 'programm-pusher' ) );
		$username         = sanitize_text_field( wp_unslash( $_POST['username'] ?? '' ) );
		$password         = wp_unslash( $_POST['password'] ?? '' );
		$prompt           = PP_Sanitizer::sanitize_textarea( wp_unslash( $_POST['prompt'] ?? '' ) );
		$logo_url         = esc_url_raw( wp_unslash( $_POST['logo_url'] ?? '' ) );
		$uninstall_cleanup = ( wp_unslash( $_POST['uninstall_cleanup'] ?? 'no' ) === 'yes' ) ? 'yes' : 'no';

		if ( empty( $slug ) ) {
			wp_send_json_error( [ 'message' => 'Slug darf nicht leer sein.' ] );
		}

		update_option( PP_Settings::OPT_MASTER_PROMPT, $prompt );
		update_option( PP_Settings::OPT_FRONTEND_SLUG, $slug );
		update_option( PP_Settings::OPT_LOGO_URL, $logo_url );
		update_option( 'pp_uninstall_cleanup', $uninstall_cleanup );
		if ( ! empty( $username ) ) {
			update_option( PP_Settings::OPT_FE_USERNAME, $username );
		}
		if ( ! empty( $password ) ) {
			update_option( PP_Settings::OPT_FE_PASSWORD, password_hash( $password, PASSWORD_DEFAULT ) );
		}

		flush_rewrite_rules();
		wp_send_json_success( [
			'message'      => 'Einstellungen gespeichert.',
			'frontend_url' => home_url( '/' . $slug . '/' ),
		] );
	}

	/**
	 * Setzt alle pp_show_prompt-Posts mit falschem Status auf 'publish'.
	 * Nur für WP-Admins aufrufbar. Stellt Projekte früherer Versionen wieder her.
	 */
	public function handle_pp_repair_projects(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung.' ] );
		}
		check_ajax_referer( 'pp_admin_nonce', 'nonce' );

		global $wpdb;
		$updated = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->posts}
				    SET post_status = 'publish'
				  WHERE post_type = %s
				    AND post_status NOT IN ('publish', 'trash', 'auto-draft')",
				PP_CPT
			)
		);

		wp_send_json_success( [
			'message' => sprintf(
				'%d Projekt(e) repariert und auf "publish" gesetzt. Seite wird neu geladen…',
				(int) $updated
			),
			'count'   => (int) $updated,
		] );
	}

	public function handle_pp_reset_prompt(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung.' ] );
		}
		check_ajax_referer( 'pp_admin_nonce', 'nonce' );
		update_option( PP_Settings::OPT_MASTER_PROMPT, PP_Settings::get_default_prompt() );
		wp_send_json_success( [
			'prompt'  => PP_Settings::get_default_prompt(),
			'message' => 'Standard-Prompt wiederhergestellt.',
		] );
	}

	// -------------------------------------------------------------------------
	// Hilfsmethoden
	// -------------------------------------------------------------------------

	private function require_auth(): void {
		if ( ! PP_Session::is_authenticated() ) {
			wp_send_json_error( [ 'message' => 'Nicht eingeloggt.', 'code' => 'unauthenticated' ] );
		}
	}

	private function verify_csrf(): void {
		$token = sanitize_text_field( wp_unslash( $_POST['csrf'] ?? '' ) );
		if ( ! PP_Session::verify_token( $token ) ) {
			wp_send_json_error( [ 'message' => 'Sicherheitsprüfung fehlgeschlagen.', 'code' => 'csrf' ] );
		}
	}

	/** Alle Projektdaten aus Post Meta lesen */
	private function read_project_data( int $post_id ): array {
		$keys = [
			'kuenstlername','programmname','showdauer','sprache_der_show','programmkontext',
			'kuenstlerpersoenlichkeit','kuenstlerpersoenlichkeit_custom',
			'sprachstil','sprachstil_custom','energielevel','publikumsnaehe',
			'charakterzuege','no_go_kuenstlerwirkung','referenzen',
			'zielpublikum','altersstruktur','interaktionsgrad',
			'tonlage_ggue_publikum','tonlage_custom','publikumsbesonderheiten',
			'humortyp','humortyp_custom','gewichtung_humor_tiefe','gewichtung_custom',
			'roter_faden','zentrale_themen','emotionale_kurve','gesamtwirkung','no_go_inhalte',
			'form_der_zwischenteile','form_der_zwischenteile_custom',
			'moderationsart','moderationsart_custom',
			'sketchart','sketchart_custom',
			'running_gags','running_gag_details',
			'publikumsinteraktion','publikumsinteraktion_details',
			'vorhandenes_material_ja_nein','umgang_mit_vorhandenem_material','vorhandene_moderationen',
		];
		$data = [ 'id' => $post_id, 'title' => get_the_title( $post_id ) ];
		foreach ( $keys as $key ) {
			$val = get_post_meta( $post_id, '_pp_' . $key, true );
			$data[ $key ] = $val !== '' ? $val : null;
		}
		$data['songs']        = get_post_meta( $post_id, '_pp_songs', true ) ?: [];
		$data['zwischenteile'] = get_post_meta( $post_id, '_pp_zwischenteile', true ) ?: [];
		return $data;
	}

	/** Projektdaten aus Array in Post Meta schreiben */
	private function write_project_data( int $post_id, array $d ): void {
		$text_fields = [
			'kuenstlername','programmname','showdauer','sprache_der_show',
			'sprachstil','sprachstil_custom','energielevel','publikumsnaehe',
			'kuenstlerpersoenlichkeit_custom','altersstruktur','interaktionsgrad',
			'tonlage_ggue_publikum','tonlage_custom','gewichtung_humor_tiefe','gewichtung_custom',
			'running_gags','publikumsinteraktion','vorhandenes_material_ja_nein',
			'umgang_mit_vorhandenem_material',
			'form_der_zwischenteile_custom','moderationsart_custom','sketchart_custom',
			'humortyp_custom',
		];
		$textarea_fields = [
			'programmkontext','charakterzuege','no_go_kuenstlerwirkung','referenzen',
			'zielpublikum','publikumsbesonderheiten','roter_faden','zentrale_themen',
			'emotionale_kurve','gesamtwirkung','no_go_inhalte',
			'running_gag_details','publikumsinteraktion_details',
			'vorhandene_moderationen',
		];
		$array_fields = [
			'kuenstlerpersoenlichkeit','humortyp','form_der_zwischenteile',
			'moderationsart','sketchart',
		];

		foreach ( $text_fields as $f ) {
			update_post_meta( $post_id, '_pp_' . $f,
				sanitize_text_field( (string) ( $d[ $f ] ?? '' ) ) );
		}
		foreach ( $textarea_fields as $f ) {
			update_post_meta( $post_id, '_pp_' . $f,
				PP_Sanitizer::sanitize_textarea( (string) ( $d[ $f ] ?? '' ) ) );
		}
		foreach ( $array_fields as $f ) {
			$arr = isset( $d[ $f ] ) && is_array( $d[ $f ] )
				? array_map( 'sanitize_text_field', $d[ $f ] )
				: [];
			update_post_meta( $post_id, '_pp_' . $f, $arr );
		}

		// Songs
		$songs_clean = [];
		if ( isset( $d['songs'] ) && is_array( $d['songs'] ) ) {
			foreach ( $d['songs'] as $song ) {
				if ( empty( trim( $song['title'] ?? '' ) ) ) continue;
				$songs_clean[] = [
					'title' => sanitize_text_field( $song['title'] ?? '' ),
					'mood'  => sanitize_text_field( $song['mood'] ?? '' ),
					'note'  => PP_Sanitizer::sanitize_textarea( $song['note'] ?? '' ),
				];
			}
		}
		update_post_meta( $post_id, '_pp_songs', $songs_clean );

		// Zwischenteile
		$zt_clean = [];
		if ( isset( $d['zwischenteile'] ) && is_array( $d['zwischenteile'] ) ) {
			foreach ( $d['zwischenteile'] as $zt ) {
				$zt_clean[] = [
					'title'           => sanitize_text_field( $zt['title'] ?? '' ),
					'position'        => sanitize_text_field( $zt['position'] ?? '' ),
					'funktion'        => sanitize_text_field( $zt['funktion'] ?? '' ),
					'funktion_custom' => sanitize_text_field( $zt['funktion_custom'] ?? '' ),
					'timing'          => sanitize_text_field( $zt['timing'] ?? '' ),
					'text'            => PP_Sanitizer::sanitize_textarea( $zt['text'] ?? '' ),
					'hinweis'         => PP_Sanitizer::sanitize_textarea( $zt['hinweis'] ?? '' ),
				];
			}
		}
		update_post_meta( $post_id, '_pp_zwischenteile', $zt_clean );
	}
}
