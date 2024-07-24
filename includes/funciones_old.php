<?php 
//VG Febrero 2024 En este archivo copio todas las funciones que fueron refactorizadas y quedaron obsoletas.

function modificar_carrito_old($db, $t, $id_producto, $cantidad)
{//No esta mas en funcionamiento este código febrero 2024

    $t->set_var('base_url', HOST);
    $t->set_file("pl", "carritoCompras.html");
    $producto = getProductCart($db, $id_producto, $cantidad);

    // TODO: refactor
    switch ($producto['tipo']) {
        case 'libro': $tipoProducto = 0; break;
        case 'juego': $tipoProducto = 1; break;
        case 'juguete': $tipoProducto = 2; break;
        case 'escolar': $tipoProducto = 3; break;
        default:
            throw new Exception('Tipo de producto invalido');
    }
    $value = find($tipoProducto, $id_producto);
    if ($value !== -1) {
        $_SESSION['CART'][$tipoProducto][$value] = $producto;
    }

    $subtotal_libros             = 0;
    $subtotal_libros_descuento   = 0;
    $subtotal_juegos_descuento   = 0;
    $subtotal_juguetes_descuento = 0;
    $subtotal_escolares_descuento= 0;
    $subtotal_juegos             = 0;
    $subtotal_juguetes           = 0;
    $subtotal_escolares          = 0;
    $descuento_libros            = getDiscount($db, 'libros');
    $descuento_didacticos        = getDiscount($db, 'didacticos');
    $descuento_juguetes          = getDiscount($db, 'juguetes');
    $descuento_escolares         = getDiscount($db, 'escolares');
    $iva                         = 0.21;

    // libros
    $booksInCart = $_SESSION['CART'][0];

    foreach ($booksInCart as $i => $valor) {
        if ($booksInCart[$i] != null) {
            $descuento_producto = $booksInCart[$i]['descuento'];
            if ($descuento_producto != null && $descuento_producto > 0) {
                $precio_descuento = round($booksInCart[$i]['precio'] * (1 - ($descuento_producto/100)), 2);
            } else {
                $precio_descuento = $booksInCart[$i]['precio'];
            }
            $precio = $booksInCart[$i]['precio'];
            $subtotal_libros_descuento += $precio_descuento * $booksInCart[$i]['cantidad'];
            $subtotal_libros += $precio * $booksInCart[$i]['cantidad'];
        }

    }

    // didacticos
    $gamesInCart = $_SESSION['CART'][1];

    foreach ($gamesInCart as $i => $valor) {
        if ($gamesInCart[$i] != null) {
            $descuento_producto = $gamesInCart[$i]['descuento'];
            if ($descuento_producto != null && $descuento_producto > 0) {
                $precio_descuento = round($gamesInCart[$i]['precio'] * (1 - ($descuento_producto/100)), 2);
            } else {
                $precio_descuento = $gamesInCart[$i]['precio'];
            }
            $precio = $gamesInCart[$i]['precio'];

            $subtotal_juegos_descuento += round(($precio_descuento * $gamesInCart[$i]['cantidad']), 2);
            $subtotal_juegos += round(($precio * $gamesInCart[$i]['cantidad']), 2);
        }
    }

    // juguetes
    $toysInCart = $_SESSION['CART'][2];

    foreach ($toysInCart as $i => $valor) {
        if ($toysInCart[$i] != null) {
            $descuento_producto = $toysInCart[$i]['descuento'];
            if ($descuento_producto != null && $descuento_producto > 0) {
                $precio_descuento = round($toysInCart[$i]['precio'] * (1 - ($descuento_producto/100)), 2);
            } else {
                $precio_descuento = $toysInCart[$i]['precio'];
            }
            $precio = $toysInCart[$i]['precio'];
            $subtotal_juguetes_descuento += round(($precio_descuento * $toysInCart[$i]['cantidad']), 2);
            $subtotal_juguetes += round(($precio * $toysInCart[$i]['cantidad']), 2);
        }
    }

    // escolares
    $schoolInCart = $_SESSION['CART'][3];

    foreach ($schoolInCart as $i => $valor) {
        if ($schoolInCart[$i] != null) {
            $descuento_producto = $schoolInCart[$i]['descuento'];
            if ($descuento_producto != null && $descuento_producto > 0) {
                $precio_descuento = round($schoolInCart[$i]['precio'] * (1 - ($descuento_producto/100)), 2);
            } else {
                $precio_descuento = $schoolInCart[$i]['precio'];
            }
            $precio = $schoolInCart[$i]['precio'];
            $subtotal_escolares_descuento += round(($precio_descuento * $schoolInCart[$i]['cantidad']), 2);
            $subtotal_escolares += round(($precio * $schoolInCart[$i]['cantidad']), 2);
        }
    }

    $subtotal_libros_descuento = $subtotal_libros_descuento*(1-$descuento_libros);
    $total_libros_descuento = round($subtotal_libros_descuento, 2);

    $subtotal_juegos_descuento = $subtotal_juegos_descuento*(1-$descuento_didacticos);
    $iva_juegos = $subtotal_juegos_descuento * $iva;
    $total_juegos_iva       = round($subtotal_juegos_descuento + $iva_juegos, 2);

    $subtotal_juguetes_descuento = $subtotal_juguetes_descuento*(1-$descuento_juguetes);
    $iva_juguetes = $subtotal_juguetes_descuento * $iva;
    $total_juguetes_iva     = round($subtotal_juguetes_descuento + $iva_juguetes, 2);

    $subtotal_escolares_descuento = $subtotal_escolares_descuento*(1-$descuento_escolares);
    $iva_escolares = $subtotal_escolares_descuento * $iva;
    $total_escolares_iva     = round($subtotal_escolares_descuento + $iva_escolares, 2);

    $total_compra_iva           = round($total_juegos_iva + $total_juguetes_iva + $total_escolares_iva + $total_libros_descuento, 2);
    $total_compra           = round($subtotal_juegos_descuento + $subtotal_juguetes_descuento + $subtotal_escolares_descuento + $total_libros_descuento, 2);

    $detalles = array(
        'total_precio_iva'      => formatNumber($total_compra_iva, 2),
        'total_precio'      => formatNumber($total_compra, 2),
        'subtotal_libros'   => formatNumber($subtotal_libros, 2),
        'subtotal_juegos'   => formatNumber($subtotal_juegos, 2),
        'subtotal_juguetes' => formatNumber($subtotal_juguetes, 2),
        'subtotal_escolares'=> formatNumber($subtotal_escolares, 2),
        'iva_juegos'        => formatNumber($iva_juegos, 2),
        'iva_juguetes'      => formatNumber($iva_juguetes, 2),
        'iva_escolares'     => formatNumber($iva_escolares, 2),
        'descuento_precio_libros'  => formatNumber($total_libros_descuento, 2),
        'descuento_precio_juegos'  => formatNumber($subtotal_juegos_descuento, 2),
        'descuento_precio_juguetes'  => formatNumber($subtotal_juguetes_descuento, 2),
        'descuento_precio_escolares' => formatNumber($subtotal_escolares_descuento, 2),
    );
    guardar_carrito($db);
    echo json_encode($detalles);
    die;
}
function pintar_cardCompra_old($db, $t, $templates, $state)
{//No esta mas en funcionamiento este código febrero 2024

    $t->set_var('base_url', HOST);

    $subtotal_libros             = 0;
    $subtotal_juegos             = 0;
    $subtotal_juguetes           = 0;
    $subtotal_escolares          = 0;
    $subtotal_libros_descuento   = 0;
    $subtotal_juegos_descuento   = 0;
    $subtotal_juguetes_descuento = 0;
    $subtotal_escolares_descuento= 0;
    $iva_juegos                  = 0;
    $iva_juguetes                = 0;
    $iva_escolares               = 0;
    $total_libros                = 0;
    $total_juegos                = 0;
    $total_juguetes              = 0;
    $total_escolares             = 0;
    $iva                         = 0.21;
    $descuento_libros            = getDiscount($db, 'libros');
    $descuento_didacticos        = getDiscount($db, 'didacticos');
    $descuento_juguetes          = getDiscount($db, 'juguetes');
    $descuento_escolares         = getDiscount($db, 'escolares');

    $t->set_var("descuento_libros", $descuento_libros * 100);
    if ($descuento_libros == 0) {
        $t->set_var("classDescuentoLibro", "style='display:none;'");
    }

    $t->set_var("descuento_didacticos", $descuento_didacticos * 100);
    if ($descuento_didacticos == 0) {
        $t->set_var("classDescuentoDidacticos", "style='display:none;'");
    }

    $t->set_var("descuento_juguetes", $descuento_juguetes * 100);
    if ($descuento_juguetes == 0) {
        $t->set_var("classDescuentoJuguetes", "style='display:none;'");
    }

    $t->set_var("descuento_escolares", $descuento_escolares * 100);
    if ($descuento_escolares == 0) {
        $t->set_var("classDescuentoEscolares", "style='display:none;'");
    }

    if ($state) {
        $tf = new Template($templates, "remove");
        $tf->set_var('base_url', HOST);
        $tf->set_file("pl", "cardCompra.html");
        if (isset($_SESSION['CART']) and $_SESSION['CART'] != "") {

            // libros
            $t->set_block("pl", "libros", "_libros");
           // $booksInCart = $_SESSION['CART'][0];
            $booksInCart = $_SESSION['CART']['libro'];

            foreach ($booksInCart as $i => $valor) {
                if ($booksInCart[$i] != null) {
                    $tf->set_var("id", $booksInCart[$i]['id']);
                    $tf->set_var("observaciones", urldecode($booksInCart[$i]['observaciones']));
                    $tf->set_var("cant_prod", $booksInCart[$i]['cantidad']);
                    $tf->set_var("nombre", '<a href="' . getLink($booksInCart[$i]) . '">' . $booksInCart[$i]['nombre'] . '</a>');
                    $descuento_producto = $booksInCart[$i]['descuento'];
                    $precio_descuento   = 0;
                    if ($descuento_producto != null && $descuento_producto > 0) {
                        $precio_descuento = $booksInCart[$i]['precio'] - round($booksInCart[$i]['precio'] * $descuento_producto / 100, 2);
                    } else {
                        $precio_descuento = $booksInCart[$i]['precio'];
                    }
                    $precio = round($booksInCart[$i]['precio'], 2);

                    if ($precio == $precio_descuento) {
                        $tf->set_var("precio", "PVP: \$ " . formatNumber($precio, 2));
                        $tf->set_var("iva_dida", '');
                        $tf->set_var("tachado", '');
                    } else {
                        $tf->set_var("precio", "PVP: \$ " . formatNumber($precio_descuento, 2));
                        $tf->set_var("tachado", 'style="text-decoration:line-through;"');
                        $des_prod = round($precio, 2);
                        $str      = "(\$ " . $des_prod . ")";
                        $tf->set_var("iva_dida", $str);
                    }
                    $costo = $precio_descuento  * (1 - $descuento_libros);
                    $precio_descuento = $costo;
                    $tf->set_var("costo", "\$ " . formatNumber($costo, 2));

                    $tf->set_var("prod", "libro");

                    $subtotal_libros += $precio * $booksInCart[$i]['cantidad'];
                    $subtotal_libros_descuento += $precio_descuento * $booksInCart[$i]['cantidad'];
                    $total_libros++;

                    $t->parse("_libros", "libros", true);
                    $item = $tf->parse("MAIN", "pl");
                    $t->set_var("item_libro", $item);
                }
            }

            // didacticos
            $t->set_block("pl", "juegos", "_juegos");
            //$gamesInCart = $_SESSION['CART'][1];//didactivo
            $gamesInCart = $_SESSION['CART']['juego'];

            foreach ($gamesInCart as $i => $valor) {
                if ($gamesInCart[$i] != null) {
                    $descuento_producto = $gamesInCart[$i]['descuento'];
                    $precio_descuento   = 0;
                    if ($descuento_producto != null && $descuento_producto > 0) {
                        $precio_descuento = $gamesInCart[$i]['precio'] - round($gamesInCart[$i]['precio'] * $descuento_producto / 100, 2);
                    } else {
                        $precio_descuento = $gamesInCart[$i]['precio'];
                    }

                    $precio_descuento *= (1 - $descuento_didacticos);

                    $precio = $gamesInCart[$i]['precio'];

                    $tf->set_var("tachado", '');
                    $iva_prod = round($precio_descuento * $iva, 2);
                    $str      = "(\$ " . formatNumber($iva_prod, 2) . " iva)";
                    $tf->set_var("iva_dida", $str);

                    $costo = $precio_descuento + $iva_prod;
                    $tf->set_var("costo", "\$ " . formatNumber($costo, 2));
                    $tf->set_var("id", $gamesInCart[$i]['id']);
                    $tf->set_var("observaciones", urldecode($gamesInCart[$i]['observaciones']));
                    $tf->set_var("cant_prod", $gamesInCart[$i]['cantidad']);
                    $tf->set_var("nombre", '<a href="' . getLink($gamesInCart[$i]) . '">' . $gamesInCart[$i]['nombre'] . '</a>');
                    $tf->set_var("precio", "Precio: \$ " . formatNumber($precio_descuento, 2));
                    $tf->set_var("prod", "juego");
                    $subtotal_juegos_descuento += round(($precio_descuento * $gamesInCart[$i]['cantidad']), 2);
                    $subtotal_juegos += round(($precio * $gamesInCart[$i]['cantidad']), 2);
                    $iva_juegos += round(($precio_descuento * $gamesInCart[$i]['cantidad']) * $iva, 2);
                    $total_juegos++;
                    $t->parse("_juegos", "juegos", true);
                    $item = $tf->parse("MAIN", "pl");
                    $t->set_var("item_juego", $item);
                }
            }

            // juguetes
            $t->set_block("pl", "juguetes", "_juguetes");
           // $toysInCart = $_SESSION['CART'][2];
            $toysInCart = $_SESSION['CART']['juguete'];

            foreach ($toysInCart as $i => $valor) {
                if ($toysInCart[$i] != null) {
                    $descuento_producto = $toysInCart[$i]['descuento'];
                    $precio_descuento   = 0;
                    if ($descuento_producto != null && $descuento_producto > 0) {
                        $precio_descuento = $toysInCart[$i]['precio'] - round($toysInCart[$i]['precio'] * $descuento_producto / 100, 2);
                    } else {
                        $precio_descuento = $toysInCart[$i]['precio'];
                    }

                    $precio_descuento *= (1 - $descuento_juguetes);

                    $precio = $toysInCart[$i]['precio'];

                    $tf->set_var("tachado", '');
                    $iva_prod = round($precio_descuento * $iva, 2);
                    $str      = "(\$ " . formatNumber($iva_prod, 2) . " iva)";
                    $tf->set_var("iva_dida", $str);


                    $costo = $precio_descuento + $iva_prod;
                    $tf->set_var("costo", "\$ " . formatNumber($costo, 2));

                    $tf->set_var("id", $toysInCart[$i]['id']);
                    $tf->set_var("observaciones", urldecode($toysInCart[$i]['observaciones']));
                    $tf->set_var("cant_prod", $toysInCart[$i]['cantidad']);
                    $tf->set_var("nombre", '<a href="' . getLink($toysInCart[$i]) . '">' . $toysInCart[$i]['nombre'] . '</a>');
                    $tf->set_var("precio", "Precio: \$ " . formatNumber($precio_descuento, 2));
                    $tf->set_var("prod", "juguete");
                    $subtotal_juguetes_descuento += round(($precio_descuento * $toysInCart[$i]['cantidad']), 2);
                    $subtotal_juguetes += round(($precio * $toysInCart[$i]['cantidad']), 2);
                    $iva_juguetes += round(($precio_descuento * $toysInCart[$i]['cantidad']) * $iva, 2);
                    $total_juguetes++;
                    $t->parse("_juguetes", "juguetes", true);
                    $item = $tf->parse("MAIN", "pl");
                    $t->set_var("item_juguete", $item);
                }
            }

           // escolares
            $t->set_block("pl", "escolares", "_escolares");
            //$schoolInCart = $_SESSION['CART'][3];
            $schoolInCart = $_SESSION['CART']['escolar'];

            foreach ($schoolInCart as $i => $valor) {
                if ($schoolInCart[$i] != null) {
                    $descuento_producto = $schoolInCart[$i]['descuento'];
                    $precio_descuento   = 0;
                    if ($descuento_producto != null && $descuento_producto > 0) {
                        $precio_descuento = $schoolInCart[$i]['precio'] - round($schoolInCart[$i]['precio'] * $descuento_producto / 100, 2);
                    } else {
                        $precio_descuento = $schoolInCart[$i]['precio'];
                    }

                    $precio_descuento *= (1 - $descuento_escolares);

                    $precio = $schoolInCart[$i]['precio'];

                    $tf->set_var("tachado", '');
                    $iva_prod = round($precio_descuento * $iva, 2);
                    $str      = "(\$ " . formatNumber($iva_prod, 2) . " iva)";
                    $tf->set_var("iva_dida", $str);


                    $costo = $precio_descuento + $iva_prod;
                    $tf->set_var("costo", "\$ " . formatNumber($costo, 2));

                    $tf->set_var("id", $schoolInCart[$i]['id']);
                    $tf->set_var("observaciones", urldecode($schoolInCart[$i]['observaciones']));
                    $tf->set_var("cant_prod", $schoolInCart[$i]['cantidad']);
                    $tf->set_var("nombre", '<a href="' . getLink($schoolInCart[$i]) . '">' . $schoolInCart[$i]['nombre'] . '</a>');
                    $tf->set_var("precio", "Precio: \$ " . formatNumber($precio_descuento, 2));
                    $tf->set_var("prod", "escolar");
                    $subtotal_escolares_descuento += round(($precio_descuento * $schoolInCart[$i]['cantidad']), 2);
                    $subtotal_escolares += round(($precio * $schoolInCart[$i]['cantidad']), 2);
                    $iva_escolares += round(($precio_descuento * $schoolInCart[$i]['cantidad']) * $iva, 2);
                    $total_escolares++;
                    $t->parse("_escolares", "escolares", true);
                    $item = $tf->parse("MAIN", "pl");
                    $t->set_var("item_escolar", $item);
                }
            }

            $t->set_var("total", $total_juegos + $total_juguetes + $total_libros + $total_escolares);
        } else {
            $t->set_var("total", '0');
        }
    
    } else {
        if (isset($_SESSION['CART']) and $_SESSION['CART'] != "") {

            // libros
           // $booksInCart = $_SESSION['CART'][0];
            $booksInCart = $_SESSION['CART']['libro'];

            foreach ($booksInCart as $i => $valor) {
                if ($booksInCart[$i] != null) {
                    $descuento_producto = $booksInCart[$i]['descuento'];
                    if ($descuento_producto != null && $descuento_producto > 0) {
                        $precio_descuento = $booksInCart[$i]['precio'] - round($booksInCart[$i]['precio'] * $descuento_producto / 100, 2);
                    } else {
                        $precio_descuento = $booksInCart[$i]['precio'];
                    }

                    $precio_descuento *= (1 - $descuento_libros);

                    $precio = round($booksInCart[$i]['precio'], 2);

                    $subtotal_libros_descuento += $precio_descuento * $booksInCart[$i]['cantidad'];
                    $subtotal_libros += $precio * $booksInCart[$i]['cantidad'];
                    $total_libros++;
                }
            }

            // juegos
           // $gamesInCart = $_SESSION['CART'][1];
            $gamesInCart = $_SESSION['CART']['juego'];

            foreach ($gamesInCart as $i => $valor) {
                if ($gamesInCart[$i] != null) {
                    $descuento_producto = $gamesInCart[$i]['descuento'];
                    if ($descuento_producto != null && $descuento_producto > 0) {
                        $precio_descuento = $gamesInCart[$i]['precio'] - round($gamesInCart[$i]['precio'] * $descuento_producto / 100, 2);
                    } else {
                        $precio_descuento = $gamesInCart[$i]['precio'];
                    }

                    $precio_descuento *= (1 - $descuento_didacticos);

                    $precio = round($gamesInCart[$i]['precio'], 2);

                    $subtotal_juegos_descuento += round(($precio_descuento * $gamesInCart[$i]['cantidad']), 2);
                    $subtotal_juegos += round(($precio * $gamesInCart[$i]['cantidad']), 2);
                    $iva_juegos += round(($precio_descuento * $gamesInCart[$i]['cantidad']) * $iva, 2);
                    $total_juegos++;
                }
            }

            // juguetes
           // $toysInCart = $_SESSION['CART'][2];
            $toysInCart = $_SESSION['CART']['juguete'];

            foreach ($toysInCart as $i => $valor) {
                if ($toysInCart[$i] != null) {
                    $descuento_producto = $toysInCart[$i]['descuento'];
                    if ($descuento_producto != null && $descuento_producto > 0) {
                        $precio_descuento = $toysInCart[$i]['precio'] - round($toysInCart[$i]['precio'] * $descuento_producto / 100, 2);
                    } else {
                        $precio_descuento = $toysInCart[$i]['precio'];
                    }

                    $precio_descuento *= (1 - $descuento_juguetes);

                    $precio = round($toysInCart[$i]['precio'], 2);

                    $subtotal_juguetes_descuento += round(($precio_descuento * $toysInCart[$i]['cantidad']), 2);
                    $subtotal_juguetes += round(($precio * $toysInCart[$i]['cantidad']), 2);
                    $iva_juguetes += round(($precio_descuento * $toysInCart[$i]['cantidad']) * $iva, 2);
                    $total_juguetes++;
                }
            }

            // escolares
            //schoolInCart = $_SESSION['CART'][3];
            $schoolInCart = $_SESSION['CART']['escolar'];

            foreach ($schoolInCart as $i => $valor) {
                if ($schoolInCart[$i] != null) {
                    $descuento_producto = $schoolInCart[$i]['descuento'];
                    if ($descuento_producto != null && $descuento_producto > 0) {
                        $precio_descuento = $schoolInCart[$i]['precio'] - round($schoolInCart[$i]['precio'] * $descuento_producto / 100, 2);
                    } else {
                        $precio_descuento = $schoolInCart[$i]['precio'];
                    }

                    $precio_descuento *= (1 - $descuento_escolares);

                    $precio = round($schoolInCart[$i]['precio'], 2);

                    $subtotal_escolares_descuento += round(($precio_descuento * $schoolInCart[$i]['cantidad']), 2);
                    $subtotal_escolares += round(($precio * $schoolInCart[$i]['cantidad']), 2);
                    $iva_escolares += round(($precio_descuento * $schoolInCart[$i]['cantidad']) * $iva, 2);
                    $total_escolares++;
                }
            }

        } else {
            $t->set_var("total", '0');
        }
    }
    $total_libros_descuento = round($subtotal_libros_descuento, 2);
    $total_juegos_iva       = round($subtotal_juegos_descuento + $iva_juegos, 2);
    $total_juguetes_iva     = round($subtotal_juguetes_descuento + $iva_juguetes, 2);
    $total_escolares_iva     = round($subtotal_escolares_descuento + $iva_escolares, 2);

    $t->set_var("descuento_precio", formatNumber($total_libros_descuento, 2));

    $t->set_var("descuento_precio_libros", formatNumber($subtotal_libros_descuento, 2));
    $t->set_var("descuento_precio_juegos", formatNumber($subtotal_juegos_descuento, 2));
    $t->set_var("descuento_precio_juguetes", formatNumber($subtotal_juguetes_descuento, 2));
    $t->set_var("descuento_precio_escolares", formatNumber($subtotal_escolares_descuento, 2));

    $total_compra = ($subtotal_juegos_descuento + $total_libros_descuento + $subtotal_juguetes_descuento + $subtotal_escolares_descuento);

    $t->set_var("total_precio_iva", formatNumber($subtotal_juegos_descuento + $iva_juegos + $subtotal_juguetes_descuento + $iva_juguetes + $subtotal_escolares_descuento + $iva_escolares + $subtotal_libros_descuento, 2));
    $t->set_var("total_precio", formatNumber($total_compra, 2));
    $t->set_var("total_precio_unformatted", $total_compra);

    $t->set_var("cant_libros", $total_libros);
    $t->set_var("subtotal_libros", formatNumber($subtotal_libros, 2));

    $t->set_var("cant_juegos", $total_juegos);
    $t->set_var("subtotal_juegos", formatNumber($subtotal_juegos, 2));
    $t->set_var("iva", formatNumber($iva_juegos, 2));

    $t->set_var("cant_juguetes", $total_juguetes);
    $t->set_var("subtotal_juguetes", formatNumber($subtotal_juguetes, 2));
    $t->set_var("iva_juguetes", formatNumber($iva_juguetes, 2));

    $t->set_var("cant_escolares", $total_escolares);
    $t->set_var("subtotal_escolares", formatNumber($subtotal_escolares, 2));
    $t->set_var("iva_escolares", formatNumber($iva_escolares, 2));

    $minimum = getMinimum();
    $t->set_var('minimum', '<div id="minimum-msg" style="'. ($total_compra < $minimum ? "display: block;":"display: none;") .'">Se necesita una compra mínima total de \$' . formatNumber($minimum, 2) . ' (+IVA) para validar su pedido. En este momento el valor total de su carrito es de <span id="price" data-minimum="' . $minimum . '">\$' . formatNumber($total_compra, 2) . '</span> (+IVA).</div>');
}
function mostrar_confirmacion_pedido_old($db, $t)
{//No esta mas en funcionamiento este código febrero 2024

    $t->set_var('base_url', HOST);
    $t->set_file("pl", "confirmacionPedido.html");
    $id_pedido = mysqli_insert_id($db);
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
    $descuento_libros = getDiscount($db, 'libros');
    $descuento_didacticos = getDiscount($db, 'didacticos');
    $descuento_juguetes   = getDiscount($db, 'juguetes');
    $descuento_escolares   = getDiscount($db, 'escolares');

    $t->set_var("descuento_libros", $descuento_libros * 100);
    if ($descuento_libros == 0) {
        $t->set_var("classDescuentoLibro", "style='display:none;'");
    }

    $t->set_var("descuento_didacticos", $descuento_didacticos * 100);
    if ($descuento_didacticos == 0) {
        $t->set_var("classDescuentoDidacticos", "style='display:none;'");
    }

    $t->set_var("descuento_juguetes", $descuento_juguetes * 100);
    if ($descuento_juguetes == 0) {
        $t->set_var("classDescuentoJuguetes", "style='display:none;'");
    }

    $t->set_var("descuento_escolares", $descuento_escolares * 100);
    if ($descuento_escolares == 0) {
        $t->set_var("classDescuentoEscolares", "style='display:none;'");
    }

    $iva        = 1.21;
    if (isset($_SESSION['CART']) and $_SESSION['CART'] != "") {

        $subtotal_libros             = 0;
        $subtotal_libros_descuento   = 0;
        $subtotal_juegos             = 0;
        $subtotal_juegos_descuento   = 0;
        $subtotal_juguetes           = 0;
        $subtotal_juguetes_descuento = 0;
        $subtotal_escolares          = 0;
        $subtotal_escolares_descuento= 0;
        $total_libros                = 0;
        $total_juegos                = 0;
        $total_juguetes              = 0;
        $total_escolares             = 0;
        $iva_juegos                  = 0;
        $iva_juguetes                = 0;
        $iva_escolares               = 0;

        // libros
        //$booksInCart = $_SESSION['CART'][0];
        $booksInCart = $_SESSION['CART']['libro'];

        foreach ($booksInCart as $i => $valor) {
            if ($booksInCart[$i] != null) {
                $descuento_producto = $booksInCart[$i]['descuento'];
                if ($descuento_producto != null && $descuento_producto > 0) {
                    $precio_descuento = $booksInCart[$i]['precio'] - round($booksInCart[$i]['precio'] * $descuento_producto / 100, 2);
                } else {
                    $precio_descuento = $booksInCart[$i]['precio'];
                }

                $precio = round($booksInCart[$i]['precio'], 2);

                $subtotal_libros_descuento += $precio_descuento * $booksInCart[$i]['cantidad'];
                $subtotal_libros += $precio * $booksInCart[$i]['cantidad'];
                $total_libros++;
            }
        }

        // didacticos
       // $gamesInCart = $_SESSION['CART'][1];
        $gamesInCart = $_SESSION['CART']['juego'];

        foreach ($gamesInCart as $i => $valor) {
            if ($gamesInCart[$i] != null) {
                $descuento_producto = $gamesInCart[$i]['descuento'] + $descuento_didacticos*100;
                if ($descuento_producto != null && $descuento_producto > 0) {
                    $precio_descuento = $gamesInCart[$i]['precio'] - round($gamesInCart[$i]['precio'] * $descuento_producto / 100, 2);
                } else {
                    $precio_descuento = $gamesInCart[$i]['precio'];
                }

                $precio = round($gamesInCart[$i]['precio'], 2);

                $subtotal_juegos_descuento += round(($precio_descuento * $gamesInCart[$i]['cantidad']), 2);
                $subtotal_juegos += round(($precio * $gamesInCart[$i]['cantidad']) * $iva, 2);
                $iva_juegos += round(($precio_descuento * $gamesInCart[$i]['cantidad']) * $iva - ($precio_descuento * $gamesInCart[$i]['cantidad']), 2);
                $total_juegos++;
            }
        }

        // juguetes
        //$toysInCart = $_SESSION['CART'][2];
        $toysInCart = $_SESSION['CART']['juguete'];

        foreach ($toysInCart as $i => $valor) {
            if ($toysInCart[$i] != null) {
                $descuento_producto = $toysInCart[$i]['descuento'] + $descuento_juguetes*100;
                if ($descuento_producto != null && $descuento_producto > 0) {
                    $precio_descuento = $toysInCart[$i]['precio'] - round($toysInCart[$i]['precio'] * $descuento_producto / 100, 2);
                } else {
                    $precio_descuento = $toysInCart[$i]['precio'];
                }

                $precio = round($toysInCart[$i]['precio'], 2);

                $subtotal_juguetes_descuento += round(($precio_descuento * $toysInCart[$i]['cantidad']), 2);
                $subtotal_juguetes += round(($precio * $toysInCart[$i]['cantidad']) * $iva, 2);
                $iva_juguetes += round(($precio_descuento * $toysInCart[$i]['cantidad']) * $iva - ($precio_descuento * $toysInCart[$i]['cantidad']), 2);
                $total_juguetes++;
            }
        }

        // escolares
       // $schoolInCart = $_SESSION['CART'][3];
        $schoolInCart = $_SESSION['CART']['escolar'];

        foreach ($schoolInCart as $i => $valor) {
            if ($schoolInCart[$i] != null) {
                $descuento_producto = $schoolInCart[$i]['descuento'] + $descuento_escolares*100;
                if ($descuento_producto != null && $descuento_producto > 0) {
                    $precio_descuento = $schoolInCart[$i]['precio'] - round($schoolInCart[$i]['precio'] * $descuento_producto / 100, 2);
                } else {
                    $precio_descuento = $schoolInCart[$i]['precio'];
                }

                $precio = round($schoolInCart[$i]['precio'], 2);

                $subtotal_escolares_descuento += round(($precio_descuento * $schoolInCart[$i]['cantidad']), 2);
                $subtotal_escolares += round(($precio * $schoolInCart[$i]['cantidad']) * $iva, 2);
                $iva_escolares += round(($precio_descuento * $schoolInCart[$i]['cantidad']) * $iva - ($precio_descuento * $schoolInCart[$i]['cantidad']), 2);
                $total_escolares++;
            }
        }

        $total_libros_descuento = round($subtotal_libros_descuento - ($subtotal_libros_descuento * $descuento_libros), 2);
        $total_juegos_iva       = round($subtotal_juegos_descuento + $iva_juegos, 2);
        $total_juguetes_iva     = round($subtotal_juguetes_descuento + $iva_juguetes, 2);
        $total_escolares_iva    = round($subtotal_escolares_descuento + $iva_escolares, 2);

        $t->set_var("descuento_precio", formatNumber($total_libros_descuento, 2));

        $t->set_var("iva", formatNumber($iva_juegos, 2));
        $t->set_var("iva_juguetes", formatNumber($iva_juguetes, 2));
        $t->set_var("iva_escolares", formatNumber($iva_escolares, 2));

        $t->set_var("subtotal_libros", formatNumber($subtotal_libros, 2));
        $t->set_var("subtotal_juegos", formatNumber($subtotal_juegos_descuento, 2));
        $t->set_var("subtotal_juguetes", formatNumber($subtotal_juguetes_descuento, 2));
        $t->set_var("subtotal_escolares", formatNumber($subtotal_escolares_descuento, 2));

        $t->set_var("cant_juegos", $total_juegos);
        $t->set_var("cant_juguetes", $total_juguetes);
        $t->set_var("cant_escolares", $total_escolares);
        $t->set_var("cant_libros", $total_libros);

        $total_compra = ($total_juegos_iva + $total_libros_descuento + $total_juguetes_iva + $total_escolares_iva);
        $t->set_var("total_precio", formatNumber($total_compra, 2));

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

           /*  sendWhatsAppNewOrder($pedido);

            $mail = sendEmail($to, $subject, $preferred, $message, $bcc, 'pedido-confirmado');

            if ($mail) {
                $t->set_var("mensaje", "Se ha enviado un correo de confirmación. Revise su correo no deseado. ¡Muchas gracias!");
            } else {
                $t->set_var("error", "Hubo un error al enviar el correo. Por favor, intente nuevamente.");
            } */


        } else {
            $t->set_var("mensaje", "Dirección de email inválida");
        }

        /*fin mail*/

        // vaciar carrito
        /* $_SESSION['CART'][0] = array();
        $_SESSION['CART'][1] = array();
        $_SESSION['CART'][2] = array();
        $_SESSION['CART'][3] = array(); */

        $_SESSION['CART']['libro'] = array();
        $_SESSION['CART']['juego'] = array();
        $_SESSION['CART']['juguete'] = array();
        $_SESSION['CART']['escolar'] = array();
    }
}
function enviar_pedido_old($db, $t)
{ //No esta mas en funcionamiento este código febrero 2024

    if (count($_SESSION['CART']['libro']) > 0 || count($_SESSION['CART']['juego']) > 0 || count($_SESSION['CART']['juguete']) > 0 || count($_SESSION['CART']['escolar']) > 0) {


        encodeTitlesForJSON();

        $detalle_pedido  = json_encode($_SESSION["CART"], JSON_UNESCAPED_UNICODE);
        $fecha           = date("Y-m-d H:i:s");
        $cliente_id      = $_SESSION["id_cliente"];
        $direccion       = $_POST["direccion_envio"];
        $provincia       = $_POST["provincia"];
        $ciudad          = $_POST["ciudad"];
        $observaciones   = $_POST["observaciones"];
        $client_discount_books = getDiscount($db, 'libros');
        $client_discount_games = getDiscount($db, 'didacticos');
        $client_discount_toys = getDiscount($db, 'juguetes');
        $client_discount_schools = getDiscount($db, 'escolares');
        $estado          = "Nuevo";
        $vendedor_id     = isSeller()?$_SESSION['id_vendedor']:"NULL";
        $total = calculateOrderTotal($db);

        $query           = "INSERT INTO pedidos (fecha,estado,cliente_id,detalle,direccion_envio,provincia_id,ciudad_id,total,client_discount_books, client_discount_games, client_discount_toys, client_discount_schools, vendedor_id, observaciones)
            VALUES ('$fecha', '$estado','$cliente_id', '$detalle_pedido', '$direccion', '$provincia', '$ciudad', '$total', '$client_discount_books', '$client_discount_games', '$client_discount_toys', '$client_discount_schools', $vendedor_id, '$observaciones')";

        $result = mysqli_query($db, $query);

        if ($result) {
            mostrar_confirmacion_pedido($db, $t);
            deleteCart($db, $cliente_id);
        }
        // TODO: this can be false - add proper error handling
    } else {
        header("Location: " . HOST . "carritoCompras");
    }
}
function pintar_pedidos_detalle_old($db, $t, $id_pedido)
{ //No esta mas en funcionamiento este código febrero 2024

    $query  = "SELECT * FROM pedidos WHERE id = '$id_pedido' LIMIT 1";
    $result = mysqli_query($db, $query);

    if ($result->num_rows == 0) {
        header("Location: " . HOST . "miCuentaMisPedidos");
    } else {
        while ($row = mysqli_fetch_array($result)) {
            $descuento_libros = $row['client_discount_books'];
            $descuento_didacticos = $row['client_discount_games'];
            $descuento_juguetes = $row['client_discount_toys'];
            $descuento_escolares = $row['client_discount_schools'];

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

            if ($descuento_libros != null && $descuento_libros > 0) {
                $discount_porcentaje = $descuento_libros * 100;
                $str                 = "Descuento del " . $discount_porcentaje . "%";
                $t->set_var("descuento_cliente_libros", $str);
            } else {
                $t->set_var("descuento_cliente_libros", '');
            }

            if ($descuento_didacticos != null && $descuento_didacticos > 0) {
                $discount_porcentaje = $descuento_didacticos * 100;
                $str                 = "Descuento del " . $discount_porcentaje . "%";
                $t->set_var("descuento_cliente_didacticos", $str);
            } else {
                $t->set_var("descuento_cliente_didacticos", '');
            }

            if ($descuento_juguetes != null && $descuento_juguetes > 0) {
                $discount_porcentaje = $descuento_juguetes * 100;
                $str                 = "Descuento del " . $discount_porcentaje . "%";
                $t->set_var("descuento_cliente_juguetes", $str);
            } else {
                $t->set_var("descuento_cliente_juguetes", '');
            }

            if ($descuento_escolares != null && $descuento_escolares > 0) {
                $discount_porcentaje = $descuento_escolares * 100;
                $str                 = "Descuento del " . $discount_porcentaje . "%";
                $t->set_var("descuento_cliente_escolares", $str);
            } else {
                $t->set_var("descuento_cliente_escolares", '');
            }

            $t->set_var("total", formatNumber($row['total'], 2));

            $row['detalle'] = preg_replace('/[[:cntrl:]]/', '', $row['detalle']);

            $array = json_decode($row['detalle']);

            $total_libros   = 0;
            $total_juegos   = 0;
            $total_juguetes = 0;
            $total_escolares= 0;

            // libros
            $tf = new Template(PATH, "remove");
            $tf->set_var('base_url', HOST);
            $tf->set_file("pl", "cardPedidosDetalle.html");
            $tf->set_block("pl", "pedidos", "_pedidos");

            for ($i = 0; $i < sizeof($array[0]); $i++) {
                $tf->set_var("nombre", $array[0][$i]->nombre);
                $cantidad = $array[0][$i]->cantidad;
                $tf->set_var("cantidad", $cantidad);
                $descuento_p      = $array[0][$i]->descuento;
                $precio           = $array[0][$i]->precio;
                $precio_descuento = $array[0][$i]->precio;
                if ($descuento_p != null && $descuento_p > 0) {
                    $precio_descuento = $precio - $precio * $descuento_p / 100;
                }

                $costo = (float) $precio_descuento - (float) $precio_descuento * $descuento_libros;

                $tf->set_var("precio", formatNumber($precio, 2));
                $tf->set_var("descuento_p", formatNumber($costo, 2));

                $total_libros += round($costo * $cantidad, 2);
                $tf->set_var("suma", formatNumber($costo * $cantidad, 2));
                $tf->parse("_pedidos", "pedidos", true);
            }

            // didacticos
            $tl = new Template(PATH, "remove");
            $tl->set_var('base_url', HOST);
            $tl->set_file("pl", "cardPedidosDetalleDidactico.html");
            $tl->set_block("pl", "pedidosdidactico", "_pedidosdidactico");

            for ($i = 0; $i < sizeof($array[1]); $i++) {
                $tl->set_var("nombre", $array[1][$i]->nombre);
                $cantidad = $array[1][$i]->cantidad;
                $tl->set_var("cantidad", $cantidad);
                $descuento_p = intval($array[1][$i]->descuento);
                $precio      = $array[1][$i]->precio;
                if ($descuento_p != null && $descuento_p > 0) {
                    $precio = $precio - $precio * $descuento_p / 100;
                }

                $precio *= (1 - $descuento_didacticos);

                $result = 0.21 * ($precio);
                $tl->set_var("precio_dida", formatNumber($precio, 2));
                $tl->set_var("iva", formatNumber($result * $cantidad, 2));
                $tl->set_var("suma", formatNumber(($precio + $result) * $cantidad, 2));
                $total_juegos = $total_juegos + round(($precio + $result) * $cantidad, 2);
                $tl->parse("_pedidosdidactico", "pedidosdidactico", true);
            }

            // juguetes
            $templateJuguetes = new Template(PATH, "remove");
            $templateJuguetes->set_var('base_url', HOST);
            $templateJuguetes->set_file("pl", "cardPedidosDetalleDidactico.html");
            $templateJuguetes->set_block("pl", "pedidosdidactico", "_pedidosdidactico");

            for ($i = 0; $i < sizeof($array[2]); $i++) {
                $templateJuguetes->set_var("nombre", $array[2][$i]->nombre);
                $cantidad = $array[2][$i]->cantidad;
                $templateJuguetes->set_var("cantidad", $cantidad);
                $descuento_p = intval($array[2][$i]->descuento);
                $precio      = $array[2][$i]->precio;
                if ($descuento_p != null && $descuento_p > 0) {
                    $precio = $precio - $precio * $descuento_p / 100;
                }

                $precio *= (1 - $descuento_juguetes);

                $result = 0.21 * ($precio);
                $templateJuguetes->set_var("precio_dida", formatNumber($precio, 2));
                $templateJuguetes->set_var("iva", formatNumber($result * $cantidad, 2));
                $templateJuguetes->set_var("suma", formatNumber(($precio + $result) * $cantidad, 2));
                $total_juguetes += round(($precio + $result) * $cantidad, 2);
                $templateJuguetes->parse("_pedidosdidactico", "pedidosdidactico", true);
            }

            // Escolares
            $templateEscolares = new Template(PATH, "remove");
            $templateEscolares->set_var('base_url', HOST);
            $templateEscolares->set_file("pl", "cardPedidosDetalleDidactico.html");
            $templateEscolares->set_block("pl", "pedidosdidactico", "_pedidosdidactico");

            for ($i = 0; $i < sizeof($array[3]); $i++) {
                $templateEscolares->set_var("nombre", $array[3][$i]->nombre);
                $cantidad = $array[3][$i]->cantidad;
                $templateEscolares->set_var("cantidad", $cantidad);
                $descuento_p = intval($array[3][$i]->descuento);
                $precio      = $array[3][$i]->precio;
                if ($descuento_p != null && $descuento_p > 0) {
                    $precio = $precio - $precio * $descuento_p / 100;
                }

                $precio *= (1 - $descuento_escolares);

                $result = 0.21 * ($precio);
                $templateEscolares->set_var("precio_dida", formatNumber($precio, 2));
                $templateEscolares->set_var("iva", formatNumber($result * $cantidad, 2));
                $templateEscolares->set_var("suma", formatNumber(($precio + $result) * $cantidad, 2));
                $total_escolares += round(($precio + $result) * $cantidad, 2);
                $templateEscolares->parse("_pedidosdidactico", "pedidosdidactico", true);
            }
        }

        $pedidoJuguetes   = $templateJuguetes->parse("MAIN", "pl");
        $pedidoEscolares  = $templateEscolares->parse("MAIN", "pl");
        $pedidosdidactico = $tl->parse("MAIN", "pl");
        $pedido           = $tf->parse("MAIN", "pl");

        $t->set_var("subtotal_libros", formatNumber($total_libros, 2));
        $t->set_var("subtotal_juegos", formatNumber($total_juegos, 2));
        $t->set_var("subtotal_juguetes", formatNumber($total_juguetes, 2));
        $t->set_var("subtotal_escolares", formatNumber($total_escolares, 2));

        $t->set_var("didactico", $pedidosdidactico);
        $t->set_var("pedido", $pedido);
        $t->set_var("juguetes", $pedidoJuguetes);
        $t->set_var("escolares", $pedidoEscolares);
    }

}
function calculateOrderTotal_old($db) 
{//No esta mas en funcionamiento este código febrero 2024

  $subtotal_libros_descuento   = 0;
  $subtotal_juegos_descuento   = 0;
  $subtotal_juguetes_descuento = 0;
  $subtotal_escolares_descuento= 0;
  $descuento_libros            = getDiscount($db, 'libros');
  $descuento_didacticos        = getDiscount($db, 'didacticos');
  $descuento_juguetes          = getDiscount($db, 'juguetes');
  $descuento_escolares         = getDiscount($db, 'escolares');

  // libros
  //$booksInCart = $_SESSION['CART'][0];
  $booksInCart = $_SESSION['CART']['libro'];

  foreach ($booksInCart as $i => $valor) {
      if ($booksInCart[$i] != null) {
          $descuento_producto = $booksInCart[$i]['descuento'];
          if ($descuento_producto != null && $descuento_producto > 0) {
              $precio_descuento = round($booksInCart[$i]['precio'] * (1 - ($descuento_producto/100)), 2);
          } else {
              $precio_descuento = $booksInCart[$i]['precio'];
          }
          $subtotal_libros_descuento += $precio_descuento * $booksInCart[$i]['cantidad'];
      }

  }

  // didacticos
  //  $gamesInCart = $_SESSION['CART'][1];
  $gamesInCart = $_SESSION['CART']['juego'];

  foreach ($gamesInCart as $i => $valor) {
      if ($gamesInCart[$i] != null) {
          $descuento_producto = $gamesInCart[$i]['descuento'];
          if ($descuento_producto != null && $descuento_producto > 0) {
              $precio_descuento = round($gamesInCart[$i]['precio'] * (1 - ($descuento_producto/100)), 2);
          } else {
              $precio_descuento = $gamesInCart[$i]['precio'];
          }
          $subtotal_juegos_descuento += round(($precio_descuento * $gamesInCart[$i]['cantidad']), 2);
      }
  }

  // juguetes
  //$toysInCart = $_SESSION['CART'][2];
  $toysInCart = $_SESSION['CART']['juguete'];

  foreach ($toysInCart as $i => $valor) {
      if ($toysInCart[$i] != null) {
          $descuento_producto = $toysInCart[$i]['descuento'];
          if ($descuento_producto != null && $descuento_producto > 0) {
              $precio_descuento = round($toysInCart[$i]['precio'] * (1 - ($descuento_producto/100)), 2);
          } else {
              $precio_descuento = $toysInCart[$i]['precio'];
          }
          $subtotal_juguetes_descuento += round(($precio_descuento * $toysInCart[$i]['cantidad']), 2);
      }
  }

  // escolares
 // $schoolInCart = $_SESSION['CART'][3];
  $schoolInCart = $_SESSION['CART']['escolar'];

  foreach ($schoolInCart as $i => $valor) {
      if ($schoolInCart[$i] != null) {
          $descuento_producto = $schoolInCart[$i]['descuento'];
          if ($descuento_producto != null && $descuento_producto > 0) {
              $precio_descuento = round($schoolInCart[$i]['precio'] * (1 - ($descuento_producto/100)), 2);
          } else {
              $precio_descuento = $schoolInCart[$i]['precio'];
          }
          $subtotal_escolares_descuento += round(($precio_descuento * $schoolInCart[$i]['cantidad']), 2);
      }
  }

  $subtotal_libros_descuento = $subtotal_libros_descuento*(1-$descuento_libros);

  $subtotal_juegos_descuento = $subtotal_juegos_descuento*(1-$descuento_didacticos);

  $subtotal_juguetes_descuento = $subtotal_juguetes_descuento*(1-$descuento_juguetes);

  $subtotal_escolares_descuento = $subtotal_escolares_descuento*(1-$descuento_escolares);

  return round($subtotal_juegos_descuento*1.21 + $subtotal_juguetes_descuento*1.21 + $subtotal_escolares_descuento*1.21 + $subtotal_libros_descuento, 2);
}
