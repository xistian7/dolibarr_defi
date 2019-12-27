<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CommonObjectvasa
{
    public $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /*VASA seleccionar un projecte per defecte*/
    public function getIdTecnicAsignatTicket($idTicket){
        $sql = 'SELECT fk_user_assign FROM `llx_ticket` WHERE rowid = '.$idTicket;
        $resql = $this->db->query($sql);
        $idproj = $resql->fetch_assoc()["fk_user_assign"];
        return $idproj;
    }
    
    public function getIdTecnicAsignatIntervencio($idIntervencio){
        $sql = 'SELECT tecnic FROM `llx_fichinter_extrafields` WHERE fk_object = '.$idIntervencio;
        $resql = $this->db->query($sql);
        $idproj = $resql->fetch_assoc()["tecnic"];
        return $idproj;
    }
    
    
}
?>

