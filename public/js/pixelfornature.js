(function() {
	var menuNewMember, menuLanding, menuResetPassword, menuAccount, slidingFrame, secondaryMenu;
	var currentMenuIndex;
	
	// global variable ...
	prevNextHandler = new PrevNextHandler();

	function transitionMenu(menuDiv, isRight, onTransitionEnd) {
		var slidingMenus = $('#menu .sliding-card');
		var start = isRight ? 'slided-left': 'slided-right';
		var end = isRight ? 'slided-right' : 'slided-left';

		// update menu index
		currentMenuIndex += isRight ? (-1) : 1;

		// remove scroll bar from all menus during transition
		slidingMenus.css({
			'overflow-y': 'hidden'
		});
		// place new menu div to the right/left of the screen 
		menuDiv.removeClass(end +' slided').addClass(start);
		// Trigger a reflow, flushing the CSS changes
		menuDiv[0].offsetHeight;

		// slide the menu pages over
		$('#menu .sliding-card.slided-center')
			.removeClass('slided-center')
			.addClass(end + ' slided');
		menuDiv
			.removeClass(start)
			.addClass('slided-center slided');

		// re-add scroll bar to menu once transition complete
		menuDiv.one($.support.transition.end, function() {
			slidingMenus.css({
				'overflow-y': 'auto'
			});
			prevNextHandler.updateHandlers(currentMenuIndex);
			if (onTransitionEnd) {
				onTransitionEnd();
			}
		});
	}

	function resetMenu() {
		currentMenuIndex = 1;
		prevNextHandler.updateHandlers(currentMenuIndex);
		$('#menu .sliding-card:not(#menuLanding)')
			.removeClass('slided-center slided-right slided')
			.addClass('slided-left');
		$('#menu .sliding-card#menuLanding')
			.removeClass('slided-left slided-right slided')
			.addClass('slided-center');
	}

	function flipCard() {
	    var card = $('.flipcard');
	    var front = $('.flipcard-front');
	    var back = $('.flipcard-back');
	    var tallerHight = Math.max(front.height(), back.height()) + 'px';
	    var visible = front.hasClass('ms-front-flipped') ? back : front;
    	var invisible = front.hasClass('ms-front-flipped') ? front : back;
    	var hasTransitioned = false;
		var onTransitionEnded = function () {
			hasTransitioned = true;
	        card.css({
	            'min-height': '0px'
	        });
	        visible.css({
	            display: 'none',
	        });
	        // focus is important, as otherwise hitting <enter> twice will behave incorrectly. 
	        invisible.css({
	            position: 'relative',
	            display: 'inline-block',
	        }).find('button:first-child,a:first-child').focus();
	    }

	    card.one($.support.transition.end, onTransitionEnded);
	    // for browsers that do not support transitions, like IE9
	    setTimeout(function() {
	    	if (!hasTransitioned) {
	    		onTransitionEnded.apply();
	    	}
	    }, 2000);

	    invisible.css({
	        position: 'absolute',
	        display: 'inline-block'
	    });

    	card.css('min-height', tallerHight);
    	// the IE way: flip each face of the card
	    front.toggleClass('ms-front-flipped');
	    back.toggleClass('ms-back-flipped');
		// the webkit/FF way: flip the card
    	card.toggleClass('card-flipped');
	}

	function mapErrorToLabel(errors, idPostfix, combiNameById) {
		$.map(errors, function(error, id) {
			var idName = id + idPostfix;
			if (id == 'general') {
				$('input#emailLogin,input#passwortLogin').addClass('is-error');
				idName = 'passwort' + idPostfix; // add general errors to password form-group
			}
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

	function clearErrorLabels(form) {
		form.find('div.form-group').removeClass('has-error');
		form.find('div.form-group label').remove();
		form.find('div.combi-input-container input').removeClass('is-error');
		form.find('div.combi-input-container label').remove();
	}

	function fillUserData() {
		$.ajax({
			url: 'mitglieder',
			type: 'GET',
			dataType: 'json',
			global: true,
			beforeSend: function() {
			}
		}).always(function() {
		}).done(function(responseJson) {
			if (responseJson.error) {
			} else {
				var user = responseJson.user;
				$('input#vornameUpdate').val(user.vorname);
				$('input#nachnameUpdate').val(user.nachname);
				$('input#strasseUpdate').val(user.strasse);
				$('input#plzUpdate').val(user.plz);
				$('input#ortUpdate').val(user.ort);
				$('input#telefonUpdate').val(user.telefon);
				$('input#emailUpdate').val(user.email);
				$('.username').html(user.vorname);
				$('span#userPixelsTotal').html(parseInt(user.pixelsTotal).toLocaleString('de'));
				fillTimeline(user.timeline, user.vorname);
			}
		}).fail(function(responseJson) {
			$('div#commError').show();
		});
	}

	function fillTimeline(timelineEntries, firstname) {
		var timeline = $('ul#timeline');
		var donationTemplate = $('ul#timeline li.donation');
		timelineEntries.forEach(function(entry) {
			if (entry.type == 'signup') {
				var clone = $('ul#timeline li.signup').clone();
				clone.find('.dateSignup').html(entry.datum_erstellt);
				timeline.append(clone);
				clone.removeClass('hidden');
			} else if (entry.type == 'pixelspende') {
				var clone = donationTemplate.clone();
				clone.find('.dateDonated').html(entry.datum_erstellt);
				clone.find('.pixelsDonated').html(parseInt(entry.pixel_gespendet).toLocaleString('de'));
				clone.find('.projectDonated').html(entry.timeline_name);
				timeline.append(clone);
				clone.removeClass('hidden');
			}
			
		})
	}

	function clearUserData() {
		$('input#emailLogin').val('');
		$('input#passwortLogin').val('');
		$('form#updateMember').find('input').val('');
		$('#userPixelsTotal').html('');
		$('#timelineUsername').html('');
		$('ul#timeline li:not(.hidden)').remove();
	}

	function dropDownMenu(pushUp) {
		var menu = $('div#menu');
		if (pushUp) {
			menu.one($.support.transition.end, function() {
				// due to FF bug, have to add/remove scroll bar from parent *after* the transition
				// https://bugzilla.mozilla.org/show_bug.cgi?id=625289
				$('html').css('overflow', 'auto');
			});
			menu.css('top', '-100%');
		} else {
			menu.one($.support.transition.end, function() {
				$('html').css('overflow', 'hidden');
			});
			menu.css('top', '0');
		}
	}

	function PrevNextHandler(menusLoggedIn, menusLoggedOut) {
		var menusIn = menusLoggedIn ? menusLoggedIn : [
			null,
			{
				menu: '#menuLanding',
				title: 'Das Projekt'
			}, {
				menu: '#menuAccount',
				title: 'Mein Account'
			}];
		var menusOut = menusLoggedOut ? menusLoggedOut : [
			{
				menu: '#menuResetPassword',
				title: 'Passwort vergessen?'
			}, {
				menu: '#menuLanding',
				title: 'Das Projekt'
			}, {
				menu: '#menuNewMember',
				title: 'Jetzt mitmachen!'
			}];
		this.initMenus = function(menusLoggedIn, menusLoggedOut) {
			menusIn = menusLoggedIn;
			menusOut = menusLoggedOut;
		}
		this.updateHandlers = function(menuIndex) {
			var menus = (isLoggedin) ? menusIn : menusOut;
			var prev = menus[menuIndex - 1];
			var next = menus[menuIndex + 1];
			var self = this;
			var inTransition = false;

			$('#prevMenu')
				.prop('disabled', !prev)
				.attr('title', prev ? prev.title : '');
			if (prev) {
				$('#prevMenu')
					.unbind('click')
					.click(function() {
						if (!inTransition) {
							inTransition = true;
							transitionMenu($(prev.menu), true, function() {
								inTransition = false;
							});
						}
					})
			}
			
			$('#nextMenu')
				.prop('disabled', !next)
				.attr('title', next ? next.title : '');
			if (next) {
				$('#nextMenu')
					.unbind('click')
					.click(function() {
						if (!inTransition) {
							inTransition = true;
							transitionMenu($(next.menu), false, function() {
								inTransition = false;
							});
						}
					})
			}
		}
	}
	$(function() {

		// DEBUG
		$('#test').click(function() {
			flipCard();
		});
		$('#test2').click(function() {
			$.ajax({
				url: 'test',
				type: 'POST',
				global: true,
			}).always(function() {
			}).done(function(responseJson) {
			}).fail(function(responseJson) {
				flipCard();
			});
		});

		slidingFrame = $('div#sliding-frame');
		menuLanding = $('div#menuLanding');
		menuNewMember = $('div#menuNewMember');
		menuResetPassword = $('div#menuResetPassword');
		menuAccount = $('div#menuAccount');

		currentMenuIndex = 1;
		prevNextHandler.updateHandlers(currentMenuIndex);

		$('#down-button').click(function() {
			resetMenu();
			dropDownMenu()
		});
		$('#closeMenu').click(function() {
			dropDownMenu(true);
		});

		$('#up-button').click(function() {
			$('div#helpBox').toggle();
		});

		$('#closeHelp').click(function() {
			$('div#helpBox').toggle();
		});

		$('#closeReset').click(function() {
			$('div#overlayReset').css('top', '-100%');
			$('div#overlayReset').one($.support.transition.end, function() {
				window.location.href = '/'
			});
		})

		$('a.signupNow').click(function() {
			transitionMenu(menuNewMember, false);
		});

		$('a.lostPassword').click(function() {
			transitionMenu(menuResetPassword, true);
		});

		$('button#memberAccount').click(function() {
			transitionMenu(menuAccount, false);
		});

		var signupForm = $('form#signupNewMember');
		signupForm.submit(function(event) {
			event.preventDefault();
			var signupBtn = signupForm.find(':submit');
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
					clearErrorLabels(signupForm)
				}
			}).always(function() {
				signupBtn.removeAttr('disabled');
			}).done(function(responseJson) {
				if (responseJson.error) {
					mapErrorToLabel(responseJson.error, 'Signup');
				} else {
					isLoggedin = true;
					transitionMenu($('#menu .sliding-card:first-child'), true,
						function() {
							flipCard();
							fillUserData();
						});
				}
			}).fail(function(responseJson) {
				$('div#commError').show();
			});
			return false;
		});

		function ajaxLogin(loginForm, onDone, onFail) {
			return function(event) {
				event.preventDefault();
				var loginBtn = loginForm.find(':submit');
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
						clearErrorLabels(loginForm);
					}
				}).always(function() {
					loginBtn.removeAttr('disabled');
				}).done(onDone)
				.fail(onFail);
				return false;
			}
		};

		var loginFormLanding = $('form#loginMember.combiForm');
		loginFormLanding.submit(ajaxLogin(loginFormLanding,
			function(responseJson) {
				var combiNameById = {
					email: 'Login',
					passwort: 'Login',
					general: 'Login'
				}
				if (responseJson.error) {
					mapErrorToLabel(responseJson.error, 'Login', combiNameById);
				} else if (responseJson.success) {
					flipCard();
					fillUserData();
					isLoggedin = true;
					prevNextHandler.updateHandlers(currentMenuIndex);
				}
			}
		));

		var loginFormDanke = $('form#loginMember.groupForm');
		loginFormDanke.submit(ajaxLogin(loginFormDanke,
			function(responseJson) {
				if (responseJson.error) {
					mapErrorToLabel(responseJson.error, 'Login');
				} else if (responseJson.success) {
					flipCard();
					isLoggedin = true;
					prevNextHandler.updateHandlers(currentMenuIndex);
				}
			}
		));

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
				flipCard();
				clearUserData();
				isLoggedin = false;
				prevNextHandler.updateHandlers(currentMenuIndex);
			}).fail(function(responseJson) {});
		});

		var updateForm = $('form#updateMember');
		updateForm.submit(function(event) {
			var combiNameById = {
				vorname: 'Names',
				nachname: 'Names',
				plz: 'PlzOrt',
				ort: 'PlzOrt',
				passwort: 'Password',
				passwortWiederholt: 'Password'
			};
			var updateCredentialBtn = updateForm.find(':submit');
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
					clearErrorLabels(updateForm)
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

		var requestResetForm = $('form#requestReset');
		requestResetForm.submit(function(event) {
			event.preventDefault();
			var signupBtn = requestResetForm.find(':submit');
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
					clearErrorLabels(requestResetForm);
				}
			}).always(function() {
				signupBtn.removeAttr('disabled');
			}).done(function(responseJson) {
				if (responseJson.error) {
					mapErrorToLabel(responseJson.error, 'RequestReset');
				} else {
					alert('Eine E-Mail zum Zurücksetzen deines Passworts wurde an dich gesandt!');
					transitionMenu($('#menu .sliding-card:first-child'), false);
				}
			}).fail(function(responseJson) {
				$('div#commError').show();
			});
			return false;
		});

		var resetPasswordForm = $('form#resetPassword');
		resetPasswordForm.submit(function(event) {
			var combiNameById = {
				passwort: 'Password',
				passwortWiederholt: 'Password'
			};
			event.preventDefault();
			var signupBtn = resetPasswordForm.find(':submit');
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
					clearErrorLabels(resetPasswordForm);
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
			if (confirm('Möchtest du wirklich dein Konto löschen?')) {
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
						isLoggedin = false;
						transitionMenu($('#menuLanding'), true,
							function() {
								flipCard();
								clearUserData();
							}
						);
					}
				}).fail(function(responseJson) {});
			}
		});
	})
})();