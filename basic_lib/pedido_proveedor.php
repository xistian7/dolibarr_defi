<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class PedidoProveedor
{
    public $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /*VASA funció per amarcar pedido proveedor con a facturat*/
    public function marcarPedidoProvFacturat($idPedProv){
        //var_dump($idPedProv); die();
        $sql = 'UPDATE llx_commande_fournisseur SET billed = 1 WHERE llx_commande_fournisseur.rowid = '.$idPedProv.';';
        $resql = $this->db->query($sql);
        //$statement->execute();
        return $resql;
    }
    
    /*VASA funció per amarcar pedido proveedor con a NO facturat*/
    public function marcarPedidoProvNoFacturat($idPedProv){
        $sql = 'UPDATE llx_commande_fournisseur SET billed = 0 WHERE llx_commande_fournisseur.rowid = '.$idPedProv.';';
        $resql = $this->db->query($sql);
        return $resql;
    }
    
    /*VASA seleccionar un projecte per defecte*/
    public function getDefaultProject(){
        $sql = 'SELECT rowid FROM `llx_projet` ORDER BY rowid ASC LIMIT 1';
        $resql = $this->db->query($sql);
        $idproj = $resql->fetch_assoc()["rowid"];
        return $idproj;
    }
}
?>

