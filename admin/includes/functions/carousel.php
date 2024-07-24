<?php
//include_once 'save_image.php';

function listar_carousel($db,$t) {
  global $path_fotos_banner;
  $t->set_var('base_url', HOST);
  $t->set_var('base_url_images', $path_fotos_banner);
  $t->set_file("pl", "listado_carousel.html");
  $sql = "SELECT * FROM carousel_items WHERE eliminado IS NULL";
  $result     = mysqli_query($db, $sql);
  $t->set_block('pl','banners','_banners');
  while ($row = mysqli_fetch_array($result)) {
    $t->set_var('id_banner',$row['id']);
    $t->set_var('imagen_desk',$row['imagen_desktop']);
    $t->set_var('imagen_mobile',$row['imagen_mobile'] ? $row['imagen_mobile']:'');
    $t->set_var('show_mobile',$row['imagen_mobile'] ? "style='display:block;'":"style='display:none;'");
    $t->set_var('url',$row['url']?$row['url']:'');
    $t->parse("_banners", "banners", true);
  }
}
function nuevo_banner($t) {
  $t->set_var('base_url', HOST);
  $t->set_file("pl", "nuevo_banner.html");
  $t->set_var("show_banner","style='display:none;'");
  $t->set_var("show_remove_banner","style='margin:20px 0;display:none;'");
  $t->set_var("validate_file","class='form-control validate[required]'");
  $t->set_var("show_banner_mobile", "style='display:none;'");
  $t->set_var("show_remove_banner_mobile","style='display:none;'");
}

function guardar_banner($db, $directorio_destino, $path_fotos_banner){
  global $path_functions;

  error_log('path_functions: '.$path_functions);
  error_log('directorio_destino: '.$directorio_destino);
  error_log('path_fotos_banner: '.$path_fotos_banner);

  $id_banner = $_POST['id_banner'];
  $url = $_POST['url'];
  $banner_mobile = $_POST['banner_mobile'];

  if ($id_banner != "") {
    $sql = "UPDATE carousel_items SET url = '$url' WHERE id = $id_banner";
    mysqli_query($db, $sql);
    if (empty($$banner_mobile)) {
      $sql = "SELECT imagen_mobile FROM carousel_items WHERE id = $id_banner";
      $result = mysqli_query($db, $sql);
      $row    = mysqli_fetch_array($result);
      if ($row['imagen_mobile'] != ''){
        $archivo = $directorio_destino  .  $id_banner . '/'.$row['imagen_mobile'];
        if (file_exists($archivo)) {
          unlink($archivo);          //Eliminamos el archivo
          error_log('Despues de eliminar el archivo: '.$archivo);
          $sql = "UPDATE carousel_items SET imagen_mobile = '' WHERE id = $id_banner";
          error_log('Despues de eliminar el archivo: '.$sql);
          mysqli_query($db, $sql);
        }
      }
    }
  } else {
    $sql = "INSERT INTO carousel_items (url) VALUES ('$url')";
    mysqli_query($db, $sql);
    $id_banner = mysqli_insert_id($db);
  }

  error_log('despues de sql: '.$id_banner);
  if (!empty($_FILES['upload'])) {
    error_log("files not emplty");
    $srcPath  = createDir($directorio_destino .  $id_banner) . '/';
    $temPath = $path_functions.'/uploads';
    
    foreach ($_FILES['upload']['name'] as $key => $error) {
    error_log("en el for");
    if (isset($_FILES['upload']['name'][$key]) && !empty($_FILES['upload']['name'][$key])) {
        $fileN = explode('.',$_FILES['upload']['name'][$key]);

        $fileName = 'banner-'.$id_banner.'-'.$key.'.'.$fileN[1];
        error_log("fileName: ".$fileName);
        error_log("temPath: ".$temPath);
        if (is_dir($temPath)) {
          error_log("temPath is a dir ");
          $tmp_name = $fileN[0].'-'.$key.'.'.$fileN[1];
          if (file_exists($temPath.'/'.$tmp_name)){
            copy($temPath.'/'.$tmp_name,$srcPath.'/'.$fileName);
            vaciarDir($temPath);
          } else {
            $info     = new SplFileInfo($_FILES['upload']['name'][$key]);
            $tmp_name = $_FILES['upload']["tmp_name"][$key];
            $img_type = $_FILES['upload']["type"][$key];
            $exte     = $info->getExtension();
            subir_fichero($srcPath, $tmp_name, $img_type, $fileName);
          }

          $sql = "UPDATE carousel_items SET ";
          if ($key == 'desk') {
            $sql .= "imagen_desktop = '".$fileName."'";
          } else {
            $sql .= "imagen_mobile = '".$fileName."'"; 
          }
          $sql .= " WHERE id = $id_banner";
          error_log($sql);
          mysqli_query($db, $sql);
        }
      }
    } 
    header("Location: " . HOST . "banners");
    exit;
	}
}

function editar_banner($db, $t, $id_banner) {
  global $path_fotos_banner;
  $t->set_var('base_url', HOST);
  $t->set_var('base_url_images', $path_fotos_banner);
  $t->set_file("pl", "nuevo_banner.html");
  $t->set_var("user", $_SESSION["admin_name"]);
  $query  = "SELECT * FROM carousel_items WHERE id = " . $id_banner;
  $result = mysqli_query($db, $query);

  $row = mysqli_fetch_array($result);

  $t->set_var("id_banner", $row['id']);
  $t->set_var("url", $row['url']);
  $t->set_var("imagen_desktop", $row['imagen_desktop']);
  $t->set_var("show_banner", $row['imagen_desktop']?"style='display:block;'":"style='display:none;'");
  $t->set_var("show_remove_banner", $row['imagen_desktop']?"style='display:block;'":"style='display:none;'");
  $t->set_var("imagen_mobile", $row['imagen_mobile']);
  $t->set_var("show_banner_mobile", $row['imagen_mobile']?"style='display:block;'":"style='display:none;'");
  $t->set_var("show_remove_banner_mobile", $row['imagen_mobile']?"style='display:block;'":"style='display:none;'");
  $t->set_var("validate_file",$row['imagen_desktop']?"class='form-control'":"class='form-control validate[required]'");

}

function eliminar_banner($db,$banner_id){
  $sql = "UPDATE carousel_items SET eliminado = now() WHERE id = $banner_id";
  mysqli_query($db, $sql);
}
