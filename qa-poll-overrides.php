<?php
		
	function qa_get_permit_options() {
		$permits = qa_get_permit_options_base();
		$permits[] = 'permit_post_poll';
		$permits[] = 'permit_vote_poll';
		return $permits;
	}
						
/*							  
		Omit PHP closing tag to help avoid accidental output
*/							  
						  

