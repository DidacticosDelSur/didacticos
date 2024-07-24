<?php 
//VG Marzo 2024 En este archivo copio todas las funciones que fueron refactorizadas y quedaron obsoletas.

function editar_pedido_old($db, $t, $templates, $id_pedido)
{//No esta mas en funcionamiento este código Marzo 2024
  $t->set_var('base_url', HOST);
  if (!isset($_SESSION['PEDIDO_ACTUAL']) || $_SESSION['PEDIDO_ACTUAL'] == "") {
    $_SESSION['PEDIDO_ACTUAL'] = array(['datos']['libros']['juegos']);
  }

  $t->set_file("pl", "datos_pedido.html");
  $t->set_var("user", $_SESSION["admin_name"]);
  $query                              = "SELECT * FROM pedidos WHERE id = " . $id_pedido;
  $result                             = mysqli_query($db, $query);
  $_SESSION['PEDIDO_ACTUAL']['datos'] = mysqli_fetch_array($result);
  $t->set_var("total", number_format(calcularTotal(), 2));

  $pedido = $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'];
  $pedido = preg_replace('/[[:cntrl:]]/', '', $pedido);

  $pedido = json_decode($pedido, true);

  //for ($i = 0; $i < count($pedido); $i++) {
  foreach (array_keys($pedido) as $i) {
    if ($pedido[$i][0] != null) {
      if ($pedido[$i][0]['tipo'] == 'libro') {
        $_SESSION['PEDIDO_ACTUAL']['libros'] = $pedido[$i];
      } else if ($pedido[$i][0]['tipo'] == 'juego') {
        $_SESSION['PEDIDO_ACTUAL']['juegos'] = $pedido[$i];
      } else if ($pedido[$i][0]['tipo'] == 'juguete') {
        $_SESSION['PEDIDO_ACTUAL']['juguetes'] = $pedido[$i];
      } else if ($pedido[$i][0]['tipo'] == 'escolar') {
        $_SESSION['PEDIDO_ACTUAL']['escolares'] = $pedido[$i];
      }
    }
  }

  $cliente = get_datos_cliente($db, $_SESSION['PEDIDO_ACTUAL']['datos']['cliente_id']);
  if ($cliente['vendedor_id'] == $_SESSION["admin_id"]) { // si el usuario logueado es el vendedor
    $t->set_var("clases", 'vendedor');
  }

  pintar_datos_pedido($t, $db);
  pintar_libros_pedido($t, $templates);
  pintar_juegos_pedido($t, $templates);
  pintar_juguetes_pedido($t, $templates);
  pintar_escolares_pedido($t, $templates);
}

function pintar_datos_pedido($t, $db)
{//No esta mas en funcionamiento este código Marzo 2024
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "datos_pedido.html");
    $t->set_var("id_cliente", $_SESSION['PEDIDO_ACTUAL']['datos']['cliente_id']);
    $t->set_var("id", $_SESSION['PEDIDO_ACTUAL']['datos']['id']);
    $t->set_var("fecha", $_SESSION['PEDIDO_ACTUAL']['datos']['fecha']);
    $t->set_var("direccion", $_SESSION['PEDIDO_ACTUAL']['datos']['direccion_envio']);
    $t->set_var("total", number_format(calcularTotal(), 2));
    $t->set_var("total_unformatted", calcularTotal());
    $es_vendedor = $_SESSION['PEDIDO_ACTUAL']['datos']['vendedor_id'];

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

    if (isset($_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_books']) && $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_books'] > 0) {
        $descuento_cliente_libros = $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_books'] * 100;
        $descuento_cliente_libros = '(descuento cliente ' . $descuento_cliente_libros . '%)';
    }
    $t->set_var('client_discount_books', $descuento_cliente_libros);

    if (isset($_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_games']) && $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_games'] > 0) {
        $descuento_cliente_didacticos = $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_games'] * 100;
        $descuento_cliente_didacticos = '(descuento cliente ' . $descuento_cliente_didacticos . '%)';
    }
    $t->set_var('client_discount_games', $descuento_cliente_didacticos);

    if (isset($_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_toys']) && $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_toys'] > 0) {
        $descuento_cliente_juguetes = $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_toys'] * 100;
        $descuento_cliente_juguetes = '(descuento cliente ' . $descuento_cliente_juguetes . '%)';
    }
    $t->set_var('client_discount_toys', $descuento_cliente_juguetes);

    if (isset($_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_schools']) && $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_schools'] > 0) {
        $descuento_cliente_escolares = $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_schools'] * 100;
        $descuento_cliente_escolares = '(descuento cliente ' . $descuento_cliente_escolares . '%)';
    }
    $t->set_var('client_discount_schools', $descuento_cliente_escolares);

    $t->set_var("subtotal_libro", number_format(calcularSubtotalLibros(), 2));
    $t->set_var("subtotal_juegos", number_format(calcularSubtotalJuegos(), 2));
    $t->set_var("subtotal_juguetes", number_format(calcularSubtotalJuguetes(), 2));
    $t->set_var("subtotal_escolares", number_format(calcularSubtotalEscolares(), 2));

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
function calcularSubtotalLibros()
{
    $subtotal_libros   = 0;
    $descuento_cliente = 0;
    if (isset($_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_books'])) {
        $descuento_cliente = (float) $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_books'];
    }
    for ($i = 0; $i < count($_SESSION['PEDIDO_ACTUAL']['libros']); $i++) {
        $precio = $_SESSION['PEDIDO_ACTUAL']['libros'][$i]['precio'];
        if (isset($_SESSION['PEDIDO_ACTUAL']['libros'][$i]['descuento'])) {
            $precio = $precio - $precio * ($_SESSION['PEDIDO_ACTUAL']['libros'][$i]['descuento'] / 100);
        }
        $subtotal_libros += $precio * $_SESSION['PEDIDO_ACTUAL']['libros'][$i]['cantidad'];
    }
    $subtotal_libros = $subtotal_libros - ($subtotal_libros * $descuento_cliente);
    return round($subtotal_libros, 2);
}

function calcularSubtotalJuegos()
{
    $subtotal_juegos = 0;
    $iva             = 1.21;
    for ($i = 0; $i < count($_SESSION['PEDIDO_ACTUAL']['juegos']); $i++) {
        $precio = $_SESSION['PEDIDO_ACTUAL']['juegos'][$i]['precio'];
        if (isset($_SESSION['PEDIDO_ACTUAL']['juegos'][$i]['descuento'])) {
            $precio = $precio - $precio * ($_SESSION['PEDIDO_ACTUAL']['juegos'][$i]['descuento'] / 100);
        }
        $subtotal_juegos += $precio * $_SESSION['PEDIDO_ACTUAL']['juegos'][$i]['cantidad'];
    }
    $subtotal_juegos = $subtotal_juegos * $iva;
    return round($subtotal_juegos, 2);
}

function calcularSubtotalJuguetes()
{
    $subtotal_juguetes = 0;
    $iva               = 1.21;
    for ($i = 0; $i < count($_SESSION['PEDIDO_ACTUAL']['juguetes']); $i++) {
        $precio = $_SESSION['PEDIDO_ACTUAL']['juguetes'][$i]['precio'];
        if (isset($_SESSION['PEDIDO_ACTUAL']['juguetes'][$i]['descuento'])) {
            $precio = $precio - $precio * ($_SESSION['PEDIDO_ACTUAL']['juguetes'][$i]['descuento'] / 100);
        }
        $subtotal_juguetes += $precio * $_SESSION['PEDIDO_ACTUAL']['juguetes'][$i]['cantidad'];
    }
    $subtotal_juguetes = $subtotal_juguetes * $iva;
    return round($subtotal_juguetes, 2);
}

function calcularSubtotalEscolares()
{
    $subtotal_escolares = 0;
    $iva               = 1.21;
    for ($i = 0; $i < count($_SESSION['PEDIDO_ACTUAL']['escolares']); $i++) {
        $precio = $_SESSION['PEDIDO_ACTUAL']['escolares'][$i]['precio'];
        if (isset($_SESSION['PEDIDO_ACTUAL']['escolares'][$i]['descuento'])) {
            $precio = $precio - $precio * ($_SESSION['PEDIDO_ACTUAL']['escolares'][$i]['descuento'] / 100);
        }
        $subtotal_escolares += $precio * $_SESSION['PEDIDO_ACTUAL']['escolares'][$i]['cantidad'];
    }
    $subtotal_escolares = $subtotal_escolares * $iva;
    return round($subtotal_escolares, 2);
}
function calcularTotal()
{
    $total = 0;
    $total = round(calcularSubtotalLibros() + calcularSubtotalJuegos() + calcularSubtotalJuguetes() + calcularSubtotalEscolares(), 2);
    return $total;
}
function eliminar_producto_pedido_old($db, $t, $templates, $id_producto)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "datos_pedido.html");

    $query  = "SELECT * FROM productos where id = " . $id_producto;
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);
    $tipo   = translateType($db,$row["tipo"]); //VER
   // $tipo   = translateType($row["tipo"]);
    for ($i = 0; $i < count($_SESSION['PEDIDO_ACTUAL'][$tipo]); $i++) {
        if ($_SESSION['PEDIDO_ACTUAL'][$tipo][$i] != null) {
            if (($_SESSION['PEDIDO_ACTUAL'][$tipo][$i]['tipo'] == $row["tipo"]) && $_SESSION['PEDIDO_ACTUAL'][$tipo][$i]['id'] == $id_producto) {
                array_splice($_SESSION['PEDIDO_ACTUAL'][$tipo], $i, 1);
            }
        }
    }

    $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'] = preg_replace('/[[:cntrl:]]/', '', $_SESSION['PEDIDO_ACTUAL']['datos']['detalle']);
    $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'] = json_decode($_SESSION['PEDIDO_ACTUAL']['datos']['detalle']);
    if ($row['tipo'] == 'libro') {
        $value = array_search($id_producto, array_column($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][0], 'id'), false);
        array_splice($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][0], $value, 1);
    } elseif ($row['tipo'] == 'juego') {
        $value = array_search($id_producto, array_column($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][1], 'id'), false);
        array_splice($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][1], $value, 1);
    } elseif ($row['tipo'] == 'juguete') {
        $value = array_search($id_producto, array_column($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][2], 'id'), false);
        array_splice($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][2], $value, 1);
    } elseif ($row['tipo'] == 'escolar') {
        $value = array_search($id_producto, array_column($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][3], 'id'), false);
        array_splice($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][3], $value, 1);
    }

    $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'] = json_encode($_SESSION['PEDIDO_ACTUAL']['datos']['detalle']);
    $t->set_var("change", "style='display:inline-block;'");
    $t->set_var("hide", "style='display:none;'");
    pintar_datos_pedido($t, $db);
    pintar_libros_pedido($t, $templates);
    pintar_juegos_pedido($t, $templates);
    pintar_juguetes_pedido($t, $templates);
    pintar_escolares_pedido($t, $templates);
}

function actualizar_pedido_actual_old($db, $t, $templates, $id_producto, $cant_producto)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "datos_pedido.html");
    $producto = getProductPedido($db, $id_producto, $cant_producto);

    $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'] = preg_replace('/[[:cntrl:]]/', '', $_SESSION['PEDIDO_ACTUAL']['datos']['detalle']);
    $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'] = json_decode($_SESSION['PEDIDO_ACTUAL']['datos']['detalle']);
    if ($producto['tipo'] == 'libro') {
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][0], 'id'), false);
        $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][0][$value]->cantidad = $cant_producto;
    } elseif ($producto['tipo'] == 'juego') {
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][1], 'id'), false);
        $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][1][$value]->cantidad = $cant_producto;
    } elseif ($producto['tipo'] == 'juguete') {
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][2], 'id'), false);
        $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][2][$value]->cantidad = $cant_producto;
    } elseif ($producto['tipo'] == 'escolar') {
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][3], 'id'), false);
        $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][3][$value]->cantidad = $cant_producto;
    }

    $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'] = json_encode($_SESSION['PEDIDO_ACTUAL']['datos']['detalle']);

    if ($producto['tipo'] == 'libro') {
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['libros'], 'id'), false);
        $_SESSION['PEDIDO_ACTUAL']['libros'][$value]['cantidad'] = $cant_producto;
    } elseif ($producto['tipo'] == 'juego') {
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['juegos'], 'id'), false);
        $_SESSION['PEDIDO_ACTUAL']['juegos'][$value]['cantidad'] = $cant_producto;
    } elseif ($producto['tipo'] == 'juguete') {
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['juguetes'], 'id'), false);
        $_SESSION['PEDIDO_ACTUAL']['juguetes'][$value]['cantidad'] = $cant_producto;
    } elseif ($producto['tipo'] == 'escolar') {
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['escolares'], 'id'), false);
        $_SESSION['PEDIDO_ACTUAL']['escolares'][$value]['cantidad'] = $cant_producto;
    }

    $t->set_var("change", "style='display:inline-block;'");
    $t->set_var("hide", "style='display:none;'");

    pintar_datos_pedido($t, $db);
    pintar_libros_pedido($t, $templates);
    pintar_juegos_pedido($t, $templates);
    pintar_juguetes_pedido($t, $templates);
    pintar_escolares_pedido($t, $templates);
}


function guardar_producto_pedido_old($t, $db, $templates, $id_producto, $cant_producto)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "datos_pedido.html");
    $producto                                      = getProductPedido($db, $id_producto, $cant_producto);

    $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'] = preg_replace('/[[:cntrl:]]/', '', $_SESSION['PEDIDO_ACTUAL']['datos']['detalle']);
    $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'] = json_decode($_SESSION['PEDIDO_ACTUAL']['datos']['detalle']);

    if ($producto['tipo'] == 'libro') {
        if (!isset($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][0])) {
            $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][0] = [];
        }
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][0], 'id'), false);
        if ($value !== false && $value !== null) {
            $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][0][$value]->cantidad = $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][0][$value]->cantidad + $producto['cantidad'];
        } else if ($value !== null) {
            array_push($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][0], $producto);
        }
    } elseif ($producto['tipo'] == 'juego') {
        if (!isset($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][1])) {
            $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][1] = [];
        }
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][1], 'id'), false);
        if ($value !== false && $value !== null) {
            $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][1][$value]->cantidad = $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][1][$value]->cantidad + $producto['cantidad'];
        } else if ($value !== null) {
            array_push($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][1], $producto);
        }
    } elseif ($producto['tipo'] == 'juguete') {
        if (!isset($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][2])) {
            $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][2] = [];
        }
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][2], 'id'), false);
        if ($value !== false && $value !== null) {
            $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][2][$value]->cantidad = $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][2][$value]->cantidad + $producto['cantidad'];
        } else if ($value !== null) {
            array_push($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][2], $producto);
        }
    } elseif ($producto['tipo'] == 'escolar') {
        if (!isset($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][3])) {
            $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][3] = [];
        }
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][3], 'id'), false);
        if ($value !== false && $value !== null) {
            $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][3][$value]->cantidad = $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][3][$value]->cantidad + $producto['cantidad'];
        } else if ($value !== null) {
            array_push($_SESSION['PEDIDO_ACTUAL']['datos']['detalle'][3], $producto);
        }
    }

    $_SESSION['PEDIDO_ACTUAL']['datos']['detalle'] = json_encode($_SESSION['PEDIDO_ACTUAL']['datos']['detalle']);

    if ($producto['tipo'] == 'libro') {
        if (!isset($_SESSION['PEDIDO_ACTUAL']['libros'])) {
            $_SESSION['PEDIDO_ACTUAL']['libros'] = [];
        }
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['libros'], 'id'), false);
        if ($value !== false && $value !== null) {
            $_SESSION['PEDIDO_ACTUAL']['libros'][$value]['cantidad'] += $producto['cantidad'];
        } else if ($value !== null) {
            array_push($_SESSION['PEDIDO_ACTUAL']['libros'], $producto);
        }
    } elseif ($producto['tipo'] == 'juego') {
        if (!isset($_SESSION['PEDIDO_ACTUAL']['juegos'])) {
            $_SESSION['PEDIDO_ACTUAL']['juegos'] = [];
        }
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['juegos'], 'id'), false);
        if ($value !== false && $value !== null) {
            $_SESSION['PEDIDO_ACTUAL']['juegos'][$value]['cantidad'] += $producto['cantidad'];
        } else if ($value !== null) {
            array_push($_SESSION['PEDIDO_ACTUAL']['juegos'], $producto);
        }
    } elseif ($producto['tipo'] == 'juguete') {
        if (!isset($_SESSION['PEDIDO_ACTUAL']['juguetes'])) {
            $_SESSION['PEDIDO_ACTUAL']['juguetes'] = [];
        }
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['juguetes'], 'id'), false);
        if ($value !== false && $value !== null) {
            $_SESSION['PEDIDO_ACTUAL']['juguetes'][$value]['cantidad'] += $producto['cantidad'];
        } else if ($value !== null) {
            array_push($_SESSION['PEDIDO_ACTUAL']['juguetes'], $producto);
        }
    } elseif ($producto['tipo'] == 'escolar') {
        if (!isset($_SESSION['PEDIDO_ACTUAL']['escolares'])) {
            $_SESSION['PEDIDO_ACTUAL']['escolares'] = [];
        }
        $value = array_search($producto['id'], array_column($_SESSION['PEDIDO_ACTUAL']['escolares'], 'id'), false);
        if ($value !== false && $value !== null) {
            $_SESSION['PEDIDO_ACTUAL']['escolares'][$value]['cantidad'] += $producto['cantidad'];
        } else if ($value !== null) {
            array_push($_SESSION['PEDIDO_ACTUAL']['escolares'], $producto);
        }
    }

    $t->set_var("change", "style='display:inline-block;'");
    $t->set_var("hide", "style='display:none;'");
    pintar_datos_pedido($t, $db);
    pintar_libros_pedido($t, $templates);
    pintar_juegos_pedido($t, $templates);
    pintar_juguetes_pedido($t, $templates);
    pintar_escolares_pedido($t, $templates);
}

function pintar_libros_pedido($t, $templates)
{
    $t->set_var('base_url', HOST);

    $tf = new Template($templates, "remove");
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "datos_libros_pedido.html");
    $t->set_block("pl", "libros", "_libros");
    if (!empty($_SESSION['PEDIDO_ACTUAL']['libros'])) {
        for ($i = 0; $i < count($_SESSION['PEDIDO_ACTUAL']['libros']); $i++) {
            $producto = $_SESSION['PEDIDO_ACTUAL']['libros'][$i];

            if ($producto == null) continue;

            $tf->set_var("sku", $producto['sku']);
            $tf->set_var("id_libro", $producto['id']);
            $tf->set_var("nombre_libro", $producto['nombre']);
            $tf->set_var("precio_libro", number_format($producto['precio'], 2));
            $tf->set_var("cantidad_libro", $producto['cantidad']);
            $tf->set_var("observaciones", ($producto['observaciones'])?urldecode("Observaciones: " . $producto['observaciones']):'');

            $cantidad  = $producto['cantidad'];
            $precio    = $producto['precio'];
            $descuento = $producto['descuento'] + $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_books'] * 100;
            $total     = ($precio - ($precio / 100 * $descuento)) * $cantidad;
            $tf->set_var("descuento_libro", $descuento);
            $tf->set_var("total_precio_libro", number_format($total, 2));

            $t->parse("_libros", "libros", true);
            $item = $tf->parse("MAIN", "pl");
            $t->set_var("producto_libro", $item);
        }
    }
}

function pintar_juegos_pedido($t, $templates)
{
    $tf = new Template($templates, "remove");
    $t->set_var('base_url', HOST);
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "datos_juegos_pedido.html");
    $t->set_block("pl", "juegos_", "_juegos");
    if (!empty($_SESSION['PEDIDO_ACTUAL']['juegos'])) {
        for ($i = 0; $i < count($_SESSION['PEDIDO_ACTUAL']['juegos']); $i++) {
            $producto = $_SESSION['PEDIDO_ACTUAL']['juegos'][$i];

            if ($producto == null) continue;

            $tf->set_var("sku", $producto['sku']);
            $tf->set_var("id_juego", $producto['id']);
            $tf->set_var("nombre_juego", $producto['nombre']);
            $tf->set_var("precio_juego", number_format($producto['precio'], 2));
            $tf->set_var("cantidad_juego", $producto['cantidad']);
            $tf->set_var("observaciones", ($producto['observaciones'])?urldecode("Observaciones: " . $producto['observaciones']):'');

            $cantidad  = $producto['cantidad'];
            $precio    = $producto['precio'];
            $descuento = $producto['descuento']  + $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_games'] * 100;
            $total     = ($precio - ($precio / 100 * $descuento)) * $cantidad;
            $tf->set_var("descuento_juego", $descuento);
            $tf->set_var("total_precio_juego", number_format($total, 2));

            $t->parse("_juegos", "juegos_", true);
            $item = $tf->parse("MAIN", "pl");
            $t->set_var("producto_juego", $item);
        }
    }
}
function pintar_juguetes_pedido($t, $templates)
{
    $tf = new Template($templates, "remove");
    $t->set_var('base_url', HOST);
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "datos_juegos_pedido.html");
    $t->set_block("pl", "juguetes_", "_juguetes");
    if (!empty($_SESSION['PEDIDO_ACTUAL']['juguetes'])) {
        for ($i = 0; $i < count($_SESSION['PEDIDO_ACTUAL']['juguetes']); $i++) {
            $producto = $_SESSION['PEDIDO_ACTUAL']['juguetes'][$i];

            if ($producto == null) continue;

            $tf->set_var("sku", $producto['sku']);
            $tf->set_var("id_juego", $producto['id']);
            $tf->set_var("nombre_juego", $producto['nombre']);
            $tf->set_var("precio_juego", number_format($producto['precio'], 2));
            $tf->set_var("cantidad_juego", $producto['cantidad']);
            $tf->set_var("observaciones", ($producto['observaciones'])?urldecode("Observaciones: " . $producto['observaciones']):'');

            $cantidad  = $producto['cantidad'];
            $precio    = $producto['precio'];
            $descuento = $producto['descuento'] + $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_toys'] * 100;
            $total     = ($precio - ($precio / 100 * $descuento)) * $cantidad;
            $tf->set_var("descuento_juego", $descuento);
            $tf->set_var("total_precio_juego", number_format($total, 2));

            $t->parse("_juguetes", "juguetes_", true);
            $item = $tf->parse("MAIN", "pl");
            $t->set_var("producto_juguete", $item);
        }
    }
}
function pintar_escolares_pedido($t, $templates)
{
    $tf = new Template($templates, "remove");
    $t->set_var('base_url', HOST);
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "datos_juegos_pedido.html");
    $t->set_block("pl", "escolares_", "_escolares");
    if (!empty($_SESSION['PEDIDO_ACTUAL']['escolares'])) {
        for ($i = 0; $i < count($_SESSION['PEDIDO_ACTUAL']['escolares']); $i++) {
            $producto = $_SESSION['PEDIDO_ACTUAL']['escolares'][$i];

            if ($producto == null) continue;

            $tf->set_var("sku", $producto['sku']);
            $tf->set_var("id_juego", $producto['id']);
            $tf->set_var("nombre_juego", $producto['nombre']);
            $tf->set_var("precio_juego", number_format($producto['precio'], 2));
            $tf->set_var("cantidad_juego", $producto['cantidad']);
            $tf->set_var("observaciones", ($producto['observaciones'])?urldecode("Observaciones: " . $producto['observaciones']):'');

            $cantidad  = $producto['cantidad'];
            $precio    = $producto['precio'];
            $descuento = $producto['descuento'] + $_SESSION['PEDIDO_ACTUAL']['datos']['client_discount_schools'] * 100;
            $total     = ($precio - ($precio / 100 * $descuento)) * $cantidad;
            $tf->set_var("descuento_juego", $descuento);
            $tf->set_var("total_precio_juego", number_format($total, 2));

            $t->parse("_escolares", "escolares_", true);
            $item = $tf->parse("MAIN", "pl");
            $t->set_var("producto_escolar", $item);
        }
    }
}
function translateType($type) {
  switch ($type) {
      case 'escolar':
          return 'escolares';
      default:
          return $type . 's';
  }
}