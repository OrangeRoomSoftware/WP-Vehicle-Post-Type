<?php
/*
Plugin Name: Vehicle Post Type
Plugin URI: https://github.com/orangeroomsoftware/WP-Vehicle-Post-Type
Version: 1.0
Author: <a href="http://www.orangeroomsoftware.com/">Orange Room Software</a>
Description: A post type for Vehicles
*/

define('VEHICLE_PLUGIN_URL', '/wp-content/plugins/' . basename(dirname(__FILE__)) );
define('VEHICLE_PLUGIN_DIR', dirname(__FILE__));

# Post Thumbnails
add_theme_support( ‘post-thumbnails’ );

/*
 * Add shortcodes to the widgets and excerpt
*/
add_filter( 'get_the_excerpt', 'do_shortcode' );
add_filter( 'the_excerpt', 'do_shortcode' );
add_filter( 'widget_text', 'do_shortcode' );

# Site Stylesheet
add_action('wp_print_styles', 'ors_vehicle_template_stylesheets', 6);
function ors_vehicle_template_stylesheets() {
  wp_enqueue_style('vehicle-template-style', VEHICLE_PLUGIN_URL . "/style.css", 'ors-vehicle', null, 'all');
}

# Admin Stylesheet
add_action('admin_print_styles', 'ors_vehicle_plugin_admin_stylesheets', 6);
function ors_vehicle_plugin_admin_stylesheets() {
  wp_enqueue_style('vehicle-vehicle-admin-style', VEHICLE_PLUGIN_URL . "/admin-style.css", 'ors-admin', null, 'all');
}

# Admin Javascript
add_action('admin_print_scripts', 'ors_vehicle_plugin_admin_script', 5);
function ors_vehicle_plugin_admin_script() {
  wp_register_script( 'ors_vehicle_plugin_admin_script', VEHICLE_PLUGIN_URL . "/admin-script.js", 'jquery', time() );
  wp_enqueue_script('ors_vehicle_plugin_admin_script');
}

/*
 * First time activation
*/
register_activation_hook( __FILE__, 'activate_vehicle_post_type' );
function activate_vehicle_post_type() {
  create_vehicle_post_type();
  flush_rewrite_rules();
  add_option( 'ors-vehicle-global-options',  'Air Conditioning|Climate Control|Power Steering|Power Disc Brakes|Power Windows|Power Door Locks|Tilt Wheel|Telescoping Wheel|Steering Wheel Audio Controls|Cruise Control|AM/FM Stereo|Cassette|Single Compact Disc|Multi Compact Disc|CD Auto Changer|Premium Sound|Integrated Phone|Navigation System|Parking Sensors|Dual Front Airbags|Side Front Airbags|Front and Rear Side Airbags|ABS 4-Wheel|Traction Control|Leather|Full Leather|Power Seat|Dual Power Seats|Flip-up Sun Roof|Sliding Sun Roof|Moon Roof|Alloy Wheels', '', true );
  add_option( 'ors-vehicle-types', 'Car|Truck|SUV|Van|Minivan|Wagon', '', true );
}

# Custom post type
add_action( 'init', 'create_vehicle_post_type' );
function create_vehicle_post_type() {
  $labels = array(
    'name' => _x('Vehicles', 'post type general name'),
    'singular_name' => _x('Vehicle', 'post type singular name'),
    'add_new' => _x('Add New', 'vehicle'),
    'add_new_item' => __('Add New Vehicle'),
    'edit_item' => __('Edit Vehicle'),
    'new_item' => __('New Vehicle'),
    'view_item' => __('View Vehicle'),
    'search_items' => __('Search Vehicles'),
    'not_found' =>  __('No vehicles found'),
    'not_found_in_trash' => __('No vehicles found in Trash'),
    'parent_item_colon' => '',
    'menu_name' => 'Vehicles'

  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'query_var' => true,
    'capability_type' => 'post',
    'has_archive' => true,
    'hierarchical' => false,
    'menu_position' => 6,
    'supports' => array('title', 'excerpt', 'gallery', 'thumbnail', 'editor', 'tags'),
    'menu_icon' => VEHICLE_PLUGIN_URL . '/icon.png',
    'rewrite' => array(
      'slug' => 'vehicles',
      'with_front' => false
    )
  );

  register_post_type( 'vehicle', $args );
}

/**
 * Admin Options
 */
require_once ( VEHICLE_PLUGIN_DIR . '/plugin-options.php' );

/**
 * Meta Box for Editor
 */
add_action( 'add_meta_boxes', 'add_custom_vehicle_meta_boxes' );
function add_custom_vehicle_meta_boxes() {
  add_meta_box("vehicle_meta", 'Vehicle Information', "custom_vehicle_meta_boxes", "vehicle", "normal", "high");
}

function custom_vehicle_meta_boxes() {
  global $post;
  $custom_data = get_post_custom($post->ID);

  $options = array_filter(explode('|', $custom_data['options'][0]), 'strlen');
  sort($options);

  $global_options = explode('|', get_option('ors-vehicle-global-options'));
  $vehicle_types = explode('|', get_option('ors-vehicle-types'));

  ?>
  <div class="group">
    <p>
      <label>Stock:</label><br>
      <input type="text" name="vehicle_meta[stock]" value="<?php echo $custom_data['stock'][0]; ?>" size="10">
    </p>

    <p>
      Vehicle Type:<br>
      <?php foreach ( $vehicle_types as $type ) { ?>
        <input type="radio" name="vehicle_meta[vehicle_type]" value="<?php echo $type; ?>" <?php echo ($custom_data['vehicle_type'][0] == $type) ? 'checked' : ''; ?>>
        <label><?php echo $type; ?></label>
      <?php } ?>
    </p>

    <p>
      Availability Status:<br>
      <input type="radio" name="vehicle_meta[available]" value="Available Now" <?php echo ($custom_data['available'][0] == 'Available Now') ? 'checked' : ''; ?>>
      <label>Available Now</label>

      <input type="radio" name="vehicle_meta[available]" value="Sold" <?php echo ($custom_data['available'][0] == 'Sold') ? 'checked' : ''; ?>>
      <label>Sold</label>
    </p>
  </div>

  <div class="group">
    <p>
      Asking Price:<br>
      $<input type="text" name="vehicle_meta[asking_price]" value="<?php echo $custom_data['asking_price'][0]; ?>" size="10">
    </p>
    <p>
      Sale Price:<br>
      $<input type="text" name="vehicle_meta[sale_price]" value="<?php echo $custom_data['sale_price'][0]; ?>" size="10">
    </p>
    <p>
      Sale Expire:<br>
      <input type="text" name="vehicle_meta[sale_expire]" value="<?php echo $custom_data['sale_expire'][0]; ?>" size="10">
    </p>
  </div>

  <div class="group">
    <p>
      Year:<br>
      <input type="text" name="vehicle_meta[year]" value="<?php echo $custom_data['year'][0]; ?>" size="4">
    </p>
    <p>
      Make:<br>
      <input type="text" name="vehicle_meta[make]" value="<?php echo $custom_data['make'][0]; ?>" size="15">
    </p>
    <p>
      Model:<br>
      <input type="text" name="vehicle_meta[model]" value="<?php echo $custom_data['model'][0]; ?>" size="40">
    </p>
  </div>

  <div class="group">
    <p>
      Doors:<br>
      <input type="text" name="vehicle_meta[doors]" value="<?php echo $custom_data['doors'][0]; ?>" size="2" class="numeric">
    </p>
    <p>
      Mileage:<br>
      <input type="text" name="vehicle_meta[mileage]" value="<?php echo $custom_data['mileage'][0]; ?>" size="6" class="numeric">
    </p>
    <p>
      <label>Exterior Color:</label><br>
      <input type="text" name="vehicle_meta[exterior_color]" value="<?php echo $custom_data['exterior_color'][0]; ?>" size="20">
    </p>
    <p>
      <label>Interior Color:</label><br>
      <input type="text" name="vehicle_meta[interior_color]" value="<?php echo $custom_data['interior_color'][0]; ?>" size="20">
    </p>
  </div>

  <div class="group">
    <p>
      Engine:<br>
      <input type="text" name="vehicle_meta[engine]" value="<?php echo $custom_data['engine'][0]; ?>" size="30">
    </p>
    <p>
      Transmission:<br>
      <input type="text" name="vehicle_meta[transmission]" value="<?php echo $custom_data['transmission'][0]; ?>" size="30">
    </p>
  </div>

  <p>
    Equipment:<br>
    <input type="hidden" id="options-data" name="vehicle_meta[options]" value="<?php echo $custom_data['options'][0]; ?>">
    <ul id="options" class="bundle">
      <?php foreach ( $global_options as $value ) { if (empty($value)) continue; ?>
      <li><input type="checkbox" value="<?php echo $value; ?>" <?php echo in_array($value, $options) ? 'checked="checked"' : ''; ?>> <?php echo $value; ?></li>
      <?php } ?>
    </ul>
    <input type="text" id="add-option-text" name="add-option" value="" size="20">
    <input type="button" id="add-option-button" value="Add">
  </p>

  <?php
}

add_action( 'save_post', 'save_vehicle_postdata' );
function save_vehicle_postdata( $post_id ) {
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
    return;

  // Check permissions
  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) )
      return;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) )
      return;
  }

  // Page Meta
  $custom_data = $_POST['vehicle_meta'];
  foreach ($custom_data as $key=>$value) {
    update_post_meta($post_id, $key, $value);
  }

  // Global Features and Options
  $options = explode('|', $custom_data['options']); sort($options);
  $global_options = explode('|', get_option('ors-vehicle-global-options'));
  $global_options = array_filter(array_unique(array_merge($global_options, $options)), 'strlen');
  sort($global_options);
  update_option('ors-vehicle-global-options', implode('|', $global_options));
}

add_filter("manage_edit-vehicle_columns", "vehicle_edit_columns");
function vehicle_edit_columns($columns){
  $columns = array(
    "cb" => "<input type=\"checkbox\" />",
    "thumbnail" => "Photo",
    "title" => "Headline",
    "ymm" => "Year Make Model",
    "asking_price" => "Price",
    "author" => "Author",
    "date" => "Date Added"
  );

  return $columns;
}

add_action("manage_posts_custom_column",  "vehicle_custom_columns");
function vehicle_custom_columns($column){
  global $post;
  $custom = get_post_custom();

  switch ($column) {
    case "thumbnail":
      if ( has_post_thumbnail( $post->ID ) ) {
        the_post_thumbnail(array(50,50));
      }
      break;
    case "asking_price":
      echo '$' . $custom["asking_price"][0];
      break;
    case "ymm":
      echo "{$custom["year"][0]} {$custom["make"][0]} {$custom["model"][0]}";
      break;
    case "color":
      echo "{$custom["exterior_color"][0]} {$custom["interior_color"][0]}";
      break;
    case "mileage":
      echo $custom["mileage"][0];
      break;
    case "vehicle_type":
      echo $custom["vehicle_type"][0];
      break;
  }
}

/*
 * Custom Query for this post type to sort by price
 * Don't use this sort in Admin
*/
if ( !is_admin() ) add_filter( 'posts_clauses', 'ors_vehicle_query' );
function ors_vehicle_query($clauses) {
  if ( !strstr($clauses['where'], 'vehicle') ) return $clauses;

  global $wpdb, $ors_vehicle_cookies;
  $clauses['fields'] .= ", CAST((select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'asking_price') as decimal) as price";
  $clauses['fields'] .= ", CAST((select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'mileage') as decimal) as mileage";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'vehicle_type') as vehicle_type";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'exterior_color') as exterior_color";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'interior_color') as interior_color";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'make') as make";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'model') as model";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'engine') as engine";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'transmission') as transmission";
  $clauses['fields'] .= ", (select {$wpdb->postmeta}.meta_value from {$wpdb->postmeta} where {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID and {$wpdb->postmeta}.meta_key = 'options') as options";
  $clauses['having'] = array();
  $clauses['orderby'] = '';

  if ( isset($ors_vehicle_cookies['text_search']) and $ors_vehicle_cookies['text_search'] != '' ) {
    $clauses['having']['textsearch']  = "(make like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or post_title like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or post_content like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or model like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or engine like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or transmission like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or exterior_color like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or interior_color like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= " or options like '%{$ors_vehicle_cookies['text_search']}%'";
    $clauses['having']['textsearch'] .= ")";
  }

  $search_params = array('vehicle_type');
  foreach ($search_params as $param) {
    if ( isset($ors_vehicle_cookies[$param]) and $ors_vehicle_cookies[$param] != 'All' and $ors_vehicle_cookies[$param] != '' ) {
      $clauses['having'][] = "$param = '$ors_vehicle_cookies[$param]'";
    }
  }
  if ( !empty($clauses['having']) ) {
    $clauses['where'] .= ' HAVING ' . implode(' and ', $clauses['having']);
  }

  $order_params = array('price' => 'price_near', 'mileage' => 'mileage_near');
  foreach ($order_params as $field => $param) {
    if ( isset($ors_vehicle_cookies[$param]) and $ors_vehicle_cookies[$param] != '' ) {
      $clauses['orderby'] .= ", ABS({$ors_vehicle_cookies[$param]} - $field)";
    }
  }
  if ( $clauses['orderby'] == '' ) $clauses['orderby'] = 'price ASC';
  else $clauses['orderby'] = substr($clauses['orderby'], 2);

  // print "<pre>" . print_r($clauses, 1) . "</pre>";
  return $clauses;
}

/*
 * Search Box
*/
add_filter( 'loop_start', 'ors_vehicle_search_box' );
function ors_vehicle_search_box() {
  if ( get_post_type() != 'vehicle' ) return;

  if ( is_single() ) {
    print '<a class="back-button" href="' . $_SERVER['HTTP_REFERER'] . '">◄ Back to Listings</a>';
    return;
  }

  global $ors_vehicle_cookies;
  $vehicle_types = explode('|', get_option('ors-vehicle-types'));
  ?>
  <div id='ors-vehicle-search-box'>
    <form action="/vehicles/" method="POST">
      Type <select id="vehicle_type" type="text" name="vehicle_type">
        <option <?php echo $ors_vehicle_cookies['vehicle_type'] == 'All' ? 'selected' : ''; ?>>All</option>
        <?php foreach ( $vehicle_types as $type ) { ?>
        <option <?php echo $ors_vehicle_cookies['vehicle_type'] == $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
        <?php } ?>
      </select>
      Price Near <input id="price_near" type="text" name="price_near" size=6 value="<?php echo $ors_vehicle_cookies['price_near'] ?>">
      Mileage Near <input id="mileage_near" type="text" name="mileage_near" size=6 value="<?php echo $ors_vehicle_cookies['mileage_near'] ?>">
      Text <input id="text_search" type="text" name="text_search" size=30 value="<?php echo $ors_vehicle_cookies['text_search'] ?>">
      <input type="hidden" name="post_type" value="vehicle">
      <input type="submit" name="submit" value="Search">
      <input type="submit" name="clear" value="Clear">
    </form>
  </div>
  <?php
}

function ors_vehicle_set_cookies() {
  global $ors_vehicle_cookies;
  $search_params = array('price_near', 'mileage_near', 'vehicle_type', 'exterior_color', 'text_search');

  foreach ($search_params as $param) {
    if ( isset($_POST[$param]) ) {
      if ( $_POST['clear'] == 'Clear' ) $_POST[$param] = '';
      $ors_vehicle_cookies[$param] = $_POST[$param];
      setcookie($param, $_POST[$param], time() + 3600, COOKIEPATH, COOKIE_DOMAIN, false);
    }

    elseif ( isset($_COOKIE[$param]) ) {
      $ors_vehicle_cookies[$param] = $_COOKIE[$param];
    }
  }
}
add_action( 'init', 'ors_vehicle_set_cookies');


/*
 * Fix the content
*/
add_filter( 'the_title', 'vehicle_title_filter' );
function vehicle_title_filter($content) {
  if ( !in_the_loop() or get_post_type() != 'vehicle' ) return $content;

  foreach ( get_post_custom() as $key => $value ) {
    $custom[$key] = $value[0];
  }

  if ( $custom['available'] == 'Sold' ) $sold = true; else $sold = false;

  $output = '';
  $output .= '<span class="price">' . ($sold ? 'Sold' : '$'.$custom['asking_price']) . '</span>';
  if ( $custom['year'] or $custom['make'] or $custom['model'] )
    $output .= '<span class="title">' . "{$custom['year']} {$custom['make']} {$custom['model']}" . '</span>';
  else
    $output .= '<span class="title">' . $content . '</span>';
  $output .= '<span class="vehicle-type">' . $custom['vehicle_type'] . '</span>';

  return $output;
}

add_filter('the_excerpt', 'vehicle_excerpt_filter');
function vehicle_excerpt_filter($content) {
  if ( get_post_type() != 'vehicle' ) return $content;

  foreach ( get_post_custom() as $key => $value ) {
    $custom[$key] = $value[0];
  }

  if ( $custom['available'] == 'Sold' ) $visible = false; else $visible = true;

  $output  = '';

  if ( !has_post_thumbnail( $post->ID ) ) {
    $output .= '<a href="' . get_permalink() . '"><img width="150" height="150" src="' . VEHICLE_PLUGIN_URL . '/nophoto.png" class="attachment-thumbnail wp-post-image" alt="No Photo" title="' . $address . '"></a>';
  }

  $output .= "<ul class='meta'>";
  $output .= "  <li>{$custom['doors']} Door, {$custom['exterior_color']}/{$custom['interior_color']}, {$custom['mileage']} Miles</li>";
  $output .= "</ul>";

  $output .= "<p class='excerpt'>";
  $output .= "  " . $content;
  $output .= "</p>";

  return $output;
}

add_filter('the_content', 'vehicle_content_filter');
function vehicle_content_filter($content) {
  if ( !is_single() or get_post_type() != 'vehicle' ) return $content;

  foreach ( get_post_custom() as $key => $value ) {
    $custom[$key] = $value[0];
  }

  $options = array_filter(explode('|', $custom['options']), 'strlen');

  $output  = get_option('ors-vehicle-gallery-shortcode') . '<br/>';
  $output .= $content;
  $output .= 'Vehicle Details:';
  $output .= "<ul class='meta'>";
  if ( $custom['engine'] )
    $output .= "  <li>Engine: " . $custom['engine'] . '</li>';
  if ( $custom['transmission'] )
    $output .= "  <li>Transmission: " . $custom['transmission'] . '</li>';
  if ( $custom['mileage'] )
    $output .= "  <li>Mileage: " . $custom['mileage'] . ' Miles</li>';
  if ( $custom['doors'] )
    $output .= "  <li>Doors: " . $custom['doors'] . '</li>';
  if ( $custom['exterior_color'] )
    $output .= "  <li>Exterior Color: " . $custom['exterior_color'] . '</li>';
  if ( $custom['interior_color'] )
    $output .= "  <li>Interior Color: " . $custom['interior_color'] . '</li>';
  $output .= "</ul>";

  if ( is_array($options) ) {
    $output .= "<div class='options'>";
    $output .= "Equipment:<br>";
    $output .= '<ul>';
    foreach ( $options as $value ) {
      $output .= '  <li>' . $value . '</li>';
    }
    $output .= '</ul></div>';
  }

  if ( $inquiry = get_option('ors-vehicle-inquiry-form') ) {
    $output .= '<div class="inquiry-form">';
    $output .= '<h2>Send Email Inquiry</h2>';
    $output .= $inquiry;
    $output .= '</div>';
  }

  if ( $tell_a_friend = get_option('ors-vehicle-tell-a-friend-form') ) {
    $output .= '<div class="inquiry-form">';
    $output .= '<h2>Tell-A-Friend</h2>';
    $output .= $tell_a_friend;
    $output .= '</div>';
  }

  return $output;
}
