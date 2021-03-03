<form id="smly" action="https://<?php echo $this->domain; ?>.sendsmaily.net/api/opt-in/" method="post">
	<p class="error" style="padding:15px;background-color:#f2dede;margin:0 0 10px;display:<?php echo $this->form_has_response ? 'block' : 'none'; ?>"><?php echo esc_html( $this->response_message ); ?></p>
	<p class="success" style="padding:15px;background-color:#dff0d8;margin:0 0 10px;display:<?php echo $this->form_is_successful ? 'block' : 'none'; ?>"><?php echo esc_html__( 'Thank you for subscribing to our newsletter.', 'smaily-for-wp' ); ?></p>
	<?php if ( $this->autoresponder_id ): ?>
		<input type="hidden" name="autoresponder" value="<?php echo $this->autoresponder_id; ?>" />
	<?php endif; ?>
	<input type="hidden" name="lang" value="<?php echo esc_html( $this->getLanguageCode() ); ?>" />
	<input type="hidden" name="success_url" value="<?php echo $this->success_url ? $this->success_url : get_site_url(); ?>" />
	<input type="hidden" name="failure_url" value="<?php echo $this->failure_url ? $this->failure_url : get_site_url(); ?>" />
	<p><input type="text" name="email" value="" placeholder="<?php echo esc_html__( 'Email', 'smaily-for-wp' ); ?>" required/></p>
	<?php if ( $this->show_name ) : ?>
		<p><input type="text" name="name" value="" placeholder="<?php echo esc_html__( 'Name', 'smaily-for-wp' ); ?>" /></p>
	<?php endif; ?>
	<p><button type="submit"><?php echo esc_html__( 'Subscribe', 'smaily-for-wp' ); ?></button></p>
</form>
