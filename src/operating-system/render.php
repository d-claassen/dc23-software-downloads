<?php
$os = get_post_meta( $block->context['postId'], '_dc23_software_os', true );
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo esc_html( $os ); ?>
</div>
