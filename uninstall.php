<?php
/**
 * Uninstall file, which would delete all user metadata and configuration settings
 *
 * @since 1.0
 */
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit();

global $wpdb;

$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '%wptp%';");
$wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type='wptp-global';");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name='wptp_version';");
