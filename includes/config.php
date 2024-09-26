<?PHP

$env = 'dev';
const HOST = "http://localhost/didacticos/";
const HOST_SINGLE = "http://localhost/didacticos";

//$env = 'staging';
//const HOST = "https://diegofernandez.org/dds/";
//const HOST_SINGLE = "https://diegofernandez.org/dds";

//$env = 'production';
//const HOST = "https://www.didacticosdelsur.com/";
//const HOST_SINGLE = "https://www.didacticosdelsur.com";

if ($env === 'dev') {
    $mysql_host = "localhost";
    $mysql_username = "root";
    $mysql_passwd = "mysql";
    $mysql_database = "didacticos";

    $templates = "./html/";
    $path_site = "/Volumes/Sites/didacticos/";
    $path_home = "./";
    $url_site = "http://localhos/didacticos/";
    $path_fotos = "/Aplicaciones/AMPPS/www/didacticos/images/productos/";
}

if ($env === 'staging') {

    $mysql_host = "localhost";
    $mysql_username = "diegofer_diegofer";
    $mysql_passwd = "miguelito666";
    $mysql_database = "diegofer_didacticos";

    $templates = "/home2/diegofer/public_html/dds/html/";
    $path_home = '/home2/diegofer/public_html/dds/';
    $url_site = "https://diegofernandez.org/dds/";
    $path_fotos = "/home2/diegofer/public_html/dds/images/productos/";
}

if ($env === 'production') {

    $mysql_host = "localhost";
    $mysql_username = "didactic_user";
    $mysql_passwd = "eMGi88W#vL#D";
    $mysql_database = "didactic_didacticos";

    $templates = "/home2/didacticosdelsur/public_html/html/";
    $path_home = '/home2/didacticosdelsur/public_html/';
    $url_site = "https://www.didacticosdelsur.com";
    $path_fotos = "/home2/didacticosdelsur/public_html/images/productos/";
}


?>
