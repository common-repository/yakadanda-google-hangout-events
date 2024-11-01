<div id="google-hangout-event-settings" class="wrap">
  <div id="icon-edit" class="icon32 icon32-posts-quote"><br></div>
  <h1><?php _e('Settings', 'yakadanda-google-hangout-events'); ?></h1>
  <?php if ($message): ?>
    <div id="googleplushangoutevent-msg" class="<?php echo $message['class']; ?>">
      <p><?php echo $message['msg']; ?></p>
    </div>
  <?php if (isset($message['cookie'])) setcookie('googleplushangoutevent_message', null, time()-1, '/'); endif; ?>
  <div class="google-web-starter-kit">
    <form method="post" action="">
      <input type="hidden" name="update_settings" value="1" />
      <div id="preference_tabs">
        <ul>
          <li><a href="#preference-tabs-1"><?php _e('Setup', 'yakadanda-google-hangout-events'); ?></a></li>
          <li><a href="#preference-tabs-2"><?php _e('Widget', 'yakadanda-google-hangout-events'); ?></a></li>
          <li><a href="#preference-tabs-3"><?php _e('Cache', 'yakadanda-google-hangout-events'); ?></a></li>
        </ul>
        <div id="preference-tabs-1">
          <p><?php $url = 'https://console.developers.google.com/project'; echo sprintf(__('Get your api key, client id, and client secret at <a href="%s" target="_blank" class="cta">Google Developers Console</a>. For more details see <a id="googleplushangoutevent-help-tab" href="#">Setup section</a> on Help tab above.', 'yakadanda-google-hangout-events'), $url); ?></p>
          <table class="form-table">
            <tr valign="top">
              <th scope="row"><label for="calendar_id"><?php _e('Calendar ID', 'yakadanda-google-hangout-events'); ?></label></th>
              <td><input name="calendar_id" type="text" id="calendar_id" value="<?php echo isset($data['calendar_id']) ? $data['calendar_id'] : null; ?>" class="regular-text" />&nbsp;
                <?php if (get_option('yakadanda_googleplus_hangout_event_access_token')): ?>
                  <strong class="color--green"><?php _e('Connected.', 'yakadanda-google-hangout-events'); ?></strong>
                <?php else: ?>
                  <strong class="color--red"><?php _e('Not Connected.', 'yakadanda-google-hangout-events'); ?></strong>
                <?php endif; ?>
                <p class="description"><?php _e('Calendar identifier, e.g. your_name@gmail.com'); ?></p></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="api_key"><?php _e('API Key', 'yakadanda-google-hangout-events'); ?></label></th>
              <td><input name="api_key" type="text" id="api_key" value="<?php echo isset($data['api_key']) ? $data['api_key'] : null; ?>" class="regular-text" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="client_id"><?php _e('Client ID', 'yakadanda-google-hangout-events'); ?></label></th>
              <td><input name="client_id" type="text" id="client_id" value="<?php echo isset($data['client_id']) ? $data['client_id'] : null; ?>" class="regular-text" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="client_secret"><?php _e('Client Secret', 'yakadanda-google-hangout-events'); ?></label></th>
              <td><input name="client_secret" type="text" id="client_secret" value="<?php echo isset($data['client_secret']) ? $data['client_secret'] : null; ?>" class="regular-text" /></td>
            </tr>
          </table>
        </div>
        <div id="preference-tabs-2">
          <table class="form-table">
            <tr valign="top">
              <th scope="row"><label for="widget_border"><?php _e('Widget Border', 'yakadanda-google-hangout-events'); ?></label></th>
              <td><input name="widget_border" type="text" id="event_border" value="<?php echo $data['widget_border']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['widget_border']); ?>;"/></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="widget_background"><?php _e('Widget Background', 'yakadanda-google-hangout-events'); ?></label></th>
              <td><input name="widget_background" type="text" id="widget_background" value="<?php echo $data['widget_background']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['widget_background']); ?>;"/></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="title_color"><?php _e('Title', 'yakadanda-google-hangout-events'); ?></label></th>
              <td>
                <input name="title_color" type="text" id="title_color" value="<?php echo $data['title_color']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['title_color']); ?>;"/>
                <?php
                  googleplushangoutevent_font_themes('title_theme', $data['title_theme']);
                  googleplushangoutevent_font_sizes('title_size', $data['title_size']);
                  googleplushangoutevent_font_styles('title_style', $data['title_style']);
                ?>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="date_color"><?php _e('Date', 'yakadanda-google-hangout-events'); ?></label></th>
              <td>
                <input name="date_color" type="text" id="date_color" value="<?php echo $data['date_color']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['date_color']); ?>;"/>
                <?php
                  googleplushangoutevent_font_themes('date_theme', $data['date_theme']);
                  googleplushangoutevent_font_sizes('date_size', $data['date_size']);
                  googleplushangoutevent_font_styles('date_style', $data['date_style']);
                ?>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="detail_color"><?php _e('Main Text', 'yakadanda-google-hangout-events'); ?></label></th>
              <td>
                <input name="detail_color" type="text" id="detail_color" value="<?php echo $data['detail_color']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['detail_color']); ?>;"/>
                <?php
                  googleplushangoutevent_font_themes('detail_theme', $data['detail_theme']);
                  googleplushangoutevent_font_sizes('detail_size', $data['detail_size']);
                  googleplushangoutevent_font_styles('detail_style', $data['detail_style']);
                ?>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="icon_border"><?php _e('Buttons Border', 'yakadanda-google-hangout-events'); ?></label></th>
              <td><input name="icon_border" type="text" id="icon_border" value="<?php echo $data['icon_border']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['icon_border']); ?>;"/></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="icon_background"><?php _e('Buttons Background', 'yakadanda-google-hangout-events'); ?></label></th>
              <td><input name="icon_background" type="text" id="icon_background" value="<?php echo $data['icon_background']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['icon_background']); ?>;"/></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="icon_hover"><?php _e('Buttons Hover', 'yakadanda-google-hangout-events'); ?></label></th>
              <td><input name="icon_hover" type="text" id="icon_hover" value="<?php echo $data['icon_hover']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['icon_hover']); ?>;"/></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="icon_color"><?php _e('Buttons Text', 'yakadanda-google-hangout-events'); ?></label></th>
              <td>
                <input name="icon_color" type="text" id="icon_color" value="<?php echo $data['icon_color']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['icon_color']); ?>;"/>
                <?php
                  googleplushangoutevent_font_themes('icon_theme', $data['icon_theme']);
                  googleplushangoutevent_font_sizes('icon_size', $data['icon_size']);
                  googleplushangoutevent_font_styles('icon_style', $data['icon_style']);
                ?>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="countdown_background"><?php _e('Countdown Background', 'yakadanda-google-hangout-events'); ?></label></th>
              <td><input name="countdown_background" type="text" id="countdown_background" value="<?php echo $data['countdown_background']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['countdown_background']); ?>;"/></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="countdown_color"><?php _e('Countdown Text', 'yakadanda-google-hangout-events'); ?></label></th>
              <td>
                <input name="countdown_color" type="text" id="countdown_color" value="<?php echo $data['countdown_color']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['countdown_color']); ?>;"/>
                <?php
                  googleplushangoutevent_font_themes('countdown_theme', $data['countdown_theme']);
                  googleplushangoutevent_font_sizes('countdown_size', $data['countdown_size']);
                  googleplushangoutevent_font_styles('countdown_style', $data['countdown_style']);
                ?>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="event_button_background"><?php _e('View Event Button Background', 'yakadanda-google-hangout-events'); ?></label></th>
              <td><input name="event_button_background" type="text" id="event_button_background" value="<?php echo $data['event_button_background']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['event_button_background']); ?>;"/></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="event_button_hover"><?php _e('View Event Button Hover', 'yakadanda-google-hangout-events'); ?></label></th>
              <td><input name="event_button_hover" type="text" id="event_button_hover" value="<?php echo $data['event_button_hover']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['event_button_hover']); ?>;"/></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="event_button_color"><?php _e('View Event Button Text', 'yakadanda-google-hangout-events'); ?></label></th>
              <td>
                <input name="event_button_color" type="text" id="event_button_color" value="<?php echo $data['event_button_color']; ?>" class="regular-text iris-color-picker" style="<?php echo googleplushangoutevent_bg_cl($data['event_button_color']); ?>;"/>
                <?php
                  googleplushangoutevent_font_themes('event_button_theme', $data['event_button_theme']);
                  googleplushangoutevent_font_sizes('event_button_size', $data['event_button_size']);
                  googleplushangoutevent_font_styles('event_button_style', $data['event_button_style']);
                ?>
              </td>
            </tr>
          </table>
        </div>
        <div id="preference-tabs-3">
          <p><?php _e('Set cache system with expiration time.', 'yakadanda-google-hangout-events'); ?></p>
          <table class="form-table">
            
            <tr valign="top">
              <th scope="row"><?php _e('Transient' ,'yakadanda-instagram'); ?></th>
              <td>
                <fieldset>
                  <legend class="screen-reader-text">
                    <span><?php _e('Transient', 'yakadanda-google-hangout-events'); ?></span>
                  </legend>
                  <label title="On">
                    <input type="radio" value="on" name="transient" <?php echo ($data['transient'] == 'on') ? 'checked="checked"' : null; ?>>
                    <span><?php _e('On', 'yakadanda-google-hangout-events'); ?></span>
                  </label>
                  <br>
                  <label title="Off">
                    <input type="radio" value="off" name="transient" <?php echo ($data['transient'] == 'off') ? 'checked="checked"' : null; ?>>
                    <span><?php _e('Off', 'yakadanda-google-hangout-events'); ?></span>
                  </label>
                </fieldset>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="expiration"><?php _e('Expiration', 'yakadanda-google-hangout-events'); ?></label></th>
              <td><input name="expiration" type="number" min="1" max="1440" id="expiration" value="<?php echo isset($data['expiration']) ? $data['expiration'] : 1; ?>" class="regular-text" <?php echo ($data['transient']) ? null : 'readonly'; ?>/>
              <p class="description"><?php _e('The maximum of minutes to keep the data before refreshing.'); ?></p></td>
            </tr>
          </table>
        </div>
      </div>
      <p class="submit">
        <?php if ( get_option('yakadanda_googleplus_hangout_event_access_token') ): ?>
          <input id="submit" class="button--primary" type="submit" value="<?php _e('Save Changes', 'yakadanda-google-hangout-events'); ?>" name="submit">&nbsp;
          <input id="googleplushangoutevent-logout" class="button--primary" type="button" value="<?php _e('Logout', 'yakadanda-google-hangout-events'); ?>" name="googleplushangoutevent-logout">&nbsp;
        <?php else: ?>
          <input id="submit" class="button--primary" type="submit" value="<?php _e('Save and Connect', 'yakadanda-google-hangout-events'); ?>" name="submit">&nbsp;
        <?php endif; ?>
        <input id="googleplushangoutevent-restore-settings" class="button--primary" type="button" value="<?php _e('Reset', 'yakadanda-google-hangout-events'); ?>" name="googleplushangoutevent-restore-settings">
      </p>
    </form>
  </div>
</div>
<div id="googleplushangoutevent-dialog-confirm" title="Confirmation" style="display: none;">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php _e('These will also disconnect from Google API if connected. Are you sure?', 'yakadanda-google-hangout-events'); ?></p>
</div>
