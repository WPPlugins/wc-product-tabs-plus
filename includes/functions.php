<?php
if (!defined("ABSPATH")) exit;

/**
 * Return all global product tabs
 *
 * @return array
 */
function wptp_get_all_global_tabs() {
    $tabs = array();
    $tab_query = new WP_Query(array(
        'post_type' => \WPTP\GlobalTabs::get_posttype(),
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));

    // Gather data what's required
    foreach($tab_query->posts as $post) {
        $fieldID = get_post_meta($post->ID, '_wptp_field', true);
        $tab_data = (object) array(
            'ID' => $post->ID,
            'fieldID' => $fieldID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'global' => true,
            'hide' => false
        );
        $tabs[$fieldID] =  $tab_data;
    }

    return $tabs;
}

/**
 * Return all products tabs
 *
 * @return array
 */
function wptp_get_all_tabs() {
    global $post;

    // Retrieve global tabs
    $global_tabs = wptp_get_all_global_tabs();

    // Retrieve product specific tabs
    $product_tabs = get_post_meta($post->ID, '_wptp', true);

    // Override global tab property: "hide, content" from product tab
    return wptp_merge_tabs($global_tabs, $product_tabs);
}

/**
 * Merge global/custom tab
 * @param array $global_tabs
 * @param array $product_tabs
 * @return array
 */
function wptp_merge_tabs($global_tabs, $product_tabs) {
    // If both empty return nothing
    if (empty($global_tabs) && empty($product_tabs)) {
        return array();
    }

    // If product have no specific tabs return globals
    if (empty($product_tabs)) {
        wptp_apply_html($global_tabs);
        \WPTP\Tabs::set($global_tabs);
        return $global_tabs;
    }

    /**
     * Override global tabs content
     */
    foreach ($product_tabs as $fieldID => &$product_tab) {
        // Assuming that global tab ($global_tabs[$fieldID]) is deleted, so skip...
        if ($product_tab->global && !isset($global_tabs[$fieldID])) {
            unset($product_tabs[$fieldID]);
            continue;
        }

        // If it's not a global tab, don't bother, we skip
        if (!$product_tab->global) continue;

        // Single product tab
        $global_tab = $global_tabs[$fieldID];

        // Temporarily save hide value for later override
        $hide = $product_tab->hide;

        // Assign all properties from global tab
        $product_tab = $global_tab;

        /**
         * Assign back the original hide value to product tab
         */
        $product_tab->hide = $hide;
    }

    // Let's append newly created global tabs afterwards
    foreach ($global_tabs as $global_tab) {
        // Skip if fieldID is not set
        if (!isset($global_tab->fieldID)) continue;

        // Skip if existing global fieldID is already in product_tabs[]
        if (isset($product_tabs[$global_tab->fieldID])) continue;

        $product_tabs[$global_tab->fieldID] = $global_tab;
    }

    wptp_apply_html($product_tabs);
    \WPTP\Tabs::set($product_tabs);
    return $product_tabs;
}

/**
 * Check whether the tab is global or not
 * @param string $tab_field
 * @return bool
 */
function wptp_is_global($tab_field) {
    $global_tabs = wptp_get_all_global_tabs();
    $is_global = false;

    // Loop through each global tab
    foreach ($global_tabs as $global_tab) {
        // fieldID is same, which means the tab is global
        if ($tab_field === $global_tab->fieldID) {
            $is_global = true;
            break;
        }
    }

    return $is_global;
}

/**
 * Render tab html content on admin
 *
 * @param object $tab
 * @return void
 */
function wptp_render_block($tab) {
    \WPTP\Tabs::setCurrentTab($tab);
    include \WPTP\TEMPLATES . 'product-tab.php';
?>

<?php
}

/**
 * Upgrade function
 */
function wptp_upgrade() {
    // upgrade process
}

/**
 * Apply shortcode to tabs content
 * @param $tabs array
 * @since 1.0.1
 * @return void
 */
function wptp_apply_html( &$tabs) {
    /**
     * Parsing shortcode on frontend only, because this function is used
     * cross side, so we don't want to parse shortcode on the backend editor
     */
    if (is_admin()) return;

    array_walk($tabs, function($tab) {
        $tab->content = wptp_get_the_content(do_shortcode($tab->content));
    });
}

/**
 * Return html format custom tab contents
 *
 * @param $content string
 * @since 1.0.1
 * @return string
 */
function wptp_get_the_content( $content ) {
	$content = apply_filters( 'the_content', $content );
	$content = str_replace( ']]>', ']]&gt;', $content );
	return $content;
}
