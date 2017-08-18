<?php

	class session {

		

		function __construct() {

		   // configura las funciones de la sesión personalizada.

		   session_set_save_handler(array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc'));

		 

		   // Esta línea evita efectos inesperados al usar objetos como manipuladores de guardado.

		   register_shutdown_function('session_write_close');

		}

		

		function inicio_sesion($session_name, $secure) {

		   // Ahora podemos iniciar la sesión

		   session_start();

		}

		

		function open() {

		   $host = 'localhost';

		   $user = 'root';

		   $pass = '';

		   $name = 'betstore';

		   $mysqli = new mysqli('p:'.$host, $user, $pass, $name);

		   $this->db = $mysqli;

		   return true;

		}

		

		function close() {

		   $this->db->close();

		   return true;

		}

		

		function read($id) {

		   $id = $this->limpiar($id);

		   if(!isset($this->read_stmt)) {

			  $this->read_stmt = $this->db->prepare("SELECT data FROM sesiones WHERE id = '".$id."' LIMIT 1");

		   }

		   //$this->read_stmt->bind_param('s', $id);

		   $this->read_stmt->execute();

		   $this->read_stmt->store_result();

		   $this->read_stmt->bind_result($data);

		   $this->read_stmt->fetch();

		   $key = $this->getkey($id);

		   $data = $this->decrypt($data, $key);

		   return $data;

		}

		

		function write($id, $data) {	

		   $id = $this->limpiar($id);

		   

		   // Consigue una clave única

		   $key = $this->getkey($id);

		   // Cifra los datos

		   $data = $this->encrypt($data, $key);

		 

		   $time = time();

		   if(!isset($this->w_stmt)) {

			  $this->w_stmt = $this->db->prepare("REPLACE INTO sesiones (id, horario, data, clave_sesion) VALUES ('".$id."','".$time."','".$data."','".$key."')");

		   }

		 

		   //$this->w_stmt->bind_param('siss', $id, $time, $data, $key);

		   $this->w_stmt->execute();

		   return true;

		}

		

		function destroy($id) {

		   $id = $this->limpiar($id);

		   if(!isset($this->delete_stmt)) {

			  $this->delete_stmt = $this->db->prepare("DELETE FROM sesiones WHERE id = '".$id."'");

		   }

		   //$this->delete_stmt->bind_param('s', $id);

		   $this->delete_stmt->execute();

		   return true;

		}

		

		function gc($max) {

  		   $old = time() - $max;

		   if(!isset($this->gc_stmt)) {

			  $this->gc_stmt = $this->db->prepare("DELETE FROM sesiones WHERE horario < '".$old."'");

		   }

		   //$this->gc_stmt->bind_param('s', $old);

		   $this->gc_stmt->execute();

		   return true;

		}

		

		private function getkey($id) {

		   $id = $this->limpiar($id);

		   if(!isset($this->key_stmt)) {

			  $this->key_stmt = $this->db->prepare("SELECT clave_sesion FROM sesiones WHERE id = '".$id."' LIMIT 1");

		   }

		   //$this->key_stmt->bind_param('s', $id);

		   $this->key_stmt->execute();

		   $this->key_stmt->store_result();

		   if($this->key_stmt->num_rows == 1) { 

			  $this->key_stmt->bind_result($key);

			  $this->key_stmt->fetch();

			  return $key;

		   } else {

			  $random_key = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));

			  return $random_key;

		   }

		}

		

		private function encrypt($data, $key) {

		   $salt = 'Er2teVS37HGvWC1pBMrTyxa5ipuGP3bENfETn31ER16mFRG6m5mbbd5MP8f5Friu';

		   $key = substr(hash('sha256', $salt.$key.$salt), 0, 32);

		   $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);

		   $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		   $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB, $iv));

		   return $encrypted;

		}

		private function decrypt($data, $key) {

		   $salt = 'Er2teVS37HGvWC1pBMrTyxa5ipuGP3bENfETn31ER16mFRG6m5mbbd5MP8f5Friu';

		   $key = substr(hash('sha256', $salt.$key.$salt), 0, 32);

		   $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);

		   $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		   $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($data), MCRYPT_MODE_ECB, $iv);

		   return $decrypted;

		}

		

		private function limpiar($texto_depurar)

		{	

			//Variables para conexión a la base de datos

			$bd_servidor = 'localhost';

			$bd_usuario = 'root';

			$bd_clave = '';

			$bd_nombre = 'betstore';

			

			//Abre la conexión a la base de datos

			$c=mysql_pconnect($bd_servidor,$bd_usuario,$bd_clave);

			mysql_select_db($bd_nombre,$c);

		

			$texto_depurar = str_replace("'","",$texto_depurar);

			$texto_depurar = str_replace('"',"",$texto_depurar);

			$texto_depurar = str_replace(">","",$texto_depurar);

			$texto_depurar = str_replace("<","",$texto_depurar);

			$texto_depurar = str_replace("[","",$texto_depurar);

			$texto_depurar = str_replace("]","",$texto_depurar);

			$texto_depurar = str_replace("{","",$texto_depurar);

			$texto_depurar = str_replace("}","",$texto_depurar);

			$texto_depurar = str_replace("´","",$texto_depurar);

			$texto_depurar = str_replace("`","",$texto_depurar);

			$texto_depurar = str_replace("|","",$texto_depurar);

			$texto_depurar = str_replace("¬","",$texto_depurar);

			$texto_depurar = str_replace("°","",$texto_depurar);

			$texto_depurar = str_replace("%","",$texto_depurar);

			$texto_depurar = str_replace("&","",$texto_depurar);

			$texto_depurar = str_replace("¿","",$texto_depurar);

			$texto_depurar = str_replace("~","",$texto_depurar);

			$texto_depurar = str_replace("+","",$texto_depurar);

			$texto_depurar = str_replace("^","",$texto_depurar);

			$texto_retornar = trim(mysql_real_escape_string($texto_depurar)); 

			

			//cierra conexiones de bases de datos

			mysql_close($c);

			$c=null;

			return $texto_retornar;

		}

		

	}

	

?>