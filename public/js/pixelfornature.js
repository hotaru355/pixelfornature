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
	var menuNewMember, menuLanding, menuResetPassword, menuAccount, slidingFrame, secondaryMenu;

	function transitionMenu(menuDiv) {
		var slidingMenus = $('div.sliding-menu');
		secondaryMenu = menuDiv;
		// remove scroll bar from all menus during transition
		slidingMenus.css({
			'overflow-y': 'hidden'
		});
		// place new menu div to the right of the screen 
		placeMenu(menuDiv, '100%');
		// slide the menu pages over
		slidingFrame.children().first().css({
			left: '-100%'
		});
		menuDiv.css({
			left: '0'
		});
		// re-add scroll bar to menu once transition complete
		menuDiv.one($.support.transition.end, function() {
			slidingMenus.css({
				'overflow-y': 'auto'
			});
		});
	}

	function placeMenu(menuDiv, left) {
		// Disable transitions
		menuDiv.addClass('noTransition');
		menuDiv.css('left', left);
		// Trigger a reflow, flushing the CSS changes
		menuDiv[0].offsetHeight;
		// Re-enable transitions
		menuDiv.removeClass('noTransition');
	}

	function resetMenu() {
		placeMenu($('div#sliding-frame :first-child'), '0');
		if (secondaryMenu) {
			placeMenu(secondaryMenu, '-100%');
		}
	}

	function mapErrorToLabel(errors, idPostfix, combiNameById) {
		$.map(errors, function(error, id) {
			var idName = id + idPostfix;
			if (combiNameById && combiNameById[id]) {
				var formGroup = $('div#combi' + combiNameById[id]);
				$('input#' + idName).addClass('is-error');
				$.map(errors[id], function(errMsg, errName) {
					formGroup.append('<label class="control-label is-error" for="' + idName + '">' + errMsg + '</label>');
				});
			} else {
				var formGroup = $('div#' + idName + 'Group');
				formGroup.addClass('has-error');
				$.map(errors[id], function(errMsg, errName) {
					formGroup.append('<label class="control-label" for="' + idName + '">' + errMsg + '</label>');
				});
			}
		});
	}

	function clearErrorLabels(formId) {
		$('form#' + formId + ' div.form-group').removeClass('has-error');
		$('form#' + formId + ' div.form-group label').remove();
		$('form#' + formId + ' div.combi-input-container input').removeClass('is-error');
		$('form#' + formId + ' div.combi-input-container label').remove();
	}

	$(function() {
		slidingFrame = $('div#sliding-frame');
		menuLanding = $('div#menuLanding');
		menuNewMember = $('div#menuNewMember');
		menuResetPassword = $('div#menuResetPassword');
		menuAccount = $('div#menuAccount');

		$('area#infoCtrl').click(function() {
			resetMenu();
			$('div#menu').css('top', '0');
		});
		$('button#closeMenu').click(function() {
			$('div#menu').css('top', '-100%');
		});

		$('area#flashCtrl').click(function() {
			$('div#helpBox').toggle();
		});

		$('button#closeHelp').click(function() {
			$('div#helpBox').toggle();
		});

		$('button#closeReset').click(function() {
			$('div#overlayReset').css('top', '-100%');
			$('div#overlayReset').one($.support.transition.end, function() {
				window.location.href = '/'
			});
		})


		$('a.signupNow').click(function() {
			transitionMenu(menuNewMember);
		});

		$('a.lostPassword').click(function() {
			transitionMenu(menuResetPassword);
		});

		$('button#memberAccount').click(function() {
			transitionMenu(menuAccount);
		});

		$('form#signupNewMember').submit(function(event) {
			event.preventDefault();
			var signupBtn = $('form#signupNewMember :submit');
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
					clearErrorLabels('signupNewMember')
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
			return false;
		});

		$('form#loginMember').submit(function(event) {
			var combiNameById = {
				email: 'Login',
				passwort: 'Login'
			}
			event.preventDefault();
			var loginBtn = $('form#loginMember :submit');
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
					clearErrorLabels('loginMember');
				}
			}).always(function() {
				loginBtn.removeAttr('disabled');
			}).done(function(responseJson) {
				if (responseJson.error) {
					mapErrorToLabel(responseJson.error, 'Login', combiNameById);
				} else if (responseJson.success) {
					location.reload();
				} else {
					$('div#combiLogin input').addClass('is-error');
					$('div#combiLogin').append('<label class="control-label is-error" for="passwortLogin">Die Anmeldung schlug leider fehl</label>');
				}
			}).fail(function(responseJson) {
				$('div#commError').show();
			});
			return false;
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

		$('form#updateMember').submit(function(event) {
			var combiNameById = {
				vorname: 'Names',
				nachname: 'Names',
				plz: 'PlzOrt',
				ort: 'PlzOrt',
				passwort: 'Password',
				passwortWiederholt: 'Password'
			};
			var updateCredentialBtn = $('form#updateUser :submit');
			var data = {
				vorname: $('input#vornameUpdate').val(),
				nachname: $('input#nachnameUpdate').val(),
				strasse: $('input#strasseUpdate').val(),
				plz: $('input#plzUpdate').val(),
				ort: $('input#ortUpdate').val(),
				telefon: $('input#telefonUpdate').val(),
				email: $('input#emailUpdate').val()
			};
			var password = $('input#passwortUpdate').val();
			var passwordRep = $('input#passwortWiederholtUpdate').val();

			event.preventDefault();
			if (password || passwordRep) {
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
					clearErrorLabels('updateMember')
				}
			}).always(function() {
				updateCredentialBtn.removeAttr('disabled');
			}).done(function(responseJson) {
				if (responseJson.success) {
					alert('Kontodaten wurden aktualisiert!');
				} else {
					mapErrorToLabel(responseJson.error, 'Update', combiNameById);
				}
			}).fail(function(responseJson) {});
			return false;
		});

		$('form#requestReset').submit(function(event) {
			event.preventDefault();
			var signupBtn = $('form#requestReset :submit');
			$.ajax({
				url: 'auth/request-reset',
				type: 'POST',
				dataType: 'json',
				global: true,
				data: {
					email: $('input#emailRequestReset').val(),
				},
				beforeSend: function() {
					signupBtn.attr('disabled', 'disabled');
					clearErrorLabels('requestReset')
				}
			}).always(function() {
				signupBtn.removeAttr('disabled');
			}).done(function(responseJson) {
				if (responseJson.error) {
					mapErrorToLabel(responseJson.error, 'RequestReset');
				} else {
					alert('Eine E-Mail zum Zurücksetzen deines Passworts wurde an dich gesand!');
				}
			}).fail(function(responseJson) {
				$('div#commError').show();
			});
			return false;
		});

		$('form#resetPassword').submit(function(event) {
			var combiNameById = {
				passwort: 'Password',
				passwortWiederholt: 'Password'
			};
			event.preventDefault();
			var signupBtn = $('form#resetPassword :submit');
			$.ajax({
				url: 'aendern',
				type: 'POST',
				dataType: 'json',
				global: true,
				data: {
					email: $('input#emailResetPassword').val(),
					passwort: $('input#passwortResetPassword').val(),
					passwortWiederholt: $('input#passwortWiederholtResetPassword').val(),
					verifizierungHash: $('input#verifizierungHashResetPassword').val(),
				},
				beforeSend: function() {
					signupBtn.attr('disabled', 'disabled');
					clearErrorLabels('resetPassword')
				}
			}).always(function() {
				signupBtn.removeAttr('disabled');
			}).done(function(responseJson) {
				if (responseJson.error) {
					mapErrorToLabel(responseJson.error, 'ResetPassword', combiNameById);
				} else {
					alert('Dein Passwort wurde geändert!');
					document.location.href = "/";
				}
			}).fail(function(responseJson) {
				$('div#commError').show();
			});
			return false;
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