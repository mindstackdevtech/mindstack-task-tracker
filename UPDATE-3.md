# Update 3: security hardening, activity history, export

Install updates 1 and 2 first.

## Install

1. Right-click the zip, choose **Extract All**, and set the destination to
   `C:\xampp1\htdocs\mindstack-task-tracker`. Choose **Replace the files in
   the destination** when asked.
2. In the project folder run:

   ```
   php artisan view:clear
   php artisan route:clear
   php artisan serve
   ```

There is no new database table this time, so `php artisan migrate` is not
needed.

## What is new

Security (details in `SECURITY.md`):

- Browser security headers on every page
- Stronger password rule on sign-up, reset and change
- Rate limits on log in, sign-up, password reset, invites, chat, comments
- Failed logins are logged
- Changing your email needs your password; the old address is warned
- Password change alert by notification and email
- The project owner can reset the invite link

Features:

- **Activity history:** a full, paged list of what happened in a project
- **Export tasks (CSV):** download a project's tasks as a spreadsheet

Existing passwords keep working. The stronger rule applies the next time a
password is set.

## Files

New:

- `app/Http/Middleware/SecurityHeaders.php`
- `app/Support/Security.php`
- `app/Http/Controllers/ProjectToolsController.php`
- `resources/views/projects/activity.blade.php`
- `SECURITY.md`

Replaced:

- `routes/web.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/PasswordResetController.php`
- `app/Http/Controllers/AccountController.php`
- `app/Http/Controllers/ProjectMemberController.php`
- `resources/views/auth/register.blade.php`
- `resources/views/auth/reset-password.blade.php`
- `resources/views/account/edit.blade.php`
- `resources/views/projects/show.blade.php`
- `public/css/extras.css`
