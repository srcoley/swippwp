var emailAddress;
var userToken;
jQuery(document).ready(function($) {

	$("#swipp_sign_up").on('click', function(e){
		emailAddress = ($('#swipp_user_email').val().length > 0) ? $('#swipp_user_email').val() : false;
		userToken = ($('#swipp_user_token').val().length > 0) ? $('#swipp_user_token').val() : false;
		if(emailAddress !== false || userToken !== false) {
			var signUpPayload = {
				action: 'swipp_sign_up',
				accountType: 1,
				emailAddress: emailAddress,
				accountToken: userToken
			};
			swippSignUp(signUpPayload);
		} else {
			alert("You must provide an email address and a user token.");
		}
		e.preventDefault();
	});

});

function swippSignUp(payload){
	jQuery.ajax({
		url:	ajaxurl,
		type: 'POST',
		data:	payload,

		success: function(data){
			console.log('Response:');
			console.log(data);
			var signInPayload = {
				action: 'swipp_sign_in',
				accountType: 1,
				emailAddress: emailAddress,
				accountToken: userToken
			};
			swippSignIn(signInPayload);
		},
		error: function(xhr, ts, et) {
			console.log('Error:');
			console.log(xhr);
			console.log(ts);
			console.log(et);
		}
	});
}

function swippSignIn(payload){
	console.log(payload);
	jQuery.ajax({
		url:	ajaxurl,
		type: 'POST',
		data:	payload,

		success: function(data){
			console.log('Response:');
			data = jQuery.parseJSON(data);
			console.log(data);
			jQuery("#swipp_sign_up").addClass('hidden');
			jQuery("#swipp_account_token, #swipp_account_token_hidden")
				.val(data.response.signInOutput.accessToken)
				.removeClass('hidden');
		},
		error: function(xhr, ts, et) {
			console.log('Error:');
			console.log(xhr);
			console.log(ts);
			console.log(et);
		}
	});
}
