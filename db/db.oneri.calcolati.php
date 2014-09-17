<?/*
GESTIONE calcolo oneri:
dopo aver inserito il record in oneri_calcolati con la libreria savedata faccio qui il calcolo con i parametri passati e aggiorno la tabella con i valori calcolati

*/

//print_r($_POST);

//se ho annullato esco
if ($_POST["azione"]=="Annulla"){
	$active_form.="?pratica=$idpratica";
	return;
}

//Modulo condiviso per la gestione dei dati
include_once "./db/db.savedata.php";



if ($_POST["azione"]!="Elimina"){
	//Dopo aver salvato i dati passo al calcolo e aggiorno i dati dei campi calcolati
	$sql="select tr,a,ie,k from oneri.e_tariffe where tabella='".$_POST["tabella"]."' and anno=" .$_POST["anno"] ;
	$result = $db->sql_query ($sql);
	if (!$result){
		return;
	}
print_debug($sql);

	$tariffa = $db->sql_fetchrow();
	
	//Recupero l'id di riga del calcolo e le info per il calcolo
	$id=$_POST["id"];
	if(!$id)	$id=$_SESSION["ADD_NEW"];//ho inserito un nuovo calcolo
	$sup=str_replace(",",".",$_POST["sup"]);
	$pratica=$_POST["pratica"];
	$intervento=$_POST["intervento"];
	$perc=$_POST["perc"];
	$C1=$_POST["c1"];
	$C2=$_POST["c2"];
	$C3=$_POST["c3"];
	$C4=$_POST["c4"];
	$D1=$_POST["d1"];
	$D2=$_POST["d2"];
	$n=$_POST["n"];
       $n1=$_POST["n1"];
       $nn=(($n1-$n)/$n);

	$K = $tariffa["k"];
	$A = $tariffa["a"];
	$B = $tariffa["tr"]-$A;
	$IE= $tariffa["ie"];
	$B1=$B*$IE/100;
	$B2=$B-$B1;
	
	//ricalcolo qui A,B1,B2 con la riduzione per edif. degradati
	//per ora utilizzato solo da Ceriale
	if($_POST["degradato"]){
		$A=($A*90)/100;
		$B1=($B1*90)/100;
		$B2=($B2*90)/100;
	}

       ///moltiplico per 2 se in sanatoria (utilizzato da Vezzano)
	if($_POST["sanatoria"]==1){
		$A=$A*2;
		$B1=$B1*2;
		$B2=$B2*2;
	}

	if ($intervento>900){ 
		
		switch ($intervento) {
			case 901:
                            if($n>0 || $n1>0)
                                $perc=9+$n+5*$nn;
                            else $perc=10;
				break;
			case 902:
                            if($n>0 || $n1>0)
				    $perc=13+2*$n+7.5*$nn;
                            else $perc=15;
				break;
			case 903:
                            if($n>0 || $n1>0)
				    $perc=26+4*$n+7.5*$nn;
                            else $perc=30;
				break;
			case 904:
                            if($n>0 || $n1>0)
				    $perc=19+$n+10*$nn;
				break;
			case 905:
                            if($n>0 || $n1>0)
				    $perc=30+5*$n+10*$nn;
				break;
			case 906:
                            if($n>0 || $n1>0)
				    $perc=10+10*$nn;
				break;
			case 907:
                            if($n>0 || $n1>0)
				    $perc=0.125*$n+75*(-1)*$nn;
				break;
			case 908:
                            if($n>0 || $n1>0)
				    $perc=5+2.5*$n1+75*(-1)*$nn;
				break;
		}
		$perc=(($perc>60)?(60):(($perc<10)?(10):($perc)));
		$intervento=$perc;
	}
		
	if ($intervento > 100){ //INTERVENTO DI SISTEMAZIONE CALCOLO SISTEMAZIONE SOLO SU B1
		if($perc==0)
			$perc=$intervento-100;
		$CC = 0;
		$B1 = $K * $perc * ((100 - ($C2 + $C3 + $C4) + $D2) * $B1) / 1000000;
		$B2 = 0;
		$E1 = 0;
		$E2 = 0;
	}
	else{
		if($perc==0)
			$perc=$intervento;
		$CC = $K * $perc * ((100 + $D1) * $A) / 1000000;
		$B1 = $K * $perc * ((100 - ($C2 + $C3 + $C4) + $D2) * $B1) / 1000000;
		$B2 = $K * $perc * ((100 - ($C1 + $C2 + $C3 + $C4) + $D2) * $B2) / 1000000;
	}
	$CC=$CC*$sup;
	$B1=$B1*$sup;
	$B2=$B2*$sup;
	
	$sql="update oneri.calcolati set cc=$CC,b1=$B1,b2=$B2 where id=$id;"; 
	$db->sql_query($sql);
	print_debug($sql);
}
// Dopo aver aggiornato la tabella oneri.calcolati con l'ultimo calcolo devo aggiornare la tabella oneritotali contenente i dati complessivi
// se questo è il primo calcolo devo aggiungere il record 
// se ho eliminato l'ultimo calcolo devo eliminare anche il record da oneri.totali



if ($_POST["azione"]=="Elimina"){
	$sql="delete from oneri.totali where pratica=".$_POST["pratica"];
	if(!$db->sql_query($sql)) print_debug($sql);
}

$sql="SELECT count(*) as num from oneri.totali where pratica=".$_POST["pratica"];
if(!$db->sql_query($sql)) print_debug($sql);
$totali=$db->sql_fetchfield("num");
$sql=(!$totali)?("insert into oneri.totali (pratica,cc,b1,b2,calcolo) select pratica,sum(cc),sum(b1),sum(b2),1 from oneri.calcolati where pratica=".$_POST["pratica"]." group by pratica;"):("update oneri.totali set cc=(select sum(calcolati.cc) from oneri.calcolati where pratica=".$_POST["pratica"]."),b1=(select sum(calcolati.b1) from oneri.calcolati where pratica=".$_POST["pratica"]."),b2=(select sum(calcolati.b2) from oneri.calcolati where pratica=".$_POST["pratica"]."),calcolo=1 where pratica=".$_POST["pratica"]);
if(!$db->sql_query($sql)) print_debug($sql);

$active_form.="?pratica=$idpratica";



?>
