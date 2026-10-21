<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VCF_Admin_Meta {

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post_product', array( __CLASS__, 'save_meta_box' ) );
	}

	public static function add_meta_box() {
		add_meta_box(
			'vcf_compatibility_box',
			'Vehicle Compatibility',
			array( __CLASS__, 'render_meta_box' ),
			'product',
			'side',
			'high'
		);
	}

	public static function render_meta_box( $post ) {
		wp_nonce_field( 'vcf_save_compatibility', 'vcf_compatibility_nonce' );

		// Get saved terms
		$saved_make  = wp_get_post_terms( $post->ID, 'vehicle_make', array( 'fields' => 'ids' ) );
		$saved_model = wp_get_post_terms( $post->ID, 'vehicle_model', array( 'fields' => 'ids' ) );
		$saved_year  = wp_get_post_terms( $post->ID, 'vehicle_year', array( 'fields' => 'ids' ) );

		$selected_make  = ! empty( $saved_make ) ? $saved_make[0] : '';
		$selected_model = ! empty( $saved_model ) ? $saved_model[0] : '';
		$selected_year  = ! empty( $saved_year ) ? $saved_year[0] : '';

		// Fetch terms
		$makes  = get_terms( array( 'taxonomy' => 'vehicle_make', 'hide_empty' => false ) );
		$models = get_terms( array( 'taxonomy' => 'vehicle_model', 'hide_empty' => false ) );
		$years  = get_terms( array( 'taxonomy' => 'vehicle_year', 'hide_empty' => false ) );

		echo '<div style="margin-bottom: 15px;">';
		
		// Make Dropdown
		echo '<label for="vcf_make" style="display:block; font-weight:bold; margin-bottom:5px;">Vehicle Make</label>';
		echo '<select name="vcf_make" id="vcf_make" style="width:100%;">';
		echo '<option value="">— Select Make —</option>';
		if ( ! is_wp_error( $makes ) ) {
			foreach ( $makes as $term ) {
				echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( $selected_make, $term->term_id, false ) . '>' . esc_html( $term->name ) . '</option>';
			}
		}
		echo '</select>';
		echo '</div>';

		// Model Dropdown
		echo '<div style="margin-bottom: 15px;">';
		echo '<label for="vcf_model" style="display:block; font-weight:bold; margin-bottom:5px;">Vehicle Model</label>';
		echo '<select name="vcf_model" id="vcf_model" style="width:100%;">';
		echo '<option value="">— Select Model —</option>';
		if ( ! is_wp_error( $models ) ) {
			foreach ( $models as $term ) {
				echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( $selected_model, $term->term_id, false ) . '>' . esc_html( $term->name ) . '</option>';
			}
		}
		echo '</select>';
		echo '</div>';

		// Year Dropdown
		echo '<div style="margin-bottom: 15px;">';
		echo '<label for="vcf_year" style="display:block; font-weight:bold; margin-bottom:5px;">Vehicle Year</label>';
		echo '<select name="vcf_year" id="vcf_year" style="width:100%;">';
		echo '<option value="">— Select Year —</option>';
		if ( ! is_wp_error( $years ) ) {
			foreach ( $years as $term ) {
				echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( $selected_year, $term->term_id, false ) . '>' . esc_html( $term->name ) . '</option>';
			}
		}
		echo '</select>';
		echo '</div>';

		echo '<p class="description" style="font-size:12px; color:#666;">Select the compatible vehicle for this product.</p>';
	}

	public static function save_meta_box( $post_id ) {
		// Verify nonce
		if ( ! isset( $_POST['vcf_compatibility_nonce'] ) || ! wp_verify_nonce( $_POST['vcf_compatibility_nonce'], 'vcf_save_compatibility' ) ) {
			return;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save Make
		if ( isset( $_POST['vcf_make'] ) && ! empty( $_POST['vcf_make'] ) ) {
			wp_set_post_terms( $post_id, array( absint( $_POST['vcf_make'] ) ), 'vehicle_make', false );
		} else {
			wp_set_post_terms( $post_id, array(), 'vehicle_make', false );
		}

		// Save Model
		if ( isset( $_POST['vcf_model'] ) && ! empty( $_POST['vcf_model'] ) ) {
			wp_set_post_terms( $post_id, array( absint( $_POST['vcf_model'] ) ), 'vehicle_model', false );
		} else {
			wp_set_post_terms( $post_id, array(), 'vehicle_model', false );
		}

		// Save Year
		if ( isset( $_POST['vcf_year'] ) && ! empty( $_POST['vcf_year'] ) ) {
			wp_set_post_terms( $post_id, array( absint( $_POST['vcf_year'] ) ), 'vehicle_year', false );
		} else {
			wp_set_post_terms( $post_id, array(), 'vehicle_year', false );
		}
	}
}