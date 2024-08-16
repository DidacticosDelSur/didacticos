<?php

function getOrder($id)
{
	global $db;

	$query =  "SELECT * FROM pedidos WHERE id = $id LIMIT 1";

	$result = mysqli_query($db, $query);

	return $result;
}

function createOrder()
{
	global $db, $t;
	if (empty($_POST)) {
		$t->set_file("pl", "nuevo_pedido.html");

		$query  = "SELECT * FROM clientes WHERE (borrado IS NULL) AND verficado = '1'";

		if (!$_SESSION["super_admin"]) {
			$query .= " AND vendedor_id = '" . $_SESSION["admin_id"] . "'";
		}
		$result = mysqli_query($db, $query);
		$t->set_block("pl", "cliente", "_cliente");
		while ($row = mysqli_fetch_array($result)) {
			$t->set_var("nombre_cliente", $row['nombre'] . " " . $row['apellido'] ." - " . $row['email'] . " (" . $row['tango_id'] . ")");
			$t->set_var("id_cliente", $row['id']);
			$t->parse("_cliente", "cliente", true);
		}
	}
	else {
		$total = 0;

		$detalle_pedido  = json_encode([], JSON_UNESCAPED_UNICODE);
		$fecha		   = date("Y-m-d H:m:s");
		$cliente_id	  = $_POST["cliente"];

		$cliente = get_datos_cliente($db, $cliente_id);

		$direccion	   = "";
		$estado		  = "Nuevo";

		$provincia	   = $cliente['provincia_id'];
		$ciudad		  = $cliente['ciudad_id'];
    //VER ACA NO SE LLAMA NUNCA
		$client_discount_libros = round($result["descuento_libros"] / 100, 2);
		$client_discount_didacticos = round($result["descuento_didacticos"] / 100, 2);
		$client_discount_juguetes = round($result["descuento_juguetes"] / 100, 2);

		$vendedor_id = $_SESSION["admin_id"];

		$query		   = "INSERT INTO pedidos (fecha, estado, cliente_id, detalle, direccion_envio, provincia_id, ciudad_id, total, client_discount_books, client_discount_games, client_discount_toys, vendedor_id)
			VALUES ('$fecha','$estado','$cliente_id','$detalle_pedido','$direccion','$provincia','$ciudad','$total', '$client_discount_libros', '$client_discount_didacticos', '$client_discount_juguetes', $vendedor_id)";
		$result = mysqli_query($db, $query);
		if ($result) {
			$id_pedido = mysqli_insert_id($db);
			header("Location: " . HOST . "editar_pedido/" . $id_pedido);
		}
	}
}

/************************************************ REFACTOR CATEGORIAS **********************************************************/
//VG Marzo 2024  Refactor categorias ADMIN. Este archivo contiene todas las funciones vinculadas con el pedido
function formatear_pedido($pedido){
  $aux_ped = [];
  $update_pedido = false;
  foreach ($pedido as $i => $value) {
    if (is_numeric($i)) {
      //Parche transición de carritos. Es para los carritos que quedaron con el formato viejo al momento de la migración
      switch ($i) {
        case 0:
          $i = 'libro';
          break;
        case 1:
          $i = 'juego';
          break;
        case 2:
          $i = 'juguete';
          break;
        case 3:
          $i = 'escolar';
          break;
      }
      $aux_ped[$i]=$value;
      $update_pedido = true;
    }
  }
  if ($update_pedido) {
    $pedido = $aux_ped;
  }
  error_log('Pedido formateado: '.json_encode($pedido));
  return $pedido;
}
function editar_pedido($db, $t, $templates, $id_pedido)
{ //VG: Refactor categorias Marzo 2024
  $t->set_var('base_url', HOST);
  if (!isset($_SESSION['PEDIDO_ACTUAL']) || $_SESSION['PEDIDO_ACTUAL'] == "") {
      $_SESSION['PEDIDO_ACTUAL'] = array(['datos']);
  }

  $t->set_file("pl", "datos_pedido.html");
  $t->set_var("user", $_SESSION["admin_name"]);
  $query                              = "SELECT * FROM pedidos WHERE id = " . $id_pedido;
  $result                             = mysqli_query($db, $query);
  $_SESSION['PEDIDO_ACTUAL']['datos'] = mysqli_fetch_array($result);

  $pedido = $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'];
  $pedido = preg_replace('/[[:cntrl:]]/', '', $pedido);

  $pedido = json_decode($pedido, true);
  $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'] = formatear_pedido($pedido);
  
  $cliente = get_datos_cliente($db, $_SESSION['PEDIDO_ACTUAL']['datos']['cliente_id']);
  if ($cliente['vendedor_id'] == $_SESSION["admin_id"]) { // si el usuario logueado es el vendedor
      $t->set_var("clases", 'vendedor');
  }


  pintar_datos_pedido($t, $templates, $db);

}

function pintar_datos_pedido($t, $templates, $db)
{//VG: Refactor categorias Marzo 2024
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "datos_pedido.html");
  $t->set_var("id_cliente", $_SESSION['PEDIDO_ACTUAL']['datos']['cliente_id']);
  $t->set_var("id", $_SESSION['PEDIDO_ACTUAL']['datos']['id']);
  $t->set_var("fecha", $_SESSION['PEDIDO_ACTUAL']['datos']['fecha']);
  $t->set_var("direccion", $_SESSION['PEDIDO_ACTUAL']['datos']['direccion_envio']);
  $total = calcularTotal($db);
  $t->set_var("total", number_format($total, 2));
  $t->set_var("total_unformatted", $total);

  $es_vendedor = $_SESSION['PEDIDO_ACTUAL']['datos']['vendedor_id'];
  $des_cliente = [];
  $t->set_block("pl", "categ", "_categ");

  if ($es_vendedor) {

    $queryvendedor        = 'SELECT CONCAT(v.nombre," ",v.apellido) as nombre_vendedor
    FROM vendedores v
    WHERE v.id = ' . $es_vendedor;
    $nombrevendedor = mysqli_query($db, $queryvendedor);
    $rowv           = mysqli_fetch_array($nombrevendedor);
    $badge = '<h3><span class="badge badge-info">Creado por el vendedor ' . $rowv['nombre_vendedor'] . '</span></h3><br>';
  }
  else {
      $badge = '';
  }

  $t->set_var("observaciones", $badge .$_SESSION['PEDIDO_ACTUAL']['datos']['observaciones']);
  $descuento_cliente = '';

  $cat = getTipoCategorias($db);
  
  foreach ($cat as $i => $value) {
    $t->set_var('categoria',$value['categoria']);
    $t->set_var('tipo',$value['tipo']);
    $tip = translateType($db,$value['tipo']);
    if (isset($_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_'.$tip]) && $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_'.$tip] > 0) {
      $des_cliente[$value['tipo']] = $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_'.$tip] * 100;
      $des_cliente[$value['tipo']] = '(descuento cliente ' . $des_cliente[$value['tipo']] . '%)';
      $t->set_var('descuento', $des_cliente[$value['tipo']]);
    } else {
      $t->set_var('descuento', '');
    }
    if ($value['tipo'] == 'libro') {
      $t->set_var('showPvp', 'style = "min-width: 120px; display: block;"');
      $t->set_var('showPrecio', 'style = "display: none;"');
    } else {
      $t->set_var('showPrecio', 'style = "min-width: 120px; display: block;"');
      $t->set_var('showPvp', 'style = "display: none;"');
    }
    
    pintar_items_pedido($t,$templates,$value['tipo'],$tip);
    error_log('Llamando desde pintar_datos_pedido');
    $t->set_var('subtotal',number_format(calcularSubtotal($db,$value['tipo'],$tip), 2));
    $t->parse("_categ", "categ", true);
  }

  if ($_SESSION['PEDIDO_ACTUAL']['datos']['estado'] == "Nuevo") {
      $t->set_var("check_nuevo", "selected");
  } elseif ($_SESSION['PEDIDO_ACTUAL']['datos']['estado'] == "En proceso") {
      $t->set_var("check_enproceso", "selected");
  } elseif ($_SESSION['PEDIDO_ACTUAL']['datos']['estado'] == "Finalizado") {
      $t->set_var("check_finalizado", "selected");
  } else {
      $t->set_var("check_cancelado", "selected");
  }

  if (!$_SESSION['super_admin']) {
      $t->set_var('disabled', 'disabled');
  }

  $sql_cliente    = "SELECT nombre, apellido,tango_id FROM clientes WHERE id = " . $_SESSION['PEDIDO_ACTUAL']['datos']['cliente_id'];
  $result         = mysqli_query($db, $sql_cliente);
  $result_cliente = mysqli_fetch_array($result);
  $t->set_var("cliente", $result_cliente['apellido'] . ", " . $result_cliente['nombre']);
  $t->set_var("tango", $result_cliente['tango_id']);
}

function pintar_items_pedido($t, $templates, $tipo, $tip)
{//VG: Refactor categorias Marzo 2024

  $tf = new Template($templates, "remove");
  $tf->set_var('base_url', HOST);
  $tf->set_file("pl", "datos_items_pedido.html");
  $tf->set_block("pl", "items", "_items");
  if (!empty($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][$tipo])) {
    $productos = $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][$tipo];
    foreach($productos as $i => $value){
      $producto = $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][$tipo][$i];

      if ($producto == null) continue;

      $tf->set_var("sku", $producto['sku']);
      $tf->set_var("id", $producto['id']);
      $tf->set_var("nombre", $producto['nombre']);
      $tf->set_var("precio", number_format($producto['precio'], 2));
      $tf->set_var("cantidad", $producto['cantidad']);
      $tf->set_var("observaciones", ($producto['observaciones'])?urldecode("Observaciones: " . $producto['observaciones']):'');

      $cantidad  = $producto['cantidad'];
      $precio    = $producto['precio'];
      $descuento_acumulativo = (1 - $producto['descuento']/100) * (1-$_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_'.$tip]);
      $descuento = 1 - (1 - $producto['descuento']/100) * (1-$_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_'.$tip]);
      //$descuento = $producto['descuento'] + $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_'.$tip] * 100;
      $total     = ($precio - ($precio * $descuento)) * $cantidad;
      $tf->set_var("descuento", $descuento*100);
      $tf->set_var("tipo", $tipo);
      $tf->set_var("total_precio", number_format($total, 2));

      $tf->parse("_items", "items", true);
     
    } 
    $item = $tf->parse("MAIN", "pl");
    $t->set_var("producto_item_".$tipo, $item);
  }
}

function calcularSubtotal($db,$tipo,$tip)
{//VG: Refactor categorias Marzo 2024
  error_log('Calculando subtotal de: '.$tipo);
  error_log('Detalle: '.json_encode($_SESSION['PEDIDO_ACTUAL']['datos']['detalle']['libro']));
  $subtotal   = 0;
  $iva             = 1.21;
  $descuento_cliente = 0;

  if (isset($_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_'.$tip])) {
    $descuento_cliente = (float) $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_'.$tip];
  }

  if (isset($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][$tipo])) {
    $ped = $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][$tipo];
    for ($i = 0; $i < count($ped); $i++) {
      $precio = $ped[$i]['precio'];
      if (isset($ped[$i]['descuento'])) {
          $precio = $precio - $precio * ($ped[$i]['descuento'] / 100);
      }
      $subtotal += $precio * $ped[$i]['cantidad'];
    }
  }

  $subtotal = $subtotal - ($subtotal * $descuento_cliente);
  
  if (!tipo_exento($db,$tipo)){
    $subtotal = $subtotal * $iva;
  }

  return round($subtotal, 2);
    
}

function calcularTotal($db)
{//VG: Refactor categorias Marzo 2024
  $total = 0;
  $tipos = getTipos($db);
  foreach($tipos as $t) {
    $tip = translateType($db,$t);
    error_log('Llamando desde CalcularTotal');
    $total += calcularSubtotal($db,$t,$tip);
  }
  $total = round($total, 2);
  return $total;
}

function eliminar_producto_pedido($db, $t, $templates, $id_producto)
{//VG: Refactor categorias Marzo 2024
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "datos_pedido.html");

  $query  = "SELECT * FROM productos where id = " . $id_producto;
  $result = mysqli_query($db, $query);
  $row    = mysqli_fetch_array($result);
  $tipo   = $row["tipo"]; 

  $pedido = $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'];

  $value = array_search($id_producto, array_column($pedido[$tipo], 'id'), false);
  array_splice($pedido[$tipo], $value, 1);

  $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'] = $pedido;
  $t->set_var("change", "style='display:inline-block;'");
  $t->set_var("hide", "style='display:none;'");

  pintar_datos_pedido($t, $templates, $db);

}

function guardar_producto_pedido($t, $db, $templates, $id_producto, $cant_producto)
{//VG: Refactor categorias Marzo 2024
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "datos_pedido.html");
  $producto                                      = getProductPedido($db, $id_producto, $cant_producto);

  $pedido = $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'];

  $tipo = $producto['tipo'];

  if (!isset($pedido[$tipo])) {
    $pedido[$tipo] = [];
  }

  $value = array_search($producto['id'], array_column($pedido[$tipo], 'id'), false);
  if ($value !== false && $value !== null) {
    $pedido[$tipo][$value]->cantidad = $pedido[$tipo][$value]->cantidad + $producto['cantidad'];
  } else if ($value !== null) {
    array_push($pedido[$tipo], $producto);
  }

  $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'] = $pedido;

  $t->set_var("change", "style='display:inline-block;'");
  $t->set_var("hide", "style='display:none;'");
  pintar_datos_pedido($t, $templates, $db);
  
}

function actualizar_pedido_actual($db, $t, $templates, $id_producto, $cant_producto)
{//VG: Refactor categorias Marzo 2024
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "datos_pedido.html");
  $producto = getProductPedido($db, $id_producto, $cant_producto);

  $pedido = $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'];

  $tipo = $producto['tipo'];

  $value = array_search($producto['id'], array_column($pedido[$tipo], 'id'), false);

  $pedido[$tipo][$value]['cantidad'] = $cant_producto;


  $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'] = $pedido;

  $t->set_var("change", "style='display:inline-block;'");
  $t->set_var("hide", "style='display:none;'");

  pintar_datos_pedido($t, $templates, $db);
}

/******************* RE DISTRIBUCIÓN DE FUNCIONES DEL MISMO TIPO **************************/
function detalle_pedido($db, $id)
{
	$query  = "SELECT * FROM pedidos WHERE id = " . $id;
	$result = mysqli_query($db, $query);
	$result = mysqli_fetch_array($result);
  $pedido = $result['detalle'];
  $pedido = preg_replace('/[[:cntrl:]]/', '', $pedido);
  $pedido = json_decode($pedido, true);

  $result['detalle'] = formatear_pedido($pedido);
 
  $descuentos = [];
  $tipos = getTipos($db);
  foreach ($tipos as $tipo) {
    $trans = translateType($db,$tipo);
    $descuentos[$tipo] = $result['client_discount_'.$trans] * 100;
  }

  $result['descuentos'] = $descuentos;

	echo json_encode($result);
	exit;
}

function imprimir_pedido($db, $t, $templates, $id)
{
	$t->set_var('base_url', HOST);
	$t->set_file("pl", "imprimir.html");
	$query     = "SELECT * FROM pedidos WHERE id = '$id' LIMIT 1";
	$result    = mysqli_query($db, $query);

	$row = mysqli_fetch_array($result);

	$id        = $row["id"];
	$fecha     = $row["fecha"];
	$detalle   = $row["detalle"];
	$cliente   = $row["cliente_id"];
	$direccion = $row["direccion_envio"];
	$provincia = $row["provincia_id"];
	$ciudad    = $row["ciudad_id"];


	$es_vendedor = $row['vendedor_id'];

	if ($es_vendedor) {

		$queryvendedor        = 'SELECT CONCAT(v.nombre," ",v.apellido) as nombre_vendedor
		FROM vendedores v
		WHERE v.id = ' . $es_vendedor;
		$nombrevendedor = mysqli_query($db, $queryvendedor);
		$rowv           = mysqli_fetch_array($nombrevendedor);
		$badge = 'Creado por el vendedor ' . $rowv['nombre_vendedor'] . '<br>';
	}
	else {
		$badge = '';
	}

	$observaciones    = $badge . $row["observaciones"];

	$t->set_var("id", $id);
	$t->set_var("fecha", $fecha);
	$t->set_var("direccion", $direccion);
	$t->set_var("observaciones", $observaciones);
	$detalle = array($row["detalle"]);

	$querynombreciudad    = "SELECT nombre FROM localidades WHERE id = '$ciudad'";
	$querynombreprovincia = "SELECT nombre FROM provincias WHERE id = '$provincia'";
	$querynombrecliente   = "SELECT c.nombre,c.apellido, c.tango_id FROM clientes c WHERE c.id = '$cliente'";
	$queryvendedor        = 'SELECT CONCAT(v.nombre," ",v.apellido) as nombre_vendedor
	FROM clientes c
	JOIN vendedores v
	on c.vendedor_id = v.id WHERE c.id = ' . $cliente;

	$nombreciudad = mysqli_query($db, $querynombreciudad);
	$rowc         = mysqli_fetch_array($nombreciudad);
	if ($rowc != null) {
		$t->set_var("ciudad", $rowc["nombre"]);
	}
	$nombreprovincia = mysqli_query($db, $querynombreprovincia);
	$rowp            = mysqli_fetch_array($nombreprovincia);
	if ($rowp != null) {
		$t->set_var("provincia", $rowp["nombre"]);
	}
	$nombrecliente = mysqli_query($db, $querynombrecliente);
	$rown          = mysqli_fetch_array($nombrecliente);
	if ($rown != null) {
		$t->set_var("nombre", $rown["nombre"]);
		$t->set_var("apellido", $rown["apellido"]);
		$t->set_var("tango_id", $rown["tango_id"]);
	}
	$nombrevendedor = mysqli_query($db, $queryvendedor);
	$rowv           = mysqli_fetch_array($nombrevendedor);
	if ($rowv != null) {
		$t->set_var("vendedor", $rowv["nombre_vendedor"]);
	}

  $tipos = getTipoCategorias($db);
  $cadena = '';
  foreach ($tipos as $id => $value) {
    $tag = $value['dto_tag'];
    $descuento = $row['client_discount_'.$tag]*100;
    $cadena .= "<span class='value'>". $value['categoria'].": ". $descuento .'%</span>';
    if (next($tipos)==true) $cadena .= " - ";
  }
  $t->set_var('cadena',$cadena);
}

function listado_pedidos($db, $t)
{
	$t->set_var('base_url', HOST);
	$_SESSION['PEDIDO_ACTUAL'] = array(['datos']);
	$t->set_file("pl", "listado_pedidos.html");
	$t->set_var("user", $_SESSION["admin_name"]);
	$t->set_block("pl", "pedidos", "_pedidos");
	if ($_SESSION['super_admin'] == true) {
		$query = "SELECT *, pcia.nombre as nombre_provincia FROM pedidos p
		JOIN provincias pcia ON p.provincia_id = pcia.id
		JOIN localidades l ON p.ciudad_id = l.id AND pcia.id = l.provincia_id";
	} else {
		$query = "SELECT *, pcia.nombre as nombre_provincia FROM pedidos p
		JOIN clientes c ON p.cliente_id = c.id
		JOIN provincias pcia ON p.provincia_id = pcia.id
		JOIN localidades l ON p.ciudad_id = l.id AND pcia.id = l.provincia_id WHERE c.vendedor_id =" . $_SESSION["admin_id"];
	}
	if ($_GET['estado']) {
		$query .= ' AND p.estado = "' . $_GET['estado'] . '"';
	}
	$result = mysqli_query($db, $query);
	while ($row = mysqli_fetch_array($result)) {
		$t->set_var("id", $row[0]);
		$t->set_var("fecha", $row['fecha']);
		$t->set_var("estado", $row['estado']);
		$t->set_var("canbedeleted", ($row['estado'] == 'Nuevo')?'style="display: inline-block;"':'style="display: none;"');
		$t->set_var("hidden", ($row['estado'] == 'Finalizado')?"hidden":"");
		$datos_cliente = get_datos_cliente($db, $row['cliente_id']);
		$t->set_var("cliente", $datos_cliente['apellido'] . ", " . $datos_cliente['nombre']);
		$t->set_var("direccion", $row['direccion_envio']);
		$t->set_var("provincia", $row['nombre_provincia']);
		$t->set_var("ciudad", strtolower($row["nombre"]));
		$t->set_var("es_vendedor", ($row['vendedor_id'] != null)?'':'d-none');
		$t->set_var("cp", $row["codigo_postal"]);
		$t->set_var("total", $row['total']);
		$t->parse("_pedidos", "pedidos", true);
	}
}

function guardar_pedido($db)
{
	$id_pedido = $_POST["id"];
	$direccion = $_POST["direccion"];
	$estado    = $_POST["estado"];
	$total     = $_POST["total"];
	$detalle   = json_encode($_SESSION['PEDIDO_ACTUAL']["datos"]["detalle"]);
	$query     = "UPDATE pedidos SET estado ='$estado', direccion_envio = '$direccion', detalle = '$detalle', total = '$total' WHERE id = $id_pedido";

	mysqli_query($db, $query);
	$_SESSION['PEDIDO_ACTUAL'] = [];
	header("Location: " . HOST . "listar_pedidos");
}

function deleteOrder($id) {
	global $db;
	$query = "DELETE FROM pedidos WHERE id = $id LIMIT 1";
	mysqli_query($db, $query);
}