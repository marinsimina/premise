<div class="entry-optin-optin align<?php echo $align; ?>">
	<?php if($title) { ?>
	<div class="optin-header"><?php echo esc_html( $title ); ?></div>
	<?php } ?>
	<div class="optin-box">
	<?php if ( ! $complete ) { ?>
		<form class="" method="post" action="<?php echo get_permalink(); ?>#mc<?php echo $mcnumber; ?>" id="mc<?php echo $mcnumber; ?>">

			<ul class="signup-form-messages">
				<?php foreach($messages as $message) { ?>
				<li class="signup-form-message <?php echo $message['type']; ?>"><?php echo esc_html( $message['body'] ); ?></li>
				<?php } ?>
			</ul>


			<ul class="form-container">
				<?php
				do_action( 'premise_optin_signup_extra_fields', 'mailchimp' );
				foreach( $mv as $mvdata ) {

					if ( ! $mvdata['public'] )
						continue;
				?>
				<li>
					<?php 
					$this->mailchimp_label( $mvdata ); 
					$this->mailchimp_field( $mvdata ); 
					?>
				</li>
				<?php } ?>
				<li class="premise-form-container-submit">
					<input type="submit" name="mailchimp[signup]" id="mailchimp-signup" value="<?php echo $button_text; ?>" />
				</li>
			</ul>
			<?php wp_nonce_field('mailchimp-signup-'.$id, 'mailchimp-signup-nonce'); ?>
			<input type="hidden" name="mailchimp[list]" id="mailchimp-list" value="<?php echo esc_attr($id); ?>" />
			<input type="hidden" name="mailchimp[formkey]" id="mailchimp-formkey" value="mc<?php echo $mcnumber; ?>" />
			<input type="hidden" name="mailchimp[currenturl]" id="mailchimp-currenturl" value="<?php the_permalink(); ?>" />

		</form>
	<?php 
	} // complete

	$this->optin_confirm();
	?>
	</div>
</div>