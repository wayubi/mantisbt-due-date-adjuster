<?php
/**
 * MantisBT - Due Date Adjuster Plugin
 * 
 * Allows users to quickly adjust issue due dates by adding 1 week, 2 weeks, or 1 month
 * while preserving the original time.
 * 
 * @copyright Copyright 2026
 * @link https://www.mantisbt.org
 */

class DueDateAdjusterPlugin extends MantisPlugin {
    
    /**
     * Plugin registration
     */
    function register() {
        $this->name = 'Due Date Adjuster';
        $this->description = 'Allows quick adjustment of due dates by adding 1 week, 2 weeks, or 1 month';
        $this->page = '';
        
        $this->version = '1.0.0';
        $this->requires = array(
            'MantisCore' => '2.0.0',
        );
        
        $this->author = 'MantisBT Plugin Developer';
        $this->contact = '';
        $this->url = '';
    }
    
    /**
     * Plugin hooks
     */
    function hooks() {
        return array(
            'EVENT_MENU_ISSUE' => 'menu_issue',
            'EVENT_VIEW_BUG_EXTRA' => 'view_bug_extra',
        );
    }
    
    /**
     * Add menu items to issue menu
     */
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
    
    /**
     * Add quick action buttons to bug view page
     */
    function view_bug_extra( $p_event, $p_bug_id ) {
        $t_bug = bug_get( $p_bug_id );
        
        // Only show buttons if issue has a due date
        if ( $t_bug->due_date == '' ) {
            return;
        }
        
        // Check if user has access to update the issue
        if ( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
            return;
        }
        
        echo '<tr>';
        echo '<th class="category">Quick Due Date Adjust</th>';
        echo '<td colspan="5">';
        echo '<form method="post" action="' . plugin_page( 'adjust_due_date' ) . '" style="display:inline;">';
        echo form_security_field( 'plugin_DueDateAdjuster_adjust' );
        echo '<input type="hidden" name="bug_id" value="' . $p_bug_id . '" />';
        echo '<input type="hidden" name="interval" value="1week" />';
        echo '<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="+1 Week" onclick="return confirm(\'Push due date forward by 1 week?\');" />';
        echo '</form> ';
        
        echo '<form method="post" action="' . plugin_page( 'adjust_due_date' ) . '" style="display:inline;">';
        echo form_security_field( 'plugin_DueDateAdjuster_adjust' );
        echo '<input type="hidden" name="bug_id" value="' . $p_bug_id . '" />';
        echo '<input type="hidden" name="interval" value="2weeks" />';
        echo '<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="+2 Weeks" onclick="return confirm(\'Push due date forward by 2 weeks?\');" />';
        echo '</form> ';
        
        echo '<form method="post" action="' . plugin_page( 'adjust_due_date' ) . '" style="display:inline;">';
        echo form_security_field( 'plugin_DueDateAdjuster_adjust' );
        echo '<input type="hidden" name="bug_id" value="' . $p_bug_id . '" />';
        echo '<input type="hidden" name="interval" value="1month" />';
        echo '<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="+1 Month" onclick="return confirm(\'Push due date forward by 1 month?\');" />';
        echo '</form>';
        
        echo '</td>';
        echo '</tr>';
    }
}
