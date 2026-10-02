<?php
/**
 * Plugin Name:       Programm-Pusher
 * Plugin URI:        https://amepres-tools.de/lp/programm-pusher
 * Description:       Frontend-Prompt-Generator für Bühnenprogramme. Erfasst Showdaten im Browser und generiert einen kopierbaren Master-Prompt für KI-Systeme.
 * Version:           0.9.4
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            AMEPRES-Tools
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       programm-pusher
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PP_VERSION',    '0.9.4' );
define( 'PP_FILE',       __FILE__ );
define( 'PP_DIR',        plugin_dir_path( __FILE__ ) );
define( 'PP_URL',        plugin_dir_url( __FILE__ ) );
define( 'PP_CPT',        'pp_show_prompt' );
define( 'PP_SESSION_KEY', 'pp_frontend_auth' );

// Autoloader
spl_autoload_register( function ( string $class ): void {
	$map = [
		'PP_Loader'               => 'class-pp-loader',
		'PP_Admin'                => 'class-pp-admin',
		'PP_Settings'             => 'class-pp-settings',
		'PP_Project_CPT'          => 'class-pp-project-cpt',
		'PP_Frontend'             => 'class-pp-frontend',
		'PP_Session'              => 'class-pp-session',
		'PP_Ajax'                 => 'class-pp-ajax',
		'PP_Prompt_Builder'       => 'class-pp-prompt-builder',
		'PP_Placeholder_Resolver' => 'class-pp-placeholder-resolver',
		'PP_Sanitizer'            => 'class-pp-sanitizer',
		'PP_Utils'                => 'class-pp-utils',
	];
	if ( isset( $map[ $class ] ) ) {
		require_once PP_DIR . 'includes/' . $map[ $class ] . '.php';
	}
} );

register_activation_hook( __FILE__, function (): void {
	PP_Settings::install_defaults();
	PP_Frontend::flush_rewrite();
} );

register_deactivation_hook( __FILE__, function (): void {
	flush_rewrite_rules();
} );

add_action( 'plugins_loaded', function (): void {
	( new PP_Loader() )->run();
} );

/**
 * Versions-Migration: läuft bei jedem Seitenaufruf, kostet aber kaum
 * Performance (nur ein get_option-Check). Sobald eine neue Version
 * installiert wurde (ZIP-Upload-Overwrite ohne Deaktivierung), werden
 * die Rewrite-Rules automatisch neu gesetzt — kein manuelles
 * "Einstellungen speichern" mehr nötig.
 */
add_action( 'init', function (): void {
	if ( get_option( 'pp_db_version' ) !== PP_VERSION ) {
		// CPT ist zu diesem Zeitpunkt bereits registriert (priority 10),
		// daher können die Rules jetzt sicher neu aufgebaut werden.
		flush_rewrite_rules();

		/*
		 * Prompt-Reparatur:
		 * Ältere Versionen können einen Prompt mit anderen Platzhalter-Namen
		 * gespeichert haben (z. B. {{KUENSTLER}} statt {{KUENSTLERNAME}}).
		 * Da install_defaults() add_option() nutzt, wird ein vorhandener Prompt
		 * nie automatisch überschrieben.
		 * → Enthält der gespeicherte Prompt keinen einzigen aktuellen
		 *   Platzhalter im Format {{KEY}}, wird er auf den aktuellen Default
		 *   zurückgesetzt (nur beim Versions-Wechsel, nie im laufenden Betrieb).
		 */
		$stored = (string) get_option( PP_Settings::OPT_MASTER_PROMPT, '' );
		$needs_reset = false;
		if ( ! empty( $stored ) ) {
			// Fall A: altes Format ohne {{...}}-Platzhalter (ältere Versionen)
			if ( false === strpos( $stored, '{{KUENSTLERNAME}}' )
				&& false === strpos( $stored, '{{PROGRAMMNAME}}' )
			) {
				$needs_reset = true;
			}
			// Fall B: aktuelles Format, aber Abschnitt 17 (Recherche-Pflicht) fehlt noch
			if ( false === strpos( $stored, 'RECHERCHEPROTOKOLL' ) ) {
				$needs_reset = true;
			}
		}
		if ( $needs_reset ) {
			update_option( PP_Settings::OPT_MASTER_PROMPT, PP_Settings::get_default_prompt() );
		}

		update_option( 'pp_db_version', PP_VERSION );
	}
}, 20 );
