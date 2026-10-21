<?php
/**
 * Plugin Name:       Vehicle Compatibility Finder
 * Plugin URI:        https://github.com/bird-ie/vehicle-compatibility-finder
 * Description:       A WooCommerce plugin to filter products based on vehicle make, model, and year.
 * Version:           1.0.0
 * Author:            Sohaib Hassan
 * Author URI:        https://github.com/bird-ie
 * License:           GPL-2.0+
 * Text Domain:       vehicle-compat-finder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'VCF_VERSION', '1.0.0' );
define( 'VCF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VCF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once VCF_PLUGIN_DIR . 'includes/class-vcf-taxonomies.php';
require_once VCF_PLUGIN_DIR . 'includes/class-vcf-admin-meta.php';
require_once VCF_PLUGIN_DIR . 'includes/class-vcf-frontend-filter.php';
require_once VCF_PLUGIN_DIR . 'admin/class-vcf-csv-importer.php';

function vcf_run_plugin() {
	VCF_Taxonomies::init();
	VCF_Admin_Meta::init();
	VCF_Frontend_Filter::init();
	VCF_CSV_Importer::init();
}
add_action( 'plugins_loaded', 'vcf_run_plugin' );