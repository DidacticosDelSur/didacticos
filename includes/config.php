<?PHP

//define("RECAPTCHA_V3_SECRET_KEY", '6LcNpAsdAAAAAN9RnPQKMMAu_iIUwPiLEpCEwcUI');Seba
//define("RECAPTCHA_V3_WEB_KEY", '6LcNpAsdAAAAAPEtkG6mT65ncQyKWxUH92LzFjj6');Seba
define("RECAPTCHA_V3_WEB_KEY", '6LeTXwsqAAAAACKupltyhqqrO4Nlb9bEKBq56Ndf');//Vic
define("RECAPTCHA_V3_SECRET_KEY", '6LeTXwsqAAAAAFezTpumZyls2ZAR4ez61_78CUB1');//Vic
/*define("RECAPTCHA_KEY", '6LfkOwsqAAAAAJkmPySRVs7aikbUz6RgRz3olfOB');//VIC
define("RECAPTCHA_SECRET_KEY", '6LfkOwsqAAAAAG4m7A5goUXa44_0Umg4UlLX2Vop');//VIC
*/
$env = 'dev';
const HOST = "http://didacticos/dds/";

//$env = 'dev2';
//const HOST = "http://dev.didacticosdelsur.com/";

//$env = 'staging';
//const HOST = "https://www.didacticosdelsur.com/staging/";

//$env = 'production';
//const HOST = "https://www.didacticosdelsur.com/";

if ($env === 'dev') {
    $mysql_host = "localhost";
    $mysql_username = "root";
    $mysql_passwd = "";
    $mysql_database = "didactic_didacticos";

    $templates = "./html/";
    $path_site = "/Volumes/Sites/didacticos/";
    $path_home = "./";
    $url_site = "http://didacticos/dds/";
    $path_fotos = "/wamp64/www/didacticos/dds/images/productos/";
}

if ($env === 'dev2') {
    $mysql_host = "localhost";
    $mysql_username = "root";
    $mysql_passwd = "";
    $mysql_database = "didacticos_didacticos";

    $templates = "./html/";
    $path_site = "/Volumes/Sites/didacticos/";
    $path_home = 'd:/apps/wamp/www/didacticosdelsur/';
    $url_site = "http://dev.didacticosdelsur.com";
    $path_fotos = "/var/www/html/images/productos/";
}

if ($env === 'staging') {

    $mysql_host = "localhost";
    $mysql_username = "didactic_user";
    $mysql_passwd = "eMGi88W#vL#D";
    $mysql_database = "didactic_didacticos_staging";

    $templates = "/home2/didacticosdelsur/public_html/staging/html/";
    $path_home = '/home2/didacticosdelsur/public_html/staging/';
    $url_site = "https://www.didacticosdelsur.com/staging/";
    $path_fotos = "/home2/didacticosdelsur/public_html/staging/images/productos/";
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
