<?php

// server should keep session data for AT LEAST 12 hour
ini_set('session.gc_maxlifetime', 60*60*12);

// each client should remember their session id for EXACTLY 12 hour
session_set_cookie_params(60*60*12);

//Inicio de Session
session_start();

//INCLUDES VARIS
include "includes/template.php";
include "includes/config.php";
include "includes/funciones.php";
include "includes/functions/carritos.php";

error_reporting(0);
//error_reporting(E_ALL);

$db = mysqli_connect($mysql_host, $mysql_username, $mysql_passwd, $mysql_database);

// Check connection
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}
else {
    mysqli_query($db, "SET time_zone = '-03:00'");
}
mysqli_set_charset($db,"utf8");

$t = new Template($templates, "remove");

$path = ltrim($_SERVER['REQUEST_URI'] . "", '/'); // Trim leading slash(es)
$url  = explode("/", $_SERVER['QUERY_STRING']);

$elements = explode('/', $path);
if ($elements[0]==='dds') {
    //fix para probar en subcarpetas en servidor
    array_shift($elements);
}
if ($elements[0] == "didacticos") {
    $arr = array_shift($elements);
}
if ($elements[0] == "staging") {
    $arr = array_shift($elements);
}
if ($elements[0] == "admin") {
    $arr = array_shift($elements);
}

$elements[0] = explode('?', $elements[0])[0]; // we remove any parameters
pintar_panel($t, "./html/", $_SESSION["super_admin"]);

if ($_SESSION["user_admin"] == "" or $_SESSION["user_admin"] == "user") {
    loguear($db, $t);
} else if ($_SESSION["user_admin"] != "user" and $_SESSION["user_admin"] != "") {
    if (empty($elements[0])) {
        mostrar_inicio($db, $t);
    } else {
        
        switch ($elements[0]) {
            case "mostrar_inicio":
                mostrar_inicio($db, $t);
                break;
            case "logout":
                logout($db);
                break;
            case "ciudades":
                listar_ciudades($db, $elements[1]);
                break;
            case "precio":
                obtener_precio_producto($db, $elements[1]);
                break;

            //PRODUCTOS
            case "productos":
                mostrar_producto($db, $t);
                break;
            case "ver_producto":
                ver_producto($db, $t);
                break;
            case "editar_producto":
                editar_producto($db, $t, $elements[1]);
                break;
            case "eliminar_producto":
                eliminar_producto($db, $elements[1]);
                break;
            case "desactivar_producto":
                desactivar_producto($db, $elements[1]);
                break;
            case "activar_producto":
                activar_producto($db, $elements[1]);
                break;
            case "guardar_producto":
                guardar_producto($db, $directorio_destino . 'productos/', $path_fotos);
                break;
            case "eliminar_imagen":
                eliminar_imagen($db, $directorio_destino  . 'productos/', $elements[1], $elements[2]);
                break;
                
            //TIPOS CATEGORIAS
            case "listar_tipo_categorias":
                listar_tipos_categorias($db, $t);
                break;
            case "editar_tipo_categoria":
                editar_tipo_categorias($db, $t, $elements[1]);
                break;
            case "guardar_tipo":
                guardar_tipo_categorias($db,$t);
                break;
            case "nuevo_tipo_categoria":
                nuevo_tipo_categoria($t, $elements[1] == 'error' ? true : false);
                break;
            case "eliminar_tipo_categoria":
                eliminar_tipo_categorias($db, $elements[1]);
                break;

            //CATEGORIAS
            case "listar_categorias":
                listar_categorias($db, $t);
                break;
            case "editar_categoria":
                editar_categoria($db, $t, $elements[1]);
                break;
            case "guardar_categoria":
                guardar_categoria($db);
                break;
            case "nueva_categoria":
                nueva_categoria($db,$t);
                break;
            case "eliminar_categoria":
                eliminar_categoria($db, $elements[1]);
                break;
            case "get_allcategories":
                get_allcategories($db, $elements[1]);
                break;

            //MARCAS
            case "listar_marcas":
                listar_marcas($db, $t);
                break;
            case "editar_marca":
                editar_marca($db, $t, $elements[1]);
                break;
            case "guardar_marca":
                guardar_marca($db, $t);
                break;
            case "nueva_marca":
                nueva_marca($db,$t);
                break;
            case "eliminar_marca":
                eliminar_marca($db, $t, $elements[1]);
                break;
            case "get_allmarcas":
                get_allmarcas($db, $elements[1]);
                break;

            //TAGS
            case "listar_tags":
                listar_tags($db, $t);
                break;
            case "editar_tag":
                editar_tag($db, $t, $elements[1]);
                break;
            case "guardar_tag":
                guardar_tag($db);
                break;
            case "nuevo_tag":
                nuevo_tag($t);
                break;
            case "eliminar_tag":
                eliminar_tag($db, $elements[1]);
                break;
            //ADD get to get all tags or all tags of type selected
            case "get_alltags":
                get_alltags($db, $elements[1]);
                break;
            //get all tags of id_producto
            case "get_tag_producto":
                get_tag_producto($db, $elements[1]);
                break;

            //RANGO DE EDADES
            case "listar_rango_edades":
                listar_rango_edades($db, $t);
                break;
            case "nuevo_rango_edad":
                nuevo_rango_edad($db,$t);
                break;
            case "guardar_rango_edad":
                guardar_rango_edad($db);
                break;            
            case "editar_rango_edad":
                editar_rango_edad($db, $t, $elements[1]);
                break;
            
            case "eliminar_rango_edad":
                eliminar_rango_edad($db, $elements[1]);
                break;

            //PEDIDOS
            case "listar_pedidos":
                listado_pedidos($db, $t);
                break;
            case "nuevo_pedido":
                createOrder();
                break;
            case "editar_pedido":
                editar_pedido($db, $t, $templates, $elements[1]);
                break;
            case "guardar_pedido":
                guardar_pedido($db, $t);
                break;
            case "agregar_producto_pedido":
                agregar_producto_pedido($db, $t, $elements[1], $elements[2]);
                break;
            case "actualizar_pedido_actual":
                actualizar_pedido_actual($db, $t, $templates, $elements[1], $elements[2]);
                break;
            case "guardar_producto_pedido":
                guardar_producto_pedido($t, $db, $templates, $elements[1], $elements[2]);
                break;
            case "eliminar_producto_pedido":
                eliminar_producto_pedido($db, $t, $templates, $elements[1]);
                break;

            //CLIENTE
            case "nuevo_cliente":
                nuevo_cliente($db, $t);
                break;
            case "listado_clientes":
                listado_clientes($db, $t);
                break;
            case "editar_cliente":
                editar_cliente($db, $t, $elements[1]);
                break;
            case "guardar_cliente":
                guardar_cliente($db, $t);
                break;
            case "guardar_nuevo_cliente":
                guardar_nuevo_cliente($db, $t);
                break;
            case "desactivar_cliente":
                desactivar_cliente($db, $elements[1]);
                break;
            case "activar_cliente":
                activar_cliente($db, $elements[1]);

            //VENDEDOR
            case "nuevo_vendedor":
                nuevo_vendedor($t);
                break;
            case "listado_vendedores":
                listado_vendedores($db, $t);
                break;
            case "editar_vendedor":
                editar_vendedor($db, $t, $elements[1]);
                break;
            case "guardar_vendedor":
                guardar_vendedor($db);
                break;
            case "guardar_nuevo_vendedor":
                guardar_nuevo_vendedor($db, $t);
                break;
            case "desactivar_vendedor":
                desactivar_vendedor($db, $elements[1]);
                break;

            // PEDIDOS
            case "imprimir_pedido":
                imprimir_pedido($db, $t, $templates, $elements[1]);
                break;
            case "detalle_pedido":
                detalle_pedido($db, $elements[1]);
                break;
            case "eliminar_pedido":
                deleteOrder($elements[1]);
                break;

            // NEWSLETTER
            case "newsletter":
                showNewsletterList();
                break;
            case "export_newsletter":
                exportNewsletterList();
                break;
            case "delete_newsletter":
                deleteEmailFromNewsletter($elements[1]);
                break;

            //ANUNCIOS
            case "listar_anuncios":
                listar_anuncios();
                break;
            case "editar_anuncio":
                editar_anuncio($elements[1]);
                break;
            case "guardar_anuncio":
                guardar_anuncio();
                break;
            case "nuevo_anuncio":
                nuevo_anuncio();
                break;
            case "eliminar_anuncio":
                eliminar_anuncio($elements[1]);
                break;

            //BÚSQUEDAS
            case "listar_busquedas":
                listar_busquedas($db, $t,$url_site);
                break;
            case "vaciar_busquedas":
                vaciar_busquedas($db);
                break;
            // OPCIONES
            case 'opciones':
                showOptions();
                break;
            case "guardar_opciones":
                saveOptions();
                break;

            //CAROUSEL
            case "banners":
                listar_carousel($db, $t);
                break;
            case "nuevo_banner":
                nuevo_banner($t);
                break;
            case "guardar_banner":
                guardar_banner($db, $directorio_banners_destino . 'banners/', $path_fotos_banner);
                break;
            case "editar_banner":
                editar_banner($db, $t, $elements[1]);
                break;
            case "eliminar_banner":
                eliminar_banner($db,$elements[1]);
                break;

            //CARRITOS
            case "listar_carritos":
                listar_carritos($db,$t);
                break;
            case "ver_carrito":
                ver_carrito($db, $t, $templates, $elements[1]);
                break;
            case "eliminar_carrito":
                eliminar_carrito($db,$elements[1]);
                break;
            case "imprimir_carrito":
                imprimir_carrito($db, $t, $templates, $elements[1]);
                break;
            case "detalle_carrito":
                detalle_carrito($db, $elements[1]);
                break;
            default:
                mostrar_inicio($db, $t);
                break;
        }
    }
}

pintar_footer($t, $templates);

$t->parse("MAIN", "pl");
$t->p("MAIN");
