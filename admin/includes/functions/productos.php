<?php
//PRODUCTOS
function getProduct($id)
{
  global $db;
  $query  = "SELECT * FROM productos where id = " . $id;
  $result = mysqli_query($db, $query);
  $row    = mysqli_fetch_array($result);
  return $row;
}

function ver_producto($db, $t)
{
	$t->set_var('base_url', HOST);
	$t->set_file("pl", "nuevo_producto.html");
	$t->set_var("user", $_SESSION["admin_name"]);
	$t->set_var("descuento_producto", '0');
  
	get_tipo_categorias($db, $t);
	get_categorias($db, $t);
	getRangoEdades($db, $t);
	get_marcas($db, $t);
	$t->set_block("pl", "pictures", "_pictures");
	$t->set_var("nuevo_editar", "nuevo");
}

function mostrar_producto($db, $t)
{
	global $url_site;

	$t->set_var('base_url', HOST);
	$t->set_var('url_site', $url_site);

	$t->set_file("pl", "listado_productos.html");
	$t->set_var("user", $_SESSION["admin_name"]);
	$t->set_block("pl", "productos", "_productos");

	$query = "SELECT * FROM productos WHERE borrado IS NULL";

	if ($_GET['estado']) {
		$query .= " AND estado = '" . $_GET['estado'] . "'";
	}
	$result = mysqli_query($db, $query);
	while ($row = mysqli_fetch_array($result)) {
		$t->set_var("id_producto", $row['id']);
		$t->set_var("sku", $row['sku']);
    $t->set_var("codigo_barras", $row['codigo_barras']);
    $t->set_var("nombre_producto", $row['nombre']);
		$tipo = getCategoria($db,$row['tipo']);
		$t->set_var("tipo_producto", $tipo);

		$t->set_var("categoria", getNombreCategoria($row['categoria_id'], $db));
		$t->set_var("marca", getNombreMarca($row['marca_id'], $db));
		/*$t->set_var("tag", getNombreTag($row['tag_id'], $db)); */
		$precio = $row['precio_pvp'];
		$t->set_var("pvp", $precio);
		$t->set_var("descuento", $row['descuento']);
		if ($row['estado'] != "Disponible") {
			$t->set_var("estado", '<span class="badge badge-pill badge-danger">No Disponible</span>');
		} else {
			$t->set_var("estado", '<span class="badge badge-pill badge-success">Disponible</span>');
		}

		$t->set_var('estado_raw', str_replace(" ", "-", strtolower($row['estado'])));

		$t->parse("_productos", "productos", true);
	}
}

function editar_producto($db, $t, $id_producto)
{
	$t->set_var('base_url', HOST);
	$t->set_file("pl", "nuevo_producto.html");
	$t->set_var("user", $_SESSION["admin_name"]);
  
	$query  = "SELECT * FROM productos WHERE id = " . $id_producto;
	$result = mysqli_query($db, $query);
	$row    = mysqli_fetch_array($result);
	$t->set_var("id_producto", $row['id']);
	$t->set_var("nombre_producto", $row['nombre']);
	$t->set_var("video_producto", $row['video']);
	$t->set_var("sku", $row['sku']);
  $t->set_var("codigo_barras", $row['codigo_barras'] != 'NULL' ? $row['codigo_barras'] : '');
	$t->set_var("descripcion_producto", $row['descripcion']);
	/*tipo seleccionado*/

	$t->set_var(trim($row['tipo']), "selected");

	$t->set_var("precio_producto", $row['precio_pvp']);
	$t->set_var("descuento_producto", $row['descuento']);
	$t->set_var("categoria_producto", $row['categoria']);
	$t->set_var("marca_producto", $row['marca_id']);

	//estado disponible y no disponible
	if (trim("Disponible") == trim($row['estado'])) {
		$t->set_var("estado1", "selected");
		$t->set_var("estado2", "");
	} else {
		$t->set_var("estado2", "selected");
		$t->set_var("estado1", "");
	}

	$t->set_var("rango_edad", $row['rango_edad_text']);
	$t->set_var("rango_edad", $row['rango_edad']);
	$t->set_var("en_tv", ($row['en_tv'])?"checked":"");

	get_tipo_categorias($db, $t, $row['tipo']);
	get_categorias($db, $t, $id_producto);
	getRangoEdades($db, $t, $row['rango_edad']);
	get_marcas($db, $t, $id_producto);
	get_imagenes($db, $t, $id_producto, $row['portada']);
	$t->set_var("nuevo_editar", "editar");
}

function guardar_producto($db, $directorio_destino, $path_fotos)
{
	$id_producto   = $_POST["id"];
	$sku           = $_POST["sku"];
	$codigo_barras = isset($_POST["codigo_barras"]) ? $_POST["codigo_barras"] === '' ? "NULL" : $_POST["codigo_barras"] : "NULL";
  $nombre        = $_POST["nombre_producto"];
	$video         = $_POST["video_producto"];
	$categoria     = $_POST["categoria"];
	$marca         = $_POST["marca"];
	$tags          = $_POST["tags"];
	$precio        = $_POST["precio"];
	$descuento     = $_POST["descuento"];
	$tipo_producto = $_POST["tipo_producto"];
	$descripcion   = $_POST["descripcion_producto"];
	$edad          = $_POST["rango_id"] === '' ? "NULL" : $_POST['rango_id'];
	$estado        = $_POST["estado"];
	//$edad          = $_POST["rango_edad"];
	$en_tv         = ($_POST["en_tv"] == 'true')?"1":"0";
	$portada       = ($_POST['portada'] != 'undefined')?$_POST['portada']:"NULL";

  $sqlSKU = "SELECT 1 FROM productos WHERE sku = '$sku' AND borrado IS NULL ";
  if ($codigo_barras != 'NULL') $sqlCb = "SELECT 1 FROM productos WHERE codigo_barras = '$codigo_barras' AND borrado IS NULL ";

	if ($_POST['accion'] == "editar") {
    $sqlSKU .= " AND id <> '$id_producto'";
    if ($codigo_barras != 'NULL') {
      $sqlCb .= " AND id <> '$id_producto'";
      $r = mysqli_query($db,$sqlCb);
      $result = mysqli_fetch_array($r);
      if (isset($result[1])) {
        http_response_code(400);
        echo 	'cod_bar';
        exit;
      }
    }
    $r = mysqli_query($db,$sqlSKU);
    $result = mysqli_fetch_array($r);
    if (isset($result[1])) {
			http_response_code(400);
			echo 	'SKU';
      exit;
    }
		$query = "UPDATE productos SET nombre = '$nombre', categoria_id = '$categoria', marca_id = '$marca',
		precio_pvp = '$precio', descuento = '$descuento', estado = '$estado', sku = '$sku', codigo_barras = '$codigo_barras',
		tipo = '$tipo_producto', descripcion = '$descripcion', rango_edad = $edad, video = '$video', en_tv = $en_tv, portada = $portada, fecha_actualizacion = now() WHERE id = '$id_producto'";
} else {
    $r = mysqli_query($db,$sqlSKU);
    $result = mysqli_fetch_array($r);
    if (isset($result[1])) {
			http_response_code(400);
			echo 	'SKU';
      exit;
    }
    if ($codigo_barras != 'NULL') {
      $r = mysqli_query($db,$sqlCb);
      $result = mysqli_fetch_array($r);
      if (isset($result[1])) {
        http_response_code(400);
        echo 	'cod_bar';
        exit;
      }
    }

		$query = "INSERT INTO productos (sku, codigo_barras, nombre, descripcion, tipo, precio_pvp, descuento, estado, rango_edad, categoria_id, marca_id, video, en_tv) VALUES
	('$sku','$codigo_barras','$nombre','$descripcion', '$tipo_producto', '$precio', '$descuento', '$estado', $edad, '$categoria', '$marca', '$video', $en_tv)";
	}


	mysqli_query($db, $query);

	if ($_POST['accion'] == "nuevo") {
		$id_producto = mysqli_insert_id($db);

		sendWhatsAppNewProduct($id_producto);
	}

	if ($_POST['accion'] == "editar") {
		$delete_tags = "DELETE FROM producto_tags WHERE id_producto = " . $id_producto;
		mysqli_query($db, $delete_tags);
	}

	if ($tags) {
		$array_tags = explode(",", $tags);
		foreach ($array_tags as $valor) {
			$insert_tag = "INSERT INTO producto_tags (id_producto, id_tag) VALUES ('$id_producto', '$valor')";
			mysqli_query($db, $insert_tag);
		}
	}

	if (!empty($_FILES['files'])) {
		//include("../resize-class.php");
		if (($_POST['accion'] == "nuevo") || ($_POST['accion'] == "editar")) {

			$srcPath  = createDir($directorio_destino . getNumberType($tipo_producto) . '/' . $id_producto) . '/';

			$path_fotos .= getNumberType($tipo_producto) . '/' . $id_producto . '/';

			$counter = 0;

			foreach ($_FILES['files']['name'] as $key => $error) {
				$info     = new SplFileInfo($_FILES['files']['name'][$key]);
				$tmp_name = $_FILES["files"]["tmp_name"][$key];
				$img_type = $_FILES["files"]["type"][$key];
				$exte     = $info->getExtension();

				$nameFile = slugify($nombre);
				//$nameFile .= '_' . $id_producto;
				$nameFile .= "_" . (date('U') + $counter) . "." . $exte;
				//subir_fichero($srcPath, $tmp_name, $img_type, $nameFile);
				subir_fichero($srcPath, $tmp_name, $img_type, 'original-' . $nameFile);

				$thumbFile = "thumb-" . $nameFile;
				$resizedFile = $nameFile;

				list($ancho, $alto) = getimagesize($srcPath . 'original-' . $nameFile);
				$nuevo_ancho = ($ancho > 1920)?1920:$ancho;
				$nuevo_alto = ($ancho > 1920)?$alto*1920/$ancho:$alto;

				createResizedImage($imagePath = $srcPath . 'original-' . $nameFile, $newPath = $srcPath . $thumbFile, $newWidth = 192, $newHeight = ($alto*192/$ancho), $outExt = 'DEFAULT');

				createResizedImage($imagePath = $srcPath . 'original-' . $nameFile, $newPath = $srcPath . $resizedFile, $newWidth = $nuevo_ancho, $newHeight = $nuevo_alto, $outExt = 'DEFAULT');
				
				$query = "INSERT INTO `media`(`producto_id`, `nombre`, `path`, `path_thumb`, `filetype`) VALUES ('" . $id_producto . "','" . $nameFile . "','" . $path_fotos . $nameFile . "','" . $path_fotos . $thumbFile . "','" . $exte . "')";
				mysqli_query($db, $query);

				$counter++;
			}
		}
	}
	header("Location: " . HOST . "editar_producto/" . $id_producto);
	exit;
}

function desactivar_producto($db, $id_producto)
{
	$query = "UPDATE productos SET estado = 'No Disponible' WHERE id = " . $id_producto;
	mysqli_query($db, $query);
	header("Location: " . HOST . "productos");
	exit;
}

function activar_producto($db, $id_producto)
{
	$query = "UPDATE productos SET estado = 'Disponible' WHERE id = " . $id_producto;
	mysqli_query($db, $query);
	header("Location: " . HOST . "productos");
	exit;
}

function eliminar_producto($db, $id_prod)
{
	// we check if the product does not exist in any order
	$query = "SELECT count(*) AS count FROM pedidos WHERE JSON_SEARCH(detalle, 'one', $id_prod, null, '$[*][0].id') IS NOT null";

	$result = mysqli_query($db, $query);
	$row    = mysqli_fetch_array($result);

	if ($row['count'] == 0) {
		$query = "DELETE FROM productos WHERE id = $id_prod";
		$result = mysqli_query($db, $query);
		header("Location: " . HOST . "productos?deleted=true");
	}
	else {
		header("Location: " . HOST . "productos?deleted=false");
	}
	exit;

}

function obtener_precio_producto($db, $id)
{
  $query  = "SELECT precio_pvp FROM productos WHERE id =" . $id;
  $result = mysqli_query($db, $query);
  echo json_encode(mysqli_fetch_array($result));
  exit;
}