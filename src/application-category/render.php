<?php
$category = get_post_meta( $block->context['postId'], '_dc23_software_category', true );
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo esc_html( $category ); ?>
</div>
