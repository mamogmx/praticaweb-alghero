<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of new
 *
 * @author marco
 */

require_once APPS_DIR."plugins/PHPWord.php";
class wordDoc {
    var $db;
    var $modello;
    var $pratica;
    var $viste;
    var $funzioni;
    var $data;
    var $schema='stp';
    var $modelliDir;
    
    function __construct($modello,$pratica){
        $this->db=appUtils::getDb();
        $db=$this->db;
        $this->modello=$modello;
        $this->pratica=$pratica;
        $sql="SELECT * FROM stp.e_modelli WHERE id=?";
        $ris=$db->fetchAssoc($sql,Array($modello));
        
        $this->modello=$ris["nome"];
        $this->viste=explode(',',$ris["views"]);
        $this->funzioni=explode(',',$ris["functions"]);
        $this->modelliDir=DATA_DIR.DIRECTORY_SEPARATOR."praticaweb".DIRECTORY_SEPARATOR."modelli".DIRECTORY_SEPARATOR;
        $info=pathinfo($this->modello);
        $this->basename=$info["filename"];
        $this->extension=$info["extension"];
        $this->docName=  str_pad(rand(0,999999), 6,'0',STR_PAD_LEFT)."-".$this->modello;
        $this->actions=$ris["action"];
    }
    private function getData(){
        $db=$this->db;
        for($i=0;$i<count($this->viste);$i++){
            $vista=$this->viste[$i];
            $sql="SELECT * FROM ".$this->schema.".$vista WHERE pratica=?";
            $ris=$db->fetchAssoc($sql,Array($this->pratica));
            foreach($ris as $key=>$val){
                $this->data[$vista][$key]=$val;
            }
        }
        for($i=0;$i<count($this->funzioni);$i++){
            $funzione=$this->funzioni[$i];
            $sql="SELECT * FROM ".$this->schema.".$funzione(?);";
            $ris=$db->fetchAssoc($sql,Array($this->pratica));
            foreach($ris as $key=>$val){
                $this->data[$funzione][$key]=$val;
            }
        }
    }
    function createDoc($name=null){
        $PHPWord = new PHPWord();
        //return;
        $this->getData();
        $template = $PHPWord->loadTemplate($this->modelliDir.$this->modello);
        foreach($this->data as $tb=>$data){
            foreach($data as $col=>$val){
                try{
                    $val=(mb_detect_encoding($val)=='UTF-8')?(utf8_decode($val)):($val);
                    $template->setValue("$tb.$col", $val);
                }
                catch(Exception $e){
                    echo "<p>$tb.$col</p>";
                }
            }
        }
        $template->setValue("data", date("d/m/Y"));
        $pr=new pratica($this->pratica);
        $file=($name)?($pr->documenti.$name):($pr->documenti.$this->docName);
        $template->save($file);
    }
}
?>
