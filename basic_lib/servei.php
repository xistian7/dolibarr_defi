<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class Servei
{
    public $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /*VASA et diu si s0ha facturar el proxi periode de temps*/
    public function pendentDeFacturarLinea($idLineaContracte){
        $estaPendent = TRUE;
        $sql = 'SELECT data_ultima_facturacio FROM `llx_contratdet_extrafields` WHERE fk_object = '.$idLineaContracte;
        $resql = $this->db->query($sql);
        $dataUltimaFacturacio = $resql->fetch_assoc()["data_ultima_facturacio"];
        if($dataUltimaFacturacio != null){
            $sql2 = 'SELECT duration FROM `llx_product` WHERE rowid = ( SELECT fk_product FROM `llx_contratdet` WHERE rowid = '.$idLineaContracte.')';
            $resql2 = $this->db->query($sql2);
            $durada = $resql2->fetch_assoc()["duration"];
                if($durada != NULL){
                    switch (substr($durada, -1, 1)) {
                    case 'm':
                        $anyFact= date('Y',strtotime($dataUltimaFacturacio));
                        $mesFact = date('n',strtotime($dataUltimaFacturacio));
                        
                        $numMesos = substr($durada, 0, 1);
                        $anyComparar= date('Y');
                        $mesComparar = date('n') - $numMesos;
                        if($mesComparar <= 0){
                            $mesComparar = $mesComparar + 12;
                            $anyComparar = $anyComparar - 1;
                        }
                               
                        if(strtotime($anyFact."-".$mesFact."-01") <=  strtotime($anyComparar."-".$mesComparar."-01")){
                            $estaPendent = TRUE;
                        }else{
                            $estaPendent = FALSE;
                        }

                        break;
                    case 'y':
                        $anyFact= date('Y',strtotime($dataUltimaFacturacio));
                        
                        $numAnys = substr($durada, 0, 1);
                        $anyComparar= date('Y') - $numAnys;
                               
                        if(strtotime($anyFact."-01-01") <=  strtotime($anyComparar."-01-01")){
                            $estaPendent = TRUE;
                        }else{
                            $estaPendent = FALSE;
                        }

                        break;

                }
            } 
        }
        return $estaPendent;
    }
    
    /*VASA et diu si s0ha facturar el proxi periode de temps*/
    public function crearIntervencióLineaServei($idLineaContracte, $dataFacturat = NULL){
        if($dataFacturat == null){ $dataFacturat = date('Y-m-d'); }
        //CREAR INTERVENCIÓ
        $sql = 'INSERT INTO `llx_fichinter`('
                . '`fk_soc`, '
                . '`fk_projet`, '
                . '`fk_contrat`, '
                . '`ref`, '
                . '`ref_ext`, '
                . '`entity`, '
                . '`tms`, '
                . '`datec`, '
                . '`date_valid`, '
                . '`datei`, '
                . '`fk_user_author`, '
                . '`fk_user_modif`, '
                . '`fk_user_valid`, '
                . '`fk_statut`, '
                . '`dateo`, '
                . '`datee`, '
                . '`datet`, '
                . '`duree`, '
                . '`description`, '
                . '`note_private`, '
                . '`note_public`, '
                . '`model_pdf`, '
                . '`last_main_doc`, '
                . '`import_key`, '
                . '`extraparams`'
            . ') VALUES ('
                . '(SELECT `fk_soc` FROM `llx_contrat` WHERE `rowid` = (SELECT `fk_contrat` FROM `llx_contratdet` WHERE `rowid` = '.$idLineaContracte.')),'
                . '2,'
                . '0,'
                . '"'.$this->getNextReferencia().'",'
                . 'NULL ,'
                . '1 ,'
                . '"'.date('Y-m-d H:i:s').'",'
                . '"'.date('Y-m-d H:i:s').'",'
                . '"'.date('Y-m-d H:i:s').'",'
                . 'NULL ,'
                . '8,'
                . '8,'
                . '8,'
                . '3,'
                . '"'.date('Y-m-d H:i:s').'",'
                . '"'.date('Y-m-d H:i:s').'",'
                . 'NULL,'
                . '0,'
                . '(SELECT `description` FROM `llx_contratdet` WHERE `rowid` = '.$idLineaContracte.'),'
                . 'NULL,'
                . 'NULL,'
                . '"soleil",'
                . 'NULL,'
                . 'NULL,'
                . 'NULL'
            . ');';
        $resql = $this->db->query($sql);

        //CREAR LINIA DE LINTERVENCIÖ
        
        $sql2 = 'INSERT INTO `llx_fichinterdet`('
                . '`fk_fichinter`, '
                . '`fk_parent_line`, '
                . '`date`, '
                . '`description`, '
                . '`duree`, '
                . '`rang`'
            . ') VALUES ('
                . '(SELECT rowid FROM `llx_fichinter` ORDER BY rowid DESC LIMIT 1),'
                . 'NULL,'
                . '"'.date('Y-m-d H:i:s').'",'
                . '(SELECT `description` FROM `llx_contratdet` WHERE `rowid` = '.$idLineaContracte.'),'
                . '0,'
                . '0'
            . ')';
        $resql2 = $this->db->query($sql2);
        
        //AFEGIR EXTRAFIELS INTERVENCIÓ DET
        $sql3 = 'INSERT INTO `llx_fichinterdet_extrafields`('
                . '`tms`, '
                . '`fk_object`, '
                . '`import_key`, '
                . '`descuento2`, '
                . '`cantidad2`, '
                . '`servicios2`'
            . ') VALUES ('
                . '"'.date('Y-m-d H:i:s').'",'
                . '(SELECT rowid FROM `llx_fichinterdet` ORDER BY rowid DESC LIMIT 1),'
                . 'NULL,'
                . '(SELECT `remise_percent` FROM `llx_contratdet` WHERE `rowid` = '.$idLineaContracte.'),'
                . '(SELECT `qty` FROM `llx_contratdet` WHERE `rowid` = '.$idLineaContracte.'),'
                . '(SELECT `fk_product` FROM `llx_contratdet` WHERE `rowid` = '.$idLineaContracte.')'
            . ')';
        $resql3 = $this->db->query($sql3);
        
        //AFEGIR EXTRAFIELD INTERVENCIÓ
        $sql4 = 'INSERT INTO `llx_fichinter_extrafields`('
                . '`tms`, '
                . '`fk_object`, '
                . '`import_key`, '
                . '`tecnic`'
            . ') VALUES ('
                . '"'.date('Y-m-d H:i:s').'",'
                . '(SELECT rowid FROM `llx_fichinter` ORDER BY rowid DESC LIMIT 1),'
                . 'NULL,'
                . '0'
            . ')';
        $resql4 = $this->db->query($sql4);
        
        
        $this->actulitzarFetxaFacturatContracte($idLineaContracte);
       

        return $resql;
    }
    
    /*VASA recuperar seguent REF intervencio*/
    public function getNextReferencia(){
        $sql = 'SELECT ref FROM `llx_fichinter` ORDER BY rowid DESC LIMIT 1';
        $resql = $this->db->query($sql);
        $ref = $resql->fetch_assoc()["ref"];
        $nextRef =  "FI".date('y').''.date('m')."-".str_pad((intval(substr($ref,-4))+1),4,'0',STR_PAD_LEFT);
        return $nextRef;
    }
    
    /*VASA ACTUALITZAR DATA FACTURACIÓ*/
    public function actulitzarFetxaFacturatContracte($idLineaContracte){
        $sql = 'SELECT data_ultima_facturacio FROM `llx_contratdet_extrafields` WHERE fk_object = '.$idLineaContracte;
        $resql = $this->db->query($sql);
        $datU = $resql->fetch_assoc()["data_ultima_facturacio"];
        if($datU != NULL){
             //ACTUALITZAR DATA FACTURACIÓ
            $sql5 = 'UPDATE llx_contratdet_extrafields SET data_ultima_facturacio = '.$dataFacturat.' WHERE fk_object = '.$idLineaContracte.';';
            
        }else{
            //CREAR DATA FACTURACIÓ
            $sql5 = 'INSERT INTO `llx_contratdet_extrafields`(`tms`, `fk_object`, `import_key`, `data_ultima_facturacio`) VALUES ("'.date('Y-m-d H:i:s').'",'.$idLineaContracte.',NULL,"'.date('Y-m-d').'")';
        }
        $resql5 = $this->db->query($sql5);
        return $resql5;
    }
}
?>

