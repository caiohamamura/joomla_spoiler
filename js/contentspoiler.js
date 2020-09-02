/**
 * @package Content Plugin ContentSpoiler for Joomla! 3.x
 * @version $Id: version 1.4
 * @file: contentspoiler.js
 * @author Dmitry Borets
 * @copyright (C) 2016-2017 - Dmitry Borets
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

function sh( id ) {
	var item = document.getElementById(id);
	var style = window.getComputedStyle(item);
	var label_show = document.getElementById(id+"_show_label");
	var label_hide = document.getElementById(id+"_hide_label");
	var label_hide_b = document.getElementById(id+"_hide_label_b");
	if ( style.maxHeight == "0px" ) {
		item.style.maxHeight = item.scrollHeight + "px";
		if (label_show != null) {
			label_show.style.display = "none";
			label_show.style.visibility = "hidden";
		}
		if (label_hide != null) {
			label_hide.style.display = "inline";
			label_hide.style.visibility = "visible";
		}
		if (label_hide_b != null) {
			label_hide_b.style.display = "inline";
			label_hide_b.style.visibility = "visible";
		}
	} else {
		item.style.maxHeight = "0px";
		if (label_show != null) {
			label_show.style.display = "inline";
			label_show.style.visibility = "visible";
		}
		if (label_hide != null) {
			label_hide.style.display = "none";
			label_hide.style.visibility = "hidden";
		}
		if (label_hide_b != null) {
			label_hide_b.style.display = "none";
			label_hide_b.style.visibility = "hidden";
		}
	}
}