<?php 

//TIPO CATEGORIAS

function nuevo_tipo_categoria($t, $error = false){
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "nuevo_tipo_categoria.html");
  if ($error) {
    $t->set_var("show_error", "style='display: block'");
    $t->set_var("error_message", "Existe otro tipo con el mismo tipo o link");
  } else {
    $t->set_var("show_error", "style='display: none'");
  }

}

function listar_tipos_categorias($db, $t, $error = null)
{
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "listado_tipo_categorias.html");
  $t->set_var("error", $error);
  $t->set_var("user", $_SESSION["admin_name"]);
  $t->set_block("pl", "tipos", "_tipos");
  $query  = "SELECT * FROM tipo_categorias WHERE eliminado = '0' order by id";
  $result = mysqli_query($db, $query);

  while ($row = mysqli_fetch_array($result)) {
    //$t->set_var("nombre_tipo_categoria", utf8_encode($row['categoria']));
    $t->set_var("nombre_tipo_categoria", $row['categoria']);
    $t->set_var("tipo_categoria", $row['tipo']);
    $t->set_var("id_tipo_categoria", $row['id']);
    $t->set_var("link_tipo", $row['link']);
    $t->set_var("dto_tag", $row['dto_tag']);
    $t->set_var("descuento", $row['descuento']*100);
    $t->set_var("icon_categoria","class='".$row['icon']."'");
    $t->set_var("clase_estilo", $row['clase_estilo']);
    $t->set_var("exento_iva", $row['exento_iva'] == 1 ? 'Si':'No');
    $t->parse("_tipos", "tipos", true);
  }

}

function editar_tipo_categorias($db, $t, $id_tipo)
{
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "nuevo_tipo_categoria.html");
  $t->set_var("user", $_SESSION["admin_name"]);
  $query  = "SELECT * FROM tipo_categorias WHERE id = " . $id_tipo;
  $result = mysqli_query($db, $query);

  $row = mysqli_fetch_array($result);

  $t->set_var("show_error", "style='display: none'");
  $t->set_var("nombre_tipo_categoria", utf8_encode($row['categoria']));
  $t->set_var("tipo_categoria", $row['tipo']);
  $t->set_var("id_tipo_categoria", $row['id']);
  $t->set_var("link_tipo", $row['link']);
  $t->set_var("descuento", $row['descuento']*100);
 /*  $t->set_var("icon", $row['icon']);
  $t->set_var("icon_categoria",$row['icon']!= null?"class='".$row['icon']."'":"");
  $t->set_var("clase_estilo", $row['clase_estilo']); */
  $t->set_var("exento_iva", $row['exento_iva']);
  $t->set_var("exento_iva_checked", $row['exento_iva'] == 1 ? 'checked':'');
}

function guardar_tipo_categorias($db)
{
  $id_tipo        = $_POST["id_tipo_categoria"];
  $tipo           = strtolower(slugify($_POST["tipo"]));
  $nombre_tipo    = $_POST["nombre"];
  $descuento      = intval($_POST['descuento'])/100;
  $link           = strtolower(slugify($_POST['link']));
  $dto_tag        = $link;
  $exento         = $_POST['exento_iva']?'1':0;

  if ($id_tipo != "") {
    //NO edito el tipo, ni el link, ni el dto_tag porque afecta a estructuras de BD
    $query = "UPDATE tipo_categorias SET categoria = '$nombre_tipo', descuento = $descuento, exento_iva = $exento where id = " . $id_tipo;
    mysqli_query($db, $query);
  } else {
    $sql = "SELECT 1 FROM tipo_categorias WHERE tipo = '$tipo' OR link = '$link'";
    $r = mysqli_query($db,$sql);
    $result = mysqli_fetch_array($r);
    if (isset($result[1])) {
      header("Location: " . HOST . "nuevo_tipo_categoria/error");
      exit;
    } else {
      $query = "INSERT INTO tipo_categorias (tipo, link, dto_tag, categoria, descuento, exento_iva) VALUES ('$tipo', '$link', '$dto_tag', '$nombre_tipo', $descuento, $exento)";
      mysqli_query($db, $query);

      //Modifica la estructura de las tablas "clientes" y "pedidos" para manejar los descuentos
      $sql = "ALTER TABLE pedidos ADD COLUMN client_discount_$dto_tag FLOAT DEFAULT '0'";
      mysqli_query($db, $sql);
      $sql = "ALTER TABLE clientes ADD COLUMN descuento_$dto_tag INT(11) DEFAULT '0'";
      mysqli_query($db, $sql);
    }
  }
  header("Location: " . HOST . "listar_tipo_categorias");
  exit;
}

function get_tipo_categorias($db, $t, $tipo_sel = null)
{
  $t->set_var('base_url', HOST);
  $t->set_block("pl", "tipos_categorias", "_tipos_categorias");

  $query_todas  = "SELECT * FROM tipo_categorias WHERE eliminado = 0";
  $result_todas = mysqli_query($db, $query_todas);

  while ($row = mysqli_fetch_array($result_todas)) {
    if (trim($tipo_sel) == trim($row['tipo'])) {
      $t->set_var("seleccionado", "selected");
    } else {
      $t->set_var("seleccionado", "");
    }

    $t->set_var("nombre_tipo", $row['categoria']);
    $t->set_var("id", $row['tipo']);
    $t->parse("_tipos_categorias", "tipos_categorias", true);
  }
}

function eliminar_tipo_categorias($db, $id_tipo)
{
  $tipo = getTipoById($db,$id_tipo);
  $query  = "SELECT count(id) FROM productos WHERE estado != 'No Disponible' AND  tipo = '" . $tipo."'";
  $result = mysqli_query($db, $query);
  $row    = mysqli_fetch_array($result);
  if ($row['count(id)'] > 0) {
      echo "error";
      exit;
  } else {
    $query = "UPDATE tipo_categorias SET eliminado = 1 WHERE id = " . $id_tipo;
    mysqli_query($db, $query);
    exit;
  }
}