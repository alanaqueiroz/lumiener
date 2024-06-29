(function ($) {
    "use strict";

    $('.owl-carousel').owlCarousel({
        loop: true,
        margin: 30,
        nav: true,
        pagination: true,
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 1
            },
            1000: {
                items: 2
            }
        }
    });

    $(window).scroll(function () {
        var scroll = $(window).scrollTop();
        var box = $('.header-text').height();
        var header = $('header').height();

        if (scroll == 0) {
            $("header").removeClass("background-header");
        } else if (scroll >= box - header) {
            $("header").addClass("background-header");
        } else {
            $("header").removeClass("background-header");
        }
    });

    // Window Resize Mobile Menu Fix
    mobileNav();

    // Menu Dropdown Toggle
    if ($('.menu-trigger').length) {
        $(".menu-trigger").on('click', function () {
            $(this).toggleClass('active');
            $('.header-area .nav').slideToggle(200, function () {
                // Ajustar a margem dos itens do menu ao abrir
                if ($('.menu-trigger').hasClass('active')) {
                    $('.header-area .nav').css('margin-top', '0px'); // Ajuste conforme necessário
                } else {
                    $('.header-area .nav').css('margin-top', '0');
                }
            });
        });
    }

    // Ajuste de posição do menu trigger no mobile
    $('.menu-trigger').css('left', '20px'); // Ajuste a margem do topo conforme necessário

    const Accordion = {
        settings: {
            first_expanded: false,
            toggle: false
        },

        openAccordion: function (toggle, content) {
            if (content.children.length) {
                toggle.classList.add("is-open");
                let final_height = Math.floor(content.children[0].offsetHeight);
                content.style.height = final_height + "px";
            }
        },

        closeAccordion: function (toggle, content) {
            toggle.classList.remove("is-open");
            content.style.height = 0;
        },

        init: function (el) {
            const _this = this;

            let is_first_expanded = _this.settings.first_expanded;
            if (el.classList.contains("is-first-expanded")) is_first_expanded = true;
            let is_toggle = _this.settings.toggle;
            if (el.classList.contains("is-toggle")) is_toggle = true;

            const sections = el.getElementsByClassName("accordion");
            const all_toggles = el.getElementsByClassName("accordion-head");
            const all_contents = el.getElementsByClassName("accordion-body");
            for (let i = 0; i < sections.length; i++) {
                const section = sections[i];
                const toggle = all_toggles[i];
                const content = all_contents[i];

                toggle.addEventListener("click", function (e) {
                    if (!is_toggle) {
                        for (let a = 0; a < all_contents.length; a++) {
                            _this.closeAccordion(all_toggles[a], all_contents[a]);
                        }
                        _this.openAccordion(toggle, content);
                    } else {
                        if (toggle.classList.contains("is-open")) {
                            _this.closeAccordion(toggle, content);
                        } else {
                            _this.openAccordion(toggle, content);
                        }
                    }
                });

                if (i === 0 && is_first_expanded) {
                    _this.openAccordion(toggle, content);
                }
            }
        }
    };

    (function () {
        const accordions = document.getElementsByClassName("accordions");
        for (let i = 0; i < accordions.length; i++) {
            Accordion.init(accordions[i]);
        }
    })();

    // Home separator
    if ($('.home-seperator').length) {
        $('.home-seperator .left-item, .home-seperator .right-item').imgfix();
    }

    // Home number counterup
    if ($('.count-item').length) {
        $('.count-item strong').counterUp({
            delay: 10,
            time: 1000
        });
    }

    // Page loading animation
    $(window).on('load', function () {
        if ($('.cover').length) {
            $('.cover').parallax({
                imageSrc: $('.cover').data('image'),
                zIndex: '1'
            });
        }

        $("#preloader").animate({
            'opacity': '0'
        }, 600, function () {
            setTimeout(function () {
                $("#preloader").css("visibility", "hidden").fadeOut();
            }, 300);
        });
    });

    // Window Resize Mobile Menu Fix
    $(window).on('resize', function () {
        mobileNav();
    });

    function mobileNav() {
        var width = $(window).width();
        $('.submenu').on('click', function () {
            if (width < 992) {
                $('.submenu ul').removeClass('active');
                $(this).find('ul').toggleClass('active');
            }
        });
    }

})(window.jQuery);
