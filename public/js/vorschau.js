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
	function uploadAsync(popup) {
		$.ajax({
			url : 'hochladen',
			type : 'GET',
			dataType : 'json',
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
			if (responseJson.linkUrl) {
				popup.location.href = responseJson.linkUrl;
				window.location.href = 'danke';
			} else {
				$('span#errorCode').html(responseJson.errorCode);
				$('div#facebookError').show();
			}
		}).fail(function(responseJson) {
			$('div#commError').show();
		});
	}

	$(function() {
		$('button#upload').click(function() {
			var popup = window.open('', '_blank', 'fullscreen=yes,height=600,width=900,scrollbars=yes');
			FB.getLoginStatus(function(response) {
				if (response.status === 'connected') {
					console.log('Already logged in. Going straight to goal.');
					uploadAsync(popup);
				} else if (response.status === 'not_authorized') {
					// The person is logged into Facebook, but not your app.
					// if (!isset($permissions['data'][0]['user_photos'])
					// or !isset($permissions['data'][0]['manage_pages']) )
				} else {
					FB.login(function(response) {
						if (response.status === 'connected') {
							console.log('Just logged in. Going to goal now.');
							uploadAsync(popup);
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
		$('button#closeOverlay').click(function() {
			$('div#overlay').css('top', '-100%');
			setTimeout(function() {
				window.location.href = '/'
			}, 1000);
		})

	});
})();
