<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Add New Landing Page', 'premise' ); ?></h2>
	<p>
		<?php _e('Please choose the type of landing page you wish to create.  You cannot modify the type of a landing page after creating it, so please choose wisely.', 'premise' )?>
	</p>
	<ul id="premise-landing-page-choice">
		<?php
		global $post;
		$types = $this->getAvailableLandingPageTypes();
		foreach($types as $advice => $type) {
			?>
			<li>
				<?php
				$url = add_query_arg(array('action'=>'edit', 'post'=>$post->ID, 'landing_page'=>$type['template'], 'landing_page_advice'=>$advice, 'landing_page_set'=>1), admin_url('post.php'));
				$url = wp_nonce_url($url, 'landing_page_set');
				?>
				<a href="<?php echo esc_attr($url); ?>"><?php esc_html_e($type['name'], 'premise' ); ?></a><br />
				<?php esc_html_e($type['description'], 'premise' ); ?>
			</li>
			<?php
		}
		?>
	</ul>
</div>