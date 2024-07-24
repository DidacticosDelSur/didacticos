<?php
const PATH = "./html/";

function get_carousel($db,$t){
  $tc = new Template(PATH, "remove");
  $tc->set_var('base_url', HOST);
  $tc->set_file("pl", "carousel.html");
  $sql = "SELECT * FROM carousel_items WHERE eliminado IS NULL";
  $result = mysqli_query($db, $sql);
  $tc->set_block("pl", "banners", "_banners");
  $tc->set_block("pl", "banner_links", "_banner_links");
  $cont = 0;
  while ($row = mysqli_fetch_array($result)) {
    $tc->set_var('item_selected',$cont === 0 ? 'class="item-select-slid"':'');
    $tc->set_var('show',$cont === 0 ? 'style="z-index: 0; opacity: 1;"':'');
    $tc->set_var('id_banner',$row['id']);
    $tc->set_var('id_link',$cont);
    $tc->set_var('url',$row['url']);
    $tc->set_var('img_desk',$row['imagen_desktop']);
    $tc->set_var('mobile-class','');
    $tc->set_var('img_mobile',$row['imagen_mobile']?$row['imagen_mobile']:$row['imagen_desktop']);
    $tc->parse("_banners", "banners", true);
    $tc->parse("_banner_links", "banner_links", true);
    $cont++;
  }
  $item = $tc->parse("MAIN", "pl");
  $t->set_var("banner", $item); 
}