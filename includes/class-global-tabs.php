<?php
namespace WPTP;

/**
 * Class GlobalTabs
 *
 * @since 1.0.0
 */
class GlobalTabs {

    /**
     * post type slug
     * @var string
     */
    private static $post_type = 'wptp-global';

    /**
     * Initiliaze
     */
    public static function init() {
        add_action('init', array(__CLASS__, 'register_post_type'));

        // Save Field ID for current tab
        add_action('save_post_' . self::$post_type, array(__CLASS__, 'save_post'), 10, 3);

        // Render Field ID to the publish metabox
        add_action('post_submitbox_misc_actions', array(__CLASS__, 'minor_actions'));

        // Add Help Tab
	    add_action('current_screen', array(__CLASS__, 'add_tabs'));
    }

    /**
     * Register post type
     */
    public static function register_post_type() {
        $labels = array(
            'name'               => _x('Global Product Tabs', 'post type general name', 'wptp'),
            'singular_name'      => _x('Global Product Tab', 'post type singular name', 'wptp'),
            'menu_name'          => _x('Global Product Tabs', 'admin menu', 'wptp'),
            'name_admin_bar'     => _x('Global Product Tab', 'add new on admin bar', 'wptp'),
            'add_new'            => _x('Add New', 'book', 'wptp'),
            'add_new_item'       => __('Add New Global Product Tab', 'wptp'),
            'new_item'           => __('New Global Product Tab', 'wptp'),
            'edit_item'          => __('Edit Global Product Tab', 'wptp'),
            'view_item'          => __('View Global Product Tab', 'wptp'),
            'all_items'          => __('Global Product Tabs', 'wptp'),
            'search_items'       => __('Search Global Product Tabs', 'wptp'),
            'parent_item_colon'  => __('Parent Global Product Tabs:', 'wptp'),
            'not_found'          => __('No Global Product Tabs found.', 'wptp'),
            'not_found_in_trash' => __('No Global Product Tabs found in Trash.', 'wptp')
        );

        register_post_type(self::$post_type, array(
            'labels' => $labels,
            'description' => __('This is where you can add global tabs.', 'wptp'),
            'public' => false,
            'show_ui' => true,
            'supports' => array('title', 'editor'),
            'show_in_menu' => 'woocommerce',
            'show_in_nav_menus'   => true
        ));
    }

    /**
     * Get post type for Global tabs
     * @return string
     */
    public static function get_posttype() {
        return self::$post_type;
    }

    /**
     * Hooked into save_post_{$post_type}
     * @param $post_id
     * @return void
     */
    public static function save_post($post_id, $post, $update) {
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Don't save revisions and autosaves
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        if (!$update) return;

        // Check if the field id is not save already
        if (!get_post_meta($post_id, '_wptp_field', true)) {
            update_post_meta($post_id, '_wptp_field', uniqid('wptp'));
        }
    }

    public static function minor_actions() {
        global $post;

        if ($post->post_type !== self::$post_type) return;

        $fieldID = get_post_meta($post->ID, '_wptp_field', true);

        echo '<div class="misc-pub-section misc-pub-post-uniqid">Field ID: <span id="post-status-display">' . ($fieldID ? $fieldID : 'N/A') . '</span>';
    }

    public static function add_tabs() {
	    $screen = get_current_screen();

	    if ( ! $screen || ! in_array( $screen->id, array('edit-wptp-global', 'wptp-global') ) ) {
		    return;
	    }

	    $screen->add_help_tab(array(
	    	'id' => 'wptp-global-tabs',
		    'title' => 'Global Tabs',
	        'content' =>
		        '<h2>' . __('Global Tabs', 'wptp') . '</h2>' .
	            '<p>' . __('Global tabs are reusable tabs which will be displayed under every product. Let’s say you add a global tab named “Product Details”, now that tab will be displayed under each products edit screen and also on single product page.', 'wptp') . '</p>' .
	            '<p>' . __('You can identify Global Tabs on Product edit screen by its non editable title and the edit (pencil) icon. Clicking on it, will redirect you to Global Tab edit screen.', 'wptp') . '</p>' .
	            '<p>' . sprintf('<strong>%s</strong> %s', __('Note: ', 'wptp'), __('New Global Tabs are automatically appended to the list of tabs in the individual Products i.e. will always appear after all the tabs you have added to the Product.', 'wptp') . '</p>') .
	            '<h3>' . __('Warning!', 'wptp') . '</h3>' .
	            '<p>' . __('Please do not clone/draft a global tab from the Global Tabs table listing', 'wptp') . '</p>' .
	            '<p>' . __('When you clone, the unique field ID of the tab remains same, which fails to list global tabs on product edit screen. To make sure new (unique) Field ID is generated, always use “Add new” button to create new tabs.', 'wptp') . '</p>'
	    ));

	    $screen->add_help_tab(array(
	    	'id' => 'wptp-action-hooks',
		    'title' => 'Action/Filter Hooks',
	        'content' =>
		        '<h2>' . __('Action/Filter Hooks', 'wptp') . '</h2>' .
		        '<p>' . __('Action/Filter hooks are provided to allow any plugin or theme to change content output. You can customize the rendered HTML for title/contents to be displayed on the front end, for all or on a per tab basis.', 'wptp') . '</p>' .
		        '<h3>' . __('Per Tab', 'wptp') . '</h3>' .
		        "<p><pre>
add_action('wptp_tab_{\$field_ID}', function(\$product) {
    // do something with \$product-> ....
});</pre></p>" .

		        '<p>' . __('See Field ID help tab to read how to fetch "$field_ID" for selective tabs.', 'wptp') . '</p>' .

		        '<h3>' . __('All Tabs', 'wptp') . '</h3>' .
		        "<p><pre>
add_action('wptp_tab', function(\$product) {
    // do something with \$product-> ....
});</pre></p>" .

		        '<h3>' . __('Modify Tab object', 'wptp') . '</h3>' .
		        "<p><pre>
add_filter('wptp_tab_object', function(\$product_tab) {
    // do something with \$product_tab-> ....
    return \$product_tab;
});</pre></p>"
	    ));

	    $screen->add_help_tab(array(
	    	'id' => 'wptp-field-id',
		    'title' => 'Field ID',
	        'content' =>
		        '<h2>' . __('Field ID', 'wptp') . '</h2>' .
		        '<p>' . __('Field id is a unique identification for tabs added both globally and those added within the product. It is used to add hook for single selective tab.', 'wptp') . '</p>' .
	            '<h3>' . __('Get Field ID', 'wptp') . '</h3>' .
	            '<p>' . __('To find the field id for a Global Tab, edit that Global Tab and, while still on Global Tab edit screen, you can see the "Field ID" in the Publish metabox on far right as shown below.', 'wptp') . '</p>'
	    ));
    }
}
