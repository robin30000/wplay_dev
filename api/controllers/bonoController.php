<?php 

	$data = json_decode(file_get_contents("php://input")); 

switch ($data->metodo) {
	case 'verificaBono':
		require_once '../class/consultasGenerales.php';
		$consulta = new ConsultasGenerales;
		$consulta->verificaBono($data->data);
		break;
	
	default:
		# code...
		break;
}


 ?>