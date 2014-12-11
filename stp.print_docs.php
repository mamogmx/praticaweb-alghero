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
<script language="javascript">

var lim = 20;
var docToPrint = [];
function getSearchData(){
    var data = {};
    $.each($('#frmSearch .textbox'),function(key,val){
        var v = $(val).val();
        if (v){
            data[val.id] = v;
        }
    });
    return data;
}

function showResult(data,textStatus,jqXHR){
    if (data["data"].length>0){
        for(i=0;i<data["data"].length;i++){
            var el = data["data"][i];
            var j = (i%4)+1;
            $('#col-'+j).append('<div>\
    <div>' + (i+1) + ') \
        <input type="checkbox" name="pratica" id="pratica-' + el['pratica'] + '" value="' + el['pratica'] + '" checked="checked">\
        Pratica n° ' + el['numero']+ ' del ' + el['data_presentazione'] + '\
    </div> <div id="printed-' + el['pratica'] + '"></div>\
</div>') 
        }
        $('#azione-cerca').hide();
        $('#div-check-all').show();
        $('#azione-stampa').show();
    }
    else{
    
    }
}

function showPrinted(data,textStatus,jqXHR){
    if (data["success"]==1  && data["data"].length>0){
        for(i=0;i<data["data"].length;i++){
            var el = data["data"][i];
            var testo = '<img src="images/word.gif" border=0 >&nbsp;&nbsp;<a target="documenti" href="./openDocument.php?id=' + el['id'] +'&pratica=' + el['pratica'] + '" >' + el['name'] + '</a>';
            $('#printed-' + el['pratica']).html(testo);
        }
        printDoc();
    }
}

function printDoc(){
    var toPrint = docToPrint.splice(0,lim);
    mod = 130;
    if (toPrint.length > 0){
        $.ajax({
            url:'services/xPrint.php',
            data: {action:'print',params:toPrint,model:mod},
            dataType: 'json',
            success:showPrinted,
            error:function(jqXHR,textStatus,errorThrown){},
            type:'POST'
        });
    }
    else{
        
    }
}

function search(obj){
    var msg = ''
    if (!$('#tipo').val()){
        msg += '<p> <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>Selezionare una tipologia di pratica!</p>'
    }
    if (!$('#numero_in').val()){
        msg += '<p> <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>Selezionare una numero di pratica iniziale!</p>'
    }
    if (!$('#numero_fi').val()){
        msg += '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>Selezionare una numero di pratica finale!</p>'
    }
    if (msg){
        $('<div>' + msg + '</div>').dialog({'title':'Attenzione','modal':true,width:500});
        return false;
    }
    else{
        var dataSend = {};
        dataSend['params'] = getSearchData();
        dataSend['action']='search';
        $.ajax({
            url:'services/xPrint.php',
            data: dataSend,
            dataType: 'json',
            success:showResult,
            error:function(jqXHR,textStatus,errorThrown){},
            type:'POST'
        });
    }
    return true;
}

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