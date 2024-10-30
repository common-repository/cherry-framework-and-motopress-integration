<?php

/**
* Plugin Name: Cherry Framework and MotoPress Integration
* Plugin URI: http://www.getmotopress.com/
* Description: Extend MotoPress Content Editor plugin with Cherry Framework shortcodes.
* Version: 1.0
* Author: MotoPress
* Author URI: http://www.getmotopress.com/
* License: GPL2 or later
*/

global $motopress_cherry_shortcodes_map, $mpc_cherry_prefix;

$motopress_cherry_shortcodes_map = array();
$mpc_cherry_prefix = 'mpc_';

$defaultTitle = 'Lorem ipsum dolor sit amet';
$defaultText = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam adipiscing sodales massa at luctus.';
$defaultButton = 'Read more';

require_once 'motopress-cherry-shortcodes-utils.php';
require_once 'motopress-cherry-shortcodes-map.php';

/* create new shortcode to render original one in div for margin and custom class name */
foreach ($motopress_cherry_shortcodes_map as $id => $cherryShortcode) {
    if (function_exists('add_shortcode') && $cherryShortcode['rewrite'] == TRUE) {
        add_shortcode($id, "motopress_cherry_common_shortcode_renderer");
    }
}

// add Cherry scripts
add_action('wp_head', 'motopress_cherry_shortcodes_wphead');

// add Cherry shortcodes to library
add_action('mp_library', 'motopress_cherry_shortcodes_mp_library_action', 10, 1);

// include php files
add_action('motopress_render_shortcode', 'motopress_cherry_shortcodes_render_action', 10, 1);

// filter shortcode render
add_filter('cherry_plugin_shortcode_output', 'motopress_cherry_plugin_shortcode_output_filter', 10, 3);


function motopress_cherry_shortcodes_wphead() {
    wp_enqueue_script( 'owl-carousel', CHERRY_PLUGIN_URL . 'lib/js/owl-carousel/owl.carousel.min.js', array('jquery'), '1.31', true );
    wp_enqueue_script( 'cherry-plugin', CHERRY_PLUGIN_URL . 'includes/js/cherry-plugin.js', array('jquery') );
    wp_enqueue_script( 'roundabout_script', CHERRY_PLUGIN_URL . 'lib/js/roundabout/jquery.roundabout.min.js', array('jquery') );
    wp_enqueue_script( 'roundabout_shape', CHERRY_PLUGIN_URL . 'lib/js/roundabout/jquery.roundabout-shapes.min.js', array('jquery') );
    
    wp_register_style('motopress-cherry-shortcodes-style', plugin_dir_url(__FILE__) . 'motopress-cherry-shortcodes-style.css', array(), '1.0');
    wp_enqueue_style('motopress-cherry-shortcodes-style');
}

// add cherry shortcodes to MP Library
function motopress_cherry_shortcodes_mp_library_action($motopressCELibrary)
{
    global $motopress_cherry_shortcodes_map;
    require_once 'motopress-cherry-shortcodes-parser-class.php';

    $cherry_shortcodes_dir = CHERRY_PLUGIN_DIR . "admin/shortcodes/shortcodes/";

    foreach ($motopress_cherry_shortcodes_map as $id => $cherryShortcode)
    {
        $mpCherryShortcodeParser = new MPCherryShortcodeParser(
            $cherry_shortcodes_dir . $cherryShortcode['jsFile'], $cherryShortcode);
        $parameters = $mpCherryShortcodeParser->parameters;

        $motopressCELibrary->addObject(
            // new MPCEObject( $id, $name, $icon, $attributes, $position, $closeType, $resize );
            new MPCEObject(
                $id,
                $cherryShortcode['label'],
                $cherryShortcode['icon'],
                $parameters,
                0,
                MPCEObject::ENCLOSED
            ),
            $cherryShortcode['group']
        );
    }
}

// include cherry shortcodes
function motopress_cherry_shortcodes_render_action($shortcode)
{
    global $motopress_cherry_shortcodes_map;
    $cherry_shortcodes_dir = CHERRY_PLUGIN_DIR . "includes/shortcodes/";

    if (!empty($shortcode) && !empty($motopress_cherry_shortcodes_map[$shortcode]))
    {
        $phpFile = $cherry_shortcodes_dir . $motopress_cherry_shortcodes_map[$shortcode]['phpFile'];
        // include one shortcode
        if ( file_exists($phpFile) )
                include_once ($phpFile);
    } else {
        // include all shortcodes
        foreach ($motopress_cherry_shortcodes_map as $id => $cherryShortcode)
        {
            $phpFile = $cherry_shortcodes_dir . $cherryShortcode['phpFile'];
            if ( file_exists($phpFile) )
                include_once ($phpFile);
        }
    }
}

// filter shortcode output
function motopress_cherry_plugin_shortcode_output_filter($content, $atts, $shortcodename)
{
    /*global $motopress_cherry_shortcodes_map;
    $shortcodeMap = $motopress_cherry_shortcodes_map[$shortcodename];
    $hasOwnCustomClass = $shortcodeMap['hasOwnCustomClass'] == TRUE;*/
    $hasOwnCustomClass = true;
    
    extract(shortcode_atts(addStyleAtts(), $atts));

    if (!empty($classes)) $classes = ' ' . $classes;
    if (!empty($custom_class)) $custom_class = ' ' . $custom_class;

    return '<div class="' . $classes . getMarginClasses($margin) . ($hasOwnCustomClass ? "" : $custom_class) . '">' . $content . '</div>';
}
