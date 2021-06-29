<?php
/**
 * Plugin Name:       HUisHU Essentials Plugins – Iconfont Helper
 * Description:       A Plugin to give needed functionality to other HUisHU Plugins and Themes
 * Version:           1.0.9
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            HUisHU. Digitale Kreativagentur.
 * Author URI:        https://www.huishu-agentur.de
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Silence is golden; exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Use Plugin Update Checker to check for Updates on Github
 */
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/HUisHUAgentur/huishu-essentials-iconfont-helper/',
	__FILE__,
	'huishu-essentials-iconfont-helper'
);

/**
 * Add Settings to Wordpress Backend
 */
function hu_ep_ih_register_settings_or_add_to_hu_framework(){
    if(function_exists('hu_get_custom_theme_options_name')){
        add_filter('huishu_framework_options_page_options','hu_ep_ih_add_settings_to_hu_framework');
    } else {
        add_filter( 'admin_init', 'hu_ep_ih_register_fields');
    }
    add_action( 'admin_menu', 'hu_ep_ih_register_menu_page' );
}
add_action('after_setup_theme','hu_ep_ih_register_settings_or_add_to_hu_framework');

/**
 * Add Menu Page to Settings
 */
function hu_ep_ih_register_menu_page(){
    add_options_page( 'Icons ermitteln', 'Icons ermitteln', 'activate_plugins', 'hu_ep_ih_ic_ermitteln', 'hu_ep_ih_settings_page' );
}

/**
 * Helper function to get all icons
 */
function hu_ep_ih_get_all_icons($type = 'label'){
	$options = get_option('hu_ep_ih_glyphnames',array());
    $return = array();
    foreach($options as $icon => $name){
        if(is_array($name) && isset($name[$type])){
            $return[$icon] = $name[$type];
        } elseif(is_array($name) && isset($name['label'])){
            $return[$icon] = $name['label'];
        } elseif(!is_array($name)){
            $return[$icon] = $name;
        }
    }
    return $return;
    //return get_option('hu_ep_ih_glyphnames',array());
}

function hu_ep_ih_register_iconfont_style(){
    if($csspath = hu_ep_ih_get_css_file_url()){
        wp_register_style('hu-ep-ih-iconfont-style',$csspath,array(),get_option('hu_ep_ih_glyphnames_time',123));
    }
}
add_action('init','hu_ep_ih_register_iconfont_style');

function hu_ep_ih_enqueue_admin_style( $hook ) {
    if ( 'settings_page_hu_ep_ih_ic_ermitteln' != $hook ) {
        return;
    }
    wp_enqueue_style( 'hu-ep-ih-iconfont-style');
}
add_action( 'admin_enqueue_scripts', 'hu_ep_ih_enqueue_admin_style' );

/**
 * Create Settings Page
 */
function hu_ep_ih_settings_page(){
	$path = hu_ep_ih_get_font_file_path();
	$glyphs = hu_ep_ih_get_all_icons();
	if( $_POST['glyph_getter_submit'] == 'Einlesen' ){
		if(is_readable($path)){
			$allglyphs = hu_ep_ih_read_glyphs($path);
			if(!count($allglyphs)){
				echo "Es konnten keine Icons in der angegebenen Schrift gefunden werden.";
			} else {
				$glyppppp = array();
				foreach($allglyphs as $glyph){
                    $glyppppp[$glyph] = '';
				}
				if( $glyphs !== $glyppppp ){
					$glyphs = $glyppppp;
				}
			}
		}
	}
	if($_POST['glyph_getter_submit'] == 'Speichern'){
		$names = $_POST['hu_ep_ih_glyphnames'];
        update_option('hu_ep_ih_glyphnames',$names);
        update_option('hu_ep_ih_glyphnames_time',time());
		$glyphs = $names;
	}
	if(count($glyphs)>0){
		?><h2>Gespeicherte Icons</h2><?php
		echo '<strong>insgesamt: '.count($glyphs).' Icons.</strong><br />';
	}
	if($path){
		echo '<strong>Gespeicherte SVG-Datei:'.$path.'</strong>';
		?>
        <div class="wrap">
            <form method="post">
                <?php
                    foreach($glyphs as $icon => $name){
                        $fields_to_save = apply_filters('hu_ep_ih_icon_fields_to_save',array('label' => 'Beschriftung für Icon '.$icon.' (<i class="icon-'.$icon.'"></i>)'),$icon,$name);
                        foreach($fields_to_save as $fieldname => $field_description){
                            $value = $name;
                            if(count($fields_to_save) > 1){
                                if(is_array($value) && isset($value[$fieldname])){
                                    $value = $value[$fieldname];
                                } elseif(is_array($value)){
                                    $value = "";
                                }
                            }
                            ?>
                            <label for="hu_ep_ih_glyphnames[<?php echo $icon ?>][<?php echo $fieldname; ?>]"><?php echo $field_description; ?></label>
                            <input type="text" name="hu_ep_ih_glyphnames[<?php echo $icon ?>][<?php echo $fieldname; ?>]" value="<?php echo esc_attr($value); ?>" /><br />
                            <?php
                        }
                    }
                ?>
                <input type="submit" name="glyph_getter_submit" value="Speichern" /><br /><br />
                <input type="submit" name="glyph_getter_submit" value="Einlesen" />
            </form>
        </div>
		<?php
	} else {
		echo '<p>Bitte stellen Sie zunächst den Pfad zur SVG-Font-Datei ein.</p>';
	}
}

function hu_ep_ih_read_glyphs($svgFile){
    $svgContent = file_get_contents($svgFile);
    $xmlInit = simplexml_load_string($svgContent);
    $svgJson = json_encode($xmlInit);
    $svgArray = json_decode($svgJson, true);
    $svgGlyphs = $svgArray['defs']['font']['glyph'];
    if (count($svgGlyphs) > 0) {
        $svgGlyphsClear = array();
        foreach ($svgGlyphs as $glyphId => $glyph) {
            if (isset($glyph['@attributes']['glyph-name'])) {
                $svgGlyphsClear[$glyphId] = $glyph['@attributes']['glyph-name'];
            }
        }
    }
    return $svgGlyphsClear;
}

function hu_ep_ih_get_font_file_path(){
    if(function_exists('hu_get_custom_theme_options_name')){
        $options_name = hu_get_custom_theme_options_name();
        if ( function_exists( 'cmb2_get_option' ) ) {
            // Use cmb2_get_option as it passes through some key filters.
            return cmb2_get_option( $options_name, 'hu_ep_ih_font_file_path', NULL );
        }
        // Fallback to get_option if CMB2 is not loaded yet.
        $opts = get_option( $options_name, array() );
        $val = array();
        if ( is_array( $opts ) && array_key_exists( 'hu_ep_ih_font_file_path', $opts ) && false !== $opts[ 'hu_ep_ih_font_file_path' ] ) {
            $val = $opts[ 'hu_ep_ih_font_file_path'];
        }
        return $val;
    } else {
        $value = get_option( 'hu_ep_ih_font_file_path', '' );
        return $value;
    }
    return "";
}

function hu_ep_ih_get_css_file_url(){
    if(function_exists('hu_get_custom_theme_options_name')){
        $options_name = hu_get_custom_theme_options_name();
        if ( function_exists( 'cmb2_get_option' ) ) {
            // Use cmb2_get_option as it passes through some key filters.
            return cmb2_get_option( $options_name, 'hu_ep_ih_css_file_url', NULL );
        }
        // Fallback to get_option if CMB2 is not loaded yet.
        $opts = get_option( $options_name, array() );
        $val = "";
        if ( is_array( $opts ) && array_key_exists( 'hu_ep_ih_css_file_url', $opts ) && false !== $opts[ 'hu_ep_ih_css_file_url' ] ) {
            $val = $opts[ 'hu_ep_ih_css_file_url'];
        }
        return $val;
    } else {
        $value = get_option( 'hu_ep_ih_css_file_url', '' );
        return $value;
    }
    return "";
}

/**
 * This function is executed if the hu-framework is available. It registers the fields for the Iconfont-Filepath and the Iconfont-CSS-URL
 */
function hu_ep_ih_add_settings_to_hu_framework($options = array()){
    $options['hu_ep_ih_title'] = array(
            'name'             	=> 'Einstellungen für die Icons',
            'id'               	=> 'hu_ep_ih_title',
            'type'             	=> 'title',
    );
    $options['hu_ep_ih_font_file_path'] = array(
            'name'             	=> 'Pfad zur Iconfont',
            'desc'             	=> 'Unter welchem Pfad (lokal) kann die Iconfont-SVG-Datei gefunden werden?',
            'id'               	=> 'hu_ep_ih_font_file_path',
            'type'             	=> 'text',
            'default'          	=> get_stylesheet_directory(),
    );
    $options['hu_ep_ih_css_file_url'] = array(
            'name'             	=> 'URL zur Iconfont-CSS-Datei',
            'desc'             	=> 'Unter welcher URL kann die Iconfont-CSS-Datei gefunden werden?',
            'id'               	=> 'hu_ep_ih_css_file_url',
            'type'             	=> 'text_url',
            'default'          	=> get_theme_file_uri(),
    );
    return $options;
}

/**
 * These functions are only executed if the hu-framework is not available. They register the fields for the Iconfont-Filepath and the Iconfont-CSS-URL
 */
function hu_ep_ih_register_fields(){
	register_setting('general', 'hu_ep_ih_font_file_path');
	register_setting('general', 'hu_ep_ih_css_file_url');
	add_settings_field('hu_ep_ih_font_file_path', '<label for="hu_ep_ih_font_file_path">Pfad zur Iconfont</label>' , 'hu_ep_ih_font_file_path_html', 'general');
	add_settings_field('hu_ep_ih_css_file_url', '<label for="hu_ep_ih_css_file_url">URL zur Iconfont-CSS</label>' , 'hu_ep_ih_css_file_url_html', 'general');
}

function hu_ep_ih_font_file_path_html(){
	$value = get_option( 'hu_ep_ih_font_file_path', '' );
	if(!$value){
		$value = get_stylesheet_directory();
	}
	echo '<input type="text" id="hu_ep_ih_font_file_path" name="hu_ep_ih_font_file_path" value="' . $value . '" />';
}

function hu_ep_ih_css_file_url_html(){
	$value = get_option( 'hu_ep_ih_css_file_url', '' );
	if(!$value){
		$value = get_theme_file_uri();
	}
	echo '<input type="text" id="hu_ep_ih_css_file_url" name="hu_ep_ih_css_file_url" value="' . $value . '" />';
}