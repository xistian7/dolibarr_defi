<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/basic_lib/intervencio.php';
$intervencioClass = new Intervencio($db);
$intervencioid = $_POST['intervencioid'];
$intervencio = $intervencioClass->getIntervencioById($intervencioid);
$response = '<b>REF:</b> '.$intervencio["ref"].'<br/>';
$response .= '<b>Descripció:</b>'.$intervencio["description"].'<br/><br/>';
$response .= '<table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">';
    $response .= '<tbody>';
        $response .= '<tr class="liste_titre"><td class=" col-md-3 col-md-first">Producto/Servicio</td>';
            $response .= '<td class=" col-md-1 col-md-first" style="text-align:center;">Cantidad</td>';
            $response .= '<td class=" col-md-1 col-md-first" style="text-align:center;">Desc. (%)</td>';
            $response .= '<td class=" col-md-5 col-md-first">Descripció</td>';
            $response .= '<td class=" col-md-2 col-md-first" style="text-align:center;">Data</td>';
        $response .= '</tr>';
        $linies = $intervencioClass->getLiniesIntervencioById($intervencioid);
        if($linies != NULL){
            foreach ($linies as $linea) {
                $response .= '<tr class="oddeven">';
                    $response .= '<td class=" col-md-3 col-md-first">'.$linea['label'].'</td>';
                    $response .= '<td class=" col-md-1 col-md-first" style="text-align:center;">'.round($linea['cantidad2'],2).'</td>';
                    $response .= '<td class=" col-md-1 col-md-first" style="text-align:center;">'.round($linea['descuento2'],2).'</td>';
                    $response .= '<td class=" col-md-5 col-md-first"><a name="10"></a>'.$linea['description'].'</td>';
                    $response .= '<td class="col-md-2 col-md-first" style="text-align:center;">'.date("d-m-Y", strtotime($linea['date'])).'</td>';
                $response .= '</tr>';
            }
        }
        
            
    $response .= '</tbody>';
$response .= '</table>';

echo $response;
exit;

