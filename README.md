> **Note:** This plugin is designed for Finnish-language pages. All form fields and notifications use Finnish.

# Email List Plugin

A simple WordPress plugin for collecting email addresses and user information via a frontend form and storing them in a custom post type.

## Features

- Frontend form for collecting:
  - First name (Etunimi)
  - Last name (Sukunimi)
  - Email address (Sähköposti)
  - Organization (Organisaatio)
- AJAX form submission using the WordPress REST API
- Validation for required fields and email format (supports Finnish characters)
- Success and error notifications
- Stores submissions as a custom post type in the WordPress admin
- Admin search and custom columns for easy management

## Installation

1. Upload the plugin folder to your WordPress `/wp-content/plugins/` directory.
2. Activate the plugin from the WordPress admin panel.
3. Use the `[email-list-form]` shortcode to display the form on any page or post.

## Usage

Add the following shortcode to any page or post where you want the form to appear:

```
[email-list-form]
```

## Customization

- You can modify the form fields or validation logic in the plugin’s template and JS files.
- Success and error messages can be customized in the JavaScript file.

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher

## License

MIT

---

**Note:**  
This plugin is intended as a simple example and may require further customization for production use.
