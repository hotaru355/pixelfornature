(function() {

	// Init Facebook
	window.fbAsyncInit = function() {
		FB.init({
			appId : '1410418365855348',
			channelUrl : '//localhost/channel.html',
			status : true, // check login status
			cookie : true, // enable cookies so server can access session
			xfbml : false
		});

		// Eventhandler wenn Authentifizierungsstatus sich aendert
		FB.Event.subscribe('auth.authResponseChange', function(response) {
			if (response.status === 'connected') {
				uploadAsync(response['authResponse']);
			} else {
				alert('just diconnected');
			}
		});

		$('#upload').button().click(function() {
			// uploadAsync({
			// 	accessToken : '',
			// 	signedRequest : '',
			// 	userID : ''
			// });

			FB.login();
		});
	};

	// Lade Facebook-SDK asynchron
	(function(d) {
		var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
		if (d.getElementById(id)) {
			return;
		}
		js = d.createElement('script');
		js.id = id;
		js.async = true;
		js.src = '//connect.facebook.net/de_DE/all.js';
		ref.parentNode.insertBefore(js, ref);
	}(document));

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
				'userID' : authResponse['userID'],
				// 'imageProps' : imageProps,
			},
			global : true,
			beforeSend : function() {
				$('#upload').hide();
				$('#progressbar').show();
			}
		}).always(function() {
			$('#progressbar').hide();
			$('#upload').show();
		}).done(function(responseJson) {
			if (responseJson['link']) {
				$('#successMsg').show();
				window.open(responseJson['link'], '_blank', 'fullscreen=yes,height=600,width=900,scrollbars=yes');
			} else {
				$('#errorCode').html(responseJson['errorCode']);
				$('#facebookError').show();
			}
		}).fail(function(responseJson) {
			$('#commError').show();
		});
	}

	$(function() {

		// Ladebalken
		$('#progressbar').progressbar({
			value : false,
		});
		
		// Bildmatrix und Besuchername
		matrix = 'matrix(' + $.url().param('scale') + ', 0, 0, '
				+ $.url().param('scale') + ', '
				+ $.url().param("x") + ', '
				+ $.url().param("y") + ')';
		transform = {
			"transform" : matrix,
			"-ms-transform" : matrix,
			"-webkit-transform" : matrix
		};
		$('#clippingImage').css(transform);
		$('#labelUserName').html($.url().param('name'));
	});


})();
