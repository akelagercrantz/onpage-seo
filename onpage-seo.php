<?php
/**
 * @package OnpageSEO
 * @version 0.1
 */
/*
Plugin Name: OnpageSEO
Plugin URI: http://blog.norbyit.se/tag/onpage-seo
Description: A minimalistic but functionality-specked SEO plugin for wordpress.
Version: 0.1
Author: Åke Lagercrantz
Author URI: http://norbyit.se/
*/

/*  Copyright 2010  Åke Lagercrantz  (email : ake@norbyit.se)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( "OnpageSEO" ) ) {
  
  /**
   * The main class. This class contains all the functionality of the plugin.
   *
   * @package OnpageSEO
   * @since 0.1
   */
  class OnpageSEO {
    public static $table_name = "onpage_seo_meta";
    public static $database_version = 1;
    
    public static $post_array       = 'onpage_seo';
    public static $level            = 'level';
    public static $entity_type      = 'entity_type';
    
    public $entity_types = array(
      "post" => "posts",
      "page" => "pages",
      "category" => "categories",
      "archive" => "archives",
      "tag" => "tags",
      "author" => "authors"
    );
    
    public $meta_types = array(
      "title" => "title",
      "description" => "description",
      "keywords" => "keywords",
      "extra_meta" => "extra_meta",
      "robots" => "robots"
    );
    
    public $admin = null;
    
    /**
     * The constructor function. Load textdomain, add actions to some hooks etc.
     *
     * @package OnpageSEO
     * @since 0.1
     */
    function OnpageSEO() {
      // Load textdomain with language files.
      $path_to_translations = plugin_basename( dirname( __FILE__ ) . '/translations' );
      load_plugin_textdomain( 'onpage_seo', '', $path_to_translations );
      
      // Add support for any custom post types available.
      $this->add_custom_post_types();
      
      // Actions
      do_action( "onpage_seo_add_entity_types" );
      do_action( "onpage_seo_add_meta_types" );
      
      // Filters
      add_filter( 'onpage_seo_prepare_title',         array( $this, 'replace_meta_information' ) );
      add_filter( 'onpage_seo_prepare_description',   array( $this, 'replace_meta_information' ) );
      add_filter( 'onpage_seo_prepare_keywords',      array( $this, 'replace_meta_information' ) );
      add_filter( 'onpage_seo_prepare_extra_meta',    array( $this, 'replace_meta_information' ) );
      add_filter( 'onpage_seo_prepare_title',         array( $this, 'add_paged_information' ) );
      add_filter( 'onpage_seo_prepare_description',   array( $this, 'add_paged_information' ) );
      add_filter( 'onpage_seo_prepare_title',         "esc_html" );
      add_filter( 'onpage_seo_prepare_description',   "esc_html" );
      add_filter( 'onpage_seo_prepare_keywords',      "esc_html" );
      
      // Should we really force lowercase in keywords? Plus, strtolower doesn't work with unicode characters.
      #add_filter( 'onpage_seo_prepare_keywords',      "strtolower" ); 
    }
    
    /**
     * Adds all custom post types to the entity-types array.
     * 
     * @package OnpageSEO
     * @since   0.1
     */
    function add_custom_post_types() {
      /*
      
      for each custom post type
      $onpage_seo->entity_types add custom post type
      
      */
    }
    
    /**
     * Returns a meta value from the database.
     *
     * @package OnpageSEO
     * @since   0.1
     * @param   integer   $meta_id  The ID of the meta_data in the database.
     * @return  mixed               The meta value as a string. Null if not found.
     */
    function get_meta( $meta_id ) { global $wpdb;
      return stripslashes( $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}{$this::$table_name} WHERE ID = %d", $meta_id ) ) );
    }
    
    /**
     * Finds a meta value in the database based on a number of attributes.
     *
     * @package OnpageSEO
     * @since   0.1
     * @param   string  $meta_type          The meta type, one of title, description etc.
     * @param   array   $entity             The entity, an array containing the entity_type and the entity_id.
     * @param   string  $entity_id          If the entity is singular, the ID of the entity.
     * @param   boolean $return_preceding   Wether to return the meta value for the preceding entity_type if none is found. Default is false.
     * @return  mixed                       The meta value as a string. Null if not found.
     */
    function find_meta( $meta_type, $entity, $return_from_level_above = false ) { global $wpdb;
      $sql = $wpdb->prepare( 
        "SELECT meta_value
         FROM {$wpdb->prefix}{$this::$table_name}
         WHERE meta_type = %s
         AND entity_type = %s
         AND entity_id = %d",
         $meta_type,
         $entity["entity_type"],
         ( isset( $entity["entity_id"] ) ) ? $entity["entity_id"] : null
      );
      
      $meta_value = $wpdb->get_var( $sql );
      
      // If the value is not found, and $return_from_level_above is true, check the level above for a value.
      if ( is_null( $meta_value ) and $return_from_level_above and !is_null( $this->entity_type_for_level_above( $entity["entity_type"] ) ) ) {
        // We search for a new meta based on the entity type for the level above. We also strip the entity_id.
        return $this->find_meta( $meta_type, array( "entity_type" => $this->entity_type_for_level_above( $entity["entity_type"] ) ), $return_from_level_above );
      }
      else
        return stripslashes( $meta_value );
    }
    
    /**
     * Returns the entity_type used for the level above. For instance, the entity_type used in the level above post is posts.
     *
     * @package OnpageSEO
     * @since   0.1
     * @param   string  $entity_type  We're looking for an entity_type in the level above this entity_type.
     * @return  mixed                 Returns a string with the entity_type, or null if there is no level above.
     */
    function entity_type_for_level_above( $entity_type ) {
      // The general entity_type is the topmost level. There is no level above.
      if ( $entity_type == "general" )
        return null;
      
      if ( isset( $this->entity_types[$entity_type] ) )
        return $this->entity_types[$entity_type];
      else
        return "general";
    }
    
    /**
     * Determines the current entity being viewed by the visitor.
     * 
     * @package OnpageSEO
     * @since   0.1
     * @return  array       An array containing the entity_type and possibly the entity_id.
     */
    function entity_being_viewed() { global $post;
      if ( is_single() or is_page() )
        // We're viewing a single entity, the entity_type will be a post_type.
        return array( "entity_type" => get_post_type(), "entity_id" => $post->ID );
      elseif ( is_category() ) {
        // We're viewing a category page, the entity_type is simply "category".
        $categories = get_the_category();
        $category = $categories[0];
        return array( "entity_type" => "category", "entity_id" => $category->term_id );
      }
      elseif ( is_404() )
        return array( "entity_type" => "404" );
      elseif ( is_tag() ) {
        global $wp_query;
        $tag = $wp_query->get_queried_object();
        return array( "entity_type" => "tag", "entity_id" => $tag->term_id );
      }
      elseif ( is_author() ) {
        global $author_name, $author;
        $curauth = ( isset( $_GET['author_name'] ) ) ? get_user_by( 'slug', $author_name ) : get_userdata( intval( $author ) );
        return array( "entity_type" => "author", "entity_id" => $curauth->ID );
      }
      elseif ( is_archive() )
        return array( "entity_type" => "archive" );
      else
        // We're viewing something else. We will use our default option here, "general".
        return array( "entity_type" => "general" );
    }
    
    /**
     * Replaces special keywords in the meta value with dynamic information, like post title etc.
     *
     * @package OnpageSEO
     * @since   0.1
     * @param   string  $meta_value   The meta value to replace in.
     * @return  string                The meta value with information replaced.
     */
    function replace_meta_information( $meta_value ) { global $post;
      $meta_value =   str_replace( '%blog_title%',        get_bloginfo('name'),             $meta_value );
      $meta_value =   str_replace( '%blog_description%',  get_bloginfo('description'),      $meta_value );
      
      if ( is_single() || is_page() )
        $meta_value = str_replace( '%post_title%',        $post->post_title,                $meta_value );
      else
        $meta_value = str_replace( '%post_title%',        "",                               $meta_value );
      
      if ( is_category() ) {
        $categories = get_the_category();
        $category = $categories[0];
        $meta_value = str_replace( '%category_title%',    $category->cat_name,              $meta_value );
      }
      else
        $meta_value = str_replace( '%category_title%',    "",                               $meta_value );
      
      $meta_value =   str_replace( '%request_words%',     $this->humanize_request_uri(),    $meta_value );
      
      if ( is_archive() )
        $meta_value = str_replace( '%date%',              wp_title( '', false ),            $meta_value );
      elseif ( is_single() || is_page() )
        $meta_value = str_replace( '%date%',              $post->post_date,                 $meta_value );
      else
        $meta_value = str_replace( '%date%',              date(get_option('date_format')),  $meta_value );
      
      if ( is_tag() )
        $meta_value = str_replace( '%tag%',               single_tag_title( '', false ),    $meta_value );
      else
        $meta_value = str_replace( '%tags',               "",                               $meta_value );
      
      return $meta_value;
    }
    
    /**
     * Appends page number to meta_value if needed.
     *
     * @package OnpageSEO
     * @since   0.1
     * @param   string  $meta_value   The meta_value to append to.
     * @return  string                The meta_value with the page number appended.
     */
    function add_paged_information( $meta_value ) { global $paged, $page;
      if ( $paged >= 2 || $page >= 2 )
        $meta_value .= ' | ' . sprintf( __( 'Page %s', 'onpage-seo' ), max( $paged, $page ) );
    
      return $meta_value;
    }
    
    /**
     * Converts a request uri to human readable form.
     * 
     * @package OnpageSEO
     * @since   0.1
     * @return  string      The converted uri.
     */
    function humanize_request_uri() {
      $search = array( ".html", ".htm", ".", "/" );
      $replace = " ";
      $request_uri = str_replace( $search, $replace, esc_html ( $_SERVER['REQUEST_URI'] ) );
      
      $human_readable = "";
      foreach ( explode( ' ', $request_uri ) as $word )
        $human_readable .= ucwords( $word ) . " ";
      
      return trim( $human_readable );
    }
    
    /**
     * Prints the page title.
     * 
     * @package OnpageSEO
     * @since   0.1
     */
    function title() {
      $title = $this->find_meta( $this->meta_types['title'], $this->entity_being_viewed(), true );
      if ( !empty( $title ) )
        echo "<title>" . apply_filters( "onpage_seo_prepare_title", $title ) . "</title>\n";
    }
    
    /**
     * Prints the page description.
     * 
     * @package OnpageSEO
     * @since   0.1
     */
    function description() {
      $description = $this->find_meta( $this->meta_types['description'], $this->entity_being_viewed(), true );
      if ( !empty( $description ) )
        echo "<meta name=\"description\" content=\"" . apply_filters( "onpage_seo_prepare_description", $description ) . "\">\n";
    }
    
    /**
     * Prints the page keywords.
     * 
     * @package OnpageSEO
     * @since   0.1
     */
    function keywords() {
      $keywords = $this->find_meta( $this->meta_types['keywords'], $this->entity_being_viewed(), true );
      if ( !empty( $keywords ) )
        echo "<meta name=\"keywords\" content=\"" . apply_filters( "onpage_seo_prepare_keywords", $keywords ) . "\">\n";
    }
    
    /**
     * Prints any extra meta-data.
     * 
     * @package OnpageSEO
     * @since   0.1
     */
    function extra_meta() {
      $extra_meta = $this->find_meta( $this->meta_types['extra_meta'], $this->entity_being_viewed(), true );
      if ( !empty( $extra_meta ) )
        echo apply_filters( "onpage_seo_prepare_extra_meta", $extra_meta ) . "\n";
    }
    
    /**
     * Prints the robots meta value, such as indexing and follow options.
     * 
     * @package OnpageSEO
     * @since   0.1
     */
    function robots() {
      $robots = $this->find_meta( $this->meta_types['robots'], $this->entity_being_viewed(), true );
      if ( !empty( $robots ) )
        echo "<meta name=\"robots\" content=\"" . apply_filters( "onpage_seo_prepare_robots", $robots ) . "\">\n";
    }
    
  } # class OnpageSEO
  
}

/**
 * We initialize the OnpageSEO class, and reference it in a global variable.
 *
 * @package OnpageSEO
 * @since 0.1
 * @global    object    $onpage_seo
 */
function onpage_seo_init() {
  global $onpage_seo;
  
  $onpage_seo = new OnpageSEO();
  
  if ( is_admin() ) {
    $onpage_seo->admin = new OnpageSEOAdmin();
  }
}

/**
 * Wrapper for the title function.
 *
 * @package OnpageSEO
 * @since   0.1
 */
function onpage_seo_title() { global $onpage_seo;
  $onpage_seo->title();
}

/**
 * Wrapper for the description function.
 *
 * @package OnpageSEO
 * @since   0.1
 */
function onpage_seo_description() { global $onpage_seo;
  $onpage_seo->description();
}

/**
 * Wrapper for the keywords function.
 *
 * @package OnpageSEO
 * @since   0.1
 */
function onpage_seo_keywords() { global $onpage_seo;
  $onpage_seo->keywords();
}

/**
 * Wrapper for the extra meta function.
 *
 * @package OnpageSEO
 * @since   0.1
 */
function onpage_seo_extra_meta() { global $onpage_seo;
  $onpage_seo->extra_meta();
}

/**
 * Wrapper for the robots options function.
 *
 * @package OnpageSEO
 * @since   0.1
 */
function onpage_seo_robots() { global $onpage_seo;
  $onpage_seo->indexing();
}

/**
 * Wrapper for all the OnpageSEO meta, like title, description etc.
 * 
 * @package OnpageSEO
 * @since   0.1
 */
function onpage_seo_meta() { global $onpage_seo;
  $onpage_seo->title();
  $onpage_seo->description();
  $onpage_seo->keywords();
  $onpage_seo->extra_meta();
  $onpage_seo->robots();
}

// Start the whole thing up by registering the init function. Also load some extra files if an administration page is being displayed.
if ( is_admin() ) {
  require_once('php/onpage-seo-admin.php');
  register_activation_hook( __FILE__, array( "OnpageSEOAdmin", "install" ) );
}
add_action( 'init', 'onpage_seo_init' );

?>