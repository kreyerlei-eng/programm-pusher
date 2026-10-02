<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Sanitizer {
	public static function sanitize_textarea( string $value ): string {
		$value = str_replace( "\r\n", "\n", $value );
		$value = str_replace( "\r", "\n", $value );
		$value = wp_strip_all_tags( $value );
		return trim( $value );
	}
}
