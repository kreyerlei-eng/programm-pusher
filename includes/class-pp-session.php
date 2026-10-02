<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Session {

	/**
	 * Session früh starten (Hook: init, Prio 1).
	 * Seit 0.9.4 mit gehärtetem Cookie: für Skripte im Browser unlesbar (HttpOnly),
	 * auf https-Seiten nur verschlüsselt übertragen (Secure), nicht von fremden
	 * Seiten mitgeschickt (SameSite=Lax); fremde Sitzungskennungen werden abgelehnt.
	 */
	public static function start(): void {
		if ( session_status() === PHP_SESSION_NONE && ! headers_sent() ) {
			session_name( 'pp_session' );
			ini_set( 'session.use_strict_mode', '1' );
			ini_set( 'session.use_only_cookies', '1' );
			session_set_cookie_params( [
				'lifetime' => 0,
				'path'     => '/',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			] );
			session_start();
		}
	}

	/** Benutzer einloggen und CSRF-Token erzeugen */
	public static function login(): string {
		session_regenerate_id( true );
		$token = bin2hex( random_bytes( 32 ) );
		$_SESSION[ PP_SESSION_KEY ] = [
			'authenticated' => true,
			'token'         => $token,
			'time'          => time(),
		];
		return $token;
	}

	/** Session beenden */
	public static function logout(): void {
		$_SESSION[ PP_SESSION_KEY ] = [];
		session_destroy();
	}

	/** Ist der Nutzer eingeloggt? */
	public static function is_authenticated(): bool {
		if ( ! isset( $_SESSION[ PP_SESSION_KEY ]['authenticated'] ) ) {
			return false;
		}
		if ( $_SESSION[ PP_SESSION_KEY ]['authenticated'] !== true ) {
			return false;
		}
		// Session-Timeout: 8 Stunden
		$age = time() - ( $_SESSION[ PP_SESSION_KEY ]['time'] ?? 0 );
		if ( $age > 8 * 3600 ) {
			self::logout();
			return false;
		}
		return true;
	}

	/** CSRF-Token aus der Session holen */
	public static function get_token(): string {
		return $_SESSION[ PP_SESSION_KEY ]['token'] ?? '';
	}

	/** CSRF-Token aus Request prüfen */
	public static function verify_token( string $token ): bool {
		$stored = self::get_token();
		if ( empty( $stored ) || empty( $token ) ) return false;
		return hash_equals( $stored, $token );
	}
}
