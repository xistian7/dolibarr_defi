<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if(isset($_POST['action']) && $_POST['action'] == "deleteLinePedido"){
    PedidoProveedor::deleteLinePedido($_POST['idLineaRebut']);
}

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
    
    /*VASA seleccionar un projecte per defecte*/
    public static function deleteLinePedido($idLinea){
        require_once '../master.inc.php';
        //var_dump($db); die();
        $db = new mysqli($conf->db->host, $conf->db->user, $conf->db->pass, $conf->db->name, $conf->db->port);
        $sql = 'UPDATE llx_product_stock SET reel = reel-(SELECT qty FROM `llx_commande_fournisseur_dispatch` WHERE rowid = '.$idLinea.') WHERE fk_product = (SELECT fk_product FROM `llx_commande_fournisseur_dispatch` WHERE rowid = '.$idLinea.');';
        $resql = $db->query($sql);
        $sql2 = 'DELETE FROM `llx_commande_fournisseur_dispatch` WHERE rowid = '.$idLinea;
        $resql2 = $db->query($sql2);
        return $idLinea;
    }
}
?>

