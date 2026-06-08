var template = "product";
var str = __st.pageurl;
var rest = str.substring(0, str.lastIndexOf("/") + 1);
var template_cart = str.substring(str.lastIndexOf("/") + 1, str.length);
var shop_name = Shopify.shop;
var valid = true;
var current_product;
var enable_timer_data = 'false';

if (template == "product"){


(function defineMustache(global,factory){if(typeof exports==="object"&&exports&&typeof exports.nodeName!=="string"){factory(exports)}else if(typeof define==="function"&&define.amd){define(["exports"],factory)}else{global.Mustache={};factory(global.Mustache)}})(this,function mustacheFactory(mustache){var objectToString=Object.prototype.toString;var isArray=Array.isArray||function isArrayPolyfill(object){return objectToString.call(object)==="[object Array]"};function isFunction(object){return typeof object==="function"}function typeStr(obj){return isArray(obj)?"array":typeof obj}function escapeRegExp(string){return string.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g,"\\$&")}function hasProperty(obj,propName){return obj!=null&&typeof obj==="object"&&propName in obj}var regExpTest=RegExp.prototype.test;function testRegExp(re,string){return regExpTest.call(re,string)}var nonSpaceRe=/\S/;function isWhitespace(string){return!testRegExp(nonSpaceRe,string)}var entityMap={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;","/":"&#x2F;","`":"&#x60;","=":"&#x3D;"};function escapeHtml(string){return String(string).replace(/[&<>"'`=\/]/g,function fromEntityMap(s){return entityMap[s]})}var whiteRe=/\s*/;var spaceRe=/\s+/;var equalsRe=/\s*=/;var curlyRe=/\s*\}/;var tagRe=/#|\^|\/|>|\{|&|=|!/;function parseTemplate(template,tags){if(!template)return[];var sections=[];var tokens=[];var spaces=[];var hasTag=false;var nonSpace=false;function stripSpace(){if(hasTag&&!nonSpace){while(spaces.length)delete tokens[spaces.pop()]}else{spaces=[]}hasTag=false;nonSpace=false}var openingTagRe,closingTagRe,closingCurlyRe;function compileTags(tagsToCompile){if(typeof tagsToCompile==="string")tagsToCompile=tagsToCompile.split(spaceRe,2);if(!isArray(tagsToCompile)||tagsToCompile.length!==2)throw new Error("Invalid tags: "+tagsToCompile);openingTagRe=new RegExp(escapeRegExp(tagsToCompile[0])+"\\s*");closingTagRe=new RegExp("\\s*"+escapeRegExp(tagsToCompile[1]));closingCurlyRe=new RegExp("\\s*"+escapeRegExp("}"+tagsToCompile[1]))}compileTags(tags||mustache.tags);var scanner=new Scanner(template);var start,type,value,chr,token,openSection;while(!scanner.eos()){start=scanner.pos;value=scanner.scanUntil(openingTagRe);if(value){for(var i=0,valueLength=value.length;i<valueLength;++i){chr=value.charAt(i);if(isWhitespace(chr)){spaces.push(tokens.length)}else{nonSpace=true}tokens.push(["text",chr,start,start+1]);start+=1;if(chr==="\n")stripSpace()}}if(!scanner.scan(openingTagRe))break;hasTag=true;type=scanner.scan(tagRe)||"name";scanner.scan(whiteRe);if(type==="="){value=scanner.scanUntil(equalsRe);scanner.scan(equalsRe);scanner.scanUntil(closingTagRe)}else if(type==="{"){value=scanner.scanUntil(closingCurlyRe);scanner.scan(curlyRe);scanner.scanUntil(closingTagRe);type="&"}else{value=scanner.scanUntil(closingTagRe)}if(!scanner.scan(closingTagRe))throw new Error("Unclosed tag at "+scanner.pos);token=[type,value,start,scanner.pos];tokens.push(token);if(type==="#"||type==="^"){sections.push(token)}else if(type==="/"){openSection=sections.pop();if(!openSection)throw new Error('Unopened section "'+value+'" at '+start);if(openSection[1]!==value)throw new Error('Unclosed section "'+openSection[1]+'" at '+start)}else if(type==="name"||type==="{"||type==="&"){nonSpace=true}else if(type==="="){compileTags(value)}}openSection=sections.pop();if(openSection)throw new Error('Unclosed section "'+openSection[1]+'" at '+scanner.pos);return nestTokens(squashTokens(tokens))}function squashTokens(tokens){var squashedTokens=[];var token,lastToken;for(var i=0,numTokens=tokens.length;i<numTokens;++i){token=tokens[i];if(token){if(token[0]==="text"&&lastToken&&lastToken[0]==="text"){lastToken[1]+=token[1];lastToken[3]=token[3]}else{squashedTokens.push(token);lastToken=token}}}return squashedTokens}function nestTokens(tokens){var nestedTokens=[];var collector=nestedTokens;var sections=[];var token,section;for(var i=0,numTokens=tokens.length;i<numTokens;++i){token=tokens[i];switch(token[0]){case"#":case"^":collector.push(token);sections.push(token);collector=token[4]=[];break;case"/":section=sections.pop();section[5]=token[2];collector=sections.length>0?sections[sections.length-1][4]:nestedTokens;break;default:collector.push(token)}}return nestedTokens}function Scanner(string){this.string=string;this.tail=string;this.pos=0}Scanner.prototype.eos=function eos(){return this.tail===""};Scanner.prototype.scan=function scan(re){var match=this.tail.match(re);if(!match||match.index!==0)return"";var string=match[0];this.tail=this.tail.substring(string.length);this.pos+=string.length;return string};Scanner.prototype.scanUntil=function scanUntil(re){var index=this.tail.search(re),match;switch(index){case-1:match=this.tail;this.tail="";break;case 0:match="";break;default:match=this.tail.substring(0,index);this.tail=this.tail.substring(index)}this.pos+=match.length;return match};function Context(view,parentContext){this.view=view;this.cache={".":this.view};this.parent=parentContext}Context.prototype.push=function push(view){return new Context(view,this)};Context.prototype.lookup=function lookup(name){var cache=this.cache;var value;if(cache.hasOwnProperty(name)){value=cache[name]}else{var context=this,names,index,lookupHit=false;while(context){if(name.indexOf(".")>0){value=context.view;names=name.split(".");index=0;while(value!=null&&index<names.length){if(index===names.length-1)lookupHit=hasProperty(value,names[index]);value=value[names[index++]]}}else{value=context.view[name];lookupHit=hasProperty(context.view,name)}if(lookupHit)break;context=context.parent}cache[name]=value}if(isFunction(value))value=value.call(this.view);return value};function Writer(){this.cache={}}Writer.prototype.clearCache=function clearCache(){this.cache={}};Writer.prototype.parse=function parse(template,tags){var cache=this.cache;var tokens=cache[template];if(tokens==null)tokens=cache[template]=parseTemplate(template,tags);return tokens};Writer.prototype.render=function render(template,view,partials){var tokens=this.parse(template);var context=view instanceof Context?view:new Context(view);return this.renderTokens(tokens,context,partials,template)};Writer.prototype.renderTokens=function renderTokens(tokens,context,partials,originalTemplate){var buffer="";var token,symbol,value;for(var i=0,numTokens=tokens.length;i<numTokens;++i){value=undefined;token=tokens[i];symbol=token[0];if(symbol==="#")value=this.renderSection(token,context,partials,originalTemplate);else if(symbol==="^")value=this.renderInverted(token,context,partials,originalTemplate);else if(symbol===">")value=this.renderPartial(token,context,partials,originalTemplate);else if(symbol==="&")value=this.unescapedValue(token,context);else if(symbol==="name")value=this.escapedValue(token,context);else if(symbol==="text")value=this.rawValue(token);if(value!==undefined)buffer+=value}return buffer};Writer.prototype.renderSection=function renderSection(token,context,partials,originalTemplate){var self=this;var buffer="";var value=context.lookup(token[1]);function subRender(template){return self.render(template,context,partials)}if(!value)return;if(isArray(value)){for(var j=0,valueLength=value.length;j<valueLength;++j){buffer+=this.renderTokens(token[4],context.push(value[j]),partials,originalTemplate)}}else if(typeof value==="object"||typeof value==="string"||typeof value==="number"){buffer+=this.renderTokens(token[4],context.push(value),partials,originalTemplate)}else if(isFunction(value)){if(typeof originalTemplate!=="string")throw new Error("Cannot use higher-order sections without the original template");value=value.call(context.view,originalTemplate.slice(token[3],token[5]),subRender);if(value!=null)buffer+=value}else{buffer+=this.renderTokens(token[4],context,partials,originalTemplate)}return buffer};Writer.prototype.renderInverted=function renderInverted(token,context,partials,originalTemplate){var value=context.lookup(token[1]);if(!value||isArray(value)&&value.length===0)return this.renderTokens(token[4],context,partials,originalTemplate)};Writer.prototype.renderPartial=function renderPartial(token,context,partials){if(!partials)return;var value=isFunction(partials)?partials(token[1]):partials[token[1]];if(value!=null)return this.renderTokens(this.parse(value),context,partials,value)};Writer.prototype.unescapedValue=function unescapedValue(token,context){var value=context.lookup(token[1]);if(value!=null)return value};Writer.prototype.escapedValue=function escapedValue(token,context){var value=context.lookup(token[1]);if(value!=null)return mustache.escape(value)};Writer.prototype.rawValue=function rawValue(token){return token[1]};mustache.name="mustache.js";mustache.version="2.3.0";mustache.tags=["{{","}}"];var defaultWriter=new Writer;mustache.clearCache=function clearCache(){return defaultWriter.clearCache()};mustache.parse=function parse(template,tags){return defaultWriter.parse(template,tags)};mustache.render=function render(template,view,partials){if(typeof template!=="string"){throw new TypeError('Invalid template! Template should be a "string" '+'but "'+typeStr(template)+'" was given as the first '+"argument for mustache#render(template, view, partials)")}return defaultWriter.render(template,view,partials)};mustache.to_html=function to_html(template,view,partials,send){var result=mustache.render(template,view,partials);if(isFunction(send)){send(result)}else{return result}};mustache.escape=escapeHtml;mustache.Scanner=Scanner;mustache.Context=Context;mustache.Writer=Writer;return mustache});

jQueryCode = function(){

//////////////////////////// 
  /*!
 * Countdown v0.1.0
 * https://github.com/fengyuanchen/countdown
 *
 * Copyright 2014 Fengyuan Chen
 * Released under the MIT license
 */

!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):a(jQuery)}(function(a){"use strict";var b=function(c,d){this.$element=a(c),this.defaults=a.extend({},b.defaults,this.$element.data(),a.isPlainObject(d)?d:{}),this.init()};b.prototype={constructor:b,init:function(){var a=this.$element.html(),b=new Date(this.defaults.date||a);b.getTime()&&(this.content=a,this.date=b,this.find(),this.defaults.autoStart&&this.start())},find:function(){var a=this.$element;this.$days=a.find("[data-days]"),this.$hours=a.find("[data-hours]"),this.$minutes=a.find("[data-minutes]"),this.$seconds=a.find("[data-seconds]"),this.$days.length+this.$hours.length+this.$minutes.length+this.$seconds.length>0&&(this.found=!0)},reset:function(){this.found?(this.output("days"),this.output("hours"),this.output("minutes"),this.output("seconds")):this.output()},ready:function(){var a,b=this.date,c=100,d=1e3,e=6e4,f=36e5,g=864e5,h={};return b?(a=b.getTime()-(new Date).getTime(),0>=a?(this.end(),!1):(h.days=a,h.hours=h.days%g,h.minutes=h.hours%f,h.seconds=h.minutes%e,h.milliseconds=h.seconds%d,this.days=Math.floor(h.days/g),this.hours=Math.floor(h.hours/f),this.minutes=Math.floor(h.minutes/e),this.seconds=Math.floor(h.seconds/d),this.deciseconds=Math.floor(h.milliseconds/c),!0)):!1},start:function(){!this.active&&this.ready()&&(this.active=!0,this.reset(),this.autoUpdate=this.defaults.fast?setInterval(a.proxy(this.fastUpdate,this),100):setInterval(a.proxy(this.update,this),1e3))},stop:function(){this.active&&(this.active=!1,clearInterval(this.autoUpdate))},end:function(){this.date&&(this.stop(),this.days=0,this.hours=0,this.minutes=0,this.seconds=0,this.deciseconds=0,this.reset(),this.defaults.end())},destroy:function(){this.date&&(this.stop(),this.$days=null,this.$hours=null,this.$minutes=null,this.$seconds=null,this.$element.empty().html(this.content),this.$element.removeData("countdown"))},fastUpdate:function(){--this.deciseconds>=0?this.output("deciseconds"):(this.deciseconds=9,this.update())},update:function(){--this.seconds>=0?this.output("seconds"):(this.seconds=59,--this.minutes>=0?this.output("minutes"):(this.minutes=59,--this.hours>=0?this.output("hours"):(this.hours=23,--this.days>=0?this.output("days"):this.end())))},output:function(a){if(!this.found)return void this.$element.empty().html(this.template());switch(a){case"deciseconds":this.$seconds.text(this.getSecondsText());break;case"seconds":this.$seconds.text(this.seconds);break;case"minutes":this.$minutes.text(this.minutes);break;case"hours":this.$hours.text(this.hours);break;case"days":this.$days.text(this.days)}},template:function(){return this.defaults.text.replace("%s",this.days).replace("%s",this.hours).replace("%s",this.minutes).replace("%s",this.getSecondsText())},getSecondsText:function(){return this.active&&this.defaults.fast?this.seconds+"."+this.deciseconds:this.seconds}},b.defaults={autoStart:!0,date:null,fast:!1,end:a.noop,text:"%s days, %s hours, %s minutes, %s seconds"},b.setDefaults=function(c){a.extend(b.defaults,c)},a.fn.countdown=function(c){return this.each(function(){var d=a(this),e=d.data("countdown");e||d.data("countdown",e=new b(this,c)),"string"==typeof c&&a.isFunction(e[c])&&e[c]()})},a.fn.countdown.constructor=b,a.fn.countdown.setDefaults=b.setDefaults,a(function(){a("[countdown]").countdown()})});

  $('head').append('<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet">');
  $("form[action='/cart/add']:last").append('<span id="est_loader"><i class="fa fa-spinner fa-4x fa-spin"></i></span>');
  jQuery(document).ready(function ($) {
                

    var template = __st.p
    var shop_name = Shopify.shop;

    // --------------------------------
    function check_variant(handle, variant_id, type){
      url = $(location).attr('href');
      // var check_for_cart = ('https://'+Shopify.shop+'/cart')==url;

      if (template=='product') {
        $.ajax({
          dataType: 'json',
          async: false, 
          url: '/products/' + handle + '.js',
          success: function(product) {
            current_product = product;
            if (product["available"]!=false) {
              var var_count = 0;
              var selected_variant;

              function get_and_set_variant(var_id){
                if (var_id==undefined) {
                  var_id = product["variants"][var_count].id
                  selected_variant = $.grep(product['variants'], function (element, index) {return element.id == var_id; })[0];
                }else{
                  selected_variant = $.grep(product['variants'], function (element, index) {return element.id == var_id; })[0];
                }

                var_count=var_count+1;
                if ((selected_variant.available == false) && (type == 'onload')) {
                  if (url.includes("variant=")) {
                    return selected_variant;
                  }
                  selected_variant = get_and_set_variant(variant_id);
                  return selected_variant;
                }else{
                  return selected_variant;
                }
              }

              selected_variant = get_and_set_variant(variant_id);

              if (selected_variant.available == false) {
                // $('#visual_estimator_wrapper').css('display','none');
                $(document).find("div[id='delivery_estimator_wrapper'], div[id='visual_estimator_wrapper']").each(function (i, el) {
                  // $(this).css('display','none');
                  $(this).fadeOut();
                });
                  valid = false;
              }else if(selected_variant.available == true){
                $(document).find("div[id='delivery_estimator_wrapper'], div[id='visual_estimator_wrapper']").each(function (i, el) {
                  // $(this).css('display','block');
                  $(this).fadeIn();
                });
                valid = true;
              }
            }
          },
          error: function() {

          }
        })
      }
    }

    if (template=='product') {
      var url = $(location).attr('href');
      var parts = url.split("/");
      var handle = parts[parts.length - 1].split("?")[0];
      var variant_id, parts1;

      if (url.includes("variant=")) {
        parts1 = url.split("variant=");
        variant_id = parts1[parts1.length - 1];
      }

      check_variant(handle, variant_id, 'onload');

      function variant_change() {
        url = $(location).attr('href');
        if(url.includes("variant=")){
          parts1 = url.split("variant=");
          variant_id = parts1[parts1.length - 1];

          check_variant(handle, variant_id, 'onchange');
        }else{
          console.log('variant not present onchange');
          if($('#delivery_estimator_wrapper').length){
            if($('#delivery_estimator_wrapper').is(":hidden")){
              $('#delivery_estimator_wrapper').fadeIn();
            }
          }
          if($('#visual_estimator_wrapper').length){
            if($('#visual_estimator_wrapper').is(":hidden")){
              $('#visual_estimator_wrapper').fadeIn();
            }
          }
        }
      }

      $("form[action='/cart/add'] select, form[action='/cart/add'] input[type='radio'], variant-radios.no-js-hidden input[type='radio'],form[action='/cart/add'] button.ProductForm__Item span.ProductForm__SelectedValue").change(function () {
        if (__st.p == 'product') {
          setTimeout(variant_change, 100);
        }
      });

      $("div.Popover__ValueList button.Popover__Value").click(function () {
        setTimeout(variant_change, 100);
      });
    }

    // ------------------------------

    if (template != "product") {
      var str = __st.pageurl
      var rest = str.substring(0, str.lastIndexOf("/") + 1);
      var template = str.substring(str.lastIndexOf("/") + 1, str.length);
    }

      var base_url = 'https://deliveryapp.ecodesoft.net';   

      if ((template == 'product') || (template != 'product')) {
        var product_id = __st.rid

        // $('head').append('<link type="text/css" href="'+base_url+'/deliverytimer/deliverytimer.css?'+Math.random()+'" rel="stylesheet">');
        var url = base_url + '/get_settings?shop=' + shop_name + '&product_id=' + product_id;
        $.ajax({
          crossOrigin: true,
          url: url,
          success: function (data) {
            $('#est_loader').html('');
            var status = data["status"];

            var theme_name = Shopify.theme.name

            if (status == true) {

              ///////////////////////////////////////////
              if (data["settings"]["enable_tbtimer"] == true) {
                function findReplaceString(string, find1, replace1) {
                  if ((/[a-zA-Z\_]+/g).test(string)) {
                    var replace1 = '<span class="countdown">' + replace1 + '</span>'
                    s1 = string.replace("[countdown]", replace1);
                    return s1;
                  } else {
                    return false
                  }
                }

                function findReplaceStringDelivery(string, find1, replace1) {
                  if ((/[a-zA-Z\_]+/g).test(string)) {
                    s1 = string.replace("[deliverydate]", replace1);
                    return s1;
                  } else {
                    return false
                  }
                }

                var timezone = data["settings"]["timezone"];
                // var cutofftime = data["settings"]["cutoff_hour"] + data["settings"]["cutoff_minutes"];
                var cutofftime = data["settings"]["cutofftime"];
                var finalhtml = '🚚 '+data["settings"]["custom_message"];
                var finalhtmlpostion = data["settings"]["text_position"];


                finalhtml = findReplaceString(finalhtml, 'countdown', cutofftime)

                var deliverydate = '<span class="delivery_date" style="color: ' + data["settings"]["text_deliverydate_color"] + '">' + data["settings"]["text_delivery_date"] + '</span>'

                if (deliverydate != null) {
                  finalhtml = findReplaceStringDelivery(finalhtml, 'deliverydate', deliverydate)
                }

                var styletoapply = ''
                if (data["settings"]["text_font_size"] != '' && data["settings"]["text_font_size"] != null) {
                  styletoapply += 'font-size:' + data["settings"]["text_font_size"] + 'px;'
                } else {
                  styletoapply += 'font-size:1.5rem !important;'
                }
                if (data["settings"]["text_font_color"] != '' && data["settings"]["text_font_color"] != null) {
                  styletoapply += 'color:' + data["settings"]["text_font_color"] + ';'
                }
                if (data["settings"]["text_background_color"] != '' && data["settings"]["text_background_color"] != null) {
                  styletoapply += 'background:' + data["settings"]["text_background_color"] + ';'
                }
                if (data["settings"]["text_border_size"] != '' && data["settings"]["text_border_size"] != null) {
                  styletoapply += 'border-width:' + data["settings"]["text_border_size"] + 'px;'
                }
                if (data["settings"]["text_border_style"] != '' && data["settings"]["text_border_style"] != null) {
                  styletoapply += 'border-style:'+data["settings"]["text_border_style"]+';'
                }
                if (data["settings"]["text_border_radius"] != '' && data["settings"]["text_border_radius"] != null) {
                  styletoapply += 'border-radius:'+data["settings"]["text_border_radius"]+'px;'
                }
                if (data["settings"]["text_border_color"] != '' && data["settings"]["text_border_color"] != null) {
                  styletoapply += 'border-color:' + data["settings"]["text_border_color"] + ';'
                }
                if (data["settings"]["text_margin_top"] != '' && data["settings"]["text_margin_top"] != null) {
                  styletoapply += 'margin-top:' + data["settings"]["text_margin_top"] + 'px;'
                }
                if (data["settings"]["text_margin_bottom"] != '' && data["settings"]["text_margin_bottom"] != null) {
                  styletoapply += 'margin-bottom:' + data["settings"]["text_margin_bottom"] + 'px;'
                }
                if (data["settings"]["text_margin_left"] != '' && data["settings"]["text_margin_left"] != null) {
                  styletoapply += 'margin-left:' + data["settings"]["text_margin_left"] + 'px;'
                }
                if (data["settings"]["text_margin_right"] != '' && data["settings"]["text_margin_right"] != null) {
                  styletoapply += 'margin-right:' + data["settings"]["text_margin_right"] + 'px;'
                }
                if (data["settings"]["text_align"] != '' && data["settings"]["text_align"] != null) {
                  styletoapply += 'text-align:' + data["settings"]["text_align"] + ';'
                }
                if (valid != true) {
                  styletoapply += 'display:none;'
                }

                $('head').append('<style type="text/css">#delivery_estimator_wrapper{padding: 15px 20px 15px 20px;}</style>');

                if ($("#delivery_estimator_wrapper").length) {
                  $('head').append('<style type="text/css">#delivery_estimator_wrapper{' + styletoapply + '}</style>');
                  $("#delivery_estimator_wrapper").append(finalhtml);
                } else if (template == 'product' && current_product["available"]!=false) {
                  if (theme_name != 'Dawn') {
                    if (finalhtmlpostion == "0") {
                      $("form[action='/cart/add']:first").append('<div id="delivery_estimator_wrapper" style="' + styletoapply + '">' + finalhtml + '</div>');
                    } else if (finalhtmlpostion == "1") {
                      $("form[action='/cart/add']:first").prepend('<div id="delivery_estimator_wrapper" style="' + styletoapply + '">' + finalhtml + '</div>');
                    } else {
                      $("form[action='/cart/add']:first").append('<div id="delivery_estimator_wrapper"  style="' + styletoapply + '">' + finalhtml + '</div>');
                    }
                  } else {
                    if (finalhtmlpostion == "0") {
                      $("form[action='/cart/add']:last").append('<div id="delivery_estimator_wrapper" style="' + styletoapply + '">' + finalhtml + '</div>');
                    } else if (finalhtmlpostion == "1") {
                      $("form[action='/cart/add']:last").prepend('<div id="delivery_estimator_wrapper" style="' + styletoapply + '">' + finalhtml + '</div>');
                    } else {
                      $("form[action='/cart/add']:last").append('<div id="delivery_estimator_wrapper"  style="' + styletoapply + '">' + finalhtml + '</div>');
                    }
                  }
                }

                setTimeout(function () {
                  $(document).find("div[id='delivery_estimator_wrapper']").each(function (i, el) {
                    $(this).addClass('test' + i);
                  });
                  $(document).find(".test1").remove();
                  $(document).find(".test2").remove();
                }, 5000);

                var show_timer_past_cutoff = data["settings"]["show_timer_past_cutoff"]

                if ($.fn.countdown === undefined) {
                  // jQuery(document).ready(function($){
                  // console.log("countdown here");
                  // console.log($.fn.countdown);
                  // })
                } else {
                  var hide_comma_separator = data["settings"]["hide_comma_separator"];
                  var enable_second = data["settings"]["enable_second"];
                  var countdown_format = data["settings"]["countdown_format"];
                  // -----------------------------------------------------------
                  var text_days = data["settings"]["text_days"];
                  var text_hours = data["settings"]["text_hours"];
                  var text_minutes = data["settings"]["text_minutes"];
                  var text_seconds = data["settings"]["text_seconds"];

                  if (!text_days) {
                    text_days = "Day(s)"
                  }
                  if (!text_hours) {
                    text_hours = "hours"
                  }

                  function getminutes(data){
                    if(!text_minutes){
                        return "%s  minutes"
                    }else if(text_minutes){
                        return data + text_minutes
                    }
                  }

                  function getseconds(data){
                    if(!text_seconds){
                        return "%s  seconds"
                    }else if(text_seconds){
                        return data + text_seconds
                    }
                  }
                  // -----------------------------------------------------------
                  if (countdown_format == "format2") {
                    if (enable_second == true) {
                      $("#delivery_estimator_wrapper .countdown").countdown({
                        date: cutofftime,
                        text: "<span class='days'>%sD </span> %sH %sM<span class='seconds' style=''> %sS</span>",
                        end: function () {
                          $('#delivery_estimator_wrapper').remove();
                        }
                      });
                    } else {
                      $("#delivery_estimator_wrapper .countdown").countdown({
                        date: cutofftime,
                        text: "<span class='days'>%sD </span> %sH %sM<span class='seconds' style='display: none;'> %sS</span>",
                        end: function () {
                          $('#delivery_estimator_wrapper').remove();
                        }
                      });
                    }
                  } else if (countdown_format == "format3") {
                    if (enable_second == true) {
                      $("#delivery_estimator_wrapper .countdown").countdown({
                        date: cutofftime,
                        text: "<span class='days'>%s Day(s) </span> %s:%s<span class='seconds' style=''>:%s</span>",
                        end: function () {
                          $('#delivery_estimator_wrapper').remove();
                        }
                      });
                    } else {
                      $("#delivery_estimator_wrapper .countdown").countdown({
                        date: cutofftime,
                        text: "<span class='days'>%s Day(s) </span> %s:%s<span class='seconds' style='display: none;'>:%s</span>",
                        end: function () {
                          $('#delivery_estimator_wrapper').remove();
                        }
                      });
                    }
                  }else if (countdown_format == "format4") {
                    if (enable_second == true) {
                      $("#delivery_estimator_wrapper .countdown").countdown({
                        date: cutofftime,
                        text: "<span class='days'>%s " + text_days + ",</span> %s " + text_hours + ", " + getminutes('%s ') + "<span class='seconds' style=''>, " + getseconds('%s ') + "</span>",
                        end: function () {
                          $('#delivery_estimator_wrapper').remove();
                        }
                      });
                    } else {
                      $("#delivery_estimator_wrapper .countdown").countdown({
                        date: cutofftime,
                        text: "<span class='days'>%s " + text_days + ",</span> %s " + text_hours + ", " + getminutes('%s ') + "<span class='seconds' style='display: none;'>, " + getseconds('%s ') + "</span>",
                        end: function () {
                          $('#delivery_estimator_wrapper').remove();
                        }
                      });
                    }
                  }else if (countdown_format == "format5") {
                    if (enable_second == true) {
                      $("#delivery_estimator_wrapper .countdown").countdown({
                        date: cutofftime,
                        text: "<span class='days'>%sD,</span> %sH, %sM<span class='seconds' style=''>, %sS</span>",
                        end: function () {
                          $('#delivery_estimator_wrapper').remove();
                        }
                      });
                    } else {
                      $("#delivery_estimator_wrapper .countdown").countdown({
                        date: cutofftime,
                        text: "<span class='days'>%sD,</span> %sH, %sM<span class='seconds' style='display: none;'>, %sS</span>",
                        end: function () {
                          $('#delivery_estimator_wrapper').remove();
                        }
                      });
                    }
                    // ------------------------here custom-------------------------------
                  } else {
                    if (enable_second == true) {
                      $("#delivery_estimator_wrapper .countdown").countdown({
                        date: cutofftime,
                        text: "<span class='days'>%s " + text_days + "</span> %s " + text_hours + " " + getminutes('%s ') + "<span class='seconds' style=''> " + getseconds('%s ') + "</span>",
                        end: function () {
                          $('#delivery_estimator_wrapper').remove();
                        }
                      });
                    } else {
                      $("#delivery_estimator_wrapper .countdown").countdown({
                        date: cutofftime,
                        text: "<span class='days'>%s " + text_days + "</span> %s " + text_hours + " " + getminutes('%s ') + "<span class='seconds' style='display: none;'> " + getseconds('%s ') + "</span>",
                        end: function () {
                          $('#delivery_estimator_wrapper').remove();
                        }
                      });
                    }
                  }
                }

                if (show_timer_past_cutoff == true) {
                  var ret = $('#delivery_estimator_wrapper').find('.days').text().replace(' day(s),', '');

                  if (parseInt(ret) == 0) {
                    $('head').append('<style>\
                    #delivery_estimator_wrapper .days{display: none!important;}\
                    </style>');
                  }

                }

                var ret1 = $('#delivery_estimator_wrapper').find('.days').text().replace(' day(s),', '');

                if (parseInt(ret1) == 0) {
                  $('head').append('<style>\
                  #delivery_estimator_wrapper .days{display: none!important;}\
                  </style>');
                }

                if (data["settings"]["text_countdown_color"] != '') {
                  $('#delivery_estimator_wrapper').find('.countdown').css('color', data["settings"]["text_countdown_color"])
                }
                ///////////////////////////////////////////  
              }
              if (data["settings"]["enable_tbtimer"] == true || data["settings"]["enable_vtimer"] == true) {
                if (template == 'product' && current_product["available"]!=false) {  //only count for product page when product is in stock
                  $.ajax({
                    type: 'post',
                    url: base_url +'/store_view_logs',
                    crossOrigin: true,
                    data: {
                      product_id: product_id,
                      shop_name : shop_name,
                      page : template,
                      product_name : current_product.title,
                    },              
                    success: function(json_response) {
                      console.log("%cImpression", "color:green");
                    },
                  });
                }

              }
            } // enable_tbtimer if end
            if (data["settings"] != undefined) {
              if (data["settings"]["enable_vtimer"] == true) {

                var vicon_color = "";
                if (data["settings"]["visual_icon_color"]) {
                  vicon_color = "color:"+data["settings"]["visual_icon_color"]+";";
                }
                var vaccent_color = "";
                if (data["settings"]["visual_accent_color"]) {
                  vaccent_color = "background:"+data["settings"]["visual_accent_color"]+";";
                }
                var vfont_color = "";
                if (data["settings"]["visual_font_color"]) {
                  vfont_color = "color:"+data["settings"]["visual_font_color"]+";";
                }
                var vtext_color = '';
                if (data["settings"]["visual_text_color"]) {
                  vtext_color = "color:"+data["settings"]["visual_text_color"]+";";
                }
                var vbackground_color = '';
                var toparrow_style = '';
                if (data["settings"]["visual_background_color"]) {
                  vbackground_color = "background:"+data["settings"]["visual_background_color"]+";";
                  toparrow_style = "border-bottom: 10px solid "+data["settings"]["visual_background_color"]+";";
                }
                var step_content_style = vtext_color+vbackground_color;

                var vdel_date = data["settings"]["visual_delivery_date_text"];
                var vorder_date = data["settings"]["visual_order_date_text"];
                var vdispatches_date = data["settings"]["visual_dispatches_date_text"];
                var finalhtml1postion = data["settings"]["text_position"];
                var vcustom_arrival = data["settings"]["visual_estimated_arrival"];
                var vcustom_placed = data["settings"]["visual_order_placed"];
                var vcustom_dispatches = data["settings"]["visual_order_dispatches"];
                var vcustom_delivered = data["settings"]["visual_delivered"];

                if (!vcustom_arrival) {
                  vcustom_arrival = 'Estimated arrival';
                }
                if (!vcustom_placed) {
                  vcustom_placed = 'Order placed';
                }
                if (!vcustom_dispatches) {
                  vcustom_dispatches = 'Order dispatches';
                }
                if (!vcustom_delivered) {
                  vcustom_delivered = 'Delivered!';
                }
                if (vicon_color == '') {
                  vicon_color = '#fff';
                }

                var finalhtml1 = '<div class="estimated_arrival_text" style="'+vfont_color+'">\
                            <span class="estimated_date">'+ vdel_date +'</span> <span class="estimated_month"></span> <span style="" class="estimated_arrival_color">'+ vcustom_arrival +'</span>\
                          </div>\
                          <div class="step_indicators_section d-flex align-items-start justify-content-between mt-3">\
                          <div class="step_indicators_border" style="'+vaccent_color+'"></div>\
                          <div class="step_indicators d-flex justify-content-start flex-wrap align-items-start">\
                            <div class="step_circle w-100 d-flex justify-content-start">\
                              <div class="step_circle_icon d-flex align-items-center justify-content-center" style="'+vaccent_color+'"><i class="fas fa-calendar-check" style="'+ vicon_color +'" aria-hidden="true"></i></div>\
                            </div>\
                            <div class="step_content step_content_placed mt-2 d-flex flex-wrap justify-content-start  text-start" style="'+step_content_style+'">\
                              <div class="toparrowbox" style="'+toparrow_style+'"></div>\
                              <span class="w-100">'+vorder_date+'</span>\
                              <span class="w-100">'+vcustom_placed+'</span>\
                            </div>\
                          </div>\
                          <div class="step_indicators d-flex justify-content-center align-items-start flex-wrap">\
                            <div class="step_circle w-100 d-flex justify-content-center">\
                              <div class="step_circle_icon d-flex align-items-center justify-content-center" style="'+vaccent_color+'"><i class="fas fa-shipping-fast" style="'+ vicon_color +'" aria-hidden="true"></i></div>\
                            </div>\
                            <div class="step_content step_content_dispatches mt-2 d-flex flex-wrap text-center" style="'+step_content_style+'">\
                              <div class="toparrowbox" style="'+toparrow_style+'"></div>\
                              <span class="w-100">'+vdispatches_date+'</span>\
                              <span class="w-100">'+vcustom_dispatches+'</span>\
                            </div>\
                          </div>\
                          <div class="step_indicators d-flex justify-content-end align-items-start flex-wrap">\
                            <div class="step_circle w-100 d-flex justify-content-end">\
                              <div class="step_circle_icon d-flex align-items-center justify-content-center" style="'+vaccent_color+'"><i class="fas fa-box-open" style="'+ vicon_color +'" aria-hidden="true"></i></div>\
                            </div>\
                            <div class="step_content step_content_delivered mt-2 d-flex flex-wrap text-end" style="'+step_content_style+'">\
                              <div class="toparrowbox" style="'+toparrow_style+'"></div>\
                              <span class="w-100">'+vdel_date+'</span>\
                              <span class="w-100">'+vcustom_delivered+'</span>\
                            </div>\
                          </div>\
                        </div>';

                var styletoapply1 = ''
                if (data["settings"]["visual_font_color"] != '') {
                  styletoapply1 += 'color:' + data["settings"]["visual_font_color"] + ';'
                }
                if (data["settings"]["visual_margin_top"] != '') {
                  styletoapply1 += 'margin-top:' + data["settings"]["visual_margin_top"] + 'px;'
                }
                if (data["settings"]["visual_margin_bottom"] != '') {
                  styletoapply1 += 'margin-bottom:' + data["settings"]["visual_margin_bottom"] + 'px;'
                }
                if (data["settings"]["visual_margin_left"] != '') {
                  styletoapply1 += 'margin-left:' + data["settings"]["visual_margin_left"] + 'px;'
                }
                if (data["settings"]["visual_margin_right"] != '') {
                  styletoapply1 += 'margin-right:' + data["settings"]["visual_margin_right"] + 'px;'
                }
                if (valid != true) {
                  styletoapply1 += 'display:none;'
                }
                styletoapply1 += 'width: 100%;'

                // $('head').append('<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet">');
                $('head').append('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">');
                $('head').append('<style type="text/css">.custom_vtcolumn{flex-grow: 1;flex-basis: 33.33333%;max-width: 33.33333%;}.custom_vtcolumn>div{display: flex;flex-direction: column;}.custom_vtcolumn>div>div{display: flex; flex-direction: row; width: 100%; margin-bottom: 10px;}.fg_1{  flex-grow: 1;}.fg_1>div{ height: 50%; border-width: 2px;}.vicon{font-size: 17px;}</style>');
                $('head').append('<style type="text/css">.estimated_arrival_text{color: #004b68;}.estimated_date{font-size: 28px;font-weight: bold;}.estimated_month{font-size: 18px;}.estimated_arrival_color{font-size: 1.25rem !important;}.step_indicators_section{position: relative;}.step_indicators_border{display: block !important;position: absolute;top: 21px;left: 0px;height: 2px;width: 100%;background: #004b68;}.step_indicators {position: relative;z-index: 999;padding-right: 8px;}.step_indicators:last-child  {padding-right: 0px;}.step_circle_icon {width: 44px;height: 44px;border-radius: 50px;background: #004b68;}.step_circle_icon i.fas {font-size: 18px;color: #fff;}.step_content {position: relative;background: #a7c638;border-radius: 4px;padding: 6px 10px;width: auto;color: #fff;font-size: 14px;}.step_content .toparrowbox {display:block;width: 0;height: 0;border-left: 10px solid transparent;border-right: 10px solid transparent;border-bottom: 10px solid #a7c638;position: absolute;top: -7px;left: 11px;border-radius: 50px;}.step_content.step_content_dispatches .toparrowbox {top: -7px;left: 50%;margin-left: -10px;}.step_content.step_content_delivered .toparrowbox {top: -7px;right: 11px;left: inherit;}</style>');

                if ($("#visual_estimator_wrapper").length) {
                  $('head').append('<style type="text/css">#visual_estimator_wrapper{' + styletoapply1 + '}#visual_estimator_wrapper .fa,#visual_estimator_wrapper .fas,#visual_estimator_wrapper .far{font-family: "Font Awesome 5 Free" !important;}</style>');
                  $("#visual_estimator_wrapper").html(finalhtml1);
                } else if (template == 'product' && current_product["available"]!=false) {
                  if (theme_name != 'Dawn') {
                    if (finalhtml1postion == "0") {
                      $("form[action='/cart/add']:first").append('<div id="visual_estimator_wrapper" style="' + styletoapply1 + '">' + finalhtml1 + '</div>');
                    } else if (finalhtml1postion == "1") {
                      $("form[action='/cart/add']:first").prepend('<div id="visual_estimator_wrapper" style="' + styletoapply1 + '">' + finalhtml1 + '</div>');
                    } else {
                      $("form[action='/cart/add']:first").append('<div id="visual_estimator_wrapper"  style="' + styletoapply1 + '">' + finalhtml1 + '</div>');
                    }
                  } else {
                    if (finalhtml1postion == "0") {
                      $("form[action='/cart/add']:last").append('<div id="visual_estimator_wrapper" style="' + styletoapply1 + '">' + finalhtml1 + '</div>');
                    } else if (finalhtml1postion == "1") {
                      $("form[action='/cart/add']:last").prepend('<div id="visual_estimator_wrapper" style="' + styletoapply1 + '">' + finalhtml1 + '</div>');
                    } else {
                      $("form[action='/cart/add']:last").append('<div id="visual_estimator_wrapper"  style="' + styletoapply1 + '">' + finalhtml1 + '</div>');
                    }
                  }
                }
              }
            }

          } // success end

        })
      }
    else {
      // valid is false
    }
  });





  //////////////////////////// 
  }
  if(window.jQuery){
    jQueryCode();
  } 
  else{   
    var script = document.createElement('script'); 
    document.head.appendChild(script);  
    script.type = 'text/javascript';
    script.src = "//ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js";
    script.onload = jQueryCode;
  }


  }else{
  }

