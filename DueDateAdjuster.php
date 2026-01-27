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
            'EVENT_LAYOUT_RESOURCES' => 'resources',
        );
    }
    
    function resources($p_event) {
        return '<style>
            .snooze-dropdown { position: relative; display: inline-block; }
            .snooze-dropdown-content { display: none; position: absolute; background-color: #f9f9f9; min-width: 160px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 1000; }
            .snooze-dropdown-content a { color: black; padding: 12px 16px; text-decoration: none; display: block; }
            .snooze-dropdown-content a:hover { background-color: #f1f1f1; }
            .snooze-dropdown:hover .snooze-dropdown-content { display: block; }
        </style>';
    }
    
    function menu_issue($p_event, $p_bug_id) {
        $t_bug = bug_get($p_bug_id);
        
        // Only show menu if issue has a due date
        if ($t_bug->due_date == '') {
            return array();
        }
        
        // Check if user has access to update the issue
        if (!access_has_bug_level(config_get('update_bug_threshold'), $p_bug_id)) {
            return array();
        }
        
        $html = '<div class="snooze-dropdown">
            <button type="button" class="btn btn-primary btn-white btn-round btn-sm">Due Date</button>
            <div class="snooze-dropdown-content">
                <a href="' . plugin_page('adjust_due_date') . '&bug_id=' . $p_bug_id . '&interval=1week" onclick="return confirm(\'Push due date forward by 1 week?\');">+1 Week</a>
                <a href="' . plugin_page('adjust_due_date') . '&bug_id=' . $p_bug_id . '&interval=2weeks" onclick="return confirm(\'Push due date forward by 2 weeks?\');">+2 Weeks</a>
                <a href="' . plugin_page('adjust_due_date') . '&bug_id=' . $p_bug_id . '&interval=4weeks" onclick="return confirm(\'Push due date forward by 4 weeks?\');">+4 Weeks</a>
                <a href="' . plugin_page('adjust_due_date') . '&bug_id=' . $p_bug_id . '&interval=1month" onclick="return confirm(\'Push due date forward by 1 month?\');">+1 Month</a>
                <a href="' . plugin_page('adjust_due_date') . '&bug_id=' . $p_bug_id . '&interval=3month" onclick="return confirm(\'Push due date forward by 3 month?\');">+3 Month</a>
            </div>
        </div>';
        
        return array($html);
    }
}