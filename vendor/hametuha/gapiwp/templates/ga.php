<?php /** @var \Hametuha\GapiWP\Gapi $this */ ?>
<div class="wrap">
	<h2>
		<i class="dashicons dashicons-dashboard" style="font-size: 30px; height: 30px; width: 30px;"></i> <?php _e('Google Analytics設定', 'gapiwp'); ?>
	</h2>

	<form action="<?php echo admin_url('options-general.php?page=gapiwp-analytics') ?>" method="post">
		<?php wp_nonce_field('ga_token_update') ?>
		<table class="form-table">
			<tr>
				<th><label for="consumer_key"><?php _e('クライアントID', 'gapiwp') ?></label></th>
				<td>
					<input type="text" class="regular-text" name="consumer_key" id="consumer_key" value="<?php echo esc_attr($this->consumer_key) ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="consumer_key"><?php _e('コンシューマーシークレット', 'gapiwp') ?></label></th>
				<td>
					<input type="text" class="regular-text" name="consumer_secret" id="consumer_secret" value="<?php echo esc_attr($this->consumer_secret) ?>" />
				</td>
			</tr>
			<tr>
				<th>コールバックURL</th>
				<td>
					<input type="text" class="regular-text" readonly value="<?php echo esc_attr(admin_url('options-general.php?page=gapiwp-analytics')) ?>" />
					<p class="description"><?php printf(__('<a href="%s">Google API Console</a>で設定しておいてください。', 'gapiwp'), 'https://console.developers.google.com/project') ?></p>
				</td>
			</tr>
			<tr>
				<th>トークン</th>
				<td>
<pre class="token-display">
<?php echo esc_html($this->token) ?>
</pre>
				</td>
			</tr>
		</table>
		<?php submit_button(__('トークンを取得', '')) ?>
	</form>

<?php if( $this->ga ): ?>
	<hr />
	<h2><?php _e('サイトの設定', 'gapiwp') ?></h2>
	<p><?php _e('統計データを利用するアカウントを選んでください。', 'gapiwp') ?></p>

	<?php
		$accounts = $this->get_accounts();
		if( !$accounts ):
	?>
		<div class="error"><p><?php _e('アカウントを取得できませんでした。', 'gapiwp') ?></p></div>
	<?php else: ?>

		<form action="<?php echo admin_url('options-general.php?page=gapiwp-analytics') ?>" method="post">
			<?php wp_nonce_field('ga_account_save') ?>
			<?php foreach( $accounts as $account ): ?>
			<fieldset>
				<legend><?php printf('%s<small>%s</small>', esc_html($account->name), $account->id) ?></legend>

				<dl>
					<?php foreach( $this->get_properties($account->id) as $profile ): ?>
					<dt><?php printf('%s<small>%s</small>', esc_html($profile->name), $profile->id) ?></dt>
					<dd>
						<?php foreach( $this->get_views($account->id, $profile->id) as $view ): ?>
							<label>
								<input type="radio" name="view" value="<?php echo esc_attr($view->id) ?>"<?php checked($view->id == $this->view_id) ?>> <?php echo esc_html($view->name) ?>
							</label>
						<?php endforeach; ?>
					</dd>
					<?php endforeach; ?>
				</dl>


			</fieldset>
			<?php endforeach; ?>
			<?php submit_button(__('保存', 'gapiwp')) ?>
		</form>
	<?php endif; ?>

<?php endif; ?>

</div>
 