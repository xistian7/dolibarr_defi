<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Intervencio
{
    public $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /*VASA retorna la intervenció*/
    public function getIntervencioById($id){
        $sql = 'SELECT * FROM `llx_fichinter` ';
        $sql = $sql.' WHERE rowid = '.$id;
        $resql = $this->db->query($sql);
        return $resql->fetch_assoc();
    }
    
    /*VASA asegurarnos que mai hi ha un descompte a NULL*/
    public function setDescuentoMin0(){
        $sql = "UPDATE `llx_fichinterdet_extrafields` SET descuento2 = 0 WHERE descuento2 IS NULL;";
        $resql = $this->db->query($sql);
        return $resql;
        
    }
    
    /*VASA retorna les linies de lintervencio en una array (nom,quantitat,descompte,descripcio,data)*/
    public function getLiniesIntervencioById($id){
        $sql = 'SELECT PROD.label, FICHEXTRA.cantidad2 ,FICHEXTRA.descuento2 ,FICHDET.description, FICHDET.date FROM `llx_fichinterdet` AS FICHDET ';
        $sql = $sql.'LEFT JOIN `llx_fichinterdet_extrafields` AS FICHEXTRA ON FICHDET.rowid = FICHEXTRA.fk_object ';
        $sql = $sql.'LEFT JOIN `llx_product` AS PROD ON FICHEXTRA.servicios2 = PROD.rowid ';
        $sql = $sql.' WHERE FICHDET.fk_fichinter = '.$id;
        $resql = $this->db->query($sql);
        $i=0;
        $linies = NULL;
        while ($fila = $resql->fetch_assoc()) {
            $linies[$i] = $fila;
            $i++;
        }
        return $linies;
    }
    
    /*VASA seleccionar un projecte per defecte*/
    public function getDefaultProject(){
        $sql = 'SELECT rowid FROM `llx_projet` ORDER BY rowid ASC LIMIT 1';
        $resql = $this->db->query($sql);
        $idproj = $resql->fetch_assoc()["rowid"];
        return $idproj;
    }
    
    /*VASA recuperar nom del producte o servei d'una línea*/
    public function getProducoteOServeiLinia($idLinia){
        $sql = 'SELECT PROD.label FROM `llx_fichinterdet_extrafields` as FICHDET ';
        $sql = $sql.'LEFT JOIN `llx_product` AS PROD ON FICHDET.servicios2 = PROD.rowid ';
        $sql = $sql. 'WHERE FICHDET.fk_object = '.$idLinia;
        $resql = $this->db->query($sql);
        $idproj = $resql->fetch_assoc()["label"];
        return $idproj;
    }
    
    /*VASA recuperar quantitat d'una línea*/
    public function getCantidadLinia($idLinia){
        
        $sql = 'SELECT cantidad2 FROM `llx_fichinterdet_extrafields` WHERE fk_object = '.$idLinia;
        $resql = $this->db->query($sql);
        $idproj = $resql->fetch_assoc()["cantidad2"];
        return $idproj;
    }
    
    /*VASA recuperar descopte d'una línea*/
    public function getDescompteLinia($idLinia){
        $sql = 'SELECT descuento2 FROM `llx_fichinterdet_extrafields` WHERE fk_object = '.$idLinia;
        $resql = $this->db->query($sql);
        $idproj = $resql->fetch_assoc()["descuento2"];
        return $idproj;
    }
    
    /*VASA canviar l'estat d'una intervencio per el seu id*/
    public function setStateByID($idIntervencio, $estat = 2){
        $sql = "UPDATE `llx_fichinter` SET fk_statut = '.$estat.' WHERE rowid = ".$idIntervencio.";";
        $resql = $this->db->query($sql);
        return $resql;
    }
    
    /*VASA agregar una intervenció a una factura*/
    public function addIntervencioToFactura($idIntervencio, $idFactura){
        $sql = "INSERT INTO `llx_facturedet` "
        . "(`fk_facture`, `fk_parent_line`, `fk_product`, `label`, `description`, `vat_src_code`, `tva_tx`, `localtax1_tx`, `localtax1_type`, `localtax2_tx`, `localtax2_type`, `qty`, `remise_percent`, `remise`, `fk_remise_except`, `subprice`, `price`, `total_ht`, `total_tva`, `total_localtax1`, `total_localtax2`, `total_ttc`, `product_type`, `date_start`, `date_end`, `info_bits`, `buy_price_ht`, `fk_product_fournisseur_price`, `special_code`, `rang`, `fk_contract_line`, `fk_unit`, `import_key`, `fk_code_ventilation`, `situation_percent`, `fk_prev_id`, `fk_user_author`, `fk_user_modif`, `fk_multicurrency`, `multicurrency_code`, `multicurrency_subprice`, `multicurrency_total_ht`, `multicurrency_total_tva`, `multicurrency_total_ttc`) "
        . "SELECT ".$idFactura.", NULL, PROD.rowid, PROD.label, PROD.label, PROD.default_vat_code, PROD.tva_tx, PROD.localtax1_tx, PROD.localtax1_type, PROD.localtax2_tx, PROD.localtax2_type, FICHINTERDETEXTRA.cantidad2, FICHINTERDETEXTRA.descuento2, 0, NULL, PROD.price, NULL, (PROD.price*FICHINTERDETEXTRA.descuento2/100*FICHINTERDETEXTRA.cantidad2) as preu_tot, (PROD.price*FICHINTERDETEXTRA.descuento2/100*FICHINTERDETEXTRA.cantidad2)*PROD.tva_tx/100 as iva_tot, 0.00000000, 0.00000000, ((PROD.price*FICHINTERDETEXTRA.descuento2/100*FICHINTERDETEXTRA.cantidad2) + ((PROD.price*FICHINTERDETEXTRA.descuento2/100*FICHINTERDETEXTRA.cantidad2))*PROD.tva_tx/100), 0, NULL, NULL, 0, PROD.pmp, NULL, 0, 1, NULL, NULL, NULL, 0, 100, NULL, 8, 8, 1, 'EUR', PROD.price,(PROD.price*FICHINTERDETEXTRA.descuento2/100*FICHINTERDETEXTRA.cantidad2), (PROD.price*FICHINTERDETEXTRA.descuento2/100*FICHINTERDETEXTRA.cantidad2)*PROD.tva_tx/100, ((PROD.price*FICHINTERDETEXTRA.descuento2/100*FICHINTERDETEXTRA.cantidad2) + ((PROD.price*FICHINTERDETEXTRA.descuento2/100*FICHINTERDETEXTRA.cantidad2))*PROD.tva_tx/100) FROM `llx_fichinterdet` AS FICHINTERDET "
        . "LEFT JOIN `llx_fichinterdet_extrafields` AS FICHINTERDETEXTRA ON FICHINTERDETEXTRA.fk_object = FICHINTERDET.rowid "
        . "LEFT JOIN `llx_product` AS PROD ON PROD.rowid = FICHINTERDETEXTRA.servicios2 "
        . "WHERE FICHINTERDET.fk_fichinter = ".$idIntervencio.";";
        $resql1 = $this->db->query($sql);
        $sql2 = " INSERT INTO `llx_element_element` (fk_source, sourcetype, fk_target, targettype) VALUES (".$idIntervencio.", 'fichinter', ".$idFactura.", 'facture');";
        $resql2 = $this->db->query($sql2);
        return $resql1;
    }
    
    /*VASA retorna un llistat d'intervencions relacionades per client i finalitzades*/
    public function getIntervencionsClientFinalitzades($idClient , $dataMaxima = null){
        if($dataMaxima == null){
            $dataMaxima = date('Y-m-d H:i:s');
        }
        $sql = 'SELECT * FROM `llx_fichinter` ';
        $sql = $sql.' WHERE fk_statut = 3 AND fk_soc = '.$idClient.' AND date_valid < \''.$dataMaxima.'\'';
        $resql = $this->db->query($sql);
        $i=0;
        $configuracions = NULL;
        while ($fila = $resql->fetch_assoc()) {
            $configuracions[$i] = $fila;
            $i++;
        }
        return $configuracions;
    }
    /*VASA recuperar descopte d'una línea*/
    public function getNextReferencia(){
        $sql = 'SELECT ref FROM `llx_fichinter` ORDER BY rowid DESC LIMIT 1';
        $resql = $this->db->query($sql);
        $ref = $resql->fetch_assoc()["ref"];
        $nextRef = "FI";
        if(substr($ref, 2,-5) == date('y').''.date('m')){
            $nextRef =  $nextRef.date('y').''.date('m')."-".substr($ref, 5)+1;
        }else{
            $nextRef = $nextRef.date('y').''.date('m')."-0001";
        }
        var_dump(); 
        return $nextRef;
    }
}
?>

