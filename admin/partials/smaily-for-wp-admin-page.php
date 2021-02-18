<div class="wrap">
	<h2>
		<?php echo __( 'Smaily Wordpress plugin', 'smaily-for-wp' ); ?>
		<sup id="h2-loader" style="display:none">(<?php echo __( 'Please wait, working...', 'smaily-for-wp' ); ?>)</sup>
	</h2>

	<form id="form-container" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
		<?php echo $this->partial( 'admin/partials/smaily-for-wp-admin-form.php', $this->getVars() ); ?>
	</form>
</div>
