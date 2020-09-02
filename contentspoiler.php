<?php

/**
 * @package Content Plugin ContentSpoiler for Joomla! 3.x
 * @version $Id: plg_contentspoiler.php version 1.4
 * @author Dmitry Borets
 * @copyright (C) 2016-2017 - Dmitry Borets
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

defined('_JEXEC') or die;

class PlgContentContentSpoiler extends JPlugin
{

	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer')
		{
			return true;
		}

		// Simple performance check to determine whether bot should process further
		if (strpos($article->text,'{spoiler') === false)
		{
			return true;
		}
		
		// Expression to search for (positions)
		$regex = '/\{spoiler(?!.*\{spoiler)(\s?)(?(1)(.*?))\}(.*?)\{\/spoiler\}/is';

		$cnt = 0;

		// Find all instances of plugin and put in $matches for loadposition
		// $matches[0] is full pattern match, $matches[1] is the position
		preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

		if ($matches) {
			$document = JFactory::getDocument();
			$document->addStyleSheet(JURI::base() . 'plugins/content/contentspoiler/css/contentspoiler.css');
			$document->addStyleDeclaration($this->setUpStyles());
			$document->addScript(JURI::base() . 'plugins/content/contentspoiler/js/contentspoiler.js');
		}

		// No matches, skip this
		while ($matches)
		{

			foreach ($matches as $match)
			{
				$options = explode(',', trim($match[2]));

				// We may not have an id/name for the spoiler so fall back to the default sequential numbering - WRONG!
				// UPD in ver 1.2: Sequential numbering can generate non-unique IDs on a page, so - switching to unique IDs
				if (strlen($options[0]) == 0)
				{
					$uid = uniqid("", true);
					$id = $name = "spoiler-".$uid;
				} else {
					$id = $name = $options[0];
				}

				$img_class = ($this->params->get('show_twister') == "1") ? "_img" : "";

				// If the labels are not specified, use &nbsp; for the proper cation height
				$label_open = (array_key_exists(1, $options)) ? $options[1] : "&nbsp;";
				$label_close = (array_key_exists(2, $options)) ? $options[2] : "&nbsp;";

				// Check for access level restrictions
				if (array_key_exists(3, $options))
				{
					if (strtolower($options[3]) == "true")
					{
						$user = JFactory::getUser();
						$display_inner_text = !($user->guest);
	//					$auth_levels = $options[3].$user->name.implode($user->getAuthorisedGroups(),",");
					} else {
						$display_inner_text = true;
					}
				} else {
//					$auth_levels = "";
					$display_inner_text = true;
				}

				// NEW in v1.4: Show 'see less/close' caption on top, bottom or both - parameter #4, 'top' - default, 'bottom', 'both'
				if (!(array_key_exists(4, $options))) $options[4] = "top";
				$hcl = strtolower($options[4]);
				switch ($hcl) {
					case "top":
						$header_close_location = 0;
						break;
					case "bottom":
						$header_close_location = 1;
						break;
					case "both":
						$header_close_location = 2;
						break;
					default:
						$header_close_location = 0;
				}

				// We may not have a label for the spoiler, but show the twister anyway, if required
				$label_show = sprintf('<p id="%s_show_label" class="show_label%s">%s</p>',$id,$img_class,$label_open);
				$label_hide = sprintf('<p id="%s_hide_label" class="hide_label%s">%s</p>',$id,$img_class,$label_close);
				$label_hide_b = sprintf('<p id="%s_hide_label_b" class="hide_label%s">%s</p>',$id,$img_class,$label_close);
				$bottom_hide = ($header_close_location <> 0) ? sprintf('<div class="spoiler_label spl" onclick="sh(\'%s\')">%s</div>',$id,$label_hide_b) : "";
				$label = $label_show.(($header_close_location <> 1) ? $label_hide : "");
				$inner_text = ($display_inner_text) ? trim($match[3]) : '<span class="restricted">Only registered users can see this content.</span>';
				$output = sprintf('<div class="contentspoiler"><div class="spoiler_label spl" onclick="sh(\'%s\')">%s</div><div class="spoiler_inner spi" id="%s" name="%s">%s</div>%s</div>',
									$id,
									$label,
									$id,
									$name,
									$inner_text,
									$bottom_hide);
	
				// We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
//				$o = preg_quote($match[0]);
//				$article->text = preg_replace("|$o|", addcslashes($output, '\\$'), $article->text, 1);
				$o = $match[0];
				$article->text = str_replace($o, $output, $article->text);
				preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);
			}
		}
		              
	}

	function setUpStyles()
	{
		$bd_c = $this->params->get('border_color', "#000");
		$bd_o = $this->params->get('border_opacity', "0.3");
		$bk_c = $this->params->get('header_background_color', "#000");
		$bk_o = $this->params->get('header_background_opacity', "0.3");
		$ruo_t = $this->params->get('registered_only_style', "0");
		$ruo_c = $this->params->get('registered_only_color', "#555");
		
		$bd_c_rgb = _hex2rgb($bd_c);
		$bk_c_rgb = _hex2rgb($bk_c);
		$ruo_c_rgb = _hex2rgb($ruo_c);
		
		switch ($ruo_t) {
			case 0: 
				$ruo_font = "font-style: normal; font-weight: normal;";
				break;
			case 1:
				$ruo_font = "font-style: normal; font-weight: bold;";
				break;
			case 2:
				$ruo_font = "font-style: italic; font-weight: normal;";
				break;
			case 3:
				$ruo_font = "font-style: italic; font-weight: bold;";
				break;
		}
		
		$styles_str = "div.contentspoiler {
			overflow: hidden;
			padding: 0px;
			border: 1px solid %s;
			border: 1px solid rgba(%s,%s);
			-webkit-box-shadow: 0 1px 3px rgba(%s,0.1);
			-moz-box-shadow: 0 1px 3px rgba(%s,0.1);
			box-shadow: 0 1px 3px rgba(%s,0.1);
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			border-radius: 3px;
		}
		.contentspoiler div.spoiler_label {
			padding: 0px 0px 0px 0px;
			background-color: rgba(%s,%s); 
		}
		.contentspoiler .restricted {
			color: rgb(%s);
			%s;
		}
		}";
		$styles = sprintf($styles_str, $bd_c, $bd_c_rgb, $bd_o, $bd_c_rgb, $bd_c_rgb, $bd_c_rgb, $bk_c_rgb, $bk_o, $ruo_c_rgb, $ruo_font);
		return $styles;
	}
}

function _hex2rgb($hex) {
   $hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   return implode(",", $rgb); // returns the rgb values separated by commas
}