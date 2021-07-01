<?php
/**
 * Plugin Name: Display CSV ADW Radio Stations
 * Plugin URI: https://brandonlavello.com
 * Description: Display csv radio station content using a shortcode to insert in a page or post
 * Version: 1.25
 * Text Domain: csv-adw-radio-station-plugin
 * Author: Brandon Lavello
 * Author URI: https://brandonlavello.com
 * License: GNU GPLv3
 */


add_action('admin_menu', 'adw_plugin_setup_menu');

function adw_plugin_setup_menu(){
    add_menu_page( 'ADW Radio CSV Plugin Page', 'ADW Radio CSV Plugin', 'manage_options', 'adw-display-csv-plugin', 'form_init');
}

function form_init(){
    csv_handle_post();

?>
    <h1>A Daily Walk</h1>
    <h2>Upload a File</h2>
    <!-- Form to handle the upload - The enctype value here is very important -->
    <form  method="post" enctype="multipart/form-data">
        <input type='file' id='csv_upload' name='csv_upload'></input>
        <?php submit_button('Upload') ?>
    </form>
<?php
}

function csv_handle_post(){
    // First check if the file appears on the _FILES array
    if(isset($_FILES['csv_upload'])){
        $pdf = $_FILES['csv_upload'];

        // Use the wordpress function to upload
        // test_upload_pdf corresponds to the position in the $_FILES array
        // 0 means the content is not associated with any other posts
        $uploaded=media_handle_upload('csv_upload', 0);
        // Error checking using WP functions
        if(is_wp_error($uploaded)){
            echo "Error uploading file: " . $uploaded->get_error_message();
        } else {
          echo "File upload successful!";
          update_option('adw_csv_id', serialize($uploaded));
          /**
  				* echo "<h1>" . $uploaded . "</h1>";
  				* var_dump($uploaded);
          * $file_id = get_option('adw_csv_id', null);
          * if ($file_id !==  null) { $file_id = unserialize($file_id); }
          * echo "<h1>" . $file_id . "</h1>";
          */
        }
    }
} //end handle post

//add shortcode - call render_adw_csv
add_shortcode( 'adw_csv', 'render_adw_csv' );

// cycles through csv to display stations in table format
function render_adw_csv(){
  $output_string = "";
  // open buffer to store output
  // all echo output goes through buffer
	ob_start();

  // H1 Header
  echo "<h1>A Daily Walk Station List</h1>";

  $plugin_path_str = plugin_dir_path(__FILE__);
  //echo "<p>Plugin Path: " . $plugin_path_str . "</p>";

  // get hardcoded csv file
  // Todo: get uploaded file
  $file_id = get_option('adw_csv_id', null);
  if ($file_id !==  null) { $file_id = unserialize($file_id); }
  $file = get_attached_file($file_id);
  # $file = get_attached_file('6784');


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
       echo "The file $file does not exist.<br><br>";
     }

  // return output
	return $output_string;
}
