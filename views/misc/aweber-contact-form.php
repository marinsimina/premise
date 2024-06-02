<div class="entry-optin-optin align<?php echo $align; ?>">
	<?php if( $title ) { ?>
	<div class="optin-header"><?php echo esc_html( $title ); ?></div>
	<?php } ?>
	<div class="optin-box">
	<?php if ( ! $complete ) { ?>
		<form class="" method="post" action="<?php the_permalink(); ?>">

			<?php if ( $message ) { ?>
			<ul class="signup-form-messages">
				<li class="signup-form-message <?php echo $message['type']; ?>"><?php echo esc_html( $message['body'] ); ?></li>
			</ul>
			<?php } ?>

			<ul class="form-container">
				<?php do_action( 'premise_optin_signup_extra_fields', 'aweber' ); ?>
				<li>
					<label for="aweber-email"><?php _e( 'Email Address', 'premise' ); ?></label>
					<input type="text" id="aweber-email" name="aweber[email]" class="premise-merge-text-field premise-aweber-text-field" />
				</li>
				<?php
				if ( ! $is_checkout ) {
				?>
				<li>
					<label for="aweber-name"><?php _e( 'Your Name', 'premise' ); ?></label>
					<input type="text" id="aweber-name" name="aweber[name]" class="premise-merge-text-field premise-aweber-text-field" />
				</li>
				<?php
				}

				if ( $custom_fields ) {
					foreach( $custom_fields as $field ) {
						$slug = sanitize_title_with_dashes( $field['name'] );
					?>
				<li>
					<label for="aweber-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $field['name'] ); ?></label>
					<input type="text" id="aweber-<?php echo esc_attr( $slug ); ?>" name="aweber[<?php echo esc_attr( $slug ); ?>]" class="premise-merge-text-field premise-aweber-text-field" />
				</li>
				<?php
					}
				}
				?>
				<li class="premise-form-container-submit">
					<input type="submit" name="aweber[signup]" id="aweber-signup" value="<?php echo $button_text; ?>" />
				</li>
			</ul>
			<?php wp_nonce_field('aweber-signup-'.$list_id, 'aweber[signup-nonce]'); ?>
			<input type="hidden" name="aweber[list-id]" id="aweber-list-id" value="<?php echo esc_attr( $list_id ); ?>" />
			<input type="hidden" name="aweber[currenturl]" id="aweber-currenturl" value="<?php the_permalink(); ?>" />

		</form>
	<?php 
	} // complete

	$this->optin_confirm();
	?>		
	</div>
</div>