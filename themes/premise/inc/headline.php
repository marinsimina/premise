<?php if(premise_should_have_header()) { ?>
<div class="headline-area">
	<h1 class="entry-title"><?php the_title(); ?></h1>
	<?php if(premise_get_subhead()) { ?>
	<h2 class="entry-subtitle"><?php premise_the_subhead(); ?></h2>
	<?php } ?>
</div>
<?php } ?>