<?php
    class qa_poll_admin {

	function option_default($option) {
		
	    switch($option) {
		case 'poll_comments':
		    return '[poll]';
		case 'poll_question_title':
		    return '[poll]';
		case 'poll_checkbox_text':
		    return 'Create poll';
		case 'poll_vote_button':
		    return 'vote';
		case 'poll_voted_button':
		    return 'unvote';
		case 'poll_multiple_text':
		    return 'Allow multiple votes';
		case 'poll_already_voted':
		    return 'You have already voted once.';
		case 'poll_answers_text':
		    return 'Choices:';
		case 'poll_page_title':
		    return 'Polls';
		case 'poll_choice_count_error':
		    return 'You must enter at least two choices for the poll.';
		case 'poll_css':
		    return '#qa-poll-div {
    background-color: #D9E3EA;
    border: 1px solid #658296;
    font-size: 12px;
    padding: 10px;
}
#qa-poll-choices-title {
    font-weight:bold;
    margin-bottom:8px;
}
.qa-poll-voted-button-container,.qa-poll-vote-button-container{
    width:24px;
}
.qa-poll-choice {
    clear:both;
    padding:5px 0 5px 5px;
}
#qa-poll-choices > div:last-child  {
    padding-bottom:0px;
}
#qa-poll-choices > div:first-child  {
    padding-top:0px;
}

.qa-poll-choice-title {
    line-height:12px;
    margin-left:10px;
}
.qa-poll-votes {
    max-width:500px;
    height:10px;
    margin-left:22px; 
    margin:5px 0 5px 22px;
}
.qa-poll-vote-block {
    width:10px;
    height:10px;
    background-color:green;
}
.qa-poll-vote-block-empty {
    width:10px;
    height:10px;
}
.qa-poll-voted-button, .qa-poll-vote-button {
    cursor:pointer;
    width:12px;
    height:12px;
    float:left;
    margin-top: 1px;
}
.qa-poll-voted-button {
    background-image:url(^button_voted.png);
}
.qa-poll-vote-button {
    background-image:url(^button_vote.png);
}
.qa-poll-vote-button:hover, .qa-poll-voted-button:hover {
    background-image:url(^button_voting.png);
}';
		default:
		    return null;				
	    }
		
	}
        
        function allow_template($template)
        {
            return ($template!='admin');
        }       
            
        function admin_form(&$qa_content)
        {                       
                            
        // Process form input
            
            $ok = null;
            
            if (qa_clicked('poll_save')) {
		if((bool)qa_post_text('poll_enable') && !qa_opt('poll_enable')) {
		    $table_exists = qa_db_read_one_value(qa_db_query_sub("SHOW TABLES LIKE '^postmeta'"),true);
		    if(!$table_exists) {
			qa_db_query_sub(
			    'CREATE TABLE IF NOT EXISTS ^postmeta (
			    meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			    post_id bigint(20) unsigned NOT NULL,
			    meta_key varchar(255) DEFAULT \'\',
			    meta_value longtext,
			    PRIMARY KEY (meta_id),
			    KEY post_id (post_id),
			    KEY meta_key (meta_key)
			    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8'
			);			
		    }		    
		    $table_exists = qa_db_read_one_value(qa_db_query_sub("SHOW TABLES LIKE '^polls'"),true);
		    if(!$table_exists) {
			qa_db_query_sub(
			    'CREATE TABLE IF NOT EXISTS ^polls (
			    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			    parentid bigint(20) unsigned NOT NULL,
			    votes longtext,
			    content varchar(255) DEFAULT \'\',
			    PRIMARY KEY (id)
			    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8'
			);			
		    }		    
		}
                qa_opt('poll_enable',(bool)qa_post_text('poll_enable'));
                qa_opt('poll_update_on_vote',(bool)qa_post_text('poll_update_on_vote'));
                qa_opt('poll_question_title',qa_post_text('poll_question_title'));
                qa_opt('poll_checkbox_text',qa_post_text('poll_checkbox_text'));
                qa_opt('poll_multiple_text',qa_post_text('poll_multiple_text'));
                qa_opt('poll_vote_button',qa_post_text('poll_vote_button'));
                qa_opt('poll_voted_button',qa_post_text('poll_voted_button'));
                qa_opt('poll_already_voted',qa_post_text('poll_already_voted'));
                qa_opt('poll_answers_text',qa_post_text('poll_answers_text'));
                qa_opt('poll_page_title',qa_post_text('poll_page_title'));
                qa_opt('poll_css',qa_post_text('poll_css'));
                $ok = 'Settings Saved.';
            }
            else if (qa_clicked('poll_reset')) {
		foreach($_POST as $i => $v) {
		    $def = $this->option_default($i);
		    if($def !== null) qa_opt($i,$def);
		}
	    }
  
        // Create the form for display
            
            $fields = array();
            
            $fields[] = array(
                'label' => 'Enable polls',
                'tags' => 'NAME="poll_enable"',
                'value' => qa_opt('poll_enable'),
                'type' => 'checkbox',
            );

/*
            $fields[] = array(
                'label' => 'Update date of question on vote',
                'tags' => 'NAME="poll_update_on_vote"',
                'value' => qa_opt('poll_update_on_vote'),
                'type' => 'checkbox',
            );
*/
            $fields[] = array(
                'label' => 'Text to add to poll title',
                'tags' => 'NAME="poll_question_title"',
                'value' => qa_opt('poll_question_title'),
            );

            $fields[] = array(
                'label' => 'Text to select question as poll on ask form',
                'tags' => 'NAME="poll_checkbox_text"',
                'value' => qa_opt('poll_checkbox_text'),
            );

            $fields[] = array(
                'label' => 'Text for allowing multiple poll votes on ask form',
                'tags' => 'NAME="poll_multiple_text"',
                'value' => qa_opt('poll_multiple_text'),
            );

            $fields[] = array(
                'label' => 'Vote button text',
                'tags' => 'NAME="poll_vote_button"',
                'value' => qa_opt('poll_vote_button'),
            );

            $fields[] = array(
                'label' => 'Voted button text',
                'tags' => 'NAME="poll_voted_button"',
                'value' => qa_opt('poll_voted_button'),
            );

            $fields[] = array(
                'label' => 'Error to display when inputting less than two poll choices',
                'tags' => 'NAME="poll_choice_count_error"',
                'value' => qa_opt('poll_choice_count_error'),
            );

            $fields[] = array(
                'label' => 'Text to display when disallowing second vote',
                'tags' => 'NAME="poll_already_voted"',
                'value' => qa_opt('poll_already_voted'),
            );

            $fields[] = array(
                'label' => 'Text to add to answers list on ask form',
                'tags' => 'NAME="poll_answers_text"',
                'value' => qa_opt('poll_answers_text'),
            );

            $fields[] = array(
                'label' => 'Poll page title',
                'tags' => 'NAME="poll_page_title"',
                'value' => qa_opt('poll_page_title'),
            );

            $fields[] = array(
                'label' => 'Poll question stylesheet',
                'tags' => 'NAME="poll_css"',
                'value' => qa_opt('poll_css'),
		'rows' => 20,
		'type' => 'textarea',
		'note' => '^ will be replaced by location of this plugin directory',
            );

            return array(           
                'ok' => ($ok && !isset($error)) ? $ok : null,
                    
                'fields' => $fields,
             
                'buttons' => array(
                    array(
                        'label' => qa_lang_html('main/save_button'),
                        'tags' => 'NAME="poll_save"',
                    ),
                    array(
                        'label' => qa_lang_html('admin/reset_options_button'),
                        'tags' => 'NAME="poll_reset"',
                    ),
                ),
            );
        }
    }

