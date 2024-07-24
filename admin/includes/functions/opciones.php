<?php

function showOptions() {
	global $db, $t;

    $t->set_var('base_url', HOST);
    $t->set_file("pl", "opciones/opciones.html");
    $t->set_var("user", $_SESSION["admin_name"]);

    $query  = "SELECT * FROM opciones";
    $result = mysqli_query($db, $query);

    $t->set_block("pl", "opciones", "_opciones");

    while ($row = mysqli_fetch_array($result)) {
        $t->set_var("label", $row['title']);

        $input = '';

        switch ($row['type']) {
        	case 'number':
        		$input = '<input type="number" name="' . $row['option_name'] . '" value="' . $row['option_value'] . '" />';
        		break;
        	case 'checkbox':
        		$input = '<input type="checkbox" name="' . $row['option_name'] . '" value="1" ' . (($row['option_value'] == '1')?"checked":"") . ' />';
        		break;
        	case 'text':
        		$input = '<textarea name="' . $row['option_name'] . '">' . $row['option_value'] . '</textarea>';
        		break;
            case 'select': 
                $q = "SELECT * FROM tematica_estilos";
                $res = mysqli_query($db, $q);
                $input = '<select id="tematica" name="tematica" class="form-control">
                            <option value="">Seleccione una temática...</option>';
                while ($r = mysqli_fetch_array($res)) {
                    $input .= '<option value="'.$r['id'].'" '. (($row['option_value'] == $r['id']) ? "selected":"").'>'.$r['nombre'].'</option>';
                }
                $input .= '</select>';
        		break;
        }
        $t->set_var("input", $input);

        $t->parse("_opciones", "opciones", true);
    }
}

function saveOptions() {
	global $db, $t;

	$query = 'UPDATE opciones SET option_value="0" WHERE type = "checkbox"';
    $result = mysqli_query($db, $query);
    error_log(json_encode($_POST));

    foreach ($_POST as $name => $value) {
        $query = "UPDATE opciones SET option_value='$value' WHERE option_name = '$name'";
        error_log($query);
    	$result = mysqli_query($db, $query);    	
    }

    header("Location: " . HOST . "mostrar_inicio");
    exit;
}