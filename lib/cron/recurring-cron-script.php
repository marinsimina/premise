<?php
/*
 * This script is used by the recurring membership payment system
 * DO NOT call this file from within WordPress
 */

global $premise_cron_args;

// context checks
if ( defined( 'ABSPATH' ) || empty( $argv[1] ) )
	die;

if ( ! empty( $_SERVER['HTTP_HOST'] ) || ! empty( $_SERVER['REQUEST_URI'] ) )
	die;

// do some rudimentary checks on the passed arguments 
$premise_cron_args = array();
parse_str( strip_tags( stripslashes( $argv[1] ) ), $premise_cron_args );
foreach( array( 'key', 'url', 'path' ) as $id ) {

	if ( empty( $premise_cron_args[$id] ) )
		die;

}

// don't allow funny business with the path
if ( substr( $premise_cron_args['path'], 0, 1 ) !== '/' || strpos( $premise_cron_args['path'], '../' ) !== false )
	die;

// make sure the path points to a WP install
$premise_cron_args['path'] = rtrim( $premise_cron_args['path'], '/' ) . '/';
if ( ! is_file( $premise_cron_args['path'] . 'wp-admin/admin-ajax.php' ) )
	die;

// block vBulletin from loading
// this gives a warning if warnings are turned on in PHP
define( 'VBULLETIN_PATH', dirname( $premise_cron_args['path'] ) );

// set global variables for WP
$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = parse_url( $premise_cron_args['url'], PHP_URL_HOST );
$_SERVER['REQUEST_URI'] = parse_url( $premise_cron_args['url'], PHP_URL_PATH );
$_GET['action'] = $_REQUEST['action'] = 'premise_cron';

// fire up WP 
chdir( $premise_cron_args['path'] . 'wp-admin' );
include_once( 'admin-ajax.php' );

die;
