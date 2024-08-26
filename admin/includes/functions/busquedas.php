<?php

function listar_busquedas($db,$t,$url_site){
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "listado_busquedas.html");
  $sql = "SELECT bc.id, bc.busqueda,IF(isNull(c.id), 'Usuario no logueado', IF((bc.es_vendedor = 1), IF(isNull(bc.vendedor_id),CONCAT(v1.nombre,' ',v1.apellido),CONCAT(v.nombre,' ',v.apellido,' (',c.nombre,' ',c.apellido,')')), CONCAT(c.nombre,' ',c.apellido))) as usuario, bc.resultado, bc.link, DATE_FORMAT(bc.fecha, '%d/%m/%Y %H:%i') as fecha 
          FROM busquedas_clientes bc 
          LEFT JOIN clientes c on c.id = bc.cliente_id 
          LEFT JOIN vendedores v on v.id = bc.vendedor_id
          LEFT JOIN vendedores v1 on v1.id = bc.cliente_id
          WHERE 1 ORDER By fecha desc;";
  $result = mysqli_query($db, $sql);
  $t->set_block("pl", "busquedas", "_busquedas");

  while ($row = mysqli_fetch_array($result)) {
    $t->set_var('id_busqueda',$row['id']);
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

function eliminar_busqueda($db,$id)
{
  $sql = "DELETE FROM busquedas_clientes WHERE id = $id;";
  mysqli_query($db, $sql);

}