<?php
//const PATH = "./html/";
//VG Febrero 2024  Refactor categorias. Este archivo contiene todas las consultas vinculadas con la base de datos tipo_categorias

function getTipoCategorias($db) 
{ //VG: Refactor categorias Febrero 2024
  $categorias = [];
  $sql = "SELECT * FROM tipo_categorias WHERE eliminado <> 1 order by id";
  $result = mysqli_query($db, $sql);
  while ($row_ = mysqli_fetch_array($result)) {
    $row_['categoria'] = $row_['categoria'];
   // $row_['categoria'] = utf8_encode($row_['categoria']);//prueba local
    $categorias[] = $row_;
  }
  return $categorias;
}

function getTipo($db,$tipo)
{//VG: Refactor categorias Febrero 2024
  $sql = "SELECT tipo FROM tipo_categorias WHERE link = '$tipo' or tipo = '$tipo'";
  $result = mysqli_query($db, $sql);
  $row    = mysqli_fetch_array($result);

  return $row['tipo']; 
}

function getTipoById($db,$id)
{//VG: Refactor categorias Febrero 2024
  $sql = "SELECT tipo FROM tipo_categorias WHERE id = $id";
  error_log($sql);
  $result = mysqli_query($db, $sql);
  $row    = mysqli_fetch_array($result);

  return $row['tipo']; 
}

function getTipos($db) 
{//VG: Refactor categorias
  $tipos = [];
  $sql = "SELECT tipo FROM tipo_categorias WHERE eliminado = 0";
  $result = mysqli_query($db, $sql);
  while ($row = mysqli_fetch_array($result)) {
    $tipos[] = $row['tipo'];
  }
  return $tipos;
}

function obtener_tipos($db)
{//VG: Refactor categorias
  $categorias = getTipos($db);
  echo json_encode($categorias);
  die;
}

function getCategoria($db,$tipo)
{//VG: Refactor categorias Febrero 2024
  $sql = "SELECT categoria FROM tipo_categorias";
  if (is_numeric($tipo)) {
    $id = intval($tipo) + 1;
    $sql .= " WHERE id = '$id'";
  } else {
    $sql .= " WHERE tipo = '$tipo'";
  }
  $result = mysqli_query($db, $sql);
  $row    = mysqli_fetch_array($result);

  return $row['categoria']; 
 // return utf8_encode($row['categoria']); 
}

function setCategoria($db, $t, $tipo)
{ //VG: Refactor Categorias
    $categoria = getCategoria($db, $tipo);

    $t->set_var("categoria_tipo", $tipo);
    $t->set_var("categoria", $categoria);
   
}

function translateType($db,$type) 
{ //VG: Refactor categorias Febrero 2024
  $sql = "SELECT dto_tag FROM tipo_categorias";
  if (is_numeric($type)){
    $id = intval($type) + 1;
    $sql .= " WHERE id = $id";
  } else {
    $sql .= "  WHERE tipo = '$type'";
  }

  $result = mysqli_query($db, $sql);
  $row    = mysqli_fetch_array($result);
  return $row['dto_tag'];
}

function tipo_exento($db, $type) 
{//VG: Refactor categorias Febrero 2024
  $sql = "SELECT exento_iva FROM tipo_categorias WHERE tipo = '$type' AND eliminado = 0";
  $result = mysqli_query($db, $sql);
  $row    = mysqli_fetch_array($result);
  return $row['exento_iva'] == 1;

}

function obtener_data_categorias($db) 
{//VG:Refactor categorias Febrero 2024
  $categorias = [];
  $sql = "SELECT id, tipo, categoria, clase_estilo, exento_iva, link FROM tipo_categorias WHERE eliminado = 0 order by id";
  $query  = mysqli_query($db, $sql);

  while ($row = mysqli_fetch_array($query)) {
    $categorias[] = [
      'id'=> $row['id'],
      'tipo' => $row['tipo'],
      'categoria'=>$row['categoria'], 
      //'categoria'=>utf8_encode($row['categoria']), 
      'clase_estilo'=>$row['clase_estilo'], 
      'exento_iva' => $row['exento_iva'], 
      'link' => $row['link']
    ];
  }

  return $categorias;
}