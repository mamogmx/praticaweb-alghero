var baseURL='/gisclient/template/';
var searchUrl='/services/xSearch.php';

function setDatiAutoSuggest(event,ui){
    if (typeof(ui.item.child)!='undefined'){
        $.each(ui.item.child,function(k,v){
            $('#'+k).val(v);  
        });
    }
}

function confirmDelete(obj){
    return confirm('Sei sicuro di voler eliminare il record?')
}
function confirmSpostaVariazioni(obj){
    return confirm('Sei sicuro di voler volturare il record?')
}
function goToView(obj){
    $('#btn_azione').val('Annulla');
    $(obj).parents('form:first').attr('action','praticaweb.php');
    
    $(obj).parents('form:first').submit();
}

function linkToList(url,prms){
    var form='<form method="POST" action="'+url+'" id="submitFrm"></form>';
    $(form).appendTo('body');
    prms['mode']='list';
    $.each(prms,function(k,v){
        
        $('<input type="hidden" name="'+k+'" value="'+v+'">').appendTo($('#submitFrm'));
    });
    $('#submitFrm').submit();
}

function linkToView(url,prms){
    var form='<form action="'+url+'" method="POST" id="submitFrm"></form>';
    $(form).appendTo('body');
    prms['mode']='view';
    $.each(prms,function(k,v){
        $('<input type="hidden" name="'+k+'" value="'+v+'">').appendTo($('#submitFrm'));
    });
    $('#submitFrm').submit();
    //var params='';
    //var tmp=Array();
    // $.each(prms,function(k,v){
    //    tmp.push(k+'='+v);
    //});
    //tmp.push('mode=view');
    //window.location=url+'?'+tmp.join('&');
}
function linkToEdit(url,prms){
    var form='<form action="'+url+'" method="POST" id="submitFrm"></form>';
    prms['mode']='edit';
    if (!window.parent){
        $(form).appendTo('body');
        $.each(prms,function(k,v){
            $('<input type="hidden" name="'+k+'" value="'+v+'">').appendTo($('#submitFrm'));
        });
         $('#submitFrm').submit();
    }
    else{
        $(form).appendTo($('body',window.parent.document));
        $.each(prms,function(k,v){
            $('<input type="hidden" name="'+k+'" value="'+v+'">').appendTo($('#submitFrm',window.parent.document));
        });
        $('#submitFrm',window.parent.document).submit();
    }
        
    //var params='';
    //var tmp=Array();
    //$.each(prms,function(k,v){
    //    tmp.push(k+'='+v);
    //});
    //tmp.push('mode=edit');
    //window.parent.location=url+'?'+tmp.join('&');
}
function goToPratica(url,prms){
    var form='<form action="'+url+'" method="POST" id="submitFrm"></form>';
    prms['mode']='view';
    if (!window.parent){
        $(form).appendTo('body');
        $.each(prms,function(k,v){
            $('<input type="hidden" name="'+k+'" value="'+v+'">').appendTo($('#submitFrm'));
        });
         $('#submitFrm').submit();
    }
    else{
        $(form).appendTo($('body',window.parent.document));
        $.each(prms,function(k,v){
            $('<input type="hidden" name="'+k+'" value="'+v+'">').appendTo($('#submitFrm',window.parent.document));
        });
        $('#submitFrm',window.parent.document).submit();
    }
}

function calcola_cf(){
	var oggetti=Array('cognome','nome','comunato','datanato','sesso');
	var tipo=Array('testo','testo','testo','data','testo');
	var descrizione=Array('il cognome','il nome','il comune di nascita','la data di nascita','il sesso');
	var param={'field':'codfis'};
    var exec=1;
   // param['funz']"funz=codice_fiscale&oggetto=codfis";
    $.each(oggetti,function(i,v){
         
        var val = $('#'+v).val();
        if (val.length==0){
            alert('Inserire '+descrizione[i]);
            exec=0;
            return;
        }
        else
            param[v]=val;
    });
    if (exec==0)
        return;
	$.ajax({
        url     : 'services/xSuggest.php',
        type    : 'POST',
        data    : param,
        dataType:'json',
        success : function(data, textStatus, jqXHR){
            if (data.value){
                $('#codfis').val(data.value);
            }
            else
                alert(data.error);
        }
    });
}

function selectTavola(obj){
    var vincolo=$(obj).val();
    $.ajax({
        url     : 'services/xSuggest.php',
        type    : 'POST',
        data    : {'field':'tavola','term':vincolo},
        dataType:'json',
        success : function(data, textStatus, jqXHR){
            $('#tavola').html('');
            $('#zona').html('');
            for(i=0;i<data.length;i++)
                 $('#tavola').append($('<option>', { value : data[i]['id'] }).text(data[i]['opzione']));
        }
    });
}
function selectZona(obj){
    var tavola=$(obj).val();
    var vincolo=$('#vincolo').val();
    $.ajax({
        url     : 'services/xSuggest.php',
        type    : 'POST',
        data    : {'field':'zona','term':tavola,'vincolo':vincolo},
        dataType:'json',
        success : function(data, textStatus, jqXHR){
            $('#zona').html('');
            for(i=0;i<data.length;i++)
                 $('#zona').append($('<option>', { value : data[i]['id'] }).text(data[i]['opzione']));
        }
    });
}


function closeWindow(obj){
	window.blur();
	(window.open(window.opener.location, window.opener.name) || window).focus();
	window.close();
}
function NewWindow(url, winname, winwidth, winheight, scroll) {
	
	if (!winwidth)
		  winwidth =screen.availWidth-10;
	if (!winheight)
		  winheight = screen.availHeight-35;
	winprops = 'height='+winheight+',width='+winwidth+',scrollbars='+scroll+',menubar=no,top=0,status=yes,left=0,screenX=0,screenY=0,resizable,close=no';
	
	
	win = window.open(url, winname, winprops)
	if (parseInt(navigator.appVersion) >= 4) { 
		win.window.focus(); 
	}
}


  function ApriMappa(mapsetid,template,parameters){
		if(!template) template = this.Template;
		var winWidth = window.screen.availWidth-8;
		var winHeight = window.screen.availHeight-55;
		var winName = 'mapset_'+mapsetid;
		template=template +"/";
		if(!parameters) parameters='';
		if(template.indexOf('?')>0)
			template=template + '&';
		else
			template=template + '?';
		var mywin=window.open(baseURL + template + "mapset=" + mapsetid + "&" + parameters, winName,"width=" + winWidth + ",height=" + winHeight + ",menubar=no,toolbar=no,scrollbar=auto,location=no,resizable=yes,top=0,left=0,status=yes");
		mywin.focus();
  }
  
  function ApriDocumento(url){
	  var mywin=window.open(url,'Documenti');
	  mywin.focus();
  };
  function ApriEditor(id){
	  var mywin=window.open('stp.editor_documenti.php?id_doc='+id,'Editor');
	  mywin.focus();
  }