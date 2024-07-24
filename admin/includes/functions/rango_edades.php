<?php 

//RANGO DE EDADES

function getRangoEdades($db, $t, $tipo_sel = 0) {
  $t->set_block("pl", "rangos", "_rangos");

  $query_todas  = "SELECT * FROM rango_edades where ISNULL(eliminado)";
  $result_todas = mysqli_query($db, $query_todas);

  while ($row = mysqli_fetch_array($result_todas)) {
    if (trim($tipo_sel) == trim($row['id'])) {
      $t->set_var("seleccionado", "selected");
    } else {
      $t->set_var("seleccionado", "");
    }

    $t->set_var("rango", $row['rango']);
    $t->set_var("id", $row['id']);
    $t->parse("_rangos", "rangos", true);
  }
}

function listar_rango_edades($db, $t, $error = null)
{
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "listado_rango_edades.html");
  $t->set_var("error", $error);
  $t->set_var("user", $_SESSION["admin_name"]);
  $t->set_block("pl", "edades", "_edades");
  $query  = "SELECT id, rango FROM rango_edades WHERE ISNULL(eliminado) order by id";
  $result = mysqli_query($db, $query);

  while ($row = mysqli_fetch_array($result)) {
    $t->set_var("id_rango_edad", $row['id']);
    $t->set_var("rango", $row['rango']);
    
    $t->parse("_edades", "edades", true);
  } 
}

function nuevo_rango_edad($db,$t){
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "nuevo_rango_edad.html");
}

function guardar_rango_edad($db)
{
  $id_rango_edad   = $_POST["id_rango_edad"];
  $rango           = $_POST["rango"];

  if ($id_rango_edad != "") {
    $query = "UPDATE rango_edades SET rango = '$rango' where id = " . $id_rango_edad;
    mysqli_query($db, $query);
  } else {
    $query = "INSERT INTO rango_edades (rango) VALUES ('$rango')";
    mysqli_query($db, $query);
  }
  header("Location: " . HOST . "listar_rango_edades");
  exit;
}

function editar_rango_edad($db,$t, $id_rango_edad){
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "nuevo_rango_edad.html");
  $query = "SELECT id, rango FROM rango_edades WHERE id = $id_rango_edad";
  $result = mysqli_query($db, $query);

  $row = mysqli_fetch_array($result);

  $t->set_var("id_rango_edad", $row['id']);
  $t->set_var("rango", $row['rango']);

}

function eliminar_rango_edad($db, $id_rango_edad){
  $query  = "SELECT count(id) FROM productos WHERE estado != 'No Disponible' AND  rango_edad=" . $id_rango_edad;
  $result = mysqli_query($db, $query);
  $row    = mysqli_fetch_array($result);
  if ($row['count(id)'] > 0) {
      echo "error";
      exit;
  } else {
      $query = "UPDATE rango_edades SET eliminado =  now() WHERE id = " . $id_rango_edad;
      error_log($id_rango_edad);
      mysqli_query($db, $query);
      exit;
  }
}