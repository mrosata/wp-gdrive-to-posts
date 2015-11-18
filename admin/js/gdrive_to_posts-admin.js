'use strict';

var _createClass = (function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; })();

function _typeof(obj) { return obj && typeof Symbol !== "undefined" && obj.constructor === Symbol ? "symbol" : typeof obj; }

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
    function TemplateAjaxClass() {
      var _this = this;

      _classCallCheck(this, TemplateAjaxClass);

      $(window).on('load', function () {
        _this.setupFetchFieldsButton();
        _this.createNewTemplateBtn();
        _this.templateSelect();
        _this.setupGoogleTestBtn();
        _this.setupTemplateTestBtn();
        _this.deleteTemplateBtn();
      });
    }

    /**
     * Call Ajax hook to parse the top row of an CSV and get the fields back to show the user.
     */

    _createClass(TemplateAjaxClass, [{
      key: 'setupFetchFieldsButton',
      value: function setupFetchFieldsButton() {
        var fetchFieldsBtn = $('#get-gdrive-sheet-field-names');
        if (fetchFieldsBtn.length) {
          fetchFieldsBtn.on('click', function (e) {
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
              complete: function complete(resp) {
                var outputElem = $('.gdrive-template-fields-listings'),
                    fields,
                    num = 1,
                    output = '',
                    outputFields = '',
                    outputNums = '';
                if (!resp || !resp.responseJSON || resp.responseJSON.success != 1) {
                  output += '<h5>Could not find any fields for the Sheet File ID provided with template ' + sheetID + '!</h5>';
                } else {
                  resp = resp.responseJSON;
                  // Basically we should just be getting back a list of fields
                  fields = resp.fields;

                  // Create the output for the fields if found
                  if (fields.length) {
                    output += '<p><span class="gdrive-bold">Here are your ' + fields.length + ' variables.</span> ' + 'Type them as shown below and they will be replaced by column data as posts are created. ' + 'To change a variable name simply edit the top row of your Google Sheet!<dl>';

                    var _iteratorNormalCompletion = true;
                    var _didIteratorError = false;
                    var _iteratorError = undefined;

                    try {
                      for (var _iterator = fields[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                        var field = _step.value;

                        outputFields += '<code>{!!' + field + '!!}</code>';
                        outputNums += '<code>{!#' + num++ + '#!}</code>';
                      }
                    } catch (err) {
                      _didIteratorError = true;
                      _iteratorError = err;
                    } finally {
                      try {
                        if (!_iteratorNormalCompletion && _iterator.return) {
                          _iterator.return();
                        }
                      } finally {
                        if (_didIteratorError) {
                          throw _iteratorError;
                        }
                      }
                    }

                    output += '<dt>Named columns are referenced in <span class="gdrive-bold">"' + sheetID + '"</span> template using: </dt>';
                    output += '<dd>' + outputFields + '</dd><dt>Numbered columns are referenced in <span class="gdrive-bold">"' + sheetID + '"</span> template using: </dt><dd>' + outputNums + '</dd>';
                    output += '</dl></ul></p>';
                  } else {
                    output += '<h5>Could not find any fields for the Sheet File ID provided with template ' + sheetID + '!</h5>';
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

    }, {
      key: 'setupGoogleTestBtn',
      value: function setupGoogleTestBtn() {
        var googleTestBtn = $('#google-client-test'),
            data;
        if (!googleTestBtn.length) {
          return false;
        }

        googleTestBtn.on('click.gdrive-to-posts', function (evt) {
          data = {
            nonce: gdriveToPosts.nonce,
            action: 'gdrive_to_posts_test_gclient'
          };

          $.ajax({
            url: gdriveToPosts.ajaxURL,
            type: 'post',
            dataType: 'json',
            data: data,
            complete: function complete(resp) {
              var output = '';
              if (!resp || (typeof resp === 'undefined' ? 'undefined' : _typeof(resp)) !== "object" || _typeof(resp.responseJSON) !== "object") {
                output += '<h3>Status of Google Client:  NOT CONNECTED';
                output += '<h3>Status of GDrive Service: NOT CONNECTED';
              }
              var elem = $('.test-gclient-results');
              output += '<br><span>Status of Google Client: </span><span class="' + (resp.responseJSON.gclient == 1 ? 'pass">' : 'fail">NOT ') + 'CONNECTED</span>';
              output += '<br><span>Status of GDrive Service:</span><span class="' + (resp.responseJSON.gdrive == 1 ? 'pass">' : 'fail">NOT ') + 'CONNECTED</span>';

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

    }, {
      key: 'setupTemplateTestBtn',
      value: function setupTemplateTestBtn() {
        var templateTestBtn = $('#sheet-template-tester'),
            data;
        if (!templateTestBtn.length) {
          return false;
        }

        templateTestBtn.on('click.gdrive-to-posts', function (evt) {
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
            data: data,
            complete: function complete(resp) {
              var output = '';
              if (!resp || (typeof resp === 'undefined' ? 'undefined' : _typeof(resp)) !== "object" || _typeof(resp.responseJSON) !== "object") {
                output += '<h3>The server didn\'t parse your template</h3>';
              }
              var elem = $('.test-preview-results');
              resp = resp.responseJSON;
              if (resp.success == 1) {
                output += resp.output;
              } else {
                output += "<br><span>Could not parse your template! Please check it for errors.<\/span>";
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
       * Click Handler for Google Test Button
       */

    }, {
      key: 'deleteTemplateBtn',
      value: function deleteTemplateBtn() {
        var templateDeleteBtn = $('#gdrive-delete-template'),
            _self = this; // Need this in a deeper scope. Arrows won't help.
        if (!templateDeleteBtn.length) {
          return false;
        }

        templateDeleteBtn.on('click.gdrive-to-posts', function (evt) {
          evt.preventDefault();
          evt.stopPropagation();

          // This button gets sheetID like the fields button (through the select change method).
          var sheetLabel = $(this).data('sheet-label');
          var data = {
            action: 'gdrive_to_posts_delete_template',
            nonce: gdriveToPosts.nonce,
            sheet_label: sheetLabel
          };

          $.ajax({
            url: gdriveToPosts.ajaxURL,
            type: 'post',
            dataType: 'json',
            data: data,
            complete: function complete(resp) {
              var output = '';
              if (!resp || (typeof resp === 'undefined' ? 'undefined' : _typeof(resp)) !== "object" || _typeof(resp.responseJSON) !== "object") {
                output += '<h3>The server did not respond well.</h3>';
              } else {
                var templateSelect = $('select[name="choose-editor-template"]'),
                    individualOptions = $('.gdrive-template-individual-settings.all-templates'),
                    hiddenTemplateBody = $('input[name="gdrive_to_posts_template_body[' + sheetLabel + ']"]'),
                    fieldsToRemove = '.template-field-container.template-' + sheetLabel,
                    optsToRemove = 'option[value="' + sheetLabel + '"]';

                if (individualOptions.find(fieldsToRemove).length) {
                  individualOptions.find(fieldsToRemove).remove();
                }

                if (templateSelect.find(optsToRemove).length) {
                  templateSelect.find(optsToRemove).remove();
                  templateSelect.find('option').first().prop('selected', 1);
                  if (typeof _self.deleteLastActiveTemplate === "function") {
                    _self.deleteLastActiveTemplate();
                  }
                  templateSelect.trigger('change');
                }
                if (hiddenTemplateBody.length) {
                  hiddenTemplateBody.remove();
                }
                // Hide the delete button, nothing to delete now.
                templateDeleteBtn.hide();
              }

              //TODO: Setup what happens when delete goes through.
            }
          });
        });
      }

      /**
       * Create CSS for a modal to show messages on the form.
       * @param message
       * @param type
       */

    }, {
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
        var $selectBox = $('select[name="choose-editor-template"]'),
            templateOptionLabel = 'gdrive_to_posts_template_body',
            titlesOptionLabel = 'gdrive_to_posts_template_title',
            variousBtnsWithInfo = $('#get-gdrive-sheet-field-names, #sheet-template-tester, #gdrive-delete-template'),
            fieldsExplaination = $('.gdrive-template-fields-explanation'),
            outputElem = $('.gdrive-template-fields-listings'),
            _self = this; // Need this to tack on deleteLastActiveTemplate to this object in different scope.

        $selectBox.on('change', function (evt) {
          // doing this will hide the fields that were open from last template viewed
          fieldsExplaination.addClass('open');
          // Empty the elem that output template variables specific to each template
          outputElem.empty();
          var selectedOption = $(this).val(),
              editor = { $: $('#' + templateOptionLabel + '-editor') },
              title = { $: $('#post-title-template input') },
              hiddenTemplateFields = $('#gdrive-hidden-templates'),
              hiddenTitleFields = $('#hidden-title-templates'),
              desiredTemplate = {},
              desiredTitle = {};

          if (!editor.$.length || !hiddenTemplateFields.length) {
            return false;
          }
          // We need the name of editor to be able to change it out.
          editor.name = editor.$.prop('name');
          editor.content = editor.$.val();
          // This is the editing for the title fields.
          title.content = title.$.prop('name');
          title.content = title.$.val();

          // show the data, categories, author, tags, title for this template.
          showActiveTemplateOptionFields(selectedOption);

          if (!selectedOption) {
            hideLastActiveTemplate();
            // empty the text editor
            updateTextEditor('');
            // empty the title val
            title.$.val('');
            variousBtnsWithInfo.data('sheet-label', '').hide();

            return false;
          }
          // We only want to see this button when there is a file id to show.
          variousBtnsWithInfo.show().data('sheet-label', selectedOption);

          desiredTemplate.name = templateOptionLabel + '[' + selectedOption + ']';
          desiredTemplate.$ = hiddenTemplateFields.find('input[name="' + desiredTemplate.name + '"]');
          desiredTemplate.content = desiredTemplate.$.length ? desiredTemplate.$.val() : '';
          desiredTitle.name = titlesOptionLabel + '[' + selectedOption + ']';
          desiredTitle.$ = hiddenTitleFields.find('input[name="' + desiredTitle.name + '"]');
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
          } else {
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
          function updateTextEditor() {
            var val = arguments.length <= 0 || arguments[0] === undefined ? '' : arguments[0];

            if (typeof tinyMCE !== "undefined" && tinyMCE.get(templateOptionLabel + '-editor') !== null) {
              tinyMCE.get(templateOptionLabel + '-editor').setContent(val, { format: 'raw' });
            } else {
              editor.$.empty().val(val);
            }
          }

          function getTextEditorContent() {
            if (!!tinyMCE && tinyMCE.get(templateOptionLabel + '-editor') !== null) {
              return firstDefined(tinyMCE.get(templateOptionLabel + '-editor').getContent(), '');
            } else {
              return firstDefined(editor.$.val(), '');
            }
          }

          /**
           * Utility for this method. Swaps content f
           */
          function hideLastActiveTemplate() {
            if (editor.name != '') {
              editor.content = firstDefined(getTextEditorContent());

              if (hiddenTemplateFields.find('input[name="' + editor.name + '"]').length) {
                hiddenTemplateFields.find('input[name="' + editor.name + '"]').val(editor.content);
              } else {
                hiddenTemplateFields.append('<input name="' + editor.name + '" value="' + editor.content + '" type="hidden">');
              }
            }
          }

          /**
           * This should only be called from outside to remove the templates
           * hidden field so that on a 'save settings' the deleted template isn't
           * still present in the list of available templates.
           */
          function deleteLastActiveTemplate() {
            updateTextEditor();
            editor.name = '';
            editor.content = '';
            editor.$.prop('name', '');
          }
          // It will have to be called from outer function
          _self.deleteLastActiveTemplate = deleteLastActiveTemplate;

          function showActiveTemplateOptionFields(label) {
            var selectedTemplateOpts = $('.gdrive-template-individual-settings .template-' + label),
                allTemplateOpts = $('.template-field-container');
            if (allTemplateOpts.length) {
              allTemplateOpts.hide();
              if (label && selectedTemplateOpts.length) {
                selectedTemplateOpts.show();
              }
            }
          }
        }); // End selectbox.on('change');
      }
    }, {
      key: 'createNewTemplateBtn',
      value: function createNewTemplateBtn() {
        var _this2 = this;

        var $addNewButton = $('#gdriveToPostsAddNewTemplateBtn');

        if ($addNewButton.length) {
          $addNewButton.on('click', function (evt) {
            var fileID = $('input[name="template-sheet-id"]').val(),
                tempLabel = $('input[name="template-label"]').val();
            if (!fileID || !tempLabel) {
              _this2.displayModal('Enter a Google Sheets File ID and Template Label please!', 'error');
              return false;
            }
            // Disable the button
            $addNewButton.prop('disabled', 1);
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
              success: function success(resp) {
                if (resp && resp.success == 1 && resp.html && resp.hiddenHTML) {
                  var templateDropdown = $('select[name="choose-editor-template"]'),
                      templateHiddenInputs = $('.gdrive-template-individual-settings.all-templates');
                  // Add new label to the dropdown menu
                  if (templateDropdown.length && !!resp.html) {
                    templateDropdown.append(resp.html);
                  }
                  // Add new template body input to the collection of hidden inputs
                  if (templateHiddenInputs.length && !!resp.hiddenHTML) {
                    // We only want to append new template settings if it is new. It could just be an updated
                    // label -> ID relationship
                    if (!templateHiddenInputs.find('.template-field-container.template-' + tempLabel).length) {
                      templateHiddenInputs.append(resp.hiddenHTML);
                    }
                  }
                }

                $('input[name="template-sheet-id"]').val('');
                $('input[name="template-label"]').val('');
              },
              complete: function complete(error) {
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
    }]);

    return TemplateAjaxClass;
  })();

  var newTemplate = new TemplateAjaxClass();
})(jQuery);

/**
 * Return the first argument that isn't null or undefined
 * @param vals
 * @returns {*}
 */
function firstDefined() {
  "use strict";

  for (var _len = arguments.length, vals = Array(_len), _key = 0; _key < _len; _key++) {
    vals[_key] = arguments[_key];
  }

  var _iteratorNormalCompletion2 = true;
  var _didIteratorError2 = false;
  var _iteratorError2 = undefined;

  try {
    for (var _iterator2 = vals[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
      var val = _step2.value;

      if (typeof val !== "undefined" && !Object.is(val, null)) {
        return val;
      }
    }
  } catch (err) {
    _didIteratorError2 = true;
    _iteratorError2 = err;
  } finally {
    try {
      if (!_iteratorNormalCompletion2 && _iterator2.return) {
        _iterator2.return();
      }
    } finally {
      if (_didIteratorError2) {
        throw _iteratorError2;
      }
    }
  }
}