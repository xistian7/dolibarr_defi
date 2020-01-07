<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Ticketvasa
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
    
    /*VASA canviar l'estat d'un ticket per el seu id*/
    public function setStateByID($idTicket){
        $sql = "UPDATE `llx_ticket` SET fk_statut = 8, progress = 100, date_read = '".date('Y-m-d H:i:s')."', date_close = '".date('Y-m-d H:i:s')."' WHERE rowid = ".$idTicket.";";
        $resql = $this->db->query($sql);
        
        return $resql;
    }
    
}
?>

