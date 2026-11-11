# Chum Chum's — Homepage (Phase 1)

PHP 7.4+ / 8.x · MySQL 5.7+ or MariaDB 10.3+ · HTML5 · CSS3 · vanilla JavaScript.
No frameworks, no build step, no Composer — upload and run on any cPanel host.

## Install on cPanel

1. **Create the database** — cPanel → *MySQL® Databases*:
   - Create a database (e.g. `cpuser_chumchums`).
   - Create a user with a strong password and add it to the database with **All Privileges**.
2. **Import the content** — cPanel → *phpMyAdmin* → select the new database → *Import* →
   choose `database/chumchums.sql` → *Go*.
3. **Upload the files** — cPanel → *File Manager* → `public_html` (or a sub-folder) →
   upload the zip and *Extract*. Make sure the hidden `.htaccess` files are included.
4. **Connect** — edit `config/config.php` and set `DB_NAME`, `DB_USER` and `DB_PASS`
   (keep `DB_HOST` as `localhost`).
5. Visit the site. If the database can't be reached the page still renders with the
   built-in default content, and the error is written to the PHP error log.

## Folder structure

```
index.php                 Homepage
admin/                    Admin panel (login, content editing, users)
config/config.php         Database credentials & site options (web access blocked)
includes/bootstrap.php    PDO connection + helpers
includes/content.php      Queries + default content fallbacks
includes/icons.php        Inline SVG icons
includes/partials/        Shared header (with menu drawer) and footer
database/chumchums.sql    Schema + seed data for a fresh install (web access blocked)
database/updates/         Incremental SQL for existing databases — run each new file once in phpMyAdmin
assets/css/style.css      All styles (mobile → tablet → desktop)
assets/js/main.js         Menu drawer + testimonial slider
assets/fonts/             DM Sans (self-hosted, variable)
assets/img/               Images
```

## Admin panel

Go to **`/admin/`** (e.g. https://www.chumchums.com.au/admin/).

- **First visit:** you are sent to `admin/setup.php` to create the first admin account.
  The *setup key* is the database password (`DB_PASS` in `config/config.php`).
  Once an account exists, the setup page is permanently disabled.
- **Sidebar:** Dashboard · **Home Page** · Site Settings · Account & Users.
  - *Home Page* has a tab bar with every homepage section, in page order: Hero, Slider,
    About, Opening Hours, Showcase Grid, Reservation, Testimonials.
  - *Site Settings* holds site-wide options: General & Contact, Links, Footer, SEO, Maintenance Mode.
  - New pages (About Us, Gallery, Contact…) will each get their own sidebar item with the same tab bar.
- **Images** uploaded in the admin are saved to `assets/img/uploads/` (JPG/PNG/WebP/GIF, max 5 MB).
  That folder must be writable by PHP (cPanel default 755 is fine).
- **Security:** hashed passwords, CSRF-protected forms, 5 failed logins per IP → 15 min lockout,
  auto-logout after 2 hours idle.
- **Forgot password?** Another admin can add you a new account under *Account & Users*.
  If there is no other admin, delete your row from the `admin_users` table in phpMyAdmin and
  visit `/admin/` again to re-run setup.

## Editing content

All homepage text is in the database, editable through the admin panel (or directly in phpMyAdmin):

| Table             | Controls                                                         |
|-------------------|------------------------------------------------------------------|
| `settings`        | Headings, paragraphs, phone, email, address, social & order links |
| `menu_categories` | The dish pills around the hero (`side` = left / right). Each one is also a **hero slide**: clicking the pill shows its `hero_title`, `hero_subtitle`, `hero_badge` and `hero_image` (empty fields fall back to the Hero defaults in `settings`). |
| `opening_hours`   | Rows in the yellow Opening Hours card                            |
| `showcase_items`  | The 7 grid tiles (slot 1 feature, 2/4/6 text, 3/5/7 images)      |
| `testimonials`    | Slides in the yellow testimonial slider (`is_active` to hide)    |

Image fields hold a file name inside `assets/img/` (or a full URL).

## Responsive behaviour

- **≥ 1200 px** — the 1440 px design layout, full width: it scales proportionally to fill
  any screen (smaller laptops and large 1920 px / 2560 px monitors alike).
- **768–1199 px** — tablet layout (stacked about section, 2-column dish grid).
- **< 768 px** — mobile layout; navigation moves into the "Menu" drawer.

## Links pending later phases

"About Us", "Gallery", "Contact Us" and "Menu" currently jump to the matching homepage
sections. "Order Now", "Current Customer?" and the social icons use `#` until real URLs
are added in the `settings` table (`order_url`, `customer_url`, `facebook_url`, `instagram_url`).
