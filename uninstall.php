<?php
/**
 * StackEAD Core Uninstaller
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove roles
require_once plugin_dir_path( __FILE__ ) . 'includes/Roles/RoleManager.php';
\StackEAD\Roles\RoleManager::remove_roles();

// Delete options
delete_option( 'stackead_db_version' );
delete_option( 'stackead_theme' );
