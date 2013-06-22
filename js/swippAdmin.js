var emailAddress;
var userToken;
jQuery(document).ready(function($) {

	/**
	 * Sign Up Event Handler
	 */
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


	/**
	 * Create Widget Event Handler
	 */
	$("#swipp_create_widget").on('click', function(e){
		var swippTermInput = $('#swipp_select_term');
		if(swippTermInput.val() != '') {
			var swippTerm = swippTermInput.val();
			var postId = $('#post_ID').val();
			var orgTermPayload = {
				'action'	: 'swipp_org_term',
				'term'	: swippTerm,
				'post_id'	: postId
			}
			swippApiCall(orgTermPayload, function(data){
				data = $.parseJSON(data);
				if(data.status == 200) {
					swippCreateWidget(postId, data.response.termId);
				} else {
					alert('Failed Request');
				}
			});
		} else {
			alert('Invalid Term');
		}
		e.preventDefault();
	});


	/**
	 * Term Select Event Handler
	 */
	$("#swipp_select_term").on("keyup", function(e){
		var autosuggest = $(this);

		jQuery('#swipp_select_term').autocomplete({ minLength: 1 });
		var payload = {
			'action'	: 'swipp_autosuggest',
			'term'		: autosuggest.val()
		}
		swippApiCall(payload, function(data){
			var data = $.parseJSON(data);
			var suggestions = [];
			jQuery.each(data.response.searchOutput.terms.terms, function(i, e) {
				suggestions.push(e.name);
				jQuery('#swipp_select_term').autocomplete('option', 'source', suggestions);
			});
		});
	});

});


/**
 * Append text to the content editor
 */
function swippAppendText(text) {
	//Insert content
	if(parent.tinyMCE.activeEditor.getContent().indexOf('[swippjs]') === -1) {
		parent.tinyMCE.activeEditor.setContent(parent.tinyMCE.activeEditor.getContent() + text);
	}
	//Close window
	parent.jQuery("#TB_closeWindowButton").click();
}


/**
 * Swipp sign up and callback
 */
function swippSignUp(payload){
	swippApiCall(payload, function(data){
		data = jQuery.parseJSON(data);
		var signInPayload = {
			action: 'swipp_sign_in',
			accountType: 1,
			emailAddress: emailAddress,
			accountToken: userToken
		};
		swippSignIn(signInPayload);
	});
}

/**
 * Swipp sign in callback
 */
function swippSignIn(payload){
	swippApiCall(payload, function(data){
		data = jQuery.parseJSON(data);
		jQuery("#swipp_sign_up").addClass('hidden');
		jQuery("#swipp_account_token, #swipp_account_token_hidden")
			.val(data.response.signInOutput.accessToken)
			.removeClass('hidden');
		jQuery("#swipp_user_guid_hidden")
			.val(data.response.signInOutput.userGuid);
		var checkOrgPayload = {
			action: 'swipp_check_org',
		};
		swippCheckOrg(checkOrgPayload);
	});
}

/**
 * Swipp check org callback
 */
function swippCheckOrg(payload){
	swippApiCall(payload, function(data){
		data = jQuery.parseJSON(data);
		var orgId = (data.response.length > 0) ? data.response.orgAccountDetails.id : data.response.accountId;
		jQuery("#swipp_org_id_hidden").val(orgId);
	});
}

/**
 * Swipp create widget
 */
function swippCreateWidget(postId, termId){
	var payload = {
		'action': 'swipp_create_widget',
		'post_id': postId,
		'term_id': termId
	}
	swippApiCall(payload, function(data){
		swippAppendText('[swippjs]');
		$('input[value="swipp_widget"]').parent().parent().find('td:nth-child(2)').find('textarea').val(data);
	});
}

/**
 * Swipp prepare api call
 */
function swippApiCall(payload, successCallback){
	console.log('API Call: ');
	console.log(payload);
	jQuery.ajax({
		url:	ajaxurl,
		type: 'POST',
		data:	payload,
		success: function(data){
			console.log(data);
			if(!swippApiErrors(data)) {
				successCallback(data);
			}
		},
		error: function(xhr, ts, et) {
			console.log('Error:');
			console.log(xhr);
			console.log(ts);
			console.log(et);
		}
	});
}

/**
 * Swipp handle api errors
 */
function swippApiErrors(data){
	var data = jQuery.parseJSON(data);
	if(typeof data.response !== 'undefined') {
		if(typeof data.response.errorInfo !== 'undefined') {
			console.log("Error Status: " + data.response.errorInfo.errorCode);
			console.log("Error Message: " + data.response.errorInfo.errorMessage);
			alert(data.response.errorInfo.errorMessage);
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}
