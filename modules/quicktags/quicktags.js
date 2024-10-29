/**
 Originally by Alex King
 http://www.alexking.org/
 edit toolbar used without permission
 This file has been edited from its original state
 The original author should not be held responsible for any bugs.
  **/

var aWP_qt = function(){
 var element =[];
	var edButtons = [];
	var edOpenTags = [];
	var suffix = '';

	var $ = function(id) {
		return document.getElementById(id);
	};

	var _$ = function(id) {

		if(!element[id]){
			if(!suffix){
				base = document.getElementById('awpsubmit_commentform_'+id).getElementsByTagName('textarea');
				x = base.length;
				for(i=0; i<x; i++){
					if(base[i].name = 'comment'){
						element[id] = base[i];
						i = x;
					}
				}
			}else{
				element[id] = document.getElementById('comment'+suffix);
			}
		}
		return element[id];
	};


	function edButton(i, display, tagStart, tagEnd, access, open) {
		this.id = i;				// used to name the toolbar button
		this.display = display;		// label on button
		this.tagStart = tagStart; 	// open tag
		this.tagEnd = tagEnd;		// close tag
		this.access = access;		// access key
		this.open = open;			// set to -1 if tag does not need to be closed
	}

	function zeroise(number, threshold) {
		// FIXME: or we could use an implementation of printf in js here
		var str = number.toString();
		if (number < 0) { str = str.substr(1, str.length); }
		while (str.length < threshold) { str = "0" + str; }
		if (number < 0) { str = '-' + str; }
		return str;
	}

	var now = new Date();
	var datetime = now.getUTCFullYear() + '-' +
	zeroise(now.getUTCMonth() + 1, 2) + '-' +
	zeroise(now.getUTCDate(), 2) + 'T' +
	zeroise(now.getUTCHours(), 2) + ':' +
	zeroise(now.getUTCMinutes(), 2) + ':' +
	zeroise(now.getUTCSeconds() ,2) +
	'+00:00';

	edButtons[edButtons.length] = new edButton('ed_strong','b','<strong>','</strong>','b');
	edButtons[edButtons.length] = new edButton('ed_em','i','<em>','</em>','i');
	edButtons[edButtons.length] = new edButton('ed_link','link','','</a>','a'); // special case
	edButtons[edButtons.length] = new edButton('ed_block','b-quote','\n\n<blockquote>','</blockquote>\n\n','q');
	edButtons[edButtons.length] = new edButton('ed_img','img','','','m',-1); // special case
	edButtons[edButtons.length] = new edButton('ed_ul','ul','<ul>\n','</ul>\n\n','u');
	edButtons[edButtons.length] = new edButton('ed_ol','ol','<ol>\n','</ol>\n\n','o');
	edButtons[edButtons.length] = new edButton('ed_li','li','\t<li>','</li>\n','l');
	edButtons[edButtons.length] = new edButton('ed_code','code','<code>','</code>','c');
	edButtons[edButtons.length] = new edButton('ed_quote','Quote','<q>','</q>','');


	function edAddTag(button) {
		if(!edOpenTags[id]){
			edOpenTags[id] = [];}

		if (edButtons[button].tagEnd !== '') {
			edOpenTags[id][edOpenTags[id].length] = button;
			$(edButtons[button].id + '_'+ id).value = '/' + $(edButtons[button].id + '_'+ id).value;
		}
	};

	function edRemoveTag(button) {

		for (i = 0; i < edOpenTags[id].length; i++) {
			if (edOpenTags[id][i]== button) {
				edOpenTags[id].splice(i, 1);
				$(edButtons[button].id + '_'+ id).value = 		$(edButtons[button].id + '_'+ id).value.replace(/\//g, '');
			}
		}
	};

	function edCheckOpenTags(button) {
		if(!edOpenTags[id]){
			edOpenTags[id] = [];}

		var tag = 0;
		for (i = 0; i < edOpenTags[id].length; i++) {
			if (edOpenTags[id][i] == button) {
				tag++;
			}
		}
		if (tag > 0) {
			return true; // tag found
		}
		else {
			return false; // tag not found
		}
	};

		function edInsertContent  (myValue) {
		var myField = _$(id);
			//IE support
			if (document.selection) {
				myField.focus();
				sel = document.selection.createRange();
				sel.text = myValue;
				myField.focus();
			}
			//MOZILLA/NETSCAPE support
			else if (myField.selectionStart || myField.selectionStart == '0') {
				var startPos = myField.selectionStart;
				var endPos = myField.selectionEnd;
				myField.value = myField.value.substring(0, startPos)
						+ myValue
						+ myField.value.substring(endPos, myField.value.length);
				myField.focus();
				myField.selectionStart = startPos + myValue.length;
				myField.selectionEnd = startPos + myValue.length;
			} else {
				myField.value += myValue;
				myField.focus();
			}
		};

	return {
		edCloseAllTags: function (the_id, suf) {
			if( the_id){
				id = the_id;}
		if (suf){
		suffix = suf;}
			if(!edOpenTags[id]){
				edOpenTags[id] = [];}

			var count = edOpenTags[id].length;
			for (o = 0; o < count; o++) {
				aWP_qt.edInsertTag(id,edOpenTags[id][edOpenTags[id].length - 1]);
			}
		},

	// insertion code
		edInsertTag: function (the_id,i, suf) {
		if (the_id){
		id = the_id;}
		if (suf){
		suffix = suf;}
		var myField = _$(id);

			//IE support
			if (document.selection) {
				myField.focus();
			sel = document.selection.createRange();
				if (sel.text.length > 0) {
					sel.text = edButtons[i].tagStart + sel.text + edButtons[i].tagEnd;
				}
				else {
					if (!edCheckOpenTags(i) || edButtons[i].tagEnd === '') {
						sel.text = edButtons[i].tagStart;
						edAddTag(i);
					}
					else {
						sel.text = edButtons[i].tagEnd;
						edRemoveTag(i);
					}
				}
				myField.focus();
			}
			//MOZILLA/NETSCAPE support
			else if (myField.selectionStart || myField.selectionStart == '0') {
				var startPos = myField.selectionStart;
				var endPos = myField.selectionEnd;
				var cursorPos = endPos;
				var scrollTop = myField.scrollTop;
				if (startPos != endPos) {
					myField.value = myField.value.substring(0, startPos)
							+ edButtons[i].tagStart
							+ myField.value.substring(startPos, endPos)
							+ edButtons[i].tagEnd
							+ myField.value.substring(endPos, myField.value.length);
					cursorPos += edButtons[i].tagStart.length + edButtons[i].tagEnd.length;
				}
				else {
					if (!edCheckOpenTags(i) || edButtons[i].tagEnd === '') {
						myField.value = myField.value.substring(0, startPos)
								+ edButtons[i].tagStart
								+ myField.value.substring(endPos, myField.value.length);
						edAddTag(i);
						cursorPos = startPos + edButtons[i].tagStart.length;
					}
					else {
						myField.value = myField.value.substring(0, startPos) +
										edButtons[i].tagEnd  +
										myField.value.substring(endPos, myField.value.length);
						edRemoveTag(i);
						cursorPos = startPos + edButtons[i].tagEnd.length;
					}
				}
				myField.focus();
				myField.selectionStart = cursorPos;
				myField.selectionEnd = cursorPos;
				myField.scrollTop = scrollTop;
			}
			else {
				if (!edCheckOpenTags(i) || edButtons[i].tagEnd === '') {
					myField.value += edButtons[i].tagStart;
					edAddTag(i);
				}
				else {
					myField.value += edButtons[i].tagEnd;
					edRemoveTag(i);
				}
				myField.focus();
			}
		},

		edInsertLink: function(the_id, i, suf) {
		if (the_id){
			id = the_id;
		}		if (suf){
		suffix = suf;}

			if (!edCheckOpenTags(i)) {
				var URL = prompt('Enter the URL' ,'http://');
				if (URL) {
					edButtons[i].tagStart = '<a href="' + URL + '">';
					aWP_qt.edInsertTag(the_id,i);
				}
			}
			else {
				aWP_qt.edInsertTag(the_id,i);
			}
		},

		edInsertImage: function (the_id,myField, suf) {
			if (the_id){
				id = the_id;
			}

			if (suf){
				suffix = suf;
			}

			var myValue = prompt('Enter the URL of the image', 'http://');
			if (myValue) {
				myValue = '<img src="'
						+ myValue
						+ '" alt="' + prompt('Enter a description of the image', '')
						+ '" />';
				edInsertContent(myValue);
			}
		},


		edInsertSmilie: function (the_id,smilie, suf) {
			if (smilie) {
				if (the_id){
					id = the_id;
				}
				if (suf){
					suffix = suf;
				}
				edInsertContent(smilie);
			}
		}
	}
}();