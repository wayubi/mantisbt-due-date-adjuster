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
            .duedate-adjuster .caret {
                border-top-color: #6688a6 !important;
                border-bottom-color: #6688a6 !important;
            }
        </style>';
    }
    
    function menu_issue($p_event, $p_bug_id) {
        $t_bug = bug_get($p_bug_id);
        
        if ($t_bug->due_date == '') {
            return array();
        }
        
        if (!access_has_bug_level(config_get('update_bug_threshold'), $p_bug_id)) {
            return array();
        }
        
        $t_lang_strings = array(
            'now' => plugin_lang_get('push_now'),
            'today' => plugin_lang_get('push_today'),
            '1week' => plugin_lang_get('push_1week'),
            '2weeks' => plugin_lang_get('push_2weeks'),
            '4weeks' => plugin_lang_get('push_4weeks'),
            '1month' => plugin_lang_get('push_1month'),
            '3month' => plugin_lang_get('push_3months'),
            '1year' => plugin_lang_get('push_1year'),
        );
        
        $t_confirm_strings = array(
            'now' => plugin_lang_get('confirm_now'),
            'today' => plugin_lang_get('confirm_today'),
            '1week' => plugin_lang_get('confirm_1week'),
            '2weeks' => plugin_lang_get('confirm_2weeks'),
            '4weeks' => plugin_lang_get('confirm_4weeks'),
            '1month' => plugin_lang_get('confirm_1month'),
            '3month' => plugin_lang_get('confirm_3months'),
            '1year' => plugin_lang_get('confirm_1year'),
        );
        
        $t_page = plugin_page('adjust_due_date');
        
        $html = '<div class="btn-group duedate-adjuster">
            <button type="button" class="btn btn-primary btn-white btn-round btn-sm" data-toggle="dropdown">
                ' . lang_get('due_date') . ' <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">';
        
        foreach ($t_lang_strings as $t_interval => $t_label) {
            $t_confirm = $t_confirm_strings[$t_interval];
            $html .= '<li>
                <a href="' . $t_page . '&bug_id=' . $p_bug_id . '&interval=' . $t_interval . '" 
                   onclick="return confirm(\'' . addslashes($t_confirm) . '\');">' 
                   . $t_label . '</a>
            </li>';
        }
        
        $html .= '</ul></div>';
        
        return array($html);
    }
}
