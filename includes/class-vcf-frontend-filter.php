<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VCF_Frontend_Filter {

	public static function init() {
		// Add the vehicle selector widget above the shop loop
		add_action( 'woocommerce_before_shop_loop', array( __CLASS__, 'render_vehicle_widget' ), 10 );
		
		// Wrap the product loop in a custom container for AJAX replacement
		add_action( 'woocommerce_before_shop_loop', array( __CLASS__, 'open_product_grid' ), 15 );
		add_action( 'woocommerce_after_shop_loop', array( __CLASS__, 'close_product_grid' ), 15 );

		// Add compatibility warning on single product page
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_compatibility_warning' ), 15 );

		// Enqueue JS for AJAX filtering
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		
		// Handle the AJAX requests
		add_action( 'wp_ajax_vcf_filter_products', array( __CLASS__, 'ajax_filter_products' ) );
		add_action( 'wp_ajax_nopriv_vcf_filter_products', array( __CLASS__, 'ajax_filter_products' ) );
		add_action( 'wp_ajax_vcf_save_vehicle', array( __CLASS__, 'ajax_save_vehicle' ) );
	}

	public static function enqueue_scripts() {
		if ( ! is_shop() && ! is_product_category() && ! is_product() ) {
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

			<?php if ( is_user_logged_in() ) : 
				$user_id = get_current_user_id();
				$saved_make = get_user_meta( $user_id, '_vcf_saved_make', true );
				$saved_model = get_user_meta( $user_id, '_vcf_saved_model', true );
				$saved_year = get_user_meta( $user_id, '_vcf_saved_year', true );
				
				if ( $saved_make && $saved_model && $saved_year ) :
					$make_name = get_term( $saved_make )->name;
					$model_name = get_term( $saved_model )->name;
					$year_name = get_term( $saved_year )->name;
			?>
				<div style="background: #ecfdf3; border: 1px solid #b7ebc6; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
					<strong>Your Saved Vehicle:</strong> <?php echo esc_html( $make_name . ' ' . $model_name . ' (' . $year_name . ')' ); ?>
				</div>
			<?php endif; endif; ?>

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
			<?php if ( is_user_logged_in() ) : ?>
				<button id="vcf_save_vehicle_btn" style="margin-top: 15px; margin-left: 10px; background: #10b981; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">Save My Vehicle</button>
			<?php endif; ?>
			<div id="vcf_filter_message" style="margin-top: 10px; color: #166534; font-weight: bold;"></div>
		</div>
		<?php
	}

	public static function open_product_grid() {
		echo '<div id="vcf-product-grid">';
	}

	public static function close_product_grid() {
		echo '</div>';
	}

	public static function render_compatibility_warning() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id = get_current_user_id();
		$saved_make  = get_user_meta( $user_id, '_vcf_saved_make', true );
		$saved_model = get_user_meta( $user_id, '_vcf_saved_model', true );
		$saved_year  = get_user_meta( $user_id, '_vcf_saved_year', true );

		if ( ! $saved_make || ! $saved_model || ! $saved_year ) {
			return;
		}

		global $product;
		$product_id = $product->get_id();

		$product_makes  = wp_get_post_terms( $product_id, 'vehicle_make', array( 'fields' => 'ids' ) );
		$product_models = wp_get_post_terms( $product_id, 'vehicle_model', array( 'fields' => 'ids' ) );
		$product_years  = wp_get_post_terms( $product_id, 'vehicle_year', array( 'fields' => 'ids' ) );

		$is_compatible = (
			in_array( $saved_make, $product_makes ) &&
			in_array( $saved_model, $product_models ) &&
			in_array( $saved_year, $product_years )
		);

		if ( ! $is_compatible ) {
			$make_name  = get_term( $saved_make )->name;
			$model_name = get_term( $saved_model )->name;
			$year_name  = get_term( $saved_year )->name;

			echo '<div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">';
			echo '<strong>⚠️ Compatibility Warning:</strong> This part may not fit your saved vehicle (' . esc_html( $make_name . ' ' . $model_name . ' ' . $year_name ) . '). Please verify before purchasing.';
			echo '</div>';
		}
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

		ob_start();
		if ( ! $query->have_posts() ) {
			echo '<p class="woocommerce-info">No products were found matching your vehicle.</p>';
		} else {
			woocommerce_product_loop_start();
			while ( $query->have_posts() ) {
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			}
			woocommerce_product_loop_end();
		}
		wp_reset_postdata();

		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html, 'count' => $query->found_posts ) );
	}

	public static function ajax_save_vehicle() {
		check_ajax_referer( 'vcf_ajax_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'You must be logged in to save your vehicle.' ) );
		}

		$user_id = get_current_user_id();
		$make_id  = isset( $_POST['make_id'] ) ? absint( $_POST['make_id'] ) : 0;
		$model_id = isset( $_POST['model_id'] ) ? absint( $_POST['model_id'] ) : 0;
		$year_id  = isset( $_POST['year_id'] ) ? absint( $_POST['year_id'] ) : 0;

		if ( ! $make_id || ! $model_id || ! $year_id ) {
			wp_send_json_error( array( 'message' => 'Please select Make, Model, and Year first.' ) );
		}

		update_user_meta( $user_id, '_vcf_saved_make', $make_id );
		update_user_meta( $user_id, '_vcf_saved_model', $model_id );
		update_user_meta( $user_id, '_vcf_saved_year', $year_id );

		wp_send_json_success( array( 'message' => 'Vehicle saved successfully!' ) );
	}
}