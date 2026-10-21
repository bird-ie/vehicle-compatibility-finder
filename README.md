# Vehicle Compatibility Finder

A WooCommerce plugin designed to prevent incorrect part purchases by filtering products based on vehicle make, model, year, and engine.

## 🎯 The Business Problem

Auto parts stores lose money because customers buy the wrong parts (e.g., brake pads for a 2018 Ford Focus vs. a 2019 Ford Focus). 
*   Customers don't know their exact vehicle details (Engine size, variant).
*   Store owners have no way to easily bulk-assign compatibility data to their 5,000+ products.

## 💡 The Solution

*   Adds a "Select Your Vehicle" widget to the WooCommerce shop.
*   Customers select Make → Model → Year → Engine. The shop filters to show *only* compatible parts.
*   Products have a dedicated "Compatibility" tab in the admin where the store owner can add/remove vehicles or bulk-import a CSV.
*   Logged-in customers can "Save My Vehicle" for future visits.

## 🚀 Features

### Admin Operations
*   **Custom Taxonomies:** Dedicated `Vehicle Make`, `Vehicle Model`, and `Vehicle Year` taxonomies for WooCommerce products.
*   **Product Meta Box:** A clean, side-panel UI on the product edit screen to assign a vehicle to a product.
*   **CSV Importer:** A bulk-upload tool that maps product SKUs to vehicle data. If a term doesn't exist, it automatically creates it.

### Frontend Experience
*   **Vehicle Filter Widget:** Displays on the shop and category pages with three cascading dropdowns.
*   **AJAX Filtering:** Instantly updates the product grid without a page reload.
*   **Saved Vehicles:** Logged-in customers can save their vehicle, which is stored in their user meta.
*   **Compatibility Warning:** If a customer views a product that is *not* compatible with their saved vehicle, a red warning banner appears.

## 🛠️ Technical Architecture

### Directory Structure
```text
vehicle-compatibility-finder/
│
├── admin/
│   └── class-vcf-csv-importer.php
│
├── assets/
│   └── frontend.js
│
├── includes/
│   ├── class-vcf-taxonomies.php
│   ├── class-vcf-admin-meta.php
│   └── class-vcf-frontend-filter.php
│
├── vehicle-compatibility-finder.php
└── README.md
Key Technical Implementations
Taxonomy-Powered Filtering: Uses WordPress tax_query with an AND relationship to ensure products match all selected vehicle criteria.

AJAX Filtering: The frontend uses jQuery to send an AJAX request to admin-ajax.php, which runs a custom WP_Query and returns the rendered product HTML.

CSV Processing: Reads the CSV file line-by-line using fgetcsv(), looks up the product by SKU using wc_get_product_id_by_sku(), and uses term_exists() and wp_set_post_terms() to assign the data.

⚙️ Installation
Upload the vehicle-compatibility-finder folder to /wp-content/plugins/.

Activate the plugin through the 'Plugins' menu in WordPress.

Ensure WooCommerce is installed and active.

Go to Products → Vehicle Makes (and Models/Years) to add your vehicle data.

Go to Products → Vehicle Importer to bulk-assign vehicles via CSV.

💻 Developer Notes
AJAX Security: All AJAX endpoints verify nonces (wp_verify_nonce) and sanitize inputs.

Conditional Loading: The frontend scripts are only loaded on shop, category, or single product pages to keep the site lightweight.