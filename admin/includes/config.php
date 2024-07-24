<?PHP

$env = 'dev';
const HOST = "http://didacticos/dds/admin/";

//$env = 'dev2';
//const HOST = "http://dev.didacticosdelsur.com/admin/";

//$env = 'staging';
//const HOST = "https://www.didacticosdelsur.com/staging/admin/";

//$env = 'production';
//const HOST = "https://www.didacticosdelsur.com/admin/";

if ($env === 'dev') {
    $mysql_host = "localhost";
    $mysql_username = "root";
    $mysql_passwd   = "";
    $mysql_database = "didactic_didacticos";
    //$mysql_database = "dds_produccion";
    //$mysql_database = "dds_didacticos";

    $templates          = "./html/";
    $path_site          = "./";
    $path_fotos         = "http://didacticos/dds/images/productos/";
    $path_fotos_banner         = "http://didacticos/dds/assets/images/banners/";
    $url_site           = "http://didacticos/dds";
    $directorio_destino = "/wamp64/www/didacticos/dds/images/";
    $directorio_banners_destino = "/wamp64/www/didacticos/dds/assets/images/";
    $path_home = '/wamp64/www/didacticos/dds/';
    $path_functions = '/wamp64/www/didacticos/dds/admin/includes/functions';
}

if ($env === 'dev2') {
    $mysql_host = "localhost";
    $mysql_username = "root";
    $mysql_passwd = "";
    $mysql_database = "didactic_didacticos_staging";

    $templates = "./html/";
    $path_site          = "./dds/";
    $path_fotos         = "http://didacticos/dds/images/productos/";
    $url_site           = "http://didacticos/dds";
    $directorio_destino = "/var/www/html/images/";
    $path_home = 'd:/apps/wamp/www/didacticosdelsur/';
}

if ($env === 'staging') {
    $mysql_host     = "localhost";
    $mysql_username = "didactic_user";
    $mysql_passwd   = "eMGi88W#vL#D";
    $mysql_database = "didactic_didacticos_staging";

    $templates          = "/home2/didacticosdelsur/public_html/staging/admin/html/";
    $path_site          = "https://www.didacticosdelsur.com/staging/admin";
    $path_fotos         = "https://www.didacticosdelsur.com/staging/images/productos/";
    $directorio_destino = "/home2/didacticosdelsur/public_html/staging/images/";
    $url_site           = "https://www.didacticosdelsur.com/staging";
    $path_home = '/home2/didacticosdelsur/public_html/staging/';
}

if ($env === 'production') {
    $mysql_host     = "localhost";
    $mysql_username = "didactic_user";
    $mysql_passwd   = "eMGi88W#vL#D";
    $mysql_database = "didactic_didacticos";

    $templates          = "/home2/didacticosdelsur/public_html/admin/html/";
    $path_site          = "https://www.didacticosdelsur.com/admin";
    $path_fotos         = "https://www.didacticosdelsur.com/images/productos/";
    $directorio_destino = "/home2/didacticosdelsur/public_html/images/";
    $url_site           = "https://www.didacticosdelsur.com";
    $path_home = '/home2/didacticosdelsur/public_html/';
}
