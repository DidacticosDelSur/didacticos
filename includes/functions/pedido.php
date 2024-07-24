<?php
const PATH = "./html/";
//VG Febrero 2024  Refactor categorias. Este archivo contiene todas las funciones vinculadas con el pedido

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
      return $aux_ped;
  } else {
   return $pedido;
  }
}

function enviar_pedido($db, $t)
{ //VG: Refactor categorias Febrero 2024
  if (!carrito_vacio($db)) {

    encodeTitlesForJSON();

    $detalle_pedido  = json_encode($_SESSION["CART"], JSON_UNESCAPED_UNICODE);
    $fecha           = date("Y-m-d H:i:s");
    $cliente_id      = $_SESSION["id_cliente"];
    $direccion       = $_POST["direccion_envio"];
    $provincia       = $_POST["provincia"];
    $ciudad          = $_POST["ciudad"];
    $observaciones   = $_POST["observaciones"];
    $estado          = "Nuevo";
    $vendedor_id     = isSeller()?$_SESSION['id_vendedor']:"NULL";
    $total = calculateOrderTotal($db);

    $query           = "INSERT INTO pedidos (fecha,estado,cliente_id,detalle,direccion_envio,provincia_id,ciudad_id,total, vendedor_id, observaciones)
        VALUES ('$fecha', '$estado','$cliente_id', '$detalle_pedido', '$direccion', '$provincia', '$ciudad', '$total', $vendedor_id, '$observaciones')";
    error_log(date('h:i:s'). ' Guardando pedido en BD [cliente: '.$_SESSION["id_cliente"].', sql: '.$query."]\n",3,"./logs/error_".date("Y-m-d ").".log");
    $result = mysqli_query($db, $query);
    $last_id = mysqli_insert_id($db);

    if ($result) {
      $tipos = getTipos($db);
      foreach ($tipos as $value) {
        $trans = translateType($db,$value);
        $descuento = getDiscount($db,$trans);
        $sql = "UPDATE pedidos SET client_discount_$trans = $descuento WHERE id = $last_id";
        mysqli_query($db, $sql);
      } 
      mostrar_confirmacion_pedido($db, $t, $last_id);
      deleteCart($db, $cliente_id);
    }
    // TODO: this can be false - add proper error handling
  } else {
    header("Location: " . HOST . "carritoCompras");
  }
}

function mostrar_confirmacion_pedido($db, $t,$id_pedido)
{//VG: Refactor categorias Febrero 2024
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "confirmacionPedido.html");
  $sql       = "SELECT p.id, p.direccion_envio, p.fecha,
      pcia.nombre AS nombre_provincia, 
      l.nombre AS nombre_localidad, l.codigo_postal 
      FROM pedidos p 
          JOIN provincias pcia ON p.provincia_id = pcia.id 
          JOIN localidades l ON p.ciudad_id = l.id AND pcia.id = l.provincia_id 
          JOIN clientes c ON p.cliente_id=c.id WHERE p.id =" . $id_pedido;
  $query     = mysqli_query($db, $sql);
  $row       = mysqli_fetch_array($query);

  $usuario   = $_SESSION["nombre_usuario"];
  $provincia = $row['nombre_provincia'];
  $ciudad    = ucwords(strtolower($row['nombre_localidad']));

  $cp        = $row["codigo_postal"];
  $direccion = $row["direccion_envio"];
  $pedido    = str_pad($row['id'], 5, "0", STR_PAD_LEFT);
  $date      = explode(" ", $row["fecha"]);
  $date      = explode("-", $date[0]);
  $date      = $date[2] . "/" . $date[1] . "/" . $date[0];
  $fecha     = $date;
  $email     = $_SESSION["email_cliente"];
  $t->set_var("provincia", $provincia);
  $t->set_var("ciudad", $ciudad);
  $t->set_var("cp", $cp);
  $t->set_var("direccion", $direccion);
  $t->set_var("user", $usuario);
  $t->set_var("email", $email);
  $t->set_var("pedido", $pedido);
  $t->set_var("fecha", $fecha);

  $categ = obtener_data_categorias($db);
  $iva        = 0.21;

  $t->set_block("pl", "categorias", "_categorias");
  
  foreach ($categ as $value) {
    $t->set_var('titulo',$value['categoria']);
    $t->set_var('className',$value['clase_estilo']);
    $descuento = getDiscount($db,$value['link']);

    if ($descuento == 0) {
      $t->set_var("classDescuento", "style='display:none;'");
    } else {
      $t->set_var("classDescuento", "style=''");
    }
    $totales = ['subtotal'=>0,'subtotal_descuento'=>0,'iva'=>0,'total'=>0,'descuento'=>$descuento]; //ver si se puede mejorar
    $prodInCart = $_SESSION['CART'][$value['tipo']];
    $productos = [];
    
    foreach ($prodInCart as $i => $valor) {
      if ($valor != null) {
        $descuento_producto = $valor['descuento'];
        $precio_descuento   = 0;
        if ($descuento_producto != null && $descuento_producto > 0) {
          $precio_descuento = $valor['precio'] - round($valor['precio'] * $descuento_producto / 100, 2);
        } else {
          $precio_descuento = $valor['precio'];
        }
        $precio = round($valor['precio'], 2);
        $precio_descuento = $precio_descuento  * (1 - $totales['descuento']);

        $totales['subtotal'] += $precio * $valor['cantidad'];
        $totales['subtotal_descuento'] += round($precio_descuento * $valor['cantidad'], 2);
        if (tipo_exento($db,$value['tipo'])){
          $t->set_var('classNotIva',"style='display:none;'");
        } else {
          $totales['iva'] += round(($precio_descuento * $valor['cantidad']) * $iva, 2);
          $t->set_var('classNotIva',"style='display:flex;'");
        }

        $totales['total']++;
      }
    }

    $t->set_var('cantidad',$totales['total']);
    $t->set_var('descuento',$totales['descuento']*100);
    $t->set_var('tipo',$value['tipo']);
    $t->set_var('subtotal_tipo',formatNumber($totales['subtotal'],2));
    $t->set_var('subtotal_descuento',formatNumber($totales['subtotal_descuento'],2));
    $t->set_var('iva',formatNumber($totales['iva'],2));
    $total_compra += $totales['subtotal_descuento'];
    $total_compra_iva += $totales['subtotal_descuento'] + $totales['iva'];

    $t->parse("_categorias", "categorias", true);
  }

  $t->set_var("total_precio", formatNumber($total_compra_iva, 2));

  /*mail confirmacion*/

  $email_ = $email;

  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $to        = $email_;
    //$bcc       = getEmailViajante($_SESSION['id_cliente']);
    $subject   = "Confirmación del Pedido #" . $pedido;
    $preferred = $usuario;
    $message   = "Se ha realizado un pedido a Didácticos del Sur. El número de pedido es $pedido, realizado el día $fecha<br>" .
    " El destino del envío será $direccion, $ciudad, $provincia ($cp)<br><br>" .
    " El total estimativo* es de $" . formatNumber($total_compra, 2) . ".-<br>" .
    " * Los precios y el stock pueden variar sin previo aviso y están sujetos a cambios inflacionarios.<br>" .
        '<p style="font-weight: bold; color: red;">Atención: no debe abonar nada hasta que el pedido sea confirmado por un representante. Nos pondremos en contacto con Usted a la brevedad.</p>';

    sendWhatsAppNewOrder($pedido);

    $mail = sendEmail($to, $subject, $preferred, $message, $bcc, 'pedido-confirmado');

    if ($mail) {
      $t->set_var("mensaje", "Se ha enviado un correo de confirmación. Revise su correo no deseado. ¡Muchas gracias!");
    } else {
      $t->set_var("error", "Hubo un error al enviar el correo. Por favor, intente nuevamente.");
    }


  } else {
    $t->set_var("mensaje", "Dirección de email inválida");
  }

  /*fin mail*/

  // vaciar carrito
  foreach ($categ as $categoria){
    $_SESSION['CART'][$categoria['tipo']] = [];
  }

}

function detalle_pedido($db, $t, $id_pedido)
{
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "miCuentaMisPedidosDetalle.html");
    $t->set_var("user", $_SESSION["nombre_usuario"]);

    if (canAccessOrder($id_pedido)) {
        pintar_pedidos_detalle($db, $t, $id_pedido);
    }
    else {
        header("Location: " . HOST . "miCuentaMisPedidos");
    }
}

function renderizar_pedido($t, $templates,$datos)
{//VG: Refactor categorias Febrero 2024
  $tf = new Template($templates, "remove");
  $tf->set_var('base_url', HOST);
  $tf->set_file("pl", "cardPedidosDetalle.html"); 
  $tf->set_block("pl", "elementos", "_elementos");
  foreach ($datos as $i => $valor) {
    $tf->set_var("nombre", $valor['nombre']);
    $tf->set_var("cantidad", $valor['cantidad']);
    $tf->set_var("classPrecio", $valor['classPrecio']);
    $tf->set_var("classDescuentoP", $valor['classDescuentoP']);
    $tf->set_var("classPrecioDida", $valor['classPrecioDida']);
    $tf->set_var("classIva", $valor['classIva']);
    $tf->set_var("suma", $valor['suma']);
    $tf->set_var("descuento_p", $valor['descuento_p']);
    $tf->set_var("precio", $valor['precio']);
    $tf->set_var("precio_dida", $valor['precio_dida']);
    $tf->set_var("iva", $valor['iva']);
    $tf->parse("_elementos", "elementos", true);
  }
  
  $item = $tf->parse("MAIN", "pl");
  $t->set_var("item_lista", $item); 
}

function pintar_pedidos_detalle ($db, $t, $id_pedido)
{ //VG: Refactor categorias Febrero 2024
  $sql   = "SELECT * FROM pedidos WHERE id='$id_pedido'";
  $query = mysqli_query($db, $sql);

  if ($query->num_rows == 0) {
    header("Location: " . HOST . "miCuentaMisPedidos");
  } else {
    while ($row = mysqli_fetch_array($query)) {
      
      $t->set_var("id", $row['id']);
      $t->set_var("fecha", $row['fecha']);
      $t->set_var("estado", $row['estado']);
      //datos cliente
      $queryname  = "SELECT nombre, apellido FROM clientes WHERE id = " . $row['cliente_id'];
      $resultname = mysqli_query($db, $queryname);
      $rowname    = mysqli_fetch_array($resultname);
      $t->set_var("nombre", $rowname['nombre'] . " " . $rowname['apellido']);

      $t->set_var("direccion", $row['direccion_envio']);
      $es_vendedor = $row['vendedor_id'] != null;
      $t->set_var("observaciones", (($es_vendedor)?'<span class="badge badge-info" style="display: inline-block; background: #17a2b8; padding: 5px 10px; color: #fff; border-radius: 6px;">Pedido creado por el vendedor en nombre del cliente</span><br>':'') . $row['observaciones']);

      //datos ciudad
      $querynamecity  = "SELECT nombre FROM localidades WHERE id=" . $row['ciudad_id'];
      $resultnamecity = mysqli_query($db, $querynamecity);
      $rownamecity    = mysqli_fetch_array($resultnamecity);
      $t->set_var("ciudad", $rownamecity['nombre']);

      //datos provincia
      $querynameprov  = "SELECT nombre FROM provincias WHERE id=" . $row['provincia_id'];
      $resultnameprov = mysqli_query($db, $querynameprov);
      $rownameprov    = mysqli_fetch_array($resultnameprov);
      $t->set_var("provincia", $rownameprov['nombre']);

      //datos pedido
      $pedido = $row['detalle'];
      $pedido = preg_replace('/[[:cntrl:]]/', '', $pedido);
      $pedido = json_decode($pedido,true);

      $pedido = formatear_pedido($pedido);
      $t->set_var("total", formatNumber($row['total'], 2));

      $totales = [];
      $t->set_block("pl", "categorias", "_categorias");

      foreach ($pedido as $id => $value) {
        $trans = translateType($db,$id);
        $descuento = $row['client_discount_'.$trans];
        $categoria = getCategoria($db,$id);
        $total = 0;
        $t->set_var('categoria_nombre',$categoria);
 
        if ($descuento != null && $descuento > 0) {
          $discount_porcentaje = $descuento * 100;
          $str                 = "Descuento del " . $discount_porcentaje . "%";
          $t->set_var("descuento_cliente", $str);
        } else {
          $t->set_var("descuento_cliente", '');
        }
        $productos = [];

        foreach ($value as $v) {
          $prod = [];
          $prod['nombre'] = isset($v->nombre) ? $v->nombre: $v['nombre'];
          $cantidad = isset($v->cantidad) ? $v->cantidad: $v['cantidad'];
          $prod['cantidad'] = $cantidad;
          $descuento_p      = isset($v->descuento) ? $v->descuento : $v['descuento']; //intval($array[1][$i]->descuento);
          $precio           = isset($v->precio) ? $v->precio : $v['precio'];
          $precio_descuento = isset($v->precio) ? $v->precio : $v['precio'];
          if ($descuento_p != null && $descuento_p > 0) {
              $precio_descuento = $precio - $precio * $descuento_p / 100;
          }

          if ($id == 'libro') {
            //TODO: Ver si se puede evitar este if. VG Febrero 2024
            $costo = (float) $precio_descuento - (float) $precio_descuento * $descuento;

            $prod['precio'] = formatNumber($precio, 2);
            $prod['descuento_p'] =formatNumber($costo, 2);
            
            $total += round($costo * $cantidad, 2);

            $prod['suma'] = formatNumber($costo * $cantidad, 2);
            $prod['classPrecio'] = "style='display:flex;'";
            $t->set_var("classPrecio", "style='display:flex;'");
            $prod['classDescuentoP'] = "style='display:flex;'";
            $t->set_var("classDescuentoP", "style='display:flex;'");
            $prod['classPrecioDida'] ="style='display:none;'";
            $t->set_var("classPrecioDida", "style='display:none;'");
            $prod['classIva'] = "style='display:none;'";
            $t->set_var("classIva", "style='display:none;'");

          } else {
            $precio = $precio_descuento;
            $precio *= (1 - $descuento);

            $result = 0.21 * ($precio);
            $prod['precio_dida'] = formatNumber($precio, 2);
            $prod['iva'] = formatNumber($result * $cantidad, 2);
            $prod['suma'] = formatNumber(($precio + $result) * $cantidad, 2);
            $total += round(($precio + $result) * $cantidad, 2);

            $prod['classPrecio'] = "style='display:none;'";
            $t->set_var("classPrecio", "style='display:none;'");
            $prod['classDescuentoP'] = "style='display:none;'";
            $t->set_var("classDescuentoP", "style='display:none;'");
            $prod['classPrecioDida'] ="style='display:flex;'";
            $t->set_var("classPrecioDida", "style='display:flex;'");
            $prod['classIva'] = "style='display:flex;'";
            $t->set_var("classIva", "style='display:flex;'");
          } 
          $productos[]=$prod;
        }

        renderizar_pedido($t, PATH, $productos);
        $t->set_var("subtotal", formatNumber($total, 2));
        $t->parse("_categorias", "categorias", true); 
      }
    }
  }
}