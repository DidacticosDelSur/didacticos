<?php

function setComments($id, $comment)
{ 
    global $db;

	$producto = getProductCart($db, $id, 0);

    $value = find($producto['tipo'], $id);
    if ($value !== -1) {
        $_SESSION['CART'][$producto['tipo']][$value]['observaciones'] = $comment;
        guardar_carrito($db);
    }

    die;
}