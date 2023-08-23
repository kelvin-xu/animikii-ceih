<?php


/**
 * Side_Menu_Walker class.
 *
 * @extends Walker_Nav_Menu
 */
class Side_Menu_Walker extends Walker_Nav_Menu {

  /**
   * start_lvl function.
   *
   * @access public
   * @param mixed &$output
   * @param mixed $depth
   * @return void
   */
  function start_lvl( &$output, $depth = 0, $args = array() ) {

    $indent = str_repeat( "\t", $depth );
    $output	   .= "\n$indent<ul>";

  }

  /**
   * end_lvl function.
   *
   * @access public
   * @param mixed &$output
   * @param mixed $depth
   * @return void
   */
  function end_lvl (&$output, $depth = 0, $args = array()) {
    $output .= "</ul>\n";
  }

  /**
   * start_el function.
   *
   * @access public
   * @param mixed &$output
   * @param mixed $item
   * @param int $depth (default: 0)
   * @param array $args (default: array())
   * @param int $id (default: 0)
   * @return void
   */
  function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {

    global $wp_query;
    $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

    $class_names = $value = '';

    $classes = empty( $item->classes ) ? array() : (array) $item->classes;

    //Add class and attribute to LI element that contains a submenu UL.
    if ($args->has_children){
      $classes[] 		= '';
      //$li_attributes .= ' data-dropdown="dropdown"';
    }
    $classes[] = 'menu-item-' . $item->ID;
    //If we are on the current page, add the active class to that menu item.
    $classes[] = ($item->current) ? 'active' : '';

    //Make sure you still add all of the WordPress classes.
    $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
    $class_names = ' class="' . esc_attr( $class_names ) . '"';

    $output .= $indent . '<li' . $value . $class_names . '>';

    $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
    $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
    $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
    $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

    $item_output = $args->before;
    $item_output .= '<a'. $attributes .'>';
    $item_output .= $args->link_before .apply_filters( 'the_title', $item->title, $item->ID );
    $item_output .= '</a>';
    $item_output .= $args->after;

    $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
  }

  //Overwrite display_element function to add has_children attribute. Not needed in >= Wordpress 3.4

  /**
   * display_element function.
   *
   * @access public
   * @param mixed $element
   * @param mixed &$children_elements
   * @param mixed $max_depth
   * @param int $depth (default: 0)
   * @param mixed $args
   * @param mixed &$output
   * @return
   */
  function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {

    if ( !$element )
      return;

	if ( ! $depth ) {
		$depth = 0;
	}

    $id_field = $this->db_fields['id'];

    //display this element
    if ( is_array( $args[0] ) )
      $args[0]['has_children'] = ! empty( $children_elements[$element->$id_field] );
    else if ( is_object( $args[0] ) )
      $args[0]->has_children = ! empty( $children_elements[$element->$id_field] );
    $cb_args = array_merge( array(&$output, $element, $depth), $args);
    call_user_func_array(array(&$this, 'start_el'), $cb_args);

    $id = $element->$id_field;

    // descend only when the depth is right and there are childrens for this element
    if ( ($max_depth == 0 || $max_depth > $depth+1 ) && isset( $children_elements[$id]) ) {

      foreach( $children_elements[ $id ] as $child ){

        if ( !isset($newlevel) ) {
          $newlevel = true;
          //start the child delimiter
          $cb_args = array_merge( array(&$output, $depth), $args);
          call_user_func_array(array(&$this, 'start_lvl'), $cb_args);
        }
        $this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
      }
        unset( $children_elements[ $id ] );
    }

    if ( isset($newlevel) && $newlevel ){
      //end the child delimiter
      $cb_args = array_merge( array(&$output, $depth), $args);
      call_user_func_array(array(&$this, 'end_lvl'), $cb_args);
    }

    //end this element
    $cb_args = array_merge( array(&$output, $element, $depth), $args);
    call_user_func_array(array(&$this, 'end_el'), $cb_args);

  }

}
