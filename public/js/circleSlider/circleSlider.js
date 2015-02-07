(function() {
	function circleSliderJs(options) {
		var min = options.min || 0;
		var max = options.max || 100;
		var value = options.value || 0;
		var slideFn = options.slide || function(e, val) {};
		var container = $('#' + options.container);
		var slider = $('#' + options.slider);

		if (!container.length || !slider.length) {
			throw Error('container or slider not found!');
		}

		var sliderWidth = slider.width();
		var sliderHeight = slider.height();
		var radius = container.width() / 2;
		var mdown = false;

		function positionSlider(degree) {
			var x = Math.round(radius * Math.sin(degree * Math.PI / 180));
			var y = Math.round(radius * -Math.cos(degree * Math.PI / 180));

			slider.css({
				left: x + radius - sliderWidth / 2,
				top: y - sliderHeight / 2
			});
		}

		// initial position
		var deg = 270 - (value - min) * 180 / (max - min);
		positionSlider(deg);

		container
			.mousedown(function(e) {
				mdown = true;
				e.originalEvent.preventDefault();
			})
			.mouseup(function(e) {
				mdown = false;
			})
			.mouseleave(function(e) {
				//mdown = false;
			})
			.mousemove(function(e) {
				if (mdown) {
					// firefox compatibility
					if (typeof e.offsetX === "undefined" || typeof e.offsetY === "undefined") {
						var targetOffset = $(e.target).offset();
						e.offsetX = e.pageX - targetOffset.left;
						e.offsetY = e.pageY - targetOffset.top;
					}
					if ($(e.target).is('#rotationSliderContainer')) {
						var mPos = {
							x: e.offsetX,
							y: e.offsetY + radius
						};
					} else {
						var mPos = {
							x: e.target.offsetLeft + e.offsetX,
							y: e.target.offsetTop + e.offsetY + radius
						};
					}

					var atan = Math.atan2(mPos.x - radius, mPos.y - radius);
					deg = -atan / Math.PI * 180 + 180;

					if (deg < 90) {
						deg = 90;
					} else if (deg > 270) {
						deg = 270;
					}

					positionSlider(deg);

					var value = min + (270 - deg) / 180 * (max - min);

					slideFn(e, value);
				}
			});
	}

	function circleSliderSvg(options) {
		var min = options.min || 0;
		var max = options.max || 100;
		var initialValue = options.value || 0;
		var slideFn = options.slide || function(e, val) {};
		var container = $(options.container);
		var slider = $(options.slider);

		if (!container.length || !slider.length) {
			throw Error('container or slider not found!');
		}

		function positionSlider(degree) {
			slider.attr('transform', 'rotate(' + degree + ', 95, 95)');
		}

		function moveHandler(e) {
			var evt = e ? e : window.event;
			var moveX = 0, moveY = 0;
			var radius = container.width() / 2;

			if ((evt.clientX || evt.clientY) &&
				document.body &&
				document.body.scrollLeft!=null) {
				moveX = evt.clientX + document.body.scrollLeft;
				moveY = evt.clientY + document.body.scrollTop;
			}
			if ((evt.clientX || evt.clientY) &&
				document.compatMode=='CSS1Compat' && 
				document.documentElement && 
				document.documentElement.scrollLeft!=null) {
				moveX = evt.clientX + document.documentElement.scrollLeft;
				moveY = evt.clientY + document.documentElement.scrollTop;
			}
			if (evt.pageX || evt.pageY) {
				moveX = evt.pageX;
				moveY = evt.pageY;
			}

			var x = moveX - container.offset().left - radius;
			var y = moveY - container.offset().top - radius;
			var deg = Math.atan2(x, y) / Math.PI * -180 + 270;

			if (deg < 180) {
				deg = 180;
			} else if (deg > 360) {
				deg = 360;
			}
			positionSlider(deg);

			var value = min + (360 - deg) / 180 * (max - min);
			slideFn(e, value);

			$(this).addClass('moved');
			slider.addClass('active');

			return false;
		}

		// initial position
		var initialDeg = (min - initialValue) * 180 / (max - min) + 360;
		positionSlider(initialDeg);

		slider.mousedown(function(e) {
			e.originalEvent.preventDefault();
			$('body')
				.bind('mousemove', moveHandler)
				.bind('mouseup', function(evt) {
					$(this)
						.unbind('mousemove', moveHandler)
						.unbind(evt)
						.removeClass('moved');
					slider.removeClass('active');
				});
		});
	}
	$.fn.extend({
		circleSlider: circleSliderSvg
	});
})();