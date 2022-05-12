<?php
require_once("models/usuarioModel.php");

class usuariosController
{
    public static $titulo = 'Login con Google';

    static function login()
    {
        // Antes de nada, si estoy logueado, redirigimos al perfil directamente    
        if (isset($_SESSION['logueado']) && $_SESSION['logueado'] == 'OK') // Controlamos el acceso
        {
            header("Status: 301 Moved Permanently");
            header("Location: index.php?controller=usuarios&action=perfil");
            exit();
        }

        // Incrustamos el 0Auth para ver si nos autenticamos con Google o no
        require_once("recursos/cliente_0auth.php");

        // Creamos un error vacío para más tarde mostrarlo en el formulario
        $error_login = '';

        // Si se accede a través de Google... (el loguin ya está hecho)
        if (isset($_SESSION['access_token']) && !empty($_SESSION['access_token'])) // Accede por Google 
        {
            // Creamos la URL para aplicarla en el enlace del formulario de la vista (accedemos al callback)                 
            $cliente->setAccessToken($_SESSION['access_token']);
            $servicio = new Google\Service\Oauth2($cliente);
            // Una vez que hemos accedido, obtenemos la información del perfil de Google
            $user_info = $servicio->userinfo->get();

            // En este caso guardamos el ID del perfil de Google
            $data = array();
            $data['id'] = $user_info['id'];

            // Comprobamos si el perfil existe en la base de datos con ese ID
            $modelo = new usuarioModel();
            $modelo->setData($data);
            $modelo->info();
            $datos = $modelo->getData();

            // Si el usuario existe en a base de datos nos logueamos
            if (!empty($datos['consulta'])) // Si existe nos logueamos directamente
            {
                $_SESSION['logueado'] = 'OK';
                $_SESSION['id_user'] = $datos['consulta']['id'];
            } else // Si no existe, lo damos de alta en la base de datos y nos logueamos
            {
                // Creamos la estructura de datos y asignamos la información de Google
                $data = array();
                $data['id'] = $user_info['id'];
                $data['nombre'] = $user_info['name'];
                $data['email'] = $user_info['email'];
                // El password lo hemos creado nosotros para esta prueba. Se debería crear uno aleatorio cada vez
                $data['password'] = md5("1234");

                $modelo->setData($data);        
                $modelo->add(); // Añadimos el usuario a la base de datos
                $datos = $modelo->getData();    

                if ($datos['consulta'] == 'OK') {   // Si se ha añadido correctamente, nos logueamos
                    $_SESSION['logueado'] = 'OK';   // Nos logueamos
                    $_SESSION['id_user'] = $user_info['id'];    // Guardamos el ID del usuario en la sesión
                }
            }

            // El login siempre se ha realizado cuando llegamos aquí porque es Google quien autentifica al usuario.
            // Eliminamos la variable de sesión de Google
            unset($_SESSION['access_token']);

            header("Status: 301 Moved Permanently");
            header("Location: index.php?controller=usuarios&action=perfil");
            exit();
        }
        // Si nos logueamos por el formulario tradicional
        else if (isset($_POST) && !empty($_POST)) // Acceso tradicional
        {
            // Guardamos los datos del formulario en el array. El password codificado en MD5
            $data = array();
            $data['email'] = $_POST['email'];
            $data['password'] = md5($_POST['password']);

            // Hacemos una consulta sobre la base de datos en el modelo
            $modelo = new usuarioModel();
            // Asignamos el email y el password
            $modelo->setData($data);
            // Comprobamos si existe o no en la base de datos
            $modelo->login();
            $datos = $modelo->getData();

            // Si la consulta no está vacía, el usuario está en la base de datos y creamos la sesión logueado junto con su ID a la sesión y redirigimos al perfil
            if (!empty($datos['consulta'])) {
                // Si nos logueamos, creamos esa sesión y le ponemos un "OK" para comprobar al inicio si está logueado o no
                $_SESSION['logueado'] = 'OK';
                // Si tuviésemos que controlar roles, se guardarían en una variable de sesión que se extraería de la base de datos
                $_SESSION['id_user'] = $datos['consulta']['id'];
                // Sesión ficticia para el supuesto administrador
                //$_SESSION['id_user'] = $datos['consulta']['rol'];

                header("Status: 301 Moved Permanently");
                header("Location: index.php?controller=usuarios&action=perfil");
                exit();
            } else // Si la consulta no devuelve nada significa que el usuario o la contraseña no son correctos
            {
                // Si el usuario no ha introducido los datos correctamente, mostramos un error
                $error_login = 'El usuario y la contraseña no son correctos';
            }
        }
        // Se accede de manera tradicional

        $title = self::$titulo;
        // Todo esto lo llevamos a la vista del login       
        require_once("views/usuarios/login.php");
    }

    static function logout()
    {
        if (!isset($_SESSION['logueado'])) // Controlamos el acceso
        {
            header("Status: 301 Moved Permanently");
            header("Location: index.php?controller=usuarios&action=login");
            exit();
        }

        session_unset();
        session_destroy();
        header("Status: 301 Moved Permanently");
        header("Location: index.php");
        exit();
    }

    static function perfil()
    {
        // Antes de nada, si no se ha logueado el usuario, denegamos el acceso y redirigimos al login
        // Habría que crear otro para el administrador si hiciese falta para ese perfil en específico
        if (!isset($_SESSION['logueado'])) // Controlamos el acceso
        {
            header("Status: 301 Moved Permanently");
            header("Location: index.php?controller=usuarios&action=login");
            exit();
        }

        // Si se ha logueado el usuario, creamos un array y guardamos el ID de la variable de sesión del usuario
        $data = array();
        $data['id'] = $_SESSION['id_user'];

        $title = self::$titulo;
        $modelo = new usuarioModel();
        $modelo->setData($data);
        // Extraemos la información y la metemos en la vista de Perfil
        $modelo->info();
        $datos = $modelo->getData();

        require_once("views/usuarios/perfil.php");
    }

    // Función de ejemplo para los administradores
    /*static function admnistracion() {
        // Se pone el control de sesión para ver si se ha logueado el administrador
        if(!isset($_SESSION['logueado']) && $_SESSION['rol'] == 'administrador') // Controlamos el acceso
        {
            header("Status: 301 Moved Permanently");
            header("Location: index.php?controller=usuarios&action=login");
            exit();
        }
    }*/
}
