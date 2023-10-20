<?php
/**
 *
 * @class       WPGenius_Export_Actions
 * @author      Team WPGenius (Makarand Mane)
 * @category    Admin
 * @package     multisite-media-export-import/includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPGenius_Export_Actions extends WPGenius_Events_API{

	public static $instance;
	public static function init(){

	    if ( is_null( self::$instance ) )
	        self::$instance = new WPGenius_Export_Actions();
	    return self::$instance;
	}

	/**
	 * Class constructor
	 */
	private function __construct(){		
		add_action('admin_menu', 	array($this,'export_menu'),	10 );
		add_action('init',			array( $this, 'export_subsite_media' ) , 100);
	} // END public function __construct

	/**
	 * Add menu under media page
	 *
	 * @return void
	 */
	function export_menu(  ){		
		add_media_page( __('Export Media'), __('Export media','cm-lms'),  'manage_options', 'export-media', array( $this, 'multisite_media_export' ) );
	}

	/**
	 * Add form for export page
	 *
	 * @return void
	 */
	function multisite_media_export(){
		?>
		<div class="wrap">
			<div id="icon-tools" class="icon32"></div>
			<h2>Export media from sub site</h2>

			<p>Custom script written for Accel to export media.</p>  

			<p></p>

			<form method="post" action="" class="repeater" enctype="multipart/form-data">
				<?php wp_nonce_field( 'secure_export_to_csv', '_ipnonce' ); ?>
				<input type="hidden" name="export_subsite_media" value="true" />
				<p><input type="submit" value="Export" class="button-primary"/></p>

				
			</form>
		</div>
		<?php
	}

	/**
	 * Export urls of all media in wordpress
	 *
	 * @return void
	 */
	function export_subsite_media(){
	
		if ( isset($_POST["export_subsite_media"] ) ) { 
			
			if(wp_verify_nonce( $_REQUEST['_ipnonce'] , 'secure_export_to_csv' )){

	
				//query
				
				$this->download_send_headers( $_SERVER['HTTP_HOST']."_".$_POST['report_type']."-". date("YmdHis") . ".csv" );
			
				$out = fopen('php://output', 'w');
					
				$csv_row = array( "id", "URL", "Path" );
				
				fputcsv( $out, $csv_row );
				
				// Get latest 3 questions.
				$args = array(
					'post_type' => 'attachment',
					'post_status' => 'inherit', 
            		'posts_per_page' => -1,
				);
	
				$the_query = new WP_Query( $args );
				
				if ( $the_query->have_posts()) {
					
					while ( $the_query->have_posts() ) {
	
						$the_query->the_post(  );						
	
						$csv_row = array(
							get_the_ID(),
							str_replace( site_url(), "", wp_get_attachment_url( get_the_ID() ) ) ,
							wp_get_attachment_url( get_the_ID() ),
						);
						fputcsv( $out, $csv_row );
					}
				}
				
				fclose($out);		
				die();
			}
	
		}
	
	}
		
} // END class WPGenius_Export_Actions