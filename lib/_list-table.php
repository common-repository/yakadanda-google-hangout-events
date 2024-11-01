<?php
if (!class_exists('WP_List_Table')) require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
class googleplushangoutevent_List_Table extends WP_List_Table {
  function __construct() {
    global $status, $page;

    // Set parent defaults
    parent::__construct(array(
      'singular' => 'event',
      'plural' => 'events',
      'ajax' => false
    ));
  }

  function column_default($item, $column_name) {
    switch ($column_name) {
      case 'author':
        $url = isset($item["\0*\0modelData"]['creator']['id']) ? 'https://plus.google.com/' . $item["\0*\0modelData"]['creator']['id'] : 'mailto:' . $item["\0*\0modelData"]['creator']['email'];
        return '<a href="' . $url . '">' . $item["\0*\0modelData"]['creator']['displayName'] . '</a>';
      case 'time':
        $start = isset($item["\0*\0modelData"]['start']['dateTime']) ? $item["\0*\0modelData"]['start']['dateTime'] : $item["\0*\0modelData"]['start']['date'];
        $end = isset($item["\0*\0modelData"]['end']['dateTime']) ? $item["\0*\0modelData"]['end']['dateTime'] : $item["\0*\0modelData"]['end']['date'];
        $timezone = isset($item['timeZoneLocation']) ? $item['timeZoneLocation'] : $item['timeZoneCalendar'];
        return $this->get_time($start, $end, $timezone);
      case 'location':
        return isset($item['location']) ? '<a href="http://maps.google.com/?q='. $item['location'] .'">' . $item['location'] . '</a>' : '<a href="' . $item['hangoutLink'] . '">Hangout</a>';
      case 'date':
        return $this->get_date($item['created'], $item['updated']);
      default:
        return print_r($item, true); // Show the whole array for troubleshooting purposes
    }
  }

  function column_title($item) {
    // Build row actions
    $actions = array(
      'extend' => sprintf(__('<a href="?page=%s&action=%s&id=%s" class="cta--secondary">Extend</a>', 'yakadanda-google-hangout-events'), $_REQUEST['page'], 'extend', $item['id']),
      'delete' => sprintf(__('<a href="?page=%s&action=%s&id=%s" class="cta--secondary">Delete</a>', 'yakadanda-google-hangout-events'), $_REQUEST['page'], 'delete', $item['id']),
      'view' => sprintf(__('<a href="%s" class="cta--secondary">View</a>', 'yakadanda-google-hangout-events'), $item['htmlLink'])
    );

    $title = '<a href="?page=' . $_REQUEST['page'] . '&action=extend&id=' . $item['id'] . '">' . $item['summary'] . '</a>';
    if ( $item['visibility'] ) $title = $title . ' - <span class="post-state">Private</span>';
    // Return the title contents
    return sprintf('<strong>%1$s</strong>%2$s',
      /* $1%s */ $title,
      /* $2%s */ $this->row_actions($actions)
    );
  }

  function column_cb($item) {
    return sprintf(
      '<input id="cb-select-%1$s" type="checkbox" name="%2$s[]" value="%1$s" />',
      /* $1%s */ $item['id'],
      /* $2%s */ $this->_args['plural']
    );
  }

  function get_columns() {
    $columns = array(
      'cb' => '<input type="checkbox" />', // Render a checkbox instead of text
      'title' => __('Title', 'yakadanda-google-hangout-events'),
      'author' => __('Author', 'yakadanda-google-hangout-events'),
      'time' => __('Time', 'yakadanda-google-hangout-events'),
      'location' => __('Location', 'yakadanda-google-hangout-events'),
      'date' => __('Date', 'yakadanda-google-hangout-events')
    );
    
    return $columns;
  }

  function get_sortable_columns() {
    $sortable_columns = array(
      'title' => array('summary', false), // true means it's already sorted
      'location' => array('location', false),
      'date' => array('created', false)
    );
    
    return $sortable_columns;
  }

  function get_bulk_actions() {
    $actions = array(
      'delete' => 'Delete'
    );
    
    return $actions;
  }

  function process_bulk_action() {
    // Detect when a bulk action is being triggered...
    if ('delete' === $this->current_action()) {
      if (isset($_GET['events']) && is_array($_GET['events'])) {
        foreach ($_GET['events'] as $eventId) {
          googleplushangoutevent_delete_event($eventId);
        }
      }
    }
  }

  function prepare_items() {
    global $wpdb;

    $per_page = 20;

    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();

    $this->_column_headers = array($columns, $hidden, $sortable);

    $this->process_bulk_action();
  
    $data = googleplushangoutevent_response_admin();
  
    function usort_reorder($a, $b) {
      $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'updated'; // If no sort, default to title
      $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; // If no order, default to asc
      $result = strcmp($a[$orderby], $b[$orderby]); // Determine sort order
      return ($order === 'desc') ? $result : -$result; // Send final sort direction to usort
    }

    usort($data, 'usort_reorder');

    $current_page = $this->get_pagenum();

    $total_items = count($data);

    $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

    $this->items = $data;

    $this->set_pagination_args(array(
      'total_items' => $total_items, // WE have to calculate the total number of items
      'per_page' => $per_page, // WE have to determine how many items to show on a page
      'total_pages' => ceil($total_items / $per_page)   // WE have to calculate the total number of pages
    ));
  }
  
  function get_date($created, $modified) {
    $created = strtotime($created);
    $modified = strtotime($modified);
    
    $date = $created;
    $status = 'Published';
    if ($created < $modified ) {
      $date = $modified;
      $status = 'Last Modified';
    }
    
    $output = '<abbr title="' . date('Y/m/d g:i:s A', $date) . '">' . date('Y/m/d', $date) . '</abbr><br>' . $status;
    
    return $output;
  }
  
  function get_time($startdate, $finishdate, $timezone) {
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
    
    $timezone_abbreviations = googleplushangoutevent_timezone_abbreviations( $timezone );
    
    $output = date('D, M d, g:i A', strtotime($begindate[0])) . ' ' . $timezone_abbreviations;
    if ($years > 0) $output = date('D, M d Y, g:i A', strtotime($begindate[0])) . ' ' . $timezone_abbreviations;
    
    if ( $diff >= 1 ) {
      $enddate = str_split($finishdate, 19);
      $output = date('D, M d, g:i A', strtotime($begindate[0])) . ' ' . $timezone_abbreviations . ' - ' . date('D, M d, g:i A', strtotime($enddate[0])) . ' ' . $timezone_abbreviations;
      if ($years > 0) $output = date('D, M d Y, g:i A', strtotime($begindate[0])) . ' ' . $timezone_abbreviations . ' - ' . date('D, M d Y, g:i A', strtotime($enddate[0])) . '&nbsp;' . $timezone_abbreviations;
      
      if ( !isset($timezone_abbreviations) ) {
        $output = date('D, M d, ', strtotime($begindate[0])) . 'All day';
        if ($years > 0) $output = date('D, M d Y, ', strtotime($begindate[0])) . 'All day';
      }
    }
    
    return $output;
  }

}
