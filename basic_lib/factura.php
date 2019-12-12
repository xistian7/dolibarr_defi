<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Factura
{
    public $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /*VASA seleccionar un projecte per defecte*/
    public function getDefaultProject(){
        $sql = 'SELECT rowid FROM `llx_projet` ORDER BY rowid ASC LIMIT 1';
        $resql = $this->db->query($sql);
        $idproj = $resql->fetch_assoc()["rowid"];
        return $idproj;
    }
    /*VASA seleccionar un projecte per defecte*/
    public function getDefaultBankAcount(){
        $sql = 'SELECT rowid FROM `llx_bank_account` ORDER BY rowid ASC LIMIT 1';
        $resql = $this->db->query($sql);
        $idproj = $resql->fetch_assoc()["rowid"];
        return $idproj;
    }
    
    /*VASA seleccionar un projecte per defecte*/
    public function getProducteLiniaFactura($idLiniaIntervenció){
        $sql = 'SELECT PROD.*,FICHDET.cantidad2,FICHDET.descuento2 FROM `llx_fichinterdet_extrafields` as FICHDET ';
        $sql = $sql.'LEFT JOIN `llx_product` AS PROD ON FICHDET.servicios2 = PROD.rowid ';
        $sql = $sql. 'WHERE FICHDET.fk_object = '.$idLiniaIntervenció;
        $resql = $this->db->query($sql);
        $idproj = $resql->fetch_assoc();
        return $idproj;
    }
}
?>

