var tempStatus = 1;
var currentCount = 28;
var stepCount = 28;
var HOST = "http://house";

function getLength(obj) {
    return Object.keys(obj).length;
}

function insertFeed(feed, addContainer, appendFeed) {

	var divContainer = $("<div>", {"class": "games-grid mb-16"});

	$.each(feed, function(i, v) {

		var divGameContainer = $("<div>", {"class": "games-grid__item game-preview game-preview_left game_call", "id": "game_"+v.id, "category": v.cat_name, "slug": v.uri});
		var divGameImgContainer = $("<div>", {"class": "game-preview__img"});
		var divGameRatioContainer = $("<div>", {"class": "ratio__box"});
		var divGamePictureContainer = $("<picture>");

		var divGameImgBlock = $("<source/>", {"srcset": "/public/"+v.img, "type": "image/webp"});
		var divGameImgBlockTwo = $("<img/>", {"src": "/public/"+v.img, "alt": "img"});

		divGamePictureContainer.append(divGameImgBlock).append(divGameImgBlockTwo);
		divGameRatioContainer.append(divGamePictureContainer);
		divGameImgContainer.append(divGameRatioContainer);
		divGameContainer.append(divGameImgContainer);
		divContainer.append(divGameContainer);

	});

	if(appendFeed == 1) {
		$("#" + addContainer).append(divContainer);
		return;
	}
	$("#" + addContainer).html(divContainer);

}

function openModal(popupId) {
	$.fancybox.close();
	$.fancybox.open({ src: "#"+popupId, type : 'inline' });
}

$(function() {

	$('body').on('submit', '#reg_first_stepDEPRECATED', function(e) {
		var reg_first_status = 1;

		$("#reg_first_step input").each(function() {

			if ($(this).attr("class") !== "valid") {
				e.preventDefault();
				reg_first_status = 0;
				return false;
			}

		});

		var form = $(e.target);

		if(reg_first_status == 1) {
			call_popup("registration_2");

			$.each($(form).serializeArray(), function(_, field) {
				var transfer = $(this).attr('name')+"_t";
				$("#"+transfer).val(field.value);
			});


			console.log("valid");
		} else if(reg_first_status == 0) {
			console.log("not valid");
		}
		e.preventDefault();

    });

	$(document).on("submit", "#reg_first_step", function(e) {

		e.preventDefault();

		$("#reg_first_step .btn_profile").text("Please wait");

		var form = $(e.target);
		$.post(form.attr("action"), form.serialize(), function(response) {

			$("#reg_first_step .btn_profile").text("Next step");

			$(".error_container").html("");

			if (response.success) {

				$.each($(form).serializeArray(), function(_, field) {
					var transfer = $(this).attr('name')+"_t";
					$("#"+transfer).val(field.value);
				});

				openModal('registration_2');

			}

			if (response.errors) {
				var error_list = $('#registration_1 .error_container');
				$.each(response.errors, function(i) {
					var li = $('<li/>')
						.appendTo(error_list);
					var error_element = $('<span/>')
						.text(response.errors[i])
						.appendTo(li);
				});

				$.each(response.error_keys, function(i) {
					$("#"+response.error_keys[i]).removeClass("valid").addClass("error");
				});

			}

		});

	});

	$(document).on("submit", "#create_account_final", function(e) {

		e.preventDefault();

		var form = $(e.target);
		form_s = form.serialize();

		$("#create_account_final .btn_profile").text("Please wait");

		$.post(form.attr("action"), form_s, function(response) {

			$(".error_container").html("");

			if (response.success) {

				//closeModalById("popup-create-account");
				//closeModalById("popup-create-account-final");
				location.reload();

			}

			if (response.redirect) window.location.href = HOST + response.redirect;

			if (response.errors) {

				$("#create_account_final .btn_profile").text("Registration");

				var error_list = $('#registration_2 .error_container');
				$.each(response.errors, function(i) {
					var li = $('<li/>')
						.appendTo(error_list);

					var error_element = $('<span/>')
						.text(response.errors[i])
						.appendTo(li);
				});

				$.each(response.error_keys, function(i) {
					$("#"+response.error_keys[i]).removeClass("valid").addClass("error");
				});
			}

		});

	});


	$(document).on("submit", "#login_form", function(e) {
        console.log("start");
		e.preventDefault();

		$("#login_form .btn_profile").text("Please wait");

		var form = $(e.target);
		$.post(form.attr("action"), form.serialize(), function(response) {

			$("#login .error_container").html("");
            console.log("end");
			if (response.redirect) {
				console.log("here22");
                console.log(response.redirect);
				window.location.href = HOST + response.redirect;

			} else if (response.errors) {

				$("#login_form .btn_profile").text("Sign in");

				var error_list = $('#login .error_container');
				$.each(response.errors, function(i) {
					var li = $('<li/>')
						.appendTo(error_list);
					var error_element = $('<span/>')
						.text(response.errors[i])
						.appendTo(li);
				});

				$.each(response.error_keys, function(i) {
					$("#"+response.error_keys[i]).removeClass("valid").addClass("error");
				});

				if(response.total_error) {
					$('#login_form input').each(function (e) {
						$(this).removeClass("valid").addClass("error");
					});
				}

			}

		});

		e.preventDefault();

	});

	$(document).on("submit", "#change_pass", function(e) {

		e.preventDefault();

		$("#change_pass .btn_profile").text("Please wait");

		var form = $(e.target);

		$.post(form.attr("action"), form.serialize(), function(response) {

			$("#change_pass .error_container").html("");

			if (response.redirect) {

				window.location.href = HOST + response.redirect;

			} else if (response.errors) {

				$("#change_pass .btn_profile").text("Sign in");

				var error_list = $('#change_pass .error_container');
				$.each(response.errors, function(i) {
					var li = $('<li/>')
						.appendTo(error_list);
					var error_element = $('<span/>')
						.text(response.errors[i])
						.appendTo(li);
				});

				$.each(response.error_keys, function(i) {
					$("#"+response.error_keys[i]).removeClass("valid").addClass("error");
				});

				if(response.total_error) {
					$('#change_pass input').each(function (e) {
						$(this).removeClass("valid").addClass("error");
					});
				}

			}

		});

		e.preventDefault();

	});


	$(document).on("submit", "#deposit_form", function(e) {

		e.preventDefault();

		$("#deposit_form .btn_profile").text("Please wait");

		var form = $(e.target);

		$.post(form.attr("action"), form.serialize(), function(response) {

			$("#deposit_form .error_container").html("");

			if (response.success) {

				$("#info_text").text("Deposit confirmed");
				openModal('info');

			} else if (response.errors) {

				$("#deposit_form .btn_profile").text("Deposit");

				var error_list = $('#deposit_form .error_container');
				$.each(response.errors, function(i) {
					var li = $('<li/>')
						.appendTo(error_list);
					var error_element = $('<span/>')
						.text(response.errors[i])
						.appendTo(li);
				});

				$.each(response.error_keys, function(i) {
					$("#"+response.error_keys[i]).removeClass("valid").addClass("error");
				});

				if(response.total_error) {
					$('#deposit_form input').each(function (e) {
						$(this).removeClass("valid").addClass("error");
					});
				}

			}

		});

		e.preventDefault();

	});


	$(document).on("submit", "#withdraw_form", function(e) {

		e.preventDefault();

		$("#withdraw_form .btn_profile").text("Please wait");

		var form = $(e.target);

		$.post(form.attr("action"), form.serialize(), function(response) {

			$("#withdraw_form .error_container").html("");

			if (response.success) {

				$("#info_text").text("Withdraw confirmed");
				openModal('info');

			} else if (response.errors) {

				$("#withdraw_form .btn_profile").text("Deposit");

				var error_list = $('#withdraw_form .error_container');
				$.each(response.errors, function(i) {
					var li = $('<li/>')
						.appendTo(error_list);
					var error_element = $('<span/>')
						.text(response.errors[i])
						.appendTo(li);
				});

				$.each(response.error_keys, function(i) {
					$("#"+response.error_keys[i]).removeClass("valid").addClass("error");
				});

				if(response.total_error) {
					$('#withdraw_form input').each(function (e) {
						$(this).removeClass("valid").addClass("error");
					});
				}

			}

		});

		e.preventDefault();

	});

	$('body').on('change', '#deposit_wallets, #withdraw_wallets', function() {

		var balance 	  = $(this).find(":selected").attr("balance");
		var currency_id   = $(this).find(":selected").attr("currency_id");
		var currency_code = $(this).find(":selected").attr("currency_code");

		var selectId = $(this).attr("id");
		var formId = "deposit_form";
		if (selectId == "withdraw_wallets") {
			formId = "withdraw_form";
		}

		$("."+formId+" #wallet_code").text(currency_code);
		$("."+formId+" #wallet_balance").text(balance);
		$("#"+formId+" #wallet_currency").val(currency_id);
		$("."+formId+" .currency_code").text(currency_code);
		$("#"+formId+" #wallet_id").val($(this).val());

	});

	$('body').on('click', '.slot_call .idle_col', function() {

		$(".main-nav__item_active").removeClass("home-nav__item_active").addClass("idle_col");
		$(this).addClass("home-nav__item_active");
		$("#played_call").removeClass("home-nav__item_active");

		sliceToInsert = feed.slice(0, 0 + stepCount);
		insertFeed(sliceToInsert, "game_feed");

		$("#show_more_games").show();
	});

	$('body').on('click', '#show_more_games', function() {

		sliceToInsert = feed.slice(currentCount, currentCount + stepCount);

		currentCount = currentCount + stepCount;

		insertFeed(sliceToInsert, "game_feed", 1);

		if(getLength(sliceToInsert) < stepCount) {
			$("#show_more_games").hide();
		}

	});

	$('body').on('click', '#show_search', function() {
		$("#main_search").slideToggle();
	});

	$('body').on('click', '.deposit_sum_radio', function() {

		//console.log($(this).children("input").val());
	});



	$('body').on('click', '#played_call', function() {
		$(".main-nav__item_active").removeClass("home-nav__item_active").addClass("idle_col");
		$(this).addClass("home-nav__item_active");
		$("#game_feed").html('<h4 class="center_message">Please, sign in to see your recent games</h4>');
		$("#show_more_games").hide();
	});

	$('body').on('click', '.fancybox-slide', function(e) {

	    if (e.target !== this)
			return;

	    $.fancybox.close();

	});

	$('body').on('click', '.link-aside', function(e) {

		var asideLink = $(this).attr("link");
		var link = $(this);

		$.get("/a/"+asideLink, {}, function(response) {

			if(!response.redirect) {

				$(".link-aside").removeClass("link-aside_active");
				link.addClass("link-aside_active");
				$("#profile_container_part").html(response.content).fadeIn(1000);;
				forms();
				$(".nice-select").niceSelect();
				window.history.pushState("Details", "Profile", "https://housecasino.com/"+asideLink);

			} else {

				window.location.href = HOST + "/?action=logout";

			}

		}, "json");

	});

	$('body').on('click', '.popup__close', function(e) {

	    $.fancybox.close();

	});


	$('body').on('click', '#responsible_gaming_mode', function(e) {

		$(".profile-block").hide();

		if($(this).prop("checked") == true){
			$("#big_responsible").fadeIn("fast");
		}
		else if($(this).prop("checked") == false){
			$("#small_responsible").fadeIn("fast");
		}
	});

	$('body').on('click', '.game_call', function(e) {

		var cat = $(this).attr("category");
		var slug = $(this).attr("slug");
		var token = $("#global_token").attr("token");

		$.post("/a/get_game", {cat:cat, slug:slug, _token:token}, function(response) {

			if(!response.errors) {

				$("#game_mask #game").attr("src",response.game.game_url);
				$("#game_mask").show();
				$("#main_mask").hide();

			}

		}, "json");

		//var remoteFrame =

	});



	$('body').on('click', '#close_slot', function(e) {

		$("#game").attr("src","");
		$("#game_mask").hide();
		$("#main_mask").show();

	});

	$('body').on('click', '#i_saw_cookie', function(e) {

		$cookies.set('i_saw_cookie', 1);
		$(".coockies").fadeOut();

	});

	$('body').on('click', '#fullscreen', function(e) {

		if($.fullscreen.isFullScreen() === true) {
			$.fullscreen.exit();
		} else {
			$('#main_mask').fullscreen();
		}

	});

	$('body').on('click', '#deposit_options .bank-cards__item', function(e) {

		if($(this).hasClass("bank-card_disabled")) return;

		$("#deposit_options .bank-cards__item").removeClass("bank-card_active");
		$(this).addClass("bank-card_active");

		var choosedOption = $(this).attr("payment_id");
		$("#deposit_option").attr("value", choosedOption);

	});

	$('body').on('click', '#withdraw_options .bank-cards__item', function(e) {

		if($(this).hasClass("bank-card_disabled")) return;

		$("#withdraw_options .bank-cards__item").removeClass("bank-card_active");
		$(this).addClass("bank-card_active");

	});




});


$(".profile-aside__burger").click((function(t) {
    $(this).toggleClass("aside-toggler__toggle"), $(".profile-aside").toggleClass("aside-open")
}));

$(".slot__search-switch").click((function(t) {
    $(".slot__search").toggleClass("slot__search-toggle"), $(this).toggleClass("slot__search-switch-hide"), t.stopPropagation()
}));

$(".slot__search_inner").click((function(t) {
    t.stopPropagation()
}));

$(".search-slot__close").click((function(t) {
    $(".slot__search").toggleClass("slot__search-toggle"), setTimeout((function() {
        $(".slot__search-switch").toggleClass("slot__search-switch-hide")
    }), 500)
}));

$(document).click((function() {
    $(".slot__search-switch").removeClass("slot__search-switch-hide"), $(".slot__search").removeClass("slot__search-toggle")
}));

$(".dropdown__toggle").click((function(t) {
    $(this).find(".dropdown__content").fadeToggle("fast", "swing"), $(this).toggleClass("dropdown__active"), t.stopPropagation()
}));

$(".dropdown__content").click((function(t) {
    t.stopPropagation()
}));

$(document).click((function() {
    $(".dropdown__content").fadeOut("fast", "swing"), $(this).removeClass("dropdown__active")
}));

$(document).ready((function() {
    $(".nice-select").niceSelect()
}));

$(document).ready((function() {
    $(".more").hide(), $(".accordeon__head").click((function() {
        $(this).parent().siblings().not($(this)).find(".accordeon__body").slideUp(300), $(this).parent().find(".accordeon__body").slideToggle(300), $(this).parent().siblings().not($(this)).find(".accordeon__angle").removeClass("accordeon__toggle"), $(this).parent().find(".accordeon__angle").toggleClass("accordeon__toggle")
    }))
}));


$("document").ready((function() {
    $(".phone_inp").mask("+7 (999) 999-99-99", {
        autoclear: !1
    })
}));

$("[data-fancybox]").fancybox({
    closeExisting: true,
    autoFocus: !1,
    modal: !0
});

$(document).keyup((function(t) {
    27 == t.keyCode && $.fancybox.close()
}));

$(".popup__close-btn").click((function() {
    parent.$.fancybox.close()
}));

$(".popup__close").click((function() {
    parent.$.fancybox.close()
}));

$("input").keyup((function(t) {
    27 == t.keyCode && $.fancybox.close()
}));

$(document).ready((function() {
    $("form").each((function() {
        $(this).validate({})
    }))
}));

var gotop = $(".gotop");

function testWebP(t) {
    var e = new Image;
    e.src = "data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA", e.onload = e.onerror = function() {
        t(2 == e.height)
    }
}

function scroll() {
    var t = $(window).scrollTop();
    $(".navbar").toggleClass("navbar__scroll", t > 0), $(".gotop__wrap").toggleClass("gotop_toggle", t > 0), $(".scoreboard_navbar").toggleClass("scoreboard_navbar-toggle", t > 0)
}

function forms() {

	$(".tab__body").not(":first-child").hide(), $(".tab__head").click((function() {
		$(this).siblings().removeClass("tab__active").removeAttr("disabled"), $(this).addClass("tab__active").attr("disabled", "disabled"), $(this).closest(".tabs").find(".tab__body").hide().eq($(this).index()).fadeIn()
	})).eq(0).addClass("tab__active").attr("disabled", "disabled");

    if ($("select").length > 0) {
        function t() {
            $.each($("select"), (function(t, e) {
                var s = t;
                $(this).hide(), 0 == $(this).parent(".select-block").length ? $(this).wrap("<div class='select-block " + $(this).attr("class") + "-select-block'></div>") : $(this).parent(".select-block").find(".select").remove();
                var i = "",
                    a = "",
                    l = $(this).parent(".select-block"),
                    n = "<div class='select-options'><div class='select-options-scroll'><div class='select-options-list'>";
                "multiple" == $(this).attr("multiple") && (i = "multiple", a = "check"), $.each($(this).find("option"), (function(t, e) {
                    if ("" != $(this).attr("class") && null != $(this).attr("class")) {
                        $(this).attr("class")
                    }
                    "" != $(this).attr("value") ? n = "" != $(this).attr("data-icon") && null != $(this).attr("data-icon") ? n + "<div data-value='" + $(this).attr("value") + "' class='select-options__value_" + s + " select-options__value value_" + $(this).val() + "  " + a + "'><div><img src=" + $(this).attr("data-icon") + ' alt=""></div><div>' + $(this).html() + "</div></div>" : n + "<div data-value='" + $(this).attr("value") + "' class='select-options__value_" + s + " select-options__value value_" + $(this).val() + "  " + a + "'>" + $(this).html() + "</div>" : "on" == $(this).parent().attr("data-label") && 0 == l.find(".select__label").length && l.prepend('<div class="select__label">' + $(this).html() + "</div>")
                })), n += "</div></div></div>", "search" == $(this).attr("data-type") ? (l.append("<div data-type='search' class='select_" + s + " select " + $(this).attr("class") + "__select " + i + "'><div class='select-title'><div class='select-title__arrow ion-ios-arrow-down'></div><input data-value='" + $(this).find('option[selected="selected"]').html() + "' class='select-title__value value_" + $(this).find('option[selected="selected"]').val() + "' /></div>" + n + "</div>"), $(".select_" + s).find("input.select-title__value").jcOnPageFilter({
                    parentSectionClass: "select-options_" + s,
                    parentLookupClass: "select-options__value_" + s,
                    childBlockClass: "select-options__value_" + s
                })) : "true" == $(this).attr("data-icon") ? l.append("<div class='select_" + s + " select " + $(this).attr("class") + "__select icon " + i + "'><div class='select-title'><div class='select-title__arrow ion-ios-arrow-down'></div><div class='select-title__value value_" + $(this).find('option[selected="selected"]').val() + "'><div><img src=" + $(this).find('option[selected="selected"]').attr("data-icon") + ' alt=""></div><div>' + $(this).find('option[selected="selected"]').html() + "</div></div></div>" + n + "</div>") : l.append("<div class='select_" + s + " select " + $(this).attr("class") + "__select " + i + "'><div class='select-title'><div class='select-title__arrow ion-ios-arrow-down'></div><div class='select-title__value value_" + $(this).find('option[selected="selected"]').val() + "'>" + $(this).find('option[selected="selected"]').html() + "</div></div>" + n + "</div>"), "" != $(this).find('option[selected="selected"]').val() && l.find(".select").addClass("focus"), 1 == l.find(".select-options__value").length && l.find(".select").addClass("one"), "on" == $(this).attr("data-req") && $(this).addClass("req"), $(".select_" + s + " .select-options-scroll").niceScroll(".select-options-list", {
                    cursorcolor: "#ff6600",
                    cursorwidth: "4px",
                    background: "",
                    autohidemode: !1,
                    bouncescroll: !1,
                    cursorborderradius: "10px",
                    scrollspeed: 100,
                    mousescrollstep: 50,
                    directionlockdeadzone: 0,
                    cursorborder: "0px solid #c5c5c5"
                })
            }))
        }
        t(), $("body").on("keyup", "input.select-title__value", (function() {
            $(".select").not($(this).parents(".select")).removeClass("active").find(".select-options").slideUp(300), $(this).parents(".select").addClass("active"), $(this).parents(".select").find(".select-options").slideDown(300, (function() {
                $(this).find(".select-options-scroll").getNiceScroll().resize()
            })), $(this).parents(".select-block").find("select").val("")
        })), $("body").on("click", ".select", (function() {
            if (!$(this).hasClass("disabled") && !$(this).hasClass("one")) {
                $(".select").not(this).removeClass("active").find(".select-options").slideUp(300), $(this).toggleClass("active"), $(this).find(".select-options").slideToggle(300, (function() {
                    $(this).find(".select-options-scroll").getNiceScroll().resize()
                })), "search" == $(this).attr("data-type") && ($(this).hasClass("active") || searchselectreset(), $(this).find(".select-options__value").show());
                var t = $.trim($(this).find(".select-title__value").attr("class").replace("select-title__value", ""));
                $(this).find(".select-options__value").show().removeClass("hide").removeClass("last"), "" != t && $(this).find(".select-options__value." + t).hide().addClass("hide"), $(this).find(".select-options__value").last().hasClass("hide") && $(this).find(".select-options__value").last().prev().addClass("last")
            }
        })), $("body").on("click", ".select-options__value", (function() {
            if ($(this).parents(".select").hasClass("multiple")) return $(this).hasClass("active") ? ($(this).parents(".select").find(".select-title__value span").length > 0 ? $(this).parents(".select").find(".select-title__value").append('<span data-value="' + $(this).data("value") + '">, ' + $(this).html() + "</span>") : ($(this).parents(".select").find(".select-title__value").data("label", $(this).parents(".select").find(".select-title__value").html()), $(this).parents(".select").find(".select-title__value").html('<span data-value="' + $(this).data("value") + '">' + $(this).html() + "</span>")), $(this).parents(".select-block").find("select").find("option").eq($(this).index() + 1).prop("selected", !0), $(this).parents(".select").addClass("focus")) : ($(this).parents(".select").find(".select-title__value").find('span[data-value="' + $(this).data("value") + '"]').remove(), 0 == $(this).parents(".select").find(".select-title__value span").length && ($(this).parents(".select").find(".select-title__value").html($(this).parents(".select").find(".select-title__value").data("label")), $(this).parents(".select").removeClass("focus")), $(this).parents(".select-block").find("select").find("option").eq($(this).index() + 1).prop("selected", !1)), !1;
            "search" == $(this).parents(".select").attr("data-type") ? ($(this).parents(".select").find(".select-title__value").val($(this).html()), $(this).parents(".select").find(".select-title__value").attr("data-value", $(this).html())) : ($(this).parents(".select").find(".select-title__value").attr("class", "select-title__value value_" + $(this).data("value")), $(this).parents(".select").find(".select-title__value").html($(this).html())), $(this).parents(".select-block").find("select").find("option").removeAttr("selected"), "" != $.trim($(this).data("value")) ? ($(this).parents(".select-block").find("select").val($(this).data("value")), $(this).parents(".select-block").find("select").find('option[value="' + $(this).data("value") + '"]').attr("selected", "selected")) : ($(this).parents(".select-block").find("select").val($(this).html()), $(this).parents(".select-block").find("select").find('option[value="' + $(this).html() + '"]').attr("selected", "selected")), "" != $(this).parents(".select-block").find("select").val() ? $(this).parents(".select-block").find(".select").addClass("focus") : ($(this).parents(".select-block").find(".select").removeClass("focus"), $(this).parents(".select-block").find(".select").removeClass("err"), $(this).parents(".select-block").parent().removeClass("err"), $(this).parents(".select-block").removeClass("err").find(".form__error").remove()), "" != !$(this).parents(".select").data("tags") && 0 == $(this).parents(".form-tags").find('.form-tags__item[data-value="' + $(this).data("value") + '"]').length && $(this).parents(".form-tags").find(".form-tags-items").append('<a data-value="' + $(this).data("value") + '" href="" class="form-tags__item">' + $(this).html() + '<span class="fa fa-times"></span></a>'), $(this).parents(".select-block").find("select").change(), "on" == $(this).parents(".select-block").find("select").data("update") && t()
        })), $(document).on("click touchstart", (function(t) {
            $(t.target).is(".select *") || $(t.target).is(".select") || ($(".select").removeClass("active"), $(".select-options").slideUp(300, (function() {})), searchselectreset())
        })), $(document).on("keydown", (function(t) {
            27 == t.which && ($(".select").removeClass("active"), $(".select-options").slideUp(300, (function() {})), searchselectreset())
        }))
    }
}

function searchselectreset() {
    $.each($('.select[data-type="search"]'), (function(t, e) {
        var s = $(this).parent(),
            i = $(this).parent().find("select");
        1 == $(this).find(".select-options__value:visible").length ? ($(this).addClass("focus"), $(this).parents(".select-block").find("select").val($(".select-options__value:visible").data("value")), $(this).find(".select-title__value").val($(".select-options__value:visible").html()), $(this).find(".select-title__value").attr("data-value", $(".select-options__value:visible").html())) : "" == i.val() && ($(this).removeClass("focus"), s.find("input.select-title__value").val(i.find('option[selected="selected"]').html()), s.find("input.select-title__value").attr("data-value", i.find('option[selected="selected"]').html()))
    }))
}

gotop.on("click", (
	function(t) {
        t.preventDefault(), $(window).scrollTop(0)
    })), testWebP((function(t) {
        $(".ibg").each((function() {
            t && $(this).find(".ibg__img").siblings("source") && null != $(this).find(".ibg__img").siblings("source").attr("srcset") ? ($(this).css("background-image", "url(" + $(this).find("source").attr("srcset") + ")"), $(this).find(".ibg__img").first().parent("picture").remove()) : $(this).find(".ibg__img") && null != $(this).find(".ibg__img").attr("src") && ($(this).css("background-image", "url(" + $(this).find(".ibg__img").attr("src") + ")"), $(this).find(".ibg__img").first().remove())
        }))
    })), $(document).ready((function() {
        setTimeout((function() {
            $(".lazy").each((function() {
                $(this).siblings("source").attr("srcset", $(this).attr("data-src-alt")), $(this).attr("src", $(this).attr("data-src")), $(this).on("load", (function() {
                    $(this).addClass("lazyloaded")
                }))
            }))
        }), 2e3)
    })), $(document).ready((function() {
        $(".navbar__open").click((function() {
            $(".navbar__box").toggleClass("nav__toggle"), $(this).toggleClass("aside-toggler__toggle"), $("body").toggleClass("body-toggle")
        })), $(document).on("keydown", (function(t) {
            27 === t.keyCode && ($(".navbar__box").removeClass("nav__toggle"), $("body").css("overflow", "auto"))
        }))
    })), $(document).ready((function() {
        scroll()
    })), $(window).on("scroll", (function() {
        scroll()
    })), $(document).ready((function() {
        $(".popular-slider").slick({
            pauseOnHover: !1,
            infinite: !0,
            slidesToShow: 3,
            slidesToScroll: 1,
            autoplay: !0,
            arrows: !1,
            dots: !1,
            appendDots: $(".popular-slider__dots"),
            zIndex: 1,
            responsive: [{
                breakpoint: 767,
                settings: {
                    slidesToShow: 1,
                    dots: !0
                }
            }]
        })
    })), $(document).ready((function() {
        $(".slider").slick({
            pauseOnHover: !1,
            infinite: !0,
            slidesToShow: 4,
            focusOnSelect: !0,
            slidesToScroll: 1,
            autoplay: !0,
            arrows: !0,
            prevArrow: '<button class="left_arrow"><img src="img/common/arrow_left.svg" alt="img"></button>',
            nextArrow: '<button class="right_arrow"><img src="img/common/arrow_right.svg" alt="img"></button>',
            dots: !1,
            swipeToSlide: !0,
            zIndex: 1,
            responsive: [{
                breakpoint: 767,
                settings: {
                    slidesToShow: 1,
                    appendArrows: $(".slider__arrow"),
                    arrows: !1
                }
            }, {
                breakpoint: 992,
                settings: {
                    slidesToShow: 2,
                    arrows: !1
                }
            }, {
                breakpoint: 1200,
                settings: {
                    slidesToShow: 3,
                    arrows: !1
                }
            }]
        })
    })), $(document).ready((function() {
        $(".scoreboard__slider").slick({
            pauseOnHover: !1,
            infinite: !0,
            slidesToShow: 1,
            speed: 5e3,
            autoplaySpeed: 0,
            autoplay: !0,
            arrows: !1,
            dots: !1,
            zIndex: 1,
            variableWidth: !0,
            cssEase: "linear"
        })
    })),
    function() {
        let t = [],
            e = document.querySelectorAll("[data-da]"),
            s = [],
            i = [];
        if (e.length > 0) {
            let c = 0;
            for (let i = 0; i < e.length; i++) {
                const a = e[i],
                    o = a.getAttribute("data-da");
                if ("" != o) {
                    const e = o.split(","),
                        i = e[1] ? e[1].trim() : "last",
                        r = e[2] ? e[2].trim() : "767",
                        d = "min" === e[3] ? e[3].trim() : "max",
                        u = document.querySelector("." + e[0].trim());
                    e.length > 0 && u && (a.setAttribute("data-da-index", c), t[c] = {
                        parent: a.parentNode,
                        index: (l = a, n = void 0, n = Array.prototype.slice.call(l.parentNode.children), n.indexOf(l))
                    }, s[c] = {
                        element: a,
                        destination: document.querySelector("." + e[0].trim()),
                        place: i,
                        breakpoint: r,
                        type: d
                    }, c++)
                }
            }(a = s).sort((function(t, e) {
                return t.breakpoint > e.breakpoint ? -1 : 1
            })), a.sort((function(t, e) {
                return t.place > e.place ? 1 : -1
            }));
            for (let t = 0; t < s.length; t++) {
                const e = s[t],
                    a = e.breakpoint,
                    l = e.type;
                i.push(window.matchMedia("(" + l + "-width: " + a + "px)")), i[t].addListener(o)
            }
        }
        var a, l, n;

        function o(t) {
            for (let t = 0; t < s.length; t++) {
                const e = s[t],
                    a = e.element,
                    l = e.destination,
                    n = e.place,
                    o = "_dynamic_adapt_" + e.breakpoint;
                if (i[t].matches) {
                    if (!a.classList.contains(o)) {
                        let t = r(l)[n];
                        "first" === n ? t = r(l)[0] : "last" === n && (t = r(l)[r(l).length]), l.insertBefore(a, l.children[t]), a.classList.add(o)
                    }
                } else a.classList.contains(o) && (c(a), a.classList.remove(o))
            }
        }

        function c(e) {
            const s = e.getAttribute("data-da-index"),
                i = t[s],
                a = i.parent,
                l = i.index,
                n = r(a, !0)[l];
            a.insertBefore(e, a.children[n])
        }

        function r(t, e) {
            const s = t.children,
                i = [];
            for (let t = 0; t < s.length; t++) {
                const a = s[t];
                (e || null == a.getAttribute("data-da")) && i.push(t)
            }
            return i
        }
        o()
    }(),
    function(t) {
        t.fn.niceSelect = function(e) {
            if ("string" == typeof e) return "update" == e ? this.each((function() {
                var e = t(this),
                    i = t(this).next(".nice-select"),
                    a = i.hasClass("open");
                i.length && (i.remove(), s(e), a && e.next().trigger("click"))
            })) : "destroy" == e ? (this.each((function() {
                var e = t(this),
                    s = t(this).next(".nice-select");
                s.length && (s.remove(), e.css("display", ""))
            })), 0 == t(".nice-select").length && t(document).off(".nice_select")) : console.log('Method "' + e + '" does not exist.'), this;

            function s(e) {

                e.after(t("<div></div>").addClass("nice-select").addClass(e.attr("class") || "").addClass(e.attr("disabled") ? "disabled" : "").attr("tabindex", e.attr("disabled") ? null : "0").html('<span class="current"></span><ul class="list"></ul>'));
                var s = e.next(),
                    i = e.find("option"),
                    a = e.find("option:selected");
                s.find(".current").html(a.data("display") || "<img src='" + a.attr("data-image") + '\' alt="img">' + a.text()), i.each((function(e) {
                    var i = t(this),
                        a = i.data("display");
                    s.find("ul").append(t("<li></li>").attr("data-value", i.val()).attr("data-image", i.attr("data-image")).attr("data-display", a || null).addClass("option" + (i.is(":selected") ? " selected" : "") + (i.is(":disabled") ? " disabled" : "")).html("<img src='" + i.attr("data-image") + '\' alt="img">' + i.text()))
                }))
            }
            this.hide(), this.each((function() {
                var e = t(this);
                e.next().hasClass("nice-select") || s(e)
            })), t(document).off(".nice_select"), t(document).on("click.nice_select", ".nice-select", (function(e) {
                var s = t(this);
                t(".nice-select").not(s).removeClass("open"), s.toggleClass("open"), s.hasClass("open") ? (s.find(".option"), s.find(".focus").removeClass("focus"), s.find(".selected").addClass("focus")) : s.focus()
            })), t(document).on("click.nice_select", (function(e) {


                0 === t(e.target).closest(".nice-select").length && t(".nice-select").removeClass("open").find(".option")
            })), t(document).on("click.nice_select", ".nice-select .option:not(.disabled)", (function(e) {


                var s = t(this),
                    i = s.closest(".nice-select");
					d = $(this).parents(".nice-select").prev().children('select');

				$(d).children('option').removeAttr("selected");
				$(d).children('option[value="'+s.data("value")+'"]').attr("selected","selected");
				$(d).change();

                i.find(".selected").removeClass("selected"), s.addClass("selected");
                var a = s.data("display") || "<img src='" + s.attr("data-image") + '\' alt="img">' + s.text();
                i.find(".current").html(a), i.prev("select").val(s.data("value")).trigger("change")
            })), t(document).on("keydown.nice_select", ".nice-select", (function(e) {

                var s = t(this),
                    i = t(s.find(".focus") || s.find(".list .option.selected"));
                if (32 == e.keyCode || 13 == e.keyCode) return s.hasClass("open") ? i.trigger("click") : s.trigger("click"), !1;
                if (40 == e.keyCode) {
                    if (s.hasClass("open")) {
                        var a = i.nextAll(".option:not(.disabled)").first();
                        a.length > 0 && (s.find(".focus").removeClass("focus"), a.addClass("focus"))
                    } else s.trigger("click");
                    return !1
                }
                if (38 == e.keyCode) {
                    if (s.hasClass("open")) {
                        var l = i.prevAll(".option:not(.disabled)").first();
                        l.length > 0 && (s.find(".focus").removeClass("focus"), l.addClass("focus"))
                    } else s.trigger("click");
                    return !1
                }
                if (27 == e.keyCode) s.hasClass("open") && s.trigger("click");
                else if (9 == e.keyCode && s.hasClass("open")) return !1
            }));
            var i = document.createElement("a").style;
            return i.cssText = "pointer-events:auto", "auto" !== i.pointerEvents && t("html").addClass("no-csspointerevents"), this
        }
    }(jQuery),
    function(t) {
        "use strict";
        var e = e || {},
            s = document.querySelectorAll.bind(document);

        function i(t) {
            var e = "";
            for (var s in t) t.hasOwnProperty(s) && (e += s + ":" + t[s] + ";");
            return e
        }
        var a = {
                duration: 500,
                show: function(t, e) {
                    if (2 === t.button) return !1;
                    var s = e || this,
                        l = document.createElement("div");
                    l.className = "waves-ripple", s.appendChild(l);
                    var n, o, c, r, d, u = (r = {
                            top: 0,
                            left: 0
                        }, d = (n = s) && n.ownerDocument, o = d.documentElement, void 0 !== n.getBoundingClientRect && (r = n.getBoundingClientRect()), c = function(t) {
                            return null !== (e = t) && e === e.window ? t : 9 === t.nodeType && t.defaultView;
                            var e
                        }(d), {
                            top: r.top + c.pageYOffset - o.clientTop,
                            left: r.left + c.pageXOffset - o.clientLeft
                        }),
                        h = t.pageY - u.top,
                        p = t.pageX - u.left,
                        f = "scale(" + s.clientWidth / 100 * 80 + ")";
                    "touches" in t && (h = t.touches[0].pageY - u.top, p = t.touches[0].pageX - u.left), l.setAttribute("data-hold", Date.now()), l.setAttribute("data-scale", f), l.setAttribute("data-x", p), l.setAttribute("data-y", h);
                    var v = {
                        top: h + "px",
                        left: p + "px"
                    };
                    l.className = l.className + " waves-notransition", l.setAttribute("style", i(v)), l.className = l.className.replace("waves-notransition", ""), v["-webkit-transform"] = f, v["-moz-transform"] = f, v["-ms-transform"] = f, v["-o-transform"] = f, v.transform = f, v.opacity = "1", v["-webkit-transition-duration"] = a.duration + "ms", v["-moz-transition-duration"] = a.duration + "ms", v["-o-transition-duration"] = a.duration + "ms", v["transition-duration"] = a.duration + "ms", v["-webkit-transition-timing-function"] = "cubic-bezier(0.250, 0.460, 0.450, 0.940)", v["-moz-transition-timing-function"] = "cubic-bezier(0.250, 0.460, 0.450, 0.940)", v["-o-transition-timing-function"] = "cubic-bezier(0.250, 0.460, 0.450, 0.940)", v["transition-timing-function"] = "cubic-bezier(0.250, 0.460, 0.450, 0.940)", l.setAttribute("style", i(v))
                },
                hide: function(t) {
                    l.touchup(t);
                    var e = this,
                        s = (e.clientWidth, null),
                        n = e.getElementsByClassName("waves-ripple");
                    if (!(n.length > 0)) return !1;
                    var o = (s = n[n.length - 1]).getAttribute("data-x"),
                        c = s.getAttribute("data-y"),
                        r = s.getAttribute("data-scale"),
                        d = 350 - (Date.now() - Number(s.getAttribute("data-hold")));
                    d < 0 && (d = 0), setTimeout((function() {
                        var t = {
                            top: c + "px",
                            left: o + "px",
                            opacity: "0",
                            "-webkit-transition-duration": a.duration + "ms",
                            "-moz-transition-duration": a.duration + "ms",
                            "-o-transition-duration": a.duration + "ms",
                            "transition-duration": a.duration + "ms",
                            "-webkit-transform": r,
                            "-moz-transform": r,
                            "-ms-transform": r,
                            "-o-transform": r,
                            transform: r
                        };
                        s.setAttribute("style", i(t)), setTimeout((function() {
                            try {
                                e.removeChild(s)
                            } catch (t) {
                                return !1
                            }
                        }), a.duration)
                    }), d)
                },
                wrapInput: function(t) {
                    for (var e = 0; e < t.length; e++) {
                        var s = t[e];
                        if ("input" === s.tagName.toLowerCase()) {
                            var i = s.parentNode;
                            if ("i" === i.tagName.toLowerCase() && -1 !== i.className.indexOf("ripple")) continue;
                            var a = document.createElement("i");
                            a.className = s.className + " waves-input-wrapper";
                            var l = s.getAttribute("style");
                            l || (l = ""), a.setAttribute("style", l), s.className = "waves-button-input", s.removeAttribute("style"), i.replaceChild(a, s), a.appendChild(s)
                        }
                    }
                }
            },
            l = {
                touches: 0,
                allowEvent: function(t) {
                    var e = !0;
                    return "touchstart" === t.type ? l.touches += 1 : "touchend" === t.type || "touchcancel" === t.type ? setTimeout((function() {
                        l.touches > 0 && (l.touches -= 1)
                    }), 500) : "mousedown" === t.type && l.touches > 0 && (e = !1), e
                },
                touchup: function(t) {
                    l.allowEvent(t)
                }
            };

        function n(e) {
            var s = function(t) {
                if (!1 === l.allowEvent(t)) return null;
                for (var e = null, s = t.target || t.srcElement; null !== s.parentElement;) {
                    if (!(s instanceof SVGElement || -1 === s.className.indexOf("ripple"))) {
                        e = s;
                        break
                    }
                    if (s.classList.contains("ripple")) {
                        e = s;
                        break
                    }
                    s = s.parentElement
                }
                return e
            }(e);
            null !== s && (a.show(e, s), "ontouchstart" in t && (s.addEventListener("touchend", a.hide, !1), s.addEventListener("touchcancel", a.hide, !1)), s.addEventListener("mouseup", a.hide, !1), s.addEventListener("mouseleave", a.hide, !1))
        }
        e.displayEffect = function(e) {
            "duration" in (e = e || {}) && (a.duration = e.duration), a.wrapInput(s(".ripple")), "ontouchstart" in t && document.body.addEventListener("touchstart", n, !1), document.body.addEventListener("mousedown", n, !1)
        }, e.attach = function(e) {
            "input" === e.tagName.toLowerCase() && (a.wrapInput([e]), e = e.parentElement), "ontouchstart" in t && e.addEventListener("touchstart", n, !1), e.addEventListener("mousedown", n, !1)
        }, t.Waves = e, document.addEventListener("DOMContentLoaded", (function() {
            e.displayEffect()
        }), !1)
    }(window), forms();
