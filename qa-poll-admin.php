<?php
    class qa_poll_admin {

	function option_default($option) {
		
	    switch($option) {
		case 'poll_question_title':
		    return '[poll]';
		case 'poll_checkbox_text':
		    return 'Create poll';
		case 'poll_multiple_text':
		    return 'Allow multiple votes';
		case 'poll_already_voted':
		    return 'You have already voted once.';
		case 'poll_answers_text':
		    return 'Answers:';
		case 'poll_page_title':
		    return 'Polls';
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
			    meta_key varchar(255) DEFAULT NULL,
			    meta_value longtext,
			    PRIMARY KEY (meta_id),
			    KEY post_id (post_id),
			    KEY meta_key (meta_key)
			    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8'
			);			
		    }		    
		}
                qa_opt('poll_enable',(bool)qa_post_text('poll_enable'));
                qa_opt('poll_update_on_vote',(bool)qa_post_text('poll_update_on_vote'));
                qa_opt('poll_question_title',qa_post_text('poll_question_title'));
                qa_opt('poll_checkbox_text',qa_post_text('poll_checkbox_text'));
                qa_opt('poll_multiple_text',qa_post_text('poll_multiple_text'));
                qa_opt('poll_already_voted',qa_post_text('poll_already_voted'));
                qa_opt('poll_answers_text',qa_post_text('poll_answers_text'));
                qa_opt('poll_page_title',qa_post_text('poll_page_title'));
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
                'label' => 'Update date of answer on vote',
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

