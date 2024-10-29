
/**
*	Copyrighted 2007 to:
*	Aaron Huran http://anthologyoi.com
*	The code is released under a Creative Commons Liscense
*	(Attribution-NonCommercial-ShareAlike 2.0)
*	TERMS: (Removal this section indicates agreement with these Terms.)
*	For Personal (non-distribution) this notice (sans Terms section) must remain.
*	For Distribution this notice must remain and attribution through
*	a publically accessible "followable" link on applicable information/download page is required.
*	No Commercial use without prior approval.
**/

var AOI_eff = function () {
	var delay = 100;
	var _d = [];
	var $ = function (id) {
		return document.getElementById(id);
	};

	var hideChildren = function(id){
		var c = document.getElementById(id).getElementsByTagName('div');

		for(x=0; x < c; x++){

			if(c.parentNode.id == id){
// 				console.log(c.parentNode.id);
			}
// 		console.log(id, c, document.getElementById(c).parentNode.id)
		}

	}

	return {
		start: function () {
			var	i = arguments[0];

			if (!_d[i]) {
				_d[i] = arguments[1] || [];

				if (!_d[i].queue) {
					_d[i].queue = '';
				}

				if (!_d[i].mode) {
					if ($(i).style.display === 'block') {
						_d[i].mode = 'hide';
					} else {
						_d[i].mode = 'show';
					}
				}

				if(_d[i].hideChildren)
					hideChildren(_d[i].hideChildren);
				AOI_eff.setup(i);
			}
		},
		setup: function (i) {

			if ($(i)) {
				if (_d[i].mode === 'hide') {
					_d[i].step = -10;

					AOI_eff.ready(i, 'hide');
				} else if (_d[i].mode === 'show') {

					_d[i].step = 10;
					AOI_eff.ready(i, 'show');
				} else if (_d[i].mode === 'other') {
					_d[i].step = 10;
					AOI_eff.ready(i, 'other');
				}

				if (!_d[i].delay) {
					_d[i].delay = delay;
				}

				AOI_eff.doit(i);
			} else {
				return false;
			}
		},
		ready: function (i, m) {
			var e = $(i).style;
			switch (_d[i].eff) {
				case 'Expand':

					_d[i]['overflow'] = e.overflow;
					_d[i]['lineHeight'] = e.lineHeight;
					_d[i]['letterSpacing'] = e.letterSpacing;

					if (m === 'show') {
						e.overflow = 'hidden';
						e.lineHeight = '300%';
						e.letterSpacing = '1em';
					}

					break;
				case 'SlideUp':
					_d[i]['height'] = $(i).offsetHeight;
					if (m === 'show') { /*We need an object to be displayed to retrieve height.*/
						e.position='absolute'; /*Pull the element out of its default location*/
						e.visibility='hidden'; /*Hide the element*/
						e.display='block';/*"Display" the hidden element*/
						_d[i]['height'] = $(i).offsetHeight;
						e.visibility=''; /*Show it*/
						e.position='relative'; /*Put it back where it was*/
						e.height = '0px'; /*shrink it for the effect.*/
					}
					e.overflow="hidden";
					break;

				case 'ScrollLeft':
					_d[i]['marginLeft'] = e.marginLeft;
					if (m === 'show') {
						e.marginLeft = 80+'%';
					}
					break;
				case 'Fade':
					e.zoom = 1;/*IE fix*/
					e.backgroundColor = _d[i].background;
					if (m === 'show') {
						e.filter = 'alpha(opacity=0)';
						e.opacity = 0;
					}
					break;
			}

			if (m === 'show') {
				e.display = 'block';
			}
		},
		doit: function (i) {
			var e = $(i).style; /**/
			var m = _d[i].mode;
			var s = _d[i].step;
			var v = 0;

			if ( _d[i].step !== 0  ) {
				switch (_d[i].eff) {
					case 'Expand':
						if 	( m === 'hide' ) {
							v = (100+ (10+s)*20); /*IE fix*/
							e.lineHeight = v+'%';
							e.letterSpacing = ((10+s)*3)+'px';
							_d[i].step += 1;
						} else {
							v = (300 - (10-s)*20);/*IE fix*/
							e.lineHeight = v+'%';
							e.letterSpacing = s*2+'px';
							_d[i].step -= 1;
						}
					break;

					case 'SlideUp':
						if 	( m === 'hide' ) {
							e.height =  Math.floor( _d[i]['height']*s*-0.1)+'px';
							_d[i].step += 1;
						} else {
							e.height = Math.floor( _d[i]['height']*(10-s)*0.1)+'px';
							_d[i].step -= 1;
						}
					break;

					case 'ScrollLeft':

						if 	( m === 'hide' ) {
							if ((!window.innerHeight && s < -3) || window.innerHeight ) {/*IE fix*/
								e.marginLeft=((10 + s)*10)+'%';
							}
							_d[i].step += 1;
						} else {
							e.marginLeft=(s*8)+'%';
							_d[i].step -= 1;
						}
					break;

					case 'Fade' :
						if 	( m === 'hide' ) {
							e.opacity = (s)/-10;
							e.filter = 'alpha(opacity='+(s*-10)+')';
							_d[i].step+= 1;
						} else {
							e.filter = 'alpha(opacity='+((10-s)*10)+')';
							e.opacity = (10 - s)/10;
							_d[i].step -= 1;
						}
						break;
					default:
						_d[i].step = 0;
						break;

				}
					setTimeout("AOI_eff.doit('"+i+"');",  _d[i].delay); /*Call next frame after delay.*/
			} else {
				AOI_eff.finish(i); /*Clean up*/
			}
		},
		finish: function (i) {
			var e = $(i).style; /**/
			if ( _d[i].mode === 'hide' ) {
				e.display = 'none';
			}

			switch (_d[i].eff) {
				case 'Expand':
					if ( _d[i]['overflow'] ) {
						e.overflow = _d[i]['overflow'];
					}

					e.lineHeight ="normal";
					e.letterSpacing ="normal";

					break;
				case 'SlideUp':
					e.height = _d[i]['height']+'px';
					if(window.innerHeight){ /*IE has problems setting height to auto.*/
						e.height = 'auto';}
					e.overflow="visible";
					break;
				case 'ScrollLeft':
					e.marginLeft = _d[i]['marginLeft'] ;
					break;
				default :
					e.opacity = 10;
					e.filter = 'alpha(Opacity=100)';
			}

			if ( _d[i].queue.length > 0 ) {/* Checks to see if there is another effect*/
				var val;

				val = _d[i].queue.shift().split('::'); /*Gets values for first queue'd item*/
				if (val[2]) { /*Sets effect*/
					_d[i].eff = val[2];
				}
				if (val[3]) { /*Sets delay*/
					_d[i].delay = val[3];
				}
				dtemp = _d[i];
				_d[i] = null;
				_d[val[1]] = [];
				_d[val[1]] = dtemp;
				_d[val[1]].mode = val[0];


				AOI_eff.setup(val[1]);
			} else {
				_d[i] = null;
			}
		}
	};
}();