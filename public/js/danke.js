(function() {
	prevNextHandler.initMenus([null, null], [
		{
			menu: '#menuResetPassword',
			title: 'Passwort vergessen?'
		}, {
			menu: '#menuThankyou',
			title: 'Meine gespendeten Pixel'
		}, {
			menu: '#menuNewMember',
			title: 'Jetzt mitmachen!'
		}]);
	
	$(function() {


		$('button#closeMenu').click(function() {
			setTimeout(function() {
				window.location.href = '/';
			}, 1000);
		})

		$('div#menu').css({top: 0});
	});
})();