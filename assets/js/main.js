$(".hamburguer-content").bind("click", function () {
  $(".mobile-menu").slideToggle("");
  $("nav").toggleClass("slicing");
  $(".hamburguer-content .icon-menu_FILL0").toggle();
  $(".hamburguer-content .icon-close").toggle();
});
/*

$('.btn-toolt').bind('click', function(){
$(".lightbox").fadeIn().css("display","block");
});

$('.btn-close-light').bind('click', function(){
$(".lightbox").fadeOut();
});

   $('nav a').bind('click', function(){
   $('nav').slideUp('');
});

$('button.next').bind('click', function(){
setTimeout(function () {
$('body').addClass('scroller');
}, 1000);
 $('.stage').addClass('hidder');
});


$('.bx1').bind('click', function(){
   $(this).toggleClass('checked');
   $('.bx2').removeClass('checked');
   $('.deskText').show('');
   $('.mobText').hide('');
});

$('.bx2').bind('click', function(){
   $(this).toggleClass('checked');
   $('.bx1').removeClass('checked');
   $('.deskText').hide('');
   $('.mobText').show('');
});
*/

$(".newsletter-form").on("submit", function (evt) {
  evt.preventDefault();

  if ($(this).validationEngine("validate")) {
    email = $(this).find("input").prop("disabled", true).val();

    $.ajax({
      url: "/newsletter/" + email,
      success: (data) => {
        $(".newsletter-form-wrapper").html(
          '<h3 style="margin: 0;">Gracias</h3>'
        );
      },
    });
  }
});
