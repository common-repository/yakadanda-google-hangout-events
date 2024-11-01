<?php
/**
 * Google+ Event
 */
add_action( 'widgets_init', create_function( '', 'register_widget( "googlePlusEvent" );' ) );
class googlePlusEvent extends WP_Widget {
  //Register widget with WordPress.
  public function __construct() {
    parent::__construct(
      'googleplus_events', // Base ID
      'Google+ Event', // Name
      array('description' => __('A countdown function to time of the Google+ Event', 'yakadanda-google-hangout-events'),) // Args
    );
  }
  
  // Front-end display of widget.
  public function widget( $args, $instance ) {
    // Enqueue scripts
    googleplushangoutevent_wp_enqueue_scripts_load();
    
    $data = googleplushangoutevent_get_settings();
    $token = get_option('yakadanda_googleplus_hangout_event_access_token');

    $data['transient'] = ($data['transient'] == 'on') ? true : false;

    $instance['timezone'] = isset($instance['timezone']) ? $instance['timezone'] : null;

    $transient_name = md5('special_query_event_widget_' . $instance['timezone'] . $token);
    if ( (false === ( $special_query_event_widget = get_transient($transient_name) )) && $token && $data['transient'] ) {
      $special_query_event_widget = googleplushangoutevent_response(null, null, null, $instance['timezone']);

      if ( !empty($special_query_event_widget) ) { set_transient($transient_name, $special_query_event_widget, 60 * $data['expiration']); }
    }
    if (!$token) {
      delete_transient($transient_name);
    }
    $events = ($data['transient']) ? $special_query_event_widget : googleplushangoutevent_response(null, null, null, $instance['timezone']);

    // sorting
    if ($events) { uasort( $events , 'googleplushangoutevent_sort_events_asc' ); }
    
    $i = 0;
    $display = isset( $instance['display'] ) ? $instance['display'] : 1;
    $creator = 1;
    $author = isset($instance['author']) ? $instance['author'] : 'all';
    $countdown = isset($instance['countdown']) ? $instance['countdown'] : 'first';
    $visibility = isset($instance['visibility']) ? $instance['visibility'] : 'public';
    $source = empty($instance['src']) ? 'all' : $instance['src'];
    
    $http_status = isset($events['error']['code']) ? $events['error']['code'] : null;
    
    extract( $args );
    $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? null : $instance['title'], $instance, $this->id_base );
    
    echo $before_widget;
    if ( ! empty( $title ) ) echo $before_title . $title . $after_title;
    
    ?>
      <div id="ghe-event-widget">
        <?php if ($events && !$http_status):
          $is_countdown = ($countdown == 'none') ? false : true;
          $src_filter = true;
        ?>
          <?php foreach ( $events as $event ):
            $hangoutlink = isset($event['hangoutLink']) ? $event['hangoutLink'] : false;

            $event['visibility'] = isset($event['visibility']) ? $event['visibility'] : 'public';
            $event['visibility'] = ($visibility == "all") ? 'all' : $event['visibility'];

            switch($author) {
              case 'self':
                if ( isset($event["\0*\0modelData"]['creator']['self']) )
                  $creator = $event["\0*\0modelData"]['creator']['self'];
                else
                  $creator = ($event["\0*\0modelData"]['creator']['email'] == $data['calendar_id']) ? 1 : 0;
                break;
              case 'other':
                if ( isset($event["\0*\0modelData"]['creator']['self']) )
                  $creator = !$event["\0*\0modelData"]['creator']['self'];
                else
                  $creator = ($event["\0*\0modelData"]['creator']['email'] == $data['calendar_id']) ? 0 : 1;
                break;
            }
            
            if ($source != 'all') $src_filter = googleplushangoutevent_src_filter($source, $event['htmlLink']);
            
            if ( !$hangoutlink && $creator && ($visibility == $event['visibility']) && $src_filter ):
              $timezone = isset($event['timeZoneLocation']) ? $event['timeZoneLocation'] : $event['timeZoneCalendar'];
              $timezone = ($instance['timezone']) ? $instance['timezone'] : $timezone;
              
              $start = (array) $event["\0*\0modelData"]['start'];
              $end = (array) $event["\0*\0modelData"]['end'];
              $start_event = isset($start['dateTime']) ? $start['dateTime'] : $start['date'];
              $end_event = isset($end['dateTime']) ? $end['dateTime'] : $end['date'];
              
              $time = googleplushangoutevent_start_time($start_event, $timezone);
          ?>
            <div itemscope itemtype="http://data-vocabulary.org/Event" class="ghe-vessel">
              <h4 itemprop="summary" class="ghe-title"><?php echo $event['summary']; ?></h4>
              <div class="ghe-time"><?php echo googleplushangoutevent_time($start_event, $end_event, $timezone, 'widget'); ?></div>
              <div itemprop="description" class="ghe-detail"><?php echo isset($event['description']) ? nl2br( $event['description'] ) : null; ?></div>
              
              <ul class="ghe-icons">
                <li><a href="<?php echo $event['htmlLink'] ?>" target="_blank"><?php _e('Event', 'yakadanda-google-hangout-events'); ?></a></li>
              </ul>
              
              <?php if ($is_countdown): ?>
                <div id="<?php echo uniqid(); ?>" class="ghe-countdown fix" data-cdate="<?php echo $time; ?>"><?php echo $time; ?></div>
              <?php endif; ?>
              
              <div class="ghe-button"><a itemprop="url" href="<?php echo $event['htmlLink'] ?>" target="_blank"><?php _e('View Event on Google+', 'yakadanda-google-hangout-events'); ?></a></div>
            </div>
            
            <?php if ( ($countdown == 'first') && ($i==0) ) $is_countdown = false; ?>
            <?php $i++; if ( $i == $display ) break; ?>
            
          <?php endif; endforeach; ?>
        <?php endif; ?>

        <?php if ($i == 0): ?>
          <div class="ghe-vessel"><p><?php echo googleplushangoutevent_get_widget_message($events, 'normal'); ?></p></div>
        <?php endif; ?>
      </div>
      
    <?php
    
    echo $after_widget;
	}
  
  // Sanitize widget form values as they are saved.
  public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['author'] = strip_tags($new_instance['author']);
    $instance['src'] = strip_tags($new_instance['src']);
    $instance['display'] = strip_tags($new_instance['display']);
    $instance['visibility'] = strip_tags($new_instance['visibility']);
    $instance['countdown'] = strip_tags($new_instance['countdown']);
    $instance['timezone'] = strip_tags($new_instance['timezone']);
    return $instance;
  }
  
  // Back-end widget form.
  public function form( $instance ) {
    if ( isset( $instance[ 'title' ] ) ) $title = $instance[ 'title' ];
    $author = isset( $instance[ 'author' ] ) ? $instance[ 'author' ] : null;
    $src = isset( $instance[ 'src' ] ) ? $instance[ 'src' ] : null;
    $display = isset( $instance[ 'display' ] ) ? $instance[ 'display' ] : null;
    $visibility = isset( $instance[ 'visibility' ] ) ? $instance[ 'visibility' ] : null;
    $countdown = isset( $instance[ 'countdown' ] ) ? $instance[ 'countdown' ] : null;
    $timezone = isset( $instance[ 'timezone' ] ) ? $instance[ 'timezone' ] : null;
    ?>
      <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'yakadanda-google-hangout-events'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo isset($title) ? esc_attr($title): null; ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('author'); ?>"><?php _e('Author:', 'yakadanda-google-hangout-events'); ?></label><br/>
        <select id="<?php echo $this->get_field_id('author'); ?>" name="<?php echo $this->get_field_name('author'); ?>">
          <option value="all" <?php echo ($author == 'all') ? 'selected="selected"': null; ?>><?php _e('All', 'yakadanda-google-hangout-events'); ?>&nbsp;</option>
          <option value="self" <?php echo ($author == 'self') ? 'selected="selected"': null; ?>><?php _e('Self', 'yakadanda-google-hangout-events'); ?>&nbsp;</option>
          <option value="other" <?php echo ($author == 'other') ? 'selected="selected"': null; ?>><?php _e('Other', 'yakadanda-google-hangout-events'); ?>&nbsp;</option>
        </select>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('src'); ?>"><?php _e('Source:', 'yakadanda-google-hangout-events'); ?></label><br/>
        <select id="<?php echo $this->get_field_id('src'); ?>" name="<?php echo $this->get_field_name( 'src' ); ?>">
          <option value="all" <?php echo ($src == 'all') ? 'selected="selected"': null; ?>><?php _e('All', 'yakadanda-google-hangout-events'); ?>&nbsp;</option>
          <option value="gcal" <?php echo ($src == 'gcal') ? 'selected="selected"': null; ?>><?php _e('Google Calendar', 'yakadanda-google-hangout-events'); ?>&nbsp;</option>
          <option value="gplus" <?php echo ($src == 'gplus') ? 'selected="selected"': null; ?>>Google+&nbsp;</option>
        </select>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('display'); ?>"><?php _e('Display:', 'yakadanda-google-hangout-events'); ?></label><br/>
        <select id="<?php echo $this->get_field_id('display'); ?>" name="<?php echo $this->get_field_name('display'); ?>">
          <option value="1" <?php echo ($display == 1) ? 'selected="selected"': null; ?>>1&nbsp;</option>
          <option value="2" <?php echo ($display == 2) ? 'selected="selected"': null; ?>>2&nbsp;</option>
          <option value="3" <?php echo ($display == 3) ? 'selected="selected"': null; ?>>3&nbsp;</option>
          <option value="4" <?php echo ($display == 4) ? 'selected="selected"': null; ?>>4&nbsp;</option>
          <option value="5" <?php echo ($display == 5) ? 'selected="selected"': null; ?>>5&nbsp;</option>
        </select>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('visibility'); ?>"><?php _e('Visibility:', 'yakadanda-google-hangout-events'); ?></label><br/>
        <select id="<?php echo $this->get_field_id('visibility'); ?>" name="<?php echo $this->get_field_name('visibility'); ?>">
          <option value="public" <?php echo ($visibility == "public") ? 'selected="selected"': null; ?>>Public&nbsp;</option>
          <option value="private" <?php echo ($visibility == "private") ? 'selected="selected"': null; ?>>Private&nbsp;</option>
          <option value="all" <?php echo ($visibility == "all") ? 'selected="selected"': null; ?>>All&nbsp;</option>
        </select>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('countdown'); ?>"><?php _e('Countdown:', 'yakadanda-google-hangout-events'); ?></label><br/>
        <label title="Display countdown clock on first only">
          <input type="radio" value="first" name="<?php echo $this->get_field_name('countdown'); ?>" <?php echo ( ($countdown == 'first') || empty($countdown) ) ? 'checked="checked"' : null; ?>>
          <span><?php _e('First only', 'yakadanda-google-hangout-events'); ?></span>
        </label>
        <br/>
        <label title="Display countdown clock on all">
          <input type="radio" value="all" name="<?php echo $this->get_field_name('countdown'); ?>" <?php echo ($countdown == 'all') ? 'checked="checked"' : null; ?>>
          <span><?php _e('All', 'yakadanda-google-hangout-events'); ?></span>
        </label>
        <br/>
        <label title="Display countdown clock on none">
          <input type="radio" value="none" name="<?php echo $this->get_field_name('countdown'); ?>" <?php echo ($countdown == 'none') ? 'checked="checked"' : null; ?>>
          <span><?php _e('None', 'yakadanda-google-hangout-events'); ?></span>
        </label>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('timezone'); ?>"><?php _e('Timezone:', 'yakadanda-google-hangout-events'); ?></label><br/>
        <select id="<?php echo $this->get_field_id('timezone'); ?>" name="<?php echo $this->get_field_name('timezone'); ?>" style="width: 100%">
          <?php googleplushangoutevent_timezone_options($timezone); ?>
        </select>
      </p>
    <?php
  }
  
}/* end of googlePlusHangoutEvents class */

/**
 * Google+ Hangout
 */
add_action( 'widgets_init', create_function( '', 'register_widget( "googlePlusHangout" );' ) );
class googlePlusHangout extends WP_Widget {
  //Register widget with WordPress.
  public function __construct() {
    parent::__construct(
      'googleplus_hangout_events', // Base ID
      'Google+ Hangout', // Name
      array('description' => __('A countdown function to time of the Google+ Hangout', 'yakadanda-google-hangout-events'),) // Args
    );
  }
  
  // Front-end display of widget.
  public function widget( $args, $instance ) {
    // Enqueue scripts
    googleplushangoutevent_wp_enqueue_scripts_load();

    $data = googleplushangoutevent_get_settings();
    $token = get_option('yakadanda_googleplus_hangout_event_access_token');
    
    $data['transient'] = ($data['transient'] == 'on') ? true : false;
    
    $instance['timezone'] = isset($instance['timezone']) ? $instance['timezone'] : null;

    $transient_name = md5('special_query_hangout_widget_' . $instance['timezone'] . $token);
    if ( (false === ( $special_query_hangout_widget = get_transient($transient_name) )) && $token && $data['transient'] ) {
      $special_query_hangout_widget = googleplushangoutevent_response(null, null, null, $instance['timezone']);

      if ( !empty($special_query_hangout_widget) ) { set_transient($transient_name, $special_query_hangout_widget, 60 * $data['expiration']); }
    }
    if (!$token) {
      delete_transient($transient_name);
    }
    $events = ($data['transient']) ? $special_query_hangout_widget : googleplushangoutevent_response(null, null, null, $instance['timezone']);

    // sorting
    if ($events) { uasort( $events , 'googleplushangoutevent_sort_events_asc' ); }
    
    $i = 0;
    $display = isset( $instance['display'] ) ? $instance['display'] : 1;
    $creator = 1;
    $author = isset($instance['author']) ? $instance['author'] : 'all';
    $countdown = isset($instance['countdown']) ? $instance['countdown'] : 'first';
    $visibility = isset($instance['visibility']) ? $instance['visibility'] : 'public';
    
    $http_status = isset($events['error']['code']) ? $events['error']['code'] : null;
    
    extract( $args );
    $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? null : $instance['title'], $instance, $this->id_base );
    
    echo $before_widget;
    if ( ! empty( $title ) ) echo $before_title . $title . $after_title;
    
    ?>
      <div id="ghe-hangout-widget">
        <?php if ($events && !$http_status):
          $is_countdown = ($countdown == 'none') ? false : true;
        ?>
          <?php foreach ( $events as $event ):
            $hangoutlink = isset($event['hangoutLink']) ? $event['hangoutLink'] : false;

            $event['visibility'] = isset($event['visibility']) ? $event['visibility'] : 'public';
            $event['visibility'] = ($visibility == "all") ? 'all' : $event['visibility'];
            
            switch($author) {
              case 'self':
                if ( isset($event["\0*\0modelData"]['creator']['self']) )
                  $creator = $event["\0*\0modelData"]['creator']['self'];
                else
                  $creator = ($event["\0*\0modelData"]['creator']['email'] == $data['calendar_id']) ? 1 : 0;
                break;
              case 'other':
                if ( isset($event["\0*\0modelData"]['creator']['self']) )
                  $creator = !$event["\0*\0modelData"]['creator']['self'];
                else
                  $creator = ($event["\0*\0modelData"]['creator']['email'] == $data['calendar_id']) ? 0 : 1;
                break;
            }
            
            if ( $hangoutlink && $creator && ($visibility == $event['visibility']) ):
              $timezone = isset($event['timeZoneLocation']) ? $event['timeZoneLocation'] : $event['timeZoneCalendar'];
              $timezone = ($instance['timezone']) ? $instance['timezone'] : $timezone;
              
              $start = (array) $event["\0*\0modelData"]['start'];
              $end = (array) $event["\0*\0modelData"]['end'];
              $start_event = isset($start['dateTime']) ? $start['dateTime'] : $start['date'];
              $end_event = isset($end['dateTime']) ? $end['dateTime'] : $end['date'];
              
              $time = googleplushangoutevent_start_time($start_event, $timezone);
              
              $onair = googleplushangoutevent_onair($start['dateTime'], $end['dateTime']);
            ?>
            <div itemscope itemtype="http://data-vocabulary.org/Event" class="ghe-vessel">
              <h4 itemprop="summary" class="ghe-title"><?php echo $event['summary']; ?></h4>
              <div class="ghe-time"><?php echo googleplushangoutevent_time($start_event, $end_event, $timezone, 'widget'); ?></div>
              <div itemprop="description" class="ghe-detail"><?php echo nl2br( $event['description'] ); ?></div>
              
              <ul class="ghe-icons">
                <li><a href="<?php echo $event['htmlLink'] ?>" target="_blank"><?php _e('Event', 'yakadanda-google-hangout-events'); ?></a></li>
                <li><a href="<?php echo $event['htmlLink'] ?>" target="_blank"><?php _e('Hangout', 'yakadanda-google-hangout-events'); ?></a></li>
                <?php if ($onair): ?>
                  <li><a href="<?php echo $event['hangoutLink'] ?>" target="_blank"><?php _e('On Air', 'yakadanda-google-hangout-events'); ?></a></li>
                <?php endif; ?>
              </ul>
              
              <?php if ($is_countdown): ?>
                <div id="<?php echo uniqid(); ?>" class="ghe-countdown fix" data-cdate="<?php echo $time; ?>"><?php echo $time; ?></div>
              <?php endif; ?>
              
              <div class="ghe-button"><a itemprop="url" href="<?php echo $event['htmlLink'] ?>" target="_blank"><?php _e('View Event on Google+', 'yakadanda-google-hangout-events'); ?></a></div>
            </div>
            
            <?php if ( ($countdown == 'first') && ($i==0) ) $is_countdown = false; ?>
            <?php $i++; if ( $i == $display ) break; ?>
            
          <?php endif; endforeach; ?>
        <?php endif; ?>

        <?php if ($i == 0): ?>
          <div class="ghe-vessel"><p><?php echo googleplushangoutevent_get_widget_message($events, 'hangout'); ?></p></div>
        <?php endif; ?>
      </div>
      
    <?php
    
    echo $after_widget;
	}
  
  // Sanitize widget form values as they are saved.
  public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['author'] = strip_tags($new_instance['author']);
    $instance['display'] = strip_tags($new_instance['display']);
    $instance['visibility'] = strip_tags($new_instance['visibility']);
    $instance['countdown'] = strip_tags($new_instance['countdown']);
    $instance['timezone'] = strip_tags($new_instance['timezone']);
    return $instance;
  }
  
  // Back-end widget form.
  public function form( $instance ) {
    if ( isset( $instance[ 'title' ] ) ) $title = $instance[ 'title' ];
    $author = isset( $instance[ 'author' ] ) ? $instance[ 'author' ] : null;
    $display = isset( $instance[ 'display' ] ) ? $instance[ 'display' ] : null;
    $visibility = isset( $instance[ 'visibility' ] ) ? $instance[ 'visibility' ] : null;
    $countdown = isset( $instance[ 'countdown' ] ) ? $instance[ 'countdown' ] : null;
    $timezone = isset( $instance[ 'timezone' ] ) ? $instance[ 'timezone' ] : null;
    ?>
      <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'yakadanda-google-hangout-events'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo isset($title) ? esc_attr( $title ) : null; ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('author'); ?>"><?php _e('Author:', 'yakadanda-google-hangout-events'); ?></label><br/>
        <select id="<?php echo $this->get_field_id('author'); ?>" name="<?php echo $this->get_field_name('author'); ?>">
          <option value="all" <?php echo ($author == 'all') ? 'selected="selected"': null; ?>><?php _e('All', 'yakadanda-google-hangout-events'); ?>&nbsp;</option>
          <option value="self" <?php echo ($author == 'self') ? 'selected="selected"': null; ?>><?php _e('Self', 'yakadanda-google-hangout-events'); ?>&nbsp;</option>
          <option value="other" <?php echo ($author == 'other') ? 'selected="selected"': null; ?>><?php _e('Other', 'yakadanda-google-hangout-events'); ?>&nbsp;</option>
        </select>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('display'); ?>"><?php _e('Display:', 'yakadanda-google-hangout-events'); ?></label><br/>
        <select id="<?php echo $this->get_field_id('display'); ?>" name="<?php echo $this->get_field_name('display'); ?>">
          <option value="1" <?php echo ($display == 1) ? 'selected="selected"': null; ?>>1&nbsp;</option>
          <option value="2" <?php echo ($display == 2) ? 'selected="selected"': null; ?>>2&nbsp;</option>
          <option value="3" <?php echo ($display == 3) ? 'selected="selected"': null; ?>>3&nbsp;</option>
          <option value="4" <?php echo ($display == 4) ? 'selected="selected"': null; ?>>4&nbsp;</option>
          <option value="5" <?php echo ($display == 5) ? 'selected="selected"': null; ?>>5&nbsp;</option>
        </select>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('visibility'); ?>"><?php _e('Visibility:', 'yakadanda-google-hangout-events'); ?></label><br/>
        <select id="<?php echo $this->get_field_id('visibility'); ?>" name="<?php echo $this->get_field_name('visibility'); ?>">
          <option value="public" <?php echo ($visibility == "public") ? 'selected="selected"': null; ?>>Public&nbsp;</option>
          <option value="private" <?php echo ($visibility == "private") ? 'selected="selected"': null; ?>>Private&nbsp;</option>
          <option value="all" <?php echo ($visibility == "all") ? 'selected="selected"': null; ?>>All&nbsp;</option>
        </select>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('countdown'); ?>"><?php _e('Countdown:', 'yakadanda-google-hangout-events'); ?></label><br/>
        <label title="Display countdown clock on first only">
          <input type="radio" value="first" name="<?php echo $this->get_field_name('countdown'); ?>" <?php echo ( ($countdown == 'first') || empty($countdown) ) ? 'checked="checked"' : null; ?>>
          <span><?php _e('First only', 'yakadanda-google-hangout-events'); ?></span>
        </label>
        <br/>
        <label title="Display countdown clock on all">
          <input type="radio" value="all" name="<?php echo $this->get_field_name('countdown'); ?>" <?php echo ($countdown == 'all') ? 'checked="checked"' : null; ?>>
          <span><?php _e('All', 'yakadanda-google-hangout-events'); ?></span>
        </label>
        <br/>
        <label title="Display countdown clock on none">
          <input type="radio" value="none" name="<?php echo $this->get_field_name('countdown'); ?>" <?php echo ($countdown == 'none') ? 'checked="checked"' : null; ?>>
          <span><?php _e('None', 'yakadanda-google-hangout-events'); ?></span>
        </label>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('timezone'); ?>"><?php _e('Timezone:', 'yakadanda-google-hangout-events'); ?></label><br/>
        <select id="<?php echo $this->get_field_id('timezone'); ?>" name="<?php echo $this->get_field_name('timezone'); ?>" style="width: 100%">
          <?php googleplushangoutevent_timezone_options($timezone); ?>
        </select>
      </p>
    <?php 
  }
  
}/* end of googlePlusHangoutEvents class */

add_action('wp_head', 'googleplushangoutevent_css');
function googleplushangoutevent_css() {
  $data = googleplushangoutevent_get_settings();
  ?>
    <style type="text/css">
      .widget_googleplus_events #ghe-event-widget .ghe-vessel,
      .widget_googleplus_hangout_events #ghe-hangout-widget .ghe-vessel {
        background: <?php echo $data['widget_background']; ?>;
        border: 1px solid <?php echo $data['widget_border']; ?>;
      }
      .widget_googleplus_events h4.ghe-title,
      .widget_googleplus_hangout_events h4.ghe-title {
        color: <?php echo $data['title_color']; ?>;
        font-family: <?php echo $data['title_theme']; ?>;
        font-size: <?php echo $data['title_size']; ?>px;
        <?php echo ( $data['title_style'] != 'italic' ) ? 'font-weight: ' . $data['title_style'] . ';' : 'font-style: ' . $data['title_style'] . ';'; ?>
      }
      .widget_googleplus_events .ghe-time,
      .widget_googleplus_hangout_events .ghe-time {
        color: <?php echo $data['date_color']; ?>;
        font-family: <?php echo $data['date_theme']; ?>;
        font-size: <?php echo $data['date_size']; ?>px;
        <?php echo ( $data['date_style'] != 'italic' ) ? 'font-weight: ' . $data['date_style'] . ';' : 'font-style: ' . $data['date_style'] . ';'; ?>
      }
      .widget_googleplus_events .ghe-detail,
      .widget_googleplus_hangout_events .ghe-detail {
        color: <?php echo $data['detail_color']; ?>;
        font-family: <?php echo $data['detail_theme']; ?>;
        font-size: <?php echo $data['detail_size']; ?>px;
        <?php echo ( $data['detail_style'] != 'italic' ) ? 'font-weight: ' . $data['detail_style'] . ';' : 'font-style: ' . $data['detail_style'] . ';'; ?>
      }
      .widget_googleplus_events #ghe-event-widget .ghe-vessel ul.ghe-icons li,
      .widget_googleplus_hangout_events #ghe-hangout-widget .ghe-vessel ul.ghe-icons li {
        background: <?php echo $data['icon_background']; ?>;
        border: 1px solid <?php echo $data['icon_border']; ?>;
        font-family: <?php echo $data['icon_theme']; ?>;
        font-size: <?php $data['icon_size']; ?>px;
        <?php echo ( $data['icon_style'] != 'italic' ) ? 'font-weight: ' . $data['icon_style'] . ';' : 'font-style: ' . $data['icon_style'] . ';'; ?>
      }
      .widget_googleplus_events #ghe-event-widget .ghe-vessel ul.ghe-icons li a,
      .widget_googleplus_hangout_events #ghe-hangout-widget .ghe-vessel ul.ghe-icons li a {
        color: <?php echo $data['icon_color']; ?>;
      }
      .widget_googleplus_events #ghe-event-widget .ghe-vessel ul.ghe-icons li a:hover,
      .widget_googleplus_hangout_events #ghe-hangout-widget .ghe-vessel ul.ghe-icons li a:hover {
        color: <?php echo $data['icon_hover']; ?>;
      }
      .widget_googleplus_events .ghe-countdown,
      .widget_googleplus_hangout_events .ghe-countdown {
        background: <?php echo $data['countdown_background']; ?>;
        color: <?php echo $data['countdown_color']; ?>;
        font-family: <?php echo $data['countdown_theme']; ?>;
        font-size: <?php echo $data['countdown_size']; ?>px;
        <?php echo ( $data['countdown_style'] != 'italic' ) ? 'font-weight: ' . $data['countdown_style'] . ';' : 'font-style: ' . $data['countdown_style'] . ';'; ?>
      }
      .widget_googleplus_events .ghe-countdown span,
      .widget_googleplus_hangout_events .ghe-countdown span {
        font-size: <?php echo $data['countdown_size']; ?>px;
      }
      .widget_googleplus_events #ghe-event-widget .ghe-vessel .ghe-button,
      .widget_googleplus_hangout_events #ghe-hangout-widget .ghe-vessel .ghe-button {
        background: <?php echo $data['event_button_background']; ?>;
        font-family: <?php echo $data['event_button_theme']; ?>;
        font-size: <?php echo $data['event_button_size']; ?>px;
        <?php echo ( $data['event_button_style'] != 'italic' ) ? 'font-weight: ' . $data['event_button_style'] . ';' : 'font-style: ' . $data['event_button_style'] . ';'; ?>
      }
      .widget_googleplus_events #ghe-event-widget .ghe-vessel .ghe-button a,
      .widget_googleplus_hangout_events #ghe-hangout-widget .ghe-vessel .ghe-button a {
        color: <?php echo $data['event_button_color']; ?>;
      }
      .widget_googleplus_events #ghe-event-widget .ghe-vessel .ghe-button a:hover,
      .widget_googleplus_hangout_events #ghe-hangout-widget .ghe-vessel .ghe-button a:hover {
        color: <?php echo $data['event_button_color']; ?>;
      }
      .widget_googleplus_events #ghe-event-widget .ghe-vessel .ghe-button:hover,
      .widget_googleplus_hangout_events #ghe-hangout-widget .ghe-vessel .ghe-button:hover {
        color: <?php echo $data['event_button_color']; ?>;
        background: <?php echo $data['event_button_hover']; ?>;
      }
    </style>
  <?php
}

function googleplushangoutevent_calendar_list( $service ) {
  $output = array( array( 'id' => null ) );
  
  $calendarList = $service->calendarList->listCalendarList();
  
  if ( $calendarList['items'] ) { $output = null;
    while(true) {
      foreach ($calendarList['items'] as $calendarListEntry) {
        if (strpos($calendarListEntry['id'],'group.v') == false) $output[] = $calendarListEntry;
      }
      
      $pageToken = isset($calendarList['nextPageToken']) ? $calendarList['nextPageToken'] : null;
      if ($pageToken) {
        $optParams = array('pageToken' => $pageToken);
        $calendarList = $service->calendarList->listCalendarList($optParams);
      } else {
        break;
      }
    }
  }
  
  return $output;
}

function googleplushangoutevent_i_last($events, $option = 'all') {
  $summary = 0;
  $event_filter = true;
  
  $http_status = isset($events['error']['code']) ? $events['error']['code'] : null;
  
  if ($events && !$http_status) {
    foreach ( $events as $event ) {
      $hangoutlink = isset($event['hangoutLink']) ? $event['hangoutLink'] : false;
      
      if ($option == 'hangout') $event_filter = $hangoutlink;
      elseif ($option == 'normal') $event_filter = !$hangoutlink;
      
      if ($event_filter) {
        $summary = ++$summary;
      }
    }
  }
  return $summary;
}

function googleplushangoutevent_start_time( $time, $timezone ) {
  $starttime = new DateTime( $time );
  $dateTimeZone = new DateTimeZone( $timezone );
  $starttime->setTimezone($dateTimeZone);
  $starttime = $starttime->format('c');
  
  $output = $starttime;
  
  return $output;
}

function googleplushangoutevent_get_widget_message($events, $type) {
  $message = __('Not Connected.', 'yakadanda-google-hangout-events');

  $token = get_option('yakadanda_googleplus_hangout_event_access_token');
  if ($token) {
    if ($type == 'normal') $message = 'No event yet.';
    else $message = __('No hangout event yet.', 'yakadanda-google-hangout-events');
    
    // Error 403 message
    $http_status = isset($events['error']['code']) ? $events['error']['code'] : null;
    if ($http_status) {
      $message = isset($events['error']['message']) ? $events['error']['message'] : null;
      $message = $http_status . ' ' . $message . '.';
    }
  }

  return $message;
}

function googleplushangoutevent_onair($datetime1, $datetime2) {
  $output = false;
  $today = date('c');
  $start = (strtotime($datetime1) - strtotime($today)) / 60;
  $finish = (strtotime($datetime2) - strtotime($today)) / 60;
  
  if ( ($start <= 0) && ($finish >= 0) ) {
    $output = true;
  }
  
  return $output;
}

function googleplushangoutevent_sort_events_asc($a,$b) {
  $aStart = (array) $a["\0*\0modelData"]['start'];
  $bStart = (array) $b["\0*\0modelData"]['start'];
  $x = isset($aStart['dateTime']) ? strtotime($aStart['dateTime']) : strtotime($aStart['date']);
  $y = isset($bStart['dateTime']) ? strtotime($bStart['dateTime']) : strtotime($bStart['date']);
  return $x > $y ? 1 : -1;
}

function googleplushangoutevent_sort_events_desc($a,$b) {
  $aStart = (array) $a["\0*\0modelData"]['start'];
  $bStart = (array) $b["\0*\0modelData"]['start'];
  $x = isset($aStart['dateTime']) ? strtotime($aStart['dateTime']) : strtotime($aStart['date']);
  $y = isset($bStart['dateTime']) ? strtotime($bStart['dateTime']) : strtotime($bStart['date']);
  return $x < $y ? 1 : -1;
}

function googleplushangoutevent_timezone_options($timezone_setting=null) {
  $timezones = array(
      'Africa/Abidjan','Africa/Accra','Africa/Addis_Ababa','Africa/Algiers','Africa/Asmara',
      'Africa/Asmera','Africa/Bamako','Africa/Bangui','Africa/Banjul','Africa/Bissau',
      'Africa/Blantyre','Africa/Brazzaville','Africa/Bujumbura','Africa/Cairo','Africa/Casablanca',
      'Africa/Ceuta','Africa/Conakry','Africa/Dakar','Africa/Dar_es_Salaam','Africa/Djibouti',
      'Africa/Douala','Africa/El_Aaiun','Africa/Freetown','Africa/Gaborone','Africa/Harare',
      'Africa/Johannesburg','Africa/Juba','Africa/Kampala','Africa/Khartoum','Africa/Kigali',
      'Africa/Kinshasa','Africa/Lagos','Africa/Libreville','Africa/Lome','Africa/Luanda',
      'Africa/Lubumbashi','Africa/Lusaka','Africa/Malabo','Africa/Maputo','Africa/Maseru',
      'Africa/Mbabane','Africa/Mogadishu','Africa/Monrovia','Africa/Nairobi','Africa/Ndjamena',
      'Africa/Niamey','Africa/Nouakchott','Africa/Ouagadougou','Africa/Porto-Novo','Africa/Sao_Tome',
      'Africa/Timbuktu','Africa/Tripoli','Africa/Tunis','Africa/Windhoek',
      'America/Adak','America/Anchorage','America/Anguilla','America/Antigua','America/Araguaina',
      'America/Argentina/Buenos_Aires','America/Argentina/Catamarca','America/Argentina/ComodRivadavia','America/Argentina/Cordoba','America/Argentina/Jujuy',
      'America/Argentina/La_Rioja','America/Argentina/Mendoza','America/Argentina/Rio_Gallegos','America/Argentina/Salta','America/Argentina/San_Juan',
      'America/Argentina/San_Luis','America/Argentina/Tucuman','America/Argentina/Ushuaia','America/Aruba','America/Asuncion',
      'America/Atikokan','America/Atka','America/Bahia','America/Bahia_Banderas','America/Barbados',
      'America/Belem','America/Belize','America/Blanc-Sablon','America/Boa_Vista','America/Bogota',
      'America/Boise','America/Buenos_Aires','America/Cambridge_Bay','America/Campo_Grande','America/Cancun',
      'America/Caracas','America/Catamarca','America/Cayenne','America/Cayman','America/Chicago',
      'America/Chihuahua','America/Coral_Harbour','America/Cordoba','America/Costa_Rica','America/Creston',
      'America/Cuiaba','America/Curacao','America/Danmarkshavn','America/Dawson','America/Dawson_Creek',
      'America/Denver','America/Detroit','America/Dominica','America/Edmonton','America/Eirunepe',
      'America/El_Salvador','America/Ensenada','America/Fort_Wayne','America/Fortaleza','America/Glace_Bay',
      'America/Godthab','America/Goose_Bay','America/Grand_Turk','America/Grenada','America/Guadeloupe',
      'America/Guatemala','America/Guayaquil','America/Guyana','America/Halifax','America/Havana',
      'America/Hermosillo','America/Indiana/Indianapolis','America/Indiana/Knox','America/Indiana/Marengo','America/Indiana/Petersburg',
      'America/Indiana/Tell_City','America/Indiana/Vevay','America/Indiana/Vincennes','America/Indiana/Winamac','America/Indianapolis',
      'America/Inuvik','America/Iqaluit','America/Jamaica','America/Jujuy','America/Juneau',
      'America/Kentucky/Louisville','America/Kentucky/Monticello','America/Knox_IN','America/Kralendijk','America/La_Paz',
      'America/Lima','America/Los_Angeles','America/Louisville','America/Lower_Princes','America/Maceio',
      'America/Managua','America/Manaus','America/Marigot','America/Martinique','America/Matamoros',
      'America/Mazatlan','America/Mendoza','America/Menominee','America/Merida','America/Metlakatla',
      'America/Mexico_City','America/Miquelon','America/Moncton','America/Monterrey','America/Montevideo',
      'America/Montreal','America/Montserrat','America/Nassau','America/New_York','America/Nipigon',
      'America/Nome','America/Noronha','America/North_Dakota/Beulah','America/North_Dakota/Center','America/North_Dakota/New_Salem',
      'America/Ojinaga','America/Panama','America/Pangnirtung','America/Paramaribo','America/Phoenix',
      'America/Port-au-Prince','America/Port_of_Spain','America/Porto_Acre','America/Porto_Velho','America/Puerto_Rico',
      'America/Rainy_River','America/Rankin_Inlet','America/Recife','America/Regina','America/Resolute',
      'America/Rio_Branco','America/Rosario','America/Santa_Isabel','America/Santarem','America/Santiago',
      'America/Santo_Domingo','America/Sao_Paulo','America/Scoresbysund','America/Shiprock','America/Sitka',
      'America/St_Barthelemy','America/St_Johns','America/St_Kitts','America/St_Lucia','America/St_Thomas',
      'America/St_Vincent','America/Swift_Current','America/Tegucigalpa','America/Thule','America/Thunder_Bay',
      'America/Tijuana','America/Toronto','America/Tortola','America/Vancouver','America/Virgin',
      'America/Whitehorse','America/Winnipeg','America/Yakutat','America/Yellowknife',
      'Antarctica/Casey','Antarctica/Davis','Antarctica/DumontDUrville','Antarctica/Macquarie','Antarctica/Mawson',
      'Antarctica/McMurdo','Antarctica/Palmer','Antarctica/Rothera','Antarctica/South_Pole','Antarctica/Syowa',
      'Antarctica/Vostok','Arctic/Longyearbyen',
      'Asia/Aden','Asia/Almaty','Asia/Amman','Asia/Anadyr','Asia/Aqtau',
      'Asia/Aqtobe','Asia/Ashgabat','Asia/Ashkhabad','Asia/Baghdad','Asia/Bahrain',
      'Asia/Baku','Asia/Bangkok','Asia/Beirut','Asia/Bishkek','Asia/Brunei',
      'Asia/Calcutta','Asia/Choibalsan','Asia/Chongqing','Asia/Chungking','Asia/Colombo',
      'Asia/Dacca','Asia/Damascus','Asia/Dhaka','Asia/Dili','Asia/Dubai',
      'Asia/Dushanbe','Asia/Gaza','Asia/Harbin','Asia/Hebron','Asia/Ho_Chi_Minh',
      'Asia/Hong_Kong','Asia/Hovd','Asia/Irkutsk','Asia/Istanbul','Asia/Jakarta',
      'Asia/Jayapura','Asia/Jerusalem','Asia/Kabul','Asia/Kamchatka','Asia/Karachi',
      'Asia/Kashgar','Asia/Kathmandu','Asia/Katmandu','Asia/Khandyga','Asia/Kolkata',
      'Asia/Krasnoyarsk','Asia/Kuala_Lumpur','Asia/Kuching','Asia/Kuwait','Asia/Macao',
      'Asia/Macau','Asia/Magadan','Asia/Makassar','Asia/Manila','Asia/Muscat',
      'Asia/Nicosia','Asia/Novokuznetsk','Asia/Novosibirsk','Asia/Omsk','Asia/Oral',
      'Asia/Phnom_Penh','Asia/Pontianak','Asia/Pyongyang','Asia/Qatar','Asia/Qyzylorda',
      'Asia/Rangoon','Asia/Riyadh','Asia/Saigon','Asia/Sakhalin','Asia/Samarkand',
      'Asia/Seoul','Asia/Shanghai','Asia/Singapore','Asia/Taipei','Asia/Tashkent',
      'Asia/Tbilisi','Asia/Tehran','Asia/Tel_Aviv','Asia/Thimbu','Asia/Thimphu',
      'Asia/Tokyo','Asia/Ujung_Pandang','Asia/Ulaanbaatar','Asia/Ulan_Bator','Asia/Urumqi',
      'Asia/Ust-Nera','Asia/Vientiane','Asia/Vladivostok','Asia/Yakutsk','Asia/Yekaterinburg',
      'Asia/Yerevan',
      'Atlantic/Azores','Atlantic/Bermuda','Atlantic/Canary','Atlantic/Cape_Verde','Atlantic/Faeroe',
      'Atlantic/Faroe','Atlantic/Jan_Mayen','Atlantic/Madeira','Atlantic/Reykjavik','Atlantic/South_Georgia',
      'Atlantic/St_Helena','Atlantic/Stanley',
      'Australia/ACT','Australia/Adelaide','Australia/Brisbane','Australia/Broken_Hill','Australia/Canberra',
      'Australia/Currie','Australia/Darwin','Australia/Eucla','Australia/Hobart','Australia/LHI',
      'Australia/Lindeman','Australia/Lord_Howe','Australia/Melbourne','Australia/North','Australia/NSW',
      'Australia/Perth','Australia/Queensland','Australia/South','Australia/Sydney','Australia/Tasmania',
      'Australia/Victoria','Australia/West','Australia/Yancowinna',
      'Europe/Amsterdam','Europe/Andorra','Europe/Athens','Europe/Belfast','Europe/Belgrade',
      'Europe/Berlin','Europe/Bratislava','Europe/Brussels','Europe/Bucharest','Europe/Budapest',
      'Europe/Busingen','Europe/Chisinau','Europe/Copenhagen','Europe/Dublin','Europe/Gibraltar',
      'Europe/Guernsey','Europe/Helsinki','Europe/Isle_of_Man','Europe/Istanbul','Europe/Jersey',
      'Europe/Kaliningrad','Europe/Kiev','Europe/Lisbon','Europe/Ljubljana','Europe/London',
      'Europe/Luxembourg','Europe/Madrid','Europe/Malta','Europe/Mariehamn','Europe/Minsk',
      'Europe/Monaco','Europe/Moscow','Europe/Nicosia','Europe/Oslo','Europe/Paris',
      'Europe/Podgorica','Europe/Prague','Europe/Riga','Europe/Rome','Europe/Samara',
      'Europe/San_Marino','Europe/Sarajevo','Europe/Simferopol','Europe/Skopje','Europe/Sofia',
      'Europe/Stockholm','Europe/Tallinn','Europe/Tirane','Europe/Tiraspol','Europe/Uzhgorod',
      'Europe/Vaduz','Europe/Vatican','Europe/Vienna','Europe/Vilnius','Europe/Volgograd',
      'Europe/Warsaw','Europe/Zagreb','Europe/Zaporozhye','Europe/Zurich',
      'Indian/Antananarivo','Indian/Chagos','Indian/Christmas','Indian/Cocos','Indian/Comoro',
      'Indian/Kerguelen','Indian/Mahe','Indian/Maldives','Indian/Mauritius','Indian/Mayotte',
      'Indian/Reunion',
      'Pacific/Apia','Pacific/Auckland','Pacific/Chatham','Pacific/Chuuk','Pacific/Easter',
      'Pacific/Efate','Pacific/Enderbury','Pacific/Fakaofo','Pacific/Fiji','Pacific/Funafuti',
      'Pacific/Galapagos','Pacific/Gambier','Pacific/Guadalcanal','Pacific/Guam','Pacific/Honolulu',
      'Pacific/Johnston','Pacific/Kiritimati','Pacific/Kosrae','Pacific/Kwajalein','Pacific/Majuro',
      'Pacific/Marquesas','Pacific/Midway','Pacific/Nauru','Pacific/Niue','Pacific/Norfolk',
      'Pacific/Noumea','Pacific/Pago_Pago','Pacific/Palau','Pacific/Pitcairn','Pacific/Pohnpei',
      'Pacific/Ponape','Pacific/Port_Moresby','Pacific/Rarotonga','Pacific/Saipan','Pacific/Samoa',
      'Pacific/Tahiti','Pacific/Tarawa','Pacific/Tongatapu','Pacific/Truk','Pacific/Wake',
      'Pacific/Wallis','Pacific/Yap',
      'Brazil/Acre','Brazil/DeNoronha','Brazil/East','Brazil/West','Canada/Atlantic',
      'Canada/Central','Canada/East-Saskatchewan','Canada/Eastern','Canada/Mountain','Canada/Newfoundland',
      'Canada/Pacific','Canada/Saskatchewan','Canada/Yukon','CET','Chile/Continental',
      'Chile/EasterIsland','CST6CDT','Cuba','EET','Egypt',
      'Eire','EST','EST5EDT','Etc/GMT','Etc/GMT+0',
      'Etc/GMT+1','Etc/GMT+10','Etc/GMT+11','Etc/GMT+12','Etc/GMT+2',
      'Etc/GMT+3','Etc/GMT+4','Etc/GMT+5','Etc/GMT+6','Etc/GMT+7',
      'Etc/GMT+8','Etc/GMT+9','Etc/GMT-0','Etc/GMT-1','Etc/GMT-10',
      'Etc/GMT-11','Etc/GMT-12','Etc/GMT-13','Etc/GMT-14','Etc/GMT-2',
      'Etc/GMT-3','Etc/GMT-4','Etc/GMT-5','Etc/GMT-6','Etc/GMT-7',
      'Etc/GMT-8','Etc/GMT-9','Etc/GMT0','Etc/Greenwich','Etc/UCT',
      'Etc/Universal','Etc/UTC','Etc/Zulu','Factory','GB',
      'GB-Eire','GMT','GMT+0','GMT-0','GMT0',
      'Greenwich','Hongkong','HST','Iceland','Iran',
      'Israel','Jamaica','Japan','Kwajalein','Libya',
      'MET','Mexico/BajaNorte','Mexico/BajaSur','Mexico/General','MST',
      'MST7MDT','Navajo','NZ','NZ-CHAT','Poland',
      'Portugal','PRC','PST8PDT','ROC','ROK',
      'Singapore','Turkey','UCT','Universal','US/Alaska',
      'US/Aleutian','US/Arizona','US/Central','US/East-Indiana','US/Eastern',
      'US/Hawaii','US/Indiana-Starke','US/Michigan','US/Mountain','US/Pacific',
      'US/Pacific-New','US/Samoa','UTC','W-SU','WET',
      'Zulu'
  );
  asort($timezones);
  
  ?><option value="" <?php echo ($timezone_setting == null) ? 'selected="selected"': null; ?>><?php _e('Location/Calendar timezone (default)', 'yakadanda-google-hangout-events'); ?>&nbsp;</option><?php
  
  foreach ( $timezones as $timezone ) {
    ?><option value="<?php echo $timezone; ?>" <?php echo ($timezone_setting == $timezone) ? 'selected="selected"': null; ?>><?php echo $timezone; ?></option><?php
  }
}
