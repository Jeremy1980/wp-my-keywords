<?php
/**
* Plugin Name: My Keywords
* Plugin URI: https://github.com/jeremy1980/wp-my-keywords
* Description: Wtyczka posiada swoje menu w panelu administracyjnym. W Ustawieniach wtyczki można utworzyć nowy shortcode o dowolnej nazwie. Użycie tego shortcode wyświetli zawartość zadeklarowaną w polu textarea.
* Version: 1.0
* Author: Jarema Czajkowski
* Text Domain: my-keywords
* Author URI: https://github.com/jeremy1980
* Contributors: Jarema Czajkowski
* License: MIT License
* License URI: https://opensource.org/license/mit
*/


require_once(ABSPATH.'wp-admin/includes/upgrade.php');

class MyKeywords {
  private $content;
  private $charset;
  private $table;

  public function __construct() {
    global $wpdb;

    // Get the current content from the database. What you do with
    // this information is up to you.
    
    $this->charset = $wpdb->get_charset_collate();
    $this->table = $wpdb->prefix . 'mykeywords';

    $this->content = $wpdb->get_row("SELECT * FROM `$this->table`", ARRAY_A, 0);
  }
  
  // Add our WP admin hooks.
  public function load() {
    add_action('admin_menu', [$this, 'add_plugin_options_page']);
    add_action('admin_init', [$this, 'add_plugin_settings']);

	if (is_string($this->content['shortcode']))
    add_shortcode($this->content['shortcode'], [$this, 'my_keyword_content']); 
  }

  // Add our plugin's option page to the WP admin menu.
  public function add_plugin_options_page() {
    add_options_page(
      'Złota myśl',
      'Złota myśl',
      'manage_options',
      'ex',
      [$this, 'render_admin_page']
    );
  }  


  // Render our plugin's option page.
  public function render_admin_page() {
    ?>
    <div class="wrap">
      <form method="post" action="options.php">
        <?php
        settings_fields('ex');
        do_settings_sections('ex');
        submit_button();
        ?>
      </form>
    </div>
    <?php
  }

  // Initialize our plugin's settings.
  public function add_plugin_settings() {
    register_setting('ex', 'ex_field', [$this, 'field_callback']);

    add_settings_section(
      'ex_settings',
      'Moje złote myśli',
      [$this, 'render_section'],
      'ex'
    );

    add_settings_field(
      'ex_field_shortcode',
      'Twoja nazwa',
      [$this, 'render_field_shortcode'],
      'ex',
      'ex_settings'
    );

    add_settings_field(
      'ex_field',
      'Twoja zawartość',
      [$this, 'render_field'],
      'ex',
      'ex_settings'
    );
  }  

  // Render content for our plugin's section.
  public function render_section() {
    print '';
  }  

  // Render the field.
  public function render_field_shortcode() {
    printf(
      '<input type="text" id="shortcode" name="ex_field[shortcode]" value="%s" />',
      isset($this->content['shortcode']) ? esc_attr($this->content['shortcode']) : ''
    );
  }  

  public function render_field() {
    printf(
      '<input type="text" id="keyword" name="ex_field[keyword]" value="%s" />',
      isset($this->content['keyword']) ? esc_attr($this->content['keyword']) : ''
    );
  }  

  // Sanitize input from our plugin's option form and validate the content.
  public function field_callback($options) {
    global $wpdb;

    $metakey = $options['shortcode'];
    $metavalue = $options['keyword'];
    $metaid = $this->content['id'];

    if (empty($metakey)) {
      add_settings_error('ex_field_shortcode', esc_attr('settings_updated'), 'Nazwa uchwytu, musi być znana.', 'error');
      return;
    }

    if (empty($metavalue)) {
      add_settings_error('ex_field', esc_attr('settings_updated'), 'Wpisz twoje przesłanie dla odwiedzających.', 'error');
      return;
    }    

    $result = $wpdb->query(
      $wpdb->prepare(
        "INSERT INTO $this->table ( `id`, `shortcode` , `keyword` ) VALUES ( %d, %s, %s ) ON DUPLICATE KEY UPDATE `shortcode` = %s , `keyword` = %s",
        array($metaid,$metakey,$metavalue,$metakey,$metavalue)
      )
    );

    if (empty($result)) {
      add_settings_error(null, esc_attr('settings_updated'), 'Wystąpił problem z zapisaniem danych.' . $wpdb->last_query, 'error');
    }

    return;
  }

  function my_keyword_content($atts) {
      return $this->content['keyword'];
  }

}


register_activation_hook(__FILE__, function() {
    global $wpdb;

    $table = $wpdb->prefix . 'mykeywords';
    $charsetCollate = $wpdb->get_charset_collate();

    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        $sql = "CREATE TABLE $table (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `shortcode` VARCHAR(50) NOT NULL,
            `keyword` VARCHAR(100) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE INDEX unique_keyword (keyword,shortcode)
        ) $charsetCollate;";

        dbDelta($sql);
    }
});

function mykeywords_uninstall() {
  // drop a custom database table
  global $wpdb;
  $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mykeywords" );  
}

register_uninstall_hook(__FILE__, 'mykeywords_uninstall');

add_action( 'plugins_loaded', function() {
  // Load our plugin within the WP admin dashboard.
  // Load our plugin on frontside.
  $plugin = new MyKeywords();
  $plugin->load();
});

?>
