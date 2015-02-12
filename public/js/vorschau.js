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


	// Uebermittelt asynchron den Bildausschnitt und die Facebookzugangsdaten an
	// den Server und verarbeitet die Antwort
	function uploadAsync() {
		$.ajax({
			url : 'hochladen',
			type : 'GET',
			dataType : 'json',
			global : true,
			beforeSend : function() {
				$('button#upload').attr('disabled', 'disabled');
				$('div#successMsg').addClass('hidden');
				$('div#facebookError').addClass('hidden');
				$('div#commError').addClass('hidden');
			}
		}).always(function() {
			$('button#upload').removeAttr('disabled');
		}).done(function(responseJson) {
			if (responseJson.linkUrl) {
				$('a#uploadLink').attr('href', responseJson.linkUrl);
				//$('a#uploadLink').html(responseJson.linkUrl);
				$('#upload-div').hide();
				$('#change-div').removeClass('hidden');
				$('#continue').removeClass('hidden');
				//window.location.href = 'danke';
			} else {
				$('span#errorCode').html(responseJson.errorCode);
				$('div#facebookError').removeClass('hidden');
			}
		}).fail(function(responseJson) {
			$('div#commError').removeClass('hidden');
		});
	}

	$(function() {
		$('button#upload').click(function() {
			//DEBUG
			// uploadAsync();

			FB.getLoginStatus(function(response) {
				if (response.status === 'connected') {
					console.log('Already logged in. Going straight to goal.');
					uploadAsync();
				} else if (response.status === 'not_authorized') {
					// The person is logged into Facebook, but not your app.
					// if (!isset($permissions['data'][0]['user_photos'])
					// or !isset($permissions['data'][0]['manage_pages']) )
				} else {
					FB.login(function(response) {
						if (response.status === 'connected') {
							console.log('Just logged in. Going to goal now.');
							uploadAsync();
						} else if (response.status === 'not_authorized') {
							// The person is logged into Facebook, but not your app.
						} else {
						// The person is not logged into Facebook, so we're not sure if
						// they are logged into this app or not.
						}
					}, {scope: 'publish_actions'});
				}
			});
		});

		$('#closeOverlay').click(function() {
			$('div#overlay').css('top', '-100%');
			$('div#overlay').one($.support.transition.end, function() {
				window.location.href = '/'
			});
		})

	});
})();
