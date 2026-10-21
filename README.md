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

## 🛠️ Technical Stack

*   **WordPress:** Custom Post Types & Taxonomies
*   **WooCommerce:** Product Meta & Shop Filtering
*   **PHP:** OOP Architecture
*   **JavaScript:** AJAX for dynamic filtering

## ⚙️ Installation

1. Upload the `vehicle-compatibility-finder` folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Ensure WooCommerce is installed and active.