// PROFILE MENU
$(".coockies__btn").click(function () {
    $(".coockies").fadeOut();
});
// PROFILE MENU

// PROFILE MENU
$(".profile-aside__burger").click(function (e) {
    $(this).toggleClass("aside-toggler__toggle");
    $(".profile-aside").toggleClass("aside-open");
});
// PROFILE MENU

// SLOT SEARCH
$(".slot__search-switch").click(function (e) {
    $(".slot__search").toggleClass("slot__search-toggle");
    $(this).toggleClass("slot__search-switch-hide");

    e.stopPropagation();
});

$(".slot__search_inner").click(function (e) {
    e.stopPropagation();
});

$(".search-slot__close").click(function (e) {
    $(".slot__search").toggleClass("slot__search-toggle");

    setTimeout(function () {
        $(".slot__search-switch").toggleClass("slot__search-switch-hide");
    }, 500);
});



$(document).click(function () {
    $(".slot__search-switch").removeClass("slot__search-switch-hide");
    $(".slot__search").removeClass("slot__search-toggle");
});

// SLOT SEARCH


// DROPDOWN
$(".dropdown__toggle").click(function (e) {
    $(this).find(".dropdown__content").fadeToggle("fast", "swing");
    $(this).toggleClass("dropdown__active");

    e.stopPropagation();
});

$(".dropdown__content").click(function (e) {
    e.stopPropagation();
});

$(document).click(function () {
    $(".dropdown__content").fadeOut("fast", "swing");
    $(this).removeClass("dropdown__active");
});

// DROPDOWN


// NICE SELECT
$(document).ready(function () {
    $('.nice-select').niceSelect();
});
// NICE SELECT


// ACCORDEON
$(document).ready(function () {
    $(".more").hide();
    $('.accordeon__head').click(function () {
        $(this).parent().siblings().not($(this)).find(".accordeon__body").slideUp(300);
        $(this).parent().find(".accordeon__body").slideToggle(300);

        $(this).parent().siblings().not($(this)).find(".accordeon__angle").removeClass("accordeon__toggle");
        $(this).parent().find(".accordeon__angle").toggleClass("accordeon__toggle");
    });
});
// ACCORDEON


// TABS
$(".tab__body").not(":first-child").hide();
$(".tab__head").click(function () {
    $(this).siblings().removeClass("tab__active").removeAttr('disabled');
    $(this).addClass("tab__active").attr('disabled', 'disabled');
    $(this).closest('.tabs').find('.tab__body').hide().eq($(this).index()).fadeIn();
}).eq(0).addClass("tab__active").attr('disabled', 'disabled');
// TABS


//  MASK
$('document').ready(function () {
    $(".phone_inp").mask("+7 (999) 999-99-99", {
        autoclear: false,
    });
    // $('.phone_inp').mask('+7 (000) 000-00-00');
});
// MASK

// FANCYBOX
$('[data-fancybox]').fancybox({
    autoFocus: false,
    modal: true,
});

$(document).keyup(function (e) {
    if (e.keyCode == 27) {
        $.fancybox.close();
    }
});

$('.popup__close-btn').click(function () {
    parent.$.fancybox.close();
});

$('.popup__close').click(function () {
    parent.$.fancybox.close();
});

$("input").keyup(function (e) {
    if (e.keyCode == 27) {
        $.fancybox.close();
    }
});
// FANCYBOX


// VALIDATION
$(document).ready(function () {
    $("form").each(function () {
        $(this).validate({});
    });
});

// $("#myinput").rules("add", {
//     required: true,
//     minlength: 2,
//     messages: {
//         required: "Required input",
//         minlength: jQuery.validator.format("Please, at least {0} characters are necessary")
//     }
// });
// VALIDATION


// GOTOP
var gotop = $('.gotop');
gotop.on('click', function (e) {
    e.preventDefault();
    $(window).scrollTop(0);
});
// GOTOP


// IBG
function testWebP(callback) {
    var webP = new Image();
    webP.src = "data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA";
    webP.onload = webP.onerror = function () {
        callback(webP.height == 2);
    };
}

testWebP(function (support) {
    $(".ibg").each(function () {
        if (support &&
            $(this).find('.ibg__img').siblings('source') &&
            $(this).find('.ibg__img').siblings('source').attr('srcset') != null) {
            $(this).css('background-image', 'url(' + $(this).find('source').attr('srcset') + ')');
            $(this).find(".ibg__img").first().parent("picture").remove();
        } else if ($(this).find('.ibg__img') && $(this).find('.ibg__img').attr('src') != null) {
            $(this).css('background-image', 'url(' + $(this).find('.ibg__img').attr('src') + ')');
            $(this).find(".ibg__img").first().remove();
        }
    });
});
// IBG;


// LAZYLOAD
$(document).ready(function () {
    setTimeout(function () {

        $(".lazy").each(function () {
            $(this).siblings('source').attr("srcset", $(this).attr("data-src-alt"));
            $(this).attr("src", $(this).attr("data-src"));

            $(this).on('load', function () {
                $(this).addClass("lazyloaded");
            });
        });
    }, 2000);
});
// LAZYLOAD;
// NAVBAR
$(document).ready(function () {
    // If an event gets to the body
    $('.navbar__open').click(function () {
        $('.navbar__box').toggleClass("nav__toggle");
        $(this).toggleClass("aside-toggler__toggle");
        $("body").toggleClass('body-toggle');
    });

    // Close menu on press Esc button
    $(document).on('keydown', function (e) {
        if (e.keyCode === 27) { // ESC
            $('.navbar__box').removeClass("nav__toggle");
            $("body").css('overflow', 'auto');
        }
    });
});
// NAVBAR


//  NAVBAR SCROLL
function scroll() {
    var scrollTop = $(window).scrollTop();
    $('.navbar').toggleClass('navbar__scroll', scrollTop > 0);
    $('.gotop__wrap').toggleClass('gotop_toggle', scrollTop > 0);
    $('.scoreboard_navbar').toggleClass('scoreboard_navbar-toggle', scrollTop > 0);
}

$(document).ready(function () {
    scroll();
});

$(window).on('scroll', function () {
    scroll();
});
//  NAVBAR SCROLL;
// POPULAR SLIDER
$(document).ready(function () {
    $('.popular-slider').slick({
        pauseOnHover: false,
        infinite: true,
        slidesToShow: 3,
        slidesToScroll: 1,
        autoplay: true,
        arrows: false,
        dots: false,
        appendDots: $('.popular-slider__dots'),
        zIndex: 1,

        responsive: [{
            breakpoint: 767,
            settings: {
                slidesToShow: 1,
                dots: true,
            }
        },
    ]
    });
});
// POPULAR SLIDER


// SLIDER
$(document).ready(function () {
    $('.slider').slick({
        pauseOnHover: false,
        infinite: true,
        slidesToShow: 4,
        focusOnSelect: true,
        slidesToScroll: 1,
        autoplay: true,
        arrows: true,
        prevArrow: '<button class="left_arrow"><img src="img/common/arrow_left.svg" alt="img"></button>',
        nextArrow: '<button class="right_arrow"><img src="img/common/arrow_right.svg" alt="img"></button>',
        dots: false,
        swipeToSlide: true,
        zIndex: 1,

        responsive: [{
                breakpoint: 767,
                settings: {
                    slidesToShow: 1,
                    appendArrows: $('.slider__arrow'),
                    arrows: false,
                }
            },
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 2,
                    arrows: false,
                }
            },
            {
                breakpoint: 1200,
                settings: {
                    slidesToShow: 3,
                    arrows: false,

                }
            },
        ]
    });
});
// SLIDER


// POPULAR SLIDER
$(document).ready(function () {
    $('.scoreboard__slider').slick({
        pauseOnHover: false,
        infinite: true,
        slidesToShow: 1,
        speed: 5000,
        autoplaySpeed: 0,
        autoplay: true,
        arrows: false,
        dots: false,
        zIndex: 1,
        variableWidth: true,
        cssEase: 'linear',
        // centerMode: true,
    });
});
// POPULAR SLIDER;
"use strict";

(function () {
	let originalPositions = [];
	let daElements = document.querySelectorAll('[data-da]');
	let daElementsArray = [];
	let daMatchMedia = [];
	//Заполняем массивы
	if (daElements.length > 0) {
		let number = 0;
		for (let index = 0; index < daElements.length; index++) {
			const daElement = daElements[index];
			const daMove = daElement.getAttribute('data-da');
			if (daMove != '') {
				const daArray = daMove.split(',');
				const daPlace = daArray[1] ? daArray[1].trim() : 'last';
				const daBreakpoint = daArray[2] ? daArray[2].trim() : '767';
				const daType = daArray[3] === 'min' ? daArray[3].trim() : 'max';
				const daDestination = document.querySelector('.' + daArray[0].trim());
				if (daArray.length > 0 && daDestination) {
					daElement.setAttribute('data-da-index', number);
					//Заполняем массив первоначальных позиций
					originalPositions[number] = {
						"parent": daElement.parentNode,
						"index": indexInParent(daElement)
					};
					//Заполняем массив элементов
					daElementsArray[number] = {
						"element": daElement,
						"destination": document.querySelector('.' + daArray[0].trim()),
						"place": daPlace,
						"breakpoint": daBreakpoint,
						"type": daType
					};
					number++;
				}
			}
		}
		dynamicAdaptSort(daElementsArray);

		//Создаем события в точке брейкпоинта
		for (let index = 0; index < daElementsArray.length; index++) {
			const el = daElementsArray[index];
			const daBreakpoint = el.breakpoint;
			const daType = el.type;

			daMatchMedia.push(window.matchMedia("(" + daType + "-width: " + daBreakpoint + "px)"));
			daMatchMedia[index].addListener(dynamicAdapt);
		}
	}
	//Основная функция
	function dynamicAdapt(e) {
		for (let index = 0; index < daElementsArray.length; index++) {
			const el = daElementsArray[index];
			const daElement = el.element;
			const daDestination = el.destination;
			const daPlace = el.place;
			const daBreakpoint = el.breakpoint;
			const daClassname = "_dynamic_adapt_" + daBreakpoint;

			if (daMatchMedia[index].matches) {
				//Перебрасываем элементы
				if (!daElement.classList.contains(daClassname)) {
					let actualIndex = indexOfElements(daDestination)[daPlace];
					if (daPlace === 'first') {
						actualIndex = indexOfElements(daDestination)[0];
					} else if (daPlace === 'last') {
						actualIndex = indexOfElements(daDestination)[indexOfElements(daDestination).length];
					}
					daDestination.insertBefore(daElement, daDestination.children[actualIndex]);
					daElement.classList.add(daClassname);
				}
			} else {
				//Возвращаем на место
				if (daElement.classList.contains(daClassname)) {
					dynamicAdaptBack(daElement);
					daElement.classList.remove(daClassname);
				}
			}
		}
		customAdapt();
	}

	//Вызов основной функции
	dynamicAdapt();

	//Функция возврата на место
	function dynamicAdaptBack(el) {
		const daIndex = el.getAttribute('data-da-index');
		const originalPlace = originalPositions[daIndex];
		const parentPlace = originalPlace['parent'];
		const indexPlace = originalPlace['index'];
		const actualIndex = indexOfElements(parentPlace, true)[indexPlace];
		parentPlace.insertBefore(el, parentPlace.children[actualIndex]);
	}
	//Функция получения индекса внутри родителя
	function indexInParent(el) {
		var children = Array.prototype.slice.call(el.parentNode.children);
		return children.indexOf(el);
	}
	//Функция получения массива индексов элементов внутри родителя
	function indexOfElements(parent, back) {
		const children = parent.children;
		const childrenArray = [];
		for (let i = 0; i < children.length; i++) {
			const childrenElement = children[i];
			if (back) {
				childrenArray.push(i);
			} else {
				//Исключая перенесенный элемент
				if (childrenElement.getAttribute('data-da') == null) {
					childrenArray.push(i);
				}
			}
		}
		return childrenArray;
	}
	//Сортировка объекта
	function dynamicAdaptSort(arr) {
		arr.sort(function (a, b) {
			if (a.breakpoint > b.breakpoint) {
				return -1;
			} else {
				return 1;
			}
		});
		arr.sort(function (a, b) {
			if (a.place > b.place) {
				return 1;
			} else {
				return -1;
			}
		});
	}
	//Дополнительные сценарии адаптации
	function customAdapt() {
		//const viewport_width = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
	}
}());;
/*  jQuery Nice Select - v1.1.0
    https://github.com/hernansartorio/jquery-nice-select
    Made by Hernán Sartorio  */

(function ($) {

    $.fn.niceSelect = function (method) {

        // Methods
        if (typeof method == 'string') {
            if (method == 'update') {
                this.each(function () {
                    var $select = $(this);
                    var $dropdown = $(this).next('.nice-select');
                    var open = $dropdown.hasClass('open');

                    if ($dropdown.length) {
                        $dropdown.remove();
                        create_nice_select($select);

                        if (open) {
                            $select.next().trigger('click');
                        }
                    }
                });
            } else if (method == 'destroy') {
                this.each(function () {
                    var $select = $(this);
                    var $dropdown = $(this).next('.nice-select');

                    if ($dropdown.length) {
                        $dropdown.remove();
                        $select.css('display', '');
                    }
                });
                if ($('.nice-select').length == 0) {
                    $(document).off('.nice_select');
                }
            } else {
                console.log('Method "' + method + '" does not exist.');
            }
            return this;
        }

        // Hide native select
        this.hide();

        // Create custom markup
        this.each(function () {
            var $select = $(this);

            if (!$select.next().hasClass('nice-select')) {
                create_nice_select($select);
            }
        });

        function create_nice_select($select) {
            $select.after($('<div></div>')
                .addClass('nice-select')
                .addClass($select.attr('class') || '')
                .addClass($select.attr('disabled') ? 'disabled' : '')
                .attr('tabindex', $select.attr('disabled') ? null : '0')
                .html('<span class="current"></span><ul class="list"></ul>')
            );

            var $dropdown = $select.next();
            var $options = $select.find('option');
            var $selected = $select.find('option:selected');

            $dropdown.find('.current').html($selected.data('display') || "<img src='" + $selected.attr('data-image') + "' alt=\"img\">" + $selected.text());

            $options.each(function (i) {
                var $option = $(this);
                var display = $option.data('display');

                $dropdown.find('ul').append($('<li></li>')
                    .attr('data-value', $option.val())
                    .attr('data-image', $option.attr('data-image'))
                    .attr('data-display', (display || null))
                    .addClass('option' +
                        ($option.is(':selected') ? ' selected' : '') +
                        ($option.is(':disabled') ? ' disabled' : ''))
                    .html("<img src='" + $option.attr('data-image') + "' alt=\"img\">" + $option.text())
                );
            });
        }

        /* Event listeners */

        // Unbind existing events in case that the plugin has been initialized before
        $(document).off('.nice_select');

        // Open/close
        $(document).on('click.nice_select', '.nice-select', function (event) {
            var $dropdown = $(this);

            $('.nice-select').not($dropdown).removeClass('open');
            $dropdown.toggleClass('open');

            if ($dropdown.hasClass('open')) {
                $dropdown.find('.option');
                $dropdown.find('.focus').removeClass('focus');
                $dropdown.find('.selected').addClass('focus');
            } else {
                $dropdown.focus();
            }
        });

        // Close when clicking outside
        $(document).on('click.nice_select', function (event) {
            if ($(event.target).closest('.nice-select').length === 0) {
                $('.nice-select').removeClass('open').find('.option');
            }
        });

        // Option click
        $(document).on('click.nice_select', '.nice-select .option:not(.disabled)', function (event) {
            var $option = $(this);
            var $dropdown = $option.closest('.nice-select');

            $dropdown.find('.selected').removeClass('selected');
            $option.addClass('selected');

            var text = $option.data('display') || "<img src='" + $option.attr('data-image') + "' alt=\"img\">" + $option.text();
            $dropdown.find('.current').html(text);

            $dropdown.prev('select').val($option.data('value')).trigger('change');
        });

        // Keyboard events
        $(document).on('keydown.nice_select', '.nice-select', function (event) {
            var $dropdown = $(this);
            var $focused_option = $($dropdown.find('.focus') || $dropdown.find('.list .option.selected'));

            // Space or Enter
            if (event.keyCode == 32 || event.keyCode == 13) {
                if ($dropdown.hasClass('open')) {
                    $focused_option.trigger('click');
                } else {
                    $dropdown.trigger('click');
                }
                return false;
                // Down
            } else if (event.keyCode == 40) {
                if (!$dropdown.hasClass('open')) {
                    $dropdown.trigger('click');
                } else {
                    var $next = $focused_option.nextAll('.option:not(.disabled)').first();
                    if ($next.length > 0) {
                        $dropdown.find('.focus').removeClass('focus');
                        $next.addClass('focus');
                    }
                }
                return false;
                // Up
            } else if (event.keyCode == 38) {
                if (!$dropdown.hasClass('open')) {
                    $dropdown.trigger('click');
                } else {
                    var $prev = $focused_option.prevAll('.option:not(.disabled)').first();
                    if ($prev.length > 0) {
                        $dropdown.find('.focus').removeClass('focus');
                        $prev.addClass('focus');
                    }
                }
                return false;
                // Esc
            } else if (event.keyCode == 27) {
                if ($dropdown.hasClass('open')) {
                    $dropdown.trigger('click');
                }
                // Tab
            } else if (event.keyCode == 9) {
                if ($dropdown.hasClass('open')) {
                    return false;
                }
            }
        });

        // Detect CSS pointer-events support, for IE <= 10. From Modernizr.
        var style = document.createElement('a').style;
        style.cssText = 'pointer-events:auto';
        if (style.pointerEvents !== 'auto') {
            $('html').addClass('no-csspointerevents');
        }

        return this;

    };

}(jQuery));;
// RIPPLE
(function (window) {
    'use strict';

    var Waves = Waves || {};
    var $$ = document.querySelectorAll.bind(document);

    // Find exact position of element
    function isWindow(obj) {
        return obj !== null && obj === obj.window;
    }

    function getWindow(elem) {
        return isWindow(elem) ? elem : elem.nodeType === 9 && elem.defaultView;
    }

    function offset(elem) {
        var docElem, win,
            box = {
                top: 0,
                left: 0
            },
            doc = elem && elem.ownerDocument;

        docElem = doc.documentElement;

        if (typeof elem.getBoundingClientRect !== typeof undefined) {
            box = elem.getBoundingClientRect();
        }
        win = getWindow(doc);
        return {
            top: box.top + win.pageYOffset - docElem.clientTop,
            left: box.left + win.pageXOffset - docElem.clientLeft
        };
    }

    function convertStyle(obj) {
        var style = '';

        for (var a in obj) {
            if (obj.hasOwnProperty(a)) {
                style += (a + ':' + obj[a] + ';');
            }
        }

        return style;
    }

    var Effect = {

        // Effect delay
        duration: 500,

        show: function (e, element) {

            // Disable right click
            if (e.button === 2) {
                return false;
            }

            var el = element || this;

            // Create ripple
            var ripple = document.createElement('div');
            ripple.className = 'waves-ripple';
            el.appendChild(ripple);

            // Get click coordinate and element witdh
            var pos = offset(el);
            var relativeY = (e.pageY - pos.top);
            var relativeX = (e.pageX - pos.left);
            var scale = 'scale(' + ((el.clientWidth / 100) * 80) + ')';

            // Support for touch devices
            if ('touches' in e) {
                relativeY = (e.touches[0].pageY - pos.top);
                relativeX = (e.touches[0].pageX - pos.left);
            }

            // Attach data to element
            ripple.setAttribute('data-hold', Date.now());
            ripple.setAttribute('data-scale', scale);
            ripple.setAttribute('data-x', relativeX);
            ripple.setAttribute('data-y', relativeY);

            // Set ripple position
            var rippleStyle = {
                'top': relativeY + 'px',
                'left': relativeX + 'px'
            };

            ripple.className = ripple.className + ' waves-notransition';
            ripple.setAttribute('style', convertStyle(rippleStyle));
            ripple.className = ripple.className.replace('waves-notransition', '');

            // Scale the ripple
            rippleStyle['-webkit-transform'] = scale;
            rippleStyle['-moz-transform'] = scale;
            rippleStyle['-ms-transform'] = scale;
            rippleStyle['-o-transform'] = scale;
            rippleStyle.transform = scale;
            rippleStyle.opacity = '1';

            rippleStyle['-webkit-transition-duration'] = Effect.duration + 'ms';
            rippleStyle['-moz-transition-duration'] = Effect.duration + 'ms';
            rippleStyle['-o-transition-duration'] = Effect.duration + 'ms';
            rippleStyle['transition-duration'] = Effect.duration + 'ms';

            rippleStyle['-webkit-transition-timing-function'] = 'cubic-bezier(0.250, 0.460, 0.450, 0.940)';
            rippleStyle['-moz-transition-timing-function'] = 'cubic-bezier(0.250, 0.460, 0.450, 0.940)';
            rippleStyle['-o-transition-timing-function'] = 'cubic-bezier(0.250, 0.460, 0.450, 0.940)';
            rippleStyle['transition-timing-function'] = 'cubic-bezier(0.250, 0.460, 0.450, 0.940)';

            ripple.setAttribute('style', convertStyle(rippleStyle));
        },

        hide: function (e) {
            TouchHandler.touchup(e);

            var el = this;
            var width = el.clientWidth * 1.4;

            // Get first ripple
            var ripple = null;
            var ripples = el.getElementsByClassName('waves-ripple');
            if (ripples.length > 0) {
                ripple = ripples[ripples.length - 1];
            } else {
                return false;
            }

            var relativeX = ripple.getAttribute('data-x');
            var relativeY = ripple.getAttribute('data-y');
            var scale = ripple.getAttribute('data-scale');

            // Get delay beetween mousedown and mouse leave
            var diff = Date.now() - Number(ripple.getAttribute('data-hold'));
            var delay = 350 - diff;

            if (delay < 0) {
                delay = 0;
            }

            // Fade out ripple after delay
            setTimeout(function () {
                var style = {
                    'top': relativeY + 'px',
                    'left': relativeX + 'px',
                    'opacity': '0',

                    // Duration
                    '-webkit-transition-duration': Effect.duration + 'ms',
                    '-moz-transition-duration': Effect.duration + 'ms',
                    '-o-transition-duration': Effect.duration + 'ms',
                    'transition-duration': Effect.duration + 'ms',
                    '-webkit-transform': scale,
                    '-moz-transform': scale,
                    '-ms-transform': scale,
                    '-o-transform': scale,
                    'transform': scale,
                };

                ripple.setAttribute('style', convertStyle(style));

                setTimeout(function () {
                    try {
                        el.removeChild(ripple);
                    } catch (e) {
                        return false;
                    }
                }, Effect.duration);
            }, delay);
        },

        // Little hack to make <input> can perform waves effect
        wrapInput: function (elements) {
            for (var a = 0; a < elements.length; a++) {
                var el = elements[a];

                if (el.tagName.toLowerCase() === 'input') {
                    var parent = el.parentNode;

                    // If input already have parent just pass through
                    if (parent.tagName.toLowerCase() === 'i' && parent.className.indexOf('ripple') !== -1) {
                        continue;
                    }

                    // Put element class and style to the specified parent
                    var wrapper = document.createElement('i');
                    wrapper.className = el.className + ' waves-input-wrapper';

                    var elementStyle = el.getAttribute('style');

                    if (!elementStyle) {
                        elementStyle = '';
                    }

                    wrapper.setAttribute('style', elementStyle);

                    el.className = 'waves-button-input';
                    el.removeAttribute('style');

                    // Put element as child
                    parent.replaceChild(wrapper, el);
                    wrapper.appendChild(el);
                }
            }
        }
    };


    /**
     * Disable mousedown event for 500ms during and after touch
     */
    var TouchHandler = {
        /* uses an integer rather than bool so there's no issues with
         * needing to clear timeouts if another touch event occurred
         * within the 500ms. Cannot mouseup between touchstart and
         * touchend, nor in the 500ms after touchend. */
        touches: 0,
        allowEvent: function (e) {
            var allow = true;

            if (e.type === 'touchstart') {
                TouchHandler.touches += 1; //push
            } else if (e.type === 'touchend' || e.type === 'touchcancel') {
                setTimeout(function () {
                    if (TouchHandler.touches > 0) {
                        TouchHandler.touches -= 1; //pop after 500ms
                    }
                }, 500);
            } else if (e.type === 'mousedown' && TouchHandler.touches > 0) {
                allow = false;
            }

            return allow;
        },
        touchup: function (e) {
            TouchHandler.allowEvent(e);
        }
    };


    /**
     * Delegated click handler for .ripple element.
     * returns null when .ripple element not in "click tree"
     */
    function getWavesEffectElement(e) {
        if (TouchHandler.allowEvent(e) === false) {
            return null;
        }

        var element = null;
        var target = e.target || e.srcElement;

        while (target.parentElement !== null) {
            if (!(target instanceof SVGElement) && target.className.indexOf('ripple') !== -1) {
                element = target;
                break;
            } else if (target.classList.contains('ripple')) {
                element = target;
                break;
            }
            target = target.parentElement;
        }

        return element;
    }

    /**
     * Bubble the click and show effect if .ripple elem was found
     */
    function showEffect(e) {
        var element = getWavesEffectElement(e);

        if (element !== null) {
            Effect.show(e, element);

            if ('ontouchstart' in window) {
                element.addEventListener('touchend', Effect.hide, false);
                element.addEventListener('touchcancel', Effect.hide, false);
            }

            element.addEventListener('mouseup', Effect.hide, false);
            element.addEventListener('mouseleave', Effect.hide, false);
        }
    }

    Waves.displayEffect = function (options) {
        options = options || {};

        if ('duration' in options) {
            Effect.duration = options.duration;
        }

        //Wrap input inside <i> tag
        Effect.wrapInput($$('.ripple'));

        if ('ontouchstart' in window) {
            document.body.addEventListener('touchstart', showEffect, false);
        }

        document.body.addEventListener('mousedown', showEffect, false);
    };

    /**
     * Attach Waves to an input element (or any element which doesn't
     * bubble mouseup/mousedown events).
     *   Intended to be used with dynamically loaded forms/inputs, or
     * where the user doesn't want a delegated click handler.
     */
    Waves.attach = function (element) {
        //FUTURE: automatically add waves classes and allow users
        // to specify them with an options param? Eg. light/classic/button
        if (element.tagName.toLowerCase() === 'input') {
            Effect.wrapInput([element]);
            element = element.parentElement;
        }

        if ('ontouchstart' in window) {
            element.addEventListener('touchstart', showEffect, false);
        }

        element.addEventListener('mousedown', showEffect, false);
    };

    window.Waves = Waves;

    document.addEventListener('DOMContentLoaded', function () {
        Waves.displayEffect();
    }, false);

})(window);
// RIPPLE;
//FORMS
function forms() {

	//SELECT
	if ($('select').length > 0) {
		function selectscrolloptions() {
			var scs = 100;
			var mss = 50;
			var opt = {
				cursorcolor: "#ff6600",
				cursorwidth: "4px",
				background: "",
				autohidemode: false,
				bouncescroll: false,
				cursorborderradius: "10px",
				scrollspeed: scs,
				mousescrollstep: mss,
				directionlockdeadzone: 0,
				cursorborder: "0px solid #c5c5c5",
			};
			return opt;
		}

		function select() {
			$.each($('select'), function (index, val) {
				var ind = index;
				$(this).hide();
				if ($(this).parent('.select-block').length == 0) {
					$(this).wrap("<div class='select-block " + $(this).attr('class') + "-select-block'></div>");
				} else {
					$(this).parent('.select-block').find('.select').remove();
				}
				let cl = '';
				var milti = '';
				var check = '';
				var sblock = $(this).parent('.select-block');
				var soptions = "<div class='select-options'><div class='select-options-scroll'><div class='select-options-list'>";
				if ($(this).attr('multiple') == 'multiple') {
					milti = 'multiple';
					check = 'check';
				}
				$.each($(this).find('option'), function (index, val) {
					if ($(this).attr('class') != '' && $(this).attr('class') != null) {
						let cl = $(this).attr('class');
					}
					if ($(this).attr('value') != '') {
						if ($(this).attr('data-icon') != '' && $(this).attr('data-icon') != null) {
							soptions = soptions + "<div data-value='" + $(this).attr('value') + "' class='select-options__value_" + ind + " select-options__value value_" + $(this).val() + " " + cl + " " + check + "'><div><img src=" + $(this).attr('data-icon') + " alt=\"\"></div><div>" + $(this).html() + "</div></div>";
						} else {
							soptions = soptions + "<div data-value='" + $(this).attr('value') + "' class='select-options__value_" + ind + " select-options__value value_" + $(this).val() + " " + cl + " " + check + "'>" + $(this).html() + "</div>";
						}
					} else if ($(this).parent().attr('data-label') == 'on') {
						if (sblock.find('.select__label').length == 0) {
							sblock.prepend('<div class="select__label">' + $(this).html() + '</div>');
						}
					}
				});
				soptions = soptions + "</div></div></div>";
				if ($(this).attr('data-type') == 'search') {
					sblock.append("<div data-type='search' class='select_" + ind + " select" + " " + $(this).attr('class') + "__select " + milti + "'>" +
						"<div class='select-title'>" +
						"<div class='select-title__arrow ion-ios-arrow-down'></div>" +
						"<input data-value='" + $(this).find('option[selected="selected"]').html() + "' class='select-title__value value_" + $(this).find('option[selected="selected"]').val() + "' />" +
						"</div>" +
						soptions +
						"</div>");
					$('.select_' + ind).find('input.select-title__value').jcOnPageFilter({
						parentSectionClass: 'select-options_' + ind,
						parentLookupClass: 'select-options__value_' + ind,
						childBlockClass: 'select-options__value_' + ind
					});
				} else if ($(this).attr('data-icon') == 'true') {
					sblock.append("<div class='select_" + ind + " select" + " " + $(this).attr('class') + "__select icon " + milti + "'>" +
						"<div class='select-title'>" +
						"<div class='select-title__arrow ion-ios-arrow-down'></div>" +
						"<div class='select-title__value value_" + $(this).find('option[selected="selected"]').val() + "'><div><img src=" + $(this).find('option[selected="selected"]').attr('data-icon') + " alt=\"\"></div><div>" + $(this).find('option[selected="selected"]').html() + "</div></div>" +
						"</div>" +
						soptions +
						"</div>");
				} else {
					sblock.append("<div class='select_" + ind + " select" + " " + $(this).attr('class') + "__select " + milti + "'>" +
						"<div class='select-title'>" +
						"<div class='select-title__arrow ion-ios-arrow-down'></div>" +
						"<div class='select-title__value value_" + $(this).find('option[selected="selected"]').val() + "'>" + $(this).find('option[selected="selected"]').html() + "</div>" +
						"</div>" +
						soptions +
						"</div>");
				}
				if ($(this).find('option[selected="selected"]').val() != '') {
					sblock.find('.select').addClass('focus');
				}

				if (sblock.find('.select-options__value').length == 1) {
					sblock.find('.select').addClass('one');
				}

				if ($(this).attr('data-req') == 'on') {
					$(this).addClass('req');
				}
				$(".select_" + ind + " .select-options-scroll").niceScroll('.select-options-list', selectscrolloptions());
			});
		}
		select();

		$('body').on('keyup', 'input.select-title__value', function () {
			$('.select').not($(this).parents('.select')).removeClass('active').find('.select-options').slideUp(300);
			$(this).parents('.select').addClass('active');
			$(this).parents('.select').find('.select-options').slideDown(300, function () {
				$(this).find(".select-options-scroll").getNiceScroll().resize();
			});
			$(this).parents('.select-block').find('select').val('');
		});
		$('body').on('click', '.select', function () {
			if (!$(this).hasClass('disabled') && !$(this).hasClass('one')) {
				$('.select').not(this).removeClass('active').find('.select-options').slideUp(300);
				$(this).toggleClass('active');
				$(this).find('.select-options').slideToggle(300, function () {
					$(this).find(".select-options-scroll").getNiceScroll().resize();
				});

				//	var input=$(this).parent().find('select');
				//removeError(input);

				if ($(this).attr('data-type') == 'search') {
					if (!$(this).hasClass('active')) {
						searchselectreset();
					}
					$(this).find('.select-options__value').show();
				}


				var cl = $.trim($(this).find('.select-title__value').attr('class').replace('select-title__value', ''));
				$(this).find('.select-options__value').show().removeClass('hide').removeClass('last');
				if (cl != '') {
					$(this).find('.select-options__value.' + cl).hide().addClass('hide');
				}
				if ($(this).find('.select-options__value').last().hasClass('hide')) {
					$(this).find('.select-options__value').last().prev().addClass('last');
				}
			}
		});
		$('body').on('click', '.select-options__value', function () {
			if ($(this).parents('.select').hasClass('multiple')) {
				if ($(this).hasClass('active')) {
					if ($(this).parents('.select').find('.select-title__value span').length > 0) {
						$(this).parents('.select').find('.select-title__value').append('<span data-value="' + $(this).data('value') + '">, ' + $(this).html() + '</span>');
					} else {
						$(this).parents('.select').find('.select-title__value').data('label', $(this).parents('.select').find('.select-title__value').html());
						$(this).parents('.select').find('.select-title__value').html('<span data-value="' + $(this).data('value') + '">' + $(this).html() + '</span>');
					}
					$(this).parents('.select-block').find('select').find('option').eq($(this).index() + 1).prop('selected', true);
					$(this).parents('.select').addClass('focus');
				} else {
					$(this).parents('.select').find('.select-title__value').find('span[data-value="' + $(this).data('value') + '"]').remove();
					if ($(this).parents('.select').find('.select-title__value span').length == 0) {
						$(this).parents('.select').find('.select-title__value').html($(this).parents('.select').find('.select-title__value').data('label'));
						$(this).parents('.select').removeClass('focus');
					}
					$(this).parents('.select-block').find('select').find('option').eq($(this).index() + 1).prop('selected', false);
				}
				return false;
			}


			if ($(this).parents('.select').attr('data-type') == 'search') {
				$(this).parents('.select').find('.select-title__value').val($(this).html());
				$(this).parents('.select').find('.select-title__value').attr('data-value', $(this).html());
			} else {
				$(this).parents('.select').find('.select-title__value').attr('class', 'select-title__value value_' + $(this).data('value'));
				$(this).parents('.select').find('.select-title__value').html($(this).html());

			}

			$(this).parents('.select-block').find('select').find('option').removeAttr("selected");
			if ($.trim($(this).data('value')) != '') {
				$(this).parents('.select-block').find('select').val($(this).data('value'));
				$(this).parents('.select-block').find('select').find('option[value="' + $(this).data('value') + '"]').attr('selected', 'selected');
			} else {
				$(this).parents('.select-block').find('select').val($(this).html());
				$(this).parents('.select-block').find('select').find('option[value="' + $(this).html() + '"]').attr('selected', 'selected');
			}


			if ($(this).parents('.select-block').find('select').val() != '') {
				$(this).parents('.select-block').find('.select').addClass('focus');
			} else {
				$(this).parents('.select-block').find('.select').removeClass('focus');

				$(this).parents('.select-block').find('.select').removeClass('err');
				$(this).parents('.select-block').parent().removeClass('err');
				$(this).parents('.select-block').removeClass('err').find('.form__error').remove();
			}
			if (!$(this).parents('.select').data('tags') != "") {
				if ($(this).parents('.form-tags').find('.form-tags__item[data-value="' + $(this).data('value') + '"]').length == 0) {
					$(this).parents('.form-tags').find('.form-tags-items').append('<a data-value="' + $(this).data('value') + '" href="" class="form-tags__item">' + $(this).html() + '<span class="fa fa-times"></span></a>');
				}
			}
			$(this).parents('.select-block').find('select').change();

			if ($(this).parents('.select-block').find('select').data('update') == 'on') {
				select();
			}
		});
		$(document).on('click touchstart', function (e) {
			if (!$(e.target).is(".select *") && !$(e.target).is(".select")) {
				$('.select').removeClass('active');
				$('.select-options').slideUp(300, function () {});
				searchselectreset();
			};
		});
		$(document).on('keydown', function (e) {
			if (e.which == 27) {
				$('.select').removeClass('active');
				$('.select-options').slideUp(300, function () {});
				searchselectreset();
			}
		});
	}
	//SELECT
}
forms();

function searchselectreset() {
	$.each($('.select[data-type="search"]'), function (index, val) {
		var block = $(this).parent();
		var select = $(this).parent().find('select');
		if ($(this).find('.select-options__value:visible').length == 1) {
			$(this).addClass('focus');
			$(this).parents('.select-block').find('select').val($('.select-options__value:visible').data('value'));
			$(this).find('.select-title__value').val($('.select-options__value:visible').html());
			$(this).find('.select-title__value').attr('data-value', $('.select-options__value:visible').html());
		} else if (select.val() == '') {
			$(this).removeClass('focus');
			block.find('input.select-title__value').val(select.find('option[selected="selected"]').html());
			block.find('input.select-title__value').attr('data-value', select.find('option[selected="selected"]').html());
		}
	});
};


