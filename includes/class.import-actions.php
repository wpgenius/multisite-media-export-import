<?php
/**
 *
 * @class       WPGenius_Import_Actions
 * @author      Team WPGenius (Makarand Mane)
 * @category    Admin
 * @package     multisite-media-export-import/includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPGenius_Import_Actions extends WPGenius_Events_API{

	public static $instance;
	public static function init(){

	    if ( is_null( self::$instance ) )
	        self::$instance = new WPGenius_Import_Actions();
	    return self::$instance;
	}

	/**
	 * Class constructor
	 */
	private function __construct(){		
		add_action('admin_menu', 			array($this,'import_menu'),	10);
	} // END public function __construct

	/**
	 * Add menu under media page
	 *
	 * @return void
	 */
	function import_menu(  ){		
		add_media_page( __('Import Media'), __('Import media','cm-lms'),  'manage_options', 'import-media', array($this,'multisite_media_import'));
	}

	/**
	 * Import images from physical path server.
	 * Check if images exist already otherwise add to database
	 *
	 * @return void
	 */
	function multisite_media_import(){
		
		if(isset($_FILES['media_list'])){
			$csv = fopen($_FILES['media_list']['tmp_name'],"r");
			$column = fgetcsv($csv);
			$user_data =array();
			$upload_dir_paths = wp_upload_dir();
			?>
			<table border="1">
				<thead>
					<tr>
						<th><?php _e( 'OLD ID', 'cm-lms') ?></th>
						<th><?php _e( 'Old URL', 'cm-lms') ?></th>
						<th><?php _e( 'Status', 'cm-lms') ?></th>
						<th><?php _e( 'New ID', 'cm-lms') ?></th>
						<th><?php _e( 'New URL', 'cm-lms') ?></th>
					</tr>
				</thead>
				<tbody>
			
			<?php
			while(! feof($csv)){			
				$row = fgetcsv($csv);
				
				if( !is_numeric($row[0]) )
					continue;
				
				echo "<tr>";
				echo "<td>".$row[0]."</td>";
				echo "<td>".$row[2]."</td>";

				$existing_id = $this->get_attachment_id_from_url( site_url().$row[1] );

				if( $existing_id ){

					echo "<td><b>Existing</b></td>";
					echo "<td><b>".$existing_id ."</b></td>";
					echo "<td><img src='". site_url().$row[1] ."' width='200px' loading='lazy' /></td>";

				}else{

					$file = str_replace( $upload_dir_paths['baseurl'] . '/', '', site_url().$row[1] );

					$filename   = basename( $file );

					// Check image file type
					$wp_filetype = wp_check_filetype( $filename, null );

					// Set attachment data
					$attachment = array(
						'guid'			 => site_url().$row[1],
						'post_mime_type' => $wp_filetype['type'],
						'post_title'     => sanitize_file_name( $filename ),
						'post_content'   => '',
						'post_status'    => 'inherit',
					);

					// Create the attachment
					$attach_id = wp_insert_attachment( $attachment, $file );

					// Include image.php
					require_once(ABSPATH . 'wp-admin/includes/image.php');

					// Define attachment metadata
					$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

					// Assign metadata to attachment
					wp_update_attachment_metadata( $attach_id, $attach_data );

					$new_url = wp_get_attachment_image_src($attach_id, 'full');
					
					echo "<td><b>Imported</b></td>";
					echo "<td>".$attach_id ."</td>";
					echo "<td><img src='". $new_url[0] ."' width='200px' loading='lazy' /></td>";
				}
				
				echo "</tr>";
				//break;
			}
			echo "</tbody>";
			echo "</table>";
			fclose($csv);
		}
		
		echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';  screen_icon('themes'); 
			?>
			<h2>Transfer media from sub site</h2>

			<p>Custom script written for Accel to transfer media.</p>
			
			<p><?php echo sprintf( __( 'Upload CSV file. File should have 2 columns Media File link & ID.' ) ); ?></p>

			<p></p>

			<form method="post" action="" class="repeater" enctype="multipart/form-data">
			
			<table class="" cellpadding="1">
			
					<tr valign="top" >
						<th scope="row" align="right">
							<label for="media_list">
								Upload a CSV file.
							</label> 
						</th>
						<td>
							<input type="file" id="media_list" name="media_list" required />
						</td>
					</tr>
					
					
				</table>

				<p>
					<input type="submit" value="Upload & migrate" class="button-primary"/>
				</p>
				
			</form>
			<?php
		echo '</div>';		

	}

	/**
	 * Get attachment id from url
	 * https://gist.github.com/ahmadawais/15e964da8804153f50d4
	 *
	 * @param string $attachment_url
	 * @return int
	 */
	function get_attachment_id_from_url( $attachment_url = '' ) {
 
		global $wpdb;
		$attachment_id = false;
	 
		// If there is no url, return.
		if ( '' == $attachment_url )
			return $attachment_id;
	 
		// Get the upload directory paths
		$upload_dir_paths = wp_upload_dir();
	 
		// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
		if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
			
			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
	 
			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );
	 
			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value like '%s' AND wposts.post_type = 'attachment'", '%'.$attachment_url.'%' ) );
	 
		}
	 
		return $attachment_id;
	}
	

} // END class WPGenius_Import_Actions