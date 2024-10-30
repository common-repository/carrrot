<div class="wrap">
    <h2>Carrrot</h2>
    <div id="message"
         class="updated notice is-dismissible" <?php if (!isset($_REQUEST['carrrot_plugin_form_submit']) || $message == "") echo "style=\"display:none\""; ?>>
        <p><?php echo $message; ?></p>
    </div>
    <div class="notice notice-info">
        <p><?php echo __('You can look up parameters "API Key", "API Secret" and "User Auth Key" in "Settings" section of your Carrrot account administrative panel', 'carrrot'); ?></p>
    </div>
    <form method="post" action="plugins.php?page=carrrot">
        <?php wp_nonce_field('carrrot_plugin_settings', 'carrrot_plugin_nonce'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php echo __('API Key', 'carrrot'); ?></th>
                <td><input type="text" class="regular-text code" name="carrrot_api_key"
                           value="<?php echo get_option('carrrot_api_key'); ?>"/></td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __('API Secret', 'carrrot'); ?></th>
                <td><input type="text" class="regular-text code" name="carrrot_api_secret"
                           value="<?php echo get_option('carrrot_api_secret'); ?>"/></td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php echo __('User Auth Key', 'carrrot'); ?></th>
                <td><input type="text" class="regular-text code" name="carrrot_auth_key"
                           value="<?php echo get_option('carrrot_auth_key'); ?>"/></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo __('User authorization', 'carrrot'); ?></th>
                <td>
                    <input type="checkbox" name="carrrot_auth"
                           <?php echo get_option('carrrot_auth') ? 'checked' : ''; ?>/>
                    <label
                        for="carrrot_auth"><?php echo __('Send customer ID to Carrrot as User ID', 'carrrot'); ?></label>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input type="hidden" name="carrrot_plugin_form_submit" value="submit"/>
            <input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>"/>
        </p>

    </form>
</div>
