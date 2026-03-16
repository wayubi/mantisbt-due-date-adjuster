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
    
    // function resources($p_event) {
    //     return '<style>
    //         .duedate-adjuster .caret {
    //             border-top-color: #6688a6 !important;
    //             border-bottom-color: #6688a6 !important;
    //         }
    //     </style>';
    // }
    
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
        $t_bug_url = string_get_bug_view_url($p_bug_id);
        
        $html = '<div class="btn-group duedate-adjuster">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="dropdown">
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
        
        $html .= '<li class="divider"></li>
            <li><a href="#" data-toggle="modal" data-target="#duedate-custom-modal-' . $p_bug_id . '">' 
               . plugin_lang_get('push_custom') . '</a></li>';
        
        $html .= '</ul></div>';
        
        $t_date_default = date('Y-m-d');
        $t_time_default = '12:00';
        
        $html .= '
<div class="modal fade" id="duedate-custom-modal-' . $p_bug_id . '" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">' . plugin_lang_get('push_custom') . '</h4>
      </div>
      <form method="post" action="' . $t_page . '">
        <input type="hidden" name="bug_id" value="' . $p_bug_id . '" />
        <input type="hidden" name="interval" value="custom" />
        <div class="modal-body">
          <div class="form-group">
            <label for="date-' . $p_bug_id . '">Date</label>
            <input type="date" id="date-' . $p_bug_id . '" name="date" class="form-control" value="' . $t_date_default . '" required />
          </div>
          <div class="form-group">
            <label for="time-' . $p_bug_id . '">Time</label>
            <input type="time" id="time-' . $p_bug_id . '" name="time" class="form-control" value="' . $t_time_default . '" required />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" name="submit" value="1" class="btn btn-primary">Set Due Date</button>
        </div>
      </form>
    </div>
  </div>
</div>';
        
        return array($html);
    }
}
