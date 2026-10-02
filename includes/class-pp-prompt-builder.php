<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Prompt_Builder {
	/**
	 * @param int   $post_id   Post-ID (used when reading from DB).
	 * @param array $raw_data  Optional: raw form-data array from the save request.
	 *                         When provided, placeholders are resolved directly from
	 *                         this array — no DB read, no cache race condition.
	 *                         When empty, falls back to reading from post meta (DB).
	 */
	public function build( int $post_id, array $raw_data = [] ): string {
		$template = PP_Settings::get_master_prompt();
		$resolver = new PP_Placeholder_Resolver();
		$data     = ! empty( $raw_data )
			? $resolver->resolve_from_data( $raw_data )
			: $resolver->resolve( $post_id );
		foreach ( $data as $key => $value ) {
			$template = str_replace( '{{' . $key . '}}', (string) $value, $template );
		}
		return $template;
	}
}
