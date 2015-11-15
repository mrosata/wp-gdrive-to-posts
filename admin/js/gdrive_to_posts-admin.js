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
    function TemplateAjaxClass() {
      var _this = this;

      _classCallCheck(this, TemplateAjaxClass);

      $(window).on('load', function () {
        _this.setupFetchFieldsButton();
        _this.setupBtn();
        _this.templateSelect();
      });
    }

    _createClass(TemplateAjaxClass, [{
      key: 'setupFetchFieldsButton',
      value: function setupFetchFieldsButton() {
        var fetchFieldsBtn = $('#get-gdrive-sheet-field-names');
        if (fetchFieldsBtn.length) {
          fetchFieldsBtn.on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var sheetID = fetchFieldsBtn.data('sheet-label');
            var data = {
              action: 'gdrive_to_posts_fetch_sheet_fields',
              nonce: gdriveToPosts.nonce,
              sheet_label: sheetID
            };
            $.ajax({
              url: gdriveToPosts.ajaxURL,
              type: 'post',
              dataType: 'json',
              data: data,
              success: function success(resp) {
                if (!resp || resp.success != 1) {
                  return false;
                }
                // Basically we should just be getting back a list of fields
                console.log(resp.fields);
              }
            });
          });
        }
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
            templateOptionLabel = 'gdrive_to_posts_templates',
            fetchFieldsBtn = $('#get-gdrive-sheet-field-names');

        $selectBox.on('change', function (evt) {
          var selectedOption = $(this).val(),
              editor = { $: $('#' + templateOptionLabel + '-editor') },
              hiddenTemplateFields = $('#gdrive-hidden-templates'),
              desiredTemplate = {};

          if (!editor.$.length || !hiddenTemplateFields.length) {
            return false;
          }
          // We need the name of editor to be able to change it out.
          editor.name = editor.$.prop('name');
          editor.content = editor.$.val();

          if (!selectedOption) {
            hideLastActiveTemplate();
            updateTextEditor('');
            fetchFieldsBtn.data('sheet-label', '').hide();
            console.log('changing content in the editor to nothing.');
            return false;
          }
          // We only want to see this button when there is a file id to show.
          fetchFieldsBtn.data('sheet-label', selectedOption).show();

          desiredTemplate.name = templateOptionLabel + '[' + selectedOption + ']';
          desiredTemplate.$ = hiddenTemplateFields.find('input[name="' + desiredTemplate.name + '"]');
          console.log('found desired template', desiredTemplate.name);
          desiredTemplate.content = desiredTemplate.$.length ? desiredTemplate.$.val() : '';

          // We need to make sure that the content from the current editor is hidden away correctly so that the
          // user can change back to it and also so that when the form is submitted it changes properly.
          hideLastActiveTemplate();
          if (desiredTemplate.$.length) {
            //desiredTemplate.$.remove();
            // now that the content from the editor has been saved to a hidden input, we can
            updateTextEditor(desiredTemplate.content);
          } else {
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
              console.log('editor has name %s and value %s.', editor.name, editor.content);

              if (hiddenTemplateFields.find('input[name="' + editor.name + '"]').length) {
                hiddenTemplateFields.find('input[name="' + editor.name + '"]').val(editor.content);
              } else {
                hiddenTemplateFields.append('<input name="' + editor.name + '" value="' + editor.content + '" type="hidden">');
              }
            }
          }
        }); // End selectbox.on('change');
      }
    }, {
      key: 'setupBtn',
      value: function setupBtn() {
        var _this2 = this;

        var $addNewButton = $('#gdriveToPostsAddNewTemplateBtn');

        if ($addNewButton.length) {
          $addNewButton.on('click', function (evt) {
            var fileID = $('input[name="gdrive-to-posts-template-sheet-id"]').val(),
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
                      templateHiddenInputs = $('#gdrive-hidden-templates');
                  if (templateDropdown.length && !!resp.html) {
                    templateDropdown.append(resp.html);
                  }
                  if (templateHiddenInputs.length && !!resp.hiddenHTML) {
                    templateHiddenInputs.append(resp.hiddenHTML);
                  }
                }
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

  var _iteratorNormalCompletion = true;
  var _didIteratorError = false;
  var _iteratorError = undefined;

  try {
    for (var _iterator = vals[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
      var val = _step.value;

      if (typeof val !== "undefined" && !Object.is(val, null)) {
        return val;
      }
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
}