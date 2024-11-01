<?php
/*
 * [google+events]
 * [google+events type="hangout" limit="6" past="8"]
 * type = all, normal, or hangout, default is all
 * src = all, gcal, or gplus, by default source is all
 * limit = number of events to show, it limited to 20
 * past = number of months, to display past events in X months ago
 * author = self, other, or all, default is all
 * id = Event identifier (string), e.g. https://plus.google.com/events/cXXXXX XXXXX is event identifier
 * filter_out = Filter out certain events by event identifiers, seperated by comma
 * search = Text search terms (string) to display events that match these terms in any field, except for extended properties
 * attendees = show, show_all, or hide, default is hide
 * timeZone = Time zone used in the response, optional. Default is time zone based on location (hangout event not have location) if not have location it will use google account/calendar time zone. Supported time zones at http://www.php.net/manual/en/timezones.php (string)
 * countdown = true, or false, by default countdown is false
 * visibility = public, private, or all, default is public
 * transient = on or off, default is off
 * expiration = The maximum of minutes to keep the data before refreshing
 */
add_shortcode( 'google+events', 'googleplushangoutevent_shortcode' );
function googleplushangoutevent_shortcode( $atts ) {
  $a = shortcode_atts( array(
    'type' => 'all',
    'limit' => 20,
    'past' => null,
    'author' => 'all',
    'id' => null,
    'filter_out' => array(),
    'search' => null,
    'attendees' => 'hide',
    'timezone' => null,
    'countdown' => false,
    'src' => 'all',
    'visibility' => 'public',
    'transient' => null,
    'expiration' => null
  ), $atts );
  
  // Enqueue scripts
  googleplushangoutevent_wp_enqueue_scripts_load();

  $data = googleplushangoutevent_get_settings();
  $token = get_option('yakadanda_googleplus_hangout_event_access_token');

  $a['transient'] = empty($a['transient']) ? $data['transient'] : $a['transient'];
  $a['transient'] = ($a['transient'] == 'on') ? true : false;
  $a['expiration'] = empty($a['expiration']) ? $data['expiration'] : googleplushangoutevent_sanitize_expiration($a['expiration']);
  $a['limit'] = ($a['limit'] > 20) ? 20 : $a['limit'];

  if ($a['id']) {
    $transient_name = md5('special_query_id_' . $a['id'] . $a['timezone'] . $token);
    if ( (false === ( $special_query_id = get_transient($transient_name) )) && $token && $a['transient'] ) {
      $special_query_id = googleplushangoutevent_response( null, $a['id'], null, $a['timezone'] );
      
      if ( !empty($special_query_id) ) { set_transient($transient_name, $special_query_id, 60 * $a['expiration']); }
    }
    if (!$token) {
      delete_transient($transient_name);
    }
    $events = ($a['transient']) ? $special_query_id : googleplushangoutevent_response( null, $a['id'], null, $a['timezone'] );
  } else {
    $transient_name = md5('special_query_event_' . $a['past'] . $a['search'] . $a['timezone'] . $token);
    if ( (false === ( $special_query_event = get_transient($transient_name) )) && $token && $a['transient'] ) {
      $special_query_event = googleplushangoutevent_response( $a['past'], null, $a['search'], $a['timezone'] );

      if ( !empty($special_query_event) ) { set_transient($transient_name, $special_query_event, 60 * $a['expiration']); }
    }
    if (!$token) {
      delete_transient($transient_name);
    }
    $events = ($a['transient']) ? $special_query_event : googleplushangoutevent_response( $a['past'], null, $a['search'], $a['timezone'] );

    // Sorting events
    if ($a['past'] && $events) {
      uasort( $events , 'googleplushangoutevent_sort_events_desc' );
    } elseif ($events) {
      uasort( $events , 'googleplushangoutevent_sort_events_asc' );
    }
  }

  $output = null;
  $i = 0;
  $filter = $src_filter = true;
  $creator = 1;
  $http_status = isset($events['error']['code']) ? $events['error']['code'] : null;
  
  if ($events && !$http_status ) {
    
    // filter out by event identifiers
    if ($a['filter_out']) {
      $a['filter_out'] = explode(',', str_replace(' ', '', $a['filter_out']));
    }
    
    if ($a['id']) {
      // Events get
      $event = $events;

      $used_timezone = isset($event['timeZoneLocation']) ? $event['timeZoneLocation'] : $event['timeZoneCalendar'];
      $used_timezone = ($a['timezone']) ? $a['timezone'] : $used_timezone;

      $output .= '<div itemscope itemtype="http://data-vocabulary.org/Event" class="yghe-event">';

      $output .= '<div class="yghe-organizer">' . googleplushangoutevent_organizer($event);
      $output .= googleplushangoutevent_ago($event['created'], $event['updated']) . '</div>';

      $output .= '<div class="yghe-event-title"><a href="' . $event['htmlLink'] . '" title="' . $event['summary'] . '" itemprop="url"><span itemprop="summary">' . $event['summary'] . '</span></a></div>';

      $start = (array) $event["\0*\0modelData"]['start'];
      $end = (array) $event["\0*\0modelData"]['end'];
      $start_event = isset($start['dateTime']) ? $start['dateTime'] : $start['date'];
      $end_event = isset($end['dateTime']) ? $end['dateTime'] : $end['date'];

      $output .= '<div class="yghe-event-time">' . googleplushangoutevent_time($start_event, $end_event, $used_timezone, 'shortcode') . '</div>';

      if ( isset($event['location']) ) {
        $output .= '<div itemprop="location" itemscope itemtype="http://data-vocabulary.org/​Organization" class="yghe-event-location" title="Location"><a itemprop="address" itemscope itemtype="http://data-vocabulary.org/Address" href="http://maps.google.com/?q=' . $event['location'] . '" title="' . $event['location'] . '">' . $event['location'] . '</a></div>';
      } else {
        $onair = googleplushangoutevent_onair($start_event, $end_event);
        if ( $onair ) $output .= '<div class="yghe-event-onair" title="On Air">';
        else $output .= '<div class="yghe-event-hangout" title="Hangout">';

        if ( isset($event['hangoutLink']) ) {
          $output .= '<a href="' . $event['hangoutLink'] . '" title="Google+ Hangout">Google+ Hangout</a>';
        }
        $output .= '</div>';
      }

      $extend_img_src = get_option('googleplushangoutevent_' . $event['id']);
      if ($extend_img_src) {
        $output .= '<div class="yghe-event-photo"><img itemprop="photo" src="' . $extend_img_src . '"/></div>';
      }

      $description = isset($event['description']) ? nl2br( $event['description'] ) : null;
      $output .= '<div itemprop="description" class="yghe-event-description">' . $description . '</div>';

      if ( ($a['attendees'] == 'show') || ($a['attendees'] == 'show_all') ) {
        $guests = isset($event["\0*\0modelData"]['attendees']) ? $event["\0*\0modelData"]['attendees'] : null;
        $output .= '<div class="yghe-event-attendees">'. googleplushangoutevent_get_attendees( $guests, $a['attendees'] ) . '</div>';
      }

      if ($a['countdown'] == 'true') {
        $time = googleplushangoutevent_start_time($start_event, $used_timezone);
        $output .= '<div id="' . uniqid() . '" class="yghe-shortcode-countdown fix" data-cdate="' . $time . '">' . $time . '</div>';
      }

      $output .= '</div>';
      
    } else {
      // Events lists
      foreach ($events as $event) {
        $hangoutlink = isset($event['hangoutLink']) ? $event['hangoutLink'] : false;
        
        $event['visibility'] = isset($event['visibility']) ? $event['visibility'] : 'public';
        $event['visibility'] = ($a['visibility'] == "all") ? 'all' : $event['visibility'];
        
        if ($a['type'] == 'normal') $filter = !$hangoutlink;
        elseif ($a['type'] == 'hangout') $filter = $hangoutlink;
        
        switch($a['author']) {
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
        
        if ($a['src'] != 'all') {
          $src_filter = googleplushangoutevent_src_filter($a['src'], $event['htmlLink']);
        }
        
        if ( $filter && $creator && ($a['visibility'] == $event['visibility']) && !in_array($event['id'], $a['filter_out']) && $src_filter ) { $i++;
          $used_timezone = isset($event['timeZoneLocation']) ? $event['timeZoneLocation'] : $event['timeZoneCalendar'];
          $used_timezone = ($a['timezone']) ? $a['timezone'] : $used_timezone;
          
          $output .= '<div itemscope itemtype="http://data-vocabulary.org/Event" class="yghe-event">';
          
          $output .= '<div class="yghe-organizer">' . googleplushangoutevent_organizer($event);
          $output .= googleplushangoutevent_ago($event['created'], $event['updated']) . '</div>';
          
          $output .= '<div class="yghe-event-title"><a href="' . $event['htmlLink'] . '" title="' . $event['summary'] . '" itemprop="url"><span itemprop="summary">' . $event['summary'] . '</span></a></div>';
          
          $start = (array) $event["\0*\0modelData"]['start'];
          $end = (array) $event["\0*\0modelData"]['end'];
          $start_event = isset($start['dateTime']) ? $start['dateTime'] : $start['date'];
          $end_event = isset($end['dateTime']) ? $end['dateTime'] : $end['date'];
          
          $output .= '<div class="yghe-event-time">' . googleplushangoutevent_time($start_event, $end_event, $used_timezone,'shortcode') . '</div>';
          
          if ( isset($event['location']) ) {
            $output .= '<div itemprop="location" itemscope itemtype="http://data-vocabulary.org/​Organization" class="yghe-event-location" title="Location"><a itemprop="address" itemscope itemtype="http://data-vocabulary.org/Address" href="http://maps.google.com/?q=' . $event['location'] . '" title="' . $event['location'] . '">' . $event['location'] . '</a></div>';
          } else {
            $onair = googleplushangoutevent_onair($start_event, $end_event);
            if ( $onair ) $output .= '<div class="yghe-event-onair" title="On Air">';
            else $output .= '<div class="yghe-event-hangout" title="Hangout">';
          
            if ( isset($event['hangoutLink']) ) {
              $output .= '<a href="' . $event['hangoutLink'] . '" title="Google+ Hangout">Google+ Hangout</a>';
            }
            $output .= '</div>';
          }
          
          $extend_img_src = get_option('googleplushangoutevent_' . $event['id']);
          if ($extend_img_src) {
            $output .= '<div class="yghe-event-photo"><img itemprop="photo" src="' . $extend_img_src . '"/></div>';
          }
          
          $description = isset($event['description']) ? nl2br( $event['description'] ) : null;
          $output .= '<div itemprop="description" class="yghe-event-description">'. $description . '</div>';
          
          if ( ($a['attendees'] == 'show') || ($a['attendees'] == 'show_all') ) {
            $guests = isset($event["\0*\0modelData"]['attendees']) ? $event["\0*\0modelData"]['attendees'] : null;
            $output .= '<div class="yghe-event-attendees">'. googleplushangoutevent_get_attendees( $guests, $a['attendees'] ) . '</div>';
          }
          
          if ( $a['countdown'] == 'true' ) {
            $time = googleplushangoutevent_start_time($start_event, $used_timezone);
            $output .= '<div id="' . uniqid() . '" class="yghe-shortcode-countdown fix" data-cdate="' . $time . '">' . $time . '</div>';
          }
          
          $output .= '</div>';

          if ($a['limit'] == $i) break;
        }
      }
    }
  }
  
  if ( ($output == null) && !$http_status ) {
    $message = __('No event and hangout event yet.', 'yakadanda-google-hangout-events');
    if ($a['type'] == 'normal') $message = __('No event yet.', 'yakadanda-google-hangout-events');
    elseif ($a['type'] == 'hangout') $message = __('No hangout event yet.', 'yakadanda-google-hangout-events');
    $output = ($token) ? $message : __('Not Connected.', 'yakadanda-google-hangout-events');
  }
  
  // Error 403 message
  if ($http_status) {
    $message = isset($events['error']['message']) ? $events['error']['message'] : null;
    $output = $http_status . ' ' . $message . '.';
  }
  
  return $output;
}

function googleplushangoutevent_time($startdate, $finishdate, $timezone, $type) {
  $startdate = new DateTime( $startdate );
  $finishdate = new DateTime( $finishdate );
  
  $dateTimeZone = new DateTimeZone( $timezone );
  
  $startdate->setTimezone($dateTimeZone);
  $startdate = $startdate->format('c');
  
  $finishdate->setTimezone($dateTimeZone);
  $finishdate = $finishdate->format('c');
  
  $diff = round(abs(strtotime($finishdate)-strtotime($startdate))/86400);
  
  $begindate = str_split($startdate, 19);
  $year_event = date('Y', strtotime($begindate[0]));
  $year_current = date('Y');
  $years = $year_event - $year_current;
  
  $output = null;
  
  if ( $type == 'shortcode'  ) {
    $timezone_abbreviations = googleplushangoutevent_timezone_abbreviations( $timezone );
    
    $output = '<time itemprop="startDate" datetime="' . $startdate . '">' . date('D, F d, g:i A', strtotime($begindate[0])) . ' ' . $timezone_abbreviations . '</time>';
    if ($years > 0) $output = '<time itemprop="startDate" datetime="' . $startdate . '">' . date('D, F d Y, g:i A', strtotime($begindate[0])) . ' ' . $timezone_abbreviations . '</time>';
  } elseif ( $type == 'widget' ) {
    $timezone_abbreviations = googleplushangoutevent_timezone_abbreviations( $timezone );
    
    $enddate = str_split($finishdate, 19);
    $output = '<time itemprop="startDate" datetime="' . $startdate . '">' . date('F jS Y', strtotime($begindate[0])) . '<br>' . date('g:i a', strtotime($begindate[0])) . ' - ' . date('g:i a', strtotime($enddate[0])) . ' ' . $timezone_abbreviations . '</time>';
  }
  
  if ( $diff >= 1 ) {
    $timezone_abbreviations = googleplushangoutevent_timezone_abbreviations( $timezone );
    
    $enddate = str_split($finishdate, 19);
    if ( $type == 'shortcode' ) {
      $output = '<time itemprop="startDate" datetime="' . $startdate . '">' . date('D, F d, g:i A', strtotime($begindate[0])) . ' ' . $timezone_abbreviations . '</time> - <time itemprop="endDate" datetime="' . $finishdate . '">' . date('D, F d, g:i A', strtotime($enddate[0])) . ' ' . $timezone_abbreviations . '</time>';
      if ($years > 0) $output = '<time itemprop="startDate" datetime="' . $startdate . '">' . date('D, F d Y, g:i A', strtotime($begindate[0])) . ' ' . $timezone_abbreviations . '</time> - <time itemprop="endDate" datetime="' . $finishdate . '">' . date('D, F d Y, g:i A', strtotime($enddate[0])) . '&nbsp;' . $timezone_abbreviations . '</time>';
      
      if ( !isset($timezone_abbreviations) ) {
        $output = '<time itemprop="startDate" datetime="' . $startdate . '">' . date('D, F d, ', strtotime($begindate[0])) . 'All day</time>';
        if ($years > 0) $output = '<time itemprop="startDate" datetime="' . $startdate . '">' . date('D, F d Y, ', strtotime($begindate[0])) . 'All day</time>';
      }
    } elseif ( $type == 'widget' ) {
      $output = '<time itemprop="startDate" datetime="' . $startdate . '">' . date('F jS Y g:i a', strtotime($begindate[0])) . '</time> to<br><time itemprop="endDate" datetime="' . $finishdate . '">' . date('F jS Y g:i a', strtotime($enddate[0])) . ' ' . $timezone_abbreviations . '</time>';
      if ( !isset($timezone_abbreviations) ) {
        $output = '<time itemprop="startDate" datetime="' . $startdate . '">' . date('F jS Y', strtotime($begindate[0])) . '<br>All day</time>';
      }
    }
  }
  
  return $output;
}

function googleplushangoutevent_ago($datetime1, $datetime2) {
  $i = strtotime($datetime1);
  $info = null;
  
  // difference in minutes
  $diff = (strtotime($datetime2) - strtotime($datetime1)) / 60;
  if ($diff > 1) {
    $i = strtotime($datetime2);
    $info = '&nbsp;(updated)';
  }
  
  $m = time() - $i;
  $o = 'just now';
  $t = array('year' => 31556926, 'month' => 2629744, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'minute' => 60, 'second' => 1);
  foreach ($t as $u => $s) {
    if ($s <= $m) {
      $v = floor($m/$s);
      $o = "$v $u" . ($v == 1 ? '' : 's') . ' ago';
      break;
    }
  }
  
  $o = $o . $info;
  
  return $o;
}

function googleplushangoutevent_timezone_abbreviations( $timezone = null ) {
  $output = null;
  
  if ( $timezone ) {
    $dateTime = new DateTime();
    $dateTime->setTimeZone(new DateTimeZone( $timezone ));
    $output = $dateTime->format('T');
  }
  
  return $output;
}

function googleplushangoutevent_organizer($event) {
  $output = null;
  
  if ( isset($event["\0*\0modelData"]['organizer']['id']) ) {
    $output = '<a href="https://plus.google.com/' . $event["\0*\0modelData"]['organizer']['id'] . '" title="Organizer">' . $event["\0*\0modelData"]['organizer']['displayName'] . '</a> ';
  } else {
    if ( strpos($event["\0*\0modelData"]['organizer']['email'], '.calendar.') !== false ) {
      $output = '<a href="mailto:' . $event["\0*\0modelData"]['creator']['email'] . '" title="Calendar">' . $event["\0*\0modelData"]['organizer']['displayName'] . '</a> ';
    } else {
      if (isset($event["\0*\0modelData"]['organizer']['displayName'])) {
        $output = '<a href="mailto:' . $event["\0*\0modelData"]['organizer']['email'] . '" title="Organizer">' . $event["\0*\0modelData"]['organizer']['displayName'] . '</a> ';
      } else {
        $display_name = googleplushangoutevent_display_name( $event );
        if ( $display_name )
          $output = '<a href="mailto:' . $event["\0*\0modelData"]['organizer']['email'] . '" title="Coworker\'s Calendar">' . $display_name . '</a> ';
      }
    }
  }
  
  return $output;
}

function googleplushangoutevent_display_name($event) {
  $output = null;
  if ( isset($event["\0*\0modelData"]['attendees']) ) {
    foreach ( $event["\0*\0modelData"]['attendees'] as $attendee ) {
      if ( $attendee['email'] == $event["\0*\0modelData"]['organizer']['email'] ) {
        $output = ( $attendee['displayName'] ) ? $attendee['displayName'] : null;
        break;
      }
    }
  }
  return $output;
}

function googleplushangoutevent_get_attendees( $guests, $view ) {
  $output = null;
  $i = $j = $k = 0;
  
  if ( $guests ) {
    $accepted = $tentative = $needsAction = $accepted_title = $tentative_title = $needsAction_title = null;
    
    foreach ( $guests as $guest ) {
      if ( $guest['responseStatus'] == 'accepted' ) { ++$i;
        $display_name = isset($guest['displayName']) ? $guest['displayName'] : $guest['email'];
        $pass = ( ($view == 'show') && ($i >= 5) ) ? false : true;
        
        if ( $pass ) {
          if ( isset($guest['id']) ) {
            $accepted .= '<a href="https://plus.google.com/' . $guest['id'] . '">' . $display_name . '</a>';
          } else {
            $accepted .= '<a href="mailto:' . $guest['email'] . '">' . $display_name . '</a>';
          }
          $accepted .= ', ';
        } else { $accepted_title .= $display_name . ', '; }
      } elseif ( $guest['responseStatus'] == 'tentative' ) { ++$j;
        $display_name = isset($guest['displayName']) ? $guest['displayName'] : $guest['email'];
        $pass = ( ($view == 'show') && ($j >= 5) ) ? false : true;
        
        if ($pass) {
          if ( isset($guest['id']) ) {
            $tentative .= '<a href="https://plus.google.com/' . $guest['id'] . '">' . $display_name . '</a>';
          } else {
            $tentative .= '<a href="mailto:' . $guest['email'] . '">' . $display_name . '</a>';
          }
          $tentative .= ', ';
        } else { $tentative_title .= $display_name . ', '; }
      } elseif ( $guest['responseStatus'] == 'needsAction' ) { ++$k;
        $display_name = isset($guest['displayName']) ? $guest['displayName'] : $guest['email'];
        $pass = ( ($view == 'show') && ($k >= 5) ) ? false : true;
        
        if ( $pass ) {
          if ( isset($guest['id']) ) {
            $needsAction .= '<a href="https://plus.google.com/' . $guest['id'] . '">' . $display_name . '</a>';
          } else {
            $needsAction .= '<a href="mailto:' . $guest['email'] . '">' . $display_name . '</a>';
          }
          $needsAction .= ', ';
        } else { $needsAction_title .= $display_name . ', '; }
      }
    }
    
    if ($accepted) {
      $output .= '<p>Going (' . $i . ')</p>' . substr_replace($accepted ,"",-2);
      if ( ($view == 'show') && ($i>4) ) $output .= ', <span title="' . substr_replace($accepted_title ,"",-2) . '">...</span>';
    }
    if ($tentative) {
      $output .= '<p>Maybe (' . $j . ')</p>' . substr_replace($tentative ,"",-2);
      if ( ($view == 'show') && ($j>4) ) $output .= ', <span title="' . substr_replace($tentative_title ,"",-2) . '">...</span>';
    }
    if ($needsAction) {
      $output .= '<p>Unknown (' . $k . ')</p>' . substr_replace($needsAction ,"",-2);
      if ( ($view == 'show') && ($k>4) ) $output .= ', <span title="' . substr_replace($needsAction_title ,"",-2) . '">...</span>';
    }
  }
  
  return $output;
}

function googleplushangoutevent_fetch_data($url) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 20);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}

function googleplushangoutevent_google_geocoding( $address=null ) {
  $output = null;
  
  if ( $address ) {
    $address = str_replace( ' ', '+', $address );
    
    $url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&sensor=false';
    $responses = googleplushangoutevent_fetch_data($url);
    $data = json_decode( $responses );
    
    if ( $data->status == 'OK' ) {
      $lat = $data->results[0]->geometry->location->lat;
      $lng = $data->results[0]->geometry->location->lng;
      
      $output = $lat . ',' . $lng;
    }
  }
  
  return $output;
}

function googleplushangoutevent_location_timezone( $location=null, $time=null) {
  $output = null;
  
  if ( $location && $time ) {
    $timestamp = strtotime($time);
    $url = 'https://maps.googleapis.com/maps/api/timezone/json?location=' . $location . '&timestamp=' . $timestamp . '&sensor=false';
    $responses = googleplushangoutevent_fetch_data($url);
    $data = json_decode( $responses );
    
    if ( $data->status == 'OK' ) $output = $data->timeZoneId;
  }
  
  return $output;
}

function googleplushangoutevent_src_filter($src, $url) {
  $output = false;
  switch ($src) {
    case 'gcal':
      if (strpos($url, 'google.com/calendar/') !== false) {
        $output = true;
      }
      break;
    case 'gplus':
      if (strpos($url, 'plus.google.com/events/') !== false) {
        $output = true;
      }
      break;
  }
  return $output;
}
