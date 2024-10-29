<?php
/*                                                                   
Plugin Name: AddPoll
Version: 0.0.2
Plugin URI: http://www.addpoll.com/
Author: AddPoll
Author URI: http://www.addpoll.com/
Plugin Description: Fast and easy add polls, surveys and forms to your WordPress blog for free.
*/

/*
    This program is free software; you can redistribute it
    under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

class AddPollWidget extends WP_Widget {
	public function __construct() {
		parent::__construct(
	 		'AddPollWidget',
			'AddPollWidget',
			array('description' => 'Display Addpoll.com Content')
		);
	}
	
	public function form($instance) {
		echo '<div><div style="padding-bottom: 5px;">Enter AddPoll.com sharing code:</div><input type="text" name="' . $this->get_field_name('code') . '" value="' . $instance['code'] . '" class="widefat" /></div>';
	}
	
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['code'] = strip_tags($new_instance['code']);
		
	return $instance;
	}

	public function widget($args, $instance) {
		echo '<li id="' . $this->get_field_id('code') . '" class="widget-container widget_addpoll">' . addpoll_tag_parse($instance['code']) . '</li>';
	}
}

	add_action('widgets_init', create_function('', 'register_widget("AddPollWidget");'));

	function addpoll_tag_parse($content) {
		$_matches = array();
		preg_match_all('/\[addpoll\s?([a-z]+)id="?([0-9]+)"?(\surl=([^\]^\s]+))?(\swidth=([0-9]{0,4}))?(\sheight=([0-9]{0,4}))?(\sautoResize=(true|false))?\]/i', $content, $_matches);
		
		if ( !isset($_matches[1]) ) {
			return $content;
		}
		
		foreach ( $_matches[1] AS $index=>$section ) {
			$f = 'addpoll_' . $section . '_parse';
			
			if ( !function_exists($f) ) {
				continue;
			}
			
			$content = $f($content, $_matches, $index);
		}
		
	return $content;
	}
	
	function addpoll_poll_parse($content, $_matches, $index) {
		if ( !isset($_matches[2][$index]) ) {
			return $content;
		}
	
		$id = (int)$_matches[2][$index];
		
		$width = isset($_matches[6][$index]) && !empty($_matches[6][$index]) ? (int)$_matches[6][$index] : 0;
		
	return str_replace($_matches[0][$index], '<script type="text/javascript">
	var adpCustomParams' . $id . ' = [];
	adpCustomParams' . $id . '[\'width\'] = ' . $width . '; // Your flash poll custom width. Leave 0 for default values
</script>' . "<script type=\"text/javascript\" id=\"adpEmbed-" . $id . "\">
		(function(doc) {
			var sc = doc.createElement('script');
			sc.setAttribute('src', ('https:' == doc.location.protocol ? 'https://' : 'http://') + 'www.addpoll.com/poll-" . $id . ".js');
			var el = doc.getElementById('adpEmbed-" . $id . "') || doc.getElementsByTagName('script')[0];

			var div = doc.createElement('div');
			div.setAttribute('id', 'AddPollContainer-" . $id . "');
			el.parentNode.insertBefore(div, el);

			el.parentNode.insertBefore(sc, el);
		}(document));
	</script>", $content);
	}
	
	function addpoll_survey_parse($content, $_matches, $index) {
		if ( !isset($_matches[2][$index]) ) {
			return $content;
		}
	
		$id = (int)$_matches[2][$index];
		
		if ( !isset($_matches[4]) ) {
			return $content;
		}
		
		$url = (string)$_matches[4][$index];
		$width = isset($_matches[6][$index]) && !empty($_matches[6][$index]) ? (int)$_matches[6][$index] : 0;
		$height = isset($_matches[8][$index]) && !empty($_matches[8][$index]) ? (int)$_matches[8][$index] : 0;
		$autoResize = isset($_matches[10][$index]) && !empty($_matches[10][$index]) ? (string)$_matches[10][$index] : 'true';
		
	return str_replace($_matches[0][$index], '<div id="AddPollSurveyContainer-' . $id . '"></div>
	<script type="text/javascript" charset="utf-8">' . 
	"if ( typeof adpSurveyCustomParams == 'undefined' ) { var adpSurveyCustomParams = []; }
	adpSurveyCustomParams[" . $id . "] = [];
	adpSurveyCustomParams[" . $id . "]['width'] = " . $width . "; // Your custom width. Leave 0 for default values
	adpSurveyCustomParams[" . $id . "]['height'] = " . $height . "; // Your custom height. Leave 0 for default values
	adpSurveyCustomParams[" . $id . "]['url'] = '" . $url . "';
	adpSurveyCustomParams[" . $id . "]['autoResize'] = " . $autoResize . ";
	</script>" . 
	'<script type="text/javascript" id="AddPollEmbedScript-' . $id . '">' .
	"(function(doc) {
		var sc = doc.createElement('script');
		sc.setAttribute('src', ('https:' == doc.location.protocol ? 'https://' : 'http://') + 'www.addpoll.com/addpoll-survey.js');
		sc.onload = sc.onreadystatechange = function() {
			var ready = this.readyState; 
			if(ready){if(ready!='complete'){if(ready!='loaded'){return;}}}
			AddpollSurvey.show(" . $id . ");
		};
		var el = doc.getElementById('AddPollSurveyContainer-" . $id . "') || doc.getElementsByTagName('script')[0];
		el.parentNode.insertBefore(sc, el);
	})(document);
	</script>", $content);
	}
	
	function addpoll_quiz_parse($content, $_matches, $index) {
		return addpoll_survey_parse($content, $_matches, $index);
	}
	
	function addpoll_form_parse($content, $_matches, $index) {
		return addpoll_survey_parse($content, $_matches, $index);
	}
	
	add_filter('the_content', 'addpoll_tag_parse');
?>