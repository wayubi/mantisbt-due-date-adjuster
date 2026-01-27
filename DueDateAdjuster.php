<?php
/**
 * MantisBT - Due Date Adjuster Plugin
 * 
 * Allows users to quickly adjust issue due dates by adding 1 week, 2 weeks, or 1 month
 * while preserving the original time.
 */

class DueDateAdjusterPlugin extends MantisPlugin {
    
    public function register() {
        $this->name = 'Due Date Adjuster';
        $this->description = 'Allows quick adjustment of due dates by adding 1 week, 2 weeks, or 1 month';
        $this->version = '1.0.0';
        $this->requires = array(
            'MantisCore' => '2.0.0',
        );
        $this->author = 'W. Latif Ayubi';
    }
    
    public function hooks() {
        return array(
            'EVENT_MENU_ISSUE' => 'menu_issue',
            'EVENT_VIEW_BUG_EXTRA' => 'view_bug_extra',
        );
    }

    function menu_issue( $p_event, $p_bug_id ) {
        $t_bug = bug_get( $p_bug_id );
        
        // Only show menu if issue has a due date
        if ( $t_bug->due_date == '' ) {
            return array();
        }
        
        // Check if user has access to update the issue
        if ( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
            return array();
        }
        
        return array(
            '<a href="' . plugin_page( 'adjust_due_date' ) . '&bug_id=' . $p_bug_id . '&interval=1week" onclick="return confirm(\'Push due date forward by 1 week?\');">Push Due Date +1 Week</a>',
            '<a href="' . plugin_page( 'adjust_due_date' ) . '&bug_id=' . $p_bug_id . '&interval=2weeks" onclick="return confirm(\'Push due date forward by 2 weeks?\');">Push Due Date +2 Weeks</a>',
            '<a href="' . plugin_page( 'adjust_due_date' ) . '&bug_id=' . $p_bug_id . '&interval=1month" onclick="return confirm(\'Push due date forward by 1 month?\');">Push Due Date +1 Month</a>',
        );
    }
}
