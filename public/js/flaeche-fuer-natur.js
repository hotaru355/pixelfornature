if (typeof Object.create !== 'function') {
	Object.create = function(obj) {
		var Func = function() {
		};
		Func.prototype = obj;
		return new F();
	};
}

imageHandler = {
	paramName : '',
	defaultImagePath : '',
	galeryPath : '',
	image : '',
	imagePath : '',

	init : function(config) {
		if (typeof config !== 'undefined') {
			this.paramName = typeof config.paramName !== 'undefined' ? config.paramName
					: 'image';
			this.defaultImagePath = typeof config.defaultImagePath !== 'undefined' ? config.defaultImagePath
					: '';
			this.galeryPath = typeof config.galeryPath !== 'undefined' ? config.galeryPath
					: '/images/galerie/';
		}
		this.image = ($.url().param(this.paramName));
		this.imagePath = (this.image) ? this.galeryPath + this.image : this.defaultImagePath;
	},

	setBackgroundImage : function(newPath) {
		if (newPath) {
			this.imagePath = newPath;
		}
		$('html').css('backgroundImage', 'url(' + this.imagePath + ')');
	},

	findCarouselIndex : function(listItem) {
		var startIndex = 0;
		var path = this.imagePath;
		if (this.image) {
			listItem.each(function(index, element) {
				if ($(this).attr('src') === path) {
					startIndex = index;
				}
			});
		}
		return startIndex;
	},
};