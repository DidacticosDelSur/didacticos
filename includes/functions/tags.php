<?php

function showTag($id, $page = null, $producto_add = null)
{
	global $db, $t;

	$t->set_var('base_url', HOST);
    $t->set_file("pl", "categorias.html");
    $t->set_var("tipo_", $tipo);

    if ($producto_add != null) {
        $t->set_var("photo", $producto_add['path']);
        $t->set_var("producto_agregado", $producto_add['nombre']);
    } else {
        $t->set_var("producto_agregado_hidden", "style='display:none;'");
    }

    $_SESSION['filter'] = "/tags/$id/$page";

    $query = "SELECT COUNT(*) FROM productos p INNER JOIN producto_tags pt ON pt.id_producto = p.id WHERE p.borrado IS NULL AND p.estado = 'Disponible' AND pt.id_tag = '$id'";

    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);

    $cant_productos = $row["COUNT(*)"];
    $t->set_var("cant_resultados", $cant_productos);
    $t->set_var("a_mostrar", min(90, $cant_productos));

    /*if ($tipo == "libro") {
        $t->set_var("tipo", "Editorial");
    } else {
        $t->set_var("tipo", "Marca");
    }

    $t->set_block("pl", "marcas", "_marcas");

    $q = "SELECT * FROM marcas WHERE borrado IS NULL AND tipo = '$tipo'";

    $rslt = mysqli_query($db, $q);
    while ($row_ = mysqli_fetch_array($rslt)) {
        $t->set_var("id_marca", $row_["id"]);
        $t->set_var("nombre", $row_['nombre']);
        $t->parse("_marcas", "marcas", true);
    }
    
    //agregado
    $t->set_block("pl", "cat", "_cat");

    $rslt = mysqli_query($db, $q);
    while ($row_ = mysqli_fetch_array($rslt)) {
        $t->set_var("id_category", $row_["id"]);
        $t->set_var("category_name", $row_['nombre']);
        $t->parse("_cat", "cat", true);
    }
    if ($producto_add != null) {
        $t->set_var("photo", $producto_add['path']);
        $t->set_var("producto_agregado", $producto_add['nombre']);
    } else {
        $t->set_var("producto_agregado_hidden", "style='display:none;'");
    }*/

    $tagName = getTagName($id);

    $t->set_var("categoria", 'Tags: ' . $tagName);
    $t->set_var("marca", $tagName);

    $t->set_var('page_type', 'tags');
    $t->set_var('tipo_', $id . '-' . slugify($tagName));

    $limit = getLimitPage();
    $url = "tags/".$id.'-'.$tagName;
    paginado($t,$cant_productos,$page,$url,$limit);

    pintar_productos($t, $db, "", $tagName, "", "product", $page, $limit); // function pintar_productos($t, $db, $tipo, $tag = '', $title, $field)
}

function getTagName($id)
{
	global $db;

	$query = "SELECT nombre FROM tags WHERE id = '$id' LIMIT 1";

    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);

    return $row['nombre'];
    //return utf8_encode($row['nombre']);

}

function returnTagListFor($product_id)
{
    global $db;

    $query = "SELECT t.* FROM tags t INNER JOIN producto_tags pt ON pt.id_tag = t.id WHERE pt.id_producto = '$product_id'";

    $string = '';

    $result = mysqli_query($db, $query);
    while ($row = mysqli_fetch_array($result)) {
        $string .= '<a class="button-rounded button-tag" href="' . HOST . 'tags/' . $row['id'] . '-' . slugify($row['nombre']) . '">#' . slugify($row['nombre']) . '</a>';
    }

    return $string;
}

function showTagList($id, $placeholder)
{
    global $db, $t;

    $query = "SELECT t.* FROM tags t WHERE t.id = '$id'";
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);

    if ($_SERVER['REQUEST_URI'] == '/home') { // if we are showing tags for home we clear filters
        $_SESSION['filter'] = '';
    }

    pintar_productos($t, $db, '', $row['nombre'], '', $placeholder);
}