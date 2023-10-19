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

	private function __construct(){		
		add_action('admin_menu', 			array($this,'import_menu'),	10);
	} // END public function __construct

	function import_menu(  ){		
		add_media_page( __('Import Media'), __('Import media','cm-lms'),  'manage_options', 'import-media', 'multisite_media_import');
	}

	function multisite_media_import(){		
		
		if(isset($_FILES['media_list'])){
			$csv = fopen($_FILES['media_list']['tmp_name'],"r");
			$column = fgetcsv($csv);
			$user_data =array();
				
			?>
			<table border="1">
				<thead>
					<tr>
						<th><?php _e( 'OLD URL', 'cm-lms') ?></th>
						<th><?php _e( 'Old id', 'cm-lms') ?></th>
						<th><?php _e( 'New ID', 'cm-lms') ?></th>
						<th><?php _e( 'New URL', 'cm-lms') ?></th>
					</tr>
				</thead>
				<tbody>
			
			<?php
			while(! feof($csv)){			
				$row = fgetcsv($csv);
				
				if( !is_numeric($row[1]) )
					continue;
				
				echo "<tr>";
				echo "<td>".$row[0]."</td>";
				echo "<td>".$row[1]."</td>";

				//$post_thumbnail_id = get_post_thumbnail_id($post_id);
				//$image_url = wp_get_attachment_image_src($post_thumbnail_id, 'full');
				//$image_url = $image_url[0];

				$image_url = $row[0];
				$folder =  substr( $image_url ,59, 9);

				//switch_to_blog(2); 

				$upload_dir = wp_upload_dir( ); // Set upload folder
				$image_data = file_get_contents($image_url); // Get image data
				$filename   = basename($image_url); // Create image file name

				// Check folder permission and define file location
				if( wp_mkdir_p( $upload_dir['path'] . $folder ) ) {
					$file = $upload_dir['path'] . $folder . $filename;
				} else {
					$file = $upload_dir['basedir'] . $folder . $filename;
				}

				// Create the image  file on the server
				file_put_contents( $file, $image_data );

				// Check image file type
				$wp_filetype = wp_check_filetype( $filename, null );

				// Set attachment data
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => sanitize_file_name( $filename ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);

				// Create the attachment
				$attach_id = wp_insert_attachment( $attachment, $file );

				// Include image.php
				require_once(ABSPATH . 'wp-admin/includes/image.php');

				// Define attachment metadata
				$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

				// Assign metadata to attachment
				wp_update_attachment_metadata( $attach_id, $attach_data );

			// restore_current_blog(); // return to original blog

			$new_url = wp_get_attachment_image_src($attach_id, 'full');

				echo "<td>".$attach_id."</td>";
				echo "<td>". $new_url[0]."</td>";
				
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
	

} // END class WPGenius_Import_Actions