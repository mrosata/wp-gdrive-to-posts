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
        this.setupFetchFieldsButton();
        this.setupBtn();
        this.templateSelect();
        this.setupGoogleTestBtn();
        this.setupTemplateTestBtn();
      });
    }

    setupFetchFieldsButton() {
      var fetchFieldsBtn = $('#get-gdrive-sheet-field-names');
      if (fetchFieldsBtn.length) {
        fetchFieldsBtn
          .on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var sheetID = $(this).data('sheet-label'),
              fieldsExplaination = $('.gdrive-template-fields-explanation');

            var data = {
              action: 'gdrive_to_posts_fetch_sheet_fields',
              nonce: gdriveToPosts.nonce,
              sheet_label: sheetID
            };

            // closes plain description + shows the .gdrive-template-fields-listings which holds the ul we will make.
            fieldsExplaination.removeClass('open');
            $.ajax({
              url: gdriveToPosts.ajaxURL,
              type: 'post',
              dataType: 'json',
              data: data,
              complete (resp) {
                var outputElem = $('.gdrive-template-fields-listings'),
                  fields,
                  num = 1,
                  output = '',
                  outputFields = '',
                  outputNums = '';
                if (!resp || !resp.responseJSON || resp.responseJSON.success != 1) {
                  output += `<h5>Could not find any fields for the Sheet File ID provided with template ${sheetID}!</h5>`;
                } else {
                  resp = resp.responseJSON;
                  fields = resp.fields
                  // Basically we should just be getting back a list of fields
                  console.log(resp.fields);

                  // Create the output for the fields if found
                  if (fields.length) {
                    output += `<p><span class="gdrive-bold">Here are your ${fields.length} variables.<\/span> `
                      +  'Type them as shown below and they will be replaced by column data as posts are created. '
                      +  'To change a variable name simply edit the top row of your Google Sheet!<dl>';

                    for (let field of fields) {
                      outputFields += `<code>{!!${field}!!}</code>`;
                      outputNums += `<code>{!#${num++}#!}</code>`;
                    }
                    output +=  `<dt>Named columns are referenced in <span class="gdrive-bold">"${sheetID}"<\/span> template using: <\/dt>`;
                    output += `<dd>${outputFields}</dd><dt>Numbered columns are referenced in <span class="gdrive-bold">"${sheetID}"<\/span> template using: <\/dt><dd>${outputNums}<\/dd>`;
                    output += `<\/dl><\/ul><\/p>`
                  }
                  else {
                    output += `<h5>Could not find any fields for the Sheet File ID provided with template ${sheetID}!</h5>`;
                  }
                }
                if (outputElem.length) {
                  outputElem.html(output);
                }

              }
            });
          });
      }
    }


    /**
     * Click Handler for Google Test Button
     */
    setupGoogleTestBtn () {
      var googleTestBtn = $('#google-client-test'),
        data;
      if (!googleTestBtn.length) {
        return false;
      }

      googleTestBtn.on('click.gdrive-to-posts', (evt) => {
        data = {
          nonce: gdriveToPosts.nonce,
          action: 'gdrive_to_posts_test_gclient'
        };

        $.ajax({
          url: gdriveToPosts.ajaxURL,
          type: 'post',
          dataType: 'json',
          data,
          complete (resp) {
            var output = '';
            if (!resp || typeof resp !== "object" || typeof resp.responseJSON !== "object") {
              output += '<h3>Status of Google Client:  NOT CONNECTED';
              output += '<h3>Status of GDrive Service: NOT CONNECTED';
            }
            var elem = $('.test-gclient-results');
            output += `<br><span>Status of Google Client: <\/span><span class="${( resp.responseJSON.gclient == 1 ? 'pass">' : 'fail">NOT ' )}CONNECTED<\/span>`;
            output += `<br><span>Status of GDrive Service:<\/span><span class="${( resp.responseJSON.gdrive == 1  ? 'pass">' : 'fail">NOT ' )}CONNECTED<\/span>`;

            // show the results in html.
            if (elem.length) {
              elem.html(output);
            }
          }
        });
      });
    }

  /**
     * Click Handler for Google Test Button
     */
    setupTemplateTestBtn () {
      var templateTestBtn = $('#sheet-template-tester'),
        data;
      if (!templateTestBtn.length) {
        return false;
      }

      templateTestBtn.on('click.gdrive-to-posts', function(evt) {
        evt.preventDefault();
        evt.stopPropagation();

        // This button gets sheetID like the fields button (through the select change method).
        var sheetID = $(this).data('sheet-label');
        var data = {
          action: 'gdrive_to_posts_parse_through_template',
          nonce: gdriveToPosts.nonce,
          sheet_label: sheetID
        };

        $.ajax({
          url: gdriveToPosts.ajaxURL,
          type: 'post',
          dataType: 'json',
          data,
          complete (resp) {
            var output = '';
            if (!resp || typeof resp !== "object" || typeof resp.responseJSON !== "object") {
              output += '<h3>The server didn\'t parse your template</h3>';
            }
            var elem = $('.test-preview-results');
            resp = resp.responseJSON;
            if (resp.success == 1) {
              output += resp.output;
            } else {
              output += "<br><span>Could not parse your template! Please check it for errors.<\/span>"
            }

            // show the results in html.
            if (elem.length) {
              elem.html(output);
            }
          }
        });
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
        templateOptionLabel = 'gdrive_to_posts_template_body',
        titlesOptionLabel = 'gdrive_to_posts_template_title',
        fetchFieldsBtn = $('#get-gdrive-sheet-field-names, #sheet-template-tester'),
        fieldsExplaination = $('.gdrive-template-fields-explanation'),
        outputElem = $('.gdrive-template-fields-listings');

      $selectBox.on('change', function (evt) {
          // doing this will hide the fields that were open from last template viewed
          fieldsExplaination.addClass('open');
          // Empty the elem that output template variables specific to each template
          outputElem.empty();
        var selectedOption = $(this).val(),
          editor = {$: $(`#${templateOptionLabel}-editor`)},
          title = {$: $('#post-title-template input')},
          hiddenTemplateFields = $('#gdrive-hidden-templates'),
          hiddenTitleFields = $('#hidden-title-templates'),
          desiredTemplate = {},
          desiredTitle = {};

        if (!editor.$.length || !hiddenTemplateFields.length) {return false;}
        // We need the name of editor to be able to change it out.
        editor.name = editor.$.prop('name');
        editor.content = editor.$.val();
        // This is the editing for the title fields.
        title.content = title.$.prop('name');
        title.content = title.$.val();


        // show the dropdown categories option for this template
        showActiveCategoryDropDown(selectedOption);
        showActiveTemplateTags(selectedOption);
        showActiveTemplateAuthor(selectedOption);
        showActiveTemplateTitle(selectedOption);

        if (!selectedOption) {
          hideLastActiveTemplate();
          // empty the text editor
          updateTextEditor('');
          // empty the title val
          title.$.val('');
          fetchFieldsBtn.data('sheet-label', '').hide();
          console.log('changing content in the editor to nothing.');
          return false;
        }
        // We only want to see this button when there is a file id to show.
        fetchFieldsBtn.show().data('sheet-label', selectedOption);


        desiredTemplate.name = `${templateOptionLabel}[${selectedOption}]`;
        desiredTemplate.$ = hiddenTemplateFields.find(`input[name="${desiredTemplate.name}"]`);
        desiredTemplate.content = desiredTemplate.$.length ? desiredTemplate.$.val() : '';
        desiredTitle.name = `${titlesOptionLabel}[${selectedOption}]`;
        desiredTitle.$ = hiddenTitleFields.find(`input[name="${desiredTitle.name}"]`);
        console.log('found desired template', desiredTemplate.name, desiredTitle.name);
        desiredTitle.content = desiredTitle.$.length ? desiredTitle.$.val() : '';

        // We need to make sure that the content from the current editor is hidden away correctly so that the
        // user can change back to it and also so that when the form is submitted it changes properly.
        hideLastActiveTemplate();

        if (desiredTitle.$.length) {
          title.$.val(desiredTitle.content);
        }
        if (desiredTemplate.$.length) {
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
        title.$.prop('name', desiredTitle.name);
        title.$.attr('name', desiredTitle.name);
        updateTextEditor(desiredTemplate.content);
        /**
         * Utility function to set content of editor
         * @param val
         */
        function updateTextEditor(val = '') {
          if (typeof tinyMCE !== "undefined" && tinyMCE.get(`${templateOptionLabel}-editor`) !== null) {
            tinyMCE.get(`${templateOptionLabel}-editor`).setContent(val, {format : 'raw'});
          } else {
            editor.$.empty().val(val);
          }
        }

        function getTextEditorContent() {
          if (!!tinyMCE && tinyMCE.get(`${templateOptionLabel}-editor`) !== null) {
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
        function showActiveCategoryDropDown(label) {
          var dropdownSelector = $(`.template-category.template-category-${label}`),
              allSelectors = $('.template-category');
          if (allSelectors.length) {
            allSelectors.hide();
            if (label && dropdownSelector.length) {
              dropdownSelector.show()
            }
          }
        }

        /**
         * Show the template tags input for the current template
         */
        function showActiveTemplateTags(label) {
          var tagsInputWrapper = $(`.template-tags.template-${label}`),
              allTagsInputs = $('.template-tags');
          if (allTagsInputs.length) {
            allTagsInputs.hide();
            if (label && tagsInputWrapper.length) {
              tagsInputWrapper.show();
            }
          }
        }


        /**
         * Show the template tags input for the current template
         */
        function showActiveTemplateTitle(label) {
          var titelInputWrapper = $(`.template-title.template-${label}`),
              allTitelInputs = $('.template-title');
          if (allTitelInputs.length) {
            allTitelInputs.hide();
            if (label && titelInputWrapper.length) {
              titelInputWrapper.show();
            }
          }
        }

        /**
         * Show the author input for the current template
         * @param label
         */
        function showActiveTemplateAuthor(label) {
          var authorInputWrapper = $(`.template-author.template-${label}`),
              allAuthorInputs = $('.template-author');
          if (allAuthorInputs.length) {
            allAuthorInputs.hide();
            if (label && authorInputWrapper.length) {
              authorInputWrapper.show();
            }
          }
        }
      });  // End selectbox.on('change');

    }


    setupBtn () {
      var $addNewButton = $('#gdriveToPostsAddNewTemplateBtn');

      if ($addNewButton.length) {
        $addNewButton.on('click', (evt) => {
          let fileID = $('input[name="template-sheet-id"]').val(),
            tempLabel = $('input[name="template-label"]').val();
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
                // Add new label to the dropdown menu
                if (templateDropdown.length && !!resp.html) {
                  templateDropdown.append(resp.html);
                }
                // Add new template body input to the collection of hidden inputs
                if (templateHiddenInputs.length && !!resp.hiddenHTML) {
                  templateHiddenInputs.append(resp.hiddenHTML);
                }
              }

              $('input[name="template-sheet-id"]').val('');
              $('input[name="template-label"]').val('');
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
