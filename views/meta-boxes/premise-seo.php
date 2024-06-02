<div class="premise-option-box">
	<?php
	$titleLength = strlen($seo['title']);
	$suggestedTitleLength = 70;
	$titleLengthClass = $titleLength > $suggestedTitleLength ? 'exceeds' : '';
	?>
	<h4><label for="premise-seo-title"><?php _e('Custom Landing Page Title', 'premise' ); ?></label></h4>
	<p><?php _e('The title of your page will be the <code>&lt;title&gt;</code> tag by default.  You can override this by entering your own custom <code>&lt;title&gt;</code> tag below.', 'premise' ); ?></p>
	<input type="text" class="large-text" name="premise-seo[title]" id="premise-seo-title" value="<?php echo esc_attr($seo['title']); ?>" />
	<p>
		<strong><?php _e('Recommended:', 'premise' ); ?></strong>
		<?php printf(__('Most search engines allow up to <span class="premise-character-count-suggested">%d</span> characters for your title.  You have <span class="premise-character-count %s">%d</span> above.', 'premise' ), $suggestedTitleLength, $titleLengthClass, $titleLength); ?>
	</p>
</div>

<div class="premise-option-box">
	<?php
	$descriptionLength = strlen($seo['description']);
	$suggestedDescriptionLength = 150;
	$descriptionLengthClass = $descriptionLength > $suggestedDescriptionLength ? 'exceeds' : '';
	?>
	<h4><label for="premise-seo-description"><?php _e('Custom Landing Page Meta Description', 'premise' ); ?></label></h4>
	<p><?php _e('The first few words of your page will be the <code>&lt;meta name="description"&gt;</code> tag by default.  You can override this and enter your own custom description below.', 'premise' ); ?></p>
	<textarea rows="2" class="large-text" name="premise-seo[description]" id="premise-seo-description"><?php echo esc_html($seo['description']); ?></textarea>
	<p>
		<strong><?php _e('Recommended:', 'premise' ); ?></strong>
		<?php printf(__('Most search engines allow approx. <span class="premise-character-count-suggested">%d</span> characters for your description.  You have <span class="premise-character-count %s">%d</span> above.', 'premise' ), $suggestedDescriptionLength, $descriptionLengthClass, $descriptionLength); ?>
	</p>
</div>

<div class="premise-option-box">
	<h4><label for="premise-seo-keywords"><?php _e('Custom Landing Page Meta Keywords', 'premise' ); ?></label></h4>
	<p><?php _e('The <code>&lt;meta name="keywords"&gt;</code> tag has become less important, and are not used by Google, Yahoo, or Bing for site ranking.  But smaller search engines may still be using the tag, so you can add them here if you want.'); ?></p>
	<input type="text" class="large-text" name="premise-seo[keywords]" id="premise-seo-keywords" value="<?php echo esc_attr($seo['keywords']); ?>" />
</div>

<div class="premise-option-box">
	<h4><label for="premise-seo-canonical"><?php _e('Custom Canonical URI', 'premise' ); ?></label></h4>
	<p><?php _e('Add a custom <code>&lt;link rel="canonical" /&gt;</code> tag for this page.', 'premise' ); ?> <a href="#" class="premise-link-tip"><?php _e('[?]', 'premise' ); ?></a></p>
	<input type="text" class="large-text" name="premise-seo[canonical]" id="premise-seo-canonical" value="<?php echo esc_attr($seo['canonical']); ?>" />
</div>

<div class="premise-option-box">
	<h4><?php _e('Robots Meta Settings', 'premise' ); ?></h4>
	<p><?php _e('You can add these tags to tell robots not to index the content of a page, not scan it for links to follow, and/or remove your pages from the Google Cache.', 'premise' ); ?></p>
	<ul>
		<li>
			<label>
				<input <?php checked(1, $seo['noindex']); ?> type="checkbox" name="premise-seo[noindex]" id="premise-seo-noindex" value="1" />
				<?php _e('Apply <code>noindex</code> to this page', 'premise' ); ?>
			</label>
			<a href="#" class="premise-link-tip"><?php _e('[?]', 'premise' ); ?></a>
		</li>
		<li>
			<label>
				<input <?php checked(1, $seo['nofollow']); ?> type="checkbox" name="premise-seo[nofollow]" id="premise-seo-nofollow" value="1" />
				<?php _e('Apply <code>nofollow</code> to this page', 'premise' ); ?>
			</label>
			<a href="#" class="premise-link-tip"><?php _e('[?]', 'premise' ); ?></a>
		</li>
		<li>
			<label>
				<input <?php checked(1, $seo['noarchive']); ?> type="checkbox" name="premise-seo[noarchive]" id="premise-seo-noarchive" value="1" />
				<?php _e('Apply <code>noarchive</code> to this page', 'premise' ); ?>
			</label>
			<a href="#" class="premise-link-tip"><?php _e('[?]', 'premise' ); ?></a>
		</li>
	</ul>
</div>

<div class="premise-option-box">
	<h4><?php _e('Feed Autodetect', 'premise' ); ?></h4>
	<p><?php _e('On by default &mdash; checking the box turns it off.  You may not want your feed autodetect to show up on a landing page, as it may deter from your call to action.  You can turn off the feed autodiscovery only for this page and not the rest of your site.', 'premise' ); ?></p>
	<ul>
		<li>
			<label>
				<input <?php checked(1, $seo['disable-feed']); ?> type="checkbox" name="premise-seo[disable-feed]" id="premise-seo-disable-feed" value="1" />
				<?php _e('Turn off Feed Autodetect for this page?', 'premise' ); ?>
			</label>
		</li>
	</ul>
</div>

<?php wp_nonce_field('save-premise-seo-settings', 'save-premise-seo-settings-nonce'); ?>