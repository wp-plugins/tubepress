var TubePressLogger=(function(){var c=location.search.indexOf("tubepress_debug=true")!==-1,f=window.console,a=f!==undefined,b=function(){return c&&a},e=function(g){f.log(g)},d=function(g){f.dir(g)};return{on:b,log:e,dir:d}}()),TubePressJson=(function(){var b=jQuery,a=b.fn.jquery,e=/1\.6|7|8|9\.[0-9]+/.test(a)!==false,d,c=function(f){return d(f)};if(e){d=function(f){return b.parseJSON(f)}}else{d=function(f){if(typeof f!=="string"||!f){return null}f=b.trim(f);if(/^[\],:{}\s]*$/.test(f.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]").replace(/(?:^|:|,)(?:\s*\[)+/g,""))){return window.JSON&&window.JSON.parse?window.JSON.parse(f):(new Function("return "+f))()}else{throw"Invalid JSON: "+f}}}return{parse:c}}()),TubePressAjax=(function(){var b=jQuery,e=function(m,h,j,g,i,l){var k=function(o){var p=o.responseText,n=g?b("<div>").append(p).find(g):p;b(j).html(n);if(b.isFunction(l)){l()}};if(b.isFunction(i)){i()}b.ajax({url:h,type:m,dataType:"html",complete:k})},c=function(k,h,i,j,g){b.ajax({url:h,type:k,data:i,dataType:g,complete:j})},f=function(g){b(g).fadeTo(0,0.3)},d=function(g){b(g).fadeTo(0,1)},a=function(m,h,k,g,j,l){f(k);var i=function(){d(k)};if(b.isFunction(l)){i=function(){d(k);l()}}e(m,h,k,g,j,i)};return{applyLoadingStyle:f,removeLoadingStyle:d,loadAndStyle:a,get:c}}()),TubePressCss=(function(){var a=function(c){var b=document.createElement("link");b.setAttribute("rel","stylesheet");b.setAttribute("type","text/css");b.setAttribute("href",c);document.getElementsByTagName("head")[0].appendChild(b)};return{load:a}}()),TubePressEvents=(function(){return{GALLERY_VIDEO_CHANGE:"tubepressGalleryVideoChange",PLAYBACK_STARTED:"tubepressPlaybackStarted",PLAYBACK_STOPPED:"tubepressPlaybackStopped",PLAYBACK_BUFFERING:"tubepressPlaybackBuffering",PLAYBACK_PAUSED:"tubepressPlaybackPaused",PLAYBACK_ERROR:"tubepressPlaybackError",EMBEDDED_LOAD:"tubepressEmbeddedLoad",NEW_THUMBS_LOADED:"tubepressNewThumbnailsLoaded",NEW_GALLERY_LOADED:"tubepressNewGalleryLoaded",PLAYER_INVOKE:"tubepressPlayerInvoke",PLAYER_POPULATE:"tubepressPlayerPopulate"}}()),TubePressGallery=(function(){var a={},r=jQuery(document),c=TubePressEvents,f="nvpMap",n="jsMap",d=function(t){return a[t][n].ajaxPagination},g=function(t){return a[t][n].autoNext},b=function(t){return a[t][n].fluidThumbs},s=function(t){return a[t][f].embeddedHeight},q=function(t){return a[t][f].embeddedWidth},p=function(t){return a[t][n].httpMethod},e=function(t){return a[t][f]},o=function(t){return a[t][f].playerLocation},l=function(t){return a[t][n].playerJsUrl},k=function(t){return a[t][n].playerLocationProducesHtml},i=function(t){return a[t][n].sequence},h=function(t){return a[t]!==undefined},j=function(t,u){a[t]=u;r.trigger(c.NEW_GALLERY_LOADED,t)},m=function(t,u){r.ready(function(){j(t,u)})};return{isAjaxPagination:d,isAutoNext:g,isFluidThumbs:b,getEmbeddedHeight:s,getEmbeddedWidth:q,getHttpMethod:p,getNvpMap:e,getPlayerLocationName:o,getPlayerLocationProducesHtml:k,getPlayerJsUrl:l,getSequence:i,isRegistered:h,init:m}}()),TubePressPlayers=(function(){var b=jQuery,e=b(document),d=TubePressGallery,f=TubePressEvents,a={},c=function(k,i){var h=d.getPlayerLocationName(i),j=d.getPlayerJsUrl(i);if(a[h]!==true){a[h]=true;b.getScript(j)}},g=function(n,r,o){var k=d.getPlayerLocationName(r),q=d.getEmbeddedHeight(r),j=d.getEmbeddedWidth(r),l=d.getNvpMap(r),p=function(u){var s=TubePressJson.parse(u.responseText),v=s.title,t=s.html;e.trigger(f.PLAYER_POPULATE+k,[v,t,q,j,o,r])},m={action:"playerHtml",tubepress_video:o},i=TubePressGlobalJsConfig.baseUrl+"/src/main/php/scripts/ajaxEndpoint.php",h;b.extend(m,l);e.trigger(f.PLAYER_INVOKE+k,[o,r,j,q]);if(d.getPlayerLocationProducesHtml(r)){h=d.getHttpMethod(r);TubePressAjax.get(h,i,m,p,"json")}};e.bind(f.NEW_GALLERY_LOADED,c);e.bind(f.GALLERY_VIDEO_CHANGE,g)}()),TubePressSequencer=(function(){var n=TubePressGallery,b=jQuery,c=b(document),p=TubePressEvents,o=TubePressLogger,h="isCurrentlyPlayingVideo",i="currentVideoId",a={},k=function(s){var r;for(r in a){if(a.hasOwnProperty(r)){if(s(r)){return r}}}return undefined},m=function(r){var s=function(u){var t=a[u];return t[i]===r};return k(s)},l=function(r){var s=function(u){var t=a[u],v=t[i]===r&&t[h];if(v){return u}return false};return k(s)},e=function(t,s){var r={},u=n.getSequence(s);r[h]=false;if(u){r[i]=u[0]}a[s]=r;if(o.on()){o.log("Gallery "+s+" loaded")}},j=function(r,s){a[r][i]=s;c.trigger(p.GALLERY_VIDEO_CHANGE,[r,s])},f=function(t){var v=n.getSequence(t),s=a[t][i],r=b.inArray(s,v),u=v?v.length-1:r;if(r===-1||r===u){return}j(t,v[r+1])},d=function(t){var u=n.getSequence(t),s=a[t][i],r=b.inArray(s,u);if(r===-1||r===0){return}j(t,u[r+1])},g=function(s,r){var t=m(r);if(!t){return}a[t][h]=true;a[t][i]=r;if(o.on()){o.log("Playback of "+r+" started for gallery "+t)}},q=function(s,r){var t=l(r);if(!t){return}a[t][h]=false;if(o.on()){o.log("Playback of "+r+" stopped for gallery "+t)}if(n.isAutoNext(t)&&n.getSequence(t)){if(o.on()){o.log("Auto-starting next for gallery "+t)}f(t)}};c.bind(p.NEW_GALLERY_LOADED,e);c.bind(p.PLAYBACK_STARTED,g);c.bind(p.PLAYBACK_STOPPED,q);return{changeToVideo:j,next:f,prev:d}}()),TubePressThumbs=(function(){var b=jQuery,n=TubePressEvents,c=b(document),i=Math,d=n.NEW_THUMBS_LOADED+" "+n.NEW_GALLERY_LOADED,g=function(o){return"#tubepress_gallery_"+o+"_thumbnail_area"},l=function(o){return b(g(o))},j=function(o){return o[3]},m=function(p){var o=p.lastIndexOf("_");return p.substring(16,o)},e=function(r){var p=l(r),o=p.find("img:first"),q=120;if(o.length===0){o=p.find("div.tubepress_thumb:first > div.tubepress_embed");if(o.length===0){return q}}q=o.attr("width");if(q){return q}return o.width()},k=function(){var o=b(this).attr("rel").split("_"),p=j(o),q=m(b(this).attr("id"));TubePressSequencer.changeToVideo(p,q)},a=function(t){l(t).css({width:"100%"});var r=g(t),v=e(t),p=b(r),u=p.width(),q=i.floor(u/v),s=i.floor(u/q),o=b(r+" div.tubepress_thumb");p.css({width:"100%"});p.css({width:u});o.css({width:s})},h=function(o){var p=1,r="div#tubepress_gallery_"+o+" div.tubepress_thumbnail_area:first > div.pagination:first > span.current",q=b(r);if(q.length>0){p=q.html()}return p},f=function(p,o){b("#tubepress_gallery_"+o+" a[id^='tubepress_']").click(k);if(TubePressGallery.isFluidThumbs(o)){a(o)}};c.bind(d,f);return{getCurrentPageNumber:h,getGalleryIdFromRelSplit:j,getThumbAreaSelector:g,getVideoIdFromIdAttr:m}}()),TubePressAjaxPagination=(function(){var a=jQuery,b=a(document),g=TubePressEvents,i=TubePressGallery,c=g.NEW_THUMBS_LOADED+" "+g.NEW_GALLERY_LOADED,d=function(j){b.trigger(TubePressEvents.NEW_THUMBS_LOADED,j)},f=function(m,t){var r=TubePressGlobalJsConfig.baseUrl,l=i.getNvpMap(t),s=m.attr("rel"),j=TubePressThumbs.getThumbAreaSelector(t),p=function(){d(t)},q={action:"shortcode"},o=r+"/src/main/php/scripts/ajaxEndpoint.php?tubepress_"+s+"&"+a.param(a.extend(q,l)),n=j+" > *",k=i.getHttpMethod(t);TubePressAjax.loadAndStyle(k,o,j,n,"",p)},h=function(k){var j=function(){f(a(this),k)};a("#tubepress_gallery_"+k+" div.pagination a").click(j)},e=function(k,j){if(i.isAjaxPagination(j)){h(j)}};b.bind(c,e)}()),TubePressCompat=(function(){var a=jQuery,b=function(){a.getScript=function(d,e,c){return a.ajax({type:"GET",url:d,success:e,dataType:"script",cache:c})}};return{init:b}}()),TubePressPlayerApi=(function(){var E=jQuery,h=E(document),w=TubePressEvents,A=TubePressLogger,L=false,c="tubepress-youtube-player-",G={},f=/[a-z0-9\-_]{11}/i,r=false,d="tubepress-vimeo-player-",I={},z=/[0-9]+/,t="http",b="https",q=function(N){return f.test(N)},v=function(N){return z.test(N)},K=function(N,O){if(A.on()){A.log("Firing "+N+" for "+O)}h.trigger(N,O)},B=function(N){K(w.PLAYBACK_STARTED,N)},k=function(N){K(w.PLAYBACK_STOPPED,N)},a=function(N){K(w.PLAYBACK_BUFFERING,N)},H=function(N){K(w.PLAYBACK_PAUSED,N)},C=function(N){K(w.PLAYBACK_ERROR,N)},M=function(R){var S=R.target.a.id,N=S.replace(c,""),Q=G[N],P,T,O;if(!E.isFunction(Q.getVideoUrl)){return null}P=Q.getVideoUrl();T=P.split("v=")[1];O=T.indexOf("&");if(O!==-1){T=T.substring(0,O)}return T},j=function(N){return N.replace(d,"")},g=function(Q,P,N){if(P()===true){Q();return}var O=function(){g(Q,P,N)};setTimeout(O,N)},u=function(){return window.YT!==undefined&&YT.Player!==undefined},y=function(){return window.Froogaloop!==undefined},x=function(){var N=TubePressGlobalJsConfig.https?b:t;if(!L&&!u()){if(A.on()){A.log("Loading YT API")}L=true;E.getScript(N+"://www.youtube.com/player_api")}},p=function(){var N=TubePressGlobalJsConfig.https?b:t;if(!r&&!y()){if(A.on()){A.log("Loading Vimeo API")}r=true;E.getScript(N+"://a.vimeocdn.com/js/froogaloop2.min.js")}},l=function(O){var Q=M(O),N=O.data,P=YT.PlayerState;if(Q===null){return}switch(N){case P.PLAYING:B(Q);break;case P.PAUSED:H(Q);break;case P.ENDED:k(Q);break;case P.BUFFERING:a(Q);break;default:if(A.on()){A.log("Unknown YT event");A.dir(O)}break}},m=function(N){var O=M(N);if(O===null){return}if(A.on()){A.log("YT error");A.dir(N)}C(O)},D=function(N){var O=j(N);B(O)},i=function(N){var O=j(N);H(O)},J=function(N){var O=j(N);k(O)},n=function(N){var O=I[N];O.addEvent("play",D);O.addEvent("pause",i);O.addEvent("finish",J)},o=function(N){x();var O=function(){if(A.on()){A.log("Register YT video "+N+" with TubePress")}G[N]=new YT.Player(c+N,{events:{onError:m,onStateChange:l}})};g(O,u,300)},F=function(P){p();var N=d+P,O=document.getElementById(N),Q=function(){var R;if(A.on()){A.log("Register Vimeo video "+P+" with TubePress")}R=new Froogaloop(O);I[N]=R;R.addEvent("ready",n)};g(Q,y,800)},s=function(N){if(q(N)){o(N)}else{if(v(N)){F(N)}}K(w.EMBEDDED_LOAD,N)},e=function(N){h.ready(function(){try{s(N)}catch(O){A.log("Error when registering: "+O)}})};return{register:e,isYouTubeVideoId:q,isVimeoVideoId:v,onYouTubeStateChange:l,onYouTubeError:m,onVimeoPlay:D,onVimeoPause:i,onVimeoFinish:J,onVimeoReady:n}}()),TubePressAjaxSearch=(function(){var a=function(g,h,m){var d=jQuery,o=TubePressGallery,k=TubePressLogger,n=g.nvpMap.searchResultsDomId,j,f,l,c={action:"shortcode",tubepress_search:h},b="#tubepress_gallery_"+m,e=o.isRegistered(m),i=n!==undefined&&d(n).length>0;if(e){l=TubePressThumbs.getThumbAreaSelector(m);f=l+" > *";j=function(){d(document).trigger(TubePressEvents.NEW_THUMBS_LOADED,m)}}else{if(i){l=n}else{if(k.on()){k.log("Bad target selector and missing gallery")}return}}if(k.on()){k.log("Final dest: "+l);k.log("Ajax selector: "+f)}d.extend(c,g.nvpMap);TubePressAjax.loadAndStyle(g.jsMap.httpMethod,TubePressGlobalJsConfig.baseUrl+"/src/main/php/scripts/ajaxEndpoint.php?"+d.param(c),l,f,null,j)};return{performSearch:a}}()),tubePressBoot=function(){TubePressCompat.init()};if(!jQuery.browser.msie){var oldReady=jQuery.ready;jQuery.ready=function(){try{oldReady.apply(this,arguments)}catch(a){if(window.console!==undefined){console.log("Caught exception when booting TubePress: "+a)}}tubePressBoot()};jQuery.ready.promise=oldReady.promise}else{jQuery(document).ready(function(){tubePressBoot()})};