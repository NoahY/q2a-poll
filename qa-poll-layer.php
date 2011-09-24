<?php

	class qa_html_theme_layer extends qa_html_theme_base {
		
		function doctype(){
			if(qa_post_text('poll_vote')) {
				return;
			}
			if (qa_opt('poll_enable')) {
				global $qa_request;
				if($this->template == 'questions' || $qa_request == 'polls') {
					$this->content['navigation']['sub']['polls'] = array(
					  'label' => qa_opt('poll_page_title'),
					  'url' => qa_path_html('polls'),
					);
				}
				else if($this->template == 'ask') {
					$this->content['form']['fields'][] = array(
						'label' => qa_opt('poll_checkbox_text'),
						'tags' => 'NAME="is_poll" ID="is_poll" onclick="jQuery(\'#qa-poll-ask-div\').toggle()"',
						'type' => 'checkbox',
					);
					$this->content['form']['fields'][] = array(
						'note' => '<div id="qa-poll-ask-div" style="display:none"><p class="qa-form-tall-label"><input type="checkbox" name="poll_multiple">'.qa_opt('poll_multiple_text').'</p><p class="qa-form-tall-label">'.qa_opt('poll_answers_text').'</p><input type="input" class="qa-poll-answer-text" class="qa-poll-answer-text" name="poll_answer_1">&nbsp;<input type="button" class="qa-poll-answer-add" value="+" onclick="addPollAnswer(poll_answer_index)"></div>',
						'type' => 'static',
					);
				}
				$poll_array = qa_db_read_all_assoc(
					qa_db_query_sub(
						'SELECT * FROM ^postmeta WHERE meta_key=$',
						'is_poll'
					)
				);
				foreach($poll_array as $q) {
					$poll[(int)$q['post_id']] = $q['meta_value'];
				}
				if(isset($this->content['q_view'])) {

					$qid = $this->content['q_view']['raw']['postid'];
					$author = $this->content['q_view']['raw']['userid'];

					if(@$poll[$qid]) { // is a poll
					
						$this->poll = $poll[$qid];
						
					// add post elements
					
						// title

						$this->content['title'] .= ' '.qa_opt('poll_question_title');

						// poll div
						
						$this->content['q_view']['content'].= '<div id="qa-poll-div">'.$this->getPollDiv($qid,qa_get_logged_in_userid()).'</div>';
						
						// css class
						
						$this->content['main_form_tags'] .= ' class="qa-poll"';
					}
				}
				if(isset($this->content['q_list'])) {
					foreach($this->content['q_list']['qs'] as $idx => $question) {

						if(isset($poll[$question['raw']['postid']])) {
							$this->content['q_list']['qs'][$idx]['title'] .= ' '.qa_opt('poll_question_title');
						}
					}					
				}
			}
			qa_html_theme_base::doctype();
		}
		
		function html() {
			if(qa_post_text('ajax_poll_id')) {
				$this->output_raw($this->getPollDiv((int)qa_post_text('ajax_poll_id'),(int)qa_post_text('ajax_poll_voter'),(int)qa_post_text('ajax_poll_vote')));
				return;
			}
			qa_html_theme_base::html();
		}

		function head_custom() {
			if(qa_opt('poll_enable')) {
				if($this->template == 'ask') {
					$this->output_raw('<script>
	var poll_answer_index = 2;
	jQuery("document").ready(function(){jQuery("#is_poll").removeAttr("checked")});
	function addPollAnswer(idx) {
		jQuery("#qa-poll-ask-div").append(\'<br/><input type="input" class="qa-poll-answer-text" name="poll_answer_\'+idx+\'">&nbsp;<input type="button" class="qa-poll-answer-add" value="+" onclick="addPollAnswer(poll_answer_index)">\');
		poll_answer_index++;
	}
</script>');
				}
				if($this->template == 'question' && @$this->poll) {
					$this->output('<style>',str_replace('^',QA_HTML_THEME_LAYER_URLTOROOT,qa_opt('poll_css')),'</style>');
					$this->output_raw("<script>
		function pollVote(qid,uid,vid) {
			var dataString = 'ajax_poll_id='+qid+'&ajax_poll_voter='+uid+'&ajax_poll_vote='+vid;  
			jQuery.ajax({  
			  type: 'POST',  
			  url: '".qa_self_html()."',  
			  data: dataString,  
			  success: function(data) {
				if(/^[\\t\\n ]*###/.exec(data)) {
					var error = data.replace(/^[\\t\\n ]*### */,'');
					window.alert(error);
				}
				else {
						jQuery('#qa-poll-div').html(data);
				}
			  }  
			});
		}
	</script>");
				}	
			}	
			qa_html_theme_base::head_custom();
		}
		
		function nav_list($navigation, $class, $level=null)
		{
			global $qa_request;
			if($qa_request == 'polls' && $class == 'nav-sub') {
				unset($navigation['recent']['selected']);
				$navigation['polls']['selected'] = true;
			}
			qa_html_theme_base::nav_list($navigation, $class, $level=null);
		}
		
	// worker
	
		function getPollDiv($qid,$uid,$vid=null) {
			
			$answers = qa_db_read_all_assoc(
				qa_db_query_sub(
					'SELECT BINARY content as content, votes, id FROM ^polls WHERE parentid=#',
					$qid
				)
			);

			// do voting

			if($vid) {
				foreach ($answers as $idx => $answer) {
					$votes = explode(',',$answer['votes']);
					
					if($answer['id'] == $vid) {
						
						if(in_array($uid,$votes)) return '### you\'ve already voted, cheater!';
						
						$answers[$idx]['votes'] = ($answers[$idx]['votes']?$answers[$idx]['votes'].',':'').$uid;
						qa_db_query_sub(
							'UPDATE ^polls SET votes=$ WHERE id=#',
							$answers[$idx]['votes'], $vid
						);
					}
					else if(in_array($uid,$votes) && $this->poll != 2) {
						foreach($votes as $i => $vote) {
							if($uid == $vote) {
								unset($votes[$i]);
								break;
							}
						}

						$answers[$idx]['votes'] = implode(',',$votes);
						qa_db_query_sub(
							'UPDATE ^polls SET votes=$ WHERE id=#',
							$answers[$idx]['votes'], $answer['id']
						);
					}
				}
			}
			
			
			
			if(empty($answers)) return '### no choices found for poll!';

			$out = '<div id="qa-poll-choices-title">'.qa_opt('poll_answers_text').'</div><div id="qa-poll-choices">';
			
			// check if voted
			
			$allow = true;
			
			foreach ($answers as $idx => $answer) {
				$votes = explode(',',$answer['votes']);
				if(!in_array($uid,$votes))
					$answers[$idx]['vote'] = '<div class="qa-poll-vote-button" title="'.qa_opt('poll_vote_button').'" onclick="pollVote('.$qid.','.$uid.','.$answer['id'].')"></div>';
				else {
					$answers[$idx]['vote'] = '<div class="qa-poll-voted-button" title="'.qa_opt('poll_voted_button').'"></div>';
				}
			}

			foreach ($answers as $answer) {
				
				
				if(!$answer['votes']) $votes = array();
				else $votes = explode(',',$answer['votes']);
				
				$out .= '<div class="qa-poll-choice">'.@$answer['vote'].'<span class="qa-poll-choice-title">'.qa_html($answer['content']).'</span> ('.(count($votes)==1?qa_lang('main/1_vote'):str_replace('^',count($votes),qa_lang('main/x_votes'))).')';
				
				$out .= '<table class="qa-poll-votes" style="max-width:'.qa_opt('poll_max_width').'px;height:'.qa_opt('poll_vote_height').'px"><tr>';
				
				if($answer['votes']) {
					
					$c = 0;
					while( ($c++) < count($votes)) {
						$out .= '<td class="qa-poll-vote-block" title="'.(count($votes)==1?qa_lang('main/1_vote'):str_replace('^',count($votes),qa_lang('main/x_votes'))).'" style="width:'.qa_opt('poll_vote_width').'px;height:'.qa_opt('poll_vote_height').'px"></td>';
					}
				}
				$out .= '</tr></table></div>';
			}
			$out .= '</div>';
			
			return $out;
		}
		
	}

