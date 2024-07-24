<?php

//function guardar_imagen_banner(){
    error_log('en guardar imagen banner');
    extract($_POST);
    $dir = "uploads/";
    if(!is_dir($dir))
    mkdir($dir);
    error_log('despues de crear directorio '.$tipo);
    $img = str_replace('data:image/png;base64,', '', $img);
    $img = str_replace(' ', '+', $img);
    $img = base64_decode($img);
    $nombre = explode('.',$fname)[0].'-'.$tipo.'.'.explode('.',$fname)[1];
    error_log('nombre: '.$nombre);
    $save = file_put_contents($dir.$nombre, $img);
    if($save){
        http_response_code(200);
        $resp['status'] = 'success';
    }else{
        http_response_code(400);
        $resp['status'] = 'failed';

    }
    error_log(json_encode($resp));
    echo json_encode($resp);
//}