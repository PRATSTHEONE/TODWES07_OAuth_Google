<?php
class usuarioModel extends bd
{
    private $conexion;    
    private $datos;    
    
    public function __construct()
    {
        $this->conexion = new PDO('mysql:host=localhost;dbname='.$this->bbdd, $this->username, $this->password);
        
        $this->datos = array();
        $this->datos['consulta'] = '';
        $this->datos['valores'] = array();
        $this->datos['valores']['id'] = '';
        $this->datos['valores']['nombre'] = '';
        $this->datos['valores']['email'] = '';
        $this->datos['valores']['password'] = '';
    }
    
    public function setData($data)
    {      
        if(isset($data['id']))
        {
            $this->datos['valores']['id']=$data['id'];
        }

        if(isset($data['nombre']))
        {
            $this->datos['valores']['nombre']=$data['nombre'];
        }
        
        if(isset($data['email']))
        {
            $this->datos['valores']['email']=$data['email'];
        }
        
        if(isset($data['password']))
        {
            $this->datos['valores']['password']=$data['password'];
        }
    }
    
    public function getData()
    {
        return $this->datos;
    }

    // Función aplicada para el login tradicional
    public function login()
    {
        $consulta = $this->conexion->prepare("SELECT * FROM usuarios WHERE email = :e AND password = :p");
        $consulta->bindParam(':e', $this->datos['valores']['email']);
        $consulta->bindParam(':p', $this->datos['valores']['password']);
        $consulta->execute();
        $this->datos['consulta'] = $consulta->fetch();
        $consulta->closeCursor();
        $consulta = null;
    }    
    // Crea una consulta con un SELECT en el que el id es igual al id       
    public function info()
    {
        $consulta = $this->conexion->prepare("SELECT * FROM usuarios WHERE id = :id");
        $consulta->bindParam(':id', $this->datos['valores']['id']);
        $consulta->execute();
        $this->datos['consulta'] = $consulta->fetch();
        $consulta->closeCursor();
        $consulta = null;
    } 
    
    public function add()
    {   
        $consulta = $this->conexion->prepare("INSERT INTO usuarios (id, nombre, email, password) VALUES (:id, :n, :e, :p)");
        $consulta->bindParam(':id', $this->datos['valores']['id']);
        $consulta->bindParam(':n', $this->datos['valores']['nombre']);
        $consulta->bindParam(':e', $this->datos['valores']['email']);
        $consulta->bindParam(':p', $this->datos['valores']['password']);
        $consulta->execute();
        $this->datos['consulta'] = 'OK';
        $consulta->closeCursor();
        $consulta = null;
        
    }    
}