<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);
date_default_timezone_set('America/Argentina/Buenos_Aires');
header('Content-Type: text/html; charset=UTF-8');
// server should keep session data for AT LEAST 12 hour
ini_set('session.gc_maxlifetime', 60*60*12);

// each client should remember their session id for EXACTLY 12 hour
session_set_cookie_params(60*60*12);

//Inicio de Session
session_start();

//INCLUDES VARIS

include "includes/template.php";
include "includes/config.php";

// error_reporting(E_ALL);

$db = mysqli_connect($mysql_host, $mysql_username, $mysql_passwd, $mysql_database);

// Check connection
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}
else {
    mysqli_query($db, "SET time_zone = '-3:00'");
}
mysqli_set_charset($db,"utf8");

include_once "includes/functions/categorias.php";
include_once "includes/functions/buscador_interno.php";

// initialize empty cart
if (!isset($_SESSION['CART']) || $_SESSION['CART'] == "") {
    $_SESSION['CART'] = [];
    $categorias = getTipoCategorias($db);
    foreach ($categorias as $categoria) {
        $_SESSION['CART'][$categoria['tipo']] = [];
    }
}

$t = new Template($templates, "remove");

include "includes/funciones.php";

//ruteo

$path = ltrim($_SERVER['REQUEST_URI'] . "", '/'); // Trim leading slash(es)

$url = explode("/", $_SERVER['QUERY_STRING']);

$elements = explode('/', $path);
if ($elements[0] == "didacticos" || $elements[0] == "staging") {
    $arr = array_shift($elements);
}

// Split path on slashes
if (empty($elements[0])) {
    // No path elements means home
    mostrar_home($db, $t);
} else {
    if ($elements[0]==='dds') {
        //fix para probar en subcarpetas en servidor
        array_shift($elements);
    }
    switch ($elements[0]) {
        case "home":
            mostrar_home($db, $t);
            break;
        case "registro":
            error_log(date('h:i:s').' registro '."\n",3,"./logs/error_".date("Y-m-d ").".log");   
            mostrar_registro($db, $t);
            break;
        case "terminos-condiciones":
            showTerms();
            break;
        case "login":
            if (userIsLogged()) {
                header("Location: " . HOST . "miCuenta");
            }
            else {
                mostrar_login($t);
            }
            break;
        case "olvide":
            mostrar_olvide($t);
            break;
        case "categorias":
            //id_categoria
            $page = $elements[2] ? $elements[2] : 1;
            mostrar_categorias($db, $t, $elements[1],$page);
            break;
        case "tags":
            $slug = explode("-", $elements[1]);
            $id   = $slug[0];
            $page = $elements[2] ? $elements[2] : 1;
            showTag($id,$page);
            break;
        case "carritoCompras":
            if (userIsLogged()) {
                mostrar_carritoCompras($db, $t);
            }
            else {
                mostrar_login($t);
            }            
            break;
        case "producto":
            //id_producto
            $slug = explode("-", $elements[1]);
            $id   = urldecode($slug[0]);
            mostrar_productoAmpliado($db, $t, $id);
            break;
        case "loginUsuario":
            if (userIsLogged()) {
                header("Location: " . HOST . "miCuenta");
            }
            else {
                login_usuario($db, $t);
            }
            break;
        case "registroCliente":
            error_log(date('h:i:s').' registroCliente '."\n",3,"./logs/error_".date("Y-m-d ").".log");   
            registro_cliente($db, $t);
            break;
        case "agregarCarrito":
            error_log(date('h:i:s'). ' En el index antes de agregar carrito [elements: '.json_encode($elements)."]\n",3,"./logs/error_".date("Y-m-d ").".log");
            agregar_carrito($db, $t, $elements[1], $elements[2], $elements[3]);
            break;
        case "actualizarCarrito":
            modificar_carrito($db, $t, $elements[1], $elements[2]);
            break;
        case "actualizarObservaciones":
            setComments($elements[1], $elements[2]);
            break;
        case "eliminarCarrito":
            eliminar_carrito($db, $t, $elements[1]);
            break;
        case "enviarPedido":
            //total pedido
            if (userIsLogged()) {
                enviar_pedido($db, $t);
            }
            else {
                mostrar_login($t);
            }
            break;
        case "ciudades":
            //id_provincia
            listar_ciudades($db, $elements[1]);
            break;
        case "recuperarPass":
            recuperar_pw($db, $t);
            break;
        case "buscar":
            // buscar/id_producto
            if ($elements[1] != ''){
                buscar_producto($db, $t, urldecode($elements[1]));
            } else {
                mostrar_home($db, $t);
            }
            break;
        case "buscaDesdeAdmin":
            buscar_producto($db, $t, urldecode($elements[1]),null, true);
            break;
        case "resumenCompra":
            mostrar_resumen_compra($db, $t);
            break;
        case "anuncios":
            if (userIsLogged()) {
                showAnnouncements();
            }
            else {
                mostrar_login($t);
            }
            break;
        case "miCuenta":
            if (userIsLogged()) {
                mostrar_micuenta($db, $t);
            }
            else {
                mostrar_login($t);
            }
            break;
        case "catalogos":
            if (userIsLogged()) {
                showCatalogs();
            }
            else {
                mostrar_login($t);
            }
            break;
        case "miCuentaMisPedidos":
            if (userIsLogged()) {
                mostrar_micuenta_mispedidos($db, $t);
            }
            else {
                mostrar_login($t);
            }
            break;
        case "miCuentaPassword":
            if (userIsLogged()) {
                mostrar_micuenta_password($db, $t);
            }
            else {
                mostrar_login($t);
            }
            break;
        case "confirmacionPedido":
            if (userIsLogged()) {
                mostrar_confirmacion_pedido($db, $t);
            }
            else {
                mostrar_login($t);
            }
            break;
        case "guardarDatos":
            if (userIsLogged()) {
                guardar_datos($db, $t);
            }
            else {
                mostrar_login($t);
            }
            break;
        case "guardarDatosVendedor":
            if (userIsLogged()) {
                saveSeller();
            }
            else {
                mostrar_login($t);
            }
            break;
        case "guardarPassword":
            if (userIsLogged()) {
                guardar_pw($db, $t);
            }
            else {
                mostrar_login($t);
            }
            break;
        case "registroClienteCarrito":
            error_log(date('h:i:s').' registroClienteCarrito \n',3,"./logs/error_".date("Y-m-d ").".log");   
            registro_cliente_carrito($db, $t);
            break;
        case "loginUsuarioCarrito":
            if (userIsLogged()) {
                header("Location: " . HOST . "miCuenta");
            }
            else {
                login_usuario_carrito($db, $t);
            }
            break;
        case "marca":
            $page = $elements[2] ? $elements[2] : 1;
            mostrar_marcas_filtradas($db, $t, $elements[1],$page);
            break;
        case "categoria":
            $page = $elements[2] ? $elements[2] : 1;
            mostrar_categorias_filtradas($db, $t, $elements[1],$page);
            break;
        case "obtenerCategorias":
            obtener_tipos($db);
            break;
        case "edad":
            mostrar_por_edad($db, $t, $elements[1]);
            break;
        case "logout":
            if (userIsLogged()) {
                logout($db);
            }
            else {
                mostrar_login($t);
            }
            break;
        case "detalle_pedido":
            if (userIsLogged()) {
                detalle_pedido($db, $t, $elements[1]);
            }
            else {
                mostrar_login($t);
            }
            break;
        case "contact":
        case "contacto":
            contact($t);
            break;
        case "contactSend":
            contactSend($t);
            break;
        case "aboutus":
            aboutus($t);
            break;
        case "faqs":
            faqs($t);
            break;
        case "como-operar":
            howTo($t);
            break;
        case "cbu":
            showBankData();
            break;
        case "confirmRegister":
            confirmRegister($t);
            break;
        case "productoAgregado":
            productoAgregado($db, $t, $elements[1]);
            break;
        case "newsletter":
            addToNewsletter($elements[1]);
            break;
        case "cambiarUsuario":
            changeUserTo($_POST['user']);
            break;
        case "searcher":
            mostrar_buscador_para_empleados($db, $t);
            break;
        case "buscar_interno":
            buscar_producto_empleados($db, $t, urldecode($elements[1]));
            break;
        default:
            mostrar_home($db, $t);
            break;
    }


    if (userIsLogged()) {
        $t->set_var("usuario", $_SESSION["nombre"]);
        $t->set_var("nombre_usuario", $_SESSION["nombre_cliente"]);
    } else {
        $t->set_var("usuario", "Login");
        $t->set_var("ocultar_sin_logear", "style='display:none'");
    }

}

if (isset($_SESSION['CART']) and $_SESSION['CART'] != "") {
    $t->set_var("cart_cant", count($_SESSION['CART']));
} else {
    $t->set_var("cart_cant", '0');
}

pintar_header($db, $t, $templates);
pintar_footer($t, $templates);

if (isSeller() && !sellerLoggedAsUser() && (pageIs("categoria") || pageIs("index"))) {
    header("Location: " . HOST . "anuncios");
}

$t->parse("MAIN", "pl");
$t->p("MAIN");
