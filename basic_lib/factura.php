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
    
    /*VASA recaldular totals i linies factura*/
    public function updateFactura($idFactura){
        
        $sql = 'SELECT PROD.*,FICHDET.cantidad2,FICHDET.descuento2 FROM `llx_fichinterdet_extrafields` as FICHDET ';
        $sql = $sql.'LEFT JOIN `llx_product` AS PROD ON FICHDET.servicios2 = PROD.rowid ';
        $sql = $sql. 'WHERE FICHDET.fk_object = '.$idLiniaIntervenció;
        $resql = $this->db->query($sql);
        $idproj = $resql->fetch_assoc();
        return $idproj;
    }
    
    /*VASA eliminar un arxiu factura*/
    public function deleteFileFactura($fileName){
        //Eliminar el fitxer
        //unlink(DOL_DOCUMENT_ROOT.'/documents/facture/FA1912-0001/'.$fileName);
        
        $sql = "UPDATE `llx_facture` SET last_main_doc = NULL WHERE last_main_doc = 'facture/".$fileName."';";
        $resql = $this->db->query($sql);
        
        $porciones = explode("/", $fileName);
        $sql2 = "DELETE FROM `llx_ecm_files` WHERE filename = '".$porciones[1]."';";
        $resql2 = $this->db->query($sql2);
        return $resql;
    }
    
    /*VASA eliminar un arxiu factura*/
    public function createFileFactura($idFactura, $nomPlantilla){
        //Crear el fitxer
        //unlink(DOL_DOCUMENT_ROOT.'/documents/facture/FA1912-0001/'.$fileName);
        
        //$sql = "UPDATE `llx_facture` SET last_main_doc = concat('facture/',ref,'/',ref,'.pdf') WHERE rowid = ".$idFactura.";";
        //$resql = $this->db->query($sql);
        
        //$porciones = explode("/", $fileName);
        /*$sql2 = "INSERT INTO `llx_ecm_files`("
                . "`ref`, "
                . "`label`, "
                . "`share`, "
                . "`entity`, "
                . "`filepath`, "
                . "`filename`, "
                . "`src_object_type`, "
                . "`src_object_id`, "
                . "`fullpath_orig`, "
                . "`description`, "
                . "`keywords`, "
                . "`cover`, "
                . "`position`, "
                . "`gen_or_uploaded`, "
                . "`extraparams`, "
                . "`date_c`, "
                . "`date_m`, "
                . "`fk_user_c`, "
                . "`fk_user_m`, "
                . "`acl`"
            . ") VALUES ("
                . "[value-2],"
                . "[value-3],"
                . "[value-4],"
                . "[value-5],"
                . "[value-6],"
                . "[value-7],"
                . "[value-8],"
                . "[value-9],"
                . "[value-10],"
                . "[value-11],"
                . "[value-12],"
                . "[value-13],"
                . "[value-14],"
                . "[value-15],"
                . "[value-16],"
                . "[value-17],"
                . "[value-18],"
                . "[value-19],"
                . "[value-20],"
                . "[value-21]"
            . ");";*/
        //$resql2 = $this->db->query($sql2);
        var_dump($sql); var_dump($sql2); die();
        return $resql;
    }
}
?>

