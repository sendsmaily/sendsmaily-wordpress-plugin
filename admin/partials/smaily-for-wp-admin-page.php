<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>
		<?php echo __( 'Smaily Wordpress plugin', 'wp_smaily' ); ?>
		<sup id="h2-loader" style="display:none">(<?php echo __( 'Please wait, working...', 'wp_smaily' ); ?>)</sup>
	</h2>

	<form id="form-container" action="<?php echo admin_url('admin-ajax.php'); ?>" method="post">
		<?php echo $this->partial( 'html/admin/html/form.php', $this->getVars() ); ?>
	</form>
</div>
