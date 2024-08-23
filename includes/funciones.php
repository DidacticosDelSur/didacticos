<?php
const PATH = "./html/";

const tag_producto_ampliado = 2;

const tag_index_carousel1 = 2; // "Ofertas de Invierno"; // id 2
const tag_index_carousel2 = 3; //"Productos del año"; // id 3
const tag_index_carousel3 = 4; //'Promo semanal!'; // id 4

include_once "functions/carrito.php";
include_once "functions/pedido.php";
include_once 'functions/pages.php';
include_once 'functions/tags.php';
include_once 'functions/login.php';
include_once 'functions/email.php';
include_once 'functions/catalogs.php';
include_once 'functions/comments.php';
include_once 'functions/whatsapp.php';
include_once 'functions/announcements.php';
include_once 'functions/paginado.php';
include_once 'functions/carousel.php';

function contactSend($t)
{
    if (validateCaptcha($_POST['token'], $_POST['action'])) {
        $t->set_var('base_url', HOST);
        $nombre    = $_POST['nombre'];
        $apellido  = $_POST['apellido'];
        $email     = $_POST['email'];
        $telefono  = $_POST['telefono'];
        $localidad = $_POST['localidad'];
        $comment   = $_POST['comentario'];
        $email_    = $_POST["email"];
        $email     = $_POST["email"];
        if (filter_var($email_, FILTER_VALIDATE_EMAIL)) {
            $to        = "info@didacticosdelsur.com";
            $subject   = "Contacto desde Didácticos del Sur";
            $preferred = 'Didácticos del Sur';
            $message   = 'Se ha rellenado el formulario de contacto:<br><br>' .
                '<h3>Nombre:' . $nombre . ' ' . $apellido . '</h3>' .
                '<h3>Email: ' . $email . '</h3>' .
                '<h3>Teléfono: ' . $telefono . '</h3>' .
                '<h3>Localidad: ' . $localidad . '</h3>' .
                '<p>Comentario: ' . $comment . '</p>' .
                '<hr>';

            sendEmail($to, $subject, $preferred, $message);

            $t->set_var("mensaje", "¡Su mensaje ha sido enviado correctamente!");
            $t->set_var('successClass', 'show');
        } else {
            $t->set_var("error", "Hubo un error al enviar el correo. Por favor, intente nuevamente.");
            $t->set_var('errorClass', 'show');
        }
    } else {
        $t->set_var('error', 'Captcha inválido. Por favor, vuelva a intentarlo.');
        $t->set_var('errorClass', 'show');
    }

    $t->set_file("pl", "contact.html");
}


function login_usuario($db, $t)
{
    $tema = getTematica();
    $t->set_var('base_url', HOST);
    if ($_SESSION["usuario"] == "") {

        if (validateCaptcha($_POST['token'], $_POST['action'])) {
            $email = $_POST['email'];
            error_log(date('h:i:s').' Login_usuario Valido captcha usuario: '.$_POST['email']."\n",3,"./logs/error_".date("Y-m-d ").".log");
   
            $query = "SELECT * FROM clientes WHERE email = '" . $email . "' AND borrado IS NULL LIMIT 1";
            /* ligar parámetros para marcadores */
            $pwrecibida = $_POST["password"];
            $t->set_var("email", '');
            $result = mysqli_query($db, $query);
            $myrow  = mysqli_fetch_array($result);
            if ($myrow != null) {
                error_log(date('h:i:s').' Login_usuario CLIENTE: [email: '.$email."]\n",3,"./logs/error_".date("Y-m-d ").".log");
                if ($myrow['verficado'] == 1) {
                    if ((desencriptar_pass($myrow['password'])) == $pwrecibida) {
                        //save user info in session
                        setLastLoginToNow($myrow['id']);
                        setIP($myrow['id'], getIPAddress());
                        $_SESSION["usuario"]        = true;
                        $_SESSION["nombre_usuario"] = $myrow["nombre"];
                        $_SESSION["email_cliente"]  = $myrow["email"];
                        $_SESSION["id_cliente"]     = $myrow["id"];
                        error_log(date('h:i:s').' Login_usuario verificado y login correcto: [nombre_usuario: '.$_SESSION["nombre_usuario"].', email_cliente: '.$_SESSION["email_cliente"].', id_cliente: '.$_SESSION["id_cliente"]."]\n",3,"./logs/error_".date("Y-m-d ").".log");

                        loadCart($db, $myrow["id"]);

                        header("Location: " . HOST . "home");
                    } else {
                        $t->set_file("pl", "login.html");
                        $t->set_var("email", $email);
                        error_log(date('h:i:s').' Login_usuario verificado y login incorrecto: [email: '.$email."]\n",3,"./logs/error_".date("Y-m-d ").".log");
                        $t->set_var('temeticaClass',$tema);
                        $t->set_var('error', 'El usuario o clave son incorrectos');
                    }
                } else {
                    $t->set_file("pl", "login.html");
                    error_log(date('h:i:s').' Login_usuario NO verificado: [email: '.$email."]\n",3,"./logs/error_".date("Y-m-d ").".log");
                    $t->set_var("email", $email);
                    $t->set_var('temeticaClass',$tema);
                    $t->set_var('error', 'El usuario aún no está verificado');
                }
            }
            else { // we check if it is a seller
                error_log(date('h:i:s').' Login_usuario VENDEDOR: [email: '.$email."]\n",3,"./logs/error_".date("Y-m-d ").".log");
                $query = "SELECT * FROM vendedores WHERE email = '" . $email . "' AND borrado IS NULL LIMIT 1";

                $result = mysqli_query($db, $query);
                $myrow  = mysqli_fetch_array($result);

                if ($myrow != null) {
                    if ((desencriptar_pass($myrow['password'])) == $pwrecibida) {
                        setLastLoginToSellersNow($myrow['id']);
                        $_SESSION["usuario"]        = true;
                        $_SESSION["nombre_usuario"] = $myrow["nombre"];
                        $_SESSION["id_vendedor"]     = $myrow["id"];
                        $_SESSION["nombre_vendedor"]     = $myrow["nombre"];
                        error_log(date('h:i:s').' Login_usuario VENDEDOR y login correcto: [nombre_usuario: '.$_SESSION["nombre_usuario"].', nombre_vendedor: '.$_SESSION["nombre_vendedor"].', id_vendedor: '.$_SESSION["id_vendedor"]."]\n",3,"./logs/error_".date("Y-m-d ").".log");

                        header("Location: " . HOST . "anuncios");

                    } else {
                        $t->set_file("pl", "login.html");
                        $t->set_var('temeticaClass',$tema);
                        $t->set_var("email", $email);
                        error_log(date('h:i:s').' Login_usuario VENDEDOR y login incorrecto: [email: '.$email."]\n",3,"./logs/error_".date("Y-m-d ").".log");
                        $t->set_var('error', 'El usuario o clave son incorrectos');
                    }
                }
                else {
                    $t->set_file("pl", "login.html");
                    $t->set_var('temeticaClass',$tema);
                    $t->set_var("email", $email);
                    error_log(date('h:i:s').' Login_usuario VENDEDOR y login incorrecto (No encontro el email): [email: '.$email."]\n",3,"./logs/error_".date("Y-m-d ").".log");
                    $t->set_var('error', 'El usuario o clave son incorrectos');
                }
            }
        } else {
            $t->set_file("pl", "login.html");
            $t->set_var('temeticaClass',$tema);
            $t->set_var("email", $email);
            error_log(date('h:i:s').' Login_usuario login incorrecto (No Valido Captcha): [email: '.$email."]\n",3,"./logs/error_".date("Y-m-d ").".log");
            $t->set_var('error', 'Captcha inválido. Por favor, vuelva a intentarlo.');
        }
    }
}

function login_usuario_carrito($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "resumenCompra.html");
    $sql   = "SELECT * FROM provincias";
    $query = mysqli_query($db, $sql);

    $t->set_block("pl", "provincia", "_provincia");
    while ($row = mysqli_fetch_array($query)) {
        $t->set_var("nombre_provincia", $row['nombre']);
        $t->set_var("id", $row['id']);
        $t->parse("_provincia", "provincia", true);
    }
    $query = mysqli_query($db, $sql);
    $t->set_block("pl", "provincia-registro", "_provincia-registro");
    while ($row = mysqli_fetch_array($query)) {
        $t->set_var("nombre_provincia_registro", $row['nombre']);
        $t->set_var("id_registro", $row['id']);
        $t->parse("_provincia-registro", "provincia-registro", true);
    }

    if ($_SESSION["usuario"] == "") {
        $email = $_POST['email'];
        error_log(date('h:i:s').' login_usuario_carrito usuario: '.$_POST['email']."\n",3,"./logs/error_".date("Y-m-d ").".log");
        $query = "SELECT * FROM clientes WHERE email = " . "'" . $email . "'" . "AND borrado IS NULL LIMIT 1";
        /* ligar parámetros para marcadores */
        $pwrecibida = $_POST["password"];
        $result     = mysqli_query($db, $query);
        if ($result->num_rows == 0) {
            $t->set_var("usuario_no_logueado", "");
            $t->set_var("usuario_logueado", "");
            pintar_cardCompra($db, $t, PATH, false);
            $t->set_var("button_disabled", 'disabled');
            $t->set_var('error', 'El usuario o clave son incorrectos');
        } else {
            $myrow = mysqli_fetch_array($result);
            if ($myrow != null) {

                if ($myrow['verficado'] == 1) {
                    if ((desencriptar_pass($myrow['password'])) == $pwrecibida) {
                        //save user info in session
                        $_SESSION["usuario"]        = true;
                        $_SESSION["nombre_usuario"] = $myrow["nombre"];
                        $_SESSION["email_cliente"]  = $myrow["email"];
                        $_SESSION["id_cliente"]     = $myrow["id"];
                        error_log(date('h:i:s').' login_usuario_carrito VERIFICADO login correcto: [nombre_usuario: '.$_SESSION["nombre_usuario"].', email_cliente: '.$_SESSION["email_cliente"].', id_cliente: '.$_SESSION["id_cliente"]."]\n",3,"./logs/error_".date("Y-m-d ").".log");
                        $t->set_var("usuario_no_logueado", "style='display:none'");
                        $t->set_var("usuario_logueado", "");
                        loadCart($db, $myrow["id"]);
                        header("Location: " . HOST . "resumenCompra");
                    } else {
                        $t->set_var("usuario_no_logueado", "");
                        $t->set_var("usuario_logueado", "");
                        $t->set_var('error', 'El usuario o clave son incorrectos');
                        $t->set_var("button_disabled", 'disabled');
                        error_log(date('h:i:s').' login_usuario_carrito verificado y login incorrecto: [email: '.$email."]\n",3,"./logs/error_".date("Y-m-d ").".log");
                        pintar_cardCompra($db, $t, PATH, false);
                    }
                } else {
                    pintar_cardCompra($db, $t, PATH, false);
                    error_log(date('h:i:s').' login_usuario_carrito no verificado: [email: '.$email."]\n",3,"./logs/error_".date("Y-m-d ").".log");
                    $t->set_var('error', 'El usuario aún no está verificado');
                }
            } else {
                pintar_cardCompra($db, $t, PATH, false);
                error_log(date('h:i:s').' login_usuario_carrito y login incorrecto (No encontro el email): [email: '.$email."]\n",3,"./logs/error_".date("Y-m-d ").".log");
                $t->set_var('error', 'El usuario o clave son incorrectos');
            }
        }
    }
}

function logout()
{
    error_log(date('h:i:s').' logout: [id_cliente: '.$_SESSION['id_cliente'].', id_vendedor: '.$_SESSION['id_vendedor']."]\n",3,"./logs/error_".date("Y-m-d ").".log");
    $_SESSION['CART']       = "";
    $_SESSION['usuario']    = "";
    $_SESSION['id_cliente'] = "";
    $_SESSION['id_vendedor'] = "";
    $_SESSION['nombre_vendedor'] = "";
    $_SESSION['nombre_usuario'] = "";
    $_SESSION["email_cliente"]  = "";
    header("Location: " . HOST . "home");
    exit;
}

function mostrar_registro($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "registro.html");
    $tema = getTematica();
    $t->set_var('temeticaClass',$tema);
    $sql   = "SELECT * FROM provincias";
    $query = mysqli_query($db, $sql);
    $t->set_block("pl", "provincia", "_provincia");
    while ($row = mysqli_fetch_array($query)) {
        $t->set_var("nombre_provincia", $row['nombre']);
        $t->set_var("id", $row['id']);
        $t->parse("_provincia", "provincia", true);
    }

}

function mostrar_resumen_compra($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "resumenCompra.html");
    if ($_SESSION["usuario"]) {
        $t->set_var("usuario_logueado", "style='display:none;'");
        $t->set_var("usuario_no_logueado", "");
        $sql   = "SELECT * FROM provincias";
        $query = mysqli_query($db, $sql);
        $t->set_block("pl", "provincia", "_provincia");
        while ($row = mysqli_fetch_array($query)) {
            $t->set_var("nombre_provincia", $row['nombre']);
            $t->set_var("id", $row['id']);
            $t->parse("_provincia", "provincia", true);
        }
    } else {
        $t->set_var("usuario_no_logeado", "style='display:none'");
        $t->set_var("usuario_logueado", "");
        $t->set_var("button_disabled", 'disabled');
        $sql   = "SELECT * FROM provincias";
        $query = mysqli_query($db, $sql);
        $t->set_block("pl", "provincia", "_provincia");
        while ($row = mysqli_fetch_array($query)) {
            $t->set_var("nombre_provincia", $row['nombre']);
            $t->set_var("id", $row['id']);
            $t->parse("_provincia", "provincia", true);
        }
        $query = mysqli_query($db, $sql);
        $t->set_block("pl", "provincia-registro", "_provincia-registro");
        while ($row = mysqli_fetch_array($query)) {
            $t->set_var("nombre_provincia_registro", $row['nombre']);
            $t->set_var("id_registro", $row['id']);
            $t->parse("_provincia-registro", "provincia-registro", true);
        }
    }
    $query       = "SELECT p.nombre, l.nombre, l.codigo_postal, p.id, l.id FROM clientes c JOIN provincias p ON p.id = c.provincia_id JOIN localidades l ON c.ciudad_id = l.id WHERE c.borrado IS NULL AND c.id=" . $_SESSION["id_cliente"];
    $sql         = mysqli_query($db, $query);
    $row         = mysqli_fetch_array($sql);
    $nombre_pcia = explode(" ", $row[0]);
    $t->set_var("provincia", setNameLocation($nombre_pcia));
    $t->set_var("_valueprovincia", $row[3]);
    $nombre_ciudad = explode(" ", $row[1]);
    $t->set_var("ciudad", setNameLocation($nombre_ciudad));
    $t->set_var("ciudad_id", $row["id"]);
    $t->set_var("cp", $row["codigo_postal"]);
    pintar_cardCompra($db, $t, PATH, false);
}

function mostrar_micuenta($db, $t)
{
    if (isSeller()) {
        showPanelViajante($_SESSION['id_vendedor']);
    }
    else {
        $t->set_var('base_url', HOST);
        $t->set_file("pl", "miCuentaMisDatos.html");
        $t->set_var("user", $_SESSION["nombre_usuario"]);
        $sql   = "SELECT * FROM provincias";
        $query = mysqli_query($db, $sql);
        $t->set_block("pl", "provincia", "_provincia");
        while ($row = mysqli_fetch_array($query)) {
            $t->set_var("nombre_provincia", $row['nombre']);
            $t->set_var("id", $row['id']);
            $t->parse("_provincia", "provincia", true);
        }
        $query = "SELECT c.nombre, c.apellido, c.email,c.provincia_id, c.ciudad_id, p.nombre, l.nombre, l.codigo_postal ,c.vendedor_id FROM clientes c JOIN provincias p ON p.id = c.provincia_id JOIN localidades l ON c.ciudad_id = l.id WHERE c.borrado IS NULL AND c.id=" . $_SESSION["id_cliente"];
        $sql   = mysqli_query($db, $query);
        $row   = mysqli_fetch_array($sql);
        $t->set_var("nombre", $row[0]);
        $t->set_var("apellido", $row["apellido"]);
        $t->set_var("email", $row["email"]);
        $nombre_pcia = explode(" ", $row[5]);
        $t->set_var("provincia", setNameLocation($nombre_pcia));
        $t->set_var("provincia_id", $row["provincia_id"]);
        $nombre_ciudad = explode(" ", $row[6]);
        $t->set_var("ciudad", setNameLocation($nombre_ciudad));
        $t->set_var("ciudad_id", $row["ciudad_id"]);
        $t->set_var("cp", $row["codigo_postal"]);

        $t->set_var("newsletter_status", isInNewsletter($row['email'])?"checked":"");

        showDatosViajante($t, $_SESSION['id_cliente']);

    }
}

function getDateDiff($date) {
    $now = time();
    $lastUpdate = strtotime($date);
    $diff = $now - $lastUpdate;
    return round($diff / (60 * 60 * 24));
}

function setNameLocation($name)
{
    $aux = '';
    if (count($name) === 1) {
        return $name[0];
    } else {
        for ($i = 0; $i < count($name); $i++) {
            $aux = $aux . "&nbsp;" . $name[$i];
        }

        return $aux;
    }
}

function mostrar_micuenta_mispedidos($db, $t)
{
    if (isSeller()) {
        showSellerOrders();
    }
    else {
        $t->set_var('base_url', HOST);
        $t->set_file("pl", "miCuentaMisPedidos.html");
        $t->set_var("user", $_SESSION["nombre_usuario"]);
        pintar_pedidos($db, $t);

        showDatosViajante($t, $_SESSION['id_cliente']);
    }
}

function mostrar_micuenta_password($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "miCuentaPassword.html");
    $t->set_var("user", $_SESSION["nombre_usuario"]);

    if (isSeller()) {
        $t->set_var("hiddeCard", "style='display:none;'");
        showSidebarPanelSeller($_SESSION['id_vendedor']);
    }
    else {
        showDatosViajante($t, $_SESSION['id_cliente']);
    }
}

function buscar_producto($db, $t, $entrada,$desdeAdmin = false)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "mostrarBuscar.html");
    //$resultado = stripAccents(urldecode(str_replace("-", "%", $entrada)));
    $resultado = str_replace("?", "/", $entrada);
    $busqueda = str_replace("-", " ", $resultado);
    $resultado = str_replace("-", "%", $resultado);
    error_log('entrada: '.$entrada);
    error_log('resultado: '.$resultado);
    $_SESSION['filter'] = "/buscar/$entrada";
    $t->set_var("title_busqueda", $busqueda);

    $query = "SELECT DISTINCT p.*, m.nombre AS marca
    FROM productos p
    INNER JOIN marcas m ON m.id = p.marca_id
    INNER JOIN categorias c ON c.id = p.categoria_id
    LEFT JOIN producto_tags pt ON pt.id_producto = p.id
    LEFT JOIN tags t ON t.id = pt.id_tag
    WHERE p.borrado IS NULL AND
    p.estado = 'Disponible' AND
    (p.nombre LIKE '%$resultado%' OR p.tipo LIKE '%$resultado%' OR p.descripcion LIKE '%$resultado%'
      OR m.nombre LIKE '%$resultado%' OR m.descripcion LIKE '%$resultado%'
      OR c.nombre LIKE '%$resultado%' OR c.descripcion LIKE '%$resultado%'
      OR t.nombre LIKE '%$resultado%' OR t.descripcion LIKE '%$resultado%'
    )";

    //) ORDER BY p.precio_pvp DESC";
    $sql = mysqli_query($db, $query);
    $res_cant = mysqli_num_rows($sql);
    $t->set_var("cant_resultados", $res_cant);
    $t->set_var("a_mostrar", min(90, mysqli_num_rows($sql)));

    if (mysqli_num_rows($sql) === 0) {

        $query = "SELECT p.*, m.nombre AS marca
        FROM productos p
        INNER JOIN marcas m ON m.id = p.marca_id
        WHERE p.borrado IS NULL AND
        p.estado = 'Disponible' ORDER BY rand() LIMIT 12";

        $sql = mysqli_query($db, $query);
    }

    if (!$desdeAdmin) {
        if ($_SESSION['id_vendedor'] == ''){
            //El usuario logueado es cliente
            $usuario = $_SESSION['id_cliente'] == '' ? 0 : $_SESSION['id_cliente'];
            $sqlBusq = "INSERT INTO busquedas_clientes (cliente_id, busqueda, resultado, link) 
                    VALUES ($usuario, '$busqueda', $res_cant, '/buscaDesdeAdmin/$entrada')";
        } else {
            //el usuario logueado es vendedor
            $usuario = $_SESSION['id_vendedor'];
            $cliente = $_SESSION['id_cliente'];
            $sqlBusq = "INSERT INTO busquedas_clientes (cliente_id, vendedor_id, busqueda, resultado, es_vendedor, link) 
                    VALUES ($cliente, $usuario, '$busqueda', $res_cant, 1, '/buscaDesdeAdmin/$entrada')";
        }
        error_log($sqlBusq);
        mysqli_query($db, $sqlBusq);
    }    

    $tf = new Template(PATH, "remove");
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "cardProduct.html");
    $tf->set_block("pl", "productos", "_productos");

    while ($row = mysqli_fetch_array($sql)) {
        set_product($tf, $row, $db);
        $tf->parse("_productos", "productos", true);
        $product = $tf->parse("MAIN", "pl");
        $t->set_var("product", $product);

    }
}

function mostrar_por_edad($db, $t, $rango_id){
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "mostrarBuscar.html");
    $_SESSION['filter'] = "/edad/$rango_id";

    $query = "SELECT p.*, m.nombre AS marca
        FROM productos p
        INNER JOIN marcas m ON m.id = p.marca_id        
        WHERE p.borrado IS NULL AND p.estado = 'Disponible' and rango_edad = $rango_id";

    $sql = mysqli_query($db, $query);
    $t->set_var("cant_resultados", mysqli_num_rows($sql));
    $t->set_var("a_mostrar", min(90, mysqli_num_rows($sql)));

    $sqlTitle = "SELECT rango FROM rango_edades WHERE id = $rango_id";
    $st = mysqli_query($db, $sqlTitle);
    $row_t   = mysqli_fetch_array($st);

    $t->set_var("title_busqueda", $row_t['rango']);

   /*  if (mysqli_num_rows($sql) === 0) {

        $query = "SELECT p.*, m.nombre AS marca
        FROM productos p
        INNER JOIN marcas m ON m.id = p.marca_id
        WHERE p.borrado IS NULL AND
        p.estado = 'Disponible' ORDER BY rand() LIMIT 12";

        $sql = mysqli_query($db, $query);
    }
 */
    $tf = new Template(PATH, "remove");
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "cardProduct.html");
    $tf->set_block("pl", "productos", "_productos");
    while ($row = mysqli_fetch_array($sql)) {
        set_product($tf, $row, $db);
        $tf->parse("_productos", "productos", true);
        $product = $tf->parse("MAIN", "pl");
        $t->set_var("product", $product);

    }
}

function guardar_pw($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "miCuentaPassword.html");
    $old_pw   = $_POST['old-pw'];
    $new_pw_1 = $_POST['new-pw1'];
    $new_pw_2 = $_POST['new-pw2'];

    if (isSeller()) {
        $query    = "SELECT * FROM vendedores WHERE borrado IS NULL AND id = " . $_SESSION["id_vendedor"];

        $t->set_var("hiddeCard", "style='display:none;'");
        showSidebarPanelSeller($_SESSION['id_vendedor']);
    }
    else {
        $query    = "SELECT * FROM clientes WHERE borrado IS NULL AND id = " . $_SESSION["id_cliente"];
    }
    $sql      = mysqli_query($db, $query);
    $row      = mysqli_fetch_array($sql);

    if ($new_pw_1 === $new_pw_2) {
        if ((desencriptar_pass($row['password'])) == $old_pw) {
            $pw_encriptada = encriptar_pass($new_pw_1);
            if (isSeller()) {
                $query         = "UPDATE vendedores SET password = '" . $pw_encriptada . "' WHERE id = " . $_SESSION["id_vendedor"];
            }
            else {
                $query         = "UPDATE clientes SET password = '" . $pw_encriptada . "' WHERE id = " . $_SESSION["id_cliente"];
            }
            mysqli_query($db, $query);
            $t->set_var("success", 'La contraseña se cambió correctamente');
        } else {
            $t->set_var("error", 'La contraseña actual ingresada es incorrecta');
        }
    } else {
        $t->set_var("error", 'Las contraseñas nuevas no coinciden');
    }
}

function guardar_datos($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "miCuentaMisDatos.html");
    $name     = $_POST["nombre"];
    $lastname = $_POST["apellido"];
    $email    = $_POST["email"];
    $state    = $_POST['provincia'];
    $city     = $_POST['ciudad'];
    $query    = "UPDATE clientes SET nombre = '" . $name . "', apellido = '" . $lastname . "', email = '" . $email . "'
        , provincia_id = '" . $state . "', ciudad_id = '" . $city . "' WHERE id = " . $_SESSION["id_cliente"];
    if (mysqli_query($db, $query)) {
        $_SESSION["nombre_usuario"] = $name . " " . $lastname;
        $_SESSION["email_cliente"]  = $email;
    }

    if (isset($_POST['newsletter'])) {
        addToNewsletter($email);
    }
    else {
        removeFromNewsletter($email);
    }

    header("Location: " . HOST . "miCuenta");
}

function mostrar_categorias($db, $t, $tipo, $page = null, $producto_add = null)
{

    $t->set_var('base_url', HOST);
    $t->set_file("pl", "categorias.html");
    $t->set_var('page_type', 'categorias');
    $t->set_var("tipo_", $tipo);

    $_SESSION['filter'] = "/categorias/$tipo/$page";

    $tipo = getTipo($db,$tipo); //VG: Refactor Categorias

    // we count how many products of type are
    $query = "SELECT COUNT(id) FROM productos WHERE borrado IS NULL AND estado = 'Disponible' AND tipo = '$tipo'";
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);
    $cant_productos = $row["COUNT(id)"];
    setCategoria($db, $t, $tipo);
    $t->set_var("cant_resultados", $cant_productos);
    $t->set_var("a_mostrar", min(90, $cant_productos));

    if ($tipo == "libro") {
        $t->set_var("tipo", "Editorial");
    } else {
        $t->set_var("tipo", "Marca");
    }

    $t->set_block("pl", "marcas", "_marcas");

    // we set brands
    $q = "SELECT * FROM marcas WHERE borrado IS NULL AND tipo = '$tipo' ORDER BY nombre";

    $rslt = mysqli_query($db, $q);
    while ($row_ = mysqli_fetch_array($rslt)) {
        $t->set_var("id_marca", $row_["id"]);
        $t->set_var("nombre_marca", slugify($row_["nombre"]));
        //$t->set_var("nombre_marca", slugify(utf8_encode($row_["nombre"])));

        // we count how many products per brand are
        $q    = "SELECT count(id) AS cantidad FROM productos WHERE marca_id = '" . $row_['id'] . "' AND estado = 'Disponible' AND borrado IS NULL AND tipo = '$tipo' LIMIT 1";
        $cant = mysqli_query($db, $q);
        $cant = mysqli_fetch_array($cant);

        if ($cant['cantidad'] == 0) continue;

        $t->set_var("nombre", $row_['nombre'] . " (" . $cant['cantidad'] . ")");
       // $t->set_var("nombre", utf8_encode($row_['nombre']) . " (" . $cant['cantidad'] . ")");
        $t->parse("_marcas", "marcas", true);
    }
    //agregado
    $t->set_block("pl", "cat", "_cat");

    // we set categories
    $q = "SELECT * FROM categorias WHERE borrado IS NULL AND tipo = '$tipo' ORDER BY nombre";

    $rslt = mysqli_query($db, $q);
    while ($row_ = mysqli_fetch_array($rslt)) {
        $t->set_var("id_category", $row_["id"]);
        $t->set_var("nombre_categoria", $row_["nombre"]);
        //$t->set_var("nombre_categoria", slugify(utf8_encode($row_["nombre"])));

        // we count how many products per category are
        $q    = "SELECT count(id) AS cantidad FROM productos WHERE categoria_id = '" . $row_['id'] . "' AND estado = 'Disponible' AND borrado IS NULL AND tipo = '$tipo' LIMIT 1";
        $cant = mysqli_query($db, $q);
        $cant = mysqli_fetch_array($cant);

        if ($cant['cantidad'] == 0) continue;

        $t->set_var("category_name", $row_['nombre'] . " (" . $cant['cantidad'] . ")");
        //$t->set_var("category_name", utf8_encode($row_['nombre']) . " (" . $cant['cantidad'] . ")");
        $t->parse("_cat", "cat", true);
    }

    if ($producto_add != null) {
        $t->set_var("photo", $producto_add['path']);
        $t->set_var("producto_agregado", $producto_add['nombre']);
       // $t->set_var("producto_agregado", utf8_encode($producto_add['nombre']));
    } else {
        $t->set_var("producto_agregado_hidden", "style='display:none;'");
    }   

    $limit = getLimitPage();
    $url = "categorias/".$tipo;

    paginado($t,$cant_productos,$page,$url,$limit);

    pintar_productos($t, $db, $tipo, "", "", "product",$page, $limit);

}

function mostrar_marcas_filtradas($db, $t, $id_marca, $page = null, $producto_add = null)
{
    $t->set_var('base_url', HOST);

    $_SESSION['filter'] = "/marca/$id_marca/$page";

    $t->set_var('page_type', 'categorias');
    $t->set_file("pl", "categorias.html");

    if ($producto_add != null) {
        $t->set_var("photo", $producto_add['path']);
        $t->set_var("producto_agregado", $producto_add['nombre']);
    } else {
        $t->set_var("producto_agregado_hidden", "style='display:none;'");
    }

    $query = "SELECT COUNT(*), tipo FROM productos WHERE borrado IS NULL AND estado = 'Disponible' AND marca_id= '$id_marca' GROUP BY tipo";

    $q    = "SELECT * FROM marcas WHERE borrado IS NULL AND id= '$id_marca'";
    $rlst = mysqli_query($db, $q);
    $row_ = mysqli_fetch_array($rlst);
    $t->set_var("marca", $row_['nombre']);

    $just_id = explode('-', $id_marca)[0];
    $t->set_var("banner", $row_['banner']?'<img style="max-width:100%; margin-bottom: 20px; width: 100%;" src="' . HOST .  'images/marcas/' . $just_id . '/' . $just_id . '.' . $row_['banner'] . '">':"");

    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);
    $cant_productos = $row["COUNT(*)"];
    $t->set_var("cant_resultados", $cant_productos );
    $t->set_var("a_mostrar", min(90, $cant_productos));

    $tipo = $row['tipo'];
    $tipo_query = $tipo;
    $t->set_var("tipo_", $tipo_query);

    setCategoria($db, $t, $tipo); //VG: Refactor categorias

    if ($tipo == "libro") {
        $t->set_var("tipo", "Editorial");
    } else {
        $t->set_var("tipo", "Marca");
    }
    $t->set_block("pl", "marcas", "_marcas");

    $q = "SELECT * FROM marcas WHERE borrado IS NULL AND tipo = '$tipo_query' ORDER BY nombre";

    $rslt = mysqli_query($db, $q);

    while ($row_ = mysqli_fetch_array($rslt)) {
        $t->set_var("id_marca", $row_["id"]);
        $t->set_var("nombre_marca", slugify($row_["nombre"]));

        $q    = "SELECT count(id) AS cantidad FROM productos WHERE marca_id = '" . $row_['id'] . "' AND estado = 'Disponible' AND borrado IS NULL AND tipo = '$tipo'";
        $cant = mysqli_query($db, $q);
        $cant = mysqli_fetch_array($cant);

        if ($cant['cantidad'] == 0) continue;

        $t->set_var("nombre", $row_['nombre'] . " (" . $cant['cantidad'] . ")");
        $t->parse("_marcas", "marcas", true);
    }

    $t->set_block("pl", "cat", "_cat");

    $q = "SELECT * FROM categorias WHERE borrado IS NULL AND tipo = '$tipo_query' ORDER BY nombre";

    $rslt = mysqli_query($db, $q);
    while ($row_ = mysqli_fetch_array($rslt)) {
        $t->set_var("id_category", $row_["id"]);
        $t->set_var("nombre_categoria", slugify($row_["nombre"]));

        $q    = "SELECT count(id) AS cantidad FROM productos WHERE categoria_id = '" . $row_['id'] . "' AND estado = 'Disponible' AND borrado IS NULL AND tipo = '$tipo'";
        $cant = mysqli_query($db, $q);
        $cant = mysqli_fetch_array($cant);

        if ($cant['cantidad'] == 0) continue;

        $t->set_var("category_name", $row_['nombre'] . " (" . $cant['cantidad'] . ")");
        $t->parse("_cat", "cat", true);
    }

    $limit = getLimitPage();
    $url = 'marca/'.$id_marca;
    paginado($t,$cant_productos,$page,$url,$limit);

    pintar_marcas_filtro($t, $db, $tipo_query, $id_marca, $page, $limit); 
}

function pintar_marcas_filtro($t, $db, $tipo, $id_marca, $page = null, $limit = null)
{
    $tf = new Template(PATH, "remove");
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "cardProduct.html");
    $tf->set_block("pl", "productos", "_productos");

    $sql = "SELECT p.*, m.nombre AS marca
    FROM productos p
    INNER JOIN marcas m ON m.id = p.marca_id
    WHERE p.borrado IS NULL AND p.estado = 'Disponible'";

    if ($tipo != '') {
        $sql .= " AND p.tipo = '$tipo'";
    }
    if ($id_marca != '') {
        $sql .= " AND p.marca_id = '$id_marca'";
    }

    if ($page != null) {
        $lim_min = (intval($page) - 1)*$limit;
        $sql .= " LIMIT $lim_min, $limit";
    }

    //$sql .= ' ORDER BY p.precio_pvp DESC LIMIT 60';

    $query = mysqli_query($db, $sql);
    while ($row = mysqli_fetch_array($query)) {
        if ($row["borrado"] == null && $row["estado"] == "Disponible") {
            set_product($tf, $row, $db);
            $tf->parse("_productos", "productos", true);
            $product = $tf->parse("MAIN", "pl");
            $t->set_var("product", $product);
        }
    }
}

function mostrar_categorias_filtradas($db, $t, $id_categoria, $page = null, $producto_add = null)
{
    $t->set_var('base_url', HOST);

    $_SESSION['filter'] = "/categoria/$id_categoria/$page";

    $t->set_var('page_type', 'categorias');
    $t->set_file("pl", "categorias.html");

    if ($producto_add != null) {
        $t->set_var("photo", $producto_add['path']);
        $t->set_var("producto_agregado", $producto_add['nombre']);
    } else {
        $t->set_var("producto_agregado_hidden", "style='display:none;'");
    }

    $query = "SELECT COUNT(*), tipo FROM productos WHERE borrado IS NULL AND estado = 'Disponible' AND categoria_id = '$id_categoria' GROUP BY tipo";

    $q    = "SELECT * FROM categorias WHERE borrado IS NULL AND id = '$id_categoria'";
    $rlst = mysqli_query($db, $q);
    $row_ = mysqli_fetch_array($rlst);
    $t->set_var("marca", $row_['nombre']);

    $just_id = explode('-', $id_categoria);

    $t->set_var("banner", $row_['banner']?'<img style="max-width:100%; margin-bottom: 20px; width: 100%;" src="' . HOST .  'images/categorias/' . $just_id . '/' . $just_id . '.' . $row_['banner'] . '">':"");

    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);
    $cant_productos = $row["COUNT(*)"];
    $t->set_var("cant_resultados", $cant_productos);
    $t->set_var("a_mostrar", min(90, $cant_productos));


    $tipo = $row['tipo'];
    $tipo_query = $tipo;
    $t->set_var("tipo_", $tipo_query);

    setCategoria($db, $t, $tipo); //VG:Refactor Categorias

    if ($tipo_query == "libro") {
        $t->set_var("tipo", "Editorial");
    } else {
        $t->set_var("tipo", "Marca");
    }
    $t->set_block("pl", "marcas", "_marcas");

    $q = "SELECT * FROM marcas WHERE borrado IS NULL AND tipo = '$tipo_query' ORDER BY nombre";

    $rslt = mysqli_query($db, $q);

    while ($row_ = mysqli_fetch_array($rslt)) {
        $t->set_var("id_marca", $row_["id"]);
        $t->set_var("nombre_marca", slugify($row_["nombre"]));

        $q    = "SELECT count(id) AS cantidad FROM productos WHERE marca_id = '" . $row_['id'] . "' AND estado = 'Disponible' AND borrado IS NULL AND tipo = '$tipo'";
        $cant = mysqli_query($db, $q);
        $cant = mysqli_fetch_array($cant);

        if ($cant['cantidad'] == 0) continue;

        $t->set_var("nombre", $row_['nombre'] . " (" . $cant['cantidad'] . ")");
        $t->parse("_marcas", "marcas", true);
    }

    $t->set_block("pl", "cat", "_cat");

    $q = "SELECT * FROM categorias WHERE borrado IS NULL AND tipo = '$tipo_query' ORDER BY nombre";

    $rslt = mysqli_query($db, $q);
    while ($row_ = mysqli_fetch_array($rslt)) {
        $t->set_var("id_category", $row_["id"]);
        $t->set_var("nombre_categoria", slugify($row_["nombre"]));

        $q    = "SELECT count(id) AS cantidad FROM productos WHERE categoria_id = '" . $row_['id'] . "' AND estado = 'Disponible' AND borrado IS NULL AND tipo = '$tipo'";
        $cant = mysqli_query($db, $q);
        $cant = mysqli_fetch_array($cant);

        if ($cant['cantidad'] == 0) continue;

        $t->set_var("category_name", $row_['nombre'] . " (" . $cant['cantidad'] . ")");
        $t->parse("_cat", "cat", true);
    }

    $limit = getLimitPage();

    $url = "categoria/".$id_categoria;
    paginado($t,$cant_productos,$page,$url,$limit);    

    pintar_categorias_filtro($t, $db, $tipo_query, $id_categoria, $page, $limit);
}

function pintar_categorias_filtro($t, $db, $tipo, $id_categoria, $page = null, $limit = null)
{
    $tf = new Template(PATH, "remove");
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "cardProduct.html");
    $tf->set_block("pl", "productos", "_productos");

    $sql = "SELECT p.*, m.nombre AS marca
    FROM productos p
    INNER JOIN marcas m ON m.id = p.marca_id
    WHERE p.borrado IS NULL AND p.estado = 'Disponible'";

    if ($tipo != '') {
        $sql .= " AND p.tipo = '$tipo'";
    }

    if ($id_categoria != '') {
        $sql .= " AND p.categoria_id = '$id_categoria'";
    }

    if ($page != null) {
        $lim_min = (intval($page) - 1)*$limit;
        $sql .= " LIMIT $lim_min, $limit";
    }

    //$sql .= ' ORDER BY p.precio_pvp DESC LIMIT 60';

    $query = mysqli_query($db, $sql);
    while ($row = mysqli_fetch_array($query)) {
        if ($row["borrado"] == null && $row["estado"] == "Disponible") {
            set_product($tf, $row, $db);
            $tf->parse("_productos", "productos", true);
            $product = $tf->parse("MAIN", "pl");
            $t->set_var("product", $product);
        }
    }
}


function productoAgregado($db, $t, $id_producto)
{
    $query = "SELECT p.id,p.nombre,p.tipo,p.categoria_id,m.path
    FROM productos p JOIN media m WHERE p.id=m.producto_id AND p.id=" . $id_producto;
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);

    $filter_type = explode("/", $_SESSION['filter']);

    switch ($filter_type[1]) {
        case "categoria":
            mostrar_categorias_filtradas($db, $t, $filter_type[2], $filter_type[3], $row);
            break;
        case "marca":
            mostrar_marcas_filtradas($db, $t, $filter_type[2], $filter_type[3], $row);
            break;
        case "tags":
            showTag($filter_type[2], $filter_type[3], $row);
            break;
        case "buscar":
            buscar_producto($db, $t, $filter_type[2]);
            break;
        case "edad": 
            mostrar_por_edad($db, $t, $filter_type[2]);
            break;
        case "categorias":
        default:
            mostrar_categorias($db, $t, $filter_type[2], $filter_type[3], $row);
    }
}

function getDiscount($db, $type = 'libros')
{   //TODO VER TIPO CATEGORIA - Para refactorizar este código hay que cambiar estructura de BD clientes. Por ahora queda como esta (VG: Febrero 2024)
    //Nota: Este código no acumula descuentos, contempla solo un tipo de descuento. 
    //Si el cliente tiene un descuento adicional por libros, el dto del 30% lo sobreescribe con el dto del cliente. Verificar con Diego si es correcto

    $descuento = ($type == 'libros')?0.3:0;
    if (isset($_SESSION['id_cliente'])) {
        $sql    = "SELECT descuento_$type FROM clientes WHERE id =" . $_SESSION["id_cliente"];
        $query  = mysqli_query($db, $sql);
        $result = mysqli_fetch_array($query);
        if ($result["descuento_$type"] != null && $result["descuento_$type"] != 0) {
            $descuento = round($result["descuento_$type"] / 100, 2);
        }
    }
    return $descuento;

}

function mostrar_productoAmpliado($db, $t, $id_producto)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "productoAmpliado.html");

    $query = "SELECT p.*, m.nombre AS marca
    FROM productos p
    INNER JOIN marcas m ON m.id = p.marca_id
    WHERE p.borrado IS NULL AND p.estado = 'Disponible' AND (p.sku = '$id_producto') LIMIT 1";

    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);

    if (empty($row)) {
        $query = "SELECT p.*, m.nombre AS marca, re.rango as edades
        FROM productos p
        INNER JOIN marcas m ON m.id = p.marca_id
        LEFT JOIN rango_edades re ON re.id = p.rango_edad 
        WHERE p.borrado IS NULL AND p.estado = 'Disponible' AND (p.id = $id_producto) LIMIT 1";
        $result = mysqli_query($db, $query);
        $row    = mysqli_fetch_array($result);
    }

    if (empty($row)) {
        header("Location: " . HOST . "home");
    }

    set_imagenes($db, $t, $row, 'imagen');
    $t->set_var('id', $row['id']);
    //$t->set_var('nombre', utf8_encode($row['nombre']));
    $t->set_var('nombre', $row['nombre']);
    $t->set_var('descripcion', $row['descripcion']);
    //$t->set_var('descripcion', utf8_encode($row['descripcion']));
    $t->set_var('video', !empty($row['video'])?'<div class="video">' . $row['video'] . '<div data-fancybox="gallery" data-src="#video" style="display: none;"></div><div id="video" style="display: none;">' . $row['video'] . '</div></div>':'');

    $t->set_var('videoThumb', !empty($row['video'])?'<div class="video-thumb cursor-pointer"><img src="' . HOST . 'assets/images/video-icon.png" /></div>':'');
    //$t->set_var('video', !empty($row['video'])?'<div class="video">' . $row['video'] . '</div>':'');
    $t->set_var('marca', $row['marca']);
    $t->set_var('nombre_marca', slugify($row['marca']));
    $t->set_var('marca_id', $row['marca_id']);
    $t->set_var('categoria_tipo', $row['tipo']);
    $t->set_var('categoria', getNombreCategoria($row['categoria_id'], $db));

    $t->set_var('new', setNewBadge($row['fecha_alta']));
    $t->set_var("en_tv", $row["en_tv"]?"en_tv":"");
    $t->set_var("actualizacion", date('d-m-Y', strtotime($row["fecha_actualizacion"])));

    $observaciones = observaciones_en_carrito($db,$row['id'],$row['tipo']);
    $cant_agregada = cantidad_en_carrito($db,$row['id'],$row['tipo']);
    
    $t->set_var("min_prod", $cant_agregada == 0 ? 1 : 0);
    $t->set_var("obs", urldecode($observaciones));

    /*Rango edad productos*/
    if ($row["edades"]) {
        $edad = explode(" ", $row["edades"]);
        //$edad = explode(" ", utf8_encode($row["rango_edad"]));

        $edad = '<span>' . $edad[0] . '</span>' . $edad[1];

        $t->set_var("edad", $edad);
    } else {
        $t->set_var("visible", "style='display:none;'");
    }

    if (showPrices($row['tipo'])) {
        $t->set_var('pvp', formatNumber($row['precio_pvp'], 2));
        $tipo = translateType($db,$row['tipo']);

        $descuento_cliente = getDiscount($db, $tipo);
        if ($descuento_cliente > 0 && $row['tipo'] != 'libro' && $descuento_cliente != 0.3) {
            if($row['descuento'] != 0) {
                $t->set_var("descuento", $row["descuento"]);
            }
            else {
                $t->set_var("descuento", $descuento_cliente*100);
            }
        }
        else {
            $t->set_var("descuento", $row["descuento"]);
        }

        $precio = $row["precio_pvp"];

        if ($row['descuento'] != 0 || ($descuento_cliente > 0 && $row['tipo'] != 'libro' && $descuento_cliente != 0.3)) {
            $precio_desc = round((1 - ($row["descuento"] / 100)) * ($precio), 2);
            $precio      = $precio_desc;
        } else {
            $t->set_var("tiene_descuento", "style='display:none;'");
        }

        if ($row['descuento'] == 0) {
            $t->set_var("tiene_descuento_producto", "style='display:none;'");
        }

        $t->set_var("p", formatNumber($precio, 2));

        aplicarDescuento($t, $db, $precio, $tipo);
        if ($row["tipo"] == "libro") {
            $t->set_var("aplica_iva", "style='display:none;'");
        } else {
            $iva = round($precio * 1.21, 2);
            $t->set_var("iva", $iva);
            $t->set_var("hidden_pvp", "style='display:none;'");
            //$t->set_var("logueado", "style='display:none;'");
        }

        $t->set_var('tipo', $tipo);
    } else {
        $t->set_var('tipo', 'hidden-prices');
    }
    if (!userLogged()) {
        $t->set_var("logged", "not-logged");
    }

    $t->set_var('tag_list', returnTagListFor($row['id']));

    pintar_productos_categoria($t, $db, $row['tipo'], $row['categoria_id'], $row['id']);

   // showTagList(tag_producto_ampliado, 'carousel1');
}

function pintar_productos_categoria($t, $db, $tipo, $categoria, $id_producto = null)
{
    $tf = new Template(PATH, "remove");
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "cardProduct.html");
    $tf->set_block("pl", "productos", "_productos");

    $sql = "SELECT p.*, m.nombre AS marca
    FROM productos p
    INNER JOIN marcas m ON m.id = p.marca_id
    WHERE p.borrado IS NULL AND p.estado = 'Disponible'";

    if ($tipo != '') {
        // TODO: LIMITAR ESTO
        // TODO: esto no esta pensado para muchos productos agregar paginas??
        $sql .= " AND p.tipo = '$tipo' AND p.categoria_id = '$categoria' AND NOT (p.id = '$id_producto')";
    }

    $sql .= " ORDER BY rand() LIMIT 4";

    $query = mysqli_query($db, $sql);
    if ($query->num_rows == 0) {
        $sql = "SELECT p.*, m.nombre AS marca
        FROM productos p
        INNER JOIN marcas m ON m.id = p.marca_id
        WHERE p.borrado IS NULL AND p.estado = 'Disponible' AND p.tipo = '$tipo'";
        $query = mysqli_query($db, $sql);
    }
    while ($row = mysqli_fetch_array($query)) {
        if ($row["borrado"] == null && $row["estado"] == "Disponible") {
            set_product($tf, $row, $db);
            $tf->parse("_productos", "productos", true);
            $product = $tf->parse("MAIN", "pl");
            $t->set_var("product", $product);
        }
    }
}

function pintar_productos_tag($t, $db, $tag)
{
    //id tag consultar tambien por tipo??
    $tf = new Template(PATH, "remove");
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "cardProduct.html");
    $tf->set_block("pl", "productos", "_productos");

    $sql = "SELECT p.*, m.nombre AS marca
    FROM productos p
    INNER JOIN marcas m ON m.id = p.marca_id
    WHERE p.borrado IS NULL AND p.estado = 'Disponible'";

    if ($tag != '') {
        $sql .= " AND p.id IN (SELECT pt.id_producto FROM producto_tags pt JOIN tags t ON t.id = pt.id_tag WHERE t.id = '$tag') ORDER BY rand() LIMIT 12"; // TODO: LIMITAR ESTO
    }

    $query = mysqli_query($db, $sql);
    if ($query->num_rows == 0) {
        $sql = "SELECT p.*, m.nombre AS marca
        FROM productos p
        INNER JOIN marcas m ON m.id = p.marca_id
        WHERE p.borrado IS NULL AND p.estado = 'Disponible'";
        $query = mysqli_query($db, $sql);
    }
    while ($row = mysqli_fetch_array($query)) {
        set_product($tf, $row, $db);
        $tf->parse("_productos", "productos", true);
        $product = $tf->parse("MAIN", "pl");
        $t->set_var("tags", $product);
    }
}

function aplicarDescuento($t, $db, $precio, $type = 'libros')
{
    $t->set_var('base_url', HOST);
    $descuento = getDiscount($db, $type);
    $precio_final = round((1 - $descuento) * ($precio), 2);
    $t->set_var("precio_final", formatNumber($precio_final, 2));
    $t->set_var("descuento_cliente", $descuento * 100);
}

function getDiscountedPrice($price, $type = 'libros')
{
    global $db;

    $discount = getDiscount($db, $type);
    if ($discount != null && $discount != 0) {
        return round((1 - $discount) * ($price), 2);
    }

    return $price;
}

function getNombreCategoria($id, $db)
{

    $query  = "SELECT nombre FROM categorias WHERE borrado IS NULL AND id = " . $id;
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);
    if ($row != null) {
        return $row['nombre'];
    } else {
        return " ";
    }

}

function pintar_pedidos($db, $t)
{

    $tf = new Template(PATH, "remove");
    $t->set_var('base_url', HOST);
    //$tf->set_file("pl", "cardPedidos.html");
    $t->set_block("pl", "pedidos", "_pedidos");

    $sql   = "SELECT * FROM pedidos WHERE cliente_id =" . $_SESSION["id_cliente"];
    $query = mysqli_query($db, $sql);

    while ($row = mysqli_fetch_array($query)) {
        set_pedido($t, $row);
        //$pedido = $tf->parse("MAIN", "pl");
        //$t->set_var("pedido", $pedido);
        $t->parse("_pedidos", "pedidos", true);
    }
}

function set_pedido($tf, $row)
{

    $date = explode(" ", $row["fecha"]);
    $date = explode("-", $date[0]);
    $date = $date[2] . "/" . $date[1] . "/" . $date[0];
    $tf->set_var("date", $date);

    if (isSeller()) {
        $tf->set_var("nombre", $row['nombre'] . ' ' . $row['apellido']);
    }
    else {
        $tf->set_var('nombre', $row['id']);
    }

    $es_vendedor = $row['vendedor_id'] != null;
    $tf->set_var("id", $row["id"]);
    $tf->set_var("estado", $row["estado"]  . (($es_vendedor)?' <span class="badge badge-info" style="display: inline-block; background: #17a2b8; width: 10px; height: 10px; border-radius: 50%;"></span>':''));
    $tf->set_var("total", formatNumber($row["total"], 2));
    $tf->set_var("state", $row["estado"]);

   // $tf->parse("_pedidos", "pedidos", true);
}

function pintar_productos($t, $db, $tipo, $tag = '', $title, $field, $page=NULL, $limit=null)
{

    $tf = new Template(PATH, "remove");
    $tf->set_var('base_url', HOST);

    $sql = "SELECT p.*, m.nombre AS marca
        FROM productos p
        INNER JOIN marcas m ON m.id = p.marca_id
        WHERE p.borrado IS NULL AND p.estado = 'Disponible'";

    if ($tipo != '') {
        $sql .= " AND p.tipo = '$tipo'";
    }
    if ($tag != '') {
        $sql .= " AND p.id IN (
            SELECT pt.id_producto FROM producto_tags pt JOIN tags t ON t.id = pt.id_tag WHERE t.nombre = '$tag'
        )";
    }
    if ($page != null) {
        $lim_min = (intval($page) - 1)*$limit;
        $sql .= " LIMIT $lim_min, $limit";

    } else {
         $sql .= ' ORDER BY rand() LIMIT 90';
        //$sql .= ' ORDER BY p.precio_pvp DESC LIMIT 60';
    }
   
    $query = mysqli_query($db, $sql);

    $tf->set_file("pl", "cardProduct.html");
    $tf->set_block("pl", "productos", "_productos");
    if ($title != '') {
        $t->set_var("title" . $field, $title);
        //$t->set_var("title" . $field, utf8_encode($title));
    } elseif ($tag != '') {
        $t->set_var("title" . $field, $tag);
        //$t->set_var("title" . $field, utf8_encode($tag));
    } else {
        $t->set_var("title" . $field, 'Productos Destacados');
    }
    if ($query->num_rows == 0) {
        //nota comportamiento inicial: Si no encuentra ningun producto de un tipo determinado, muestra 60 productos al azar de cualquier tipo
        $sql = "SELECT p.*, m.nombre AS marca
        FROM productos p
        INNER JOIN marcas m ON m.id = p.marca_id
        WHERE p.borrado IS NULL AND p.estado = 'Disponible' ORDER BY rand() LIMIT 60";
        $query = mysqli_query($db, $sql);
    }

    while ($row = mysqli_fetch_array($query)) {
        set_product($tf, $row, $db);
        $tf->parse("_productos", "productos", true);
        $product = $tf->parse("MAIN", "pl");
        $t->set_var($field, $product);
    }
}

function set_product($tf, $row, $db)
{

    $tf->set_var("id_producto", $row["id"] . '-' . slugify($row['nombre']));
    //$tf->set_var("id_producto", $row["id"] . '-' . slugify(utf8_encode($row['nombre'])));
    $tf->set_var("nombre_producto", $row["nombre"]);
    //$tf->set_var("nombre_producto", utf8_encode($row["nombre"]));
    $tf->set_var("marca", $row["marca"]);
    //$tf->set_var("marca", utf8_encode($row["marca"]));
    $tf->set_var("tipo", $row["tipo"]);
    $tf->set_var("en_tv", $row["en_tv"]?"en_tv":"");

    $tf->set_var('new', setNewBadge($row['fecha_alta']));

    $tipo = getCategoria($db,$row['tipo']);
    $tf->set_var("tipo", $tipo);

    if (showPrices($row['tipo'])) {
        $descuento = $row["descuento"];
        $tipo = translateType($db,$row['tipo']);
        $descuento_cliente = getDiscount($db, $tipo)*100;
        $tf->set_var("descuento", $descuento);
        $tf->set_var("pvp", formatNumber($row['precio_pvp'], 2)); // precio original
        if ($descuento != null && $descuento > 0) {
            $precio_desc = ($row['precio_pvp']) - ($row['precio_pvp']) * ($descuento / 100);

            if ($row['tipo'] != 'libro') {
                $tf->set_var("precio", formatNumber($precio_desc, 2));
            } else {
                $tf->set_var("precio", formatNumber(getDiscountedPrice($precio_desc), 2));
            }
        } else {
            $tf->set_var("precio", formatNumber(getDiscountedPrice($row['precio_pvp'], $tipo), 2));
        }
    } else {
        // if not logged in
        $tf->set_var("tipo", "hidden-prices");
        $tf->set_var("descuento", 0);
        $tf->set_var("precio", '<span style="display: inline; text-decoration:none;">Ingrese para ver los precios</span>');
        $tf->set_var("pvp", formatNumber(0, 2)); // precio original
    }

    set_imagen($db, $tf, $row, 'img');

}

function set_imagen($db, $tf, $row, $nombre)
{
    global $path_fotos;

    $query  = "SELECT * FROM media WHERE producto_id = " . $row['id'];

    if ($row['portada']) {
        $query .= " AND media_id = " . $row['portada'];
    }

    $query .= " LIMIT 1";

    $result = mysqli_query($db, $query);
    $res    = mysqli_fetch_array($result);

    $path = $path_fotos . getNumberType($row['tipo']) . '/' . $row['id'] . '/';

    if (file_exists($path . 'thumb-' . $res['nombre'])) {
        $value = $res['path_thumb'];
    }
    elseif (file_exists($path . $res['nombre'])) {
        $value = $res['path'];
    }
    else {
        $value = $res['path'];
    }

    $tf->set_var($nombre, $value);
}

function set_imagenes($db, $tf, $row, $nombre)
{
    $query  = "SELECT path FROM media WHERE producto_id =" . $row['id'];
    $result = mysqli_query($db, $query);
    $tf->set_block("pl", "imagenesProducto", "_imagenesProducto");
    $tf->set_block("pl", "imagenesThumb", "_imagenesThumb");
    while ($row = mysqli_fetch_array($result)) {
        $tf->set_var($nombre, $row['path']);
        $tf->parse("_imagenesProducto", "imagenesProducto", true);
        $tf->parse("_imagenesThumb", "imagenesThumb", true);
    }
}

function registro_cliente($db, $t)
{
    if (validateCaptcha($_POST['token'], $_POST['action'])) {
        $t->set_var('base_url', HOST);
        $nombre           = $_POST['nombre'];
        $apellido         = $_POST['apellido'];
        $telefono         = $_POST['telefono'];
        $email            = $_POST['email'];
        $password         = $_POST['password'];
        $password_confirm = $_POST['password-confirm'];
        $provincia        = $_POST['provincia'];
        $ciudad           = $_POST['ciudad'];
        $sql              = "SELECT id FROM clientes WHERE email = '" . $email . "'";
        $query            = mysqli_query($db, $sql);
        $myrow            = mysqli_fetch_array($query);
        $insert           = false;
        $t->set_var('nombre', '');
        $t->set_var('apellido', '');
        $t->set_var('email', '');
        error_log(date('h:i:s').' Registro_Cliente - Usuario [Nombre: : '.$nombre.', Apellido: '.$apellido.', email: '.$email."]\n",3,"./logs/error_".date("Y-m-d ").".log");   
        if ($myrow === null) {
            if ($password == $password_confirm) {
                $pw_encriptada = encriptar_pass($password);
                $query         = "INSERT INTO clientes (nombre, apellido, telefono, email, password, provincia_id, ciudad_id, observaciones)
                    VALUES ('$nombre', '$apellido', '$telefono', '$email', '$pw_encriptada', '$provincia', '$ciudad', '')";

                $insert = mysqli_query($db, $query);
            }
        }

        if ($insert) {
            sendWhatsAppNewUser(mysqli_insert_id($db));

            if (isset($_POST['newsletter'])) {
                addToNewsletter($email);
            }

            $email_ = $_POST["email"];
            $email  = $_POST["email"];
            error_log(date('h:i:s').' Registro_Cliente Se creo el nuevo cliente correctamente: [email:  '.$email."]\n",3,"./logs/error_".date("Y-m-d ").".log");
            if (filter_var($email_, FILTER_VALIDATE_EMAIL)) {
                $to        = $email_;
                $subject   = "Registración - Didácticos del Sur";
                $preferred = $nombre;
                $message   = "Su cuenta se ha registrado correctamente. Uno de nuestros agentes se encuentra revisando toda la información. Recibirá un nuevo correo cuando la cuenta se encuentre habilitada.";

                sendEmail($to, $subject, $preferred, $message, null, 'registro');

                $t->set_var("mensaje", "El correo se ha enviado correctamente. ¡Muchas gracias!");
            } else {
                $t->set_var("error_mail", "Hubo un error al enviar el correo. Por favor, intente nuevamente.");
            }

            $to        = 'info@didacticosdelsur.com';
            $subject   = "Registración de Usuario";
            $preferred = 'Didácticos del Sur';
            $message   = "Se ha registrado un nuevo usuario.<br><br>
                                El usuario $nombre $apellido ($email) se encuentra pendiente de validación.";

            sendEmail($to, $subject, $preferred, $message);
            header("Location: " . HOST . "confirmRegister");
        } else {
            $t->set_file("pl", "registro.html");
            $sql   = "SELECT * FROM provincias";
            $query = mysqli_query($db, $sql);
            $t->set_block("pl", "provincia", "_provincia");
            $t->set_var('nombre', $nombre);
            $t->set_var('apellido', $apellido);
            $t->set_var('telefono', $telefono);
            $t->set_var('email', $email);

            while ($row = mysqli_fetch_array($query)) {
                $t->set_var("nombre_provincia", $row['nombre']);
                $t->set_var("id", $row['id']);
                $t->parse("_provincia", "provincia", true);
            }
            error_log(date('h:i:s').' Registro_Cliente Error al intentar crear el nuevo usuario: [datos:  '.json_encode($_POST)."]\n",3,"./logs/error_".date("Y-m-d ").".log");

            $t->set_var("error", 'Hubo un error. Por favor, verifique si el correo electrónico ya se encuentra en uso, o si las contraseñas coinciden. Por favor, verifique los datos ingresados o vuelva a intentar más tarde. Si el problema persiste, comuníquese con nosotros.');

        }
    } else {
        $t->set_file("pl", "registro.html");
        $sql   = "SELECT * FROM provincias";
        $query = mysqli_query($db, $sql);
        $t->set_block("pl", "provincia", "_provincia");
        $t->set_var('nombre', $nombre);
        $t->set_var('apellido', $apellido);
        $t->set_var('email', $email);

        while ($row = mysqli_fetch_array($query)) {
            $t->set_var("nombre_provincia", $row['nombre']);
            $t->set_var("id", $row['id']);
            $t->parse("_provincia", "provincia", true);
        }

        $t->set_var('error', 'Captcha inválido. Por favor, vuelva a intentarlo.');
    }

}

function registro_cliente_carrito($db, $t)
{//VG: Refactor Categorias 26/02/24
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "resumenCompra.html");
    $nombre    = $_POST['nombre'];
    $apellido  = $_POST['apellido'];
    $email     = $_POST['email'];
    $password  = $_POST['password'];
    $provincia = $_POST['provincia-registro'];
    $ciudad    = $_POST['ciudad-registro'];
    $sql       = "SELECT * FROM clientes WHERE email = '$email' LIMIT 1";
    $query     = mysqli_query($db, $sql);
    $myrow     = mysqli_fetch_array($query);
    $t->set_block("pl", "provincia", "_provincia");
    $sql            = "SELECT * FROM provincias";
    $queryprovincia = mysqli_query($db, $sql);
    while ($row = mysqli_fetch_array($queryprovincia)) {
        $t->set_var("nombre_provincia", $row['nombre']);
        $t->set_var("id", $row['id']);
        $t->parse("_provincia", "provincia", true);
    }
    $queryprovincia = mysqli_query($db, $sql);
    $t->set_block("pl", "provincia-registro", "_provincia-registro");
    while ($row = mysqli_fetch_array($queryprovincia)) {
        $t->set_var("nombre_provincia_registro", $row['nombre']);
        $t->set_var("id_registro", $row['id']);
        $t->parse("_provincia-registro", "provincia-registro", true);
    }
    pintar_cardCompra($db, $t, PATH, false);
    $insert = false;
    if (count($myrow['id']) == 0) {
        $pw_encriptada = encriptar_pass($password);
        $query         = "SELECT * FROM clientes WHERE email = '" . $email . "' AND borrado IS NULL LIMIT 1";
        /* ligar parámetros para marcadores */
        $result = mysqli_query($db, $query);
        $myrow  = mysqli_fetch_array($result);
        if ($myrow == null) {
            $query = "INSERT INTO clientes (nombre,apellido,email,password,provincia_id,ciudad_id, observaciones)
                VALUES ('$nombre','$apellido','$email','$pw_encriptada','$provincia','$ciudad','')";
            mysqli_query($db, $query);
            $insert = true;
        }
        //TODO si el descuento de la categoria la guardo en la tabla de tipos deberia agregar la logica aca porque no deberian tener valores por defecto en la tabla clientes
    } else {
        $t->set_var("error", 'Hubo un error, por favor intente nuevamente');
    }

    if ($_SESSION["usuario"] == "") {
        $query = "SELECT * FROM clientes WHERE email = '" . $email . "' AND borrado IS NULL LIMIT 1";
        /* ligar parámetros para marcadores */
        $result = mysqli_query($db, $query);
        $myrow  = mysqli_fetch_array($result);
        if ($insert) {
            if ($myrow != null) {
                if ($myrow['verficado'] == 1) {
                    //Nota (VG: 26/02/24): Nunca se da este caso
                    if ((desencriptar_pass($myrow['password'])) == $password) {
                        //save user info in session
                        $_SESSION["usuario"]        = true;
                        $_SESSION["nombre_usuario"] = $myrow["nombre"] . " " . $myrow["apellido"];
                        $_SESSION["email_cliente"]  = $myrow["email"];
                        $_SESSION["id_cliente"]     = $myrow["id"];
                        $t->set_var("usuario_logueado", "style='display:none;'");
                        $t->set_var("usuario_no_logueado", "");
                        pintar_cardCompra($db, $t, PATH, false);
                        header("Location: " . HOST . "resumenCompra");
                    }
                } else {
                    $t->set_var("error", 'Su cuenta se ha registrado correctamente. Uno de nuestros agentes se encuentra revisando toda la información. Recibirá un nuevo correo cuando la cuenta se encuentre habilitada.');
                    $t->set_var("usuario_logueado", "");
                    $t->set_var("usuario_no_logueado", "");
                    pintar_cardCompra($db, $t, PATH, false);

                }
            } else {
                $t->set_var("error", 'Hubo un error, por favor intente nuevamente');
                $t->set_var("usuario_logueado", "");
                $t->set_var("usuario_no_logueado", "");
                pintar_cardCompra($db, $t, PATH, false);
            }
        } else {
            $t->set_var("error", "El correo electrónico ya se encuentra registrado");
            pintar_cardCompra($db, $t, PATH, false);
        }
    }
}

function encriptar_pass($pass)
{
    $secret_key     = './1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $secret_iv      = './1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $encrypt_method = "AES-256-CBC";
    $key            = hash('sha256', $secret_key);
    $iv             = substr(hash('sha256', $secret_iv), 0, 16);
    $pw_encriptada  = base64_encode(openssl_encrypt($pass, $encrypt_method, $key, 0, $iv));
    return $pw_encriptada;
}

function desencriptar_pass($pass)
{
    $secret_key       = './1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $secret_iv        = './1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $encrypt_method   = "AES-256-CBC";
    $key              = hash('sha256', $secret_key);
    $iv               = substr(hash('sha256', $secret_iv), 0, 16);
    $pw_desencriptada = openssl_decrypt(base64_decode($pass), $encrypt_method, $key, 0, $iv);
    return $pw_desencriptada;
}

function contacto($t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "contacto.html");
    if (isset($_GET["ok"])) {
        if ($_GET["ok"] == 1) {
            $t->set_var("mensaje_ok", "<p style='text-align: center; color:green;'>Gracias por contactarte con Didácticos del Sur, en breve nos pondremos en contacto contigo.</p>");
        } else {
            $t->set_var("mensaje_ok", "<p style='text-align: center; color:red;'>Tu mensaje no se ha podido entregar, verifica los datos ingresados.</p>");
        }

    } else {
        $t->set_var("mensaje_ok", "");
    }
}

function recuperar_pw($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "olvide.html");
    $email_ = $_POST["email"];
    $email  = $_POST["email"];

    $query = "SELECT * FROM clientes WHERE email= '$email'";
    $sql   = mysqli_query($db, $query);
    $row   = mysqli_fetch_array($sql);
    $pw    = desencriptar_pass($row['password']);
    error_log(date('h:i:s').' recuperar_pw: [email:  '.$email."]\n",3,"./logs/error_".date("Y-m-d ").".log");


    if (filter_var($email_, FILTER_VALIDATE_EMAIL) && !empty($row)) {
        $to        = $email_;
        $subject   = "Recuperar contraseña";
        $preferred = $row['nombre'];
        $message   = "Su contraseña es: " . $pw;

        sendEmail($to, $subject, $preferred, $message);
        $t->set_var("mensaje", "El correo se ha enviado correctamente. Si el mismo no llega, como primer paso busque en Correo no deseado o SPAM. De no encontrarlo, comuníquese con nosotros a info@didacticosdelsur.com. ¡Muchas gracias!");
    } else {
        $t->set_var("error", "Hubo un error al enviar el correo. Por favor, intente nuevamente.");
    }

}

function pintar_header($db, $t, $templates)
{
    $t->set_var('base_url', HOST);

    global $env;
    if ($env == 'production') {
        $t->set_var('gtm', '<!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-50P7LL1BX2"></script>
    <script async src="/assets/js/gtag.js"></script>');
    }

    $tf = new Template($templates, "remove");
    $tf->set_var('base_url', HOST);
    if (showTopbar()) {
        setTopbar($tf);
    }
    $tf->set_file("pl", "header.html");
    pintar_menu_categorias($db,$tf);
    $header = $tf->parse("MAIN", "pl");
    $t->set_var("header", $header);

    if ($_SESSION["usuario"] == "") {
        $t->set_var("logeado_cart", "style='display:none;'");
        $t->set_var("is_logged", "");

    } else {
        $t->set_var("user_name_menu", $_SESSION["nombre_usuario"]);
        $t->set_var("logeado_sesion", "style='display:none;'");
        $t->set_var("is_logged", "is_logged");
    }

    $cant_productos = cantidad_productos_carrito($db); //VG: Refactor Categorias
    if (isset($_SESSION['CART']) and $_SESSION['CART'] != "") {
        $t->set_var("cant", $cant_productos);
    } else {
        $t->set_var("cant", '0');
    }
    if ($cant_productos > 0) {
        $t->set_var("showCartCount", "style=''");
    } else {
        $t->set_var("showCartCount", "style='display: none;'");
    }
}

function pintar_menu_categorias($db, $t){
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "header.html");
    $t->set_block("pl", "menu-categoria", "_menu-categoria");
    $t->set_block("pl", "menu-mobile-categoria", "_menu-mobile-categoria");

    $tipo_categorias = getTipoCategorias($db);
    
    foreach ($tipo_categorias as $i => $value) {
        $t->set_var("link_categoria", slugify($value["link"]));
        $t->set_var("lc_categoria", $value["categoria"]); 
        $t->set_var('estilo_c', "class= 'menu menu-".$value['tipo']."'");
        $t->set_var('icon_c', "class= '".$value['icon']."'");

        $t->parse("_menu-categoria", "menu-categoria", true);
        $t->parse("_menu-mobile-categoria", "menu-mobile-categoria", true);
    } 
}

function pintar_footer($t, $templates)
{
    $t->set_var('base_url', HOST);
    $tf = new Template($templates, "remove");
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "footer.html");
    $footer = $tf->parse("MAIN", "pl");
    $t->set_var("footer", $footer);
    $t->set_var("year", date('Y'));
    if ($_SESSION["usuario"] != "") {
        $t->set_var("logeado", "style='display:none;'");
    }

    $tf = new Template($templates, "remove");
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "footer_scripts.html");
    $footer = $tf->parse("MAIN", "pl");
    $t->set_var("footer_scripts", $footer);
}

function listar_ciudades($db, $provincia)
{

    $query   = "SELECT * FROM localidades WHERE provincia_id ='" . $provincia . "'";
    $result  = mysqli_query($db, $query);
    $payload = array();
    while ($row = mysqli_fetch_array($result)) {
        $payload[] = array(
            'id'            => $row['id'],
            //'nombre'        => $row['nombre'],
            'nombre'        => utf8_encode($row['nombre']),
            'codigo_postal' => $row['codigo_postal'],
        );
    }
    $obj = json_encode($payload);
    echo $obj;
    die;
}



function slugify($text, string $divider = '-')
{
    $unwanted_array = ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'Ñ' => 'N'];
    $str            = strtr($text, $unwanted_array);

    $slug = strtolower(trim(preg_replace('/[\s-]+/', $divider, preg_replace('/[^A-Za-z0-9-]+/', $divider, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $divider));
    return $slug;

    // replace non letter or digits by divider
    /*$text = preg_replace('~[^\pL\d]+~u', $divider, $text);

    // transliterate
    //$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = preg_replace('~[á]+~', 'a', $text);
    $text = preg_replace('~[é]+~', 'e', $text);
    $text = preg_replace('~[í]+~', 'i', $text);
    $text = preg_replace('~[ó]+~', 'o', $text);
    $text = preg_replace('~[ú]+~', 'u', $text);
    $text = preg_replace('~[ñ]+~', 'n', $text);

    // trim
    $text = trim($text, $divider);

    // remove duplicate divider
    $text = preg_replace('~-+~', $divider, $text);

    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
    return 'n-a';
    }

    return $text;*/
}

function getLimitPage(){
    return 50; //Limite de productos por página
}

function stripAccents($str)
{
    return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

function getMinimum()
{
    global $db;

    $query  = "SELECT option_value FROM opciones WHERE option_name = 'minimo' LIMIT 1";
    $result = mysqli_query($db, $query);

    $row = mysqli_fetch_array($result);

    return (int) $row['option_value'];
}

function showPrices($type)
{
    return userLogged() || showProductPrices() || ($type == 'libro' && showBookPrices());
}

function showProductPrices()
{
    global $db;

    $query  = "SELECT option_value FROM opciones WHERE option_name = 'mostrar_precios' LIMIT 1";
    $result = mysqli_query($db, $query);

    $row = mysqli_fetch_array($result);

    return (bool) $row['option_value'];
}

function showBookPrices()
{
    global $db;

    $query  = "SELECT option_value FROM opciones WHERE option_name = 'mostrar_precios_libros' LIMIT 1";
    $result = mysqli_query($db, $query);

    $row = mysqli_fetch_array($result);

    return (bool) $row['option_value'];
}

function showTopbar()
{
    global $db;

    $query  = "SELECT option_value FROM opciones WHERE option_name = 'topbar_is_active' LIMIT 1";
    $result = mysqli_query($db, $query);

    $row = mysqli_fetch_array($result);

    return (bool) $row['option_value'];
}

function getTopbarContent()
{
    global $db;

    $query  = "SELECT option_value FROM opciones WHERE option_name = 'topbar_content' LIMIT 1";
    $result = mysqli_query($db, $query);

    $row = mysqli_fetch_array($result);

    return $row['option_value'];
}

function getOption($option)
{
    global $db;

    $query  = "SELECT option_value FROM opciones WHERE option_name = '$option' LIMIT 1";
    $result = mysqli_query($db, $query);

    $row = mysqli_fetch_array($result);

    return $row['option_value'];
}

function getTematica(){
    global $db;

    $query = "SELECT t.nombre_clase FROM opciones o LEFT JOIN tematica_estilos t ON t.id = o.option_value WHERE option_name = 'tematica'";
    $result = mysqli_query($db, $query);

    $row = mysqli_fetch_array($result);

    return $row['nombre_clase'];
}

function formatNumber($number, $decimals)
{
    return number_format($number, $decimals, ',', '.');
}

function userLogged()
{
    return $_SESSION["usuario"];
}

function getLink($product)
{
    return HOST . 'producto/' . $product['id'] . '-' . slugify($product['nombre']);
}

function setLastLoginToNow($id)
{
    global $db;

    $query = "UPDATE clientes SET last_login = NOW() WHERE id = '$id'";
    mysqli_query($db, $query);
}

function setLastLoginToSellersNow($id)
{
    global $db;

    $query = "UPDATE vendedores SET last_login = NOW() WHERE id = '$id'";
    mysqli_query($db, $query);
}

function validateCaptcha($token, $action)
{
    //return true;
    // call curl to POST request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('secret' => RECAPTCHA_V3_SECRET_KEY, 'response' => $token)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $arrResponse = json_decode($response, true);

    // verify the response
    if ($arrResponse["success"] == '1' && $arrResponse["action"] == $action && $arrResponse["score"] >= 0.5) {
        // valid submission
        return true;
    } else {
        // spam submission
        return false;
    }
}

function setNewBadge($date)
{
    return (date_diff(date_create(date('Y-m-d H:i:s')), date_create($date))->format('%a') <= 60) ? "new" : "";
}

function showDatosViajante($t, $user_id)
{

    global $db;

    $query = "SELECT c.vendedor_id FROM clientes c WHERE c.borrado IS NULL AND c.id=" . $user_id;
    $sql   = mysqli_query($db, $query);
    $row   = mysqli_fetch_array($sql);

    if ($row['vendedor_id'] != null) {
        $query = "SELECT nombre, apellido, email, telefono FROM vendedores WHERE id=" . $row['vendedor_id'];
        $sql   = mysqli_query($db, $query);
        $row   = mysqli_fetch_array($sql);
        $t->set_var("nombre_viajante", $row['nombre']);
        $t->set_var("apellido_viajante", $row['apellido']);
        $t->set_var("email_viajante", $row['email']);
        $t->set_var("telefono_viajante", $row['telefono']);
    } else {
        $t->set_var("hiddeCard", "style='display:none;'");
    }
}

function userIsLogged()
{
    return $_SESSION["usuario"] != "";
}


function addToNewsletter($email)
{
    global $db;

    $query = "SELECT * FROM newsletter WHERE email = '$email' LIMIT 1";

    $sql   = mysqli_query($db, $query);
    $row   = mysqli_fetch_array($sql);

    if (!$row) {
        $query = "INSERT INTO newsletter (email) VALUES('$email');";
        error_log($query);
        $sql   = mysqli_query($db, $query);
        //$row   = mysqli_fetch_array($sql);
        return true;
    }

    return false;

}

function isInNewsletter($email)
{
    global $db;

    $query = "SELECT * FROM newsletter WHERE email = '$email' LIMIT 1";

    $sql   = mysqli_query($db, $query);
    $row   = mysqli_fetch_array($sql);

    return $row != null;
}

function removeFromNewsletter($email)
{
    global $db;

    $query = "DELETE FROM newsletter WHERE email = '$email' LIMIT 1";

    $sql = mysqli_query($db, $query);
}

function getNumberType($tipo) { //TODO
    switch ($tipo) {
        case "libro":
            return "01";
        case "juego":
            return "02";
        default:
            return "03";
    }
}

function getEmailViajante($client_id) {
    global $db;

    $query = "SELECT v.email AS email FROM clientes c INNER JOIN vendedores v ON c.vendedor_id = v.id WHERE c.id = $client_id LIMIT 1";

    $sql = mysqli_query($db, $query);

    $row   = mysqli_fetch_array($sql);

    return ($row != null)?$row['email']:null;
}

function showPanelViajante($vendedor_id) {
    global $db, $t;
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "panelVendedor.html");
    $t->set_var("user", $_SESSION["nombre_usuario"]);

    $query = "SELECT v.nombre, v.apellido, v.email FROM vendedores v WHERE v.borrado IS NULL AND v.id = $vendedor_id";
    $sql   = mysqli_query($db, $query);
    $row   = mysqli_fetch_array($sql);
    $t->set_var("nombre", $row[0]);
    $t->set_var("apellido", $row["apellido"]);
    $t->set_var("email", $row["email"]);

    showSidebarPanelSeller($vendedor_id);
}

function showSidebarPanelSeller($vendedor_id) {
    global $db, $t;

    $sql   = "SELECT * FROM clientes WHERE borrado IS null and vendedor_id = $vendedor_id";
    $query = mysqli_query($db, $sql);

    $form = '<div class="cardProfile">
                    <div class="data" style="width: 100%;">
                        <p>Operar en nombre de:</p>
                        <form action="' . HOST . 'cambiarUsuario" method="POST">
                            <select name="user" id="" style="width:100%; appearance: auto; padding: 5px; margin-bottom: 10px;">';

    while ($row = mysqli_fetch_array($query)) {
        $selected = ($_SESSION['id_cliente'] == $row['id'])?"selected":"";
        $form .=                '<option value="' . $row['id'] . '"' . $selected . '>' . $row['nombre'] . ' ' . $row['apellido'] . '</option>';
    }

    $form .=                '</select>
                            <button class="button-rounded" type="submit">Aplicar</button>
                        </form>
                    </div>
                </div>';

    $t->set_var('panel_vendedor', $form);
}

function changeUserTo($id) {
    global $db;
    if (!isSeller()) {
        header("Location: " . HOST . "home");
    }

    $_SESSION['id_cliente'] = $id;

    $query = "SELECT * FROM clientes WHERE borrado IS NULL and id = $id LIMIT 1";
    $result = mysqli_query($db, $query);
    $myrow  = mysqli_fetch_array($result);

    $_SESSION["nombre_usuario"] = $_SESSION['nombre_vendedor'] . ' (' . $myrow['apellido'] . ', ' . $myrow['nombre'] . ')';

    $_SESSION["email_cliente"]  = $myrow["email"];

    header("Location: " . HOST . "categorias/libros");
}

function isSeller() {
    return isset($_SESSION['id_vendedor']) && !empty($_SESSION['id_vendedor']);
}

function sellerLoggedAsUser() {
    return !empty($_SESSION['id_cliente']);
}

function saveSeller()
{
    global $db, $t;
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "panelVendedor.html");
    $name     = $_POST["nombre"];
    $lastname = $_POST["apellido"];
    $email    = $_POST["email"];

    $query    = "UPDATE vendedores SET nombre = '$name', apellido = '$lastname', email = '$email' WHERE id = " . $_SESSION["id_vendedor"];
    if (mysqli_query($db, $query)) {
        $_SESSION["nombre_vendedor"] = $name;
    }

    header("Location: " . HOST . "miCuenta");
}

function showSellerOrders() {
    global $db, $t;

    $t->set_var('base_url', HOST);
    $t->set_file("pl", "miCuentaMisPedidos.html");
    $t->set_var("user", $_SESSION["nombre_usuario"]);
    $t->set_var("hiddeCard", "style='display:none;'");

    showSidebarPanelSeller($_SESSION['id_vendedor']);

    paintOrdersFromSeller($db, $t);
}

function paintOrdersFromSeller()
{
    global $db, $t;

    $tf = new Template(PATH, "remove");
    $t->set_var('base_url', HOST);
    //$tf->set_file("pl", "cardPedidos.html");
    $t->set_block("pl", "pedidos", "_pedidos");

    $sql   = "SELECT *, p.id AS id, p.vendedor_id AS vendedor_id FROM pedidos p
    INNER JOIN clientes c ON c.id =  p.cliente_id WHERE c.vendedor_id =" . $_SESSION["id_vendedor"];

    $query = mysqli_query($db, $sql);
    while ($row = mysqli_fetch_array($query)) {
        set_pedido($t, $row);
        $t->set_var("nombre", $row['nombre'] . ' ' . $row['apellido']);
        //$pedido = $tf->parse("MAIN", "pl");
        //$t->set_var("pedido", $pedido);
        $t->parse("_pedidos", "pedidos", true);
    }
}

function pageIs($id) {
    global $t;

    return str_contains($t->file['pl'], $id);
}


if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}

function setTopbar($tf) {
    $topbar = '
    <div class="topbar ' . getOption('topbar_classes') . '">
        <marquee loop="infinite" behavior="scroll">' . getTopbarContent() . '</marquee>
    </div>';

    $tf->set_var('topbar', $topbar);
}

function canAccessOrder($id) {
    global $db;

    $query = "SELECT *, c.vendedor_id AS cliente_vendedor_id FROM pedidos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    WHERE p.id = '$id' LIMIT 1";
    $result = mysqli_query($db, $query);
    $pedido  = mysqli_fetch_array($result);
    //echo ("<br><br><br><br>cliente: " . $pedido['cliente_id'] .'<br>');
    //echo ("vendedor cliente: " . $pedido['cliente_vendedor_id'] . '<br>');
    //echo ("vendedor pedido: " . $pedido['vendedor_id'] . '<br>');
    //echo ("session id cliente: " . $_SESSION['id_cliente'] . '<br>');
    //echo ("session id vendedor: " . $_SESSION['id_vendedor'] . '<br>');

    return
        ($pedido['cliente_id'] == $_SESSION['id_cliente']) || // es un pedido mio
        (!empty($_SESSION['id_vendedor']) && $pedido['vendedor_id'] == $_SESSION['id_vendedor']) ||  // o soy vendedor del pedido
        (!empty($_SESSION['id_vendedor']) && $pedido['cliente_vendedor_id'] == $_SESSION['id_vendedor']);  // o soy vendedor del cliente
}

function getIPAddress() {
    //whether ip is from the share internet
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    //whether ip is from the proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    //whether ip is from the remote address
    else{
            $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function setIP($id, $ip)
{
    global $db;

    $query = "UPDATE clientes SET last_ip = '$ip' WHERE id = '$id'";
    mysqli_query($db, $query);
}
