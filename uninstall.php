<?php
/**
 * Programm-Pusher – Uninstall
 *
 * Wird ausgeführt, wenn das Plugin über WP-Admin gelöscht wird.
 *
 * DATENSCHUTZ-LOGIK:
 * Standardmäßig werden KEINE Daten gelöscht (Projekte + Einstellungen bleiben
 * erhalten), damit ein versehentliches Löschen vor dem Re-Installieren
 * keinen Datenverlust verursacht.
 * Nur wenn der Admin im Backend explizit "Alle Daten bei Deinstallation löschen"
 * aktiviert hat (Option pp_uninstall_cleanup = 'yes'), werden alle Daten entfernt.
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Daten NUR löschen, wenn der Admin das explizit aktiviert hat
$cleanup = get_option( 'pp_uninstall_cleanup', 'no' );
if ( $cleanup !== 'yes' ) {
	// Schutz aktiv → nichts tun, Daten bleiben erhalten
	return;
}

// ----- Optionen löschen (aktuelle + potenzielle Altnamen) -----
$options = [
	'pp_master_prompt_template',
	'pp_frontend_slug',
	'pp_frontend_username',
	'pp_frontend_password_hash',
	'pp_logo_url',
	'pp_db_version',
	'pp_uninstall_cleanup',
	// Alte/abweichende Namen aus früheren Versionen
	'pp_master_prompt',
	'pp_frontend_password',
];
foreach ( $options as $option ) {
	delete_option( $option );
}

// ----- Alle CPT-Posts + deren Meta löschen -----
global $wpdb;
$cpt = 'pp_show_prompt';

$post_ids = $wpdb->get_col(
	$wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s",
		$cpt
	)
);

foreach ( $post_ids as $id ) {
	$wpdb->delete( $wpdb->postmeta, [ 'post_id' => (int) $id ], [ '%d' ] );
	$wpdb->delete( $wpdb->posts,    [ 'ID'      => (int) $id ], [ '%d' ] );
}

flush_rewrite_rules();
