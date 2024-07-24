<?php

function send_whatsapp($message="Test"){
    global $env;
    if ($env == 'production') {
        $phone  = "+5492915765101";  // Enter your phone number here
        $apikey = "955530";       // Enter your personal apikey received in step 3 above
    }
    else {
        $phone  = "5492914991700";  // Enter your phone number here
        $apikey = "4993377";       // Enter your personal apikey received in step 3 above
    }

    $url = 'https://api.callmebot.com/whatsapp.php?source=php&phone='.$phone.'&text='.urlencode($message).'&apikey='.$apikey;

    if($ch = curl_init($url))
    {
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $html = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // echo "Output:".$html;  // you can print the output for troubleshooting
        curl_close($ch);
        return (int) $status;
    }
    else
    {
        return false;
    }
}

function signature() {

    return "Atte., El Server :)";
}

function sendWhatsAppNewOrder($pedido) {

    global $db;

    $query = "SELECT *, concat(c.nombre, ' ', c.apellido) as cliente, concat(v.nombre, ' ', v.apellido) as vendedor  FROM pedidos p
    INNER JOIN clientes c ON c.id = p.cliente_id
    LEFT JOIN vendedores v ON v.id = p.vendedor_id
    WHERE p.id = $pedido LIMIT 1";
    $result = mysqli_query($db, $query);
    $order  = mysqli_fetch_array($result);

    $wapp_msg = "Nuevo pedido #$pedido realizado por " . $order['cliente'] . "\n" .
    (($order['vendedor'])?"El pedido fue realizado por el vendedor " . $order['vendedor'] . "\n":"") .
    "Más info en " . HOST . "admin/editar_pedido/$pedido\n\n" . 
    signature();

    send_whatsapp($wapp_msg);
}

function sendWhatsAppNewUser($id) {
    global $db;

    $query = "SELECT * FROM clientes WHERE id = $id LIMIT 1";

    $result = mysqli_query($db, $query);
    $row  = mysqli_fetch_array($result);

    $wapp_msg = "Nuevo usuario registrado: " . $row['nombre'] . " " . $row['apellido'] . " (" . $row['email'] . ")\n" .
    "Más info en " . HOST . "admin/editar_cliente/$id\n\n" . 
    signature();

    send_whatsapp($wapp_msg);
}

function sendWhatsAppNewProduct($id) {
    global $db, $url_site;

    $query = "SELECT *, p.nombre AS producto, m.nombre AS marca FROM productos p
    INNER JOIN marcas m ON p.marca_id = m.id 
    WHERE p.id = $id LIMIT 1";

    $result = mysqli_query($db, $query);
    $row  = mysqli_fetch_array($result);

    $wapp_msg = "Nuevo producto creado: " . $row['producto'] . " de la marca " . $row['marca'] . "\n" .
    "Página del producto: $url_site/producto/" . $row['sku'] . "\n" .
    "Más info en " . HOST . "/editar_producto/$id\n\n" . 
    signature();

    send_whatsapp($wapp_msg);
}