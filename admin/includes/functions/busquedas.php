<?php

function listar_busquedas($db,$t,$url_site){
  error_log('Listando busquedas');
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "listado_busquedas.html");
  $sql = "SELECT bc.busqueda,IF(isNull(c.id), 'Usuario no logueado', CONCAT(c.nombre,' ',c.apellido)) as usuario, bc.resultado, bc.link, DATE_FORMAT(bc.fecha, '%d/%m/%Y %H:%i') as fecha 
          FROM busquedas_clientes bc 
          LEFT JOIN clientes c on c.id = bc.cliente_id WHERE 1 ORDER By fecha desc";
  $result = mysqli_query($db, $sql);
  $t->set_block("pl", "busquedas", "_busquedas");

  while ($row = mysqli_fetch_array($result)) {
    $t->set_var('busqueda',$row['busqueda']);
    $t->set_var('usuario',$row['usuario']);
    $t->set_var('resultado',$row['resultado']);
    $t->set_var('link',$row['link'] == '' ? $url_site.'/buscaDesdeAdmin/'.$row['busqueda'] : $url_site.$row['link']);
    $t->set_var('fecha',$row['fecha']);
    $t->parse("_busquedas", "busquedas", true);
  }
}

function vaciar_busquedas($db) {
  $sql = "TRUNCATE busquedas_clientes";
  mysqli_query($db, $sql);
}