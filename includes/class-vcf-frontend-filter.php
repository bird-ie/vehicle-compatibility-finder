<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VCF_Frontend_Filter {

	public static function init() {
		// Add the vehicle selector widget above the shop loop
		add_action( 'woocommerce_before_shop_loop', array( __CLASS__, 'render_vehicle_widget' ), 10 );
		
		// Enqueue JS for AJAX filtering
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		
		// Handle the AJAX request
		add_action( 'wp_ajax_vcf_filter_products', array( __CLASS__, 'ajax_filter_products' ) );
		add_action( 'wp_ajax_nopriv_vcf_filter_products', array( __CLASS__, 'ajax_filter_products' ) );
	}

	public static function enqueue_scripts() {
		if ( ! is_shop() && ! is_product_category() ) {
			return;
		}

		wp_enqueue_script(
			'vcf-frontend-js',
			VCF_PLUGIN_URL . 'assets/frontend.js',
			array( 'jquery' ),
			VCF_VERSION,
			true
		);

		wp_localize_script(
			'vcf-frontend-js',
			'vcfAjax',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'vcf_ajax_nonce' ),
			)
		);
	}

	public static function render_vehicle_widget() {
		$makes  = get_terms( array( 'taxonomy' => 'vehicle_make', 'hide_empty' => false ) );
		$models = get_terms( array( 'taxonomy' => 'vehicle_model', 'hide_empty' => false ) );
		$years  = get_terms( array( 'taxonomy' => 'vehicle_year', 'hide_empty' => false ) );

		if ( is_wp_error( $makes ) || empty( $makes ) ) {
			return;
		}

		?>
		<div class="vcf-vehicle-widget" style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #e5e7eb;">
			<h3 style="margin-top: 0;">Find Parts for Your Vehicle</h3>
			<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
				<div>
					<label for="vcf_make_filter" style="display:block; font-weight:bold; margin-bottom:5px;">Make</label>
					<select id="vcf_make_filter" style="width: 100%; padding: 8px;">
						<option value="">Select Make</option>
						<?php foreach ( $makes as $make ) : ?>
							<option value="<?php echo esc_attr( $make->term_id ); ?>"><?php echo esc_html( $make->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label for="vcf_model_filter" style="display:block; font-weight:bold; margin-bottom:5px;">Model</label>
					<select id="vcf_model_filter" style="width: 100%; padding: 8px;">
						<option value="">Select Model</option>
						<?php foreach ( $models as $model ) : ?>
							<option value="<?php echo esc_attr( $model->term_id ); ?>"><?php echo esc_html( $model->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label for="vcf_year_filter" style="display:block; font-weight:bold; margin-bottom:5px;">Year</label>
					<select id="vcf_year_filter" style="width: 100%; padding: 8px;">
						<option value="">Select Year</option>
						<?php foreach ( $years as $year ) : ?>
							<option value="<?php echo esc_attr( $year->term_id ); ?>"><?php echo esc_html( $year->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<button id="vcf_filter_btn" style="margin-top: 15px; background: #111827; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">Filter Products</button>
			<button id="vcf_reset_btn" style="margin-top: 15px; margin-left: 10px; background: #e5e7eb; color: #111827; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">Reset</button>
			<div id="vcf_filter_message" style="margin-top: 10px; color: #166534; font-weight: bold;"></div>
		</div>
		<?php
	}

	public static function ajax_filter_products() {
		check_ajax_referer( 'vcf_ajax_nonce', 'nonce' );

		$make_id  = isset( $_POST['make_id'] ) ? absint( $_POST['make_id'] ) : 0;
		$model_id = isset( $_POST['model_id'] ) ? absint( $_POST['model_id'] ) : 0;
		$year_id  = isset( $_POST['year_id'] ) ? absint( $_POST['year_id'] ) : 0;

		if ( ! $make_id && ! $model_id && ! $year_id ) {
			wp_send_json_error( array( 'message' => 'Please select at least one vehicle detail.' ) );
		}

		$tax_query = array( 'relation' => 'AND' );

		if ( $make_id ) {
			$tax_query[] = array(
				'taxonomy' => 'vehicle_make',
				'field'    => 'term_id',
				'terms'    => $make_id,
			);
		}
		if ( $model_id ) {
			$tax_query[] = array(
				'taxonomy' => 'vehicle_model',
				'field'    => 'term_id',
				'terms'    => $model_id,
			);
		}
		if ( $year_id ) {
			$tax_query[] = array(
				'taxonomy' => 'vehicle_year',
				'field'    => 'term_id',
				'terms'    => $year_id,
			);
		}

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'tax_query'      => $tax_query,
		);

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			wp_send_json_error( array( 'message' => 'No products found for this vehicle.' ) );
		}

		ob_start();
		while ( $query->have_posts() ) {
			$query->the_post();
			wc_get_template_part( 'content', 'product' );
		}
		wp_reset_postdata();

		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html, 'count' => $query->found_posts ) );
	}
}