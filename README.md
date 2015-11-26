WP GDrive to Posts Plugin
===================

This WP plugin will create posts using Spreadsheets stored on your Google Drive and then automatically check to see if new rows have been added to create posts from them as well. The plugin will check at intervals set by the site admin. It works best coupled with a service such as IFTTT.com.

**Updates 11-25-2015**
- Added the ability to template in featured images or use a column variable to hold the place of a future featured image. When a sheet is updated and WP GDrive to Posts converts the new rows into posts it will also check if there should be a featured image and provided the featured image is a remote url the plugin will grab it and attach it to your post.
- Next I'd like to add captions. Note that if your spreadsheet uses the `IMAGE=(<image>, 1)` syntax to show images you will have to remove the `IMAGE=(` and `, 1` from your spreadsheet before it is parsed. The image column must resolve to a url, raw images won't be made accessible in a .csv which is what we use as the format to parse sheets at the moment.

The first row of your Spreadsheet shall act as a header for the rest of the file. Whatever terms you place in the top row of the Sheet can be used as variables in the templated body, title and tags of your posts. A column named `address` would be reference in the template as `{!!ADDRESS!!}` or `{!!address!!}`. This way you have fine control over how your posts get created in the present as well as the future. You can change the top row of the spreadsheet and it will not effect future posts. Use the `Fetch Fields` button on the options page to see if your fields are working and how to use them inside the body, title or tags of your post.

This document will walk you through the download and install of the `wp-gdrive-to-posts` plugin. You'll need an install of WP to use or develop over the plugin. Eventually there will be a branch for dev and a branch for production but for now it's all basically development. So regardless why you want to use the project you should start by following the directions below:

1. Run  `git clone https://github.com/mrosata/wp-gdrive-to-posts.git`. 
2. Upload or move the `wp-gdrive-to-posts` folder into the `/wp-content/plugins/` directory of your WordPress install. 
3. Now you should have the plugin `gdrive-to-posts` listed in the <i class="icon-cog"></i> **Plugins** page of your WordPress dashboard. Click Active, (***if your a regular user then you don't need step 4***)
4. From the plugins directory run `cd wp-gdrive-to-posts && npm install`


Great, if you need more information on running the plugin check out this [video on YouTube](https://www.youtube.com/watch?v=wy7NWfzoUPs). I will update the video after we go to production as well.

Development
----------
If your interested in development then here is the current state of dev.
- Run `gulp` to have gulp watch for .scss files in the `admin/css/raw` folder. Any `.scss` file in that folder will compile to  the folder below `admin/css/`, gulp will watch for `admin/css/raw/wp-gtp-partials/*.scss` files as well so you can add any style modules there.
>- The default `gulp` also transpiles ES2015 to ES5 using babel. It watches for files ending in `-es6.js` in the folder `admin/js/` and transpiles to a file with the same name minus the suffix `-es6.js`. This means that you can write new JavaScript code inside `admin/js/gdrive_to_posts-admin-es6.js` and it will transpile to `admin/js/gdrive_to_posts-admin.js` which is loaded by WP. (*As a note, I used ES2015 for my own reasons in this project. At the moment there is no feature detection being done, every browser will be served ES5 compliant code regardless of compatibility. This is on the todo for after production*).
- `admin/partials/gdrive_to_posts-admin-display.php` creates the layout for the settings page.
- `admin/gdrive_to_posts-admin.php` handles most of the logic behind the options page as well as handling the first level of most hooks. So when the hook for a chron job is fired, it will run through this file first to collect information about settings and then it delegates tasks such as <i class="icon-provider-gdrive"></i> **Google Client and Drive API** connection to `includes/class-gdrive_to_posts-google-client-handler.php` and delegates <i class="icon-upload"></i> **post creation and template building** off to `includes/class-gdrive_to_posts-google-client-workhorse.php`


---
> **Note:** This file will be updated as often as possible. To get in touch [shoot me an email @ mrosata1984@gmail.com](mailto:mrosata1984@gmail.com) 
>The plugin has an option to fetch resources from URI's which was meant for users who don't want to get a key file and fingerprint id from the Google Developers Console. This feature could also parse other remote CSV files but it is not part of the intended features and as such results may vary. You can test these files in advanced through the plugin settings page in the Dashboard.