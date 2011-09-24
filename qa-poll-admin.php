<?php
    class qa_poll_admin {

	function option_default($option) {
		
	    switch($option) {
		case 'poll_question_title':
		    return '[poll]';
		case 'poll_checkbox_text':
		    return 'Create poll';
		case 'poll_vote_button':
		    return 'vote';
		case 'poll_voted_button':
		    return 'voted';
		case 'poll_multiple_text':
		    return 'Allow multiple votes';
		case 'poll_already_voted':
		    return 'You have already voted once.';
		case 'poll_answers_text':
		    return 'Choices:';
		case 'poll_page_title':
		    return 'Polls';
		case 'poll_max_width':
		    return 500;
		case 'poll_vote_width':
		    return 10;
		case 'poll_vote_height':
		    return 10;
		case 'poll_css':
		    return '#qa-poll-div {
    background-color: LightSkyBlue;
    border: 2px solid DeepSkyBlue;
    border-radius: 8px 8px 8px 8px;
    font-size: 12px;
    padding: 10px;
}
#qa-poll-choices-title {
    font-weight:bold;
    margin-bottom:8px;
}
.qa-poll-vote-block {
    background-color:green;
}
.qa-poll-voted-button-container,.qa-poll-vote-button-container{
    width:24px;
}
.qa-poll-choice {
    clear:both;
    padding:20px 0 20px 10px;
    border-bottom: 1px solid DeepSkyBlue;
}
#qa-poll-choices > div:last-child  {
    border-bottom:none;
    padding-bottom:0px;
}
#qa-poll-choices > div:first-child  {
    border-top:none;
    padding-top:0px;
}

.qa-poll-choice-title {
    line-height:24px;
    margin-left:10px;
}
.qa-poll-votes {
    margin-left:34px; 
    margin-top:10px;
}
.qa-poll-voted-button, .qa-poll-vote-button {
    width:24px;
    height:24px;
    float:left;
    clear:left;
}
.qa-poll-voted-button {
    background-image:url(^button_voted.png);
}
.qa-poll-vote-button {
    background-image:url(^button_vote.png);
}
.qa-poll-vote-button:hover {
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
                qa_opt('poll_comments',(bool)qa_post_text('poll_comments'));
                qa_opt('poll_update_on_vote',(bool)qa_post_text('poll_update_on_vote'));
                qa_opt('poll_question_title',qa_post_text('poll_question_title'));
                qa_opt('poll_checkbox_text',qa_post_text('poll_checkbox_text'));
                qa_opt('poll_multiple_text',qa_post_text('poll_multiple_text'));
                qa_opt('poll_vote_button',qa_post_text('poll_vote_button'));
                qa_opt('poll_voted_button',qa_post_text('poll_voted_button'));
                qa_opt('poll_already_voted',qa_post_text('poll_already_voted'));
                qa_opt('poll_answers_text',qa_post_text('poll_answers_text'));
                qa_opt('poll_page_title',qa_post_text('poll_page_title'));
                qa_opt('poll_max_width',(int)qa_post_text('poll_max_width'));
                qa_opt('poll_vote_width',(int)qa_post_text('poll_vote_width'));
                qa_opt('poll_vote_height',(int)qa_post_text('poll_vote_height'));
                qa_opt('poll_css',qa_post_text('poll_css'));
                $ok = 'Settings Saved.';
            }
  
        // Create the form for display
            
            $fields = array();
            
            $fields[] = array(
                'label' => 'Enable polls',
                'tags' => 'NAME="poll_enable"',
                'value' => qa_opt('poll_enable'),
                'type' => 'checkbox',
            );
            
            $fields[] = array(
                'label' => 'Allow commenting on polls (comments are shown below all answers)',
                'tags' => 'NAME="poll_comments"',
                'value' => qa_opt('poll_comments'),
                'type' => 'checkbox',
            );

            $fields[] = array(
                'label' => 'Update date of question on vote',
                'tags' => 'NAME="poll_update_on_vote"',
                'value' => qa_opt('poll_update_on_vote'),
                'type' => 'checkbox',
            );

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
                'type' => 'number',
                'label' => 'Maximum width of poll choice (px)',
                'tags' => 'NAME="poll_max_width"',
                'value' => qa_opt('poll_max_width'),
            );

            $fields[] = array(
                'type' => 'number',
                'label' => 'width of each poll vote (px)',
                'tags' => 'NAME="poll_vote_width"',
                'value' => qa_opt('poll_vote_width'),
            );

            $fields[] = array(
                'type' => 'number',
                'label' => 'height of each poll vote (px)',
                'tags' => 'NAME="poll_vote_height"',
                'value' => qa_opt('poll_vote_height'),
            );

            $fields[] = array(
                'label' => 'Poll question stylesheet',
                'tags' => 'NAME="poll_css"',
                'value' => qa_opt('poll_css'),
		'rows' => 20,
		'type' => 'textarea',
		'note' => '^ will be replaced by this plugin directory ('.QA_HTML_THEME_LAYER_URLTOROOT.')',
            );

            return array(           
                'ok' => ($ok && !isset($error)) ? $ok : null,
                    
                'fields' => $fields,
             
                'buttons' => array(
                    array(
                        'label' => 'Save',
                        'tags' => 'NAME="poll_save"',
                    )
                ),
            );
        }
    }

