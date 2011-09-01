<?php

	class qa_html_theme_layer extends qa_html_theme_base {
		
		function doctype(){
			//qa_error_log($this->content);
			if (qa_opt('poll_enable')) {
				
				if($this->template == 'ask') {
					$this->content['form']['fields'][] = array(
						'label' => qa_opt('poll_checkbox_text'),
						'tags' => 'NAME="is_poll" ID="is_poll" onclick="jQuery(\'#poll-div\').toggle()"',
						'type' => 'checkbox',
					);
					$this->content['form']['fields'][] = array(
						'note' => '<div id="poll-div" style="display:none"><p class="qa-form-tall-label"><input type="checkbox" name="poll_multiple">'.qa_opt('poll_multiple_text').'</p><p class="qa-form-tall-label">'.qa_opt('poll_answers_text').'</p><input type="input" class="poll-answer-text" class="poll-answer-text" name="poll_answer_1">&nbsp;<input type="button" class="poll-answer-add" value="+" onclick="addPollAnswer(poll_answer_index)"></div>',
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
					
						// title and message

						$this->content['title'] .= ' '.qa_opt('poll_question_title');
						
					// remove editing capabilities
						
						if(qa_get_logged_in_level()<QA_USER_LEVEL_MODERATOR) {
							
							if(qa_get_logged_in_userid()!=$author) {

								unset($GLOBALS['qa_state']);
								unset($this->qa_state);
								unset($_POST['doanswerq']);
								unset($_POST['doansweradd']);
								unset($_POST['doeditq']);
								unset($_POST['dosaveq']);
								unset($_POST['doedit']);
								unset($_POST['dosave']);
								unset($this->content['q_view']['form']['buttons']['edit']);
								unset($this->content['q_view']['form']['buttons']['answer']);
							}

							unset($_POST['docommentq']);
							unset($_POST['docommentaddq']);
							unset($this->content['q_view']['form']['buttons']['comment']);
							
							if(isset($this->content['q_view']['c_list'])) {
								foreach($this->content['q_view']['c_list'] as $cdx => $comment) {
									unset($this->content['q_view']['c_list'][$cdx]['form']['buttons']['edit']);
									unset($this->content['q_view']['c_list'][$cdx]['form']['buttons']['answer']);
									unset($this->content['q_view']['c_list'][$cdx]['form']['buttons']['comment']);							
								}
							}
							unset($this->content['q_view']['c_form']);		
							
							if(isset($this->content['a_list']['as'])) {
								foreach($this->content['a_list']['as'] as $idx => $answer) {
									
									
									if(qa_get_logged_in_userid()!=$author) {
										unset($_POST['doedita_'.$idx]);
										unset($_POST['dosavea_'.$idx]);
										unset($this->content['a_list']['as'][$idx]['select_tags']);
										unset($this->content['a_list']['as'][$idx]['unselect_tags']);
										unset($this->content['a_list']['as'][$idx]['form']['buttons']['edit']);
									}

									unset($_POST['docommenta_'.$idx]);
									unset($_POST['docommentadda_'.$idx]);
									unset($this->content['a_list']['as'][$idx]['c_form']);
									unset($this->content['a_list']['as'][$idx]['form']['buttons']['comment']);
									if(isset($answer['c_list'])) {
										foreach($answer['c_list'] as $cdx => $comment) {
											unset($this->content['a_list']['as'][$idx]['c_list'][$cdx]['form']['buttons']['edit']);
											unset($this->content['a_list']['as'][$idx]['c_list'][$cdx]['form']['buttons']['comment']);
										}
									}
								}

							}

							unset($this->content['q_view']['a_form']);					
						}			
						
					}
					if(isset($this->content['a_list']['as'])) {
						foreach($this->content['a_list']['as'] as $idx => $answer) {
							unset($this->content['a_list']['as'][$idx]['what']);
							unset($this->content['a_list']['as'][$idx]['when']);
							unset($this->content['a_list']['as'][$idx]['who']);
							unset($this->content['a_list']['as'][$idx]['form']['buttons']['follow']);
							if(@$this->content['a_list']['as'][$idx]['when_2']['prefix'] == qa_lang('main/edited').' ') {
								$this->content['a_list']['as'][$idx]['when_2']['prefix'] = qa_lang('main/answered').' ';
							}
						}
					}
				}
				if(isset($this->content['q_list'])) {
					foreach($this->content['q_list']['qs'] as $idx => $question) {
						if(isset($poll[$question['raw']['postid']])) {
							$this->content['q_list']['qs'][$idx]['title'] .= ' '.qa_opt('poll_question_title');
							if(@$this->content['q_list']['qs'][$idx]['what'] == qa_lang('main/answer_edited')) { 
								$this->content['q_list']['qs'][$idx]['what'] = qa_lang('main/answered');
							}
						}
					}					
				}
			}
			qa_html_theme_base::doctype();
		}
		function head_custom() {
			if($this->template == 'ask') {
				$this->output_raw('<script>
	var poll_answer_index = 2;
	jQuery("document").ready(function(){jQuery("#is_poll").removeAttr("checked")});
	function addPollAnswer(idx) {
		jQuery("#poll-div").append(\'<br/><input type="input" class="poll-answer-text" name="poll_answer_\'+idx+\'">&nbsp;<input type="button" class="poll-answer-add" value="+" onclick="addPollAnswer(poll_answer_index)">\');
		poll_answer_index++;
	}
</script>');
			}
			qa_html_theme_base::head_custom();
		}
		
		// add wrapper div to style polls differently

		function main() {
			if(isset($this->poll)) $this->output('<DIV CLASS="qa-main-poll">');
			qa_html_theme_base::main();
			if(isset($this->poll)) $this->output('</DIV');
		
		}


		function vote_buttons($post) {

			if($post['raw']['type'] == 'A'){
				if(!isset($this->content)) { // ajax
					
					$poll = qa_db_read_one_value(
						qa_db_query_sub(
							'SELECT meta_value FROM ^postmeta WHERE meta_key=$ AND post_id=#',
							'is_poll',$post['raw']['parentid']
						), 
						true
					);
					$this->poll = $poll;
				}
				if(isset($this->poll)) {
					$this->output('<DIV CLASS="qa-vote-buttons '.(($post['vote_view']=='updown') ? 'qa-vote-buttons-updown' : 'qa-vote-buttons-net').'">');
					$this->poll_vote_buttons($post);
					$this->output('</DIV>');
				}
				else qa_html_theme_base::vote_buttons($post);
			}
			else qa_html_theme_base::vote_buttons($post);
		}
	
	// worker functions
		
		function poll_vote_buttons($post) {
			switch (@$post['vote_state'])
			{
				case 'voted_up':
					$this->post_hover_button($post, 'vote_up_tags', '+', 'qa-vote-one-button qa-voted-up');
					break;
					
				case 'voted_up_disabled':
					$this->post_disabled_button($post, 'vote_up_tags', '+', 'qa-vote-one-button qa-vote-up');
					break;
					
				case 'voted_down':
					$this->post_hover_button($post, 'vote_down_tags', '&ndash;', 'qa-vote-one-button qa-voted-down');
					break;
					
				case 'voted_down_disabled':
					$this->post_disabled_button($post, 'vote_down_tags', '&ndash;', 'qa-vote-one-button qa-vote-down');
					break;
					
				case 'enabled':
					$this->post_hover_button($post, 'vote_up_tags', '+', 'qa-vote-one-button qa-vote-up');
					break;

				default:
					$this->post_disabled_button($post, 'vote_up_tags', '', 'qa-vote-one-button qa-vote-up');
					break;
			}
		}
	}

