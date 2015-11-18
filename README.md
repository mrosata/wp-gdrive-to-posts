#### wp-gdrive-to-posts
-----

This WP plugin will create posts from Spreadsheets stored on your Google Drive created with Google Sheets. The plugin will check at intervals to see if any rows were added to the spreadsheet and create new posts as new rows are added. It works best coupled with a service such as IFTTT.com.

The first row of your Spreadsheet shall act as a header for the rest of the file. Whatever terms you place in the top row of the Sheet can be used as variables in the templated body, title and tags of your posts. A column named `address` would be reference in the template as `{!!ADDRESS!!}`.


Note: The plugin has an option to fetch resources from URI's which was meant for users who don't want to get a key file and fingerprint id from the Google Developers Console. This feature could also parse other remote CSV files but it is not part of the intended features and as such results may vary. You can test these files in advanced through the plugin settings page in the Dashboard.