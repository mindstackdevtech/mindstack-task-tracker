# Update 4: About us and Contact us

Install updates 1, 2 and 3 first.

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

No `php artisan migrate` needed.

## What is new

- **About us** page at `/about`
- **Contact us** page at `/contact` with a form that emails you
- Links to both at the top and bottom of the log in, sign-up and password
  pages, and under your name in the side menu

## Where contact messages go

To the address in `MAIL_FROM_ADDRESS` in `.env`. To send them somewhere
else, add this line to `.env`:

```
CONTACT_EMAIL=you@example.com
```

Replying to the email replies to the person who wrote.

## Changing the About us text

Edit `resources/views/pages/about.blade.php`.

## Files

New:

- `app/Http/Controllers/PageController.php`
- `resources/views/layouts/public.blade.php`
- `resources/views/partials/public-nav.blade.php`
- `resources/views/partials/public-footer.blade.php`
- `resources/views/pages/about.blade.php`
- `resources/views/pages/contact.blade.php`

Replaced:

- `routes/web.php`
- `resources/views/layouts/app.blade.php`
- `public/css/extras.css`
