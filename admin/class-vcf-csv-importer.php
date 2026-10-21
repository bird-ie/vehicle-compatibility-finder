<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VCF_CSV_Importer {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
		add_action( 'admin_post_vcf_import_csv', array( __CLASS__, 'handle_import' ) );
	}

	public static function add_menu_page() {
		add_submenu_page(
			'edit.php?post_type=product',
			'Vehicle Compatibility Importer',
			'Vehicle Importer',
			'manage_woocommerce',
			'vcf-csv-importer',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'You do not have permission to access this page.' );
		}

		$import_url = admin_url( 'admin-post.php?action=vcf_import_csv' );
		?>
		<div class="wrap">
			<h1>Vehicle Compatibility CSV Importer</h1>
			<p>Upload a CSV file to bulk-assign vehicle compatibility to your products.</p>

			<div style="background: #f9fafb; border: 1px solid #e5e7eb; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
				<h3 style="margin-top: 0;">CSV Format</h3>
				<p>Your CSV must have the following columns (in this exact order):</p>
				<code style="display: block; background: #fff; padding: 10px; border: 1px solid #e5e7eb;">SKU, Make, Model, Year</code>
				<p style="margin-top: 10px;"><strong>Example:</strong></p>
				<code style="display: block; background: #fff; padding: 10px; border: 1px solid #e5e7eb;">
					F150-SKU, Ford, Focus, 2018<br>
					CIVIC-SKU, Honda, Civic, 2020
				</code>
			</div>

			<?php if ( isset( $_GET['import_success'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><strong>Import Complete!</strong> <?php echo absint( $_GET['import_success'] ); ?> products were updated.</p>
				</div>
			<?php endif; ?>

			<?php if ( isset( $_GET['import_error'] ) ) : ?>
				<div class="notice notice-error is-dismissible">
					<p><strong>Error:</strong> <?php echo esc_html( urldecode( $_GET['import_error'] ) ); ?></p>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( $import_url ); ?>" enctype="multipart/form-data">
				<?php wp_nonce_field( 'vcf_csv_import', 'vcf_csv_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="vcf_csv_file">CSV File</label></th>
						<td>
							<input type="file" name="vcf_csv_file" id="vcf_csv_file" accept=".csv" required>
							<p class="description">Upload a .csv file with the correct format above.</p>
						</td>
					</tr>
				</table>
				<?php submit_button( 'Import CSV' ); ?>
			</form>
		</div>
		<?php
	}

	public static function handle_import() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'Permission denied.' );
		}

		if ( ! isset( $_POST['vcf_csv_nonce'] ) || ! wp_verify_nonce( $_POST['vcf_csv_nonce'], 'vcf_csv_import' ) ) {
			wp_die( 'Security check failed.' );
		}

		if ( empty( $_FILES['vcf_csv_file']['tmp_name'] ) ) {
			wp_safe_redirect( add_query_arg( 'import_error', 'No file uploaded.', admin_url( 'edit.php?post_type=product&page=vcf-csv-importer' ) ) );
			exit;
		}

		$file = $_FILES['vcf_csv_file']['tmp_name'];
		$handle = fopen( $file, 'r' );

		if ( ! $handle ) {
			wp_safe_redirect( add_query_arg( 'import_error', 'Could not open CSV file.', admin_url( 'edit.php?post_type=product&page=vcf-csv-importer' ) ) );
			exit;
		}

		$header = fgetcsv( $handle ); // Read the header row

		if ( $header[0] !== 'SKU' || $header[1] !== 'Make' || $header[2] !== 'Model' || $header[3] !== 'Year' ) {
			fclose( $handle );
			wp_safe_redirect( add_query_arg( 'import_error', 'Invalid CSV header. Please use: SKU, Make, Model, Year', admin_url( 'edit.php?post_type=product&page=vcf-csv-importer' ) ) );
			exit;
		}

		$updated_count = 0;
		$row = 1;

		while ( ( $data = fgetcsv( $handle ) ) !== false ) {
			$row++;
			
			if ( count( $data ) < 4 ) {
				continue;
			}

			$sku   = sanitize_text_field( $data[0] );
			$make  = sanitize_text_field( $data[1] );
			$model = sanitize_text_field( $data[2] );
			$year  = sanitize_text_field( $data[3] );

			if ( empty( $sku ) ) {
				continue;
			}

			// Find product by SKU
			$product_id = wc_get_product_id_by_sku( $sku );

			if ( ! $product_id ) {
				continue; // Skip if product doesn't exist
			}

			// Helper to assign or create term
			$assign_term = function( $taxonomy, $term_name ) use ( $product_id ) {
				if ( empty( $term_name ) ) {
					return;
				}

				$term = term_exists( $term_name, $taxonomy );
				if ( ! $term ) {
					$term = wp_insert_term( $term_name, $taxonomy );
				}

				if ( ! is_wp_error( $term ) ) {
					$term_id = is_array( $term ) ? $term['term_id'] : $term;
					wp_set_post_terms( $product_id, array( $term_id ), $taxonomy, false );
				}
			};

			$assign_term( 'vehicle_make', $make );
			$assign_term( 'vehicle_model', $model );
			$assign_term( 'vehicle_year', $year );

			$updated_count++;
		}

		fclose( $handle );

		wp_safe_redirect( add_query_arg( 'import_success', $updated_count, admin_url( 'edit.php?post_type=product&page=vcf-csv-importer' ) ) );
		exit;
	}
}