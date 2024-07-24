<?php 
function paginado($t, $cant_productos, $page, $url, $limit){

  $t->set_var("mostrar_atras", "style='display: none;'");
  $t->set_var("mostrar_siguiente", "style='display: none;'");
  $t->set_var("mostrar_paginator", "style='display:none;'");
  $t->set_var("mostrar_azar", "style='display:block;'");
  $t->set_var("mostrar_totalxpag", "style='display:none;'");
  $t->set_var("mostrar_cero_result", "style='display:none;'");
 
  if ($page != null) {
    $total = ceil($cant_productos/$limit);
    $ant = $page == 1 ? null : $page - 1;
    $sig = $page == $total ? null : $page + 1;

    $t->set_block("pl", "pag", "_pag");
    if (intval($limit) > intval($cant_productos)) {
      $t->set_var("id_page", 1);
      $t->set_var("active",  'active');
      $t->set_var("url",$url);
      $total = 1;
      $t->parse("_pag", "pag", true);
      $page = null;
      $t->set_var("pagina", 1);
      $t->set_var("mostrar_atras", "style='display: none;'");
      $t->set_var("mostrar_siguiente", "style='display: none;'");
    } else {
      for ($i=1; $i<=$total; $i++){
        $t->set_var("id_page", $i);
        $t->set_var("active",  ($i == $page ? 'active' : ''));
        $t->set_var("url",$url);
        $t->parse("_pag", "pag", true);
      }
      $t->set_var("pagina", $page);
      if ($page == 1) {
        $t->set_var("mostrar_atras", "style='display: none;'");
      }  else {
        $t->set_var("mostrar_atras", "style='display: block;'");
      }
      if ($page == $total) {
        $t->set_var("mostrar_siguiente", "style='display: none;'");
      }  else {
        $t->set_var("mostrar_siguiente", "style='display: block;'");
      }
    }

    $t->set_var("cant_paginas", $total);
    $t->set_var("mostrar_paginator", "style='display:flex;'");
    $t->set_var("mostrar_azar", "style='display:none;'");
    $t->set_var("mostrar_totalxpag", "style='display:block;'");
    $t->set_var("mostrar_cero_result", "style='display:none;'");
    $t->set_var("showCartCount", "style=''");
    if ($cant_productos == 0) {
      $t->set_var("mostrar_azar", "style='display:none;'");
      $t->set_var("mostrar_totalxpag", "style='display:none;'");
      $t->set_var("mostrar_cero_result", "style='display:block;'");
    } else { 
      $t->set_var("total_por_pagina", $limit);
    }
    $t->set_var("url_section",$url);

    $t->set_var("pagina-ant", $ant);
    $t->set_var("pagina-sig", $sig);
  } 
}