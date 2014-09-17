<?
include_once("login.php");
include_once "./lib/tabella_v.class.php";
include_once "./lib/tabella_h.class.php";
$titolo=$_SESSION["TITOLO_$idpratica"];
$modo=(isset($_REQUEST["mode"]))?($_REQUEST["mode"]):('view');

$tabpath="oneri";
if (in_array($_POST["azione"],Array("Salva","Annulla","Elimina"))) {
	error_reporting(E_ALL);
	include("./db/db.oneri.tariffe.php");
}    
$id=($_REQUEST["id"])?(($_REQUEST["id"])):($_SESSION["ADD_NEW"]);

?>
<html>
<head>
<title>Parametri Oneri</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<SCRIPT language="javascript" src="js/LoadLibs.js" type="text/javascript"></SCRIPT>
<script LANGUAGE="JavaScript">
function confirmSubmit()
{
var msg='Sicuro di voler eliminare definitivamente la rata corrente?';
var agree=confirm(msg);
if (agree)
	return true ;
else
	return false ;
}
</script>
</head>
<body  background="">
<?
include "./inc/inc.page_header.php";
if (in_array($modo,Array("edit","new"))){
		

		$tabella=new tabella_v("$tabpath/tariffe",$modo);?>	
		
		<!-- <<<<<<<<<<<<<<<<<<<<<   MODALITA' FORM IN EDITING  >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>--->
		<FORM id="tariffe_alghero" name="tariffe_alghero" method="post" action="oneri.tariffe.php">
		<TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="99%" align="center">  
		  <tr> 
			<td> 
			<!-- intestazione-->
				<H2 class="blueBanner">Tariffe Oneri</H2>
			<!-- fine intestazione-->
			</td>
		  </tr>
		  <tr> 
			<td> 
				<!-- contenuto-->
			<?if($Errors){
					$tabella->set_errors($Errors);
					$tabella->set_dati($_POST);
				}
				elseif ($modo=="edit"){	
					$tabella->set_dati("id=$id");
				}
				$tabella->edita();?>				
				<!-- fine contenuto-->
			</td>
		  </tr> 
		</TABLE>
		<table>
		<input name="mode" type="hidden" value="<?=$modo?>">
		<input name="active_form" type="hidden" value="oneri.tariffe.php">
				
		</FORM>			

<?}
elseif($modo=="view"){?>
    <TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="99%" align="center">
		<TR>
			<TD>
			<?
				unset($_SESSION["ADD_NEW"]);
                $tabella=new tabella_v("$tabpath/tariffe",$modo);
				$tabella->set_titolo("Tariffe Oneri",'modifica',Array("mode"=>"edit"));
				$tabella->set_dati("id=$id");
				
				$tabella->elenco();?>
				
			</TD>
			
		</TR>
	</TABLE>
<?php   
}
else{
		//-- <<<<<<<<<<<<<<<<<<<<<   MODALITA' FORM IN VISTA DATI  >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>--->
?>
		<H2 class="blueBanner">Elenco Tariffe Oneri</H2>
		<TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="100%">
		  <TR> 
			<TD> 
			<!-- contenuto-->
				<?php
				unset($_SESSION["ADD_NEW"]);
				$tabella=new tabella_h("$tabpath/tariffe","list");
				$nrec=$tabella->set_dati("true");
				$tabella->set_titolo("Tariffe Oneri","nuovo",array("id"=>""));
				$tabella->get_titolo();
                $tabella->elenco();
				
	
					?>
			<!-- fine contenuto-->
			 </TD>
	      </TR>
		</TABLE>
        <button id="btn_close" />
        <script>
            
        
       
            
           $('#btn_close').button({
              icons:{
                 primary:'ui-icon-circle-close '
              },
              label:'Chiudi'
           }).click(function(){
              window.opener.focus();
              window.close();
           });
        </script>
<?}?>

</body>
</html>