<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Productes
{
    public $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /*VASA actualitza restant l'estoc de la factura*/
    public function updateStock($idFactura){
        
        $sql = 'UPDATE llx_product_stock AS PRODSTOCK ';
        $sql = $sql.'LEFT JOIN llx_facturedet AS FACTDET ON FACTDET.fk_product = PRODSTOCK.fk_product ';
        $sql = $sql.'LEFT JOIN llx_product AS PROD ON PRODSTOCK.fk_product = PROD.rowid ';
        $sql = $sql.'SET PRODSTOCK.reel = (PRODSTOCK.reel - FACTDET.qty) ';
        $sql = $sql.'WHERE FACTDET.fk_facture = '.$idFactura.' AND PROD.fk_product_type = 0;';
        //var_dump($sql); die();
        $resql = $this->db->query($sql);
        
        return $idFactura;
    }
    
    /*VASA actualitza suma l'estoc de la factura*/
    public function sumStock($idFactura){
        
        $sql = 'UPDATE llx_product_stock AS PRODSTOCK ';
        $sql = $sql.'LEFT JOIN llx_facturedet AS FACTDET ON FACTDET.fk_product = PRODSTOCK.fk_product ';
        $sql = $sql.'LEFT JOIN llx_product AS PROD ON PRODSTOCK.fk_product = PROD.rowid ';
        $sql = $sql.'SET PRODSTOCK.reel = (PRODSTOCK.reel + FACTDET.qty) ';
        $sql = $sql.'WHERE FACTDET.fk_facture = '.$idFactura.' AND PROD.fk_product_type = 0;';
        //var_dump($sql); die();
        $resql = $this->db->query($sql);
        
        return $idFactura;
    }

}
?>

