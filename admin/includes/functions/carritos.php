<?php

function ver_carrito($db, $t, $templates, $id_carrito)
{ //VG: Feature nueva Abril 2024
  $t->set_var('base_url', HOST);  
  $t->set_file("pl", "datos_carrito.html");

  $_SESSION['CARRITO_ACTUAL']['datos'] = [];
  $t->set_var("user", $_SESSION["admin_name"]);
  $query                              = "SELECT *, DATE_FORMAT(fecha_ultima_actualizacion, '%d/%m/%Y %H:%i') as fecha_ultima_actualizacion FROM carritos WHERE id = " . $id_carrito;
  $result                             = mysqli_query($db, $query);
  $_SESSION['CARRITO_ACTUAL']['datos'] = mysqli_fetch_array($result);
  $carrito = $_SESSION['CARRITO_ACTUAL']['datos']['detalle'];
  $carrito = preg_replace('/[[:cntrl:]]/', '', $carrito);

  $carrito = json_decode($carrito, true);

  $aux_car = [];
  $update_carrito = false;
  foreach ($carrito as $i => $value) {
    if (is_numeric($i)) {
      //Parche transición de carritos. Eliminar cuando este funcional Es para los carritos que quedaron con el formato viejo al momento de la migración
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
      $aux_car[$i]=$value;
      $update_carrito = true;
    }
  }
  if ($update_carrito) {
      $_SESSION['CARRITO_ACTUAL']['datos']['detalle'] = $aux_car;
  } else {
    $_SESSION['CARRITO_ACTUAL']['datos']['detalle'] = $carrito;
  }

  $cliente = get_datos_cliente($db, $_SESSION['CARRITO_ACTUAL']['datos']['cliente_id']);

  $cat = getTipoCategorias($db);
  foreach ($cat as $i => $value) {
    $tip = translateType($db,$value['tipo']);
    $_SESSION['CARRITO_ACTUAL']['datos']['descuento_'.$tip] = $cliente['descuento_'.$tip];
  }

  if ($cliente['vendedor_id'] == $_SESSION["admin_id"]) { // si el usuario logueado es el vendedor
    $t->set_var("clases", 'vendedor');
  } 

  $t->set_var("total", number_format(calcularTotalCarrito($db), 2));

  pintar_datos_carrito($t, $templates, $db);

}


function pintar_datos_carrito($t, $templates, $db)
{//VG: Feature nueva Abril 2024
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "datos_carrito.html");
  $t->set_var("id_cliente", $_SESSION['CARRITO_ACTUAL']['datos']['cliente_id']);
  $t->set_var("id", $_SESSION['CARRITO_ACTUAL']['datos']['id']);
  $t->set_var("fecha_ultima_actualizacion", $_SESSION['CARRITO_ACTUAL']['datos']['fecha_ultima_actualizacion']);
  $total = calcularTotalCarrito($db);
  $t->set_var("total", number_format($total, 2));
  $t->set_var("total_unformatted", $total);

  $es_vendedor = $_SESSION['CARRITO_ACTUAL']['datos']['vendedor_id'];
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
  $t->set_var("observaciones", $badge );

  $descuento_cliente = '';

  $cat = getTipoCategorias($db);
  
  foreach ($cat as $i => $value) {
    $t->set_var('categoria',$value['categoria']);
    $t->set_var('tipo',$value['tipo']);
    $tip = translateType($db,$value['tipo']);
    if (isset($_SESSION['CARRITO_ACTUAL']['datos']['descuento_'.$tip]) && $_SESSION['CARRITO_ACTUAL']['datos']['descuento_'.$tip] > 0) {
      $des_cliente[$value['tipo']] = $_SESSION['CARRITO_ACTUAL']['datos']['descuento_'.$tip];
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
    
    pintar_items_carrito($t,$templates,$value['tipo'],$tip);
    $t->set_var('subtotal',number_format(calcularSubtotalCarrito($db,$value['tipo'],$tip), 2));
    $t->parse("_categ", "categ", true);
  }

  if (!$_SESSION['super_admin']) {
      $t->set_var('disabled', 'disabled');
  }

  $sql_cliente    = "SELECT nombre, apellido,tango_id FROM clientes WHERE id = " . $_SESSION['CARRITO_ACTUAL']['datos']['cliente_id'];
  $result         = mysqli_query($db, $sql_cliente);
  $result_cliente = mysqli_fetch_array($result);
  $t->set_var("cliente", $result_cliente['apellido'] . ", " . $result_cliente['nombre']);
  $t->set_var("tango", $result_cliente['tango_id']);
}

function pintar_items_carrito($t, $templates, $tipo, $tip)
{//VG: Feature nueva Abril 2024
  $tf = new Template($templates, "remove");
  $tf->set_var('base_url', HOST);
  $tf->set_file("pl", "datos_items_carrito.html");
  $tf->set_block("pl", "items", "_items");
  if (!empty($_SESSION['CARRITO_ACTUAL']['datos']['detalle'][$tipo])) {
    for ($i = 0; $i < count($_SESSION['CARRITO_ACTUAL']['datos']['detalle'][$tipo]); $i++) {
      $producto = $_SESSION['CARRITO_ACTUAL']['datos']['detalle'][$tipo][$i];

      if ($producto == null) continue;

      $tf->set_var("sku", $producto['sku']);
      $tf->set_var("id", $producto['id']);
      $tf->set_var("nombre", $producto['nombre']);
      $tf->set_var("precio", number_format($producto['precio'], 2));
      $tf->set_var("cantidad", $producto['cantidad']);
      $tf->set_var("observaciones", ($producto['observaciones'])?urldecode("Observaciones: " . $producto['observaciones']):'');

      $cantidad  = $producto['cantidad'];
      $precio    = $producto['precio'];
      $descuento_acumulativo = (1 - $producto['descuento']/100) * (1-$_SESSION['CARRITO_ACTUAL']['datos']['client_discount_'.$tip]);
      $descuento = 1 - (1 - $producto['descuento']/100) * (1-$_SESSION['CARRITO_ACTUAL']['datos']['client_discount_'.$tip]);
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

function eliminar_carrito($db,$id){
  //VG: Feature nueva Abril 2024
  $query = "DELETE FROM carritos WHERE id = " . $id;
  mysqli_query($db, $query);
}

function imprimir_carrito($db, $t, $templates, $id)
{//VG: Feature nueva Abril 2024
	$t->set_var('base_url', HOST);
	$t->set_file("pl", "imprimir_carrito.html");
	$query     = "SELECT car.*, CONCAT(c.apellido, ', ', c.nombre, ' (',c.tango_id,')') AS nombre_comprador, l.nombre AS nombre_ciudad, p.nombre as nombre_provincia, CONCAT(v.nombre,' ',v.apellido) as nombre_vendedor, DATE_FORMAT(car.fecha_ultima_actualizacion, '%d/%m/%Y %H:%i') as fecha_ultima_actualizacion 
                FROM carritos car 
                LEFT JOIN clientes c ON c.id = car.cliente_id
                INNER JOIN localidades l ON c.ciudad_id = l.id
                INNER JOIN provincias p ON c.provincia_id = p.id
                LEFT JOIN vendedores v ON v.id = c.vendedor_id
                WHERE car.id = '$id' LIMIT 1";
	$result    = mysqli_query($db, $query);

	$row = mysqli_fetch_array($result);

	$id        = $row["id"];
	$fecha     = $row["fecha_ultima_actualizacion"];
	$detalle   = $row["detalle"];
	$cliente   = $row["cliente_id"];
  $cliente_nombre = $row['nombre_comprador'];
  $ciudad = $row['nombre_ciudad'];
  $provincia = $row['nombre_provincia'];
  $vendedor_nombre = $row['nombre_vendedor'];

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
  $t->set_var("observaciones", $badge );

	$t->set_var("id", $id);
	$t->set_var("fecha", $fecha);
	$detalle = array($row["detalle"]);

  $t->set_var("ciudad", $ciudad);
  $t->set_var("provincia", $provincia);
  $t->set_var("cliente", $cliente_nombre);
	
  $t->set_var("vendedor", $vendedor_nombre);
	$querycliente   = "SELECT * FROM clientes c WHERE c.id = '$cliente'";
  $datoscliente = mysqli_query($db, $querycliente);
	$rown          = mysqli_fetch_array($datoscliente);
  if ($rown != null) {
    $tipos = getTipoCategorias($db);
    $cadena = '';
    foreach ($tipos as $id => $value) {
      $tag = $value['dto_tag'];
      $descuento = $rown['descuento_'.$tag];
      $cadena .= "<span class='value'>". $value['categoria'].": ". $descuento .'%</span>';
      if (next($tipos)==true) $cadena .= " - ";
    }
    $t->set_var('cadena',$cadena);
  }
  
}

function detalle_carrito($db, $id)
{//VG: Feature nueva Abril 2024
  
	$query  = "SELECT * FROM carritos car 
             LEFT JOIN clientes c ON car.cliente_id = c.id
             WHERE car.id = " . $id;
	$result = mysqli_query($db, $query);
	$result = mysqli_fetch_array($result);

  $descuentos = [];
  $tipos = getTipos($db);
  foreach ($tipos as $tipo) {
    $trans = translateType($db,$tipo);
    $descuentos[$tipo] = $result['descuento_'.$trans];
  }
  $result = preg_replace('/[[:cntrl:]]/', '', $result);

  $result['descuentos'] = $descuentos;
	echo json_encode($result);
	exit;
}

function calcularSubtotalCarrito($db,$tipo,$tip)
{//VG: Feature nueva Abril 2024
  
  $subtotal   = 0;
  $iva             = 1.21;
  $descuento_cliente = 0;

  if (isset($_SESSION['CARRITO_ACTUAL']['datos']['descuento_'.$tip])) {
    $descuento_cliente = (float) $_SESSION['CARRITO_ACTUAL']['datos']['descuento_'.$tip]/100;
  }

  $ped = $_SESSION['CARRITO_ACTUAL']['datos']['detalle'][$tipo];
  if (isset($ped)){
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

function calcularTotalCarrito($db)
{//VG: Feature nueva Abril 2024
  
  $total = 0;
  $tipos = getTipos($db);
  foreach($tipos as $t) {
    $tip = translateType($db,$t);
    $total += calcularSubtotalCarrito($db,$t,$tip);
  }
  $total = round($total, 2);
  return $total;
}