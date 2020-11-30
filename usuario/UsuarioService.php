<?php
    require_once 'UsuarioBL.php';
    class UsuarioService {
        private $usuarioDTO;
        private $usuarioBL;

        public function __CONSTRUCT() {
            $this->usuarioDTO = new UsuarioDTO();
            $this->usuarioBL = new UsuarioBL();
        }

        public function Read($username, $password) {
            $this->usuarioDTO = $this->usuarioBL->Read($username, $password); // En esta linea se envia $usuario y $contrasena a userBL
            //Aqui se imprime el resultado devuelto por Read
            Echo json_encode($this->usuarioDTO, JSON_PRETTY_PRINT);
        }
        public function Auth($TOKEN)
        {
            $this->usuarioDTO->token = $TOKEN;
            $responce=$this->usuarioBL->AUTH($this->usuarioDTO->token);
            //echo json_encode($responce, JSON_PRETTY_PRINT);
            return $responce;
             
            
        }
    }
    
    $Obj = new UsuarioService();
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            {
                $headers = apache_request_headers();
                echo  $Obj->Auth($headers['Authorization']);
             
            break;

            }
            
        case 'POST':
            {
                // Para verificar que los $_POST contengan texto se utiliza el siguiente if
                //isset comprueba si una variable esta vacia 
                $data= json_decode(file_get_contents('php://input'), true);
                if((isset($data['username']) && !empty($data['username'])) && (isset($data['password']) && !empty($data['password']))) {
                    $Obj->Read($data['username'], $data['password']);
                } else {
                    Echo json_encode("Faltan datos", JSON_PRETTY_PRINT);
                    //En caso de no cumplir la condicion no se realiza ninguna acción.
                }
                break;
            }
        case 'PUT':
            Echo "PUT";
            break;
        case 'DELETE':
            Echo "DELETE";
            break;
        default:
            Echo"OTRO";
            break;
    }
?>