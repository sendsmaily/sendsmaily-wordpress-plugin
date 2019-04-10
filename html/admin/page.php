<style type="text/css"><!--
/* helpers */
.hidden {display:none}

.wrap h2 sup {color:#090}

/* form container styles */
#form-container img, #form-container span {vertical-align:middle}
	#form-container .wrap {padding:5px 0; margin:0}
	#form-container .wrap label {font-weight:bold}
	#form-container .wrap em {display:block; font-style:normal; color:#999; padding-bottom:5px}
	#form-container .wrap input.input-text, #form-container .wrap select, #form-container .wrap textarea {width:100%}

/* content tabs */
.tab-content {padding:5px 4px 10px}

.tabs {padding-top:10px}
.tabs li {float:left; margin-right:2px}
.tabs li a {background:#ccc; -moz-border-radius-topleft:5px; -moz-border-radius-topright:5px; padding:4px 6px; color:#999; text-decoration:none}
.tabs li a.selected {background:#21759B; color:#fff}
--></style>

<script type="text/javascript">//<![CDATA[
  var SS_PLUGIN_URL = '<?php echo SS_PLUGIN_URL; ?>';
//]]></script>
<script type="text/javascript" src="<?php echo SS_PLUGIN_URL; ?>/js/default.js"></script>

<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>
		<?php echo __( 'Sendsmaily Wordpress plugin', 'wp_sendsmaily' ); ?>
		<sup id="h2-loader" style="display:none">(<?php echo __( 'Please wait, working...', 'wp_sendsmaily' ); ?>)</sup>
	</h2>
	
	<!-- @todo: display welcome/getting started message -->
	
	<form id="form-container" action="<?php echo SS_PLUGIN_URL; ?>/action.php" method="post">
		<?php echo $this->partial( 'html/admin/html/form.php', $this->getVars() ); ?>
	</form>
</div>
