<?php
if (!defined('ABSPATH')) exit;

$tab = \WPTP\Tabs::getCurrentTab();
$field_id = $tab->fieldID;
$hide_id = "wptp[{$field_id}][hide]";
$content_name = "wptp[{$field_id}][content]";
$title_id = "wptp[{$field_id}][title]";
// To preserve ordering
$order = "wptp[{$field_id}][order]";
// For wp_editor
$content_id = "wptp-{$field_id}-content";
?>
<div data-field-id="<?php echo $field_id; ?>" class="wc-metabox wptp-block">
    <div class="wptp-block-inner">
        <input type="hidden" value="<?php echo $field_id; ?>" name="<?php echo $order; ?>">
        <div class="wptp-header">
            <div class="wptp-title">
                <p class="form-field">
                    <label for="<?php echo $title_id; ?>">Title *</label>
                    <?php if (!$tab->global) { ?>
                        <input type="text" name="<?php echo $title_id; ?>" id="<?php echo $title_id; ?>" value="<?php echo $tab->title ?>">
                    <?php } else { ?>
                        <span><?php echo $tab->title; ?></span>
                    <?php } ?>

                    <span class="wptp-actions">
                        <span class="dashicons dashicons-menu wptp-sort-block" title="Move"></span>
                        <?php if ($tab->global) { ?>
                            <a href="<?php echo get_edit_post_link($tab->ID); ?>" target="_blank"
                               class="dashicons dashicons-edit" title="Edit global tab">
                            </a>
                        <?php } else { ?>
                            <span class="dashicons dashicons-trash wptp-delete-block" title="Remove"></span>
                        <?php } ?>
                    </span>
                </p>
            </div>
            <div class="wptp-visibility">
                <?php
                woocommerce_wp_checkbox(array(
                    'name' => $hide_id,
                    'label' => 'Hide',
                    'id' => $hide_id,
                    'description' => 'Hide this tab on this product',
                    'desc_tip' => true,
                    'value' => $tab->hide
                ));
                ?>
            </div>
        </div>

        <div class="wptp-content">
            <?php if (!$tab->global) {
                wp_editor($tab->content, $content_id, array(
                    'textarea_name' => $content_name
                ));
            } else { ?>
                <p class="form-field">
                    <label for="<?php echo $content_id; ?>">Content</label>
                    <?php echo $tab->content; ?>
                </p>
            <?php } ?>
        </div>
    </div>
</div>
