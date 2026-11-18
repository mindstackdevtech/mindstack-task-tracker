# MindStack Task Tracker: complete update

This one zip holds every file added or changed since the first version:
forgot password, emails, notifications, team chat, account page, calendar,
security hardening, activity history, export, About us and Contact us.
It replaces updates 1 to 5, so you only need this one.

## Install

1. Right-click the zip, choose **Extract All**, and set the destination to
   `C:\xampp1\htdocs\mindstack-task-tracker`.
2. Choose **Replace the files in the destination** when asked.
3. In the project folder run:

   ```
   php artisan migrate
   php artisan view:clear
   php artisan route:clear
   php artisan config:clear
   php artisan serve
   ```

`php artisan migrate` only adds tables that are missing (notifications and
chat messages). Your accounts and projects stay. Do not use `migrate:fresh`.

## Put in your own links

Open `config\mindstack.php` and change these three lines:

```php
'github' => 'https://github.com/your-username',
'email' => 'your-email@example.com',
'facebook' => 'https://facebook.com/your-page',
```

They appear on the About us and Contact us pages. Set one to `''` to hide it.
Your name, role and one-line description are in the same file.

## Contact form

Messages go to `MAIL_FROM_ADDRESS` in `.env`. To send them elsewhere add:

```
CONTACT_EMAIL=you@example.com
```

## Security

See `SECURITY.md` for what the app protects and the checklist for going
online.
