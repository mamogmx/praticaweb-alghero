<?php
include_once("login.php");
require_once APPS_DIR."/lib/tabella_v.class.php";
require_once APPS_DIR."/lib/tabella_h.class.php";
$tabpath="stp";



?>
<html>
<head>
<title>Report</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<SCRIPT language="javascript" src="js/LoadLibs.js" type="text/javascript"></SCRIPT>
<SCRIPT language="javascript" src="js/massive.print.js" type="text/javascript"></SCRIPT>
<script language="javascript">


var lim = 20;
var docToPrint = [];

$(document).ready(function(){
    $('#azione-stampa').hide();
    
    $('#azione-cerca').button({
        icons: {
            primary: "ui-icon-search"
        },
        label:"Cerca"
    }).bind('click',function(event){
        event.preventDefault();  
        search(this);
    });
    
    $('#azione-stampa').button({
        icons: {
            primary: "ui-icon-print"
        },
        label:"Stampa"
    }).bind('click',function(event){
        event.preventDefault();
        docToPrint = [];
        $.each($('input[name="pratica"]'),function(k,v){
            if ($(v).is(':checked')) docToPrint.push($(v).val())
        });
        printDoc();
    });
    
    $('#check-all').bind('change',function(event){
        event.preventDefault();
        if($(this).is(':checked')){
            $.each($('input[name="pratica"]'),function(k,v){
                $(v).prop('checked',true);
            });
            
            $('#text-check-all').text('Deseleziona Tutti');
        }
        else{
            $.each($('input[name="pratica"]'),function(k,v){
                $(v).prop('checked',false);
            });
            $('#text-check-all').text('Seleziona tutti');
        }
    });
});
</script>
<style>
    .full-page{
        width:100%;
    }
    #risultati th{
        font-size:13px;
        font-weight:bold;
    }
    .risultati{
        color: #000066;
        font-family: Verdana,Geneva,Arial,sans-serif;
        font-size: 11px;
    }
</style>
</head>
<body>
<?php
include "./inc/inc.page_header.php";
?>
    <h2 class="blueBanner">Parametri di Ricerca</h2>
<?php
    
    $tabella=new tabella_v("$tabpath/print_docs.tab",'search');
    echo "<form id=\"frmSearch\" method=\"POST\">";
    $tabella->edita();
    $table = <<<EOT
            
<div style="padding-top:20px;padding-bottom:20px;display:none;" id="div-check-all">
    <span class="risultati"><input id="check-all" type="checkbox" checked="checked"><span id="text-check-all">Deseleziona Tutti</span></span>
</div>            
   <table class="full-page">
        <tr>
            <td class="risultati" id = "col-1"></td>
            <td class="risultati" id = "col-2"></td>
            <td class="risultati" id = "col-3"></td>
            <td class="risultati" id = "col-4"></td>
        </tr>
   </table>
EOT;
    echo $table;
    echo "</form>";
    
?>
</body>
</html>