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
        $sql = $sql.'SET FACTDET.description = PROD.label, ';
        $sql = $sql.'FACTDET.fk_product = PROD.rowid, ';
        $sql = $sql.'FACTDET.vat_src_code = PROD.default_vat_code, '; 
        $sql = $sql.'FACTDET.tva_tx = PROD.tva_tx, ';
        $sql = $sql.'FACTDET.qty = FICHDETEXTRA.cantidad2, '; 
        $sql = $sql.'FACTDET.remise_percent = FICHDETEXTRA.descuento2, ';
        $sql = $sql.'FACTDET.subprice = PROD.price, ';
        $sql = $sql.'FACTDET.total_ht = (PROD.price*FICHDETEXTRA.cantidad2*(100-FICHDETEXTRA.descuento2)/100), ';
        $sql = $sql.'FACTDET.total_tva = (PROD.price*FICHDETEXTRA.cantidad2*(100-FICHDETEXTRA.descuento2)/100)*(PROD.tva_tx/100), ';
        $sql = $sql.'FACTDET.total_ttc = (PROD.price*FICHDETEXTRA.cantidad2*(100-FICHDETEXTRA.descuento2)/100)+((PROD.price*FICHDETEXTRA.cantidad2*(100-FICHDETEXTRA.descuento2)/100)*(PROD.tva_tx/100)), ';
        $sql = $sql.'FACTDET.buy_price_ht = PROD.pmp, ';
        $sql = $sql.'FACTDET.rang = 1, '; // VASA no se que es el rang
        $sql = $sql.'FACTDET.product_type = PROD.fk_product_type, '; 
        $sql = $sql.'FACTDET.multicurrency_subprice = PROD.price, ';
        $sql = $sql.'FACTDET.multicurrency_total_ht = (PROD.price*FICHDETEXTRA.cantidad2*(100-FICHDETEXTRA.descuento2)/100), ';
        $sql = $sql.'FACTDET.multicurrency_total_tva = (PROD.price*FICHDETEXTRA.cantidad2*(100-FICHDETEXTRA.descuento2)/100)*(PROD.tva_tx/100), ';
        $sql = $sql.'FACTDET.multicurrency_total_ttc = (PROD.price*FICHDETEXTRA.cantidad2*(100-FICHDETEXTRA.descuento2)/100)+((PROD.price*FICHDETEXTRA.cantidad2*(100-FICHDETEXTRA.descuento2)/100)*(PROD.tva_tx/100))';
        $sql = $sql.'WHERE FACTDET.fk_facture = '.$facturaId.';';
        //var_dump($sql); die();
        $resql = $this->db->query($sql);
        $resqlupdate = $this->actualitzarTotalsFactura($facturaId);
        return $facturaId;
    }
    
    /*VASA funció per afegir el preu a les linies de serveis o productes*/
    public function actualitzarTotalsFactura($facturaId){
        //var_dump($facturaId); die();
        $sql = 'UPDATE llx_facture AS FACT ';
        $sql = $sql.'SET FACT.tva = (SELECT SUM(total_tva) FROM llx_facturedet WHERE llx_facturedet.fk_facture = '.$facturaId.'), ';
        $sql = $sql.'FACT.total = (SELECT SUM(total_ht) FROM llx_facturedet WHERE llx_facturedet.fk_facture = '.$facturaId.'), ';
        $sql = $sql.'FACT.total_ttc = (SELECT SUM(total_ttc) FROM llx_facturedet WHERE llx_facturedet.fk_facture = '.$facturaId.'), ';
        $sql = $sql.'FACT.multicurrency_total_ht = (SELECT SUM(total_tva) FROM llx_facturedet WHERE llx_facturedet.fk_facture = '.$facturaId.'), ';
        $sql = $sql.'FACT.multicurrency_total_tva = (SELECT SUM(total_ht) FROM llx_facturedet WHERE llx_facturedet.fk_facture = '.$facturaId.'), ';
        $sql = $sql.'FACT.multicurrency_total_ttc = (SELECT SUM(total_ttc) FROM llx_facturedet WHERE llx_facturedet.fk_facture = '.$facturaId.') ';
        $sql = $sql.'WHERE FACT.rowid = '.$facturaId.';';
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