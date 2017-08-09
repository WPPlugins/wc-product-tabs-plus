<?php
namespace WPTP;

/**
 * Class ProductData
 *
 * @since 1.0.0
 */
class ProductData {
    public static function init() {
        // Display tabs on edit screen
        add_filter('woocommerce_product_data_tabs', array(__CLASS__, 'register_wptp_data_tab'));
        add_action('woocommerce_product_data_panels', array(__CLASS__, 'render_wptp_data_tab'));

        // Save data
        add_action('woocommerce_process_product_meta', array(__CLASS__, 'save_wptp_data_tab'), 90);

	    // Add Help Tab
	    add_action('current_screen', array(__CLASS__, 'add_tabs'), 60);
    }

    public static function register_wptp_data_tab($tabs) {
        $tabs['wptp'] = array(
            'label' => __('Product Tabs', 'wptp'),
            'target' => 'wptp',
            'class' => array()
        );

        return $tabs;
    }

    public static function render_wptp_data_tab() {
        include_once TEMPLATES . 'product-data-tab.php';
    }

	/**
	 * @since 1.0.2
	 */
    public static function save_wptp_data_tab($product_ID) {

    	if ( ! isset( $_POST['wptp'] ) || ! is_array( $_POST['wptp'] ) ) {
		    return;
	    }

	    $tabs = $_POST['wptp'];

        foreach ($tabs as $field => &$tab) {
            $tab = (object) $tab;
            $tab->fieldID = $field;

            if (property_exists($tab, 'title')) {
	            $tab->title = wp_strip_all_tags($tab->title);
            }

            $tab->global = wptp_is_global($field);

            if (!property_exists($tab, 'hide')) {
                $tab->hide = false;
            }
        }

        update_post_meta($product_ID, '_wptp', $tabs);
    }

	public static function add_tabs() {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->id, array('product') ) ) {
			return;
		}

		$screen->add_help_tab(array(
			'id' => 'wptp-global-tabs',
			'title' => 'Product Tabs',
			'content' =>
				'<h2>' . __('Product Tabs', 'wptp') . '</h2>' .
				'<p>' . __('You can identify Global Tabs on Product edit screen by its non editable title and the edit (pencil) icon. Clicking on it, will redirect you to Global Tab edit screen.', 'wptp') . '</p>' .
				'<p>' . sprintf('<strong>%s</strong> %s', __('Note: ', 'wptp'), __('New Global Tabs are automatically appended to the list of tabs in the individual Products i.e. will always appear after all the tabs you have added to the Product.', 'wptp') . '</p>') .

				'<h3>' . __('Sorting', 'wptp') . '</h3>' .
				'<p>' . __('All tabs support sorting by dragging on hamburger icon.', 'wptp') . '</p>' .

				'<h3>' . __('Deletion', 'wptp') . '</h3>' .
				'<p>' . __('You can also delete the custom tabs but not the Global ones (you can only hide them) by clicking on recycle icon.', 'wptp') . '</p>' .
				'<p>' . sprintf('<strong>%s</strong> %s', __('Note: ', 'wptp'), __('Deletion only happens on HTML DOM level within the Product backend page i.e. it is a soft delete which does not remove the data from the database until you save the Product. So if you delete one accidentally, you can just refresh the page and have it back.', 'wptp') . '</p>') .

				'<h3>' . __('Visibility', 'wptp') . '</h3>' .
				'<p>' . __('Option to hide tabs per product.', 'wptp') . '</p>' .
				'<p>' . __('This has been added to give you a “draft” feature so you can keep the contents and later decide to display the tab.', 'wptp') . '</p>' .
				'<p>' . __('Talking about visibility, Title* is required field for the tabs, otherwise it will not be displayed to the frontend.', 'wptp') . '</p>' .

				'<h3>' . __('Action/Filter Hooks', 'wptp') . '</h3>' .
				'<p>' . __('Action/Filter hooks are provided to allow any plugin or theme to change content output. You can customize the rendered HTML for title/contents to be displayed on the front end, for all or on a per tab basis.', 'wptp') . '</p>' .
				'<h4>' . __('Per Tab', 'wptp') . '</h4>' .
				"<p><pre>
add_action('wptp_tab_{\$field_ID}', function(\$product) {
    // do something with \$product-> ....
});</pre></p>" .

				'<p>' . __('Read below how to fetch "$field_ID" for selective tabs.', 'wptp') . '</p>' .

				'<h4>' . __('All Tabs', 'wptp') . '</h4>' .
				"<p><pre>
add_action('wptp_tab', function(\$product) {
    // do something with \$product-> ....
});</pre></p>" .

				'<h4>' . __('Modify Tab object', 'wptp') . '</h4>' .
				"<p><pre>
add_filter('wptp_tab_object', function(\$product_tab) {
    // do something with \$product_tab-> ....
    return \$product_tab;
});</pre></p>" .

				'<h3>' . __('Get Field ID', 'wptp') . '</h3>' .
				'<p>' . __('Unfortunately there is no easy way to retreive field ID for a custom tab added to a single product. The allocation of the Id all happens behind the scenes dynamically. In order to retreive custom tab field ID you will have to use browser developer tools. Navigate to a product custom tab, right click on Title* label text and click Inspect (on Chrome) or Inspect Element (on Firefox)', 'wptp') . '</p>' .
				'<p>' . __('And you\'ll be focused on the &lt;label&gt; tag, you will find the field id inside "for" attribute', 'wptp') . '</p>' .
				'<p>' . __('Copy the ID i.e. "wptp57f8a147f2ae8" and use it on the action hook "wptp_tab_wptp57f8a147f2ae8".', 'wptp') . '</p>'

		));
	}
}
