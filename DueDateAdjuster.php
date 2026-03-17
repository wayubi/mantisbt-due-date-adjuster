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
        $this->version = '1.5';
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
        
        if (empty($t_bug->due_date)) {
            return array();
        }
        
        if (!access_has_bug_level(config_get('update_bug_threshold'), $p_bug_id)) {
            return array();
        }
        
        $t_lang_strings = array(
            'now' => plugin_lang_get('push_now'),
            'morning' => plugin_lang_get('push_morning'),
            'noon' => plugin_lang_get('push_noon'),
            'afternoon' => plugin_lang_get('push_afternoon'),
            'evening' => plugin_lang_get('push_evening'),
            'today' => plugin_lang_get('push_today'),
            'tomorrow' => plugin_lang_get('push_tomorrow'),
            'end_of_month' => plugin_lang_get('push_end_of_month'),
            'friday' => plugin_lang_get('push_friday'),
            'saturday' => plugin_lang_get('push_saturday'),
            'sunday' => plugin_lang_get('push_sunday'),
            'monday' => plugin_lang_get('push_monday'),
            '1day' => plugin_lang_get('push_1day'),
            '2days' => plugin_lang_get('push_2days'),
            '1week' => plugin_lang_get('push_1week'),
            '2weeks' => plugin_lang_get('push_2weeks'),
            '4weeks' => plugin_lang_get('push_4weeks'),
            '1month' => plugin_lang_get('push_1month'),
            '3month' => plugin_lang_get('push_3months'),
            '6month' => plugin_lang_get('push_6months'),
            '1year' => plugin_lang_get('push_1year'),
        );
        
        $t_current_due_date = $t_bug->due_date;
        $t_has_time = $t_current_due_date > 1;
        $t_time_str = $t_has_time ? date('H:i', $t_current_due_date) : '12:00';
        
        $t_time_presets = array(
            'morning' => '6am',
            'noon' => '12pm',
            'afternoon' => '3pm',
            'evening' => '9pm',
        );
        
        $t_confirm_strings = array(
            'now' => plugin_lang_get('confirm_now'),
            'morning' => 'Set due date to today at 6am?',
            'noon' => 'Set due date to today at noon?',
            'afternoon' => 'Set due date to today at 3pm?',
            'evening' => 'Set due date to today at 9pm?',
            'today' => 'Set due date to today at ' . ($t_has_time ? $t_time_str : 'noon') . '?',
            'tomorrow' => 'Set due date to tomorrow at ' . ($t_has_time ? $t_time_str : 'noon') . '?',
            'end_of_month' => 'Set due date to end of month at ' . ($t_has_time ? $t_time_str : 'noon') . '?',
            'friday' => 'Set due date to Friday at ' . ($t_has_time ? $t_time_str : 'noon') . '?',
            'saturday' => 'Set due date to Saturday at ' . ($t_has_time ? $t_time_str : 'noon') . '?',
            'sunday' => 'Set due date to Sunday at ' . ($t_has_time ? $t_time_str : 'noon') . '?',
            'monday' => 'Set due date to Monday at ' . ($t_has_time ? $t_time_str : 'noon') . '?',
            '1day' => plugin_lang_get('confirm_1day'),
            '2days' => plugin_lang_get('confirm_2days'),
            '1week' => plugin_lang_get('confirm_1week'),
            '2weeks' => plugin_lang_get('confirm_2weeks'),
            '4weeks' => plugin_lang_get('confirm_4weeks'),
            '1month' => plugin_lang_get('confirm_1month'),
            '3month' => plugin_lang_get('confirm_3months'),
            '6month' => plugin_lang_get('confirm_6months'),
            '1year' => plugin_lang_get('confirm_1year'),
        );
        
        $t_page = plugin_page('adjust_due_date');
        
        $html = '<div class="btn-group duedate-adjuster">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="dropdown">
                ' . lang_get('due_date') . ' <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">';
        
        foreach ($t_lang_strings as $t_interval => $t_label) {
            if ($t_interval === 'today') {
                $html .= '<li class="divider"></li>';
            }
            if ($t_interval === 'friday') {
                $html .= '<li class="divider"></li>';
            }
            if ($t_interval === '1day') {
                $html .= '<li class="divider"></li>';
            }
            if ($t_interval === '1week') {
                $html .= '<li class="divider"></li>';
            }
            $t_confirm = $t_confirm_strings[$t_interval];
            $html .= '<li>
                <a href="' . $t_page . '&bug_id=' . $p_bug_id . '&interval=' . $t_interval . '" 
                   onclick="return confirm(\'' . addslashes($t_confirm) . '\');">' 
                   . $t_label . '</a>
            </li>';
        }
        
        $html .= '<li class="divider"></li>
            <li><a href="#" data-toggle="modal" data-target="#duedate-custom-modal-' . $p_bug_id . '">' 
               . plugin_lang_get('push_custom') . '</a></li>
            <li><a href="' . $t_page . '&bug_id=' . $p_bug_id . '&interval=cleanup" 
               onclick="return confirm(\'' . addslashes(plugin_lang_get('confirm_cleanup')) . '\');">' 
               . plugin_lang_get('push_cleanup') . '</a></li>';
        
        $html .= '</ul></div>';
        
        if ($t_current_due_date > 1) {
            $t_date_default = date('Y-m-d', $t_current_due_date);
            $t_time_default = date('H:i', $t_current_due_date);
        } else {
            $t_date_default = date('Y-m-d');
            $t_time_default = '12:00';
        }
        
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
