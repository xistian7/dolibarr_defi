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
}
?>

