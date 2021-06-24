<?php
/**
 * Plugin Name: Display CSV ADW Radio Stations
 * Plugin URI: https://brandonlavello.com
 * Description: Display csv radio station content using a shortcode to insert in a page or post
 * Version: 1.1
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
        } else {
          echo "File upload successful!";
          echo "<h1>" . $uploaded . "</h1>";
          var_dump($uploaded);
        }
    }
} //end handle post

//add shortcode - call render_adw_csv
add_shortcode( 'adw_csv', 'render_adw_csv' );

// cycles through csv to display stations in table format
function render_adw_csv(){

  // open buffer to store output
  // all echo output goes through buffer
	ob_start();

  // H1 Header
	echo "<h1>A Daily Walk Station List</h1>";

  // get hardcoded csv file
  // Todo: get uploaded file
	$file = get_attached_file('6784');

    if (file_exists($file)) {
      //Uncomment for development
      //echo "The file $file exists. <br><br>";
      // open csv file as $f
      $f = fopen($file, "r");

      $country = "";
      $state = "";
      $new_country = false;
      $first_round = true;

      while (($line = fgetcsv($f)) !== false) {

        // Handle new Country
         if ( $country !== $line[0]) {

           if (!$first_round) {
             echo "</tbody></table></figure>";
           } else { $first_round = false; }

           $new_country = true;
           $country = $line[0];

           echo "<br><br>";

           // Print Country Heading
           echo "<h2>", $country, "</h2>";

           // Print open table tags
           echo "<figure class=\"wp-block-table\">
               <table style=\"width: 100%\"><colgroup>
               <col span=\"1\" style=\"width: 50%;\">
               <col span=\"1\" style=\"width: 25%;\">
               <col span=\"1\" style=\"width: 25%;\">
               </colgroup><tbody>";
         } //end if country

         // Handle new State
         if ( $state !== $line[1]) {
           // End Previous Table
           echo "</tbody></table></figure>";

           $state = $line[1];

           // Print State Heading
           echo "<h3>", $state, "</h3>";

           // Print open table tags
           echo "<figure class=\"wp-block-table\">
               <table style=\"width: 100%\"><colgroup>
               <col span=\"1\" style=\"width: 50%;\">
               <col span=\"1\" style=\"width: 25%;\">
               <col span=\"1\" style=\"width: 25%;\">
               </colgroup><tbody>";
         } // end if state

         echo "<tr>";

         // Table Data
         echo "<td>" . $line[2] . "</td>";
         echo "<td>" . $line[3] . "</td>";
         echo "<td>" . $line[4] . "</td>";
         echo "</tr>";
       } // end while

       // Print last table closing tags
       echo "</tr></tbody></table></figure><br><br>";

       // get all buffered output, store it to string
       $output_string = ob_get_contents();

       // clean buffer
       ob_end_clean();

       // close csv file
       fclose($f);

     } else {
       echo "The file $filename does not exist.<br><br>";
     }

  // return output
	return $output_string;
}
