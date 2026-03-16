<?php
/**
 * MantisBT - Due Date Adjuster Plugin
 * Adjust Due Date Page
 */

auth_ensure_user_authenticated();

$f_bug_id = gpc_get_int('bug_id');
$f_interval = gpc_get_string('interval');

bug_ensure_exists($f_bug_id);

access_ensure_bug_level(config_get('update_bug_threshold'), $f_bug_id);

$t_bug = bug_get($f_bug_id);
$t_current_due_date = $t_bug->due_date;

if ($f_interval !== 'cleanup' && empty($t_current_due_date)) {
    error_parameters(plugin_lang_get('no_due_date'));
    trigger_error(ERROR_GENERIC, ERROR);
}

function add_month_with_clamp(DateTime $date, $months) {
    $originalDay = (int)$date->format('j');
    $daysInOriginalMonth = (int)$date->format('t');
    $isLastDayOfMonth = ($originalDay >= $daysInOriginalMonth);

    $originalMonth = (int)$date->format('n');
    $originalYear = (int)$date->format('Y');
    $targetMonth = $originalMonth + $months;
    $targetYear = $originalYear;

    while ($targetMonth > 12) {
        $targetMonth -= 12;
        $targetYear++;
    }

    $tempDate = new DateTime();
    $tempDate->setDate($targetYear, $targetMonth, 1);
    $daysInTargetMonth = (int)$tempDate->format('t');

    if ($isLastDayOfMonth) {
        $date->setDate($targetYear, $targetMonth, $daysInTargetMonth);
    } else {
        $date->modify("+{$months} month");
    }

    return $date;
}

$t_current_due_date = $t_bug->due_date;
$t_has_time = !empty($t_current_due_date);
$t_hour = $t_has_time ? (int)date('H', $t_current_due_date) : 12;
$t_minute = $t_has_time ? (int)date('i', $t_current_due_date) : 0;

$t_interval_map = array(
    'now' => array('type' => 'now', 'text' => plugin_lang_get('push_now')),
    'morning' => array('type' => 'time_preset', 'hour' => 6, 'text' => plugin_lang_get('push_morning')),
    'noon' => array('type' => 'time_preset', 'hour' => 12, 'text' => plugin_lang_get('push_noon')),
    'afternoon' => array('type' => 'time_preset', 'hour' => 15, 'text' => plugin_lang_get('push_afternoon')),
    'evening' => array('type' => 'time_preset', 'hour' => 21, 'text' => plugin_lang_get('push_evening')),
    'today' => array('type' => 'today', 'text' => plugin_lang_get('push_today')),
    'tomorrow' => array('type' => 'tomorrow', 'text' => plugin_lang_get('push_tomorrow')),
    'end_of_month' => array('type' => 'end_of_month', 'text' => plugin_lang_get('push_end_of_month')),
    'friday' => array('type' => 'day_of_week', 'day' => 5, 'text' => plugin_lang_get('push_friday')),
    'saturday' => array('type' => 'day_of_week', 'day' => 6, 'text' => plugin_lang_get('push_saturday')),
    'sunday' => array('type' => 'day_of_week', 'day' => 0, 'text' => plugin_lang_get('push_sunday')),
    'monday' => array('type' => 'day_of_week', 'day' => 1, 'text' => plugin_lang_get('push_monday')),
    '1week' => array('type' => 'modify', 'modifier' => '+1 week', 'text' => plugin_lang_get('push_1week')),
    '2weeks' => array('type' => 'modify', 'modifier' => '+2 weeks', 'text' => plugin_lang_get('push_2weeks')),
    '4weeks' => array('type' => 'modify', 'modifier' => '+4 weeks', 'text' => plugin_lang_get('push_4weeks')),
    '1month' => array('type' => 'add_months', 'months' => 1, 'text' => plugin_lang_get('push_1month')),
    '3month' => array('type' => 'add_months', 'months' => 3, 'text' => plugin_lang_get('push_3months')),
    '1year' => array('type' => 'modify', 'modifier' => '+1 year', 'text' => plugin_lang_get('push_1year')),
    'custom' => array('type' => 'custom', 'text' => plugin_lang_get('push_custom')),
    'cleanup' => array('type' => 'cleanup', 'text' => plugin_lang_get('push_cleanup')),
);

if (!isset($t_interval_map[$f_interval])) {
    error_parameters(plugin_lang_get('invalid_interval'));
    trigger_error(ERROR_GENERIC, ERROR);
}

$t_interval_data = $t_interval_map[$f_interval];

if ($t_interval_data['type'] === 'now') {
    $t_new_due_date = time();
} elseif ($t_interval_data['type'] === 'time_preset') {
    $t_datetime = new DateTime('today');
    $t_datetime->setTime($t_interval_data['hour'], 0, 0);
    $t_new_due_date = $t_datetime->getTimestamp();
} elseif ($t_interval_data['type'] === 'today') {
    $t_datetime = new DateTime('today');
    $t_datetime->setTime($t_hour, $t_minute, 0);
    $t_new_due_date = $t_datetime->getTimestamp();
} elseif ($t_interval_data['type'] === 'tomorrow') {
    $t_datetime = new DateTime('tomorrow');
    $t_datetime->setTime($t_hour, $t_minute, 0);
    $t_new_due_date = $t_datetime->getTimestamp();
} elseif ($t_interval_data['type'] === 'end_of_month') {
    $t_datetime = new DateTime('today');
    $t_days_in_month = (int)$t_datetime->format('t');
    $t_datetime->setDate((int)$t_datetime->format('Y'), (int)$t_datetime->format('m'), $t_days_in_month);
    $t_datetime->setTime($t_hour, $t_minute, 0);
    $t_new_due_date = $t_datetime->getTimestamp();
} elseif ($t_interval_data['type'] === 'day_of_week') {
    $t_datetime = new DateTime('today');
    $targetDay = $t_interval_data['day'];
    $currentDay = (int)$t_datetime->format('w');
    $daysUntil = $targetDay - $currentDay;
    if ($daysUntil <= 0) {
        $daysUntil += 7;
    }
    $t_datetime->modify("+{$daysUntil} days");
    $t_datetime->setTime($t_hour, $t_minute, 0);
    $t_new_due_date = $t_datetime->getTimestamp();
} elseif ($t_interval_data['type'] === 'custom') {
    $f_date = gpc_get_string('date');
    $f_time = gpc_get_string('time');
    $t_datetime = new DateTime($f_date . ' ' . $f_time);
    $t_new_due_date = $t_datetime->getTimestamp();
} elseif ($t_interval_data['type'] === 'cleanup') {
    // No date calculation needed for cleanup
} elseif ($t_interval_data['type'] === 'add_months') {
    $t_datetime = new DateTime();
    $t_datetime->setTimestamp($t_current_due_date);
    add_month_with_clamp($t_datetime, $t_interval_data['months']);
    $t_new_due_date = $t_datetime->getTimestamp();
} else {
    $t_datetime = new DateTime();
    $t_datetime->setTimestamp($t_current_due_date);
    $t_datetime->modify($t_interval_data['modifier']);
    $t_new_due_date = $t_datetime->getTimestamp();
}

if ($t_interval_data['type'] !== 'cleanup') {
    bug_set_field($f_bug_id, 'due_date', $t_new_due_date);
}

$t_note_tag = ' #DueDateAdjuster';

if ($t_interval_data['type'] === 'now') {
    $t_note = sprintf(
        plugin_lang_get('note_now'),
        date('Y-m-d H:i', $t_current_due_date)
    ) . $t_note_tag;
} elseif ($t_interval_data['type'] === 'time_preset') {
    $t_time_labels = array(6 => '6am', 12 => '12pm', 15 => '3pm', 21 => '9pm');
    $t_note = sprintf(
        plugin_lang_get('note'),
        'today at ' . $t_time_labels[$t_interval_data['hour']],
        date('Y-m-d H:i', $t_current_due_date),
        date('Y-m-d H:i', $t_new_due_date)
    ) . $t_note_tag;
} elseif ($t_interval_data['type'] === 'today') {
    $t_note = sprintf(
        plugin_lang_get('note'),
        'today at ' . date('H:i', $t_new_due_date),
        date('Y-m-d H:i', $t_current_due_date),
        date('Y-m-d H:i', $t_new_due_date)
    ) . $t_note_tag;
} elseif ($t_interval_data['type'] === 'tomorrow') {
    $t_note = sprintf(
        plugin_lang_get('note'),
        'tomorrow at ' . date('H:i', $t_new_due_date),
        date('Y-m-d H:i', $t_current_due_date),
        date('Y-m-d H:i', $t_new_due_date)
    ) . $t_note_tag;
} elseif ($t_interval_data['type'] === 'end_of_month') {
    $t_note = sprintf(
        plugin_lang_get('note'),
        'end of month at ' . date('H:i', $t_new_due_date),
        date('Y-m-d H:i', $t_current_due_date),
        date('Y-m-d H:i', $t_new_due_date)
    ) . $t_note_tag;
} elseif ($t_interval_data['type'] === 'day_of_week') {
    $dayNames = array(0 => 'Sunday', 1 => 'Monday', 5 => 'Friday', 6 => 'Saturday');
    $t_note = sprintf(
        plugin_lang_get('note'),
        $dayNames[$t_interval_data['day']] . ' at ' . date('H:i', $t_new_due_date),
        date('Y-m-d H:i', $t_current_due_date),
        date('Y-m-d H:i', $t_new_due_date)
    ) . $t_note_tag;
} elseif ($t_interval_data['type'] === 'custom') {
    $t_note = sprintf(
        plugin_lang_get('note_custom'),
        date('Y-m-d H:i', $t_new_due_date),
        date('Y-m-d H:i', $t_current_due_date)
    ) . $t_note_tag;
} elseif ($t_interval_data['type'] === 'cleanup') {
    $t_tag = '#DueDateAdjuster';
    $t_query = "SELECT b.id, b.date_submitted FROM {bugnote} b
                JOIN {bugnote_text} t ON b.bugnote_text_id = t.id
                WHERE b.bug_id = " . db_param() . " AND t.note LIKE " . db_param() . "
                ORDER BY b.date_submitted DESC";
    $t_result = db_query($t_query, array($f_bug_id, '%' . $t_tag));
    
    $t_notes_to_keep = array();
    $t_notes_to_delete = array();
    $t_count = 0;
    
    while ($t_row = db_fetch_array($t_result)) {
        $t_count++;
        if ($t_count <= 3) {
            $t_notes_to_keep[] = $t_row['id'];
        } else {
            $t_notes_to_delete[] = $t_row['id'];
        }
    }
    
    foreach ($t_notes_to_delete as $t_note_id) {
        bugnote_delete($t_note_id);
    }
    
    $t_note = plugin_lang_get('note_cleanup') . $t_note_tag;
} else {
    $t_note = sprintf(
        plugin_lang_get('note'),
        $t_interval_data['text'],
        date('Y-m-d H:i', $t_current_due_date),
        date('Y-m-d H:i', $t_new_due_date)
    ) . $t_note_tag;
}

bugnote_add(
    $f_bug_id,
    $t_note,
    0,
    true,
    0,
    '',
    null,
    false
);

print_header_redirect_view($f_bug_id);
