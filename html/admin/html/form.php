<?php
$api_credentials = $this->api_credentials;
$autoresponders = $this->autoresponders;
?>

<div>
	<input type="hidden" name="op" value="save" />
	<input type="hidden" name="is_advanced" value="0" />
</div>

<?php if ( ! empty( $api_credentials ) ) : ?>
<p>
	<span><?php echo esc_html__( 'Your current credentials are validated', 'wp_smaily' ); ?></span>
	<a href="#" onclick="javascript:Default.removeApiKey();return false;"><strong><?php echo esc_html__( 'Remove', 'wp_smaily' ); ?></strong><img src="<?php echo SS_PLUGIN_URL; ?>/gfx/remove.png" alt="<?php echo esc_html__( 'Remove', 'wp_smaily' ); ?>" title="<?php echo esc_html__( 'Remove', 'wp_smaily' ); ?>" /></a>
</p>
<?php else : ?>
<div>
<table class="form-table">
	<tbody>
	<tr class="form-field">
		<th><?php echo esc_html__( 'Subdomain', 'wp_smaily' ); ?></th>
		<td>
			<input type="text" class="regular-text" name="subdomain" style="max-width:50%;"/>
			<small class="form-text text-muted" style="display:block;">
				For example <strong>"demo"</strong> from https://<strong>demo</strong>.smaily.net/
			</small>
		</td>

	</tr>
	<tr class="form-field">
		<th><?php echo esc_html__( 'Username', 'wp_smaily' ); ?></th>
		<td><input type="text" class="regular-text" name="username" style="max-width:50%;"/></td>
	</tr>
	<tr class="form-field">
		<th><?php echo esc_html__( 'Password', 'wp_smaily' ); ?></th>
		<td><input type="password" class="regular-text" name="password" style="max-width:50%;"/></td>
	</tr>
	</tbody>
</table>
	<input type="button" value="<?php echo esc_html__( 'Check', 'wp_smaily' ); ?>" name="Submit" class="button-primary" onclick="javascript:Default.validateApiKey();return false;" />
</div>
<?php endif; ?>

<?php if ( ! empty( $api_credentials ) ) : ?>
<ul class="tabs">
	<li><a id="link-basic" href="#basic" class="selected"><?php echo esc_html__( 'Basic', 'wp_smaily' ); ?></a></li>
	<li><a id="link-advanced" href="#advanced"><?php echo esc_html__( 'Advanced', 'wp_smaily' ); ?></a></li>
</ul>
<div class="clear"></div>

<div id="content-basic" class="tab-content">
	<div class="wrap">
		<label><?php echo esc_html__( 'Autoresponders', 'wp_smaily' ); ?> <a href="#" onclick="javascript:Default.refreshAutoresp();return false;">(<?php echo esc_html__( 'Refresh', 'wp_smaily' ); ?>)</a></label>
		<em><?php echo esc_html__( 'Select autoresponder to change regular opt-in functionality', 'wp_smaily' ); ?></em>
		<?php if ( ! empty( $autoresponders ) ) : ?>
		<select name="basic[autoresponder]">
			<option value=""><?php echo esc_html__( 'No autoresponder', 'wp_smaily' ); ?></option>
			<?php foreach ( $this->autoresponders as $item ) : ?>
			<option value="<?php echo $item->id; ?>"<?php if ( $this->autoresponder == $item->id ) : ?> selected="selected"<?php endif; ?>><?php echo $item->title; ?></option>
			<?php endforeach; ?>
		</select>
		<?php else : ?>
		<span><?php echo esc_html__( 'No autoresponders. Please click on refresh link to update.', 'wp_smaily' ); ?></span>
		<?php endif; ?>
	</div>
</div>

<div id="content-advanced" class="tab-content hidden">
	<div class="wrap">
		<label><?php echo esc_html__( 'Newsletter subscription form', 'wp_smaily' ); ?> <a href="#" onclick="javascript:Default.resetForm();return false;" title="<?php echo esc_html__( 'Restore original subscription form', 'wp_smaily' ); ?>">(<?php echo esc_html__( 'Regenerate', 'wp_smaily' ); ?>)</a></label>
		<em><?php echo esc_html__( 'HTML of subscription form', 'wp_smaily' ); ?></em>
		<textarea id="advanced-form" name="advanced[form]" rows="15"><?php echo stripslashes( $this->form ); ?></textarea>
	</div>
</div>

<p style="font-size:10px"><?php echo esc_html__( 'Note: When you save under Basic tab, default form will be used.', 'wp_smaily' ); ?></p>

<div class="wrap">
	<input type="button" value="<?php echo esc_html__( 'Save changes', 'wp_smaily' ); ?>" name="Submit" class="button-primary" onclick="javascript:Default.save();return false;" />
</div>
<?php endif; ?>

<script type="text/javascript">//<![CDATA[
  new Tabs({'target':'ul.tabs'});
  jQuery('#link-basic').click(function(){
	  jQuery('input[name=is_advanced]').val('0');
  });
  jQuery('#link-advanced').click(function(){
	  jQuery('input[name=is_advanced]').val('1');
  });
//]]></script>
