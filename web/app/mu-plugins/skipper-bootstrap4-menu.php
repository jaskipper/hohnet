<?php
/*
Plugin Name:  Skipper Bootstrap 4 Menu Walker
Plugin URI:   https://skipperinnovations.com
Description:  A New Navwalker for Bootstrap 4
Version:      1.0.0
Author:       Jason Skipper
Author URI:   https://skipperinnovations.com
License:      MIT License
*/

/*
How to use: Just change the classes to your preference here in the code and then add the following code wherever you would like for it to show up in your theme. Be sure to change the navigation to whatever the name of your menu is
----
create_bootstrap4_menu("primary_navigation")
----
To work correctly with the admin bar use the following scss:
    body.admin-bar, .admin-bar .nav-primary {
      margin-top: 32px;
      @media (max-width: 782px) {
        margin-top: 46px;
      }
    }
If you are using navbar-fixed-top, use the following scss to fix the body position as well as to change it to position: absolute once the width is under 600px
    body {
      padding-top: 50px;
    }
    .navbar-fixed-top {
      @media (max-width: 600px) {
        position: absolute;
      }
    }
*/

function create_bootstrap4_menu( $theme_location ) {

    $navclasses = 'nav-primary navbar navbar-full navbar-fixed-top navbar-dark bg-primary skipper-box-shadow';
    $incontainer = true;
    $buttonclasses = 'hidden-md-up float-xs-right';
    $collapseclasses = 'navbar-toggleable-sm';
    $showbrand = true;
    $brandpadding = 0; //Default 0.25rem
    //$brandcontent = get_bloginfo('name', 'display');
    $brandcontent = '<img src="/app/uploads/2017/01/hohlogo.svg" style="height: 40px" />';
    $ulclasses = 'float-md-right';

    if ( ($theme_location) && ($locations = get_nav_menu_locations()) && isset($locations[$theme_location]) ) {

        $menu_list  = '<nav class="' . $navclasses . '">' ."\n";
        if ($incontainer == true) {
          $menu_list .= '<div class="container">' ."\n";
        }
        $menu_list .= '<button class="navbar-toggler ' . $buttonclasses . '" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation"></button>';

        if ($showbrand == true) {
          $menu_list .= '<a class="navbar-brand" href="' . home_url('/') . '" style="padding-top:' . $brandpadding . '; padding-bottom:' . $brandpadding . '" > ' . $brandcontent . '</a>';
        }
        $menu_list .= '<div class="collapse ' . $collapseclasses . '" id="navbarResponsive">' ."\n";

        $menu = get_term( $locations[$theme_location], 'nav_menu' );
        $menu_items = wp_get_nav_menu_items($menu->term_id);

        $menu_list .= '<ul class="nav navbar-nav ' . $ulclasses . '">' ."\n";

        foreach( $menu_items as $menu_item ) {
            if( $menu_item->menu_item_parent == 0 ) {

                $parent = $menu_item->ID;

                $menu_array = array();
                foreach( $menu_items as $submenu ) {
                    if( $submenu->menu_item_parent == $parent ) {
                        $bool = true;
                        $menu_array[] = '<li class="nav-item"><a class="nav-link" href="' . $submenu->url . '">' . $submenu->title . '</a></li>' ."\n";
                        $submenu_array[] = '<a class="dropdown-item" href="' . $submenu->url . '">' . $submenu->title . '</a>' ."\n";
                    }
                  }

                if( isset($bool) && $bool == true && count( $menu_array ) > 0 ) {

                      $menu_list .= '<li class="nav-item dropdown">' ."\n";
                      $menu_list .= '<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . $menu_item->title . ' <span class="caret"></span></a>' ."\n";
                      $menu_list .= '<div class="dropdown-menu" aria-labelledby="responsiveNavbarDropdown">' ."\n";
                      //$menu_list .= '<a class="dropdown-item" href="' . $menu_item->url . '">' . $menu_item->title . '</a>' ."\n";
                      $menu_list .= implode( "\n", $submenu_array );
                      unset($submenu_array);
                      $menu_list .= '</div>' ."\n";

                } else {

                    $menu_list .= '<li class="nav-item">' ."\n";
                    $menu_list .= '<a class="nav-link" href="' . $menu_item->url . '">' . $menu_item->title . '</a>' ."\n";
                }

            }

            // end <li>
            $menu_list .= '</li>' ."\n";
        }

        $menu_list .= '</ul>' ."\n";
        $menu_list .= '</div>' ."\n";
        if ($incontainer == true) {
          $menu_list .= '</div>' ."\n";
        }
        $menu_list .= '</nav>' ."\n";

    } else {
        $menu_list = '<!-- no menu defined in location "'.$theme_location.'" -->';
    }

    echo $menu_list;
}
