<?php
/**
 * @package OnpageSEO
 * @version 0.1
 */

if ( ! class_exists( "OnpageSEOAdmin" ) ) {
  
  /**
   * The admin class. This class contains all the functionality related to the admin panel, and settings.
   *
   * @package OnpageSEO
   * @since 0.1
   */
  class OnpageSEOAdmin {
    
    /**
     * The constructor function.
     *
     * @package OnpageSEO
     * @since 0.1
     */
    function OnpageSEOAdmin() {
      global $onpage_seo;
      
      $onpage_seo_taxonomy_terms = new OnpageSEOTaxonomyTerms();
      $onpage_seo_single = new OnpageSEOSingle();
      $onpage_seo_authors = new OnpageSEOAuthors();
      $onpage_seo_categories = new OnpageSEOCategories();
      
      // Register javascript
      wp_register_script( 'onpage-seo', plugins_url('/javascript/onpage-seo.js', dirname(__FILE__) ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'postbox' ) );
      
      // Actions
      add_action('admin_menu', array( $this, 'register_admin_menu_items' ) );
      
      // Filters
      
    }
    
    /**
     * Gets called on plugin initialization. Creates a database table.
     * Uninstall gets done by uninstall.php
     *
     * @since 0.1
     */
    public static function install() {
      global $wpdb;
      
      $table_name = OnpageSEO::$table_name;
      
      // Create the database if it isn't found.
      if ( $wpdb->get_var("show tables like '{$wpdb->prefix}{$table_name}'" ) != "{$wpdb->prefix}{$table_name}" ) {
        $sql = "CREATE TABLE {$wpdb->prefix}{$table_name} (
                  ID bigint(20) unsigned NOT NULL auto_increment,
                  meta_type varchar(50) NOT NULL default 'title',
                  entity_type varchar(50) NOT NULL default 'general',
                  entity_id bigint(20) unsigned default 0,
                  meta_value longtext,
                  PRIMARY KEY  (ID),
                  UNIQUE KEY  unique_meta (meta_type,entity_type,entity_id)
                )";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Adds the onpage_seo_database_version to the database.
        add_option("onpage_seo_database_version", OnpageSEO::$database_version);
        
        // Add some default options.
        $wpdb->query( "INSERT INTO {$wpdb->prefix}{$table_name} (meta_type, entity_type, entity_id, meta_value) VALUES('title', 'general', 0, '%blog_title% | %blog_description%');" );
        $wpdb->query( "INSERT INTO {$wpdb->prefix}{$table_name} (meta_type, entity_type, entity_id, meta_value) VALUES('title', 'posts', 0, '%post_title% | %blog_title%');" );
        $wpdb->query( "INSERT INTO {$wpdb->prefix}{$table_name} (meta_type, entity_type, entity_id, meta_value) VALUES('title', 'pages', 0, '%post_title% | %blog_title%');" );
        $wpdb->query( "INSERT INTO {$wpdb->prefix}{$table_name} (meta_type, entity_type, entity_id, meta_value) VALUES('title', 'categories', 0, '%category_title% | %blog_title%');" );
        $wpdb->query( "INSERT INTO {$wpdb->prefix}{$table_name} (meta_type, entity_type, entity_id, meta_value) VALUES('robots', 'general', 0, 'index, follow');" );
        
      }
    }
    
    /**
     * Adds a link in the admin menu under the Options menu item.
     * 
     * @package OnpageSEO
     * @since   0.1
     */
    function register_admin_menu_items() {
      $options_page = add_options_page( "OnpageSEO", "OnpageSEO", "manage_options", "onpage-seo", array( $this, "options_page_html" ) );
      add_action( "admin_print_scripts-{$options_page}", array( $this, 'load_javascript' ) );
      add_action( "admin_print_styles-{$options_page}", array( $this, 'load_stylesheets' ) );
    }
    
    /**
     * Loads necessary javascript files.
     *
     * @package OnpageSEO
     * @since   0.1
     */
    function load_javascript() {
      wp_enqueue_script( 'onpage-seo' );
      wp_enqueue_script( 'postbox' );
    }
    
    /**
     * Loads necessary javascript files.
     *
     * @package OnpageSEO
     * @since   0.1
     */
    function load_stylesheets() {
      echo "<link rel='stylesheet' href='" . plugins_url('/stylesheets/onpage-seo.css', dirname(__FILE__) ) . "' type='text/css' media='all'>\n";
    }
    
    /**
     * Prints out the html for the options page. Loops through the levels and entity types and prints a form for each one.
     * 
     * @since 0.1
     */
    function options_page_html( ) {
      global $onpage_seo;
      
      // Check if we need to save data.
      if ( isset( $_POST['submit'] ) ) {
        $message = $this->save_form( $_POST[$onpage_seo::$post_array], array( "entity_type" => $_POST[OnpageSEO::$post_array]['entity_type'] ) );
      }
      ?>
      
      <div class="wrap">
        <div class="icon32" id="icon-options-general"><br></div>
        <h2>OnpageSEO</h2>
        
        <?php if ( isset( $message ) ) : ?>
          <div class="updated fade" id="message"><p><?php echo $message; ?></p></div>
        <?php endif; ?>
        
        <p>
          OnpageSEO will enable you to set custom meta-data on your posts, pages etc, such as title, description, keywords and indexing options.
          OnpageSEO uses a hierarchy of two levels when determining what meta to print to the client. The most specific settings found will be used.
          If we take a post with <strong>ID 5</strong> as an example, OnpageSEO will first look for <strong>level 2</strong> meta-data related to that <strong>post</strong>.
          If none is found, it will look for meta data related to <strong>posts, level 1</strong>.
        </p>
        <ol>
          <li><strong>Level 1</strong> contains settings related to a special type of entities, such as posts, wordpress pages or categories.</li>
          <li><strong>Level 2</strong> is the most specific level. These settings are connected to a specific post, page or category.</li>
        </ol>
        
        <h4>Available variables</h4>
        <ul class="onpage-seo-variables">
          <li><strong>%blog_title%</strong> - The blog title, as set in the wordpress settings.</li>
          <li><strong>%blog_description%</strong> - The blog description, as set in the wordpress settings.</li>
          <li><strong>%post_title%</strong> - The post or page title (only available on <strong>level 1: posts</strong> and <strong>level 2: post</strong>).</li>
          <li><strong>%post_excerpt%</strong> - The excerpt of a post or page, automatically generated if there is none.</li>
          <li><strong>%category_title%</strong> - The post title (only available on <strong>level 1: categories</strong> and <strong>level 2: category</strong>).</li>
          <li><strong>%request_words%</strong> - The request URI in human readable form.</li>
          <li><strong>%date%</strong> - Depending on the page being viewed, this will print the archive date or the publish date.</li>
          <li><strong>%taxonomy%</strong> - The taxonomy name.</li>
          <li><strong>%term%</strong> - The taxonomy term name.</li>
          <li><strong>%author_name%</strong> - The author name, either in an author archive or the post author.</li>
        </ul>
        
        <div class="metabox-holder">
          <div id="normal-sortables" class="meta-box-sortables ui-sortable">
      
      <?php
      foreach ( $onpage_seo->entity_types as $singular => $plural ) {
        $this->form_html( 1, $plural );
      }
      $this->form_html( 2, "home" );
      $this->form_html( 2, "404" );
      
      echo "</div></div></div>";
    }
    
    /**
     * Prints out the html for an option page form.
     *
     * @param   integer   $level        The setting level, from 1 to 3.
     * @param   string    $entity_type  The entity_type this form belong to, e.g. posts, pages, a single page.
     * @since   0.1
     */
    function form_html( $level, $entity_type ) {
      global $onpage_seo;
      
    ?>
    
      <div class="postbox closed">
        <div title="Click to toggle" class="handlediv"><br></div><h3 class="handle"><span><?php echo sprintf ( __( "Level %d", 'onpage-seo' ), $level ) . " - " . ucwords( $entity_type ); ?></span></h3>
        <div class="inside">
          <form id="onpage-seo-<?php echo "{$entity_type}"; ?>-settings" class="onpage-seo-settings" method="post">
            <?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
            <?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
            <input type="hidden" value="<?php echo "{$level}"; ?>" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo::$level}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo::$level}]"; ?>">
            <input type="hidden" value="<?php echo "{$entity_type}"; ?>" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo::$entity_type}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo::$entity_type}]"; ?>">
            <table class="form-table">
              <tbody>
        
                <tr class="form-field form-required">
                  <th valign="top" scope="row">
                    <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['title']}"; ?>"><?php echo __( 'Page title', 'onpage-seo' ); ?></label>
                  </th>
                  <td>
                    <input type="text" size="40" value="<?php echo $onpage_seo->find_meta( $onpage_seo->meta_types['title'], array( "entity_type" => $entity_type ) ); ?>" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['title']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['title']}]"; ?>">
                    <p class="description"><?php echo __( 'The page title will appear in the search results. It should describe your page in a short sentence.', 'onpage-seo' ); ?></p>
                  </td>
                </tr>
        
                <tr class="form-field form-required">
                  <th valign="top" scope="row">
                    <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['description']}"; ?>"><?php echo __( 'Page description', 'onpage-seo' ); ?></label>
                  </th>
                  <td>
                    <textarea style="width: 97%;" cols="50" rows="5" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['description']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['description']}]"; ?>"><?php echo $onpage_seo->find_meta( $onpage_seo->meta_types['description'], array( "entity_type" => $entity_type ) ); ?></textarea>
                    <p class="description"><?php echo __( 'A short description will tell the search engines a little about your page.', 'onpage-seo' ); ?></p>
                  </td>
                </tr>
        
                <tr class="form-field form-required">
                  <th valign="top" scope="row">
                    <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['keywords']}"; ?>"><?php echo __( 'Page keywords', 'onpage-seo' ); ?></label>
                  </th>
                  <td>
                    <input type="text" size="40" value="<?php echo $onpage_seo->find_meta( $onpage_seo->meta_types['keywords'], array( "entity_type" => $entity_type ) ); ?>" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['keywords']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['keywords']}]"; ?>">
                    <p class="description"><?php echo __( 'Enter your most important keywords here, separated by a comma in lowercase.', 'onpage-seo' ); ?></p>
                  </td>
                </tr>
        
                <tr class="form-field form-required">
                  <th valign="top" scope="row">
                    <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['extra_meta']}"; ?>"><?php echo __( 'Extra meta-data', 'onpage-seo' ); ?></label>
                  </th>
                  <td>
                    <textarea style="width: 97%;" cols="50" rows="5" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['extra_meta']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['extra_meta']}]"; ?>"><?php echo $onpage_seo->find_meta( $onpage_seo->meta_types['extra_meta'], array( "entity_type" => $entity_type ) ); ?></textarea>
                    <p class="description"><?php echo __( 'Enter any custom meta-data here. It will be added to the page head.', 'onpage-seo' ); ?></p>
                  </td>
                </tr>
        
                <?php
                  $index_setting = $onpage_seo->find_meta( $onpage_seo->meta_types['robots'], array( "entity_type" => $entity_type) );
                  switch ( $index_setting ) {
                    default:
                    case "index, follow":
                      $index_follow = "checked";
                      break;
                    case "noindex, follow":
                      $noindex_follow = "checked";
                      break;
                    case "index, nofollow":
                      $index_nofollow = "checked";
                      break;
                    case "noindex, nofollow":
                      $noindex_nofollow = "checked";
                      break;
                  }
                ?>
                <tr class="">
                  <th valign="top" scope="row">
                    <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>"><?php echo __( 'Indexing options', 'onpage-seo' ); ?></label>
                  </th>
                  <td>
                    <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $index_follow ) ) echo $index_follow; ?> value="index, follow"> Index, Follow<br>
                    <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $noindex_follow ) ) echo $noindex_follow; ?> value="noindex, follow"> No-index, Follow<br>
                    <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $index_nofollow ) ) echo $index_nofollow; ?> value="index, nofollow"> Index, No-follow<br>
                    <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $noindex_nofollow ) ) echo $noindex_nofollow; ?> value="noindex, nofollow"> No-index, No-follow<br>
                    <span class="description"><?php echo __( 'This option will determine wether search engines will index the page.', 'onpage-seo' ); ?></span>
                  </td>
                </tr>
        
                <tr class="form-field">
                  <th></th>
                  <td>
                    <input class="button-primary" type="submit" name="submit" value="Submit">
                  </td>
                </tr>
              </tbody>
            </table>
          </form>
        </div>
      </div>

    <?php }
    
    /**
     * Saves the option page form data to database.
     *
     * @param   array   $post_array   The array of POST data to be saved.
     * @since   0.1
     * @return  string                A message to be displayed on the options page.
     */
    function save_form( $post_array, $entity ) {
      global $onpage_seo;
      
      foreach ( $onpage_seo->meta_types as $meta_type ) {
        if ( isset( $post_array[$meta_type])) {
          // Save the meta option. $entity_id is always null since the option page only deals with level 1 and 2 settings.
          $this->save_meta_data( $meta_type, $entity, $post_array[$meta_type] );
        }
      }
      
      return ucwords( $entity['entity_type'] ) . " updated.";
    }
    
    /**
     * Saves a meta-data entry to the database.
     *
     * @param   string  $meta_type    The type of meta-data to save, e.g. title, description.
     * @param   string  $entity_type  The type of entity this data belongs to, e.g. posts, pages, a single post.
     * @param   integer $entity_id    The ID of the entity this data belongs to. Null if none.
     * @param   string  $meta_value   The meta-data to save.
     * @since   0.1
     */
    function save_meta_data( $meta_type, $entity, $meta_value ) {
      global $wpdb, $onpage_seo;
      
      // If there already is a record in the database that we need to update, start by getting the meta_id.
      $meta_id = $this->get_meta_id( $meta_type, $entity );
      
      $data = array(
        "meta_type" => $meta_type,
        "entity_type" => $entity["entity_type"],
        "entity_id" => ( isset( $entity["entity_id"] ) ) ? $entity["entity_id"] : null,
        "meta_value" => $meta_value
      );
      $formats = array(
        "%s", "%s", "%d", "%s"
      );
      
      if ( $meta_id == null )
        $wpdb->insert( "{$wpdb->prefix}{$onpage_seo::$table_name}", $data, $formats );
      else
        $wpdb->update( "{$wpdb->prefix}{$onpage_seo::$table_name}", $data, array( "ID" => $meta_id ), $formats, "%d" );
    }
    
    function delete_meta_data( $meta_id ) { global $wpdb, $onpage_seo;
      $sql = $wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}{$onpage_seo::$table_name}
        WHERE ID = %d", $meta_id
      );
      return $wpdb->query( $sql );
    }
    
    /**
     * Searches the database and returns a meta-data ID if found. This is used to determine if a value should be inserted or updated.
     *
     * @param   string  $meta_type    The type of meta-data to look for, e.g. title, description.
     * @param   string  $entity_type  The type of entity this data belongs to, e.g. posts, pages, a single post.
     * @param   integer $entity_id    The ID of the entity this data belongs to. Null if none.
     * @return  integer|NULL          The ID, if found, null if not.
     * @since   0.1
     */
    function get_meta_id( $meta_type, $entity ) {
      global $wpdb, $onpage_seo;
      
      $sql = $wpdb->prepare(
        "SELECT ID
        FROM {$wpdb->prefix}{$onpage_seo::$table_name}
        WHERE meta_type = %s
        AND entity_type = %s
        AND entity_id = %d",
        $meta_type,
        $entity["entity_type"],
        ( isset( $entity["entity_id"] ) ) ? $entity["entity_id"] : null
      );
              
      $meta_id = $wpdb->get_var( $sql );
      
      return $meta_id;
    }
    
  } # class OnpageSEOAdmin
  
}

require_once('entity-types/onpage-seo-single.php');
require_once('entity-types/onpage-seo-authors.php');
require_once('entity-types/onpage-seo-categories.php');
require_once('entity-types/onpage-seo-taxonomy-terms.php');

?>