<div>
	<input type="hidden" name="op" value="save" />
	<input type="hidden" name="is_advanced" value="0" />
</div>

<?php if ( $this->has_credentials ) : ?>
<p>
	<span><?php echo esc_html__( 'Your API credentials are valid', 'smaily-for-wp' ); ?></span>
	<a href="#" onclick="javascript:Default.removeApiKey();return false;"><strong><?php echo esc_html__( 'Remove', 'smaily-for-wp' ); ?></strong><img src="<?php echo SMLY4WP_PLUGIN_URL; ?>/gfx/remove.png" alt="<?php echo esc_html__( 'Remove', 'smaily-for-wp' ); ?>" title="<?php echo esc_html__( 'Remove', 'smaily-for-wp' ); ?>" /></a>
</p>
<?php else : ?>
<div>
<table class="form-table">
	<tbody>
	<tr class="form-field">
		<th><?php echo esc_html__( 'Subdomain', 'smaily-for-wp' ); ?></th>
		<td>
			<input type="text" class="regular-text" name="subdomain" style="max-width:50%;"/>
			<small class="form-text text-muted" style="display:block;">
				For example <strong>"demo"</strong> from https://<strong>demo</strong>.sendsmaily.net/
			</small>
		</td>

	</tr>
	<tr class="form-field">
		<th><?php echo esc_html__( 'API username', 'smaily-for-wp' ); ?></th>
		<td><input type="text" class="regular-text" name="username" style="max-width:50%;"/></td>
	</tr>
	<tr class="form-field">
		<th><?php echo esc_html__( 'API password', 'smaily-for-wp' ); ?></th>
		<td>
			<input type="password" class="regular-text" name="password" style="max-width:50%;"/>
			<small class="form-text text-muted" style="display:block;">
				<a href="http://help.smaily.com/en/support/solutions/articles/16000062943-create-api-user">
					<?php echo esc_html__( 'How to create API credentials?', 'smaily-for-wp' ); ?>
				</a>
			</small>
		</td>
	</tr>
	</tbody>
</table>
	<input type="button" value="<?php echo esc_html__( 'Check', 'smaily-for-wp' ); ?>" name="Submit" class="button-primary" onclick="javascript:Default.validateApiKey();return false;" />
</div>
<?php endif; ?>

<?php if ( $this->has_credentials ) : ?>
<ul class="tabs">
	<li><a id="link-basic" href="#basic"<?php if ( $this->form_options['is_advanced'] === false): ?> class="selected"<?php endif; ?>><?php echo esc_html__( 'Basic', 'smaily-for-wp' ); ?></a></li>
	<li><a id="link-advanced" href="#advanced"<?php if ( $this->form_options['is_advanced'] === true): ?> class="selected"<?php endif; ?>><?php echo esc_html__( 'Advanced', 'smaily-for-wp' ); ?></a></li>
</ul>
<div class="clear"></div>

<div id="content-advanced" class="tab-content<?php if ( $this->form_options['is_advanced'] === false): ?> hidden<?php endif; ?>">
	<div class="wrap">
		<label><?php echo esc_html__( 'Newsletter subscription form', 'smaily-for-wp' ); ?> <a href="#" onclick="javascript:Default.resetForm();return false;" title="<?php echo esc_html__( 'Restore original subscription form', 'smaily-for-wp' ); ?>">(<?php echo esc_html__( 'Regenerate', 'smaily-for-wp' ); ?>)</a></label>
		<em><?php echo esc_html__( 'HTML of subscription form', 'smaily-for-wp' ); ?></em>
		<textarea id="advanced-form" name="form" rows="15"><?php echo $this->form_options['form']; ?></textarea>
	</div>
</div>

<p style="font-size:10px"><?php echo esc_html__( 'Note: When you save under Basic tab, default form will be used.', 'smaily-for-wp' ); ?></p>

<div class="wrap">
	<input type="button" value="<?php echo esc_html__( 'Save changes', 'smaily-for-wp' ); ?>" name="Submit" class="button-primary" onclick="javascript:Default.save();return false;" />
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
