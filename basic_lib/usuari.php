<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Usuari
{
    public $db;
    
    public function __construct($db = NULL)
    {
        if($db != NULL){
            $this->db = $db;
        }
        
    }
    
    /*VASA retorna l'email de l'usuari segons el seu id*/
    public function getEmailById($rowid){
        $sql = 'SELECT email FROM `llx_user` WHERE rowid = '.$rowid.' LIMIT 1';
        $resql = $this->db->query($sql);
        $email = $resql->fetch_assoc()["email"];
        return $email;
    }
    
    /*VASA retorna l'email de l'usuari segons el seu id*/
    public function getNumTicketsPendentsLlegirById($rowid){
        $sql = 'SELECT count(*) FROM `llx_ticket` WHERE fk_user_assign = '.$rowid.' AND fk_statut = 0';
        $resql = $this->db->query($sql);
        $num = $resql->fetch_assoc()["count(*)"];
        return $num;
    }
    
}
?>

