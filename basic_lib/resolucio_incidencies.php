<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ResolucioInicidencies
{
    public $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /*VASA funció per afegir el preu a les linies de serveis o productes*/
    public function actualitzarLiniaFacturaEnIncidencia($facturaId){
        //var_dump($idFactura); die();
        $sql = 'UPDATE llx_facturedet AS FACTDET ';
        $sql = $sql.'LEFT JOIN llx_facture AS FACT ON FACTDET.fk_facture = FACT.rowid ';
        $sql = $sql.'LEFT JOIN llx_element_element AS ELEMELEM ON ELEMELEM.fk_target = FACTDET.fk_facture ';
        $sql = $sql.'LEFT JOIN llx_fichinterdet AS FICHDET ON FICHDET.fk_fichinter = ELEMELEM.fk_source AND FACTDET.description = FICHDET.description ';
        $sql = $sql.'LEFT JOIN llx_fichinterdet_extrafields AS FICHDETEXTRA ON FICHDETEXTRA.fk_object = FICHDET.rowid ';
        $sql = $sql.'LEFT JOIN llx_product AS PROD ON PROD.rowid = FICHDETEXTRA.servicios2 ';
        $sql = $sql.'SET FACTDET.tva_tx = PROD.tva_tx ,FACTDET.qty = FICHDETEXTRA.cantidad2, FACTDET.subprice = PROD.price, FACTDET.product_type = PROD.fk_product_type, FACTDET.fk_product = PROD.rowid ';
        $sql = $sql.'WHERE FACTDET.fk_facture = '.$facturaId.';';
        //var_dump($sql); die();
        $resql = $this->db->query($sql);
        
        return $facturaId;
    }
    
    /*VASA funció per actualitzar l'estoc despres d'entrar un pedido de compra*/
    public function actualitzarStockPedidoCompra($facturaId){
        //var_dump($idFactura); die();
        /*$sql = 'UPDATE llx_facturedet SET FACDET.qty = FACT.rowid FROM llx_facturedet FACTDET';
        $sql.'LEFT JOIN llx_facture FACT ON FACTDET.fk_facture = FACT.rowid';
        $sql.'WHERE FACDET.fk_facture = '.$facturaId.';';
        $resql = $this->db->query($sql);
        var_dump("funcio OK ".$sql);*/
        //die();
        return $facturaId;
    }
}

?>