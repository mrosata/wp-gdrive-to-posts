/**
 * Created by Stayshine Web Development.
 * Author: Michael Rosata
 * Email: mike@stayshine.com
 * Date: 11/13/15
 * Time: 5:28 PM
 *
 * Project: wp-dev
 */

(function( $ ) {
  'use strict';

  // The localized data from WP
  var gdriveToPosts = window.gdriveToPosts;

  /**
   * TemplateAjaxClass
   */
  class TemplateAjaxClass {
    constructor($) {
      $(window).on('load', ()=>{
        this.setupBtn();
        this.templateSelect();
      });
    }


    /**
     * Create CSS for a modal to show messages on the form.
     * @param message
     * @param type
     */
    displayModal (message, type = 'success') {
      let css = {
        border: '2px solid ' + (type === 'success' ? 'green' : 'red'),
        color : (type === 'success' ? 'green' : 'red'),
        display: 'block'
      };
      let modal = $('.gdrive-to-posts-modal');
      if (modal.length) {
        modal.find('.msg').html(message);
        modal.css(css);
      }
      // hide the modal after being clicked
      modal.one('click', () => {
        modal.css('display', 'none');
      });
    }


    templateSelect () {
      var $selectBox = $('select[name="gdrive_to_posts_post_templates-template"]');
      $selectBox.on('click', function (evt) {
        let val = $(this).val();
        if (!val) {
          return false;}
        var data = {
          nonce: gdriveToPosts.nonce,
          template_index: val,
          action: 'gdrive_to_posts_get_template'
        };
        let theNewName = `gdrive_to_posts_post_templates[${val}][template]`;

        $.ajax({
          url: gdriveToPosts.ajaxURL,
          type: 'post',
          data: data,
          success: (resp) => {
            if (resp && resp.success == 1) {
              $('#gdrive_to_posts_post_templates-template')
                .prop('name', theNewName);
              // This is the returned index for the text editor.
              console.log('resp.template', resp.template);
            }
          }
        });

      });

    }


    setupBtn () {
      var $addNewButton = $('#gdriveToPostsAddNewTemplateBtn');

      if ($addNewButton.length) {
        $addNewButton.on('click', (evt) => {
          let fileID = $('input[name="gdrive-to-posts-new-file-id"]').val(),
              tempLabel = $('input[name="gdrive-to-posts-template-label"]').val();
          if (!fileID || !tempLabel) {
            this.displayModal('Enter a Google Sheets File ID and Template Label please!', 'error');
            return false;
          }
          // Disable the button
          $addNewButton
            .prop('disabled', 1);
          // Create the data needed for WP ajax action
          var data = {
            nonce: gdriveToPosts.nonce,
            new_file_id: fileID,
            template_label: tempLabel,
            action: 'gdrive_to_posts_add_new_template'
          };


          $.ajax({
            url: gdriveToPosts.ajaxURL,
            type: 'post',
            data: data,
            success: (resp) => {
              if (resp && resp.success == 1) {
                $('#gdrive-to-posts-templates').insertBefore($(resp.html), $addNewButton);
              }
            },
            done: () => {
              // Enable the button again
              $addNewButton.prop('disabled', 0);
            }
          });
        });
      }
    }
  }

  var newTemplate = new TemplateAjaxClass($);

})( jQuery );