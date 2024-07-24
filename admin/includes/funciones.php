<?php
const PATH = "../html/";
include_once "../includes/functions/categorias.php";
include_once 'functions/tags.php';
include_once 'functions/tipo_categorias.php';
include_once 'functions/rango_edades.php';
include_once 'functions/pedidos.php';
include_once 'functions/productos.php';
include_once 'functions/newsletter.php';
include_once 'functions/marcas.php';
include_once 'functions/anuncios.php';
include_once 'functions/opciones.php';
include_once 'functions/carousel.php';
include_once 'functions/busquedas.php';
include_once '../includes/functions/email.php';
include_once '../includes/functions/whatsapp.php';

include_once 'resize-class.php';
function mostrar_login($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "page_login.html");
    $_SESSION["user_admin"] = "user";
}

function loguear($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "page_login.html");
    $email = $_POST['email'];
    $pw    = $_POST['pw'];
    $query = "SELECT id,password,rol_admin,nombre,apellido from vendedores WHERE email = '" . $email . "' AND borrado IS NULL LIMIT 1";

    $result     = mysqli_query($db, $query);
    $pwrecibida = $pw;

    $row = mysqli_fetch_array($result);
    $t->set_var("show_error", "style='display:none;'");
    if ($row != null) {
        $pass = desencriptar_pass($row['password']);

        if ((desencriptar_pass($row['password'])) == $pwrecibida) {
            $_SESSION["user_admin"] = $email;
            $_SESSION["admin_id"]   = $row['id'];
            $_SESSION["admin_name"] = $row['nombre'] . " " . $row['apellido'];
            if ($row['rol_admin'] == 1) {
                $_SESSION["super_admin"] = true;
            } else {
                $_SESSION["super_admin"] = false;
            }
            header("Location: " . HOST . "mostrar_inicio");
            exit;
        } else {
            $t->set_file("pl", "page_login.html");
            $t->set_var("show_error", "style='display:block;'");
            $t->set_var('error', 'La contraseña ingresada es inválida');
        }
    } else if ($email != null or $pw != null) {

        $t->set_file("pl", "page_login.html");
        $t->set_var('error', 'Usuario inválido');
        $t->set_var("show_error", "style='display:block;'");

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

function mostrar_inicio($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "index.html");

    // stats: #pedidos nuevos
    $sql = "SELECT count(p.id) as total_pedidos FROM pedidos p";

    if (!$_SESSION["super_admin"]) {
        $sql .= " JOIN clientes c ON p.cliente_id = c.id";
    }

    $sql .= ' WHERE p.estado = "Nuevo"';

    if (!$_SESSION['super_admin']) {
        $sql .= " AND c.vendedor_id =" . $_SESSION['admin_id'] . " AND c.borrado is NULL";
    }

    $result_pedidos = mysqli_query($db, $sql);
    $result_pedidos = mysqli_fetch_array($result_pedidos);
    $t->set_var("pedidos", $result_pedidos['total_pedidos']);

    // stats: #clientes
    $sql             = "SELECT count(id) as total_clientes FROM clientes WHERE borrado IS NULL ";
    if (!$_SESSION["super_admin"]) {
        $sql .= "AND vendedor_id = " . $_SESSION["admin_id"];
    }
    $result_clientes = mysqli_query($db, $sql);
    $result_clientes = mysqli_fetch_array($result_clientes);
    $t->set_var("clientes", $result_clientes['total_clientes']);


    // stats: #clientes pendientes
    if ($_SESSION["super_admin"]) {
        $sql             = "SELECT count(*) as clientes_pend FROM clientes WHERE borrado IS NULL AND verficado = 0 AND tango_id IS NULL";
        $result_clientes = mysqli_query($db, $sql);
        $result_clientes = mysqli_fetch_array($result_clientes);
        $t->set_var("clientes_pend", "pendientes: " . $result_clientes['clientes_pend']);
    }

    // stats: #clientes que mas compraron
    if ($_SESSION["super_admin"]) {
        $t->set_block("pl", "compras", "_compras");

        $sql = "SELECT CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente, SUM(total) AS amount FROM clientes c
        LEFT JOIN pedidos p ON p.cliente_id = c.id
        GROUP BY nombre_cliente
        ORDER BY amount DESC
        LIMIT 10";
        $result = mysqli_query($db, $sql);
        $i = 1;
        while ($row = mysqli_fetch_array($result)) {
            $t->set_var("ranking", $i++);
            $t->set_var("name", $row['nombre_cliente']);
            $t->set_var("amount", number_format($row['amount'], 2));

            $t->parse("_compras", "compras", true);
        }
    }

    // stats: #clientes conectados
    if ($_SESSION["super_admin"]) {
        $t->set_block("pl", "conectados", "_conectados");

        $sql = "SELECT CONCAT(c.nombre, ' ', c.apellido) AS nombre, last_login, last_ip, l.nombre AS nombre_ciudad, CONCAT('cliente') as tipo FROM clientes c
        INNER JOIN localidades l ON c.ciudad_id = l.id
        UNION (SELECT CONCAT(nombre, ' ', apellido) AS nombre, last_login, CONCAT('') as  last_ip, CONCAT('') as nombre_ciudad, CONCAT('vendedor') as tipo FROM vendedores)
        ORDER BY last_login DESC
        LIMIT 10";
        $result = mysqli_query($db, $sql);

        while ($row = mysqli_fetch_array($result)) {
            $t->set_var("name", $row['nombre']);
            $t->set_var("is_seller", $row['tipo']=='vendedor'?'style="font-size: 12px; font-weight: bold;"': 'style="font-size: 12px;"');

            if (!empty($row['last_ip'])) {
                $t->set_var('ip', ' (<a href="https://www.cual-es-mi-ip.net/geolocalizar-ip-mapa?ip=' . $row['last_ip'] . '" target="_blank">' . $row['last_ip'] . '</a>)');
            }
            else {
                $t->set_var('ip', '');
            }
            $t->set_var('ciudad', $row['nombre_ciudad']);
            $t->set_var("fecha", time_elapsed_string($row['last_login'], true));

            $t->parse("_conectados", "conectados", true);
        }
    }

    // stats: #carritos in progress
    if ($_SESSION["super_admin"]) {
        $t->set_block("pl", "carritos", "_carritos");

        $sql = "SELECT CONCAT(c.nombre, ' ', c.apellido) AS nombre_comprador, l.nombre AS nombre_ciudad, car.id, DATE_FORMAT(car.fecha_ultima_actualizacion, '%d/%m/%Y %H:%i') as fecha_ultima_actualizacion FROM carritos car
        left join clientes c on c.id = car.cliente_id 
        inner join localidades l on c.ciudad_id = l.id ORDER BY car.fecha_ultima_actualizacion desc";
        $result = mysqli_query($db, $sql);

        while ($row = mysqli_fetch_array($result)) {
            $t->set_var("name", $row['nombre_comprador']);
            $t->set_var('ciudad', $row['nombre_ciudad']);
            $t->set_var('fecha', $row['fecha_ultima_actualizacion']);
            $t->set_var('id_carrito',$row['id']);
            $t->parse("_carritos", "carritos", true);
        }
    }

    // stats: #vendedores
    $sql               = "SELECT count(id) as total_vendedores FROM vendedores WHERE rol_admin = 0 AND borrado IS NULL ";
    $result_vendedores = mysqli_query($db, $sql);
    $result_vendedores = mysqli_fetch_array($result_vendedores);
    $t->set_var("vendedores", $result_vendedores['total_vendedores']);

    // stats: #productos
    $sql              = "SELECT count(id) as total_productos FROM productos WHERE borrado IS NULL ";
    $result_productos = mysqli_query($db, $sql);
    $result_productos = mysqli_fetch_array($result_productos);
    $t->set_var("productos", $result_productos['total_productos']);

    // stats: #productos disponibles
    $sql .= " AND estado = 'Disponible'";
    $result_productos = mysqli_query($db, $sql);
    $result_productos = mysqli_fetch_array($result_productos);
    $t->set_var("productos_disponibles", $result_productos['total_productos']);

    $t->set_var("user", $_SESSION["admin_name"]);
}

function logout($db)
{
    $_SESSION["user_admin"] = "";
    $_SESSION['admin_id'] = "";
    $_SESSION['admin_name'] = "";
    $_SESSION["super_admin"]  = false;
    $_SESSION['PEDIDO_ACTUAL']       = "";
    header("location: " . HOST . "mostrar_login");
    exit;
}

function getNombreCategoria($id, $db)
{
    $query  = "SELECT nombre FROM categorias WHERE id= " . $id;
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);
    return $row['nombre'];
}

function getNombreMarca($id, $db)
{
    $query  = "SELECT nombre FROM marcas WHERE id= " . $id;
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);
    return $row['nombre'];
}

function get_imagenes($db, $t, $id_producto, $id_portada = null)
{
    $t->set_block("pl", "pictures", "_pictures");
    $query  = "SELECT * FROM media WHERE producto_id = " . $id_producto;
    $result = mysqli_query($db, $query);
    while ($row = mysqli_fetch_array($result)) {
        $t->set_var("path", $row['path']);
        $t->set_var("id_media", $row['media_id']);
        $t->set_var("checked", (!empty($id_portada) && $id_portada == $row['media_id'])?"checked":"" );
        $t->parse("_pictures", "pictures", true);
    }
}

function createDir($folderName)
{
    if (!file_exists($folderName)) {
        mkdir($folderName, 0777, true);
    }
    return $folderName;
}

function vaciarDir($folderName)
{
    $carpeta = glob($folderName.'/*');
    foreach($carpeta as $archivo){
        if(is_file($archivo))      // Comprobamos que sean ficheros normales, y de ser asi los eliminamos en la siguiente linea
        unlink($archivo);          //Eliminamos el archivo
    }
}

function subir_fichero($directorio_destino, $tmp_name, $img_type, $nameFile)
{
    if (is_dir($directorio_destino) && is_uploaded_file($tmp_name)) {
        if (((strpos($img_type, "gif") || strpos($img_type, "jpeg") ||
            strpos($img_type, "jpg")) || strpos($img_type, "png") || strpos($img_type, "PNG"))) {
            if (move_uploaded_file($tmp_name, $directorio_destino . $nameFile)) {
                error_log('en subir fotos: '.json_encode($directorio_destino));
                return true;
            } else {
                error_log('en subir fotos: No se subio');
                echo "no se subio";
            }
        } else {
            error_log('en subir fotos: extensiones');
        }
    } else {
        error_log('en subir fotos: directorios '.json_encode($tmp_name));
    }
    error_log('en subir fotos: antes del fin');

    //Si llegamos hasta aquí es que algo ha fallado
    return false;
}


function eliminar_imagen($db, $directorio_destino, $id_prod, $id_foto)
{

    $query  = "SELECT * FROM media WHERE media_id =" . $id_foto . " AND producto_id=" . $id_prod;
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);

    $tipo_producto = getNumberType(getProduct($id_prod)['tipo']);

    @unlink($directorio_destino  . $tipo_producto . '/' . $id_prod . '/' . $row['nombre']);
    @unlink($directorio_destino  . $tipo_producto . '/' . $id_prod . '/thumb-' . $row['nombre']);
    @unlink($directorio_destino  . $tipo_producto . '/' . $id_prod . '/original-' . $row['nombre']);
    $update = "DELETE FROM `media` WHERE `media`.`media_id` =" . $id_foto . " AND `media`.`producto_id`=" . $id_prod;
    mysqli_query($db, $update);
    echo $update;
    exit;
}

//CATEGORIAS

function nueva_categoria($db,$t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "nueva_categoria.html");
    get_tipo_categorias($db, $t);

}

function listar_categorias($db, $t, $error = null)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "listado_categorias.html");
    $t->set_var("error", $error);
    $t->set_var("user", $_SESSION["admin_name"]);
    $t->set_block("pl", "categorias", "_categorias");
    $query  = "SELECT * FROM categorias WHERE borrado IS NULL";
    $result = mysqli_query($db, $query);

    while ($row = mysqli_fetch_array($result)) {
        $t->set_var("nombre_categoria", $row['nombre']);
        $t->set_var("id_categoria", $row['id']);
        $tipo = getCategoria($db,$row['tipo']);
        $t->set_var("tipo", $tipo);
        $t->set_var("descripcion", $row['descripcion']);
        $t->parse("_categorias", "categorias", true);
    }

}

function editar_categoria($db, $t, $id_categoria)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "nueva_categoria.html");
    $t->set_var("user", $_SESSION["admin_name"]);
    $query  = "SELECT * FROM categorias WHERE id = " . $id_categoria;
    $result = mysqli_query($db, $query);

    $row = mysqli_fetch_array($result);
    get_tipo_categorias($db, $t, $row['tipo']);
    $t->set_var("categoria", $row['nombre']);
    $t->set_var("id_categoria", $row['id']);
    $t->set_var("tipo", $row['tipo']);
    $t->set_var("descripcion", $row['descripcion']);
    $t->set_var("banner", $row['banner']);
    $t->set_var("show_banner", $row['banner']?"":"display:none;");

    /*seleccionado*/
    $t->set_var(trim($row['tipo']), "selected");
}

function guardar_categoria($db)
{
    global $directorio_destino;

    $id_categoria     = $_POST["id_categoria"];
    $nombre_categoria = $_POST["nombre"];
    $tipo             = $_POST['tipo'];
    $descripcion      = $_POST['descripcion'];

    $banner = $_POST['banner'];

    if (file_exists($_FILES['files']['tmp_name']) && is_uploaded_file($_FILES['files']['tmp_name'])) {

        $info     = new SplFileInfo($_FILES['files']['name']);
        $tmp_name = $_FILES["files"]["tmp_name"];
        $img_type = $_FILES["files"]["type"];
        $exte     = $info->getExtension();
        $banner = $exte;

    }


    if ($id_categoria != "") {
        $query = "UPDATE categorias SET nombre = '$nombre_categoria', tipo = '$tipo', descripcion = '$descripcion', banner = '$banner' where id = " . $id_categoria;
        mysqli_query($db, $query);
    } else {
        $query = "INSERT INTO categorias (nombre, tipo, descripcion, banner) VALUES ('$nombre_categoria', '$tipo', '$descripcion', '$banner')";
        mysqli_query($db, $query);
        $id_categoria = mysqli_insert_id($db);
    }

    if (empty($banner) && is_dir($directorio_destino  . 'categorias/' . $id_categoria . '/')) {
        rrmdir($directorio_destino  . 'categorias/' . $id_categoria . '/');
    }

    if (file_exists($_FILES['files']['tmp_name']) && is_uploaded_file($_FILES['files']['tmp_name'])) {

        $srcPath  = createDir($directorio_destino . 'categorias/' . $id_categoria) . '/';

        $nameFile = $id_categoria . "." . $exte;
        subir_fichero($srcPath, $tmp_name, $img_type, $nameFile);
    }

    header("Location: " . HOST . "listar_categorias");
    exit;
}

function get_categorias($db, $t, $id_producto = null)
{
    $t->set_var('base_url', HOST);
    $t->set_block("pl", "categorias", "_categorias");

    $query_todas  = "SELECT * FROM categorias WHERE borrado IS NULL";
    $result_todas = mysqli_query($db, $query_todas);

    if ($id_producto != "") {
        $query_id_prod   = "SELECT categoria_id,tipo FROM productos WHERE id = " . $id_producto;
        $result_id       = mysqli_query($db, $query_id_prod);
        $row_id          = mysqli_fetch_array($result_id);
        $cate_sel        = $row_id["categoria_id"];
        $query_categoria = "SELECT * FROM categorias WHERE borrado IS NULL AND tipo= '" . $row_id["tipo"] . "'";

        $result_categoria = mysqli_query($db, $query_categoria);
        while ($row = mysqli_fetch_array($result_categoria)) {
            if (trim($cate_sel) == trim($row['id'])) {
                $t->set_var("seleccionado", "selected");
            } else {
                $t->set_var("seleccionado", "");
            }

            $t->set_var("nombre_categoria", $row['nombre']);
            $t->set_var("id", $row['id']);
            $t->parse("_categorias", "categorias", true);
        }
    } else {
        while ($row = mysqli_fetch_array($result_todas)) {
            if (trim($cate_sel) == trim($row['id'])) {
                $t->set_var("seleccionado", "selected");
            } else {
                $t->set_var("seleccionado", "");
            }

            $t->set_var("nombre_categoria", $row['nombre']);
            $t->set_var("id", $row['id']);
            $t->parse("_categorias", "categorias", true);
        }
    }
}

function eliminar_categoria($db, $id_categoria)
{
    $query  = "SELECT count(id) FROM productos WHERE estado != 'No Disponible' AND categoria_id=" . $id_categoria;
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);
    if ($row['count(id)'] > 0) {
        echo "error";
        exit;
    } else {
        $query = "UPDATE categorias c SET c.borrado = now() WHERE c.id = " . $id_categoria;
        mysqli_query($db, $query);
        exit;
    }
}

function get_allcategories($db, $tipo)
{
    if ($tipo != null) {
        $query = "SELECT *
                  FROM categorias c
                  WHERE c.borrado IS NULL AND c.tipo = '" . $tipo . "'";
        $result = mysqli_query($db, $query);
    } else {
        $query  = "SELECT * FROM categorias WHERE borrado IS NULL";
        $result = mysqli_query($db, $query);
    }
    $payload = array();
    while ($row = mysqli_fetch_array($result)) {
        $payload[] = array(
            'id'     => $row['id'],
            'nombre' => $row['nombre'],
        );
    }
    $obj = json_encode($payload);
    echo $obj;
    die;
}

//CLIENTES

function nuevo_cliente($db, $t, $error = null, $params = null)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "nuevo_cliente.html");
    $t->set_var("user", $_SESSION["admin_name"]);

    $t->set_block("pl", "provincia", "_provincia");
    $query_  = "SELECT * FROM provincias";
    $result_ = mysqli_query($db, $query_);
    while ($row_ = mysqli_fetch_array($result_)) {
        $t->set_var("nombre", $row_['nombre']);
        $t->set_var("id", $row_['id']);
        $t->parse("_provincia", "provincia", true);
    }
    $t->set_block("pl","descuentos","_descuentos");
    $tipos = getTipoCategorias($db);
    foreach ($tipos as $i => $value) {
        $trans = translateType($db,$value['tipo']);
        $des = $value['descuento']*100;
        $t->set_var("descuento", $des);
        $t->set_var("tipo", $trans);
        $t->set_var("categoria", $value['categoria']);
        $t->parse("_descuentos", "descuentos", true);
    }
    $t->set_block("pl", "vendedores", "_vendedores");
    $query  = "SELECT * FROM vendedores";
    $result = mysqli_query($db, $query);
    while ($row = mysqli_fetch_array($result)) {
        $t->set_var("id", $row['id']);
        $t->set_var("nombre_vendedor", $row['nombre']);
        $t->set_var("apellido_vendedor", $row['apellido']);
        $t->parse("_vendedores", "vendedores", true);
    }
    if ($error != null) {
        $t->set_var("error", $error);
        $t->set_var("codtango", $params->codtango);
        $t->set_var("nombrecliente", $params->nombre);
        $t->set_var("apellido", $params->apellido);
        $t->set_var("email", $params->email);
    }
}

function listado_clientes($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "listado_clientes.html");
    $t->set_var("user", $_SESSION["admin_name"]);
    $t->set_block("pl", "clientes", "_clientes");
    $query = "SELECT c.*, p.nombre AS nombre_provincia, l.nombre AS nombre_ciudad, CONCAT(v.nombre, ' ', v.apellido) AS nombre_vendedor
              FROM clientes c
              LEFT JOIN provincias p ON c.provincia_id = p.id
              LEFT JOIN localidades l ON c.ciudad_id = l.id
              LEFT JOIN vendedores v ON c.vendedor_id = v.id";
    if (!$_SESSION["super_admin"]) {
        $query .= " WHERE vendedor_id = " . $_SESSION["admin_id"];
    }
    $result = mysqli_query($db, $query);

    while ($row = mysqli_fetch_array($result)) {
        if ($row['borrado']) {
            continue; // we dont show those deleted
            $t->set_var("isenabled", "style='display:none;'");
            $t->set_var("isdisabled", "style='display:inline-block;'");
        } else {
            $t->set_var("isdisabled", "style='display:none;'");
            $t->set_var("isenabled", "style='display:inline-block;'");
        }
        $t->set_var("id_cliente", $row['id']);
        $t->set_var("tango_id", $row['tango_id']);
        $t->set_var("apellido", $row['apellido']);
        $t->set_var("nombre", $row['nombre']);
        $t->set_var("ciudad", isset($row['nombre_ciudad']) ? $row['nombre_ciudad'] : "-");
        $t->set_var("vendedor", $row['nombre_vendedor']);
        $t->set_var("login", $row['last_login']);
        $t->set_var("descuento", (($row['descuento_libros'])?$row['descuento_libros']:'0') . '%');//Consulta a Diego
        $t->set_var("verficado", getVerificado($row['verficado']));
        $t->parse("_clientes", "clientes", true);
    }

}

function editar_cliente($db, $t, $id_cliente, $error = '')
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "datos_cliente.html");
    $t->set_var("user", $_SESSION["admin_name"]);
    $query             = "SELECT * FROM clientes WHERE id = '$id_cliente'";
    $result            = mysqli_query($db, $query);
    $vendedor_selected = 0;

    while ($row = mysqli_fetch_array($result)) {
        $t->set_var("id_cliente", $row['id']);
        $t->set_var("tango", $row['tango_id']);
        $t->set_var("nombre", $row['nombre']);
        $t->set_var("apellido", $row['apellido']);
        $t->set_var("provincia", $row['provincia_id']);
        $t->set_var("localidad", $row['ciudad_id']);
        $t->set_var("observaciones", $row['observaciones']);
        $t->set_block("pl", "provincias", "_provincias");
        $query_  = "SELECT * FROM provincias";
        $result_ = mysqli_query($db, $query_);
        while ($row_ = mysqli_fetch_array($result_)) {
            $t->set_var("nombre_provincia", $row_['nombre']);
            $t->set_var("id", $row_['id']);

            if ($row["provincia_id"] == $row_['id']) {
                $t->set_var("selected", "selected");
            } else {
                $t->set_var("selected", "");
            }
            $t->parse("_provincias", "provincias", true);

        }
        $t->set_block("pl", "localidades", "_localidades");

        $query_ = "SELECT * FROM localidades WHERE provincia_id = " . $row['provincia_id'];

        $result_ = mysqli_query($db, $query_);

        while ($row_ = mysqli_fetch_array($result_)) {
            $t->set_var("nombre_localidad", $row_['nombre']);
            $t->set_var("id", $row_['id']);

            if ($row["ciudad_id"] == $row_['id']) {
                $t->set_var("selected", "selected");
            } else {
                $t->set_var("selected", "");
            }
            $t->parse("_localidades", "localidades", true);
        }

        $t->set_var("email", $row['email']);
        $t->set_var("telefono", $row['telefono']);

        $vendedor_selected = $row['vendedor_id'];
        $t->set_var("id", $row['vendedor_id']);
        $t->set_block("pl","descuentos","_descuentos");
        $tipos = getTipoCategorias($db);
        foreach ($tipos as $i => $value) {
            $trans = translateType($db,$value['tipo']);
            $des = $row['descuento_'.$trans];
            $t->set_var("descuento", $des);
            $t->set_var("tipo", $trans);
            $t->set_var("categoria", $value['categoria']);
            $t->parse("_descuentos", "descuentos", true);
        }
        $t->set_var("verificado", $row['verficado']);
    }

    $t->set_block("pl", "vendedores", "_vendedores");
    $query  = "SELECT * FROM vendedores WHERE borrado IS NULL";
    $result = mysqli_query($db, $query);
    while ($row = mysqli_fetch_array($result)) {
        if ($vendedor_selected == $row['id']) {
            $t->set_var("selected", "selected");
        } else {
            $t->set_var("selected", "");
        }

        $t->set_var("id", $row['id']);
        $t->set_var("nombre_vendedor", $row['nombre']);
        $t->set_var("apellido_vendedor", $row['apellido']);
        $t->parse("_vendedores", "vendedores", true);
    }
    $t->set_var('error', $error);

}

function getNombreProvincia($id, $db)
{
    $query  = "SELECT nombre FROM provincias WHERE id= " . $id;
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);
    return $row['nombre'];
}

function getNombreCiudad($id, $db)
{
    $query  = "SELECT nombre FROM localidades WHERE id= " . $id;
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);
    return $row['nombre'];
}

function getNombreVendedor($id, $db)
{
    $query           = "SELECT nombre, apellido FROM vendedores WHERE id= " . $id;
    $result          = mysqli_query($db, $query);
    $row             = mysqli_fetch_array($result);
    $nombre_completo = $row['nombre'] . " " . $row['apellido'];
    return $nombre_completo;
}

function getVerificado($estado)
{

    if ($estado == 0) {
        return "No";
    } else {
        return "Si";
    }

}

function guardar_cliente($db, $t)
{
    $tango      = $_POST["tango"];
    $id_cliente = $_POST["id_cliente"];
    $name       = $_POST["nombre"];
    $lastname   = $_POST["apellido"];
    $telefono   = $_POST["telefono"];
    $email      = $_POST["email"];
    $state      = $_POST['provincia'];
    $city       = $_POST['ciudad'];
    $password   = $_POST['password'];
    $vendedor   = $_POST["vendedor_id"];
    $observaciones = $_POST["observaciones"];
    $verificado = $_POST["activo"];

    $tipos = getTipos($db);

    $sql2   = "SELECT * FROM clientes WHERE tango_id = '$tango' AND id != '$id_cliente'";
    $query2 = mysqli_query($db, $sql2);
    $myrow2 = mysqli_fetch_array($query2);

    if ($tango == '0' || count($myrow2['id']) == 0) {
        $sql = "SELECT verficado FROM clientes WHERE id = '$id_cliente'";
        $query = mysqli_query($db, $sql);
        $myrow = mysqli_fetch_array($query);
        $notVerified = $myrow['verficado'] == 0;

        $query = "UPDATE clientes SET nombre = '$name', apellido = '$lastname', telefono = '$telefono', email = '$email', provincia_id = $state, ciudad_id = $city, tango_id = '$tango'";
        
        foreach ($tipos as $tipo) {
            $trans = translateType($db,$tipo);
            $descuento = $_POST["descuento_$trans"];
            if ($tipo == 'libro') {
                $descuento = $descuento < 30 ? 30 : $descuento;
            }
            $query .= ", descuento_$trans = $descuento";
        }
        
        $query .= ", verficado= $verificado, observaciones = '$observaciones'";

        if ($password != null) {
            $cript_pass = encriptar_pass($password);

            $query .= ", password = '$cript_pass'";
        }

        if ($vendedor != null) {
            $query .= ", vendedor_id =  $vendedor";
        }

        $query .= " WHERE id = $id_cliente LIMIT 1";

        $cliente = mysqli_query($db, $query);

        if ($cliente != null) {
            if ($verificado == "1") {
                if ($notVerified) {
                    $email_ = $email;
                    $email  = $email;
                    if (filter_var($email_, FILTER_VALIDATE_EMAIL)) {
                        $to        = "$email";
                        $subject   = "Activación de cuenta";
                        $preferred = $name;
                        $message   = '<h3>Su cuenta ha sido activada</h3>' .
                            '<hr>';
                        $send = sendEmail($to, $subject, $preferred, $message, null, 'cuenta-activada');
                    }
                }
            }
        }

        header("Location: " . HOST . "listado_clientes");

    } else {
        $error = "Error: código de Tango existente";

        editar_cliente($db, $t, $id_cliente, $error);

    }


}

function guardar_nuevo_cliente($db, $t)
{
    $codigo_tango = $_POST['cod-tango'];
    $nombre       = $_POST['nombre'];
    $apellido     = $_POST['apellido'];
    $email        = $_POST['email'];
    $pass         = $_POST['password'];
    $pcia         = $_POST['provincia'];
    $ciudad       = $_POST['ciudad'];
    
    $vendedor     = $_POST['vendedor_id'];
    $observaciones= $_POST['observaciones'];

    if (intval($descuento) < 30) {
        $descuento = 30;
    }

    $sql    = "SELECT * FROM clientes WHERE email = '$email'";
    $query  = mysqli_query($db, $sql);
    $myrow  = mysqli_fetch_array($query);
    $sql2   = "SELECT * FROM clientes WHERE tango_id = '$codigo_tango'";
    $query2 = mysqli_query($db, $sql2);
    $myrow2 = mysqli_fetch_array($query2);
    $error  = "";
    if (count($myrow['id']) == 0) {
        if ($codigo_tango == '0' || count($myrow2['id']) == 0) {
            $pw_encriptada = encriptar_pass($pass);
            $query         = "INSERT INTO clientes (tango_id,nombre,apellido,email,password,provincia_id,ciudad_id, vendedor_id,verficado, observaciones)
            values ('$codigo_tango', '$nombre', '$apellido', '$email', '$pw_encriptada', '$pcia', '$ciudad', '$vendedor', 1, '$observaciones')";
            $result = mysqli_query($db, $query);
            $last_id = mysqli_insert_id($db);
            if ($result) {
                $tipos = getTipos($db);
                $sql = "UPDATE clientes SET nombre = '$nombre'";
                foreach ($tipos as $value) {
                  $trans = translateType($db,$value);
                  $descuento = $_POST["descuento_$trans"];
                  $sql .= ", descuento_$trans = $descuento";
                } 
                $sql .= " WHERE id = $last_id";
                mysqli_query($db, $sql);
            }
        } else {
            $error = "Error: código de Tango existente";
        }
    } else {
        $error = "Error: email existente";
    }

    if ($error == "") {
        $email_ = $_POST["email"];
        $email  = $_POST["email"];
        if (filter_var($email_, FILTER_VALIDATE_EMAIL)) {
            $to        = $email_;
            $subject   = "Registración - Didácticos del Sur";
            $preferred = $nombre;
            $message   = "Su cuenta se ha registrado correctamente, y ya se encuentra habilitada. Su contraseña es: " . $pass;
            sendEmail($to, $subject, $preferred, $message);
            $t->set_var("mensaje", "El correo se ha enviado correctamente.");
        } else {
            $t->set_var("error_mail", "Hubo un error");
        }

        $to        = 'info@didacticosdelsur.com';
        $subject   = "Registración de Usuario";
        $preferred = 'Didácticos del Sur';
        $message   = "Se ha registrado un nuevo usuario.<br><br>
                            El usuario $nombre $apellido ($email) se encuentra pendiente de validación.";

        sendEmail($to, $subject, $preferred, $message);

        header("Location: " . HOST . "listado_clientes");
    } else {
        $params = (object) [
            'codtango'  => $codigo_tango,
            'nombre'    => $nombre,
            'apellido'  => $apellido,
            'email'     => $email,
        ];
        nuevo_cliente($db, $t, $error, $params);
    }
}

function desactivar_cliente($db, $id_cliente)
{

    $query  = "SELECT * FROM clientes WHERE id = $id_cliente LIMIT 1";
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_array($result);

    if ($row['verficado'] == 0) {
        $query  = "SELECT count(id) as pedidos FROM pedidos WHERE cliente_id = $id_cliente LIMIT 1";
        $result = mysqli_query($db, $query);
        $row = mysqli_fetch_array($result);

        if ($row['pedidos'] == 0) {
            $query = "DELETE FROM clientes WHERE id = $id_cliente";
            mysqli_query($db, $query);
        }
        else { // if he previously had any orders (he could have been disabled after getting some orders)
            $query = "UPDATE clientes SET borrado = now() WHERE id = " . $id_cliente; // we soft delete it
            mysqli_query($db, $query);
        }
    }
    else {

        $query = "UPDATE clientes SET borrado = now() WHERE id = " . $id_cliente;
        mysqli_query($db, $query);
    }
    header("Location: " . HOST . "listado_clientes");
    exit;
}
function activar_cliente($db, $id_cliente)
{
    $query = "UPDATE clientes SET borrado = null WHERE id = " . $id_cliente;
    mysqli_query($db, $query);
    header("Location: " . HOST . "listado_clientes");
    exit;
}

//VENDEDORES

function editar_vendedor($db, $t, $id_vendedor)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "datos_vendedor.html");
    $t->set_var("user", $_SESSION["admin_name"]);
    $query  = "SELECT * FROM vendedores WHERE id = " . $id_vendedor;
    $result = mysqli_query($db, $query);
    while ($row = mysqli_fetch_array($result)) {
        $t->set_var("id_vendedor", $row['id']);
        $t->set_var("codigo_vendedor", $row['codigo_vendedor']);
        $t->set_var("nombre", $row['nombre']);
        $t->set_var("apellido", $row['apellido']);
        $t->set_var("email", $row['email']);
        $t->set_var("telefono", $row['telefono']);
    }
}

function guardar_vendedor($db)
{
    $id_vendedor     = $_POST["id_vendedor"];
    $codigo_vendedor = $_POST["codigo_vendedor"];
    $nombre          = $_POST["nombre"];
    $apellido        = $_POST["apellido"];
    $email           = $_POST["email"];
    $telefono        = $_POST["telefono"];
    $pass            = $_POST["password"];

    $query      = "UPDATE vendedores SET codigo_vendedor = '$codigo_vendedor', nombre = '$nombre', apellido = '$apellido', email = '$email', telefono = '$telefono'";

    if ($pass != null) {
        $pass_cript = encriptar_pass($pass);

        $query .= ", password = '$pass_cript'";
    }
    $query .= " WHERE id = $id_vendedor LIMIT 1";

    mysqli_query($db, $query);
    header("Location: " . HOST . "listado_vendedores");
    exit;
}

function nuevo_vendedor($t, $error = null, $params = null)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "nuevo_vendedor.html");
    $t->set_var("user", $_SESSION["admin_name"]);
    if ($error != null) {
        $t->set_var("error", $error);
        $t->set_var("cod-vendedor", $params->codvendedor);
        $t->set_var("nombre", $params->nombre);
        $t->set_var("apellido", $params->apellido);
        $t->set_var("email", $params->email);
        $t->set_var("telefono", $params->telefono);
    }
}

function listado_vendedores($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "listado_vendedores.html");
    $t->set_var("user", $_SESSION["admin_name"]);
    $t->set_block("pl", "vendedores", "_vendedores");
    $query  = "SELECT * FROM vendedores WHERE rol_admin = 0 AND borrado IS NULL ";
    $result = mysqli_query($db, $query);
    while ($row = mysqli_fetch_array($result)) {
        $t->set_var("id_vendedor", $row['id']);
        $t->set_var("codigo_vendedor", $row['codigo_vendedor']);
        $t->set_var("nombre", $row['nombre']);
        $t->set_var("apellido", $row['apellido']);
        $t->set_var("email", $row['email']);
        $t->set_var("telefono", $row['telefono']);
        $t->parse("_vendedores", "vendedores", true);
    }
}

function guardar_nuevo_vendedor($db, $t)
{
    $codigo_vendedor = $_POST['cod-vendedor'];
    $nombre          = $_POST['nombre'];
    $apellido        = $_POST['apellido'];
    $email           = $_POST['email'];
    $pass            = $_POST['password'];
    $telefono        = $_POST['telefono'];

    $sql    = "SELECT * FROM vendedores WHERE email = '" . $email . "'";
    $query  = mysqli_query($db, $sql);
    $myrow  = mysqli_fetch_array($query);
    $sql2   = "SELECT * FROM vendedores WHERE codigo_vendedor = '" . $codigo_vendedor . "'";
    $query2 = mysqli_query($db, $sql2);
    $myrow2 = mysqli_fetch_array($query2);
    $error  = "";
    if (count($myrow['id']) == 0) {
        if (count($myrow2['id']) == 0) {
            $pass_cript = encriptar_pass($pass);
            $query      = "INSERT into vendedores (codigo_vendedor,nombre,apellido,email,password,telefono) values
            ('$codigo_vendedor', '$nombre', '$apellido', '$email', '$pass_cript', '$telefono')";
            mysqli_query($db, $query);
        } else {
            $error = "Hubo un error: cṕdigo de vendedor existente";
        }
    } else {
        $error = "Hubo un error: email existente";
    }
    if ($error != "") {
        $params = (object) [
            'codvendedor' => $codigo_vendedor,
            'nombre'      => $nombre,
            'apellido'    => $apellido,
            'email'       => $email,
            'telefono'    => $telefono,
        ];

        nuevo_vendedor($t, $error, $params);
    } else {
        header("Location: " . HOST . "listado_vendedores");
        exit;
    }
}

function desactivar_vendedor($db, $id_vendedor)
{
    $query = "UPDATE vendedores v JOIN clientes c SET v.borrado = now(), c.vendedor_id = 0 WHERE v.id = c.vendedor_id AND v.id = $id_vendedor";
    mysqli_query($db, $query);
    header("Location: " . HOST . "listado_vendedores");
    exit;
}

//PEDIDOS

function agregar_producto_pedido($db, $t, $id_pedido, $tipo)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "nuevo_producto_pedido.html");
    $t->set_var('id_pedido', $id_pedido);
    $query  = "SELECT * FROM productos WHERE (borrado IS NULL) AND (tipo = '{$tipo}') AND (estado = 'Disponible')";
    $result = mysqli_query($db, $query);
    $t->set_block("pl", "producto", "_producto");
    while ($row = mysqli_fetch_array($result)) {
        $t->set_var("nombre_producto", $row['nombre']);
        $t->set_var("id_producto", $row['id']);
        $t->set_var("sku", $row['sku']);
        $t->set_var("precio", round($row['precio_pvp'], 2));
        $t->set_var("descuento", $row['descuento']);
        $t->parse("_producto", "producto", true);
    }
}

function getProductPedido($db, $id_producto, $cant_producto)
{
    $query = "SELECT p.id,p.sku,p.nombre,p.tipo,p.categoria_id,m.path,p.precio_pvp,p.descuento
        FROM productos p JOIN media m WHERE p.id=m.producto_id AND p.id=" . $id_producto;
    $result          = mysqli_query($db, $query);
    $row             = mysqli_fetch_array($result);
    $id              = $row["id"];
    $nombre          = $row["nombre"];
    $tipo            = $row["tipo"];
    $sku             = $row["sku"];
    $categoria       = $row["categoria_id"];
    $imagen          = $row["path"];
    $precio          = $row["precio_pvp"];
    $descuento       = $row["descuento"];
    return $producto = array(
        'id'        => $id,
        'sku'       => $sku,
        'nombre'    => $nombre,
        'tipo'      => $tipo,
        'categoria' => $categoria,
        'imagen'    => $imagen,
        'precio'    => $precio,
        'descuento' => $descuento,
        'cantidad'  => $cant_producto,
    );
}

function get_datos_cliente($db, $id)
{
    $sql    = "SELECT * FROM clientes WHERE id = " . $id;
    $result = mysqli_query($db, $sql);
    return mysqli_fetch_array($result);
}

function listar_ciudades($db, $provincia)
{
    $query   = "SELECT * FROM localidades WHERE provincia_id ='" . $provincia . "'";
    $result  = mysqli_query($db, $query);
    $payload = array();
    while ($row = mysqli_fetch_array($result)) {
        $payload[] = array(
            'id'            => $row['id'],
            'nombre'        => $row['nombre'],
            'codigo_postal' => $row['codigo_postal'],
        );
    }
    $obj = json_encode($payload);
    echo $obj;
    die;
}

function icreate($filename)
{
    $isize = getimagesize($filename);
    if ($isize['mime'] == 'image/jpeg') {
        return imagecreatefromjpeg($filename);
    } elseif ($isize['mime'] == 'image/png') {
        return imagecreatefrompng($filename);
    }

    /* Add as many formats as you can */
}

function pintar_panel($t, $templates, $esAdmin)
{
    $tf = new Template($templates, "remove");
    $t->set_var('base_url', HOST);
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "panel.html");
    $panel = $tf->parse("MAIN", "pl");
    $t->set_var("panel", $panel);
    if (!$esAdmin) {
        $t->set_var("admin", "style='display:none;'");
    }
}


function getNumberType($tipo) {
    switch ($tipo) {
        case "libro":
            return "01";
        case "juego":
            return "02";
        default:
            return "03";
    }
}

function slugify($text, string $divider = '-')
{
    $unwanted_array = ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'Ñ' => 'N'];
    $str            = strtr($text, $unwanted_array);

    $slug = strtolower(trim(preg_replace('/[\s-]+/', $divider, preg_replace('/[^A-Za-z0-9-]+/', $divider, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $divider));
    return $slug;
}

function rrmdir($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if ( is_dir($full) ) {
                rrmdir($full);
            }
            else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
}

function pintar_footer($t, $templates) {
    $t->set_var('base_url', HOST);
    $tf = new Template($templates, "remove");
    $tf->set_var('base_url', HOST);
    $tf->set_var('url_site', str_replace("admin/", "", HOST));
    $tf->set_file("pl", "footer_scripts.html");
    $footer = $tf->parse("MAIN", "pl");
    $t->set_var("footer_scripts", $footer);
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime, new DateTimeZone('America/Argentina/Buenos_Aires'));
    $now->setTimeZone(new DateTimeZone('America/Argentina/Buenos_Aires'));
    
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'año',
        'm' => 'mes',
        'w' => 'semana',
        'd' => 'día',
        'h' => 'hora',
        'i' => 'minuto',
        's' => 'segundo',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? (($v == 'mes')?'es':'s') : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? 'hace ' . implode(', ', $string) : 'justo ahora';
}
