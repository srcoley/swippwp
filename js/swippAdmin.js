var emailAddress;
var userToken;
var suggestion_ids = [];
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
			console.log();
			if(typeof swippTermInput.attr('data-term-id') === 'undefined') {
				var termKey = 'term';
				var termVal = swippTermInput.val(); 
			} else {
				var termKey = 'termId';
				var termVal = parseInt(jQuery('#swipp_term_check').attr('data-term-id'));
			}
			var swippWidgetStyle = jQuery('.swippStyle:checked').val();
			var postId = $('#post_ID').val();
			var payload = {
				'action'	: 'swipp_create_widget',
				'type'	: parseInt(swippWidgetStyle),
				'post_id'	: postId
			}
			payload[termKey] = termVal;
			swippApiCall(payload, function(data){
				data = $.parseJSON(data);
				if(data.status == 200) {
					swippCreateWidget(data.response.termId, payload.type, data.response.widgetKey);
					//swippCreateWidget(postId, data.response.termId, swippWidgetStyle);
				} else {
					alert('Failed Request');
				}
			});
		} else {
			alert('You must select a topic.');
		}
		e.preventDefault();
	});


	/*
	 * jQuery UI Autocomplete
	 */
	jQuery('#swipp_select_term').autocomplete({
		minLength: 2,
		select: function(event, ui){
			//jQuery('#swipp_term_check').css('display', 'inline-block');
			//jQuery('#swipp_create_widget').removeAttr('disabled');
			//console.log(suggestion_ids[ui.item.label]);
			jQuery('#swipp_term_check').attr('data-term-id', suggestion_ids[ui.item.label]);
		}
	});


	/**
	 * Term Select Event Handler
	 */
	$("#swipp_select_term").on("keyup", function(e){
		var autosuggest = $(this);

		
		var payload = {
			'action'	: 'swipp_autosuggest',
			'term'		: autosuggest.val()
		}
		swippApiCall(payload, function(data){
			var data = $.parseJSON(data);
			var suggestions = [];
			suggestion_ids = [];
			jQuery.each(data.response.searchOutput.terms.terms, function(i, e) {
				suggestions.push({ label: e.name, value: e.name });
				suggestion_ids[e.name] = e.id;
			});
			jQuery('#swipp_select_term').autocomplete('option', 'source', suggestions);
		});
	});

});


/**
 * Append text to the content editor
 */
function swippAppendText(text) {
	//Insert content
	if(!parent.tinyMCE.activeEditor || jQuery('#content_parent').css('display') == 'none') {
		var activeEditor = jQuery('#content.wp-editor-area');
		activeEditor.val(activeEditor.val() + text);
	} else {
		parent.tinyMCE.activeEditor.setContent(parent.tinyMCE.activeEditor.getContent() + text);
	}
	//Close window
	jQuery('#swipp_term_check').removeAttr('data-term-id');
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
		var orgId = (typeof data.response !== 'undefined' && typeof data.response.accountId === 'undefined') ? data.response.orgAccountDetails[0].id : data.response.accountId;
		jQuery("#swipp_org_id_hidden").val(orgId);
		jQuery("#swipp_auth_notice").css('display', 'inline');
	});
}

/**
 * Swipp create widget
 */
function swippCreateWidget(term_id, widget_type, widget_key){
	swippAppendText('[swippjs type="' + widget_type + '" term_id="' + term_id + '" widget_key="' + widget_key + '"]');
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
			console.log('Raw:');
			console.log(data);
			console.log('JSON:');
			console.log(jQuery.parseJSON(data));
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
			if(data.response.errorInfo.errorCode = "ALREADY_SIGNED_UP_ERROR") {
				return false;
			}
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
