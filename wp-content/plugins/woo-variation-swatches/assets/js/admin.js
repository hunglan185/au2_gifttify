/*!
 * Variation Swatches for WooCommerce 
 * 
 * Author: Emran Ahmed ( emran.bd.08@gmail.com ) 
 * Date: 6/13/2022, 4:04:45 PM
 * Released under the GPLv3 license.
 */
/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/js/PluginHelper.js":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "PluginHelper", function() { return PluginHelper; });
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/*global woo_variation_swatches_admin, wp, woocommerce_admin_meta_boxes*/
var PluginHelper = function ($) {
  var PluginHelper = /*#__PURE__*/function () {
    function PluginHelper() {
      _classCallCheck(this, PluginHelper);
    }

    _createClass(PluginHelper, null, [{
      key: "GWPAdmin",
      value: function GWPAdmin() {
        if ($().gwp_live_feed) {
          $().gwp_live_feed();
        }

        if ($().gwp_deactivate_popup) {
          $().gwp_deactivate_popup('woo-variation-swatches');
        }
      }
    }, {
      key: "GalleryNotification",
      value: function GalleryNotification() {
        $('.woocommerce_variation').each(function () {
          var optionsWrapper = $(this).find('.options:first');
          var galleryWrapper = $(this).find('.woo-variation-gallery-message');
          galleryWrapper.insertBefore(optionsWrapper);
        });
        $('input.upload_image_id').on('change', function (event) {
          var value = $.trim($(this).val());

          if (value) {
            $(this).closest('.data').find('.woo-variation-gallery-message').addClass('enable');
          } else {
            $(this).closest('.data').find('.woo-variation-gallery-message').removeClass('enable');
          }
        });
        $('a.install-woo-variation-gallery-action').on('click', function (event) {
          event.preventDefault();
          var $parent = $(this).parent();
          var installing = $parent.data('installing');
          var activated = $parent.data('activated');
          var nonce = $parent.data('nonce');
          $parent.text(installing);
          wp.ajax.send('install_woo_variation_gallery', {
            data: {
              'nonce': nonce
            },
            success: function success(response) {
              $parent.text(activated);

              _.delay(function () {
                $('.woocommerce_variable_attributes .woo-variation-gallery-message').remove();
              }, 5000);
            },
            error: function error(response) {
              $parent.text(activated);

              _.delay(function () {
                $('.woocommerce_variable_attributes .woo-variation-gallery-message').remove();
              }, 5000);
            }
          });
        });
      }
    }, {
      key: "PaginationAjax",
      value: function PaginationAjax($product_id, $attribute_id, $attribute_name, $offset, $selector) {
        $.ajax({
          global: false,
          url: woo_variation_swatches_admin.wc_ajax_url.toString().replace('%%endpoint%%', 'woo_variation_swatches_load_product_terms'),
          method: 'POST',
          data: {
            offset: $offset,
            product_id: $product_id,
            attribute_id: $attribute_id,
            attribute_name: $attribute_name,
            nonce: woo_variation_swatches_admin.nonce
          },
          beforeSend: function beforeSend(xhr, settings) {
            $selector.block({
              message: null,
              overlayCSS: {
                background: '#DDDDDD',
                opacity: 0.6
              }
            });
          }
        }).fail(function (jqXHR, textStatus) {
          console.error("not available on: ".concat($product_id, " ").concat($attribute_key, "."), textStatus);
        }).always(function () {
          $selector.unblock();
        }).done(function (termsMarkup) {
          if (termsMarkup) {
            $selector.html(termsMarkup);
            $(document.body).trigger('woo_variation_swatches_product_term_paging_done', $selector); //$('#woocommerce-product-data').trigger('woocommerce_variations_loaded');
          }
        });
      }
    }, {
      key: "MetaboxToggle",
      value: function MetaboxToggle() {
        // Meta-Boxes - Open/close
        var $wrapper = $('#woo_variation_swatches_variation_product_options');
        $wrapper.on('click', '.wc-metabox > h4', function (event) {
          var box = $(this).parent('.wc-metabox');
          var content = $(this).next('.wc-metabox-content'); // If the user clicks on some form input inside the h3, like a select list (for variations), the box should not be toggled

          if ($(event.target).filter(':input, option, .sort, select, label, .select2-selection__rendered').length) {
            return false;
          }

          if (box.hasClass('closed')) {
            box.removeClass('closed open').addClass('open');
            content.slideDown();
          } else {
            box.removeClass('closed open').addClass('closed');
            content.slideUp();
          }
        });
      }
    }, {
      key: "AttributeTypeSwitch",
      value: function AttributeTypeSwitch() {
        var $wrapper = $('#woo_variation_swatches_variation_product_options') // ATTRIBUTE TYPE
        .on('change', 'select.woo_variation_swatches_attribute_type_switch', function (event) {
          var value = $(this).val();

          if (['select'].includes(value)) {//    $(this).closest('.wc-metabox').find('.wc-metabox-content select.woo_variation_swatches_attribute_term_type_switch').val('').trigger('change');
          }

          if (['image', 'color', 'button'].includes(value)) {
            $(this).closest('.wc-metabox').find('.wc-metabox-content select.woo_variation_swatches_attribute_term_type_switch').val(value).trigger('change');
          }
        }) // TERM TYPE
        .on('change', 'select.woo_variation_swatches_attribute_term_type_switch', function (event) {
          var attribute_type = $(this).closest('.woo-variation-swatches-attribute-options-wrapper').find('select.woo_variation_swatches_attribute_type_switch').val();

          if ($(this).val() !== attribute_type) {
            $(this).closest('.woo-variation-swatches-attribute-options-wrapper').find('select.woo_variation_swatches_attribute_type_switch').val('mixed').trigger('change');
          }
        });
      }
    }, {
      key: "SetAttributeTypePaging",
      value: function SetAttributeTypePaging(selector) {
        var attribute_type_val = $(selector).closest('.woo-variation-swatches-attribute-options-wrapper').find('select.woo_variation_swatches_attribute_type_switch').val();
        var new_mode = $(selector).find('select.woo_variation_swatches_attribute_term_type_switch').hasClass('new-mode'); // Set based on attribute value

        if (['image', 'color', 'button'].includes(attribute_type_val)) {
          $(selector).find('select.woo_variation_swatches_attribute_term_type_switch.new-mode').val(attribute_type_val).trigger('change');
        }

        $(selector).find('select.woo_variation_swatches_attribute_term_type_switch.new-mode').each(function () {
          var value = $(this).val();

          if (!value) {//    $(this).trigger('change')
          }
        });
      }
    }, {
      key: "LoadProductAttributes",
      value: function LoadProductAttributes() {
        $('#woocommerce-product-data').on('woocommerce_variations_loaded', function (event) {
          var $wrapper = $('#woo_variation_swatches_variation_product_options');
          var product_id = $wrapper.data('product_id');
          $.ajax({
            global: false,
            url: woo_variation_swatches_admin.wc_ajax_url.toString().replace('%%endpoint%%', 'woo_variation_swatches_load_product_options'),
            method: 'POST',
            data: {
              product_id: product_id,
              nonce: woo_variation_swatches_admin.nonce
            },
            beforeSend: function beforeSend(xhr, settings) {
              $('#woo_variation_swatches_variation_product_options_inner').block({
                message: null,
                overlayCSS: {
                  background: '#DDDDDD',
                  opacity: 0.6
                }
              });
            }
          }).fail(function (jqXHR, textStatus) {
            console.error("not load option: ".concat(product_id, "."), textStatus);
          }).always(function () {
            $('#woo_variation_swatches_variation_product_options_inner').unblock();
          }).done(function (contents) {
            $(document.body).trigger('woo_variation_swatches_variation_product_options_loaded', product_id);
          });
        });
      }
    }, {
      key: "SaveProductAttributes",
      value: function SaveProductAttributes() {
        var changed = false;
        var $wrapper = $('#woo_variation_swatches_variation_product_options');
        $wrapper.on('change input color-changed', ':input:not(.wvs-skip-field)', function () {
          if (!changed) {
            window.onbeforeunload = function () {
              return woo_variation_swatches_admin.nav_warning;
            };

            changed = true;
          }
        }).on('click', '.woo_variation_swatches_save_product_attributes, .woo_variation_swatches_reset_product_attributes', function () {
          window.onbeforeunload = '';
        }).on('click', '.woo_variation_swatches_save_product_attributes', function (event) {
          event.preventDefault();
          var data = $wrapper.find(':input:not(.wvs-skip-field)').serializeJSON({
            disableColonTypes: true
          });
          var key = Object.keys(data) ? Object.keys(data).shift() : 'woo_variation_swatches_product_options';
          var product_id = $wrapper.data('product_id');
          var timeOut;
          $.ajax({
            global: false,
            url: woo_variation_swatches_admin.wc_ajax_url.toString().replace('%%endpoint%%', 'woo_variation_swatches_save_product_options'),
            method: 'POST',
            data: {
              data: data[key],
              product_id: product_id,
              nonce: woo_variation_swatches_admin.nonce
            },
            beforeSend: function beforeSend(xhr, settings) {
              clearTimeout(timeOut);
              $('#woo_variation_swatches_variation_product_options_inner').block({
                message: null,
                overlayCSS: {
                  background: '#DDDDDD',
                  opacity: 0.6
                }
              });
            }
          }).fail(function (jqXHR, textStatus) {
            console.error("not saved on: ".concat(product_id, "."), textStatus);
          }).always(function () {
            $('#woo_variation_swatches_variation_product_options_inner').unblock();
          }).done(function (contents) {
            $('#saved-message').show();
            timeOut = setTimeout(function () {
              $('#saved-message').hide(600);
            }, 5000);
            $(document.body).trigger('woo_variation_swatches_variation_product_options_saved', product_id);
          });
        }).on('click', '.woo_variation_swatches_reset_product_attributes', function (event) {
          event.preventDefault();

          if (confirm(woo_variation_swatches_admin.reset_notice)) {
            var product_id = $(this).data('product_id');
            $.ajax({
              global: false,
              url: woo_variation_swatches_admin.wc_ajax_url.toString().replace('%%endpoint%%', 'woo_variation_swatches_reset_product_options'),
              method: 'POST',
              data: {
                product_id: product_id,
                nonce: woo_variation_swatches_admin.nonce
              },
              beforeSend: function beforeSend(xhr, settings) {
                $('#woo_variation_swatches_variation_product_options_inner').block({
                  message: null,
                  overlayCSS: {
                    background: '#DDDDDD',
                    opacity: 0.6
                  }
                });
              }
            }).fail(function (jqXHR, textStatus) {
              console.error("not reset on: ".concat(product_id, "."), textStatus);
            }).always(function () {
              $('#woo_variation_swatches_variation_product_options_inner').unblock();
            }).done(function (contents) {
              var $html = $(contents).find('#woo_variation_swatches_variation_product_options_inner').html();
              $('#woo_variation_swatches_variation_product_options_inner').html($html); // $('#woocommerce-product-data').trigger('woocommerce_variations_loaded')

              $(document.body).trigger('woo_variation_swatches_variation_product_options_reset', product_id);
            });
          }
        });
      }
    }, {
      key: "ResetProductAttributes",
      value: function ResetProductAttributes() {
        var $wrapper = $('#woo_variation_swatches_variation_product_options');
      }
    }, {
      key: "Pagination",
      value: function Pagination() {
        var _this = this;

        var changed = false;
        var $wrapper = $('#woo_variation_swatches_variation_product_options');
        $wrapper.on('change input color-changed', ':input:not(.wvs-skip-field)', function (event) {
          if (!changed) {
            changed = true;
          }
        }).on('click', '.woo_variation_swatches_reset_product_attributes', function (event) {
          event.preventDefault();
          changed = false;
        }).on('click', '.woo_variation_swatches_save_product_attributes', function (event) {
          event.preventDefault();
          changed = false;
        }).on('click', '.first-page:not(.disabled), .prev-page:not(.disabled), .last-page:not(.disabled), .next-page:not(.disabled)', function (event) {
          if (changed) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            alert(woo_variation_swatches_admin.nav_warning);
          }
        }).on('click', '.first-page.disabled, .prev-page.disabled, .last-page.disabled, .next-page.disabled', function (event) {
          event.preventDefault();
        }).on('click', '.first-page:not(.disabled)', function (event) {
          event.preventDefault();
          var $selector = $(event.currentTarget).closest('.product-term-label-settings').find('.product-term-label-settings-contents');
          var $this = $(event.currentTarget).closest('.product-term-label-settings-pagination');
          var $product_id = $selector.data('product_id');
          var $attribute_id = $selector.data('attribute_id');
          var $attribute_name = $selector.data('attribute_name');
          var $offset = 0;

          _this.PaginationAjax($product_id, $attribute_id, $attribute_name, $offset, $selector);

          $this.find('.next-page, .last-page').removeClass('disabled');
          $this.find('.current-page').text(1);
          $selector.data('current', 1);
          $this.find('.first-page, .prev-page').addClass('disabled');
        }).on('click', '.prev-page:not(.disabled)', function (event) {
          event.preventDefault();
          var $selector = $(event.currentTarget).closest('.product-term-label-settings').find('.product-term-label-settings-contents');
          var $this = $(event.currentTarget).closest('.product-term-label-settings-pagination');
          $selector.block({
            message: null,
            overlayCSS: {
              background: '#DDDDDD',
              opacity: 0.6
            }
          });
          var $product_id = $selector.data('product_id');
          var $pages = $selector.data('pages');
          var $attribute_id = $selector.data('attribute_id');
          var $attribute_name = $selector.data('attribute_name');
          var $current = $selector.data('current'); // 1

          var $limit = $selector.data('limit'); // 3

          var $total = $selector.data('total');
          var $offset = ($current - 1) * $limit - $limit;
          var $prev = $current - 1;

          _this.PaginationAjax($product_id, $attribute_id, $attribute_name, $offset, $selector);

          $this.find('.next-page, .last-page').removeClass('disabled');
          $this.find('.current-page').text($prev);
          $selector.data('current', $prev);

          if ($offset === 0) {
            $this.find('.first-page, .prev-page').addClass('disabled');
          }
        }).on('click', '.next-page:not(.disabled)', function (event) {
          event.preventDefault();
          var $selector = $(event.currentTarget).closest('.product-term-label-settings').find('.product-term-label-settings-contents');
          var $this = $(event.currentTarget).closest('.product-term-label-settings-pagination');
          $selector.block({
            message: null,
            overlayCSS: {
              background: '#DDDDDD',
              opacity: 0.6
            }
          });
          var $product_id = $selector.data('product_id');
          var $pages = $selector.data('pages');
          var $attribute_id = $selector.data('attribute_id');
          var $attribute_name = $selector.data('attribute_name');
          var $current = $selector.data('current'); // 1

          var $limit = $selector.data('limit'); // 3

          var $total = $selector.data('total');
          var $offset = $current * $limit;
          var $next = $current + 1;

          _this.PaginationAjax($product_id, $attribute_id, $attribute_name, $offset, $selector);

          $this.find('.first-page, .prev-page').removeClass('disabled');
          $this.find('.current-page').text($next);
          $selector.data('current', $next);

          if ($pages === $next) {
            $this.find('.next-page, .last-page').addClass('disabled');
          }
        }).on('click', '.last-page:not(.disabled)', function (event) {
          event.preventDefault();
          var $selector = $(event.currentTarget).closest('.product-term-label-settings').find('.product-term-label-settings-contents');
          var $this = $(event.currentTarget).closest('.product-term-label-settings-pagination');
          $selector.block({
            message: null,
            overlayCSS: {
              background: '#DDDDDD',
              opacity: 0.6
            }
          });
          var $product_id = $selector.data('product_id');
          var $pages = $selector.data('pages');
          var $attribute_id = $selector.data('attribute_id');
          var $attribute_name = $selector.data('attribute_name');
          var $current = $selector.data('current'); // 1

          var $limit = $selector.data('limit'); // 3

          var $offset = $pages * $limit - $limit;

          _this.PaginationAjax($product_id, $attribute_id, $attribute_name, $offset, $selector);

          $this.find('.first-page, .prev-page').removeClass('disabled');
          $this.find('.current-page').text($pages);
          $selector.data('current', $pages);
          $this.find('.next-page, .last-page').addClass('disabled');
        });
      }
    }, {
      key: "ResetAfterTermCreate",
      value: function ResetAfterTermCreate() {
        $(document).ajaxComplete(function (event, request, settings) {
          try {
            var data = Object.fromEntries(new URLSearchParams(settings.data));

            if ('add-tag' === data.action && '' === $('#tag-name').val()) {
              $(document.body).trigger('woo_variation_swatches_admin_term_meta_added', data);
            }
          } catch (err) {}
        });
      }
    }, {
      key: "ImageUploader",
      value: function ImageUploader() {
        $(document.body).off('click', 'button.wvs_upload_image_button');
        $(document.body).on('click', 'button.wvs_upload_image_button', this.AddImage);
        $(document.body).on('click', 'button.wvs_remove_image_button', this.RemoveImage);
        $(document.body).on('woo_variation_swatches_admin_term_meta_added', this.ClearImagePicker);
        $(document.body).on('woo_variation_swatches_admin_term_meta_added', this.ClearColorPicker); // $(document.body).on('woo_variation_swatches_admin_term_meta_added', this.ResetTagForm);
      }
    }, {
      key: "AddImage",
      value: function AddImage(event) {
        var _this2 = this;

        event.preventDefault();
        event.stopPropagation();
        var file_frame;

        if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
          // If the media frame already exists, reopen it.
          if (file_frame) {
            file_frame.open();
            return;
          } // Create the media frame.


          file_frame = wp.media.frames.select_image = wp.media({
            title: woo_variation_swatches_admin.media_title,
            button: {
              text: woo_variation_swatches_admin.button_title
            },
            multiple: false
          }); // When an image is selected, run a callback.

          file_frame.on('select', function () {
            var attachment = file_frame.state().get('selection').first().toJSON();

            if ($.trim(attachment.id) !== '') {
              var url = typeof attachment.sizes.thumbnail === 'undefined' ? attachment.sizes.full.url : attachment.sizes.thumbnail.url;
              $(_this2).prev().val(attachment.id);
              $(_this2).closest('.meta-image-field-wrapper').find('img').attr('src', url);
              $(_this2).next().show();
            } //file_frame.close();

          }); // When open select selected

          file_frame.on('open', function () {
            // Grab our attachment selection and construct a JSON representation of the model.
            var selection = file_frame.state().get('selection');
            var current = $(_this2).prev().val();
            var attachment = wp.media.attachment(current);
            attachment.fetch();
            selection.add(attachment ? [attachment] : []);
          }); // Finally, open the modal.

          file_frame.open();
        }
      }
    }, {
      key: "RemoveImage",
      value: function RemoveImage(event) {
        event.preventDefault();
        event.stopPropagation();
        var placeholder = $(this).closest('.meta-image-field-wrapper').find('img').data('placeholder');
        $(this).closest('.meta-image-field-wrapper').find('img').attr('src', placeholder);
        $(this).prev().prev().val('');
        $(this).hide();
        return false;
      }
    }, {
      key: "ClearImagePicker",
      value: function ClearImagePicker() {
        $('#addtag').find('.wvs_remove_image_button').trigger('click');
      }
    }, {
      key: "__SelectWoo",
      value: function __SelectWoo() {
        var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'select.wvs-select-woo';

        try {
          $(document.body).on('woo_variation_swatches_select_woo_init', function (event) {
            $(selector).selectWoo({
              allowClear: true
            });
          }).trigger('woo_variation_swatches_select_woo_init');
        } catch (err) {
          // If failed (conflict?) log the error but don't stop other scripts breaking.
          window.console.log(err);
        }
      }
    }, {
      key: "InitTooltip",
      value: function InitTooltip() {
        $(document.body).trigger('init_tooltips');
      }
    }, {
      key: "SelectWoo",
      value: function SelectWoo() {
        try {
          $(document.body).trigger('wc-enhanced-select-init');
        } catch (err) {
          // If failed (conflict?) log the error but don't stop other scripts breaking.
          window.console.log(err);
        }
      }
    }, {
      key: "ColorPicker",
      value: function ColorPicker() {
        var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'input.wvs-color-picker';

        try {
          $(document.body).on('woo_variation_swatches_color_picker_init', function (event) {
            $(selector).wpColorPicker({
              change: function change(event, ui) {
                $(selector).trigger('color-changed');
              },
              clear: function clear() {
                $(selector).trigger('color-changed');
              }
            });
          }).trigger('woo_variation_swatches_color_picker_init');
        } catch (err) {
          // If failed (conflict?) log the error but don't stop other scripts breaking.
          window.console.log(err);
        }
      }
    }, {
      key: "ClearColorPicker",
      value: function ClearColorPicker() {
        $('#addtag').find('.wp-picker-clear').trigger('click');
      }
    }, {
      key: "FieldDependency",
      value: function FieldDependency() {
        var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '[data-dependency]';

        try {
          $(document.body).on('init_form_field_dependency', function () {
            $(selector).GWPFormFieldDependency();
          }).trigger('init_form_field_dependency');
        } catch (err) {
          // If failed (conflict?) log the error but don't stop other scripts breaking.
          window.console.log(err);
        }
      }
    }, {
      key: "savingDialog",
      value: function savingDialog($wrapper, $dialog, taxonomy) {
        var data = {};
        var term = ''; // @TODO: We should use form data, because we have to pick array based data also :)

        $dialog.find("input, select").each(function () {
          var key = $(this).attr('name');
          var value = $(this).val();

          if (key) {
            if (key === 'tag_name') {
              term = value;
            } else {
              data[key] = value;
            }

            $(this).val('');
          }
        });

        if (term) {
          $('.product_attributes').block({
            message: null,
            overlayCSS: {
              background: '#FFFFFF',
              opacity: 0.6
            }
          });

          var ajax_data = _objectSpread({
            action: 'woocommerce_add_new_attribute',
            taxonomy: taxonomy,
            term: term,
            security: woocommerce_admin_meta_boxes.add_attribute_nonce
          }, data);

          $.post(woocommerce_admin_meta_boxes.ajax_url, ajax_data, function (response) {
            if (response.error) {
              // Error.
              window.alert(response.error);
            } else if (response.slug) {
              // Success.
              $wrapper.find('select.attribute_values').append('<option value="' + response.term_id + '" selected="selected">' + response.name + '</option>');
              $wrapper.find('select.attribute_values').change();
            }

            $('.product_attributes').unblock();
          });
        } else {
          $('.product_attributes').unblock();
        }
      }
    }, {
      key: "AttributeDialog",
      value: function AttributeDialog() {
        var self = this;
        $('.product_attributes').on('click', 'button.wvs_add_new_attribute', function (event) {
          event.preventDefault();
          var $wrapper = $(this).closest('.woocommerce_attribute');
          var attribute = $wrapper.data('taxonomy');
          var title = $(this).data('dialog_title');
          $('.wvs-attribute-dialog-for-' + attribute).dialog({
            title: '',
            dialogClass: 'wp-dialog wvs-attribute-dialog',
            classes: {
              "ui-dialog": "wp-dialog wvs-attribute-dialog"
            },
            autoOpen: false,
            draggable: true,
            width: 'auto',
            modal: true,
            resizable: false,
            closeOnEscape: true,
            position: {
              my: "center",
              at: "center",
              of: window
            },
            open: function open() {
              // close dialog by clicking the overlay behind it
              $('.ui-widget-overlay').bind('click', function () {
                $('#attribute-dialog').dialog('close');
              });
            },
            create: function create() {// style fix for WordPress admin
              // $('.ui-dialog-titlebar-close').addClass('ui-button');
            }
          }).dialog("option", "title", title).dialog("option", "buttons", [{
            text: woo_variation_swatches_admin.dialog_save,
            click: function click() {
              self.savingDialog($wrapper, $(this), attribute);
              $(this).dialog("close").dialog("destroy");
            }
          }, {
            text: woo_variation_swatches_admin.dialog_cancel,
            click: function click() {
              $(this).dialog("close").dialog("destroy");
            }
          }]).dialog('open');
        });
      }
    }]);

    return PluginHelper;
  }();

  return PluginHelper;
}(jQuery);



/***/ }),

/***/ "./src/js/backend.js":
/***/ (function(module, exports, __webpack_require__) {

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _getRequireWildcardCache(nodeInterop) { if (typeof WeakMap !== "function") return null; var cacheBabelInterop = new WeakMap(); var cacheNodeInterop = new WeakMap(); return (_getRequireWildcardCache = function _getRequireWildcardCache(nodeInterop) { return nodeInterop ? cacheNodeInterop : cacheBabelInterop; })(nodeInterop); }

function _interopRequireWildcard(obj, nodeInterop) { if (!nodeInterop && obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(nodeInterop); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (key !== "default" && Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

jQuery(function ($) {
  Promise.resolve().then(function () {
    return _interopRequireWildcard(__webpack_require__("./src/js/PluginHelper.js"));
  }).then(function (_ref) {
    var PluginHelper = _ref.PluginHelper;
    // PluginHelper.GWPAdmin();
    // TERM PAGE
    PluginHelper.ResetAfterTermCreate();
    PluginHelper.ColorPicker();
    PluginHelper.ImageUploader();
    PluginHelper.FieldDependency(); // PRODUCT PAGE

    PluginHelper.Pagination();
    PluginHelper.MetaboxToggle();
    PluginHelper.AttributeTypeSwitch();
    PluginHelper.SaveProductAttributes(); // PluginHelper.ResetProductAttributes();
    // PluginHelper.AttributeDialog();

    /*$(document.body).on('woo_variation_swatches_variation_product_options_reset', (event, $selector) => {
        PluginHelper.InitTooltip();
        PluginHelper.SelectWoo();
        PluginHelper.ColorPicker();
        PluginHelper.FieldDependency();
        PluginHelper.SetAttributeTypePaging($selector);
    })*/

    $(document.body).on('woo_variation_swatches_variation_product_options_reset woo_variation_swatches_product_term_paging_done', function (event, $selector) {
      PluginHelper.InitTooltip();
      PluginHelper.SelectWoo();
      PluginHelper.ColorPicker();
      PluginHelper.FieldDependency();
      PluginHelper.SetAttributeTypePaging($selector);
    });
    /*$('#woocommerce-product-data').on('woocommerce_variations_loaded', function () {
        PluginHelper.GalleryNotification();
    });
     $('#variable_product_options').on('woocommerce_variations_added', function () {
        PluginHelper.GalleryNotification();
    });
     $(document.body).on('woocommerce_added_attribute', function () {
        PluginHelper.SelectWoo();
        PluginHelper.ColorPicker();
        PluginHelper.ImageUploader();
        PluginHelper.AttributeDialog();
    });
     $(document.body).on('wvs_pro_product_swatches_variation_loaded', () => {
        PluginHelper.ColorPicker();
        PluginHelper.ImageUploader();
    });*/
  });
}); // end of jquery main wrapper

/***/ }),

/***/ "./src/scss/backend.scss":
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./src/scss/frontend.scss":
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("./src/js/backend.js");
__webpack_require__("./src/scss/backend.scss");
module.exports = __webpack_require__("./src/scss/frontend.scss");


/***/ })

/******/ });