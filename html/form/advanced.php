<?php
$form = $this->form;
?>
<?php if ( ! empty( $form ) ) : ?>
	<?php echo stripslashes( $form ); ?>
<?php else : ?>
<form id="smly" class="container" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
	<p class="error" style="padding:15px;background-color:#f2dede;margin:0 0 10px;display:none"></p>
	<p class="success" style="padding:15px;background-color:#dff0d8;margin:0 0 10px;display:none"><?php echo esc_html__( 'Thank you for subscribing to our newsletter.', 'wp_sendsmaily' ); ?></p>
	<input type="hidden" name="lang" value="<?php echo ( defined( 'ICL_LANGUAGE_CODE' ) ) ? ICL_LANGUAGE_CODE : ''; ?>" />
	<input type="hidden" name="action" value="smly" />	
	<?php wp_nonce_field( 'smaily_nonce_field', 'nonce', false ); ?>
	<p><input type="text" name="email" value="" placeholder="<?php echo esc_html__( 'Email', 'wp_sendsmaily' ); ?>" required/></p>
	<?php if ( $this->show_name ) : ?>
		<p>
			<label><?php echo esc_html__( 'Name', 'wp_sendsmaily' ); ?></label>
			<input type="text" name="name" value="" />
		</p>
	<?php endif; ?>
	<p><button type="submit"><?php echo esc_html__( 'Subscribe', 'wp_sendsmaily' ); ?></button></p>
</form>
<?php endif; ?>