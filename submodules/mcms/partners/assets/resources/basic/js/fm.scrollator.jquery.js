Scrollator={scrollatorElementsStack:[],refreshAll:function(){for(var e=Scrollator.scrollatorElementsStack.length;e--;)!Scrollator.scrollatorElementsStack[e].$sourceElement.closest("body").length>0?Scrollator.scrollatorElementsStack[e].destroy():Scrollator.scrollatorElementsStack[e].refresh()}},$(window).load(function(){Scrollator.refreshAll()}),function(e){e.scrollator=function(o,t){var l={customClass:"",appendTo:"body",preventPropagation:!1,minHandleHeightPercent:10,zIndex:""},r=this;r.settings={};var n=e("html"),s=e(o);r.$sourceElement=s;var i=null,a=null,d=null,c=null,u=null,h=null,p=!1,m=0,g=0,w=0;r.init=function(){r.settings=e.extend({},l,t),i=e("#scrollator_holder"),s.addClass("scrollator"),a=e(document.createElement("div")).addClass("scrollator_lane_holder"),a.addClass(r.settings.customClass),a.css("z-index",s.css("z-index")),""!==r.settings.zIndex&&a.css("z-index",r.settings.zIndex),s.is("body")&&a.addClass("scrollator_on_body"),d=e(document.createElement("div")).addClass("scrollator_lane"),c=e(document.createElement("div")).addClass("scrollator_handle_holder"),u=e(document.createElement("div")).addClass("scrollator_handle"),k(),"BODY"==s.prop("tagName")?(n.bind("mousewheel DOMMouseScroll",b),n.bind("scroll",C),n.bind("mousemove",v)):(s.bind("mousewheel DOMMouseScroll",b),s.bind("scroll",C),s.bind("mousemove",v)),a.bind("mousewheel DOMMouseScroll",b),d.bind("mousewheel DOMMouseScroll",b),c.bind("mousewheel DOMMouseScroll",b),u.bind("mousewheel DOMMouseScroll",b),a.bind("mousemove",v),d.bind("mousemove",v),c.bind("mousemove",v),u.bind("mousemove",v),c.bind("mousedown",f),u.bind("mousedown",f),e(window).bind("mouseup",S),e(window).bind("mousemove",y),e(window).bind("keydown",E),c.append(u),d.append(c),a.append(d),i.append(a),C(),document.body.hasScrollatorPageResizeEventHandler||(document.body.hasScrollatorPageResizeEventHandler=!0,e(window).bind("resize",function(){Scrollator.refreshAll()})),v()};var b=function(o){if(!(o.ctrlKey||o.metaKey||e(o.currentTarget).hasClass("scrollator_noscroll")||"auto"==e(o.target).css("overflow-y")&&"fixed"!=e(o.target).css("position")&&"PRE"!=e(o.target).prop("tagName"))){var t=(s.is("body")?e(window):s).scrollTop(),l=t,n=0;void 0!==o.originalEvent.wheelDeltaY&&0!==o.originalEvent.wheelDeltaY?n=o.originalEvent.wheelDeltaY/1.2:void 0!==o.originalEvent.wheelDelta&&0!==o.originalEvent.wheelDelta?n=o.originalEvent.wheelDelta/1.2:void 0!==o.originalEvent.detail&&0!==o.originalEvent.detail&&(n=-33.33*o.originalEvent.detail),t+=-1*n,(s.is("body")?e(window):s).scrollTop(t),t=(s.is("body")?e(window):s).scrollTop(),Scrollator.refreshAll(),(l!=t||r.settings.preventPropagation||e(o.currentTarget).hasClass("scrollator_nopropagation"))&&(o.preventDefault(),o.stopPropagation())}},v=function(o){return"undefined"!=typeof o&&e(o.currentTarget).hasClass("scrollator_noscroll")?void a.css("opacity",0):(clearTimeout(h),void(s[0].scrollHeight>(s.is("body")?e(window).height():s.innerHeight())?(a.css("opacity",1),h=setTimeout(function(){a.css("opacity",0)},1500)):a.css("opacity",0)))},f=function(o){o.preventDefault(),p=!0,m=o.clientY,g=(s.is("body")?e(window):s).scrollTop(),w=o.offsetY,a.addClass("hover")},y=function(o){if(p){var t=o.clientY-m,l=s[0].scrollHeight/(s.is("body")?e(window).height():s.innerHeight());(s.is("body")?e(window):s).scrollTop(g+t*l),Scrollator.refreshAll(),v()}},S=function(){p=!1,a.removeClass("hover")},E=function(o){if(s.is(":visible")){var t={pageUp:33,pageDown:34,left:37,up:38,right:39,down:40};if((o.keyCode==t.pageUp||o.keyCode==t.pageDown||o.keyCode==t.up||o.keyCode==t.down)&&"TEXTAREA"!=e(document.activeElement).prop("tagName")){var l=(s.is("body")?e(window):s).scrollTop(),r=0;o.keyCode==t.pageUp||o.keyCode==t.pageDown?r=.9*(s.is("body")?e(window).height():s.innerHeight()):(o.keyCode==t.up||o.keyCode==t.down)&&(r=0),o.keyCode==t.pageUp||o.keyCode==t.up?l-=r:(o.keyCode==t.pageDown||o.keyCode==t.down)&&(l+=r),(s.is("body")?e(window):s).scrollTop(l),Scrollator.refreshAll(),v()}}};r.refresh=function(){C()};var C=function(){if(s.is(":visible")){var o=s[0].getBoundingClientRect(),t=e(window),l={left:o.left+t.scrollLeft(),top:o.top+t.scrollTop(),right:o.right+t.scrollLeft(),bottom:o.bottom+t.scrollTop(),width:o.width,height:o.height},n=parseInt(s.css("border-top-width"),10),i=parseInt(s.css("border-right-width"),10),d=parseInt(s.css("border-bottom-width"),10),u=(parseInt(s.css("border-left-width"),10),s.prop("scrollHeight")),h=s.is("body")?t.height():s.innerHeight(),p=h/u*100,m=(s.is("body")?t:s).scrollTop()/u*100,g=m/((100-p)/100),w=0;p<r.settings.minHandleHeightPercent&&(w=(r.settings.minHandleHeightPercent-p)*(g/100),p=r.settings.minHandleHeightPercent),s.is("body")||a.css({top:l.top+n,right:-l.right+i,bottom:-l.bottom+d}),c.css({height:p+"%",top:m-w+"%"})}},k=function(){0===i.length&&(i=e(document.createElement("div")).attr("id","scrollator_holder"),e(r.settings.appendTo).append(i))};r.hide=function(){a.hide()},r.show=function(){a.show()},r.destroy=function(){s.removeClass("scrollator"),e.removeData(o,"scrollator"),"BODY"==s.prop("tagName")?(n.unbind("mousewheel DOMMouseScroll",b),n.unbind("mousemove",v)):(s.unbind("mousewheel DOMMouseScroll",b),s.unbind("mousemove",v)),e(window).unbind("mouseup",S),e(window).unbind("mousemove",y),e(window).unbind("keydown",E),a.remove();for(var t=Scrollator.scrollatorElementsStack.length;t--;)Scrollator.scrollatorElementsStack[t]===r&&Scrollator.scrollatorElementsStack.splice(t,1);0===i.children().length&&(i.remove(),i=null)},r.init()},e.fn.scrollator=function(o){return o=void 0!==o?o:{},this.each(function(){if(!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|ARM|Touch|Opera Mini/i.test(navigator.userAgent))if("object"==typeof o){if(void 0===e(this).data("scrollator")){var t=new e.scrollator(this,o);Scrollator.scrollatorElementsStack.push(t),e(this).data("scrollator",t)}}else e(this).data("scrollator")[o]?e(this).data("scrollator")[o].apply(this,Array.prototype.slice.call(arguments,1)):e.error("Method "+o+" does not exist in $.scrollator")})}}(jQuery),$(function(){$(".scrollator").each(function(){var e=$(this),o={};$.each(e.data(),function(e,t){"scrollator"==e.substring(0,10)&&(o[e.substring(10,11).toLowerCase()+e.substring(11)]=t)}),e.scrollator(o)})});