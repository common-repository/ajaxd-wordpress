<?php

if(!defined('ABSPATH')){
	if(extension_loaded('zlib')){
		$z = strtolower(ini_get('zlib.output_compression'));
		if ($z == false || $z == 'off')
			ob_start('ob_gzhandler');
	}
	      $root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
      if (file_exists($root.'/wp-load.php')) {
          // WP 2.6
          require_once($root.'/wp-load.php');
      } else {
          // Before 2.6
          require_once($root.'/wp-config.php');
      }
	cache_javascript_headers();
}

$home = get_settings('siteurl');
?>
/*<script>*/
/*
AJAXed Wordpress
(C) Anthologyoi.com
*/

var aWP = function (){

		var _d=[]; /*_d is an object with an index of i that holds currently active "request" element information*/
		var _p=[]; /*_p is an object with an index of i that holds information that must exist between requests.*/
		var _a=[]; /*_a is an object that can be used throughout this function.*/
		var i; /*Currently processing element id*/
		var mrc = '';
		var force = 0;
		var timer = ''; /*Timer for smooth scrolling.*/
		var com_hide = '';
		<?php do_action('awp_js_vars');?>

	var $ = function(id) {
		return document.getElementById(id);
	};

	var pos = function(ele) {
		var e = $(ele);
		var etop = 0;
			if(e && e.style.display != 'none'){
				if (e.offsetParent) {
					etop = e.offsetTop
					while (e = e.offsetParent) {
						etop += e.offsetTop
					}
				}
			}
		return etop;

	};

	var get_current_scroll = function(){
		var cur_scroll = 0;

		if(document.body && document.body.scrollTop){

			cur_scroll = document.body.scrollTop;

		}else if(document.documentElement && document.documentElement.scrollTop){

			cur_scroll = document.documentElement.scrollTop;
		}

		return cur_scroll;
	};

	var get_form_data = function(i){
		var base = $(i).getElementsByTagName('input');
		var postobj = {};
		var x = base.length;
		var value = '';
		var name = '';
		var radios=[];

		for(y=0; y<x; y++){
			if(base[y].type != 'button'){
				if(base[y].type == 'text' || base[y].type == 'hidden' || base[y].type == 'password' || base[y].type == 'select'){
						value =  base[y].value;
						name = base[y].name;
				}else if(base[y].type == 'checkbox' || base[y].type == 'radio'){
					if (base[y].checked) {
						value =  base[y].value;
						name = base[y].name;
					}
				}
				if(name && value){
					postobj[name] = value;
				}
				name = value= null;
			}
		}
			base = $(i).getElementsByTagName('select');
			x = base.length;

			for(y=0; y<x; y++){
				postobj[base[y].name]= base[y].value;
			}

		base = $(i).getElementsByTagName('textarea');
		x = base.length;

		for(y=0; y<x; y++){
			postobj[base[y].name]= base[y].value;
		}
		return postobj;
	};

	var get_throbber = function (){

			var img = document.createElement('img');
			img.src="<?php echo WP_CONTENT_URL;?>/plugins/<?php echo AWP_BASE;?>/images/throbber<?php echo $awpall['throbber'];?>.gif";
			img.alt="Please hold now loading";
			img.id = "throbber"+i;
			img.className = "throbber";
			document.body.style.cursor='wait'
			if(_d[i].submit == 'TRUE'){

				try{$(_d[i].type+'_'+_d[i].main).parentNode.appendChild(img);}catch(e){}

			}else if(_d[i].this_page && _d[i].pagenum){

				try{$('awppage_'+_d[i].this_page+'_'+_d[i].main+'_link').appendChild(img);}catch(e){}

			}else if(_d[i].link_num){

				try{$('awp'+_d[i].type+'_link'+_d[i].link_num+'_'+_d[i].main).appendChild(img);}catch(e){}

			}else if(arguments[0] && $(arguments[0]) && arguments[1]){
				img.className =arguments[1];
				
				img.src="<?php echo WP_CONTENT_URL;?>/plugins/<?php echo AWP_BASE;?>/images/throbber"+arguments[2]+".gif";
				try{$(arguments[0]).parentNode.insertBefore(img, $(arguments[0]));}catch(e){}
			}else{

				try{$('awp'+_d[i].type+'_link_'+_d[i].main).appendChild(img);}catch(e){}

			}
	};

	var do_JS = function(e){
		var Reg = '(?:<script.*?>)((\n|.)*?)(?:</script>)';
		var match    = new RegExp(Reg, 'img');
		var scripts  = e.innerHTML.match(match);
		var doc = document.write;
		document.write = function(p){ e.innerHTML = e.innerHTML.replace(scripts[s],p)};
		if(scripts) {
			for(var s = 0; s < scripts.length; s++) {
				var js = '';
				var match = new RegExp(Reg, 'im');
				js = scripts[s].match(match)[1];
				js = js.replace('<!--','');
				js = js.replace('-->','');
				eval('try{'+js+'}catch(e){}');
			}
		}
		document.write = doc;
	};

	var link_text = function(id,newtext,toggle_text,style,style_toggle){

		if($(id)){
			var l = $(id);
			if(toggle_text){
				if(l.firstChild.data == toggle_text){
					l.firstChild.data = newtext;
					if(style) {l.className = style;}
				}else{
					l.firstChild.data = toggle_text;
					if(style_toggle) {l.className = style_toggle;}
				}
			}else{
				l.firstChild.data = newtext;
					if(style) {l.className = style;}
			}
		}
	};


	var move = function(id,moveto,mode){
		if($(id) && $(moveto) ){
			if(mode == 'sib'){
				$(id).parentNode.insertBefore($(moveto), $(id).nextSibling);
			}else{
				$(id).parentNode.insertBefore($(moveto),$(id));
			}
		}
	};
	return{

		init: function(){
			var preload_throbber = document.createElement('img');
			preload_throbber.src="<?php echo $home;?>/wp-content/plugins/<?php echo AWP_BASE;?>/images/throbber<?php echo $awpall['throbber'];?>.gif";
			var preload_throbber_large = document.createElement('img');
			preload_throbber_large.src="<?php echo $home;?>/wp-content/plugins/<?php echo AWP_BASE;?>/images/throbberlarge.gif";

		},

		addEvent: function(evn,fn,base){

			if (base.addEventListener){
				base.addEventListener(evn, fn, false);
				return true;
			} else if (base.attachEvent){
				var r = base.attachEvent("on"+evn, fn);
				return r;
			} else {
				return false;
			}
		},


		doit : function() {
			aWP.start.main(arguments);

		},

		update: function (){
			var e = $(i);
			_d[i].response = '';

			var nodes = _d[i].result.getElementsByTagName("var");
			if(nodes.length > 0){
				for (j=0; j<nodes.length; j++) {
					if(nodes.item(j).getAttribute('name') && nodes.item(j).firstChild.data){
						_d[i][nodes.item(j).getAttribute('name')] = nodes.item(j).firstChild.data;
					}
				}
			}

			nodes = _d[i].result.getElementsByTagName("response");
			if(nodes.length > 0){
				for (j=0; j<nodes.length; j++) {
					if(nodes.item(j).getAttribute('name')){
						_d[i][nodes.item(j).getAttribute('name')] = nodes.item(j).firstChild.data;
					}else{
						_d[i].response += nodes.item(j).firstChild.data
					}
				}
			}

			nodes = _d[i].result.getElementsByTagName("action");
			if(nodes.length > 0){
				for (j=0; j<nodes.length; j++) {
					if(nodes.item(j).firstChild.data){
						if(nodes.item(j).getAttribute('name')){
							_d[i][nodes.item(j).getAttribute('name')] = nodes.item(j).firstChild.data;
						}else{
							eval(nodes.item(j).firstChild.data);
						}
					}
				}
			}

			if(!_d[i].update_next){
				e.innerHTML = _d[i].response;
				do_JS(e);
				aWP.toggle.main();
			}else{
				eval(_d[i].update_next+'();');
			}
		},

		start: {

			main: function(){

				var args = arguments[0];
				var temp = args[0];
				var postobj = {};

				if(!temp.i){

					if((!temp.id && !temp.primary) || !temp.type){
						return false;
					}else{
						if(!temp.primary){
							temp.primary = 'id';
						}
					}

					i = 'awp'+temp.type+'_'+temp[temp.primary];

				}else{

					i = temp.i;
					temp.i = null;

				}

				if(!$(i)){
					if(_d[i].ths && _d[i].ths.href)
						window.location(_d[i].ths.href);
					return false;
				}else{
					e = $(i);
				}

				if(!_p[i]){
					_p[i] = [];
				}

				_d[i] = [];
				_d[i] = temp;
				_d[i].main = _d[i][_d[i].primary];
				temp = null;

				if(_d[i].submit == 'TRUE'){
					if($(_d[i].type+_d[i].main)){
						try{$(_d[i].type+_d[i].main).disabled = true;} catch(e){}
					}
					postobj = get_form_data(i);
					_d[i].force = 1;
					postobj['id'] = _d[i].id;
				}else{
					postobj['id'] = _d[i].id;
					postobj.main = _d[i].main;
				}

				postobj['type'] =_d[i].type;

				if(aWP.start[_d[i].type])
					postobj = aWP.start[_d[i].type](postobj);

				if(!postobj)
					return false;

				var n=0;

				if(e.innerHTML)
					n = e.innerHTML.length;

				if (n==0 || _d[i].force == 1){

					<?php do_action('pick_ajax','basic');?>

				}else{

					return aWP.toggle.main();

				}


			},

			<?php do_action('awp_js_start');?>
			dummy: function(){}
		},

		toggle: {
			main: function (){
				var e = $(i);
				var style = $(i).style.display; /*Otherwise we need to get it several times.*/
				if(style != 'none' && style != 'block'){ /*If the element is displayed, but not explicitly set.*/
					$(i).style.display = 'block'; /*Explicitly set it.*/
				}
				var winHeight = window.innerHeight;
				if(!winHeight){
					//yet another IE fix
					winHeight = document.documentElement.clientHeight;
				}

				if(document.getElementById('throbber'+i)){
					setTimeout("try{document.body.style.cursor='normal';document.body.style.cursor='auto';}catch(e){}",500);
					setTimeout("try{document.getElementById('throbber"+i+"').parentNode.removeChild(document.getElementById('throbber"+i+"'));}catch(e){}",500);
					setTimeout("try{document.getElementById('throbber"+i+"').parentNode.removeChild(document.getElementById('throbber"+i+"'));}catch(e){}",500);
				}

				if(aWP.toggle[_d[i].type]){
					aWP.toggle[_d[i].type](e,style);
				}else{
					var toggle = 0;
					if(!_d[i].force){

							link_text('awp'+_d[i].type+'_link'+'_'+_d[i].main, _p[i].show, _p[i].hide,'awp' + _d[i].type + '_link','awp' + _d[i].type + '_link_hide');

						toggle = 1;
					}

					if( $(i).style.display != 'block' || toggle){
						aWP.toggle.pick_switch();
					}

				}

<?php if($awpall[scrolling_type] == ''){?>
				if(_d[i].focus){

 					setTimeout("aWP.toggle.smooth_scroll('"+_d[i]['focus']+"',0);",250);
				}else if(pos(i) > 0 && !_d[i].force && !_d[i].no_jump){
					aWP.toggle.smooth_scroll(i,-1*(winHeight/4));
				}
<?php }elseif ($awpall[scrolling_type] == 'abrupt'){?>
				if(_d[i].focus){
					setTimeout("location.href= '#"+_d[i].focus+"';",100);
				}else if(pos(i) > 0 && !_d[i].force && !_d[i].no_jump){
					try{window.parent.scrollTo(0,pos(i) - winHeight/4);}catch(e){}
				}
<?php }?>

			},

<?php		do_action('awp_js_toggle');?>

			pick_switch:function(){

				<?php do_action('awp_effects');?>

			},

			smooth_scroll: function(scrolluntil,extra){
				var end = pos(scrolluntil) + extra;
				var cur_scroll = get_current_scroll();
				var step = 50;
				var scrollto = 0;
				var val = cur_scroll - end;
				
				clearTimeout(aWP.timer);

				if(Math.abs(val) > 50 && _p[i].scrollval != val){
					if(Math.abs(val) > 5000){step = 750;} else
					if(Math.abs(val) > 2500){step = 500;} else
					if(Math.abs(val) > 1000){step = 200;} else
					{ step = 50; }
					if (val > 0){
						scrollto =  cur_scroll - step;
					}else if (val < 0){
						scrollto =  cur_scroll + step;
					}
					_p[i].scrollval = val;

					try{window.parent.scrollTo(0,scrollto);}catch(e){}
					aWP.timer = setTimeout("aWP.toggle.smooth_scroll('"+scrolluntil+"',"+extra+")",100);
				}

			}
		},

		<?php do_action('awp_js_core');?>

		complete: function (){

			aWP.finish.main(_d[i].type);

		},

		finish: {
			main: function(){

				try{$('throbber'+i).parentNode.removeChild($('throbber'+i));}catch(e){}

				if(aWP.finish[_d[i].type])
					aWP.finish[_d[i].type]();

			},

<?php 		do_action('awp_js_finish');?>

			dummy: function(){}
		}
	}
}();

aWP.init();

<?php do_action('aWP_JS');?>