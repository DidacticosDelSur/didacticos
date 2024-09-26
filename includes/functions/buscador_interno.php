<?php
function mostrar_buscador_para_empleados($db, $t)
{
  $t->set_file("pl", "mostrarBuscadorParaEmpleados.html");
}

function buscar_producto_empleados($db, $t, $entrada)
{
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "mostrarBuscadorParaEmpleados.html");
  //$resultado = stripAccents(urldecode(str_replace("-", "%", $entrada)));
  $resultado = str_replace("?", "/", $entrada);
  $busqueda = str_replace("-", " ", $resultado);
  $resultado = str_replace("-", "%", $resultado);
  //$_SESSION['filter'] = "/buscar/$entrada";

  $query = "SELECT DISTINCT p.*, m.nombre AS marca
    FROM productos p
    INNER JOIN marcas m ON m.id = p.marca_id
    INNER JOIN categorias c ON c.id = p.categoria_id
    LEFT JOIN producto_tags pt ON pt.id_producto = p.id
    LEFT JOIN tags t ON t.id = pt.id_tag
    WHERE p.borrado IS NULL AND
    p.estado = 'Disponible' AND
    (p.nombre LIKE '%$resultado%' OR p.tipo LIKE '%$resultado%' OR p.descripcion LIKE '%$resultado%'
      OR p.sku LIKE '%$resultado%' OR p.codigo_barras LIKE '%$resultado%'
      OR m.nombre LIKE '%$resultado%' OR m.descripcion LIKE '%$resultado%'
      OR c.nombre LIKE '%$resultado%' OR c.descripcion LIKE '%$resultado%'
      OR t.nombre LIKE '%$resultado%' OR t.descripcion LIKE '%$resultado%'
    )";

  //) ORDER BY p.precio_pvp DESC";
  $sql = mysqli_query($db, $query);
  $res_cant = mysqli_num_rows($sql);
  $t->set_var("cant_resultados", $res_cant);
  $t->set_var("a_mostrar", min(90, mysqli_num_rows($sql)));


  $tf = new Template(PATH, "remove");
  $tf->set_var('base_url', HOST);
  $tf->set_file("pl", "cardProductInterno.html");
  $tf->set_block("pl", "productos", "_productos");

  while ($row = mysqli_fetch_array($sql)) {
    set_product($tf, $row, $db);
    $tf->parse("_productos", "productos", true);
    $product = $tf->parse("MAIN", "pl");
    $t->set_var("product", $product);

  }
}
