<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Utils {
	public static function json_response( bool $success, array $data = [] ): void {
		wp_send_json( array_merge( [ 'success' => $success ], $data ) );
	}
}
