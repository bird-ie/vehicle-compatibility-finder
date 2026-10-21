<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VCF_Taxonomies {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
	}

	public static function register_taxonomies() {
		// Register Vehicle Make
		register_taxonomy(
			'vehicle_make',
			'product',
			array(
				'labels' => array(
					'name'          => 'Vehicle Makes',
					'singular_name' => 'Vehicle Make',
					'add_new_item'  => 'Add New Make',
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
			)
		);

		// Register Vehicle Model
		register_taxonomy(
			'vehicle_model',
			'product',
			array(
				'labels' => array(
					'name'          => 'Vehicle Models',
					'singular_name' => 'Vehicle Model',
					'add_new_item'  => 'Add New Model',
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
			)
		);

		// Register Vehicle Year
		register_taxonomy(
			'vehicle_year',
			'product',
			array(
				'labels' => array(
					'name'          => 'Vehicle Years',
					'singular_name' => 'Vehicle Year',
					'add_new_item'  => 'Add New Year',
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
			)
		);
	}
}