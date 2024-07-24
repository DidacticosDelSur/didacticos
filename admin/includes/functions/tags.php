<?php

function get_alltags($db, $tipo = null)
{
    $query  = "SELECT * FROM tags WHERE borrado IS NULL";
    $result = mysqli_query($db, $query);
 
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

function listar_tags($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "listado_tags.html");
    $t->set_var("user", $_SESSION["admin_name"]);
    $t->set_block("pl", "tags", "_tags");

    $query  = "SELECT * FROM tags WHERE borrado IS NULL";
    $result = mysqli_query($db, $query);

    while ($row = mysqli_fetch_array($result)) {
        $t->set_var("nombre_tag", $row['nombre']);
        $t->set_var("id_tag", $row['id']);

        $t->set_var("descripcion", $row['descripcion']);
        $t->set_var("hidden", (in_array($row['id'], [1,2,3,4,5]))?"hidden":"");
        $t->parse("_tags", "tags", true);
    }
}

function editar_tag($db, $t, $id_tag)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "nuevo_tag.html");
    $t->set_var("user", $_SESSION["admin_name"]);
    $query  = "SELECT * FROM tags WHERE id = '$id_tag'";
    $result = mysqli_query($db, $query);

    $row = mysqli_fetch_array($result);

    $t->set_var("tag", $row['nombre']);
    $t->set_var("id_tag", $row['id']);
    $t->set_var("descripcion", $row['descripcion']);
}

function guardar_tag($db)
{
    $id_tag      = $_POST["id_tag"];
    $nombre_tag  = $_POST["nombre"];
    $descripcion = $_POST['descripcion'];
    if ($id_tag != "") {
        $query = "UPDATE tags SET nombre = '$nombre_tag', descripcion = '$descripcion' where id = '$id_tag'";
    } else {
        $query = "INSERT INTO tags (nombre, descripcion) VALUES ('$nombre_tag', '$descripcion')";
    }

    mysqli_query($db, $query);
    header("Location: " . HOST . "listar_tags");
    exit;
}

function eliminar_tag($db, $id_tag)
{

    $query = "UPDATE tags SET borrado = now() WHERE id = '$id_tag'";
    mysqli_query($db, $query);
    header("Location: " . HOST . "listar_tags");
    exit;
}

/******************* RE DISTRIBUCIÓN DE FUNCIONES DEL MISMO TIPO **************************/

function nuevo_tag($t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "nuevo_tag.html");
}

function get_tag_producto($db, $producto)
{
    $query = "SELECT *
        FROM tags t
        WHERE t.borrado IS NULL AND t.id IN
        (SELECT t2.id
        FROM tags t2
        INNER JOIN producto_tags pt
        ON (pt.id_tag = t2.id)
        WHERE pt.id_producto = '$producto')";
    $result  = mysqli_query($db, $query);
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

function getNombreTag($id, $db)
{
    $query  = "SELECT nombre FROM tags WHERE id= " . $id;
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);
    return $row['nombre'];
}
