<?php
/**
 * @package OnpageSEO
 * @version 0.1
 */

if ( ! class_exists( "OnpageSEOCategories" ) ) {
  
  /**
   * The categories class. This class contains all the admin functionality related to category archives.
   *
   * @package OnpageSEO
   * @since   0.1
   */
  class OnpageSEOCategories {
    
    /**
     * The constructor function.
     *
     * @package OnpageSEO
     * @since   0.1
     */
    function OnpageSEOCategories() {
      global $onpage_seo;
      
      // Adds the input boxes to the category forms.
      add_action( 'category_add_form_fields', array( $this, 'add_form_html' ) );
      add_action( 'category_edit_form_fields', array( $this, 'edit_form_html' ) );
      add_action( 'created_category', array( $this, 'save_category_meta' ) );
      add_action( 'edit_category', array( $this, 'save_category_meta' ) );
      add_action( 'delete_category', array( $this, 'delete_category_meta' ) );
    }
    
    /**
     * Prints the onpage-seo related form items on the Categories page.
     *
     * @package OnpageSEO
     * @since   0.1
     */
    function add_form_html() { global $onpage_seo;
      $entity_type = "category";
      $inherit_entity_type = "categories"; ?>
      
      <h4>OnpageSEO:</h4>
      
      <div class="form-field">
        <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['title']}"; ?>"><?php echo __( 'Archive page title', 'onpage-seo' ); ?></label>
        <input id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['title']}"; ?>" type="text" size="40" value="" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['title']}]"; ?>">
        <p class="description">
          <?php echo __('The page title will appear in the search results. It should describe your page in a short sentence.')?>
          <br><?php echo sprintf( __( "Leave blank to inherit: <strong>%s</strong>", "onpage-seo" ), esc_html( $onpage_seo->find_meta( $onpage_seo->meta_types['title'], array( "entity_type" => $inherit_entity_type ), true ) ) ); ?>
        </p>
      </div>
      
      <div class="form-field">
        <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['description']}"; ?>"><?php echo __( 'Archive page meta-description', 'onpage-seo' ); ?></label>
        <textarea style="width: 97%;" cols="50" rows="5" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['description']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['description']}]"; ?>"></textarea>
        <p class="description">
          <?php echo __( 'A short description will tell the search engines a little about your page.', 'onpage-seo' ); ?>
          <br><?php echo sprintf( __( "Leave blank to inherit: <strong>%s</strong>", "onpage-seo" ), esc_html( $onpage_seo->find_meta( $onpage_seo->meta_types['description'], array( "entity_type" => $inherit_entity_type ), true ) ) ); ?>
        </p>
      </div>
      
      <div class="form-field">
        <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['keywords']}"; ?>"><?php echo __( 'Archive page meta-keywords', 'onpage-seo' ); ?></label>
        <input type="text" size="40" value="" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['keywords']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['keywords']}]"; ?>">
        <p class="description">
          <?php echo __( 'Enter your most important keywords here, separated by a comma in lowercase.', 'onpage-seo' ); ?>
          <br><?php echo sprintf( __( "Leave blank to inherit: <strong>%s</strong>", "onpage-seo" ), esc_html( $onpage_seo->find_meta( $onpage_seo->meta_types['keywords'], array( "entity_type" => $inherit_entity_type ), true ) ) ); ?>
        </p>
      </div>
      
      <div class="form-field">
        <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['extra_meta']}"; ?>"><?php echo __( 'Extra meta-data', 'onpage-seo' ); ?></label>
        <textarea style="width: 97%;" cols="50" rows="5" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['extra_meta']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['extra_meta']}]"; ?>"></textarea>
        <p class="description">
          <?php echo __( 'Enter any custom meta-data here. It will be added to the archive page head.', 'onpage-seo' ); ?>
          <br><?php echo sprintf( __( "Leave blank to inherit: <strong>%s</strong>", "onpage-seo" ), esc_html( apply_filters( "onpage_seo_prepare_extra_meta", $onpage_seo->find_meta( $onpage_seo->meta_types['extra_meta'], array( "entity_type" => $inherit_entity_type ), true ) ) ) ); ?>
        </p>
      </div>
      
      <div class="">
        <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>"><?php echo __( 'Indexing options', 'onpage-seo' ); ?></label>
          <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" checked value=""> None (inherit)<br>
          <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" value="index, follow"> Index, Follow<br>
          <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" value="noindex, follow"> No-index, Follow<br>
          <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" value="index, nofollow"> Index, No-follow<br>
          <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" value="noindex, nofollow"> No-index, No-follow<br>
          <p class="description">
            <?php echo sprintf( __( "The inherit option yields: <strong>%s</strong>", "onpage-seo" ), esc_html( $onpage_seo->find_meta( $onpage_seo->meta_types['robots'], array( "entity_type" => $inherit_entity_type ), true ) ) ); ?>
          </p>
        </label>
      </div>
      
      <?php
    }
    
    /**
     * Displays the html for the category edit form.
     *
     * @package OnpageSEO
     * @since   0.1
     */
    function edit_form_html( $category ) { global $onpage_seo;
      $entity_type = "category";
      $inherit_entity_type = "categories";
      $entity_id = $category->term_id; ?>

      <tr>
        <th><h4>OnpageSEO:</h4></th>
        <td></td>
      </tr>

      <tr class="form-field">
        <th valign="top" scope="row">
          <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['title']}"; ?>"><?php echo __( 'Archive page title', 'onpage-seo' ); ?></label>
        </th>
        <td>
          <input id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['title']}"; ?>" type="text" size="40" value="<?php echo $onpage_seo->find_meta( $onpage_seo->meta_types['title'], array( "entity_type" => $entity_type, "entity_id" => $entity_id ) ); ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['title']}]"; ?>">
          <p class="description">
            <?php echo __('The page title will appear in the search results. It should describe your page in a short sentence.')?>
            <br><?php echo sprintf( __( "Leave blank to inherit: <strong>%s</strong>", "onpage-seo" ), esc_html( $onpage_seo->find_meta( $onpage_seo->meta_types['title'], array( "entity_type" => $inherit_entity_type ), true ) ) ); ?>
          </p>
        </td>
      </tr>
      
      <tr class="form-field">
        <th valign="top" scope="row">
          <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['description']}"; ?>"><?php echo __( 'Archive page meta-description', 'onpage-seo' ); ?></label>
        </th>
        <td>
          <textarea style="width: 97%;" cols="50" rows="5" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['description']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['description']}]"; ?>"><?php echo $onpage_seo->find_meta( $onpage_seo->meta_types['description'], array( "entity_type" => $entity_type, "entity_id" => $entity_id ) ); ?></textarea>
          <p class="description">
            <?php echo __( 'A short description will tell the search engines a little about your page.', 'onpage-seo' ); ?>
            <br><?php echo sprintf( __( "Leave blank to inherit: <strong>%s</strong>", "onpage-seo" ), esc_html( $onpage_seo->find_meta( $onpage_seo->meta_types['description'], array( "entity_type" => $inherit_entity_type ), true ) ) ); ?>
          </p>
        </td>
      </tr>
      
      <tr class="form-field">
        <th valign="top" scope="row">
          <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['keywords']}"; ?>"><?php echo __( 'Archive page meta-keywords', 'onpage-seo' ); ?></label>
        </th>
        <td>
          <input type="text" size="40" value="<?php echo $onpage_seo->find_meta( $onpage_seo->meta_types['keywords'], array( "entity_type" => $entity_type, "entity_id" => $entity_id ) ); ?>" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['keywords']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['keywords']}]"; ?>">
          <p class="description">
            <?php echo __( 'Enter your most important keywords here, separated by a comma in lowercase.', 'onpage-seo' ); ?>
            <br><?php echo sprintf( __( "Leave blank to inherit: <strong>%s</strong>", "onpage-seo" ), esc_html( $onpage_seo->find_meta( $onpage_seo->meta_types['keywords'], array( "entity_type" => $inherit_entity_type ), true ) ) ); ?>
          </p>
        </td>
      </tr>
      
      <tr class="form-field">
        <th valign="top" scope="row">
          <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['extra_meta']}"; ?>"><?php echo __( 'Extra meta-data', 'onpage-seo' ); ?></label>
        </th>
        <td>
          <textarea style="width: 97%;" cols="50" rows="5" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['extra_meta']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['extra_meta']}]"; ?>"><?php echo $onpage_seo->find_meta( $onpage_seo->meta_types['extra_meta'], array( "entity_type" => $entity_type, "entity_id" => $entity_id ) ); ?></textarea>
          <p class="description">
            <?php echo __( 'Enter any custom meta-data here. It will be added to the archive page head.', 'onpage-seo' ); ?>
            <br><?php echo sprintf( __( "Leave blank to inherit: <strong>%s</strong>", "onpage-seo" ), esc_html( apply_filters( "onpage_seo_prepare_extra_meta", $onpage_seo->find_meta( $onpage_seo->meta_types['extra_meta'], array( "entity_type" => $inherit_entity_type ), true ) ) ) ); ?>
          </p>
        </td>
      </tr>
      
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
      <tr class="">
        <th valign="top" scope="row">
          <label for="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>"><?php echo __( 'Indexing options', 'onpage-seo' ); ?></label>
        </th>
        <td>
          <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $inherit ) ) echo $inherit; ?> value=""> None (inherit)<br>
          <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $index_follow ) ) echo $index_follow; ?> value="index, follow"> Index, Follow<br>
          <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $noindex_follow ) ) echo $noindex_follow; ?> value="noindex, follow"> No-index, Follow<br>
          <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $index_nofollow ) ) echo $index_nofollow; ?> value="index, nofollow"> Index, No-follow<br>
          <input type="radio" id="<?php echo "{$onpage_seo::$post_array}_{$onpage_seo->meta_types['robots']}"; ?>" name="<?php echo "{$onpage_seo::$post_array}[{$onpage_seo->meta_types['robots']}]"; ?>" <?php if ( isset( $noindex_nofollow ) ) echo $noindex_nofollow; ?> value="noindex, nofollow"> No-index, No-follow<br>
          <p class="description">
            <?php echo sprintf( __( "The inherit option yields: <strong>%s</strong>", "onpage-seo" ), esc_html( $onpage_seo->find_meta( $onpage_seo->meta_types['robots'], array( "entity_type" => $inherit_entity_type ), true ) ) ); ?>
          </p>
        </td>
      </tr>
      
    <?php }
    
    /**
     * Gets called after a category has been created or edited. Saves the onpage-seo settings.
     *
     * @package OnpageSeo
     * @since   0.1
     * @param   integer   $id   The category ID.
     */
    function save_category_meta( $id ) { global $onpage_seo;
      if ( isset( $_POST[OnpageSEO::$post_array] ) )
        $onpage_seo->admin->save_form( $_POST[OnpageSEO::$post_array], array( "entity_type" => "category", "entity_id" => $id ) );
    }
    
    /**
     * Gets called after a category has been deleted. Removes all related meta-data.
     *
     * @package OnpageSEO
     * @since   0.1
     * @param   integer   $id   The category ID.
     */
    function delete_category_meta( $id ) { global $onpage_seo;
      foreach ( $onpage_seo->meta_types as $meta_type ) {
        $meta_id = $onpage_seo->admin->get_meta_id( $meta_type, array( "entity_type" => "category", "entity_id" => $id ) );
        if ( !empty($meta_id) ) $onpage_seo->admin->delete_meta_data( $meta_id );
      }
    }
    
  } # class OnpageSEOCategories
  
}
?>