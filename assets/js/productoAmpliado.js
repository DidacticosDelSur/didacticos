//document.write('<script src="http://' + (location.host || 'localhost').split(':')[0] + ':35729/livereload.js?snipver=1"></' + 'script>')

$(document).on('ready', function() {
    $(".regular").slick({
        dots: true,
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        asNavFor: '.slider-nav',
    });


    $('.slider-nav').slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        asNavFor: '.regular',
        //centerMode: true,
        focusOnSelect: true,
        infinite: false,
    });

    $('.regular').on('beforeChange', function(event, slick, currentSlide, nextSlide){
      var current = $(slick.$slides[currentSlide]);
      current.html(current.html());
    });

    $('.regular').on('afterChange', function(event, slick, currentSlide, nextSlide){
      $('.izoom').izoomify({
        magnify: 2.5,
        touch: false,
      });
    });

    $('.moreBuy').slick({
        dots: true,
        infinite: true,
        speed: 300,
        slidesToShow: 4,
        slidesToScroll: 4,
        responsive: [{
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    infinite: true,
                    dots: true
                }
            }, {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            }, {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
            // You can unslick at a given breakpoint now by adding:
            // settings: "unslick"
            // instead of a settings object
        ]
    });

    Fancybox.bind("[data-fancybox]", {
        infinite: false,
        Carousel: {
            on: {
              change: (that) => {
                if (that.slides[that.pageIndex].src != '#video') {
                    $("#video iframe").attr('src', $("#video iframe").attr('src'))
                }
              },
            },
        },
    });

});