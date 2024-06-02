<?php 
global $premise_base;

$settings = $this->getSettings();
?>
<div class="themes-php">
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2 id="premise-settings-header-line">
<?php if ( $premise_base->use_premise_theme() ) { ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=premise-main' ) ); ?>" class="nav-tab <?php premise_active_admin_tab( 'premise-main' ); ?>"><?php _e('Main', 'premise' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=premise-styles' ) ); ?>" class="nav-tab <?php premise_active_admin_tab( 'premise-styles' ); ?>"><?php _e('Styles', 'premise' ); ?></a>
<?php } ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=premise-buttons' ) ); ?>" class="nav-tab <?php premise_active_admin_tab( 'premise-buttons' ); ?>"><?php _e('Buttons', 'premise' ); ?></a>
			
		</h2>
		<?php
		if( !empty( $messages ) ) {
			foreach( $messages['updates'] as $key => $update ) {
				?><div id="premise-settings-updated-message-<?php echo $key; ?>" class="updated fade"><p><strong><?php esc_html_e( $update, 'premise' ); ?></strong></p></div><?php
			}
			foreach( $messages['errors'] as $key => $error ) {
				?><div id="premise-settings-error-message-<?php echo $key; ?>" class="error fade"><p><strong><?php esc_html_e($error, 'premise' ); ?></strong></p></div><?php
			}
		} elseif( isset( $_GET['deleted'] ) && $_GET['deleted'] == 'true' ) {
			?><div id="premise-settings-deleted-message-<?php echo $key; ?>" class="updated fade"><p><strong><?php esc_html_e('Item Deleted', 'premise' ); ?></strong></p></div><?php
		} elseif( isset( $_GET['duplicated'] ) && $_GET['duplicated'] == 'true' ) {
			?><div id="premise-settings-duplicated-message-<?php echo $key; ?>" class="updated fade"><p><strong><?php esc_html_e('Item Duplicated', 'premise' ); ?></strong></p></div><?php
		}
		?>
		<form method="post" action="<?php echo add_query_arg( array() ); ?>">
			<div id="premise-content">
