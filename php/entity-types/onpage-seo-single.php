<?php
/**
 * @package OnpageSEO
 * @version 0.1
 */

if ( ! class_exists( "OnpageSEOSingle" ) ) {
  
  /**
   * The single class. This class contains all the admin functionality
   * related to single entity types, such as posts, pages and custom post types.
   *
   * @package OnpageSEO
   * @since   0.1
   */
  class OnpageSEOSingle {
    
    /**
     * The constructor function.
     *
     * @package OnpageSEO
     * @since   0.1
     */
    function OnpageSEOSingle() {
      global $onpage_seo;
      
      add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
      add_action( 'save_post',      array( $this, 'save_post_meta' ) );
      add_action( 'delete_post',    array( $this, 'delete_post_meta' ) );
      
    }
    
    /**
     * Adds the meta box to the editing page.
     *
     * @package OnpageSEO
     * @since   0.1
     */
    function add_meta_box() {
      
      
      add_meta_box( 'onpage_seo', "OnpageSEO", array( $this, 'form_html' ), "post", "advanced", "high", array( "entity_type" => "post" ) );
      add_meta_box( 'onpage_seo', "OnpageSEO", array( $this, 'form_html' ), "page", "advanced", "high", array( "entity_type" => "page" ) );
    }
    
    /**
     * Prints the onpage-seo related form items on the Add post and Edit post page.
     *
     * @package OnpageSEO
     * @since   0.1
     */
    function form_html( $post, $metabox ) { global $onpage_seo;
      $entity_type = $metabox['args']['entity_type'];
      $inherit_entity_type = $onpage_seo->entity_types[$entity_type];
      $entity_id = $post->ID; ?>
      
      <p>
        <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['title']}"; ?>"><?php echo __( 'Page title', 'onpage-seo' ); ?>:</label><br>
        <input id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['title']}"; ?>" type="text" size="40" value="<?php echo $onpage_seo->find_meta( $onpage_seo->meta_types['title'], array( "entity_type" => $entity_type, "entity_id" => $entity_id ) ); ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['title']}]"; ?>">
        <br>
        <?php echo __('The page title will appear in the search results. It should describe your page in a short sentence.')?>
        <br><?php echo sprintf( __( "Leave blank to inherit: <strong>%s</strong>", "onpage-seo" ), esc_html( $onpage_seo->find_meta( $onpage_seo->meta_types['title'], array( "entity_type" => $inherit_entity_type ), true ) ) ); ?>
      </p>
      
      <p>
        <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['description']}"; ?>"><?php echo __( 'Page meta-description', 'onpage-seo' ); ?>:</label><br>
        <textarea style="width: 97%;" cols="50" rows="5" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['description']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['description']}]"; ?>"><?php echo $onpage_seo->find_meta( $onpage_seo->meta_types['description'], array( "entity_type" => $entity_type, "entity_id" => $entity_id ) ); ?></textarea>
        <br>
        <?php echo __( 'A short description will tell the search engines a little about your page.', 'onpage-seo' ); ?>
        <br><?php echo sprintf( __( "Leave blank to inherit: <strong>%s</strong>", "onpage-seo" ), esc_html( $onpage_seo->find_meta( $onpage_seo->meta_types['description'], array( "entity_type" => $inherit_entity_type ), true ) ) ); ?>
      </p>
      
      <p>
        <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['keywords']}"; ?>"><?php echo __( 'Page meta-keywords', 'onpage-seo' ); ?>:</label><br>
        <input type="text" size="40" value="<?php echo $onpage_seo->find_meta( $onpage_seo->meta_types['keywords'], array( "entity_type" => $entity_type, "entity_id" => $entity_id ) ); ?>" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['keywords']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['keywords']}]"; ?>">
        <br>
        <?php echo __( 'Enter your most important keywords here, separated by a comma in lowercase.', 'onpage-seo' ); ?>
        <br><?php echo sprintf( __( "Leave blank to inherit: <strong>%s</strong>", "onpage-seo" ), esc_html( $onpage_seo->find_meta( $onpage_seo->meta_types['keywords'], array( "entity_type" => $inherit_entity_type ), true ) ) ); ?>
      </p>
      
      <p>
        <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['extra_meta']}"; ?>"><?php echo __( 'Extra meta-data', 'onpage-seo' ); ?>:</label><br>
        <textarea style="width: 97%;" cols="50" rows="5" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['extra_meta']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['extra_meta']}]"; ?>"><?php echo $onpage_seo->find_meta( $onpage_seo->meta_types['extra_meta'], array( "entity_type" => $entity_type, "entity_id" => $entity_id ) ); ?></textarea>
        <br>
        <?php echo __( 'Enter any custom meta-data here. It will be added to the page head.', 'onpage-seo' ); ?>
        <br><?php echo sprintf( __( "Leave blank to inherit: <strong>%s</strong>", "onpage-seo" ), esc_html( apply_filters( "onpage_seo_prepare_extra_meta", $onpage_seo->find_meta( $onpage_seo->meta_types['extra_meta'], array( "entity_type" => $inherit_entity_type ), true ) ) ) ); ?>
      </p>
      
      <?php
        $index_setting = $onpage_seo->find_meta( $onpage_seo->meta_types['robots'], array( "entity_type" => $entity_type, "entity_id" => $entity_id ) );
        switch ( $index_setting ) {
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
          default:
            $inherit = "checked";
            break;
        }
      ?>
      <p>
        <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>"><?php echo __( 'Indexing options', 'onpage-seo' ); ?></label>:<br>
        <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $inherit ) ) echo $inherit; ?> value=""> None (inherit)<br>
        <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $index_follow ) ) echo $index_follow; ?> value="index, follow"> Index, Follow<br>
        <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $noindex_follow ) ) echo $noindex_follow; ?> value="noindex, follow"> No-index, Follow<br>
        <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $index_nofollow ) ) echo $index_nofollow; ?> value="index, nofollow"> Index, No-follow<br>
        <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $noindex_nofollow ) ) echo $noindex_nofollow; ?> value="noindex, nofollow"> No-index, No-follow<br>
        <?php echo sprintf( __( "The inherit option yields: <strong>%s</strong>", "onpage-seo" ), esc_html( $onpage_seo->find_meta( $onpage_seo->meta_types['robots'], array( "entity_type" => $inherit_entity_type ), true ) ) ); ?>
      </p>
      
      <?php
    }
    
    /**
     * Gets called after a post has been created or edited. Saves the onpage-seo settings.
     *
     * @package OnpageSeo
     * @since   0.1
     * @param   integer   $id   The post ID.
     */
    function save_post_meta( $id ) { global $onpage_seo;
      if ( $parent_id = wp_is_post_revision( $id ) )
        $id = $parent_id;
        
      $entity_type = get_post_type( $id );
      
      if ( isset( $_POST[OnpageSEO::$post_array] ) )
        $onpage_seo->admin->save_form( $_POST[OnpageSEO::$post_array], array( "entity_type" => $entity_type, "entity_id" => $id ) );
    }
    
    /**
     * Gets called after a post has been deleted. Removes all related meta-data.
     *
     * @package OnpageSEO
     * @since   0.1
     * @param   integer   $id   The post ID.
     */
    function delete_post_meta( $id ) { global $onpage_seo;
      $entity_type = get_post_type( $id );
      
      foreach ( $onpage_seo->meta_types as $meta_type ) {
        $meta_id = $onpage_seo->admin->get_meta_id( $meta_type, array( "entity_type" => $entity_type, "entity_id" => $id ) );
        if ( !empty($meta_id) ) $onpage_seo->admin->delete_meta_data( $meta_id );
      }
    }
    
  } # class OnpageSEOSingle
  
} ?>