<?
//print_r($_REQUEST);
include_once("login.php");
include "./lib/tabella_h.class.php";
include "./lib/tabella_v.class.php";
$tabpath="stp";
$tipo=($_REQUEST["tipo"])?($_REQUEST["tipo"]):'xml';
$modo=(isset($_REQUEST["mode"]))?($_REQUEST["mode"]):('view');
$id=(isset($_REQUEST["id"]))?($_REQUEST["id"]):(null);
$file_config="$tabpath/modelli_$tipo";

$db = new sql_db(DB_HOST,DB_USER,DB_PWD,DB_NAME, false);
if(!$db->db_connect_id)  die( "Impossibile connettersi al database");
/*
if ($_POST["azione"]){ 
	$idrow=$_POST["idriga"];
	$sql="SELECT * FROM stp.e_modelli WHERE id=$idrow"; 
	$db->sql_query($sql);
	$nome=$db->sql_fetchfield("nome"); 
	$file=MODELLI_DIR.$nome; 
	@unlink($file); 
	$sql="delete from stp.e_modelli where id=$idrow";
	$db->sql_query($sql);
}
*/
?>
<html>
<head>
    <title>ELENCO MODELLI DI STAMPA</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <SCRIPT language="javascript" src="js/LoadLibs.js" type="text/javascript"></SCRIPT>
</head>
<body>
<?php
 if (in_array($modo,Array("edit","new"))) {
    
	$tabella=new Tabella_v($file_config,$modo);
    //print_array($tabella);
    if(isset($Errors) && $Errors){
        $tabella->set_errors($Errors);
        $tabella->set_dati($_POST);
        $intestazione="Modello ".$_REQUEST["nome"];
    }
    elseif ($modo=="edit"){	
        $tabella->set_dati("id=$id");
        $intestazione="Modello ".$tabella->array_dati[0]["nome"];
    }
    else{
        $intestazione="Nuovo Modello di stampa";
    }
	unset($_SESSION["ADD_NEW"]);	
	include "./inc/inc.page_header.php";
?>
<!-- <<<<<<<<<<<<<<<<<<<<<   MODALITA' FORM IN EDITING  >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>--->
	<FORM id="" name="modelli" method="post" action="praticaweb.php">
		<TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="99%" align="center">		
				  
		  <tr> 
			<td> 
				<H2 class="blueBanner"><?=$intestazione?></H2>
				<?
				
				$tabella->edita();?>			  
			</td>
		  </tr>

		</TABLE>
        <input name="active_form" type="hidden" value="stp.modelli.php">				
        <input name="mode" type="hidden" value="<?=$modo?>">
    </FORM>
<?
}
elseif($modo=="view"){
?>
<!-- <<<<<<<<<<<<<<<<<<<<<   MODALITA' FORM IN VISTA DATI  >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>--->

    <H2 class="blueBanner">Avvio del procedimento e comunicazione responsabile</H2>
    <TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="100%">		
      <TR> 
        <TD> 
        <!-- contenuto-->
            <?
            $pr=new pratica($idpratica);
            $tabella=new tabella_v($file_config,"view");
            $nrec=$tabella->set_dati("id=$id");
            $tabella->set_titolo("Modello ".$tabella->array_dati[0]["nome"],"modifica");
            $tabella->get_titolo();
            $tabella->tabella();
            ?>
        <!-- fine contenuto-->
         </TD>
      </TR>
    </TABLE>
<?
}
else{
    $tabella_modelli=new Tabella_h("$tabpath/modelli_$tipo",'list');
    
    $sql="select distinct opzione,form,stampa from stp.e_form order by stampa;";
    $db->sql_query ($sql);
    $elenco_form = $db->sql_fetchrowset();
?>
    <form method="post" name="modelli" action="">
        <input type="hidden" name="azione" id="azione" value="">
        <input type="hidden" name="idriga" id="idriga" value="0">
    </form>
    <H2 class="blueBanner">Elenco dei modelli</H2><form method="post" name="modelli" action="">
        <TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="100%">		
        
<?	
    foreach ($elenco_form as $row){
		$form=$row["form"];
		$desc=$row["opzione"];

		//Visualizzare solo quelli inerenti il form e opzioni 
		$num_modelli=$tabella_modelli->set_dati("form='$form' and nome ilike '%.$tipo'");
		if ($tipo=='html'){
			$tabella_modelli->set_titolo($desc,"nuovo",array("form"=>$form));
			$upload_butt="";
		}
		else{
			$tabella_modelli->set_titolo($desc);
			//$upload_butt="<table border=\"0\" width=\"90%\"><tr><td style=\"text-align:right\"><input  class=\"hexfield1\" style=\"width:130px;\" type=\"submit\" value=\"Carica Modello\" onclick=\"NewWindow('stp.carica_modello.php?form=$form','documento',600,350);\" ></td></tr></table>";
		}
		$tabella_modelli->set_tag($idpratica);
		
		?>
                <tr> 
                  <td> 
                  <!--  intestazione-->
                      <?$tabella_modelli->get_titolo("stp.editor.php?tipo=modelli");
                          if ($num_modelli) 
                              $tabella_modelli->elenco();
                          else
                              print ("<p><b>Nessun Modello per questo Form</b></p>");

                          print $upload_butt;
                      ?>
                  <!-- fine intestazione-->
                  <br>
                  </td>
                </tr>
<?
    }
?>
                <tr>
                    <td><input class="hexfield1" style="width:100px;margin-top:10px" type="button" value="chiudi" onClick="javascript:window.opener.focus();window.close();"></td>
                </tr>
        </TABLE>
<?php
}
?>
</body>
</html>
