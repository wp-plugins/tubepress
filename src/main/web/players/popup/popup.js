var TubePressPopupPlayer=(function(){var c=TubePressEvents,b="popup",f=jQuery(document),e={},a=function(m,l,i,h,g){var k=(screen.height/2)-(g/2),j=(screen.width/2)-(h/2);e[i+l]=window.open("","","location=0,directories=0,menubar=0,scrollbars=0,status=0,toolbar=0,width="+h+"px,height="+g+"px,top="+k+",left="+j)},d=function(l,o,k,p,h,n,q){var j='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\n<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" /><title>'+o+'</title></head><body style="margin: 0pt; background-color: black;">',g='<script type="text/javascript">var TubePressPlayerApi = window.opener.TubePressPlayerApi;<\/script>',m="</body></html>",i=e[q+n].document;i.write(j+g+k+m);i.close()};f.bind(c.PLAYER_INVOKE+b,a);f.bind(c.PLAYER_POPULATE+b,d)}());