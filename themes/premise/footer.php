				<?php if(get_edit_post_link()) { ?>
				<p id="premise-final-edit-post-link"><?php edit_post_link(); ?></p>
				<?php } ?>
			</div><!-- end #inner -->
		</div><!-- end #wrap -->
		<?php if(premise_should_have_footer()) { ?>
			<div id="footer">
				<div class="wrap">
					<div class="creds">
						<?php if(premise_get_footer_copy()) { ?>
						<p><?php premise_the_footer_copy(); ?></p>
						<?php } ?>
					</div>
				</div><!-- end .wrap -->
			</div>
			<?php } ?>
		<?php premise_footer(); ?>
		<script type="text/javascript">jQuery('input[type=text]').addClass('premise-input'); jQuery('input[type=submit]').addClass('premise-submit');</script>
	</body>
</html>