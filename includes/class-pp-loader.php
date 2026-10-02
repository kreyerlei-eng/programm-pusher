<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Loader {
	public function run(): void {
		// Session früh starten
		add_action( 'init', [ 'PP_Session', 'start' ], 1 );

		// CPT registrieren
		( new PP_Project_CPT() )->register_hooks();

		// Frontend-Routing
		( new PP_Frontend() )->register_hooks();

		// AJAX-Handler (für eingeloggte WP-Nutzer UND Gäste)
		( new PP_Ajax() )->register_hooks();

		// Admin
		if ( is_admin() ) {
			( new PP_Admin() )->register_hooks();
		}
	}
}
