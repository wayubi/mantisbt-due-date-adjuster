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

if ($t_bug->due_date == '') {
    error_parameters(plugin_lang_get('no_due_date'));
    trigger_error(ERROR_GENERIC, ERROR);
}

function add_month_with_clamp(DateTime $date, $months) {
    $originalDay = (int)$date->format('j');
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

    $date->setDate($targetYear, $targetMonth, min($originalDay, $daysInTargetMonth));

    return $date;
}

$t_current_due_date = $t_bug->due_date;

$t_interval_map = array(
    'now' => array('type' => 'now', 'text' => plugin_lang_get('push_now')),
    'today' => array('type' => 'today', 'text' => plugin_lang_get('push_today')),
    '1week' => array('type' => 'modify', 'modifier' => '+1 week', 'text' => plugin_lang_get('push_1week')),
    '2weeks' => array('type' => 'modify', 'modifier' => '+2 weeks', 'text' => plugin_lang_get('push_2weeks')),
    '4weeks' => array('type' => 'modify', 'modifier' => '+4 weeks', 'text' => plugin_lang_get('push_4weeks')),
    '1month' => array('type' => 'add_months', 'months' => 1, 'text' => plugin_lang_get('push_1month')),
    '3month' => array('type' => 'add_months', 'months' => 3, 'text' => plugin_lang_get('push_3months')),
);

if (!isset($t_interval_map[$f_interval])) {
    error_parameters(plugin_lang_get('invalid_interval'));
    trigger_error(ERROR_GENERIC, ERROR);
}

$t_interval_data = $t_interval_map[$f_interval];

if ($t_interval_data['type'] === 'now') {
    $t_new_due_date = time();
} elseif ($t_interval_data['type'] === 'today') {
    $t_datetime = new DateTime('today noon');
    $t_new_due_date = $t_datetime->getTimestamp();
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

bug_set_field($f_bug_id, 'due_date', $t_new_due_date);

if ($t_interval_data['type'] === 'now') {
    $t_note = sprintf(
        plugin_lang_get('note_now'),
        date('Y-m-d H:i', $t_current_due_date)
    );
} elseif ($t_interval_data['type'] === 'today') {
    $t_note = sprintf(
        plugin_lang_get('note_today'),
        date('Y-m-d H:i', $t_current_due_date)
    );
} else {
    $t_note = sprintf(
        plugin_lang_get('note'),
        $t_interval_data['text'],
        date('Y-m-d H:i', $t_current_due_date),
        date('Y-m-d H:i', $t_new_due_date)
    );
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
