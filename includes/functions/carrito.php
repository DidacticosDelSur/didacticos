<?php
const PATH = "./html/";
//VG Febrero 2024  Refactor categorias. Este archivo contiene todas las funciones vinculadas con el carrito

function loadCart($db, int $clientId) 
{//VG: Refactor categorias Febrero 2024

  if (!existe_carrito_de_cliente($db, $clientId)) {
    return;
  }
  $cart = obtener_carrito_de_cliente($db, $clientId);

  $tipos = getTipos($db);

  // if the cart hasn't been updated for 5 days, we delete it
  /* if (getDateDiff($cart['fecha_ultima_actualizacion']) > 5) {
    deleteCart($db, $clientId);
    return;
  } */

  $details = json_decode($cart['detalle']);

  $hasChanged = false;
  foreach ($tipos as $tipo) {
    $updatedCart[$tipo] = [];
  } 

  foreach ($details as $i => $productTypes) {
    $updatedProducts = [];
    foreach ($productTypes as $product) {
        $productInfo = getProductCart($db, $product->id, $product->cantidad);
        $productInfo['observaciones'] = $product->observaciones;
        if ($product->precio !== $productInfo['precio'] || $productInfo['borrado']) {
            $hasChanged = true;
        }
        if (!$productInfo['borrado']) {
            $updatedProducts[] = $productInfo;
        }
    }
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
    }
    $updatedCart[$i] = $updatedProducts;
  }

  $_SESSION['CART'] = $updatedCart;
  $_SESSION['cart_has_changed'] = $hasChanged;
}

function deleteCart($db, int $clientId) {
  $query = "DELETE FROM carritos WHERE cliente_id = " . $clientId;
  mysqli_query($db, $query);
}

function mostrar_carritoCompras($db, $t)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "carritoCompras.html");
    calculateOrderTotal($db);
    pintar_cardCompra($db, $t, PATH, true);
}

function cantidad_productos_carrito($db)
{ //VG: Refactor categorias Febrero 2024
  $grupoTipos = getTipos($db);
  $cant = 0;
  foreach ($grupoTipos as $i => $valor) {
      $cant += count($_SESSION['CART'][$valor]);
  }
  return $cant;
}

function agregar_carrito($db, $t, $id_producto, $cantidad, $observaciones = null)
{//VG: Refactor categorias Febrero 2024
    error_log(date('h:i:s').' Agregando producto al carrito  Sesion: '.json_encode($_SESSION)."\n",3,"./logs/error_".date("Y-m-d ").".log");
    error_log(date('h:i:s').' Agregando producto al carrito [id_producto: '.$id_producto.', cantidad: '.$cantidad.', obs: '.$observaciones.', cliente: '.$_SESSION["id_cliente"]."]\n",3,"./logs/error_".date("Y-m-d ").".log");
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "productoAmpliado.html");
    $producto = getProductCart($db, $id_producto, $cantidad);

    $producto['observaciones'] = $observaciones;

    $tipoProducto = $producto['tipo']; 
    
    $value = array_search($producto['id'], array_column($_SESSION['CART'][$tipoProducto], 'id'), false);
    if ($value !== false) {
        if (isset($observaciones)){
            $_SESSION['CART'][$tipoProducto][$value]['observaciones'] =  $producto['observaciones'];
        }
        $_SESSION['CART'][$tipoProducto][$value]['cantidad'] = $_SESSION['CART'][$tipoProducto][$value]['cantidad'] + $producto['cantidad'];
    } else {
        array_push($_SESSION['CART'][$tipoProducto], $producto);
    }

    $grupoTipos = getTipos($db);

    $enc = false;
    $i = 0;

    while ($i<count($grupoTipos) && !$enc){
        if (count($_SESSION['CART'][$grupoTipos[$i]]) > 0) {
            $tf = new Template(PATH, "remove");
            $tf->set_file("pl", "header.html");
            $tf->set_var("showCartCount", "style=''");
            try {
                guardar_carrito($db);
            } catch (Throwable $e) {
                //TODO: log error
            }
            $enc = true;
        }
        $i++;
    }
    
    $cant = cantidad_productos_carrito($db); 
    $t->set_var("cant_", $cant);
    $t->set_var("cant", $cant);
    /*  header("Location: ".HOST."carritoCompras"); */
}

function eliminar_carrito($db, $t, $id_producto)
{//VG: Refactor categorias Febrero 2024
    error_log(date('h:i:s').' Eliminando producto del carrito [id_producto: '.$id_producto.', cliente: '.$_SESSION["id_cliente"]."]\n",3,"./logs/error_".date("Y-m-d ").".log");
    $t->set_var('base_url', HOST);
    $sql   = "SELECT tipo FROM productos WHERE id = '$id_producto'";
    $query = mysqli_query($db, $sql);
    $row   = mysqli_fetch_array($query);
    
    $tipoProducto = $row['tipo']; 
    $value = find($tipoProducto, $id_producto);
    if ($value !== -1) {
        unset($_SESSION['CART'][$tipoProducto][$value]);
        if (!carrito_vacio($db)) {
            guardar_carrito($db);
        } else {
            deleteCart($db, $_SESSION["id_cliente"]);
        }
    }
    header("Location: " . HOST . "carritoCompras");

}

function find($row, $id)
{
    $result = -1;
    foreach ($_SESSION['CART'][$row] as $i => $valor) {
        if ($_SESSION['CART'][$row][$i] !== null) {
            if ($_SESSION['CART'][$row][$i]['id'] == $id) {
                $result = $i;
            }
        }
    }
    return $result;
}

function observaciones_en_carrito($db,$id_producto,$tipo) {
    $value = array_search($id_producto, array_column($_SESSION['CART'][$tipo], 'id'), false);
    if ($value !== false) {
        return $_SESSION['CART'][$tipo][$value]['observaciones'];
    }
    return '';
}

function cantidad_en_carrito($db,$id_producto,$tipo){
    $value = array_search($id_producto, array_column($_SESSION['CART'][$tipo], 'id'), false);
    if ($value !== false) {
        return $_SESSION['CART'][$tipo][$value]['cantidad'];
    }
    return 0;
}

function modificar_carrito($db, $t, $id_producto, $cantidad) 
{//VG: Refactor categorias Febrero 2024
   
    error_log(date('h:i:s'). ' Modificando producto en el carrito [id_producto: '.$id_producto.', cantidad: '.$cantidad.', cliente: '.$_SESSION["id_cliente"]."]\n",3,"./logs/error_".date("Y-m-d ").".log");
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "carritoCompras.html");
    $producto = getProductCart($db, $id_producto, $cantidad);
    $iva                         = 0.21;
    $total_compra_iva            =   0;
    $total_compra                =   0;
 
    $tipoProducto = $producto['tipo'];
    $value = find($tipoProducto, $id_producto);
    if ($value !== -1) {
        $producto['observaciones'] = $_SESSION['CART'][$tipoProducto][$value]['observaciones'];
        $_SESSION['CART'][$tipoProducto][$value] = $producto;
    }

    $sql = "SELECT id, tipo, categoria, clase_estilo, exento_iva, link FROM tipo_categorias WHERE eliminado = 0";
    $query  = mysqli_query($db, $sql);

    $costo = 0;
    $totales= [];
    
    while ($row = mysqli_fetch_array($query)) {
        $descuento = getDiscount($db,$row['link']);
        $totales[$row['tipo']] = ['subtotal'=>0,'subtotal_descuento'=>0,'iva'=>0,'total'=>0,'descuento'=>$descuento];
        $productos = $_SESSION['CART'][$row['tipo']];
        foreach ($productos as $i => $valor) {
            if ($productos[$i] != null) {
                $descuento_producto = $productos[$i]['descuento'];
                if ($descuento_producto != null && $descuento_producto > 0) {
                    $precio_descuento = $productos[$i]['precio'] - round($productos[$i]['precio'] * $descuento_producto / 100, 2);
                } else {
                    $precio_descuento = $productos[$i]['precio'];
                }
                $precio = $productos[$i]['precio'];
                $totales[$row['tipo']]['subtotal_descuento'] += round(($precio_descuento * $productos[$i]['cantidad']),2);
                $totales[$row['tipo']]['subtotal'] += round(($precio * $productos[$i]['cantidad']),2);
                
                $total_compra += $totales[$row['tipo']]['subtotal'];
                if ($productos[$i]['id'] == $id_producto){
                    $costo =  $precio_descuento * (1-$descuento);
                    if ($row['exento_iva']==0){
                        $costo = $costo * 1.21;
                    } 
                    $costo = round(($costo * $productos[$i]['cantidad']),2);
                }
            }
        }
        $totales[$row['tipo']]['subtotal_descuento'] = $totales[$row['tipo']]['subtotal_descuento']*(1-$descuento);
        if ($row['exento_iva'] == 0) {
            $totales[$row['tipo']]['iva'] = $totales[$row['tipo']]['subtotal_descuento'] * $iva;
            $totales[$row['tipo']]['total_iva']  =  round($totales[$row['tipo']]['subtotal_descuento'] + $totales[$row['tipo']]['iva'], 2);
            $total_compra_iva += $totales[$row['tipo']]['total_iva'];
        }  else {
            $total_compra_iva += round($totales[$row['tipo']]['subtotal_descuento'], 2);
        }
    }

    $total_compra_iva = round($total_compra_iva, 2);
    $total_compra     = round($total_compra, 2);
    foreach ($totales as $i => $valor){
        $totales[$i]["subtotal"] = formatNumber($totales[$i]["subtotal"],2);
        $totales[$i]["subtotal_descuento"] = formatNumber($totales[$i]['subtotal_descuento'],2);
        $totales[$i]["iva"] = formatNumber($totales[$i]['iva'],2);
        $totales[$i]["total"] = formatNumber($totales[$i]['total'],2);//no se usa
        $totales[$i]["descuento"] = formatNumber($totales[$i]['descuento'],2);//no se usa
        $totales[$i]["total_iva"] = formatNumber($totales[$i]['total_iva'],2);//no se usa
    }

    $detalles = array(
        'total_precio_iva'      => formatNumber($total_compra_iva, 2),
        'total_precio'      => formatNumber($total_compra, 2),
        'totales' => $totales,
        'costo' => formatNumber($costo, 2)
    );
    
    guardar_carrito($db);
    echo json_encode($detalles);
    die;
}

function getProductCart($db, $id_producto, $cantidad)
{//VG: Refactor categorias Febrero 2024
    $query = "SELECT p.id,p.sku,p.nombre,p.tipo,p.categoria_id,m.path,p.precio_pvp,p.descuento, ma.nombre AS marca
        FROM productos p
        INNER JOIN marcas ma ON p.marca_id = ma.id
        LEFT JOIN media m ON p.id=m.producto_id WHERE p.id=" . $id_producto;
    $result = mysqli_query($db, $query);
    $row    = mysqli_fetch_array($result);

    return $producto = array(
        'id'        => $id_producto,
        'sku'       => $row["sku"],
        'nombre'    => trim($row["nombre"]), //Elimina tabs y caracteres especiales
        //'nombre'    => utf8_encode($row["nombre"]),//pruebas locales
        'marca'     => $row['marca'],
        'tipo'      => $row["tipo"],
        'categoria' => $row["categoria_id"],
        'imagen'    => $row["path"],
        'precio'    => $row["precio_pvp"],
        'descuento' => $row["descuento"],
        //'descuento' => utf8_encode($row["descuento"]),
        'cantidad'  => $cantidad,
    );
}

function renderizar_lista_productor($t, $templates,$datos)
{//VG: Refactor categorias Febrero 2024
  $tf = new Template($templates, "remove");
  $tf->set_var('base_url', HOST);
  $tf->set_file("pl", "cardCompra.html"); 
  $tf->set_block("pl", "elementos", "_elementos");
  foreach ($datos as $i => $valor) {
      $tf->set_var("id", $valor['id']);
      $tf->set_var("observaciones", $valor['observaciones'] != '' ? 'Observaciones: '.$valor['observaciones']:'');
      $tf->set_var("cant_prod", $valor['cant_prod']);
      $tf->set_var("nombre", '<a href="' . $valor['nombre'] . '</a>');
      $tf->set_var("precio", $valor['precio']);
      $tf->set_var("iva_dida", $valor['iva_dida']);
      $tf->set_var("tachado", $valor['tachado']);
      $tf->set_var("costo", $valor['costo']);
      $tf->set_var("prod", $valor['prod']);
      $tf->parse("_elementos", "elementos", true);
  }
  
  $item = $tf->parse("MAIN", "pl");
  $t->set_var("item_lista", $item); 

}

function pintar_cardCompra($db,$t, $templates, $state)
{//VG: Refactor categorias Febrero 2024
    
    $t->set_var('base_url', HOST);

    $iva                         = 0.21;
    $total_compra                = 0;
    $total_compra_iva            = 0;
    $categorias_tipo = obtener_data_categorias($db);

    $t->set_block("pl", "categorias", "_categorias");
    $t->set_block("pl", "categorias_resumen", "_categorias_resumen");
    
    foreach ($categorias_tipo as $categoria) {
        $t->set_var('titulo',$categoria['categoria']);
        $t->set_var('className',$categoria['clase_estilo']);
        $descuento = getDiscount($db,$categoria['link']);

        if ($descuento == 0) {
            $t->set_var("classDescuento", "style='display:none;'");
        } else {
            $t->set_var("classDescuento", "style=''");
        }
        $totales = ['subtotal'=>0,'subtotal_descuento'=>0,'iva'=>0,'total'=>0,'descuento'=>$descuento]; //ver si se puede mejorar
        $prodInCart = $_SESSION['CART'][$categoria['tipo']];
        $productos = [];
        
        foreach ($prodInCart as $i => $valor) {
            if ($valor != null) {
                $aplica_descuento = false;
                $prod = [];
                $prod['id'] =$valor['id'];
                $prod['observaciones'] = urldecode($valor['observaciones']);
                $prod['cant_prod'] = $valor['cantidad'];
                $prod['nombre'] = getLink($valor) . '">' . $valor['nombre'];
                $descuento_producto = $valor['descuento'];
                $precio_descuento   = 0;
                if ($descuento_producto != null && $descuento_producto > 0) {
                    $precio_descuento = $valor['precio'] - round($valor['precio'] * $descuento_producto / 100, 2);
                    $aplica_descuento = true;
                } else {
                    $precio_descuento = $valor['precio'];
                }
                $precio = round($valor['precio'], 2);

                if ($categoria['tipo'] == 'libro') {
                    $costo =  $precio_descuento * (1 - $totales['descuento']);
                    $precio_descuento = $costo;
                    if ($state) {
                        $prod['precio'] = "PVP: \$ " . formatNumber($precio_descuento, 2);
                        if ($aplica_descuento) {
                            $prod['iva_dida'] = "(\$ " . round($valor['precio'], 2) . ")";
                            $prod['tachado'] = 'style="text-decoration:line-through;"';
                        } else {
                            $prod['iva_dida'] = "";
                            $prod['tachado'] = "";
                        }
                    }
                } else {
                    $precio_descuento = $precio_descuento  * (1 - $totales['descuento']);
                    $iva_prod = round($precio_descuento * $iva, 2);
                    $costo = $precio_descuento + $iva_prod;
                    
                    if ($state) {
                        $prod['tachado'] = '';
                        $iva_prod = round($precio_descuento * $iva, 2);
                        $str      = "(\$ " . formatNumber($iva_prod, 2) . " iva)";
                        $prod['iva_dida'] = $str;
                        $prod['precio'] = "Precio: \$ " . formatNumber($precio_descuento, 2);
                    }
                }

                /*if ($state){
                    if ($categoria['tipo']=='libro'){
                        $prod['precio'] = "PVP: \$ " . formatNumber($precio_descuento, 2);
                        if ($aplica_descuento) {
                            $prod['iva_dida'] = "(\$ " . round($valor['precio'], 2) . ")";
                            $prod['tachado'] = 'style="text-decoration:line-through;"';
                        } else {
                            $prod['iva_dida'] = "";
                            $prod['tachado'] = "";
                        }
                    } else {
                        $precio_descuento = $precio_descuento  * (1 - $totales['descuento']);

                        $prod['tachado'] = '';
                        $iva_prod = round($precio_descuento * $iva, 2);
                        $str      = "(\$ " . formatNumber($iva_prod, 2) . " iva)";
                        $prod['iva_dida'] = $str;
                        $prod['precio'] = "Precio: \$ " . formatNumber($precio_descuento, 2);
                    }
                }  else {
                    $costo = $precio_descuento  * (1 - $totales['descuento']);
                }
                if ($categoria['tipo']=='libro'){ 
                    $costo = $precio_descuento  * (1 - $totales['descuento']);
                    //$precio_descuento = $costo;
                } else {
                    $costo = $precio_descuento + $iva_prod;
                }*/
                
                $prod['costo'] = "\$ " . formatNumber($costo * $valor['cantidad'], 2);
                $prod['prod'] = $categoria['tipo'];
            
                $totales['subtotal'] += $precio * $valor['cantidad'];
                $totales['subtotal_descuento'] += round($precio_descuento * $valor['cantidad'], 2);
                if ($categoria['exento_iva']<>1){
                    $totales['iva'] += round(($precio_descuento * $valor['cantidad']) * $iva, 2);
                    $t->set_var('classNotIva',"style='display:flex;'");
                } else {
                    $t->set_var('classNotIva',"style='display:none;'");
                }

                $totales['total']++;
                $productos[] = $prod;
            }
        }

        if ($state) {
            renderizar_lista_productor($t, $templates, $productos);
        }

        $t->set_var('cantidad',$totales['total']);
        $t->set_var('descuento',$totales['descuento']*100);
        $t->set_var('tipo',$categoria['tipo']);
        $t->set_var('subtotal',formatNumber($totales['subtotal'],2));
        $t->set_var('subtotal_descuento',formatNumber($totales['subtotal_descuento'],2));
        $t->set_var('iva',formatNumber($totales['iva'],2));
        $total_compra += $totales['subtotal_descuento'];
        $total_compra_iva += $totales['subtotal_descuento'] + $totales['iva'];

        if ($state) {
            $t->parse("_categorias", "categorias", true);
        }

        $t->parse("_categorias_resumen", "categorias_resumen", true);

    }

    $t->set_var('total_precio_iva',formatNumber($total_compra_iva,2));
    $minimum = getMinimum();
    $t->set_var('minimum', '<div id="minimum-msg" style="'. ($total_compra < $minimum ? "display: block;":"display: none;") .'">Se necesita una compra mínima total de \$' . formatNumber($minimum, 2) . ' (+IVA) para validar su pedido. <span style="display: none;">En este momento el valor total de su carrito es de <span id="price" data-minimum="' . $minimum . '">\$' . formatNumber($total_compra, 2) . '</span> (+IVA).</span></div>');
};

function guardar_carrito($db)
{//VG: Refactor categorias Febrero 2024
    encodeTitlesForJSON();

    $detalle_pedido  = json_encode($_SESSION["CART"], JSON_UNESCAPED_UNICODE);
    $fecha           = date("Y-m-d H:i:s");
    $cliente_id      = $_SESSION["id_cliente"];
    $vendedor_id     = isSeller()?$_SESSION['id_vendedor']:"NULL";

    if (existe_carrito_de_cliente($db, $cliente_id)) {
        $query           = "UPDATE carritos SET fecha_ultima_actualizacion = '".$fecha."', detalle = '".$detalle_pedido."'
            WHERE cliente_id = " .$cliente_id;
    } else {
        $query           = "INSERT INTO carritos (fecha_creacion, fecha_ultima_actualizacion, cliente_id,detalle, vendedor_id)
            VALUES ('$fecha', '$fecha','$cliente_id', '$detalle_pedido', $vendedor_id)";
    }
    error_log(date('h:i:s').' Guardando carrito en BD [cliente: '.$_SESSION["id_cliente"].', sql: '.$query."]\n",3,"./logs/error_".date("Y-m-d ").".log");
    mysqli_query($db, $query);
}

function existe_carrito_de_cliente($db, int $cliente_id) {
    $sql           = "SELECT 1 FROM carritos WHERE cliente_id = " . $cliente_id;
    $query         = mysqli_query($db, $sql);
    return  (bool)$query->fetch_assoc();
}

function obtener_carrito_de_cliente($db, int $cliente_id) {
    $sql           = "SELECT * FROM carritos WHERE cliente_id = " . $cliente_id;
    $query         = mysqli_query($db, $sql);
    return  $query->fetch_assoc();
}

function encodeTitlesForJSON() { 
    foreach ($_SESSION['CART'] as $key => $value) {
        foreach ($value as $key2 => $product) {
            $_SESSION['CART'][$key][$key2]['nombre'] = addslashes($product['nombre']);
        }
    }
}

function calculateOrderTotal($db) 
{//VG: Refactor categorias Febrero 2024
 
    $total = 0;
    $carrito = $_SESSION['CART'];

    foreach ($carrito as $i => $value) {
        $subtotal_descuento = 0;
        $tipo_trans = translateType($db, $i);
        $descuento = getDiscount($db,$tipo_trans);
        foreach ($value as $id => $valor) {
            if ($valor != null) {
                $descuento_producto = $valor['descuento'];
                if ($descuento_producto != null && $descuento_producto > 0) {
                    $precio_descuento = round($valor['precio'] * (1 - ($descuento_producto/100)), 2);
                } else {
                    $precio_descuento = $valor['precio'];
                }
                $subtotal_descuento += round($precio_descuento * $valor['cantidad'],2);
            }
        }
        $subtotal_descuento = $subtotal_descuento * (1-$descuento);
        if (tipo_exento($db, $i)){
            $total += $subtotal_descuento;
        } else {
            $total += $subtotal_descuento * 1.21;
        }
    }

    return round($total,2);
}

function carrito_vacio($db) 
{//VG: Refactor categorias Febrero 2024
    $tipos = getTipos($db);
    $vacio = true;
    $i = 0;
    while ($i<count($tipos) && $vacio) {
        if (count($_SESSION['CART'][$tipos[$i]]) > 0) {
            $vacio = false;
        }
        $i++;
    }
    return $vacio;
}
