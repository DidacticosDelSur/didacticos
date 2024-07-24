<?php

function mostrar_home($db, $t) {
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "index.html");
    //parameters $t, $db, tipo = 'libro' o 'juego' , tag.nombre , titulo carousel (default tag.nombre) , no tocar nombre carousel

    if (idate("m") >= 5 && idate("U") <= date("U", strtotime('third sunday of ' . date('Y-08')))) {
        $t->set_var('banner', '<div>
            <a href="/categorias/juguetes">
                <img src="{base_url}assets/images/banners/banner-ddn.jpeg" alt="Día del niño">
            </a>
        </div>');
    }
    get_carousel($db,$t);

    $tema = getTematica();
    $t->set_var('temeticaClass',$tema);

    if ($tema == 'three-wise-men'){
        $t->set_var("confetti_class","");
        $t->set_var("luces_class","");
        $t->set_var("desierto_class","class='desierto'");
    } else if ($tema == 'christmas'){
        $t->set_var("confetti_class","");
        $t->set_var("luces_class","class='luces'");
        $t->set_var("desierto_class","");
    } else {
        $t->set_var("confetti_class","class='confetti'");
        $t->set_var("luces_class","");
        $t->set_var("desierto_class","");
    }

    showTagList(tag_index_carousel1, 'carousel1');
    showTagList(tag_index_carousel2, 'carousel2');
    showTagList(tag_index_carousel3, 'carousel3');

}

function contact($t) {
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "contact.html");

}
function aboutus($t) {
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "aboutus.html");

}
function faqs($t) {
    $t->set_var('base_url', HOST);
    $t->set_var('minimum', formatNumber(getMinimum(), 2));
    $t->set_file("pl", "faqs.html");

}

function howTo($t) {
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "como-comprar.html");

}

function showBankData() {
    global $t;
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "cbu.html");

}

function confirmRegister($t) {
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "confirmRegister.html");

}
function mostrar_login($t) {
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "login.html");
    $tema = getTematica();
    $t->set_var('temeticaClass',$tema);
}

function mostrar_olvide($t) {
    $t->set_var('base_url', HOST);
    $t->set_file("pl", "olvide.html");
}