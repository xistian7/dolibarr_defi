<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/basic_lib/servei.php';
$serveiClass = new Servei($db);
$lineacontracte = $_POST['lineacontracte'];
$lineacontracte = 1;
$response = $serveiClass->crearIntervencióLineaServei($lineacontracte);

exit;

