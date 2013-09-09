/**
	SlideItMoo v1.3 - Image slider for MooTools 1.3 (MooTools 1.4 compatible)
	(c) 2007-2010 Constantin Boiangiu <http://www.php-help.ro>
	Contribution from Sylvain Philip <http://www.sphilip.com>
	MIT-style license.

	More details on: http://www.php-help.ro/php-tutorials/slideitmoo-v11-image-slider/

	Changes from version 1.0
	- added continuous navigation
	- changed the navigation from Fx.Scroll to Fx.Morph
	- added new parameters: itemsSelector: pass the CSS class for divs
	- itemWidth: for elements with margin/padding pass their width including margin/padding

	Updates ( August 4th 2009 )
	- added new parameter 'elemsSlide'. When this is set to a value lower that the actual number of elements in HTML, it will slide at once that number of elements when navigation clicked. Default: null
	- added onChange event that returns the index of the current element

	Updates ( January 12th 2010 )
	- vertical sliding available. First, set your HTML to display vertically and set itemHeight:height of individual items ( including padding, border and so on ) and slideVertical:true
	- navigators ( forward/back ) no longer added by script. Instead, add them into overallContainer making their display from CSS and after add the CSS selector class to navs parameter
		IE: navs:{ 
				fwd:'.SlideItMoo_forward', 
				bk:'.SlideItMoo_back' 
			}
	- new method available resetAll(). When called, this will reset the previous settings and restart the script. Useful if you change slider content on-the-fly
	- new method available to stop autoSlide ( stopAutoSlide() ). To start autoslide back, use startAutoSlide()

	Updates ( March 24th 2011 )
	- compatibility with MooTools 1.3

	Updates ( November 7th 2011 )
	- MooTools 1.3 compat/no-compat errors solved

	Updates ( March 21th 2013)
	- Code refactoring
	- Several items can slide at once
	- Remove the navigation from the width calculation
**/

var SlideItMoo = new Class({

	Implements: [Events,Options],

	options: {
		overallContainer: null,/* outer container, contains fwd/back buttons and container for thumbnails */
		elementScrolled: null, /* has a set width/height with overflow hidden to allow sliding of elements */
		thumbsContainer: null,	/* actual thumbnails container */	
		itemsSelector: null, /* css class for inner elements ( ie: .SlideItMoo_element ) */
		itemsVisible:5, /* number of elements visible at once */
		elemsSlide: null, /* number of elements that slide at once */
		itemWidth: null, /* single element width */
		itemHeight: null, /* single element height */
		itemMargin: 0, /* Margin between elements */
		navs:{ /* starting this version, you'll need to put your back/forward navigators in your HTML */
			fwd:'.SlideItMoo_forward', /* forward button CSS selector */
			bk:'.SlideItMoo_back' /* back button CSS selector */
		},
		slideVertical: false, /* vertical sliding enabled */
		transition: Fx.Transitions.linear, /* transition */
		duration: 800, /* transition duration */
		direction: 1, /* sliding direction ( 1: enter from left/top; -1:enter from right/bottom ) */
		autoSlide: false, /* auto slide - as milliseconds ( ie: 10000 = 10 seconds ) */
		mouseWheelNav: false, /* enable mouse wheel nav */
		startIndex:0
	},

	initialize: function(options) {
		this.setOptions(options);
		/* all elements are identified on CSS selector (itemsSelector) */
		this.elements = document.id(this.options.thumbsContainer).getElements(this.options.itemsSelector);

		// Size of thumbsContainer children
		var defaultSize = this.elements[0].getSize();
		this.elementWidth = (this.options.itemWidth || defaultSize.x) + this.options.itemMargin;
		this.elementHeight = (this.options.itemHeight || defaultSize.y) + this.options.itemMargin;

		/* resizes the container div's according to the number of itemsVisible thumbnails */
		var overallSize = {};
		var scrollSize = {};
		var thumbsSize = {};

		if (this.options.slideVertical) {
			scrollSize.height = this.options.itemsVisible * this.elementHeight - this.options.itemMargin;
			thumbsSize.height = this.elements.length * this.elementHeight;
		} else {
			overallSize.width = this.options.itemsVisible * this.elementWidth - this.options.itemMargin;
			scrollSize.width  = this.options.itemsVisible * this.elementWidth - this.options.itemMargin;
			thumbsSize.width  = this.elements.length * this.elementWidth;
		}
		document.id(this.options.overallContainer).set({styles : overallSize});
		document.id(this.options.elementScrolled).set({styles : scrollSize});
		document.id(this.options.thumbsContainer).set({styles : thumbsSize});

		if (this.elements.length <= this.options.itemsVisible) return;
		if (this.options.elemsSlide > this.options.itemsVisible) {
			this.options.elemsSlide = this.options.itemsVisible;
		}

		this.direction = this.options.direction;
		this.currentIndex = this.options.startIndex;
		this.lastIndex = this.elements.length -1;

		/* start index element */
		if (this.currentIndex > 0 && this.currentIndex < this.elements.length) {
			for (var i = 0; i < this.currentIndex; i++) {
				var currentElement = this.elements[i];
				var lastElement = this.elements[this.lastIndex];
				currentElement.inject(lastElement, 'after');
				this.lastIndex = i;
			}
		}

		/* if navigation is needed, activate it */
		if (this.options.navs) {
			this.fwd  = document.id(this.options.overallContainer).getElement(this.options.navs.fwd);
			this.bwd = document.id(this.options.overallContainer).getElement(this.options.navs.bk);
			if (this.fwd)  this.fwd.addEvent('click', this.slide.pass(1, this));
			if (this.bwd) this.bwd.addEvent('click', this.slide.pass(-1, this));
		}


		this.myFx = new Fx.Tween(this.options.thumbsContainer, { 
			property: (this.options.slideVertical ? 'margin-top':'margin-left'),
			link: 'ignore', 
			transition: this.options.transition,
			duration: this.options.duration
		});

		/* if autoSlide is not set, scoll on mouse wheel */
		if (this.options.mouseWheelNav && !this.options.autoSlide) {
			document.id(this.options.thumbsContainer).addEvent('mousewheel', function(ev) {
				new Event(ev).stop();
				this.slide(-ev.wheel);
			}.bind(this));
		}

		if (this.options.autoSlide && this.elements.length > this.options.itemsVisible) {
			this.startAutoSlide();
		}
	},

	/* resets the whole slider in case content changes */
	resetAll: function() {
		document.id(this.options.overallContainer).removeProperty('style');
		document.id(this.options.elementScrolled).removeProperty('style');
		document.id(this.options.thumbsContainer).removeProperty('style');
		this.stopAutoSlide();
		if( typeOf( this.fwd ) !== null ){
			this.fwd.dispose();
			this.bwd.dispose();
		}
		this.initialize();
	},

	/* slides elements */
	slide: function(direction) {

		if (this.started) return;

		this.direction = direction;

		var s = {};
		var fxDist = 0;
		if ( this.options.slideVertical ) {
			s['margin-top'] = this.direction == 1 ? 0 : -(this.elementHeight * this.options.elemsSlide);
			fxDist = this.direction == 1 ? -(this.elementHeight * this.options.elemsSlide) : 0;
		} else {
			s['margin-left'] = this.direction == 1 ? 0 :  -(this.elementWidth * this.options.elemsSlide);
			fxDist = this.direction == 1 ? -(this.elementWidth * this.options.elemsSlide): 0;
		}

		if( this.direction == -1 ){
			for (var i = 0; i < this.options.elemsSlide; i++) {
				var currentElement = this.elements[this.currentIndex];
				var lastElement = this.elements[this.lastIndex];
				lastElement.inject(currentElement, 'before');
				this.currentIndex = this.currentIndex == 0 ? this.elements.length - 1 : this.currentIndex - 1;
				this.lastIndex = this.currentIndex == 0 ? this.elements.length - 1 : this.currentIndex - 1;
			}
			$(this.options.thumbsContainer).setStyles(s);
		}

		this.started = true;

		this.myFx.start(fxDist).chain( function() {
			if (this.direction == 1) {
				document.id(this.options.thumbsContainer).setStyles(s);
				for (var i = 0; i < this.options.elemsSlide; i++) {
					var currentElement = this.elements[this.currentIndex];
					var lastElement = this.elements[this.lastIndex];
					currentElement.inject(lastElement, 'after');
					this.lastIndex = this.currentIndex;
					this.currentIndex = this.currentIndex >= this.elements.length-1 ? 0 : this.currentIndex + 1;
				}
			}
			this.started = false;
		}.bind(this));

		this.fireEvent('onChange', this.currentIndex);
	},

	/* starts auto sliding */
	startAutoSlide: function(){
		var duration = this.options.autoSlide + this.options.duration;
		var startIt  = this.slide.pass(this.direction|1, this);
		this.autoSlide = startIt.periodical(duration, this);
		this.elements.addEvents({
			'mouseenter' : function() {
				clearInterval(this.autoSlide);
			}.bind(this),
			'mouseleave' : function() {
				this.autoSlide = startIt.periodical(duration, this);
			}.bind(this)
		})
	},

	/* stops auto sliding */
	stopAutoSlide: function(){
		clearInterval(this.autoSlide);
		this.elements.removeEvents();
	}
})