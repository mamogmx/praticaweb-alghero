var libs = ['jquery-1.7.1.min','jquery-ui-1.8.18.custom.min','jquery.ui.datepicker-it','jquery.dataTables.min','dataTables.date.order','window','praticaweb','tinymce/jquery.tinymce'];
var libsSrc = ['window','iframe','x_core','http_request'];
var libcss = ['start/jquery-ui-1.8.16.custom','styles','TableTools','TableTools_JUI','demo_page','demo_table_jui','tabella_v']
var libcssprt = ['styles_print']
//document.write('<meta http-equiv="X-UA-Compatible" content="IE=edge" />');
for (i in libcss) document.write('<LINK media="screen" href="css/'+libcss[i]+'.css" type="text/css" rel="stylesheet"></SCRIPT>');
for (i in libcssprt) document.write('<LINK media="print" href="css/'+libcssprt[i]+'.css" type="text/css" rel="stylesheet"></SCRIPT>');
for (i in libs) document.write('<SCRIPT language="javascript" src="js/'+libs[i]+'.js" type="text/javascript"></SCRIPT>');
//for (i in libsSrc) document.write('<SCRIPT language="javascript" src="/dbmaciste/src/'+libsSrc[i]+'.js" type="text/javascript"></SCRIPT>');
