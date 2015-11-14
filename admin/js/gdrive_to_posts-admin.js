'use strict';

var _createClass = (function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; })();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 * Created by Stayshine Web Development.
 * Author: Michael Rosata
 * Email: mike@stayshine.com
 * Date: 11/13/15
 * Time: 5:28 PM
 *
 * Project: wp-dev
 */

(function ($) {
  'use strict'

  // The localized data from WP
  ;
  var gdriveToPosts = window.gdriveToPosts;

  /**
   * TemplateAjaxClass
   */

  var TemplateAjaxClass = (function () {
    function TemplateAjaxClass($) {
      var _this = this;

      _classCallCheck(this, TemplateAjaxClass);

      $(window).on('load', function () {
        _this.setupBtn();
        _this.templateSelect();
      });
    }

    /**
     * Create CSS for a modal to show messages on the form.
     * @param message
     * @param type
     */

    _createClass(TemplateAjaxClass, [{
      key: 'displayModal',
      value: function displayModal(message) {
        var type = arguments.length <= 1 || arguments[1] === undefined ? 'success' : arguments[1];

        var css = {
          border: '2px solid ' + (type === 'success' ? 'green' : 'red'),
          color: type === 'success' ? 'green' : 'red',
          display: 'block'
        };
        var modal = $('.gdrive-to-posts-modal');
        if (modal.length) {
          modal.find('.msg').html(message);
          modal.css(css);
        }
        // hide the modal after being clicked
        modal.one('click', function () {
          modal.css('display', 'none');
        });
      }
    }, {
      key: 'templateSelect',
      value: function templateSelect() {
        var $selectBox = $('select[name="gdrive_to_posts_post_templates-template"]');
        $selectBox.on('click', function (evt) {
          var val = $(this).val();
          if (!val) {
            return false;
          }
          var data = {
            nonce: gdriveToPosts.nonce,
            template_index: val,
            action: 'gdrive_to_posts_get_template'
          };
          var theNewName = 'gdrive_to_posts_post_templates[' + val + '][template]';

          $.ajax({
            url: gdriveToPosts.ajaxURL,
            type: 'post',
            data: data,
            success: function success(resp) {
              if (resp && resp.success == 1) {
                $('#gdrive_to_posts_post_templates-template').prop('name', theNewName);
                // This is the returned index for the text editor.
                console.log('resp.template', resp.template);
              }
            }
          });
        });
      }
    }, {
      key: 'setupBtn',
      value: function setupBtn() {
        var _this2 = this;

        var $addNewButton = $('#gdriveToPostsAddNewTemplateBtn');

        if ($addNewButton.length) {
          $addNewButton.on('click', function (evt) {
            var fileID = $('input[name="gdrive-to-posts-new-file-id"]').val(),
                tempLabel = $('input[name="gdrive-to-posts-template-label"]').val();
            if (!fileID || !tempLabel) {
              _this2.displayModal('Enter a Google Sheets File ID and Template Label please!', 'error');
              return false;
            }
            // Disable the button
            $addNewButton.prop('disabled', 1);
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
              success: function success(resp) {
                if (resp && resp.success == 1) {
                  $('#gdrive-to-posts-templates').insertBefore($(resp.html), $addNewButton);
                }
              },
              done: function done() {
                // Enable the button again
                $addNewButton.prop('disabled', 0);
              }
            });
          });
        }
      }
    }]);

    return TemplateAjaxClass;
  })();

  var newTemplate = new TemplateAjaxClass($);
})(jQuery);
