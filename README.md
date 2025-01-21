# A Daily Walk: Display CSV as Table

This WordPress plugin enables the display of CSV (Comma-Separated Values) data as an HTML table within your WordPress site. It's particularly useful for presenting structured data in a readable and organized format.

## Features

- **CSV to HTML Table Conversion**: Transforms CSV files into HTML tables, allowing for easy data presentation on your website.
- **Shortcode Integration**: Utilizes WordPress shortcodes to embed tables within posts or pages seamlessly.
- **Customizable Table Styling**: Offers options to style the generated tables to match your site's design.

## Installation

1. **Download the Plugin**: Clone or download the repository from [GitHub](https://github.com/brandonlavello/A-Daily-Walk-Display-CSV-as-Table).

2. **Upload to WordPress**:
   - Navigate to the WordPress dashboard.
   - Go to `Plugins` > `Add New` > `Upload Plugin`.
   - Select the downloaded ZIP file and click `Install Now`.

3. **Activate the Plugin**: After installation, click `Activate` to enable the plugin.

## Usage

1. **Upload Your CSV File**:
   - Upload your CSV file to the WordPress Media Library or ensure it's accessible via a URL.

2. **Insert Shortcode**:
   - In the desired post or page, insert the following shortcode:

     ```plaintext
     [adw_csv_to_table src="URL_TO_YOUR_CSV"]
     ```

   - Replace `URL_TO_YOUR_CSV` with the actual URL or path to your CSV file.

3. **Publish or Update**: Save your changes, and the CSV data will be displayed as a table on your site.

## Customization

You can customize the appearance of the tables by modifying the plugin's CSS or adding custom styles in your theme's stylesheet.

## Uninstallation

To uninstall the plugin:

1. Navigate to the WordPress dashboard.
2. Go to `Plugins` > `Installed Plugins`.
3. Find "A Daily Walk: Display CSV as Table" and click `Deactivate`.
4. After deactivation, click `Delete` to remove the plugin.

## License

This project is licensed under the [GPL-3.0 License](LICENSE).

## Acknowledgments

Developed by Brandon Lavello.

This plugin was inspired by the need to present CSV data effectively within WordPress sites.
