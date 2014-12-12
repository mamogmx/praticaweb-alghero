/*Funzione di Errore del servizio*/
function serviceError(jqXHR,textStatus,errorThrown){
    $('<div class="errore"><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>Si è verificato un errore!<br>' + jqXHR.responseText + '</div>').dialog({'title':'Errore','modal':true,width:500});
}

/*Funzione che recupera i parametri della ricerca*/
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

/*Funzione che effettua la ricerca*/
function search(obj){
    var msg = '';
    if (!$('#tipo').val()){
        msg += '<p> <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>Selezionare una tipologia di pratica!</p>';
    }
    if (!$('#numero_in').val()){
        msg += '<p> <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>Selezionare una numero di pratica iniziale!</p>';
    }
    if (!$('#numero_fi').val()){
        msg += '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>Selezionare una numero di pratica finale!</p>';
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
            error:serviceError,
            type:'POST'
        });
    }
    return true;
}

/*Funzione che mostra i risultati della ricerca*/
function showResult(data,textStatus,jqXHR){
    if (data["data"].length>0){
        for(i=0;i<data["data"].length;i++){
            var el = data["data"][i];
            var j = (i%4)+1;
            $('#col-'+j).append('<div>\
    <div>' + (i+1) + ') \
        <input type="checkbox" name="pratica" id="pratica-' + el['pratica'] + '" value="' + el['pratica'] + '" checked="checked">\
        Pratica n° ' + el['numero_pratica']+ ' del ' + el['data_presentazione'] + '\
    </div> <div id="printed-' + el['pratica'] + '"></div>\
</div>');
        }
        $('#azione-cerca').hide();
        $('#div-check-all').show();
        $('#azione-stampa').show();
    }
    else{
    
    }
}

/*Funzione che effettua la stampa dei documenti*/
function printDoc(){
    var toPrint = docToPrint.splice(0,lim);
    mod = 130;
    if (toPrint.length > 0){
        $.ajax({
            url:'services/xPrint.php',
            data: {action:'print',params:toPrint,model:mod},
            dataType: 'json',
            success:showPrinted,
            error:serviceError,
            type:'POST'
        });
    }
    else{
        $('<div class="full-page" style="text-align:center;">Operazione Terminata</div>').dialog({'title':'Messaggio','modal':true,width:500});
    }
}

/*Funzione che mostra i risultati della stampa*/
function showPrinted(data,textStatus,jqXHR){
    if (data["success"]==1  && data["data"].length>0){
        var testo = '';
        for(i=0;i<data["data"].length;i++){
            var el = data["data"][i];
            if (el["success"]==1)
                testo = '<img src="images/word.gif" border=0 >&nbsp;&nbsp;<a target="documenti" href="./openDocument.php?id=' + el['id'] +'&pratica=' + el['pratica'] + '" >' + el['name'] + '</a>';
            else
                testo = 'Il socumenro è stato già creato in data ' + el["data"];
            $('#printed-' + el['pratica']).html(testo);
        }
        printDoc();
    }
}