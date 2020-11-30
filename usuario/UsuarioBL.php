<?php

require_once '../Conexion.php';
require_once '../DTO/UsuarioDTO.php';

class UsuarioBL{
    private $conn;

    public function __construct()
    {
        $this-> conn = new conexion();
    } 

    public function create($usuarioDTO)
    {
        $this-> conn->OpenConnection();
        $connsql = $this-> conn->GetConnection();
        $lastInsertId = 0;

        try {
            if($connsql){
                $connsql ->beginTransaction();
                $sqlStatement = $connsql-> prepare(
                    "INSERT INTO username VALUES (
                            DEFAULT, 
                            :username,
                            :password
                            )"
                        );
                $sqlStatement->bindParam(':username', $usuarioDTO->username);
                $sqlStatement->bindParam(':password', $usuarioDTO->password);
                $sqlStatement->execute();

                $lastInsertId = $connsql->lastInsertId();
                $connsql->commit();
            }
        }catch(PDOException $e){
            $connsql -> rollBack();
        }
        return $lastInsertId;
    }



    public function Read($username, $password )
    {
        $this->conn->OpenConnection();
        $connsql = $this->conn->GetConnection();
        $arrayUsuario = new ArrayObject();
        $Auxiliar = 0;
        $SQLQuery = "SELECT * FROM usuario";
        $usuarioDTO = new UsuarioDTO();

        // Este SELECT * FROM utiliza las variables que se reciben al inicio
        $SQLQuery = "SELECT * FROM usuario WHERE username = '{$username}' AND password = '{$password }'";
        

        try{
            if($connsql)
            {
                foreach($connsql->query($SQLQuery) as $row) //se hace instancia al array
                {
                    $usuarioDTO = new UsuarioDTO();
                    $usuarioDTO->id= $row['id'];
                    $usuarioDTO->username=$row['username'];
                    $usuarioDTO->password =$row['password'];
                    $token = bin2hex(openssl_random_pseudo_bytes(15)); // En esta linea se crea el Token
                    $usuarioDTO->token=$token; // Aqui se agrega el token al usuarioDTO
                    $arrayUsuario->append($usuarioDTO); //tomara los datos de las columnas y lo va a mapear o asignar a los objetos DTO

                    $idVerificador = $usuarioDTO->id;
                    // Si arrayUser no esta vacio [Aqui se agrega el Token]
                    if($idVerificador > 0) {
                        $Auxiliar = 1;
                        $connsql->beginTransaction();
                            $sqlStatement = $connsql->prepare(
                                "UPDATE usuario SET
                                token = :token
                                WHERE id = :id"
                            );

                            $sqlStatement->bindParam(':token', $token);
                            $sqlStatement->bindParam(':id', $usuarioDTO->id);
                            $sqlStatement->execute();

                            $connsql->commit();
                        
                    }

                }

                // Si arrayUser si esta vacio [Solo se devuelven los datos que se enviaron]
                if($Auxiliar == 0) {
                    $usuarioDTO = new UsuarioDTO();
                    $usuarioDTO->username = $username;
                    $usuarioDTO->password  = $password ;
                    $arrayUsuario->append($usuarioDTO);
                }
            }

        }catch(PDOException $e){
            
        }
        return $arrayUsuario;

    }


    public function Update($usuarioDTO)
    {
        $this->conn->OpenConnection();
        $connsql = $this->conn->GetConnection();

        try
        {
            if($connsql)
            {
                $connsql->beginTransaction();
                $sqlStatement = $connsql-> prepare(
                    "UPDATE username SET 
                    username =  :username,
                    password =  :password,
                    estado = :estado
                    WHERE id = :id"
                );
                $sqlStatement->bindParam(':id', $usuarioDTO->id);
                $sqlStatement->bindParam(':username', $usuarioDTO->username);
                $sqlStatement->bindParam(':password', $usuarioDTO->password );
                $sqlStatement->bindParam(':estado', $usuarioDTO->estado);
                $sqlStatement->execute();
                $connsql ->commit();

            }
        }catch(PDOException $e){
            $connsql ->rollBack();
        }
    }


    public function Delete($id)
    {
        $this->conn->OpenConnection();
        $connsql = $this->conn->GetConnection();

        try
        {

        if($connsql)
        {
            $connsql->beginTransaction();
            $sqlStatement = $connsql->prepare(
                "DELETE FROM username WHERE id = :id"

            );
            $sqlStatement->bindParam(':id', $id);
            $sqlStatement->execute();
            $connsql ->commit();
        }

        }catch(PDOException $e){
            $connsql->rollBack();
        }
    }

    public function AUTH($token){
        $this->conn->OpenConnection();
            $connsql = $this->conn->GetConnection();
            $sqlQuery = "SELECT * FROM usuario WHERE token = '{$token}'";

            try {
                if($connsql) {
                    $connsql->beginTransaction();
                    $sqlStatment = $connsql->prepare($sqlQuery); 
                                        
                    $sqlStatment->execute();
                            
                    $response = $sqlStatment->fetch(PDO::FETCH_OBJ);
                    $connsql->commit();

                    if($response) {
                        return true;
                    } 
                    
                        
                }
            } catch (PDOException $e) {
                 return false;
            }
            
    }

}

?>