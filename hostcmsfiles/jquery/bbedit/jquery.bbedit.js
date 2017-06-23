/**	jQuery.browser
 *	@author	J.D. McKinstry (2014)
 *	@description	Made to replicate older jQuery.browser command in jQuery versions 1.9+
 *	@see http://jsfiddle.net/SpYk3/wsqfbe4s/
 *
 *	@extends	jQuery
 *	@namespace	jQuery.browser
 *	@example	jQuery.browser.browser == 'browserNameInLowerCase'
 *	@example	jQuery.browser.version
 *	@example	jQuery.browser.mobile	@returns	BOOLEAN
 *	@example	jQuery.browser['browserNameInLowerCase']
 *	@example	jQuery.browser.chrome	@returns	BOOLEAN
 *	@example	jQuery.browser.safari	@returns	BOOLEAN
 *	@example	jQuery.browser.opera	@returns	BOOLEAN
 *	@example	jQuery.browser.msie	@returns	BOOLEAN
 *	@example	jQuery.browser.mozilla	@returns	BOOLEAN
 *	@example	jQuery.browser.webkit	@returns	BOOLEAN
 *	@example	jQuery.browser.ua	@returns	navigator.userAgent String
 */
;;(function($){if(!$.browser&&1.9<=parseFloat($.fn.jquery)){var a={browser:void 0,version:void 0,mobile:!1};navigator&&navigator.userAgent&&(a.ua=navigator.userAgent,a.webkit=/WebKit/i.test(a.ua),a.browserArray="MSIE Chrome Opera Kindle Silk BlackBerry PlayBook Android Safari Mozilla Nokia".split(" "),/Sony[^ ]*/i.test(a.ua)?a.mobile="Sony":/RIM Tablet/i.test(a.ua)?a.mobile="RIM Tablet":/BlackBerry/i.test(a.ua)?a.mobile="BlackBerry":/iPhone/i.test(a.ua)?a.mobile="iPhone":/iPad/i.test(a.ua)?a.mobile="iPad":/iPod/i.test(a.ua)?a.mobile="iPod":/Opera Mini/i.test(a.ua)?a.mobile="Opera Mini":/IEMobile/i.test(a.ua)?a.mobile="IEMobile":/BB[0-9]{1,}; Touch/i.test(a.ua)?a.mobile="BlackBerry":/Nokia/i.test(a.ua)?a.mobile="Nokia":/Android/i.test(a.ua)&&(a.mobile="Android"),/MSIE|Trident/i.test(a.ua)?(a.browser="MSIE",a.version=/MSIE/i.test(navigator.userAgent)&&0<parseFloat(a.ua.split("MSIE")[1].replace(/[^0-9\.]/g,""))?parseFloat(a.ua.split("MSIE")[1].replace(/[^0-9\.]/g,"")):"Edge",/Trident/i.test(a.ua)&&/rv:([0-9]{1,}[\.0-9]{0,})/.test(a.ua)&&(a.version=parseFloat(a.ua.match(/rv:([0-9]{1,}[\.0-9]{0,})/)[1].replace(/[^0-9\.]/g,"")))):/Chrome/.test(a.ua)?(a.browser="Chrome",a.version=parseFloat(a.ua.split("Chrome/")[1].split("Safari")[0].replace(/[^0-9\.]/g,""))):/Opera/.test(a.ua)?(a.browser="Opera",a.version=parseFloat(a.ua.split("Version/")[1].replace(/[^0-9\.]/g,""))):/Kindle|Silk|KFTT|KFOT|KFJWA|KFJWI|KFSOWI|KFTHWA|KFTHWI|KFAPWA|KFAPWI/i.test(a.ua)?(a.mobile="Kindle",/Silk/i.test(a.ua)?(a.browser="Silk",a.version=parseFloat(a.ua.split("Silk/")[1].split("Safari")[0].replace(/[^0-9\.]/g,""))):/Kindle/i.test(a.ua)&&/Version/i.test(a.ua)&&(a.browser="Kindle",a.version=parseFloat(a.ua.split("Version/")[1].split("Safari")[0].replace(/[^0-9\.]/g,"")))):/BlackBerry/.test(a.ua)?(a.browser="BlackBerry",a.version=parseFloat(a.ua.split("/")[1].replace(/[^0-9\.]/g,""))):/PlayBook/.test(a.ua)?(a.browser="PlayBook",a.version=parseFloat(a.ua.split("Version/")[1].split("Safari")[0].replace(/[^0-9\.]/g,""))):/BB[0-9]{1,}; Touch/.test(a.ua)?(a.browser="Blackberry",a.version=parseFloat(a.ua.split("Version/")[1].split("Safari")[0].replace(/[^0-9\.]/g,""))):/Android/.test(a.ua)?(a.browser="Android",a.version=parseFloat(a.ua.split("Version/")[1].split("Safari")[0].replace(/[^0-9\.]/g,""))):/Safari/.test(a.ua)?(a.browser="Safari",a.version=parseFloat(a.ua.split("Version/")[1].split("Safari")[0].replace(/[^0-9\.]/g,""))):/Firefox/.test(a.ua)?(a.browser="Mozilla",a.version=parseFloat(a.ua.split("Firefox/")[1].replace(/[^0-9\.]/g,""))):/Nokia/.test(a.ua)&&(a.browser="Nokia",a.version=parseFloat(a.ua.split("Browser")[1].replace(/[^0-9\.]/g,""))));if(a.browser)for(var b in a.browserArray)a[a.browserArray[b].toLowerCase()]=a.browser==a.browserArray[b];$.extend(!0,$.browser={},a)}})(jQuery);
/* - - - - - - - - - - - - - - - - - - - */


/*
 * jQuery bbcode editor plugin
 *
 * Copyright (C) 2010 Joe Dotoff
 * http://www.w3theme.com/jquery-bbedit/
 *
 * Version 1.1
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */

(function($) {
  $.bbedit = {
    baseURL: '/hostcmsfiles/jquery/bbedit/',
    i18n: {'default': {
      'b' : 'Bold',
      'i' : 'Italic',
      'u' : 'Underline',
      's' : 'Strike through',
      'url' : 'Insert link',
      'img' : 'Insert image',
      'code' : 'Insert code',
      'quote' : 'Insert quote',
	  // add
      'font' : 'Change font',
      'olist' : 'Ordered list',
      'ulist' : 'Unordered list',
      'sup' : 'Sup',
      'sub' : 'Sub',
      'tongue' : 'Tongue out'
    }}
  };

  $.fn.extend({
    bbedit: function(settings) {
      this.defaults = {
        highlight: false,
        enableToolbar: true,
        enableSmileybar: true,
        lang: 'default',
        tags: 'b,i,u,s,url,code,img,quote,font,olist,ulist,sup,sub',
        smilies: {
			':)' : 'smile',
			';)' : 'wink',
			':D' : 'biggrin',
			':|' : 'neutral',
			':idea:' : 'idea',
			':razz:' : 'razz',
			':frown:' : 'frown',
			':surprised:' : 'surprised',
			':confused:' : 'confused',
			':(' : 'cry',
			':rolleyes:' : 'rolleyes',
			':cool:' : 'cool',
			':eek:' : 'eek',
			':question:' : 'question',
			':mad:' : 'mad',
			':mrgreen:' : 'mrgreen',
			':redface:' : 'redface',
			':twisted:' : 'twisted',
			':lol:' : 'lol',
			':sad:' : 'sad'
		}
      }
      var settings = $.extend(this.defaults, settings);
      var tags = settings.tags.split(/,\s*/);
      if ($.bbedit.baseURL == null) {
        var scripts1 = $("script");
        for (var i = 0; i < scripts1.length; i++) {
          if (scripts1.eq(i).attr("src").indexOf('jquery.bbedit') > -1) {
            $.bbedit.baseURL = scripts1.eq(i).attr("src").replace(/[^\/\\]+$/, '');
            break;
          }
        }
      }
      if (typeof $.bbedit.i18n[settings.lang] == 'undefined') {
        $.ajax({
          url: $.bbedit.baseURL + 'lang/' + settings.lang + '.js',
          async: false,
          dataType: "script",
          error: function() {
            settings.lang = 'default';
          }
        });
      }
      var toolHtml = '<div class="bbedit-toolbar">';
      for (var i in tags) {
        toolHtml += '<span class="bbedit-' + tags[i] + '" title="' + $.bbedit.i18n[settings.lang][tags[i]] + '">&nbsp;</span> ';
      }
      toolHtml += '</div>';
      
	  //var smilies = settings.smilies.split(/,\s*/);
      var smilies = settings.smilies;
	  
	  var smileyHtml = '<div class="bbedit-smileybar">';
      for (var i in smilies) {
        if (smilies[i] != '|') {
          smileyHtml += '<img src="/hostcmsfiles/forum/smiles/' + smilies[i] + '.gif" class="bbedit-' + smilies[i] + '" alt="' + i + '" /> '
        } else {
          smileyHtml += '<br />';
        }
      }
      smileyHtml += '</div>';

      return this.each(function() {
        var data = settings;
        data.range = null;
        data.ta = this;
        $(this).bind("select click keyup", function() {
          if (document.selection) {
            data.range = document.selection.createRange();
          }
        });
        if (settings.enableToolbar) {
          var toolbar = $(toolHtml);
          $(this).before(toolbar);
          if ($.browser.msie && parseInt($.browser.version) <= 6) {
            toolbar.children("span").mouseover(function() {
              $(this).addClass("hover");
            }).mouseout(function() {
              $(this).removeClass("hover");
            });
          }
          toolbar.find(".bbedit-b").click(function() {
            insertTag(data, '[b]', '[/b]');
          });
          toolbar.find(".bbedit-i").click(function() {
            insertTag(data, '[i]', '[/i]');
          });
          toolbar.find(".bbedit-u").click(function() {
            insertTag(data, '[u]', '[/u]');
          });
          toolbar.find(".bbedit-s").click(function() {
            insertTag(data, '[s]', '[/s]');
          });
          toolbar.find(".bbedit-code").click(function() {
            insertTag(data, '[code]', '[/code]');
          });
          toolbar.find(".bbedit-quote").click(function() {
            insertTag(data, '[quote]', '[/quote]');
          });
          toolbar.find(".bbedit-url").click(function() {
            insertTag(data, function(text) {
              if (/^https?:\/\//i.test(text)) {
                return '[url]' + text + '[/url]';
              } else {
                var url = prompt('Ссылка: ', '');
                if (url != null && url != '') {
                  if (!/^https?:\/\//i.test(url)) {
                    url = 'http://' + url;
                  }
                  if (text == '') {
                    return '[url]' + url + '[/url]';
                  } else {
                    return '[url=' + url + ']' + text + '[/url]';
                  }
                }
                return false;
              }
            });
          });
          toolbar.find(".bbedit-img").click(function() {
            insertTag(data, function(text) {
              if (/^https?:\/\//i.test(text)) {
                return '[img]' + text + '[/img]';
              } else {
                var url = prompt('Путь к изображению: ', '');
                if (url != null && url != '') {
                  if (!/^https?:\/\//i.test(url)) {
                    url = 'http://' + url;
                  }
                  return '[img]' + url + '[/img]';
                }
                return false;
              }
            });
          });

		  // Add
		  toolbar.find(".bbedit-font").click(function() {
            insertTag(data, '[font=Curier]', '[/font]');
          });
		  toolbar.find(".bbedit-olist").click(function() {
            insertTag(data, '[list=1 start=1]\r\n[*]', '\r\n[/list]');
          });
		  toolbar.find(".bbedit-ulist").click(function() {
            insertTag(data, '[ulist]\r\n[*]', '\r\n[/ulist]');
          });
		  toolbar.find(".bbedit-sup").click(function() {
            insertTag(data, '[sup]', '[/sup]');
          });
		  toolbar.find(".bbedit-sub").click(function() {
            insertTag(data, '[sub]', '[/sub]');
          });
		  
		  // Вставка ника
		  $(".table_messages span.author_name").click(function() {
            insertTag(data, '[b]'+$(this).text(), '[/b],\r\n');
          });
		  
        }
        if (settings.enableSmileybar) {
          var smileybar = $(smileyHtml);
          $(this).after(smileybar);
          for (var i in smilies) {
            smileybar.find(".bbedit-" + smilies[i]).click(function() {
              insertTag(data, /*'[:Q' + $(this).attr("class").replace(/bbedit-/, '') + ']'*/  $(this).attr("alt"));
            });
          }
        }
      });
    }
  });

  function insertTag(data, tag, tag2) {
    var val, startPos, endPos;
    var ta = data.ta;
    var range = data.range;
    var text = '';
    if (range != null) {
      text = range.text;
    } else if (typeof ta.selectionStart != 'undefined') {
      startPos = ta.selectionStart;
      endPos = ta.selectionEnd;
      text = ta.value.substring(startPos, endPos);
    }
    if (typeof tag == 'function' || typeof tag == 'object') {
      val = tag(text);
      if (val === false) {
        if (range != null) {
          range.moveStart('character', text.length);
          range.select();
        } else if (typeof ta.selectionStart != 'undefined') {
          ta.selectionStart = startPos + text.length;
        }
        ta.focus();
        return;
      }
    } else {
      if (!tag2 || tag2 == '') {
        val = text + tag;
      } else {
        val = tag + text + tag2;
      }
    }
    if (range != null) {
      range.text = val;
      if (data.highlight) {
        range.moveStart('character', -val.length);
        //range.moveEnd('character', 0);
      } else {
        range.moveStart('character', 0);
        //range.moveEnd('character', 0);
      }
      range.select();
    } else if (typeof ta.selectionStart != 'undefined') {
      ta.value = ta.value.substring(0, startPos) + val + ta.value.substr(endPos);
      if (data.highlight) {
        ta.selectionStart = startPos;
        ta.selectionEnd = startPos + val.length;
      } else {
        ta.selectionStart = startPos + val.length;
        ta.selectionEnd = startPos + val.length;
      }
    } else {
      ta.value += val;
    }
    ta.focus();
  }

})(jQuery);