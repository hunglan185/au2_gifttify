!function(e){var t={};function a(i){if(t[i])return t[i].exports;var n=t[i]={i:i,l:!1,exports:{}};return e[i].call(n.exports,n,n.exports,a),n.l=!0,n.exports}a.m=e,a.c=t,a.d=function(e,t,i){a.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:i})},a.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},a.t=function(e,t){if(1&t&&(e=a(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var i=Object.create(null);if(a.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)a.d(i,n,function(t){return e[t]}.bind(null,n));return i},a.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return a.d(t,"a",t),t},a.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},a.p="",a(a.s=4)}([function(e,t,a){a.p=window.flatsomeVars?window.flatsomeVars.assets_url:"/"},,,,function(e,t,a){a(0),e.exports=a(5)},function(e,t,a){"use strict";a.r(t),a(6),a(7)},function(e,t){jQuery(document).ready((function(e){e("[data-to-panel]").on("click",(function(t){wp.customize.panel(e(this).data("toPanel")).focus(),t.preventDefault()})),e("[data-to-section]").on("click",(function(t){wp.customize.section(e(this).data("toSection")).focus(),t.preventDefault()}))}))},function(e,t){jQuery((function(e){function t(t){if(t){var a=[];e('.hb-drop[data-id="'+t+'"]').find("span").each((function(){a.push(e(this).data("id"))})),wp.customize.instance(t).set(a)}}function a(t){if(t){wp.customize.control("","");var a=[];e('.hb-drop-mobile[data-id="'+t+'"]').find("span").each((function(){a.push(e(this).data("id"))})),wp.customize.instance(t).set(a)}}function i(){e(".hb-drop").each((function(){var t=e(this).data("id");t&&wp.customize.instance(t)&&wp.customize.instance(t).get().forEach((function(a){e('.hb-avaiable-desktop span[data-id="'+a+'"]').appendTo('.header-builder [data-id="'+t+'"]')}))})),e(".hb-drop-mobile").each((function(){var t=e(this).data("id");t&&wp.customize.instance(t)&&wp.customize.instance(t).get().forEach((function(a){e('.hb-avaiable-mobile span[data-id="'+a+'"]').appendTo('.header-builder [data-id="'+t+'"]')}))})),e(".hb-drop").sortable({connectWith:".hb-drop",update:function(e,a){t(jQuery(a.item).parent().data("id")),t(jQuery(a.sender).data("id"))}}).disableSelection(),e(".hb-drop-mobile").sortable({connectWith:".hb-drop-mobile",update:function(e,t){a(jQuery(t.item).parent().data("id")),a(jQuery(t.sender).data("id"))}}).disableSelection(),e(".header-builder .hb-desktop .hb-main").removeClass("hb-logo-center"),"center"==wp.customize.instance("logo_position").get()&&e(".header-builder .hb-desktop .hb-main").addClass("hb-logo-center"),e(".header-builder .hb-mobile .hb-main").removeClass("hb-logo-center"),"center"==wp.customize.instance("logo_position_mobile").get()&&e(".header-builder .hb-mobile .hb-main").addClass("hb-logo-center")}e(".wp-picker-clear").on("click",(function(){var e=jQuery(this).parent().find("[data-customize-setting-link]").data("customizeSettingLink");e&&wp.customize.instance(e).set("")})),e("#customize-control-mobile_sidebar input").focus((function(){e(".header-builder").addClass("header-builder-disabled"),jQuery('[data-open="#main-menu"]').trigger("click")})),e("#customize-control-mobile_sidebar input").blur((function(){e(".header-builder").removeClass("header-builder-disabled")})),e(".preset-click img").on("click",(function(t){var a=e(this).data("preset");e("#customize-control-preset_demo").find("select").val(a).change(),setTimeout((function(){e(".hb-drop").each((function(){e(this).find("span").appendTo(".hb-avaiable-desktop .hb-list")})),e(".hb-drop-mobile").each((function(){e(this).find("span").appendTo(".hb-avaiable-mobile .hb-list")})),i()}),300),t.preventDefault()})),e(".enable-desktop").on("click",(function(t){e(".devices .preview-desktop").trigger("click"),t.preventDefault()})),e(".enable-tablet").on("click",(function(t){e(".devices .preview-tablet").trigger("click"),t.preventDefault()})),e(".enable-mobile").on("click",(function(t){e(".devices .preview-mobile").trigger("click"),t.preventDefault()})),wp.customize.panel("header")&&wp.customize.panel("header").expanded.bind((function(t){1==t?e(".header-builder").addClass("active"):e(".header-builder").removeClass("active")})),e(".header-clear-button").on("click",(function(){e(".hb-drop").each((function(){e(this).find("span").appendTo(".hb-avaiable-desktop .hb-list"),t(e(this).data("id"))})),e(".hb-drop-mobile").each((function(){var t=e(this).data("id");e(this).find("span").appendTo(".hb-avaiable-mobile .hb-list"),a(t)}))})),e(".header-close-button").on("click",(function(){wp.customize.panel("header").expanded(!1)})),i(),e('.header-builder span[data-id="search"]').attr("data-section","header_search"),e('.header-builder span[data-id="search-form"]').attr("data-section","header_search"),e('.header-builder span[data-id="account"]').attr("data-section","header_account"),e('.header-builder span[data-id="cart"]').attr("data-section","header_cart"),e('.header-builder span[data-id*="html"]').attr("data-section","header_content"),e('.header-builder span[data-id="social"]').attr("data-section","follow"),e('.header-builder span[data-id="menu-icon"]').attr("data-section","header_mobile"),e('.header-builder span[data-id="contact"]').attr("data-section","header_contact"),e('.header-builder span[data-id="wishlist"]').attr("data-section","header_wishlist"),e('.header-builder span[data-id="button-1"]').attr("data-section","header_buttons"),e('.header-builder span[data-id="button-2"]').attr("data-section","header_buttons"),e('.header-builder span[data-id="block-1"]').attr("data-section","header_content"),e('.header-builder span[data-id="block-2"]').attr("data-section","header_content"),e('.header-builder span[data-id="newsletter"]').attr("data-section","header_newsletter"),e('.header-builder span[data-id="nav-vertical"]').attr("data-section","header_nav_vertical"),e(".header-builder [data-section]").on("click",(function(t){var a=e(this).data("section");wp.customize.section(a)&&wp.customize.section(a).focus(),t.preventDefault()})),e(".header-builder [data-section] [data-section]").on("click",(function(t){t.preventDefault();var a=e(this).data("section");wp.customize.section(a)&&wp.customize.section(a).focus()}))}))}]);