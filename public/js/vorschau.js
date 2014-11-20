(function() {


	// Init Facebook
	window.fbAsyncInit = function() {
		FB.init({
			appId : '1410418365855348',
			status : true, // check login status
			cookie : true, // enable cookies so server can access session
			xfbml      : false,
			version    : 'v2.2'
		});
	};

	// Lade Facebook-SDK asynchron
	(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) {return;}
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/de_DE/sdk.js";
		fjs.parentNode.insertBefore(js, fjs);
	} (document, 'script', 'facebook-jssdk'));


		// Eventhandler wenn Authentifizierungsstatus sich aendert
		// FB.Event.subscribe('auth.authResponseChange', function(response) {
		// 	if (response.status === 'connected') {
		// 		uploadAsync(response['authResponse']);
		// 	} else {
		// 		alert('just diconnected');
		// 	}
		// });


	// Uebermittelt asynchron den Bildausschnitt und die Facebookzugangsdaten an
	// den Server und verarbeitet die Antwort
	function uploadAsync(authResponse) {
		$.ajax({
			url : 'hochladen',
			type : 'GET',
			dataType : 'json',
			data : {
				'accessToken' : authResponse['accessToken'],
				'signedRequest' : authResponse['signedRequest'],
				'userID' : authResponse['userID']
			},
			global : true,
			beforeSend : function() {
				$('button#upload').attr('disabled', 'disabled');
				$('div#successMsg').hide();
				$('div#facebookError').hide();
				$('div#commError').hide();
			}
		}).always(function() {
			$('button#upload').removeAttr('disabled');
		}).done(function(responseJson) {
			if (responseJson['link']) {
				$('div#successMsg').show();
				window.open(responseJson['link'], '_blank', 'fullscreen=yes,height=600,width=900,scrollbars=yes');
			} else {
				$('span#errorCode').html(responseJson['errorCode']);
				$('div#facebookError').show();
			}
		}).fail(function(responseJson) {
			$('div#commError').show();
		});
	}

	$(function() {
		$('#upload').button().click(function() {
			FB.getLoginStatus(function(response) {
				if (response.status === 'connected') {
					console.log('Already logged in. Going straight to goal.');
					uploadAsync(response.authResponse);
				} else if (response.status === 'not_authorized') {
					// The person is logged into Facebook, but not your app.
					// if (!isset($permissions['data'][0]['user_photos'])
					// or !isset($permissions['data'][0]['manage_pages']) )
				} else {
					FB.login(function(response) {
						if (response.status === 'connected') {
							console.log('Just logged in. Going to goal now.');
							uploadAsync(response.authResponse);
						} else if (response.status === 'not_authorized') {
							// The person is logged into Facebook, but not your app.
						} else {
						// The person is not logged into Facebook, so we're not sure if
						// they are logged into this app or not.
						}
					}, {scope: 'user_photos,manage_pages'});
				}
			});
		});

	});
})();
