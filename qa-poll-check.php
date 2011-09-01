<?php

	class qa_poll_event {
		function process_event($event, $userid, $handle, $cookieid, $params) {
			if (qa_opt('poll_enable')) {
				switch ($event) {
					case 'a_vote_up':
						$answer=qa_db_single_select(qa_db_full_post_selectspec(null, $params['postid']));
						$poll_array = qa_db_read_one_assoc(
							qa_db_query_sub(
								'SELECT * FROM ^postmeta WHERE post_id=# AND meta_key=$',
								$answer['parentid'], 'is_poll'
							),true
						);
						if(@$poll_array['meta_value'] == '1') {  // check if already voted on single-vote poll
							global $qa_login_userid, $qa_cookieid;
							$answers = qa_db_select_with_pending(qa_db_full_child_posts_selectspec($qa_login_userid, $answer['parentid']));
							$c = 0;
							if(is_array($answers)) {
								foreach($answers as $a) {
									if(in_array($a['uservote'],array(1,-1))) $c++;
								}
							}
							if ($c > 1) {
								qa_vote_set($answer, $qa_login_userid, qa_get_logged_in_handle(), $qa_cookieid, '0');
								echo "QA_AJAX_RESPONSE\n0\n".qa_opt('poll_already_voted');
								die();
							}
						}
						
						// update answer for list
						
						qa_db_query_sub(
							'UPDATE ^posts SET updated=NOW(), lastuserid=# WHERE postid=#',
							$userid, $params['postid']
						);				
						
						break;
					case 'q_post':
						if(qa_post_text('is_poll')) {
							qa_db_query_sub(
								'INSERT INTO ^postmeta (post_id,meta_key,meta_value) VALUES (#,$,$)',
								$params['postid'],'is_poll',(qa_post_text('poll_multiple')?'2':'1')
							);

							$question=qa_db_single_select(qa_db_full_post_selectspec(null, $params['postid']));
							$c = 0;
							while(qa_post_text('poll_answer_'.(++$c))) {
								global $qa_login_userid, $qa_cookieid;
								if (!isset($qa_login_userid))
									$qa_cookieid=qa_cookie_get_create(); // create a new cookie if necessary
					
								$answerid=qa_answer_create($qa_login_userid, qa_get_logged_in_handle(), $qa_cookieid, qa_post_text('poll_answer_'.$c), '', qa_post_text('poll_answer_'.$c), null, null, $question);
								qa_report_write_action($qa_login_userid, $qa_cookieid, 'a_post', $question['postid'], $answerid, null);
							}
						}
						break;
					default:
						break;
				}
			}
		}
	}

