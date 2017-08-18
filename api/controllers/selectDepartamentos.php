<?php 
		
	$data = json_decode(file_get_contents("php://input")); 
	
	if (isset($data->metodo)) {
		require_once '../class/consultasGenerales.php';
		$consulta = new ConsultasGenerales;
		$consulta->departamentos();

	}
