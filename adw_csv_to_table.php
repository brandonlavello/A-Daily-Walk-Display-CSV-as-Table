<?php
/**
 * Plugin Name: Display CSV ADW Radio Stations
 * Plugin URI: https://brandonlavello.com
 * Description: Display csv radio station content using a shortcode to insert in a page or post
 * Version: 1.0
 * Text Domain: csv-adw-radio-station-plugin
 * Author: Brandon Lavello
 * Author URI: https://brandonlavello.com
 * License: GNU GPLv3
 */


add_action('admin_menu', 'test_plugin_setup_menu');

function test_plugin_setup_menu(){
    add_menu_page( 'ADW Radio CSV Plugin Page', 'ADW Radio CSV Plugin', 'manage_options', 'test-plugin', 'test_init' );
}

function test_init(){
    test_handle_post();

?>
    <h1>A Daily Walk</h1>
    <h2>Upload a File</h2>
    <!-- Form to handle the upload - The enctype value here is very important -->
    <form  method="post" enctype="multipart/form-data">
        <input type='file' id='test_upload_pdf' name='test_upload_pdf'></input>
        <?php submit_button('Upload') ?>
    </form>
<?php
}

function test_handle_post(){
    // First check if the file appears on the _FILES array
    if(isset($_FILES['test_upload_pdf'])){
        $pdf = $_FILES['test_upload_pdf'];

        // Use the wordpress function to upload
        // test_upload_pdf corresponds to the position in the $_FILES array
        // 0 means the content is not associated with any other posts
        $uploaded=media_handle_upload('test_upload_pdf', 0);
        // Error checking using WP functions
        if(is_wp_error($uploaded)){
            echo "Error uploading file: " . $uploaded->get_error_message();
        }else{
            echo "File upload successful!";
				echo "<h1>" . $uploaded . "</h1>";
				var_dump($uploaded);
        }
    }


}

add_shortcode( 'adw_csv', 'render_adw_csv' );

function render_adw_csv(){
	ob_start();

	echo "<h1>A Daily Walk Station List</h1>";

	$file = get_attached_file('6784');

     if (file_exists($file)) {
		 //echo "The file $file exists. <br><br>";
     } else {
       echo "The file $filename does not exist.<br><br>";
     }

	$f = fopen($file, "r");

    $country = "";
    $state = "";
	$new_country = false;
	$first_round = true;

	while (($line = fgetcsv($f)) !== false) {

// 		// Print Country Heading
        if ( $country !== $line[0]) {
			if (!$first_round) {
				echo "</tbody></table></figure>";
 		} else { $first_round = false; }
  		$new_country = true;
			$country = $line[0];
			echo "<br><br>";
			echo "<h2>", $country, "</h2>";
 			echo "<figure class=\"wp-block-table\">
			<table style=\"width: 100%\">
	   		<colgroup>
       		<col span=\"1\" style=\"width: 50%;\">
       		<col span=\"1\" style=\"width: 25%;\">
       		<col span=\"1\" style=\"width: 25%;\">
   	   		</colgroup><tbody>";
		}


        // Print State Heading
        if ( $state !== $line[1]) {
// 			// End Table
 			echo "</tbody></table></figure>";

 			$state = $line[1];
 			echo "<h3>", $state, "</h3>";
// 			// Start Table
 			echo "<figure class=\"wp-block-table\">
			<table style=\"width: 100%\">
	   		<colgroup>
       		<col span=\"1\" style=\"width: 50%;\">
       		<col span=\"1\" style=\"width: 25%;\">
       		<col span=\"1\" style=\"width: 25%;\">
   	   		</colgroup><tbody>";
        }

       echo "<tr>";

       // Table Data
       echo "<td>" . $line[2] . "</td>";
       echo "<td>" . $line[3] . "</td>";
       echo "<td>" . $line[4] . "</td>";

       echo "</tr>";

     }


	echo "</tr></tbody></table></figure><br><br>";
	$output_string = ob_get_contents();
    ob_end_clean();

	fclose($f);
	return $output_string;
}
