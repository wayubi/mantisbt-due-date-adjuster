# MantisBT Due Date Adjuster Plugin

## Description

This plugin adds functionality to MantisBT that allows users to quickly adjust issue due dates:

- **Set to Now**: Sets due date to current date and time
- **Set to Today**: Sets due date to today at noon (12:00)
- **Push Forward**: Add 1 week, 2 weeks, 4 weeks, 1 month, or 3 months to existing due date

The plugin preserves the original time when adjusting the date (except for Now/Today options).

## Features

- **Quick Action Dropdown**: Adds a dropdown menu on the issue view page
- **Set to Now**: Quickly set due date to current date and time
- **Set to Today**: Set due date to today at noon
- **Push Forward Options**: Add 1 week, 2 weeks, 4 weeks, 1 month, or 3 months
- **Time Preservation**: Maintains the original time (hours and minutes) when adjusting dates
- **History Tracking**: Logs all due date changes in the issue history
- **Automatic Notes**: Adds a bugnote documenting the change
- **Permission Checks**: Only users with update permissions can adjust due dates
- **Confirmation Dialogs**: Prompts for confirmation before making changes
- **Internationalization**: Supports multiple languages via language strings

## Requirements

- MantisBT 2.0.0 or higher
- PHP 5.6 or higher (for DateTime functionality)

## Installation

1. Download the plugin files
2. Extract the `DueDateAdjuster` folder
3. Upload the entire `DueDateAdjuster` folder to your MantisBT `plugins` directory
4. Log in to MantisBT as an administrator
5. Navigate to **Manage** → **Manage Plugins**
6. Find "Due Date Adjuster" in the available plugins list
7. Click **Install**

## File Structure

```
DueDateAdjuster/
├── DueDateAdjuster.php          # Main plugin file
├── pages/
│   └── adjust_due_date.php      # Page handling the date adjustment logic
├── lang/
│   └── strings_english.txt      # Language strings
└── README.md                     # This file
```

## Usage

### From the Issue View Page

When viewing an issue that has a due date set, you will see a dropdown menu in the issue details:

- **Now**: Sets the due date to the current date and time
- **Today**: Sets the due date to today at noon (12:00)
- **+1 Week**: Pushes the due date forward by 1 week
- **+2 Weeks**: Pushes the due date forward by 2 weeks
- **+4 Weeks**: Pushes the due date forward by 4 weeks
- **+1 Month**: Pushes the due date forward by 1 month
- **+3 Months**: Pushes the due date forward by 3 months

Simply click the desired option and confirm the action.

### From the Issue Menu

The same options are also available in the issue action menu (accessible via the dropdown menu on the issue).

## How It Works

1. User clicks one of the adjustment options
2. A confirmation dialog appears
3. Upon confirmation, the plugin:
   - Calculates the new due date by adding the selected interval
   - Preserves the original time (hours and minutes)
   - Updates the issue with the new due date
   - Logs the change in the issue history
   - Adds a private bugnote documenting the change
4. User is redirected back to the issue view page

## Example

If an issue has a due date of:
- **Original**: 2026-02-15 14:30:00

And you click "+1 Week", the new due date will be:
- **New**: 2026-02-22 14:30:00

Notice that the time (14:30:00) is preserved.

## Permissions

Only users with permission to update issues (based on the `update_bug_threshold` configuration) can see and use the due date adjustment features.

## Internationalization

Language strings can be customized in the `lang/strings_english.txt` file or by adding language-specific files (e.g., `strings_french.txt`).

## Notes

- The plugin only appears when an issue already has a due date set
- All changes are logged in the issue history for audit purposes
- A private bugnote is automatically added to document the change
- The plugin uses MantisBT's built-in security features
- The dropdown uses MantisBT's native Bootstrap-based styling

## Troubleshooting

**Q: I don't see the adjustment dropdown**
- Ensure the issue has a due date set
- Verify you have permission to update the issue
- Check that the plugin is installed and enabled

**Q: The date isn't adjusting correctly**
- Verify your server's PHP DateTime functionality is working
- Check your MantisBT date/time configuration settings

## Support

For issues or questions, please refer to the MantisBT documentation or community forums.

## License

This plugin follows the same license as MantisBT (GPL v2).

## Version History

**1.0.2** - Added Now and Today options
- Added "Now" option to set due date to current date and time
- Added "Today" option to set due date to today at noon

**1.0.1** - Code quality improvements
- Added proper internationalization support with language strings
- Fixed button styling to use MantisBT's native Bootstrap dropdown
- Added +4 weeks and +3 months options
- Refactored code for better maintainability

**1.0.0** - Initial release
- Basic functionality for adjusting due dates by 1 week, 2 weeks, or 1 month
- Integration with issue view page and issue menu
- History tracking and automatic note creation
