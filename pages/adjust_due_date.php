<?php
/**
 * MantisBT - Due Date Adjuster Plugin
 * Adjust Due Date Page
 */

auth_ensure_user_authenticated();

$f_bug_id = gpc_get_int('bug_id');
$f_interval = gpc_get_string('interval');

// Verify bug exists
bug_ensure_exists($f_bug_id);

// Check access
access_ensure_bug_level(config_get('update_bug_threshold'), $f_bug_id);

// Get the bug
$t_bug = bug_get($f_bug_id);

// Check if bug has a due date
if ($t_bug->due_date == '') {
    error_parameters('No due date set');
    trigger_error(ERROR_GENERIC, ERROR);
}

// Calculate new due date
$t_current_due_date = $t_bug->due_date;
$t_datetime = new DateTime();
$t_datetime->setTimestamp($t_current_due_date);

switch ($f_interval) {
    case '1week':
        $t_datetime->modify('+1 week');
        $t_interval_text = '1 week';
        break;
    case '2weeks':
        $t_datetime->modify('+2 weeks');
        $t_interval_text = '2 weeks';
        break;
    case '4weeks':
        $t_datetime->modify('+4 weeks');
        $t_interval_text = '4 weeks';
        break;
    case '1month':
        $t_datetime->modify('+1 month');
        $t_interval_text = '1 month';
        break;
    default:
        error_parameters('Invalid interval');
        trigger_error(ERROR_GENERIC, ERROR);
}

$t_new_due_date = $t_datetime->getTimestamp();

// Update the due date using bug_set_field instead of $t_bug->update()
bug_set_field($f_bug_id, 'due_date', $t_new_due_date);

// Add note about the change
$t_note = sprintf( 
    'Due date pushed forward by %s (from %s to %s)',
    $t_interval_text,
    date('Y-m-d H:i', $t_current_due_date),
    date('Y-m-d H:i', $t_new_due_date)
);

bugnote_add( 
    $f_bug_id, 
    $t_note,
    0, // time tracking
    true, // private
    0, // bugnote type
    '', // attr
    null, // user_id
    false // send_email
);

// Redirect back to bug view
print_header_redirect_view($f_bug_id);