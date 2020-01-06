<?php
$form = $this->form;
?>
<?php if ( ! empty( $form ) ) : ?>
	<?php echo stripslashes( $form ); ?>
<?php else : ?>
<form id="smly" class="container" action="https://<?php echo $this->domain; ?>.sendsmaily.net/api/opt-in/" method="post">
	<p class="error" style="padding:15px;background-color:#f2dede;margin:0 0 10px;display:<?php echo $this->form_has_error ? 'block' : 'none'; ?>"><?php echo esc_html( $this->error_message ); ?></p>
	<p class="success" style="padding:15px;background-color:#dff0d8;margin:0 0 10px;display:none"><?php echo esc_html__( 'Thank you for subscribing to our newsletter.', 'wp_smaily' ); ?></p>
	<input type="hidden" name="lang" value="<?php echo ( defined( 'ICL_LANGUAGE_CODE' ) ) ? ICL_LANGUAGE_CODE : ''; ?>" />
	<input type="hidden" name="success_url" value="<?php echo $this->success_url ? $this->success_url : get_site_url(); ?>" />
	<input type="hidden" name="failure_url" value="<?php echo $this->failure_url ? $this->failure_url : get_site_url(); ?>" />
	<?php wp_nonce_field( 'smaily_nonce_field', 'nonce', false ); ?>
	<p><input type="text" name="email" value="" placeholder="<?php echo esc_html__( 'Email', 'wp_smaily' ); ?>" required/></p>
	<?php if ( $this->show_name ) : ?>
		<p>
			<label><?php echo esc_html__( 'Name', 'wp_smaily' ); ?></label>
			<input type="text" name="name" value="" />
		</p>
	<?php endif; ?>
	<p><button type="submit"><?php echo esc_html__( 'Subscribe', 'wp_smaily' ); ?></button></p>
</form>
<?php endif; ?>
