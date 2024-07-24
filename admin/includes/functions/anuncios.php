<?php
//ANUNCIOS

function nuevo_anuncio() {
	global $t;

    $t->set_var('base_url', HOST);
    $t->set_var("user", $_SESSION["admin_name"]);
    $t->set_var("fecha", date("Y-m-d")." ".date("H:i:00"));
    $t->set_file("pl", "anuncios/nuevo_anuncio.html");
}

function listar_anuncios($error = null) {
	global $db, $t;

    $t->set_var('base_url', HOST);
    $t->set_file("pl", "anuncios/listado_anuncios.html");
    $t->set_var("error", $error);
    $t->set_var("user", $_SESSION["admin_name"]);
    $t->set_block("pl", "anuncios", "_anuncios");
    $query  = "SELECT * FROM anuncios";
    $result = mysqli_query($db, $query);

    while ($row = mysqli_fetch_array($result)) {
        $t->set_var("fecha", $row['fecha']);
        $t->set_var("anuncio_id", $row['id']);
        $t->set_var("descripcion", $row['descripcion']);

        $t->parse("_anuncios", "anuncios", true);
    }
}

function editar_anuncio($anuncio_id) {
	global $db, $t;

    $t->set_var('base_url', HOST);
    $t->set_file("pl", "anuncios/nuevo_anuncio.html");
    $t->set_var("user", $_SESSION["admin_name"]);
    $query  = "SELECT * FROM anuncios WHERE id = '$anuncio_id' LIMIT 1";
    $result = mysqli_query($db, $query);

    $row = mysqli_fetch_array($result);

    $t->set_var("anuncio_id", $row['id']);
    $t->set_var("fecha", $row['fecha']);
    $t->set_var("descripcion", $row['descripcion']);
}

function guardar_anuncio() {
	global $db, $t;

    $anuncio_id     = $_POST["anuncio_id"];
    $fecha = $_POST["fecha"];
    $descripcion  = $_POST['descripcion'];


    if ($anuncio_id != "") {
        $query = "UPDATE anuncios SET fecha = '$fecha', descripcion = '$descripcion' WHERE id = " . $anuncio_id;
        $result = mysqli_query($db, $query);
    } else {
        $query = "INSERT INTO anuncios (fecha, descripcion) VALUES ('$fecha', '$descripcion')";
        $result = mysqli_query($db, $query);
        $anuncio_id = mysqli_insert_id($db);
    }

    header("Location: " . HOST . "listar_anuncios");
    exit;
}

function eliminar_anuncio($anuncio_id) {
	global $db, $t;

    $t->set_var('base_url', HOST);
    $t->set_file("pl", "anuncios/listado_anuncios.html");
    $query  = "DELETE FROM anuncios WHERE id = '$anuncio_id' LIMIT 1";
    $result = mysqli_query($db, $query);
    exit;
}

function get_anuncios($id_producto = null) {
	global $db, $t;

    $t->set_var('base_url', HOST);
    $t->set_block("pl", "anuncios", "_anuncios");
    $query_todas  = "SELECT * FROM anuncios WHERE borrado IS NULL";
    $result_todas = mysqli_query($db, $query_todas);
    if ($id_producto != "") {
        $query_id_prod = "SELECT anuncio_id,tipo FROM productos WHERE id = " . $id_producto;
        $result_id     = mysqli_query($db, $query_id_prod);
        $row_id        = mysqli_fetch_array($result_id);
        $anuncio_sel     = $row_id["anuncio_id"];

        $query_categoria  = "SELECT * FROM anuncios WHERE borrado IS NULL AND tipo ='" . $row_id["tipo"] . "'";
        $result_categoria = mysqli_query($db, $query_categoria);

        while ($row = mysqli_fetch_array($result_categoria)) {
            if (trim($anuncio_sel) == trim($row['id'])) {
                $t->set_var("seleccionado", "selected");
            } else {
                $t->set_var("seleccionado", "");
            }

            $t->set_var("nombre_anuncio", $row['nombre']);
            $t->set_var("id", $row['id']);
            $t->parse("_anuncios", "anuncios", true);
        }

    } else {
        while ($row = mysqli_fetch_array($result_todas)) {
            if (trim($anuncio_sel) == trim($row['id'])) {
                $t->set_var("seleccionado", "selected");
            } else {
                $t->set_var("seleccionado", "");
            }

            $t->set_var("nombre_anuncio", $row['nombre']);
            $t->set_var("id", $row['id']);
            $t->parse("_anuncios", "anuncios", true);
        }
    }
}