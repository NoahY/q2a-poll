<?php
        
/*              
        Plugin Name: Polls
        Plugin URI: https://github.com/NoahY/q2a-poll
        Plugin Description: Ask poll questions
        Plugin Version: 1.0b
        Plugin Date: 2011-09-05
        Plugin Author: NoahY
        Plugin Author URI:                              
        Plugin License: GPLv2                           
        Plugin Minimum Question2Answer Version: 1.4
*/                      
                        
                        
        if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
                        header('Location: ../../');
                        exit;   
        }               

        qa_register_plugin_module('module', 'qa-poll-admin.php', 'qa_poll_admin', 'Poll Admin');
        qa_register_plugin_module('event', 'qa-poll-check.php', 'qa_poll_event', 'Poll Admin');
        qa_register_plugin_module('page', 'qa-poll-page.php', 'qa_poll_page', 'Poll page');
        
        qa_register_plugin_layer('qa-poll-layer.php', 'Poll Layer');
                        
                        
/*                              
        Omit PHP closing tag to help avoid accidental output
*/                              
                          

