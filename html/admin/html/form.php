<?php
$key            = $this->key;
$autoresponders = $this->autoresponders;
// Check if old api key is in use for moving from 1.1.5 to 1.2.0.
$old_key        = preg_match( '/^[^\:]+$/', $key );
?>

<div>
	<input type="hidden" name="op" value="save" />
	<input type="hidden" name="is_advanced" value="0" />
</div>

<?php if ( ! empty( $key ) && ! $old_key ) : ?>
<p>
	<span><?php echo esc_html__( 'Your current credentials are validated', 'wp_sendsmaily' ); ?></span>
	<a href="#" onclick="javascript:Default.removeApiKey();return false;"><strong><?php echo esc_html__( 'Remove', 'wp_sendsmaily' ); ?></strong><img src="<?php echo SS_PLUGIN_URL; ?>/gfx/remove.png" alt="<?php echo esc_html__( 'Remove', 'wp_sendsmaily' ); ?>" title="<?php echo esc_html__( 'Remove', 'wp_sendsmaily' ); ?>" /></a>
</p>
<?php else : ?>
<div>
<table class="form-table">
	<tbody>
	<tr class="form-field">
		<th><?php echo esc_html__( 'Subdomain', 'wp_sendsmaily' ); ?></th>
		<td>
			<input type="text" class="regular-text" name="subdomain" style="max-width:50%;"/>
			<small class="form-text text-muted" style="display:block;">
				For example <strong>"demo"</strong> from https://<strong>demo</strong>.sendsmaily.net/
			</small>
		</td>

	</tr>
	<tr class="form-field">
		<th><?php echo esc_html__( 'Username', 'wp_sendsmaily' ); ?></th>
		<td><input type="text" class="regular-text" name="username" style="max-width:50%;"/></td>
	</tr>
	<tr class="form-field">
		<th><?php echo esc_html__( 'Password', 'wp_sendsmaily' ); ?></th>
		<td><input type="password" class="regular-text" name="password" style="max-width:50%;"/></td>
	</tr>
	</tbody>
</table>
	<input type="button" value="<?php echo esc_html__( 'Check', 'wp_sendsmaily' ); ?>" name="Submit" class="button-primary" onclick="javascript:Default.validateApiKey();return false;" />
</div>
<?php endif; ?>

<?php if ( ! empty( $key ) && ! $old_key ) : ?>
<ul class="tabs">
	<li><a id="link-basic" href="#basic" class="selected"><?php echo esc_html__( 'Basic', 'wp_sendsmaily' ); ?></a></li>
	<li><a id="link-advanced" href="#advanced"><?php echo esc_html__( 'Advanced', 'wp_sendsmaily' ); ?></a></li>
</ul>
<div class="clear"></div>

<div id="content-basic" class="tab-content">
	<div class="wrap">
		<label><?php echo esc_html__( 'Autoresponders', 'wp_sendsmaily' ); ?> <a href="#" onclick="javascript:Default.refreshAutoresp();return false;">(<?php echo esc_html__( 'Refresh', 'wp_sendsmaily' ); ?>)</a></label>
		<em><?php echo esc_html__( 'Select autoresponder to change regular opt-in functionality', 'wp_sendsmaily' ); ?></em>
		<?php if ( ! empty( $autoresponders ) ) : ?>
		<select name="basic[autoresponder]">
			<option value=""><?php echo esc_html__( 'No autoresponder', 'wp_sendsmaily' ); ?></option>
			<?php foreach ( $this->autoresponders as $item ) : ?>
			<option value="<?php echo $item->id; ?>"<?php if ( $this->autoresponder == $item->id ) : ?> selected="selected"<?php endif; ?>><?php echo $item->title; ?></option>
			<?php endforeach; ?>
		</select>
		<?php else : ?>
		<span><?php echo esc_html__( 'No autoresponders. Please click on refresh link to update.', 'wp_sendsmaily' ); ?></span>
		<?php endif; ?>
	</div>
</div>

<div id="content-advanced" class="tab-content hidden">
	<div class="wrap">
		<label><?php echo esc_html__( 'Newsletter subscription form', 'wp_sendsmaily' ); ?> <a href="#" onclick="javascript:Default.resetForm();return false;" title="<?php echo esc_html__( 'Restore original subscription form', 'wp_sendsmaily' ); ?>">(<?php echo esc_html__( 'Regenerate', 'wp_sendsmaily' ); ?>)</a></label>
		<em><?php echo esc_html__( 'HTML of subscription form', 'wp_sendsmaily' ); ?></em>
		<textarea id="advanced-form" name="advanced[form]" rows="15"><?php echo stripslashes( $this->form ); ?></textarea>
	</div>
</div>

<p style="font-size:10px"><?php echo esc_html__( 'Note: When you save under Basic tab, default form will be used.', 'wp_sendsmaily' ); ?></p>

<div class="wrap">
	<input type="button" value="<?php echo esc_html__( 'Save changes', 'wp_sendsmaily' ); ?>" name="Submit" class="button-primary" onclick="javascript:Default.save();return false;" />
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
