<?php

function showTerms()
{
    global $t;

    $t->set_var('base_url', HOST);
    $t->set_var('costo_minimo', formatNumber(getMinimum(), 2));
    $t->set_file("pl", "terminos-condiciones.html");
}