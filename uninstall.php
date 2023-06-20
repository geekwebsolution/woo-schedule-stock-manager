<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// Delete Post Meta
$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE 'wssmgk\_%';" );