<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Servei
{
    public $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /*VASA et diu si s0ha facturar el proxi periode de temps*/
    public function pendentDeFacturarLinea($idLineaContracte){
        $estaPendent = TRUE;
        $sql = 'SELECT data_ultima_facturacio FROM `llx_contratdet_extrafields` WHERE fk_object = '.$idLineaContracte;
        $resql = $this->db->query($sql);
        $dataUltimaFacturacio = $resql->fetch_assoc()["data_ultima_facturacio"];
        if($dataUltimaFacturacio != null){
            $sql2 = 'SELECT duration FROM `llx_product` WHERE rowid = ( SELECT fk_roduct FROM `llx_contratdet` WHERE rowid = '.$idLineaContracte.')';
            $resql2 = $this->db->query($sql2);
            $durada = $resql2->fetch_assoc()["duration"];
                if($durada != NULL){
                    switch (substr($durada, -1, 1)) {
                    case 'd':
                        if(strtotime($dataUltimaFacturacio) <  strtotime("+".substr($durada, 0, 1)." days", strtotime(date("d-m-Y H:i:00",time())))){
                            $estaPendent = TRUE;
                        }else{
                            $estaPendent = FALSE;
                        }

                        break;
                    case 'm':
                        //if(strtotime($dataUltimaFacturacio) <  strtotime("+".substr($durada, 0, 1)." months", strtotime(date("d-m-Y H:i:00",time())))){
                        if(strtotime($dataUltimaFacturacio) <  strtotime(date('Y-m-01'))){
                            $estaPendent = TRUE;
                        }else{
                            $estaPendent = FALSE;
                        }

                        break;
                    case 'y':
                        if(strtotime($dataUltimaFacturacio) <  strtotime("+".substr($durada, 0, 1)." years", strtotime(date("d-m-Y H:i:00",time())))){
                            $estaPendent = TRUE;
                        }else{
                            $estaPendent = FALSE;
                        }

                        break;

                }
            } 
        }
        return $estaPendent;
    }
}
?>

