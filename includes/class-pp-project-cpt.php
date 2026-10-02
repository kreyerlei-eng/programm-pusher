<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Project_CPT {
	public function register_hooks(): void {
		add_action( 'init', [ $this, 'register_cpt' ] );
	}

	public function register_cpt(): void {
		register_post_type( PP_CPT, [
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'show_in_rest'       => false,
			'query_var'          => false,
			'rewrite'            => false,
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => [ 'title' ],
			'capability_type'    => 'post',
		] );
	}
}
