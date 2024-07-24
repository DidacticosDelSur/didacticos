<?php

//MARCAS

function nueva_marca($db,$t) {
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "nueva_marca.html");
    get_tipo_categorias($db, $t);

}

function listar_marcas($db, $t, $error = null) {
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "listado_marcas.html");
    $t->set_var("error", $error);
    $t->set_var("user", $_SESSION["admin_name"]);
    $t->set_block("pl", "marcas", "_marcas");
    $query  = "SELECT * FROM marcas WHERE borrado IS NULL";
    $result = mysqli_query($db, $query);

    while ($row = mysqli_fetch_array($result)) {
        $t->set_var("nombre_marca", $row['nombre']);
        $t->set_var("id_marca", $row['id']);
        $tipo = getCategoria($db,$row['tipo']);
        $t->set_var("tipo", $tipo);

        $t->set_var("descripcion", $row['descripcion']);
        $t->parse("_marcas", "marcas", true);
    }
}

function editar_marca($db, $t, $id_marca) {
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "nueva_marca.html");
    $t->set_var("user", $_SESSION["admin_name"]);
    $query  = "SELECT * FROM marcas WHERE id = " . $id_marca;
    $result = mysqli_query($db, $query);

    $row = mysqli_fetch_array($result);

    get_tipo_categorias($db, $t, $row['tipo']);
    $t->set_var("marca", $row['nombre']);
    $t->set_var("id_marca", $row['id']);
    $t->set_var("tipo", $row['tipo']);
    $t->set_var("banner", $row['banner']);
    $t->set_var("show_banner", $row['banner']?"":"display:none;");

    $t->set_var("descripcion", $row['descripcion']);
}

function guardar_marca($db) {
    global $directorio_destino;

    $id_marca     = $_POST["id_marca"];
    $nombre_marca = $_POST["nombre"];
    $tipo         = $_POST['tipo'];
    $descripcion  = $_POST['descripcion'];

    $banner = $_POST['banner'];

    if (file_exists($_FILES['files']['tmp_name']) && is_uploaded_file($_FILES['files']['tmp_name'])) {

        $info     = new SplFileInfo($_FILES['files']['name']);
        $tmp_name = $_FILES["files"]["tmp_name"];
        $img_type = $_FILES["files"]["type"];
        $exte     = $info->getExtension();
        $banner = $exte;
    }


    if ($id_marca != "") {
        $query = "UPDATE marcas SET nombre = '$nombre_marca', tipo = '$tipo', descripcion = '$descripcion', banner = '$banner' where id = " . $id_marca;
        $result = mysqli_query($db, $query);
    } else {
        $query = "INSERT INTO marcas (nombre, tipo, descripcion, banner) VALUES ('$nombre_marca', '$tipo', '$descripcion', '$banner')";
        $result = mysqli_query($db, $query);
        $id_marca = mysqli_insert_id($db);
    }

    if (empty($banner) && is_dir($directorio_destino  . 'marcas/' . $id_marca . '/')) {
        rrmdir($directorio_destino  . 'marcas/' . $id_marca . '/');
    }

    if (file_exists($_FILES['files']['tmp_name']) && is_uploaded_file($_FILES['files']['tmp_name'])) {

        $srcPath  = createDir($directorio_destino . 'marcas/' . $id_marca) . '/';

        $nameFile = $id_marca . "." . $exte;
        subir_fichero($srcPath, $tmp_name, $img_type, $nameFile);
    }

    header("Location: " . HOST . "listar_marcas");
    exit;
}

function eliminar_marca($db, $t, $id_marca) {
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "listado_marcas.html");
    $query  = "SELECT count(id) FROM productos WHERE estado != 'No Disponible' AND  marca_id=" . $id_marca;
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);
    if ($row['count(id)'] > 0) {
        echo "error";
        exit;
    } else {
        $query = "UPDATE marcas m SET m.borrado = now()WHERE m.id = " . $id_marca;
        mysqli_query($db, $query);
        exit;
    }
}

function get_marcas($db, $t, $id_producto = null) {
    $t->set_var('base_url', HOST);
    $t->set_block("pl", "marcas", "_marcas");
    $query_todas  = "SELECT * FROM marcas WHERE borrado IS NULL";
    $result_todas = mysqli_query($db, $query_todas);
    if ($id_producto != "") {
        $query_id_prod = "SELECT marca_id,tipo FROM productos WHERE id = " . $id_producto;
        $result_id     = mysqli_query($db, $query_id_prod);
        $row_id        = mysqli_fetch_array($result_id);
        $marca_sel     = $row_id["marca_id"];

        $query_categoria  = "SELECT * FROM marcas WHERE borrado IS NULL AND tipo ='" . $row_id["tipo"] . "'";
        $result_categoria = mysqli_query($db, $query_categoria);

        while ($row = mysqli_fetch_array($result_categoria)) {
            if (trim($marca_sel) == trim($row['id'])) {
                $t->set_var("seleccionado", "selected");
            } else {
                $t->set_var("seleccionado", "");
            }

            $t->set_var("nombre_marca", $row['nombre']);
            $t->set_var("id", $row['id']);
            $t->parse("_marcas", "marcas", true);
        }

    } else {
        while ($row = mysqli_fetch_array($result_todas)) {
            if (trim($marca_sel) == trim($row['id'])) {
                $t->set_var("seleccionado", "selected");
            } else {
                $t->set_var("seleccionado", "");
            }

            $t->set_var("nombre_marca", $row['nombre']);
            $t->set_var("id", $row['id']);
            $t->parse("_marcas", "marcas", true);
        }
    }
}

function get_allmarcas($db, $tipo) {
    if ($tipo != null) {
        $query = "SELECT *
                  FROM marcas m
                  WHERE m.borrado IS NULL AND m.tipo = '" . $tipo . "'";
        $result = mysqli_query($db, $query);
    } else {
        $query  = "SELECT * FROM marcas WHERE borrado IS NULL";
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