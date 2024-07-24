<?php

function showCatalogs() {
	global $db, $t;

	$t->set_var('base_url', HOST);
    $t->set_file("pl", "catalogos.html");
    $t->set_var("user", $_SESSION["nombre_usuario"]);

    if (isSeller()) {
    	$t->set_var("hiddeCard", "style='display:none;'");
    	showSidebarPanelSeller($_SESSION['id_vendedor']);
    }
    else {
    	showDatosViajante($t, $_SESSION['id_cliente']);
    }

    $tf = new Template(PATH, "remove");
    $tf->set_var('base_url', HOST);
    $tf->set_file("pl", "cardCatalogos.html");
    $tf->set_block("pl", "catalogos", "_catalogos");

    $files = scandir('catalogos-archivos');

    foreach ($files as $file) {
    	if (!is_dir($file)) {
	    	$tf->set_var("name", $file);
		    $tf->set_var("url", HOST . 'catalogos-archivos/' . rawurlencode($file));
		    $tf->parse("_catalogos", "catalogos", true);

	        $catalogo = $tf->parse("MAIN", "pl");
	        $t->set_var("catalogo", $catalogo);
	    }
    }
    
}