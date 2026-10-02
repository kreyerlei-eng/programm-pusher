<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Frontend {

	public function register_hooks(): void {
		add_action( 'init',             [ $this, 'add_rewrite_rule' ] );
		add_filter( 'query_vars',       [ $this, 'add_query_var' ] );
		add_filter( 'template_include', [ $this, 'serve_frontend' ] );
		// Slug-Änderung: Rewrite flushen
		add_action( 'update_option_' . PP_Settings::OPT_FRONTEND_SLUG, [ __CLASS__, 'flush_rewrite' ] );
	}

	/** Rewrite-Regel für den Frontend-Slug registrieren */
	public function add_rewrite_rule(): void {
		$slug = PP_Settings::get_frontend_slug();
		add_rewrite_rule(
			'^' . preg_quote( $slug, '/' ) . '(/.*)?$',
			'index.php?pp_frontend=1',
			'top'
		);
	}

	public function add_query_var( array $vars ): array {
		$vars[] = 'pp_frontend';
		return $vars;
	}

	/** Komplettes Frontend-Template ausliefern */
	public function serve_frontend( string $template ): string {
		if ( ! get_query_var( 'pp_frontend' ) ) {
			return $template;
		}
		// Assets für Frontend einbinden
		$this->enqueue_frontend_assets();
		// Template ausliefern
		$tpl = PP_DIR . 'templates/frontend.php';
		if ( file_exists( $tpl ) ) {
			return $tpl;
		}
		return $template;
	}

	private function enqueue_frontend_assets(): void {
		// Kein Theme-CSS, kein Admin-CSS
		add_action( 'wp_enqueue_scripts', function (): void {
			// Alle Theme-Styles entfernen
			global $wp_styles;
			if ( isset( $wp_styles ) ) {
				$wp_styles->queue = [];
			}
			wp_enqueue_style(
				'pp-frontend',
				PP_URL . 'assets/css/frontend.css',
				[],
				PP_VERSION
			);
		}, 999 );
	}

	/** Rewrite-Regeln nach Slug-Änderung oder Aktivierung flushen */
	public static function flush_rewrite(): void {
		// Muss nach add_rewrite_rule aufgerufen werden
		add_action( 'init', function (): void {
			flush_rewrite_rules();
		}, 999 );
	}
}
