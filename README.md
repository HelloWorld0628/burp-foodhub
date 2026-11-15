# Burp Foodhub

## Project layout

- `public/` contains the PHP endpoints, web pages, styles, scripts, and image assets.
- `database/schema.sql` is the sanitized schema/setup file, with the public menu catalog only.
- `database/local/` contains the original local SQL backups. These are intentionally excluded from Git because they contain account credentials and customer/order data.
- `public/payments_img/` and `public/delivery_img/` are runtime upload directories and are excluded from Git.

The page and API paths are kept the same. For Apache, either set the site's document root to `public/` (recommended), or serve the project root with `mod_rewrite` and `.htaccess` enabled; the root rewrite file forwards existing paths into `public/`. The project needs PHP with `mysqli` and a MySQL-compatible database. A static Live Server preview does not execute PHP.

To initialize a clean local database, create a database named `burp_foodhub` and import `database/schema.sql`. Do not import or publish the files under `database/local/`.

## GitHub and deployment safety

The payment and delivery upload folders are ignored by Git, but the application currently serves those images from the web root. Git ignore rules do not make those files private on a live site. Treat them as sensitive and do not deploy real customer uploads until access-controlled file serving is implemented.

This repository is **not ready for public production deployment**. The application still has development-only security issues, including hard-coded database credentials, plaintext password handling, and API operations that trust client-supplied driver IDs or lack server-side authorization. Moving files and excluding local data does not fix those issues. Do not expose the app to the public internet until authentication, authorization, password hashing, and database configuration have been hardened and tested.

If a database dump or upload was already pushed to GitHub, deleting it in a later commit does not remove it from Git history. Remove it from repository history and rotate any credentials it contained before sharing the repository.
