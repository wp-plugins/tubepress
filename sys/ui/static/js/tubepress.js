var TubePressLogger=(function(){var c=location.search.indexOf("tubepress_debug=true")!==-1,f=window.console,a=typeof f!=="undefined",b=function(){return c&&a},e=function(g){f.log(g)},d=function(g){f.dir(g)};return{on:b,log:e,dir:d}}());var TubePressAjax=(function(){var d=jQuery,c="GET",b="function",g=function(j,l,i,k,n){var m=function(p){var q=p.responseText,o=i?d("<div>").append(q).find(i):q;d(l).html(o);if(typeof n===b){n()}};if(typeof k===b){k()}d.ajax({url:j,type:c,dataType:"html",complete:m})},e=function(j,k,l,i){d.ajax({url:j,type:c,data:k,dataType:i,complete:l})},h=function(i){d(i).fadeTo(0,0.3)},f=function(i){d(i).fadeTo(0,1)},a=function(j,m,i,l,n){h(m);var k=function(){f(m)};if(typeof n===b){k=function(){f(m);n()}}g(j,m,i,l,k)};return{load:g,applyLoadingStyle:h,removeLoadingStyle:f,loadAndStyle:a,get:e}}());var TubePressCss=(function(){var a=function(c){var b=document.createElement("link");b.setAttribute("rel","stylesheet");b.setAttribute("type","text/css");b.setAttribute("href",c);document.getElementsByTagName("head")[0].appendChild(b)};return{load:a}}());var TubePressEvents=(function(){return{GALLERY_VIDEO_CHANGE:"tubepressGalleryVideoChange",PLAYBACK_STARTED:"tubepressPlaybackStarted",PLAYBACK_STOPPED:"tubepressPlaybackStopped",PLAYBACK_BUFFERING:"tubepressPlaybackBuffering",PLAYBACK_PAUSED:"tubepressPlaybackPaused",PLAYBACK_ERROR:"tubepressPlaybackError",EMBEDDED_LOAD:"tubepressEmbeddedLoad",NEW_THUMBS_LOADED:"tubepressNewThumbnailsLoaded",NEW_GALLERY_LOADED:"tubepressNewGalleryLoaded",PLAYER_INVOKE:"tubepressPlayerInvoke",PLAYER_POPULATE:"tubepressPlayerPopulate"}}());var TubePressGallery=(function(){var b={},f=jQuery(document),a={},n=TubePressEvents,c=function(p){return b[p].ajaxPagination},h=function(p){return b[p].autoNext},d=function(p){return b[p].fluidThumbs},j=function(p){return b[p].embeddedHeight},g=function(p){return b[p].embeddedWidth},e=function(p){return b[p].playerLocationName},l=function(p){return b[p].sequence},o=function(p){return b[p].shortcode},k=function(p){return b[p].themeCSS},i=function(p,r){b[p]=r;var q=decodeURIComponent(k(p));if(q!==""&&a[q]!==true){TubePressCss.load(getTubePressBaseUrl()+q);a[q]=true}f.trigger(n.NEW_GALLERY_LOADED,p)},m=function(p,q){f.ready(function(){i(p,q)})};return{isAjaxPagination:c,isAutoNext:h,isFluidThumbs:d,getEmbeddedHeight:j,getEmbeddedWidth:g,getPlayerLocationName:e,getSequence:l,getShortcode:o,init:m}}());var TubePressPlayers=(function(){var c=jQuery,h=c(document),i=TubePressGallery,g=TubePressEvents,a=decodeURIComponent,e={},d=function(m,k){var j=i.getPlayerLocationName(k),l=getTubePressBaseUrl()+"/sys/ui/static/players/"+j+"/"+j+".js";if(e[j]!==true){e[j]=true;c.getScript(l)}},f=function(j){return j!=="vimeo"&&j!=="youtube"&&j!=="solo"&&j!=="static"},b=function(o,s,p){var l=i.getPlayerLocationName(s),r=i.getEmbeddedHeight(s),k=i.getEmbeddedWidth(s),m=i.getShortcode(s),q=function(v){var t=c.parseJSON(v.responseText),w=a(t.title),u=a(t.html);h.trigger(g.PLAYER_POPULATE+l,[w,u,r,k,p,s])},n={tubepress_video:p,tubepress_shortcode:m},j=getTubePressBaseUrl()+"/sys/scripts/ajax/playerHtml.php";h.trigger(g.PLAYER_INVOKE+l,[p,s,k,r]);if(f(l)){TubePressAjax.get(j,n,q,"json")}};h.bind(g.NEW_GALLERY_LOADED,d);h.bind(g.GALLERY_VIDEO_CHANGE,b)}());var TubePressSequencer=(function(){var n=TubePressGallery,b=jQuery,c=b(document),p=TubePressEvents,o=TubePressLogger,h="isCurrentlyPlayingVideo",i="currentVideoId",a={},k=function(s){var r;for(r in a){if(a.hasOwnProperty(r)){if(s(r)){return r}}}return undefined},m=function(r){var s=function(u){var t=a[u];return t[i]===r};return k(s)},l=function(r){var s=function(u){var t=a[u],v=t[i]===r&&t[h];if(v){return u}};return k(s)},e=function(t,s){var r={},u=n.getSequence(s);r[h]=false;if(u){r[i]=u[0]}a[s]=r;if(o.on()){o.log("Gallery "+s+" loaded")}},j=function(r,s){a[r][i]=s;c.trigger(p.GALLERY_VIDEO_CHANGE,[r,s])},f=function(u){var w=n.getSequence(u),t=a[u][i],r=b.inArray(t,w),s=r,v=w?w.length-1:r;if(r===-1||r===v){return}j(u,w[s+1])},d=function(u){var v=n.getSequence(u),t=a[u][i],r=b.inArray(t,v),s=r;if(r===-1||r===0){return}j(u,v[s+1])},g=function(s,r){var t=m(r);if(!t){return}a[t][h]=true;a[t][i]=r;if(o.on()){o.log("Playback of "+r+" started for gallery "+t)}},q=function(s,r){var t=l(r);if(!t){return}a[t][h]=false;if(o.on()){o.log("Playback of "+r+" stopped for gallery "+t)}if(n.isAutoNext(t)&&n.getSequence(t)){if(o.on()){o.log("Auto-starting next for gallery "+t)}f(t)}};c.bind(p.NEW_GALLERY_LOADED,e);c.bind(p.PLAYBACK_STARTED,g);c.bind(p.PLAYBACK_STOPPED,q);return{changeToVideo:j,next:f,prev:d}}());var TubePressThumbs=(function(){var b=jQuery,n=TubePressEvents,c=b(document),i=Math,d=n.NEW_THUMBS_LOADED+" "+n.NEW_GALLERY_LOADED,g=function(o){return"#tubepress_gallery_"+o+"_thumbnail_area"},l=function(o){return b(g(o))},j=function(o){return o[3]},m=function(p){var o=p.lastIndexOf("_");return p.substring(16,o)},e=function(o){return l(o).find("img:first").width()},k=function(){var o=b(this).attr("rel").split("_"),p=j(o),q=m(b(this).attr("id"));TubePressSequencer.changeToVideo(p,q)},a=function(t){l(t).css({width:"100%"});var r=g(t),v=e(t),p=b(r),u=p.width(),q=i.floor(u/v),s=i.floor(u/q),o=b(r+" div.tubepress_thumb");p.css({width:"100%"});p.css({width:u});o.css({width:s})},h=function(o){var p=1,r="div#tubepress_gallery_"+o+" div.tubepress_thumbnail_area:first > div.pagination:first > span.current",q=b(r);if(q.length>0){p=q.html()}return p},f=function(p,o){b("#tubepress_gallery_"+o+" a[id^='tubepress_']").click(k);if(TubePressGallery.isFluidThumbs(o)){a(o)}};c.bind(d,f);return{getCurrentPageNumber:h,getGalleryIdFromRelSplit:j,getThumbAreaSelector:g,getVideoIdFromIdAttr:m}}());var TubePressAjaxPagination=(function(){var a=jQuery,b=a(document),g=TubePressEvents,i=TubePressGallery,c=g.NEW_THUMBS_LOADED+" "+g.NEW_GALLERY_LOADED,d=function(j){b.trigger(TubePressEvents.NEW_THUMBS_LOADED,j)},f=function(l,r){var p=getTubePressBaseUrl(),k=i.getShortcode(r),q=l.attr("rel"),j=TubePressThumbs.getThumbAreaSelector(r),n=function(){d(r)},o=p+"/sys/scripts/ajax/shortcode_printer.php?shortcode="+k+"&tubepress_"+q+"&tubepress_galleryId="+r,m=j+" > *";TubePressAjax.loadAndStyle(o,j,m,"",n)},h=function(k){var j=function(){f(a(this),k)};a("#tubepress_gallery_"+k+" div.pagination a").click(j)},e=function(k,j){if(i.isAjaxPagination(j)){h(j)}};b.bind(c,e)}());var TubePressCompat=(function(){var a=jQuery,b=function(){a.getScript=function(d,e,c){a.ajax({type:"GET",url:d,success:e,dataType:"script",cache:c})}};return{init:b}}());var TubePressPlayerApi=(function(){var D=jQuery,g=D(document),u=TubePressEvents,x="undefined",z=TubePressLogger,K=false,b="tubepress-youtube-player-",F={},e=/[a-z0-9\-_]{11}/i,q=false,c="tubepress-vimeo-player-",H={},y=/[0-9]+/,p=function(M){return e.test(M)},t=function(M){return y.test(M)},J=function(M,N){if(z.on()){z.log("Firing "+M+" for "+N)}g.trigger(M,N)},A=function(M){J(u.PLAYBACK_STARTED,M)},j=function(M){J(u.PLAYBACK_STOPPED,M)},a=function(M){J(u.PLAYBACK_BUFFERING,M)},G=function(M){J(u.PLAYBACK_PAUSED,M)},B=function(M){J(u.PLAYBACK_ERROR,M)},L=function(Q){var R=Q.target.a.id,M=R.replace(b,""),P=F[M],O=P.getVideoUrl(),S=O.split("v=")[1],N=S.indexOf("&");if(N!=-1){S=S.substring(0,N)}return S},i=function(M){return M.replace(c,"")},f=function(P,O,M){if(O()===true){P();return}var N=function(){f(P,O,M)};setTimeout(N,M)},s=function(){return typeof YT!==x&&typeof YT.Player!==x},w=function(){return typeof Froogaloop!==x},v=function(){if(!K&&!s()){if(z.on()){z.log("Loading YT API")}K=true;D.getScript("http://www.youtube.com/player_api")}},o=function(){if(!q&&!w()){if(z.on()){z.log("Loading Vimeo API")}q=true;D.getScript("http://a.vimeocdn.com/js/froogaloop2.min.js")}},k=function(N){var P=L(N),M=N.data,O=YT.PlayerState;if(P===null){return}switch(M){case O.PLAYING:A(P);break;case O.PAUSED:G(P);break;case O.ENDED:j(P);break;case O.BUFFERING:a(P);break;default:if(z.on()){z.log("Unknown YT event");z.dir(N)}break}},l=function(M){var N=L(M);if(N===null){return}if(z.on()){z.log("YT error");z.dir(M)}B(N)},C=function(M){var N=i(M);A(N)},h=function(M){var N=i(M);G(N)},I=function(M){var N=i(M);j(N)},m=function(M){var N=H[M];N.addEvent("play",C);N.addEvent("pause",h);N.addEvent("finish",I)},n=function(M){v();var N=function(){if(z.on()){z.log("Register YT video "+M+" with TubePress")}F[M]=new YT.Player(b+M,{events:{onError:l,onStateChange:k}})};f(N,s,300)},E=function(O){o();var M=c+O,N=document.getElementById(M),P=function(){var Q;if(z.on()){z.log("Register Vimeo video "+O+" with TubePress")}Q=new Froogaloop(N);H[M]=Q;Q.addEvent("ready",m)};f(P,w,800)},r=function(M){if(p(M)){n(M)}else{if(t(M)){E(M)}}J(u.EMBEDDED_LOAD,M)},d=function(M){g.ready(function(){try{r(M)}catch(N){z.log("Error when registering: "+N)}})};return{register:d,isYouTubeVideoId:p,isVimeoVideoId:t,onYouTubeStateChange:k,onYouTubeError:l,onVimeoPlay:C,onVimeoPause:h,onVimeoFinish:I,onVimeoReady:m}}());var TubePressAjaxSearch=(function(){var a=function(c,f,m,l){var d=jQuery,j=TubePressLogger,i,g,k,b="#tubepress_gallery_"+l,e=d(b).length>0,h=m&&m!==""&&d(m).length>0;if(e){k=TubePressThumbs.getThumbAreaSelector(l);g=k+" > *";i=function(){d(document).trigger(TubePressEvents.NEW_THUMBS_LOADED,l)}}else{if(h){k=m}else{if(j.on()){j.log("Bad target selector and missing gallery")}return}}if(j.on()){j.log("Final dest: "+k);j.log("Ajax selector: "+g)}TubePressAjax.loadAndStyle(getTubePressBaseUrl()+"/sys/scripts/ajax/shortcode_printer.php?shortcode="+c+"&tubepress_search="+f,k,g,null,i)};return{performSearch:a}}());var TubePressDepCheck=(function(){var a=function(){var b=jQuery.fn.jquery,c=window.console;if(/1\.6|7|8|9\.[0-9]+/.test(b)===false){if(typeof c!=="undefined"){c.log("TubePress requires jQuery 1.6 or higher. This page is running version "+b)}}};return{init:a}}());var tubePressBoot=function(){TubePressCompat.init();TubePressDepCheck.init()};if(!jQuery.browser.msie){var oldReady=jQuery.ready;jQuery.ready=function(){try{oldReady.apply(this,arguments)}catch(a){if(typeof console!=="undefined"){console.log("Caught exception when booting TubePress: "+a)}}tubePressBoot()}}else{jQuery(document).ready(function(){tubePressBoot()})};