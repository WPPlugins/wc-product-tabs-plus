<?php
if (!defined('ABSPATH')) exit;

$tabs = wptp_get_all_tabs();
?>
<div id="wptp" class="wc-metaboxes-wrapper panel woocommerce_options_panel">

    <div id="wptp-container">
        <?php foreach($tabs as $tab) { wptp_render_block($tab); } // Render tabs ?>
    </div>

    <div class="toolbar">
        <button type="button" id="wptp-repeater-add" class="button button-primary">Add tab</button>
    </div>
</div>

<?php include_once \WPTP\TEMPLATES . 'product-tab.html'; ?>

<div style="display:none;">
    <?php wp_editor('', 'wptp_settings'); ?>
</div>
