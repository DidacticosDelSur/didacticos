<?php

function showAnnouncements() {
	global $db, $t;

	$t->set_var('base_url', HOST);
    $t->set_file("pl", "anuncios.html");
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
    $tf->set_file("pl", "cardAnuncios.html");
    $tf->set_block("pl", "anuncios", "_anuncios");

    $sql = "SELECT * FROM anuncios ORDER BY fecha DESC"; // TODO: limitar a ultimos 30 dias
    
    $query = mysqli_query($db, $sql);
    
    while ($row = mysqli_fetch_array($query)) {
    	$date = date_create($row['fecha']);
        $tf->set_var("fecha", date_format($date, 'd-m-Y'));
        $tf->set_var("descripcion", $row['descripcion']);
        $tf->parse("_anuncios", "anuncios", true);

    }

    $catalogo = $tf->parse("MAIN", "pl");
    $t->set_var("anuncio", $catalogo);
}