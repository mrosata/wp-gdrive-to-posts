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
    constructor() {
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
      var $selectBox = $('select[name="choose-editor-template"]'),
          templateOptionLabel = 'gdrive_to_posts_templates';

      $selectBox.on('change', function (evt) {
        var selectedOption = $(this).val(),
          editor = {$: $(`#${templateOptionLabel}-editor`)},
          hiddenTemplateFields = $('#gdrive-hidden-templates'),
          desiredTemplate = {};

        if (!editor.$.length || !hiddenTemplateFields.length) {return false;}
        // We need the name of editor to be able to change it out.
        editor.name = editor.$.prop('name');
        editor.content = editor.$.val();



        if (!selectedOption) {
          hideLastActiveTemplate();
          updateTextEditor('');
          console.log('changing content in the editor to nothing.');
          return false;
        }

        desiredTemplate.name = `${templateOptionLabel}[${selectedOption}]`;
        desiredTemplate.$ = hiddenTemplateFields.find(`input[name="${desiredTemplate.name}"]`);
        console.log('found desired template', desiredTemplate.name);
        desiredTemplate.content = desiredTemplate.$.length? desiredTemplate.$.val() : '';

        // We need to make sure that the content from the current editor is hidden away correctly so that the
        // user can change back to it and also so that when the form is submitted it changes properly.
        hideLastActiveTemplate();
        if (desiredTemplate.$.length) {
          //desiredTemplate.$.remove();
          // now that the content from the editor has been saved to a hidden input, we can
          updateTextEditor(desiredTemplate.content);
        }
        else {
          if (!!window.gdriveToPosts._gdriveErrorsOn) {
            throw new Error("There is no template field with that name!");
          }
        }
        editor.$.prop('name', desiredTemplate.name);
        editor.$.attr('name', desiredTemplate.name);
        updateTextEditor(desiredTemplate.content);
        /**
         * Utility function to set content of editor
         * @param val
         */
        function updateTextEditor(val = '') {
          if (tinyMCE.get(`${templateOptionLabel}-editor`) !== null) {
            tinyMCE.get(`${templateOptionLabel}-editor`).setContent(val, {format : 'raw'});
          } else {
            editor.$.empty().val(val);
          }
        }

        function getTextEditorContent() {
          if (tinyMCE.get(`${templateOptionLabel}-editor`) !== null) {
            return firstDefined(tinyMCE.get(`${templateOptionLabel}-editor`).getContent(), '');
          } else {
            return firstDefined(editor.$.val(), '');
          }
        }


        /**
         * Utility for this method. Swaps content f
         */
        function hideLastActiveTemplate() {
          if (editor.name != '') {
            editor.content = firstDefined( getTextEditorContent() );
            console.log('editor has name %s and value %s.', editor.name, editor.content);

            if (hiddenTemplateFields.find(`input[name="${editor.name}"]`).length) {
              hiddenTemplateFields.find(`input[name="${editor.name}"]`).val(editor.content);
            } else {
              hiddenTemplateFields.append(`<input name="${editor.name}" value="${editor.content}" type="hidden">`);
            }
          }
        }

      });  // End selectbox.on('change');

    }


    setupBtn () {
      var $addNewButton = $('#gdriveToPostsAddNewTemplateBtn');

      if ($addNewButton.length) {
        $addNewButton.on('click', (evt) => {
          let fileID = $('input[name="gdrive-to-posts-template-sheet-id"]').val(),
            tempLabel = $('input[name="gdrive-to-posts-template-label"]').val();
          if (!fileID  || !tempLabel) {
            this.displayModal('Enter a Google Sheets File ID and Template Label please!', 'error');
            return false;
          }
          // Disable the button
          $addNewButton
            .prop('disabled', 1);
          // Create the data needed for WP ajax action
          var data = {
            nonce: gdriveToPosts.nonce,
            new_sheet_id: fileID,
            new_template_label: tempLabel,
            action: 'gdrive_to_posts_add_new_template'
          };


          $.ajax({
            url: gdriveToPosts.ajaxURL,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function (resp) {
              if (resp && resp.success == 1 && resp.html && resp.hiddenHTML) {
                var templateDropdown = $('select[name="choose-editor-template"]'),
                    templateHiddenInputs = $('#gdrive-hidden-templates');
                if (templateDropdown.length && !!resp.html) {
                  templateDropdown.append(resp.html);
                }
                if (templateHiddenInputs.length && !!resp.hiddenHTML) {
                  templateHiddenInputs.append(resp.hiddenHTML);
                }
              }
            },
            complete: (error) => {
              if (!!window.gdriveToPosts._gdriveErrorsOn) {
                throw new Error(error);
              }
              // Enable the button again regardless of how ajax response comes back.
              $addNewButton.prop('disabled', 0);
            }
          });
        });
      }
    }
  }

  var newTemplate = new TemplateAjaxClass();

})( jQuery );





/**
 * Return the first argument that isn't null or undefined
 * @param vals
 * @returns {*}
 */
function firstDefined(...vals) {
  "use strict";
  for (let val of vals) {
    if (typeof val !== "undefined" && !Object.is(val, null)) {
      return val;
    }
  }
}
