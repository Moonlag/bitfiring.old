
const playerLimitUrl = "/admin/players/"+playerId+"/get_limits";
	
function playerContentLoader() {
	window['TWILL'].vm.reloadDatas();
}

function trigger_info(type, message) {
	
	if(type == 1) {
		
		$("#message_bringer").removeClass("notif--warning").addClass("notif--success");
		$("#message_bringer #message_text").text(message);
		$(".notif").fadeIn('slow').delay(1000).fadeOut('slow');	
		
	} else if(type == 2) {
		
		var error_list = $('#message_bringer #message_text');
		error_list.html("");
		$.each(message, function(i) {
			var li = $('<div/>').appendTo(error_list);
			var error_element = $('<span/>')
				.text(message[i])
				.appendTo(li);
		});			
		$("#message_bringer").removeClass("notif--success").addClass("notif--warning");
		$(".notif").fadeIn('slow').delay(1000).fadeOut('slow');		
		
		
	}
}

$(document).on("click", "#comment_player_form button", function(e) {
	
	$("#player_comment_add_form").submit();

});

$(document).on("submit", "#player_comment_add_form", function(e) {
	
	e.preventDefault();	
	var form = $(e.target);
	var form_s = form.serialize();

	$.post(form.attr("action"), form_s, function(response) {
		
		if (response.success) {						
			trigger_info(1, response.success_message);			
		}
		
		if (response.errors) {
			trigger_info(2, response.errors);						
		}			

	});	

});

$(document).on("submit", ".player_balance_correction_form", function(e) {
	
	e.preventDefault();	
	var form = $(e.target);
	var form_s = form.serialize();

	$.post(form.attr("action"), form_s, function(response) {
		
		if (response.success) {						
			trigger_info(1, response.success_message);			
		}
		
		if (response.errors) {
			trigger_info(2, response.errors);						
		}			

	});	

});



$(document).on("click", ".submit_parent button", function(e) {
	
	$(this).parents('form:first').submit();

});

$(document).on("submit", ".player_limit_add_form", function(e) {
	
	e.preventDefault();	
	var form = $(e.target);
	var form_s = form.serialize();

	$.post(form.attr("action"), form_s, function(response) {

		if (response.success) {
			trigger_info(1, response.success_message);
			playerContentLoader();			
		}
		
		if (response.errors) {
			trigger_info(2, response.errors);
		}			

	});	

});
