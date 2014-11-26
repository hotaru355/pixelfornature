// imageHandler = {
// 	paramName : 'image',
// 	image : '',

// 	init : function(config) {
// 		if (config !== undefined && config.paramName !== undefined) {
// 			this.paramName = config.paramName;
// 		}
// 		this.image = ($.url().param(this.paramName));
// 	},

// 	setBackgroundImage : function(newPath) {
// 		if (newPath !== undefined) {
// 			this.imagePath = newPath;
// 		}
// 		$('html').css('backgroundImage', 'url(' + this.image + ')');
// 	},
// };

(function() {
	var menuNewMember, menuLanding, menuResetPassword, menuAccount;

	function transitionMenu(menuDiv) {
		placeMenu(menuDiv, '0');
		$('div#slidingContainer').append(menuDiv);
		$('div#slidingContainer')[0].offsetHeight;
		$('div#slidingContainer').children().each(function(index, child) {
			$(this).css('left', '-50%');
		})
	}

	function placeMenu(menuDiv, left) {
		menuDiv.addClass('noTransition'); // Disable transitions
		menuDiv.css('left', left);
		menuDiv[0].offsetHeight; // Trigger a reflow, flushing the CSS changes
		menuDiv.removeClass('noTransition'); // Re-enable transitions
	}

	function resetMenu() {
		if ($('div#slidingContainer').children().length > 1) {
			$('div#slidingContainer').children().each(function(index, child) {
				if (index === 0) {
					placeMenu($(this), '0');
				} else {
					$(this).remove();
				}
			})
		}
	}

	function mapErrorToLabel(errors, idPostfix) {
		$.map(errors, function(error, id) {
			var formGroup = $('div#' + id + idPostfix + 'Group');
			formGroup.addClass('has-error');
			$.map(errors[id], function(errMsg, errName) {
				formGroup.append('<label class="control-label" for="' + id + idPostfix + '">' + errMsg + '</label>');
			});
		});
	}

	function clearErrorLabels() {
		$('div.form-group').removeClass('has-error');
		$('div.form-group label').remove();
	}


	$(function() {
		menuNewMember = $('div#menuNewMember');
		menuResetPassword = $('div#menuResetPassword');
		menuAccount = $('div#menuAccount');

		$('area#infoCtrl').click(function() {
			resetMenu();
			$('div#menu').animate({
				top: 0
			});
		});
		$('button#closeMenu').click(function() {
			$('div#menu').animate({
				top: '-100%'
			});
		});

		$('area#flashCtrl').click(function() {
			$('div#helpBox').toggle();
		});

		$('button#closeHelp').click(function() {
			$('div#helpBox').toggle();
		});


		$('a.registerNow').click(function() {
			transitionMenu(menuNewMember);
		});

		$('a.lostPassword').click(function() {
			transitionMenu(menuResetPassword);
		});

		$('button#memberAccount').click(function() {
			transitionMenu(menuAccount);
		});

		var signupBtn = $('button#registerNewMember');
		signupBtn.click(function() {
			$.ajax({
				url: 'mitglieder/neu',
				type: 'POST',
				dataType: 'json',
				global: true,
				data: {
					vorname: $('input#vornameSignup').val(),
					nachname: $('input#nachnameSignup').val(),
					email: $('input#emailSignup').val(),
					passwort: $('input#passwortSignup').val(),
					passwortWiederholt: $('input#passwortWiederholtSignup').val(),
				},
				beforeSend: function() {
					signupBtn.attr('disabled', 'disabled');
					clearErrorLabels()
				}
			}).always(function() {
				signupBtn.removeAttr('disabled');
			}).done(function(responseJson) {
				if (responseJson.error) {
					mapErrorToLabel(responseJson.error, 'Signup');
				} else {
					location.reload();
				}
			}).fail(function(responseJson) {
				$('div#commError').show();
			});
		});

		var loginBtn = $('button#loginMember');
		loginBtn.click(function() {
			$.ajax({
				url: 'auth/login',
				type: 'POST',
				dataType: 'json',
				global: true,
				data: {
					email: $('input#emailLogin').val(),
					passwort: $('input#passwortLogin').val(),
				},
				beforeSend: function() {
					loginBtn.attr('disabled', 'disabled');
					clearErrorLabels()
				}
			}).always(function() {
				loginBtn.removeAttr('disabled');
			}).done(function(responseJson) {
				if (responseJson.error) {
					mapErrorToLabel(responseJson.error, 'Login');
				} else if (responseJson.success) {
					location.reload();
				} else {
					var formGroup = $('div#loginFailed');
					formGroup.addClass('has-error');
					formGroup.append('<label class="control-label" for="passwortLogin">Die Anmeldung schlug leider fehl</label>');
				}
			}).fail(function(responseJson) {
				$('div#commError').show();
			});
		});

		var logoutBtn = $('button#logoutMember');
		logoutBtn.click(function() {
			$.ajax({
				url: 'auth/logout',
				type: 'POST',
				dataType: 'json',
				global: true,
				beforeSend: function() {
					logoutBtn.attr('disabled', 'disabled');
				}
			}).always(function() {
				logoutBtn.removeAttr('disabled');
			}).done(function(responseJson) {
				location.reload();
			}).fail(function(responseJson) {});
		});

		var updateContactBtn = $('button#updateContact');
		updateContactBtn.click(function() {
			$.ajax({
				url: 'mitglieder/aendern',
				type: 'POST',
				dataType: 'json',
				global: true,
				data: {
					vorname: $('input#vornameUpdate').val(),
					nachname: $('input#nachnameUpdate').val(),
					strasse: $('input#strasseUpdate').val(),
					plz: $('input#plzUpdate').val(),
					ort: $('input#ortUpdate').val(),
					telefon: $('input#telefonUpdate').val(),
				},
				beforeSend: function() {
					updateContactBtn.attr('disabled', 'disabled');
					clearErrorLabels()
				}
			}).always(function() {
				updateContactBtn.removeAttr('disabled');
			}).done(function(responseJson) {
				if (responseJson.success) {
					alert('Kontaktdaten wurden aktualisiert!');
				} else {
					mapErrorToLabel(responseJson.error, 'Update');
				}
			}).fail(function(responseJson) {});
		});

		var updateCredentialBtn = $('button#updateCredential');
		updateCredentialBtn.click(function() {
			var data = {
				email: $('input#emailUpdate').val()
			};
			var password = $('input#passwortUpdate').val();
			var passwordRep = $('input#passwortWiederholtUpdate').val();
			if (password !== "" || passwordRep !== "") {
				data.passwort = password;
				data.passwortWiederholt = passwordRep;
			}

			$.ajax({
				url: 'mitglieder/aendern',
				type: 'POST',
				dataType: 'json',
				global: true,
				data: data,
				beforeSend: function() {
					updateCredentialBtn.attr('disabled', 'disabled');
					clearErrorLabels()
				}
			}).always(function() {
				updateCredentialBtn.removeAttr('disabled');
			}).done(function(responseJson) {
				if (responseJson.success) {
					alert('Zugangsdaten wurden aktualisiert!');
				} else {
					mapErrorToLabel(responseJson.error, 'Update');
				}
			}).fail(function(responseJson) {});
		});

		var deleteAccountBtn = $('a#deleteAccount');
		deleteAccountBtn.click(function() {
			if (confirm('Willst Du wirklich Dein Konto löschen?')) {
				$.ajax({
					url: 'mitglieder/loeschen',
					type: 'POST',
					dataType: 'json',
					global: true,
					beforeSend: function() {
						deleteAccountBtn.attr('disabled', 'disabled');
					}
				}).always(function() {
					deleteAccountBtn.removeAttr('disabled');
				}).done(function(responseJson) {
					if (responseJson.success) {
						location.reload();
					}
				}).fail(function(responseJson) {});
			}
		});

	})
})();