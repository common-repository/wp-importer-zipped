<?php
/*
Plugin Name: WordPress Importer Zipped
Plugin URI: http://wordpress.org/extend/plugins/wordpress-importer/
Description: Allow worpdress importer to parse zipped Xml files
Author: bastho, ecolosites
Author URI: http://urbancube.fr/
Version: 0.1
Text Domain: wordpress-importer-zipped
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if ( ! defined( 'WP_LOAD_IMPORTERS' ) )
	return;


function wordpress_importer_zipped_init() {
	load_plugin_textdomain( 'wordpress-importer-zipped', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	/**
	 * WordPress Importer object for registering the import callback
	 * @global WP_Import $wp_import
	 * Require Wordpress Importer Original Plugin
	 */
	 if ( class_exists( 'WP_Import' ) ) {
class WP_Import_Zipped extends WP_Import {
	function WP_Import_Zipped() { /* nothing */ }

	/**
	 * Parse a Zipped WXR file
	 *
	 * And then, return to the original Wordpress Importer Plugin
	 */
	function parse( $file ) {
		$fname = basename($file);
		$chemin = str_replace($fname,'',$file);
		//echo $chemin.' > '.$fname.'<br>';
		$pos = strrpos($fname,'.zip');
		if($pos && $pos>0){
			$nfile = substr($fname,0,$pos+4);
			$tmpval = substr($fname,$pos+4);
			//echo $nfile;
			if(rename($file,$chemin.$nfile)){
				if(is_dir(WP_TEMP_DIR.$tmpval) || mkdir(WP_TEMP_DIR.$tmpval,0777)){
			WP_Filesystem();
			//echo"<hr>unzip_file( $chemin$nfile, $chemin$tmpval )";
				if(!is_wp_error(unzip_file( $chemin.$nfile, WP_TEMP_DIR.$tmpval ))){
					$dir=scandir(WP_TEMP_DIR.$tmpval);
					echo '<p>'.__( 'File successfully unzipped', 'wordpress-importer-zipped' ).'</p>';
					if(sizeof($dir==3)){					
						echo '<p>'.$dir[2].' '.__( 'found', 'wordpress-importer-zipped' ).'</p>';
						unlink($file);
						rename( WP_TEMP_DIR.$tmpval.'/'.$dir[2], $file);						
						$parser = new WXR_Parser();	
						return $parser->parse( $file );
					}
					else{
						echo __( 'This extension only allows 1 xml file at once', 'wordpress-importer-zipped' );
						return false;
					}
					
				}
				else{
					echo __( 'Error while unzipping file', 'wordpress-importer-zipped' );
					return false;
				}
				}
			else{
				echo __( 'Error while creating temp directory', 'wordpress-importer-zipped' );
				return false;
			}
			}
			else{
				echo __( 'Error while renaming file', 'wordpress-importer-zipped' );	
				return false;
			}			
		}
		else{
			echo '<p><strong>' . __( 'There was an error when reading this Zip file', 'wordpress-importer-zipped' ) . '</strong><br />';
			echo __( 'Please upload a .zip file', 'wordpress-importer-zipped' ) . '</p>';	
			return false;
		}
	}
	// Just replace the first form to parse the zip file
	function greet() {
		echo '<div class="narrow">';
		echo '<p>'.__( 'Howdy! Upload your Zipped WordPress eXtended RSS (WXR) file and we&#8217;ll import the posts, pages, comments, custom fields, categories, and tags into this site.', 'wordpress-importer-zipped' ).'</p>';
		echo '<p>'.__( 'Choose a WXR (.xml) file to upload, then click Upload file and import.', 'wordpress-importer-zipped' ).'</p>';
		wp_import_upload_form( 'admin.php?import=wordpress_zipped&amp;step=1' );
		echo '</div>';
	}
}
$GLOBALS['wp_import_zipped'] = new WP_Import_Zipped();
register_importer( 'wordpress_zipped', 'WordPress (zipped)', __('Import <strong>posts, pages, comments, custom fields, categories, and tags</strong> from a WordPress export file.', 'wordpress-importer-zipped'), array( $GLOBALS['wp_import_zipped'], 'dispatch' ) );
} // class_exists( 'WP_Import' )
	 
}
add_action( 'admin_init', 'wordpress_importer_zipped_init' , 100);
