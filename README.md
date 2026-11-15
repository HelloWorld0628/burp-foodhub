# BURP FoodHub Project

A full-stack PHP/MySQL food ordering system with customer, admin, and driver portals.

## Attribution

**Code by:** Kevin Calleja ([@HelloWorld0628](https://github.com/HelloWorld0628))  
**Brand:** BURP FoodHub  
**License:** MIT (see [LICENSE](LICENSE))

---

## Project Layout

```
burp-foodhub/
├── public/          # Document root (web-accessible)
│   ├── index.html           # Customer home
│   ├── menu.html            # Menu (public view, login required for cart)
│   ├── checkout.html        # Protected checkout (pre-fills from session)
│   ├── track.html           # Track order + My Orders tab (logged-in)
│   ├── login.html           # Customer login
│   ├── register.html        # Customer registration
│   ├── admin*.html          # Admin portal
│   ├── delivery*.html       # Driver portal
│   └── assets/              # CSS, JS, images
├── src/             # PHP API (all use config.php + env vars)
│   ├── config.php           # DB config from $_ENV
│   ├── customer_auth.php    # Register, Login, Logout, Check, Profile
│   ├── process_order.php    # Order processing
│   ├── track_order.php      # Track by number / list by email
│   ├── delivery_*.php       # Driver API
│   └── admin_*.php          # Admin API
├── database/        # SQL files
│   ├── schema.sql           # Clean schema + menu items only
│   ├── burp_foodhub.sql     # Full dump (no sensitive data)
│   └── burp_foodhub_updated.sql
├── .env.example     # Railway env var template
└── .htaccess        # Routes to public/
```

---

## Features

### Customer Portal
- Browse menu (public)
- Add to cart → prompts login
- Secure checkout with receipt upload
- Order tracking by number
- **My Orders** tab (logged-in users see full history)
- Session-based authentication with password hashing

### Admin Portal (`/admin.html`)
- Dashboard with order management
- Menu CRUD (add/edit/delete items with image upload)
- Customer list
- Order status updates (Pending → Preparing)

### Driver Portal (`/delivery_dashboard.html`)
- Login/Register
- View available orders
- Accept orders
- Mark "On the Way"
- Upload delivery proof (photo + GPS)
- Mark "Delivered"

---

## Quick Start (Local)

1. **Requirements:** PHP 8.x, MySQL/MariaDB, Apache/Nginx
2. **Database:** Create `burp_foodhub` DB, import `database/schema.sql`
3. **Config:** Copy `.env.example` to `.env` and fill in DB credentials
4. **Serve:** Point document root to `public/` (or use `.htaccess` rewrite)
5. **Visit:** `http://localhost/`

---

## Security Notes

- Uses `password_hash()` / `password_verify()` for customer passwords
- Database credentials via environment variables (never hardcoded)
- SQL injection prevention via `real_escape_string`
- Admin cannot set "Delivered" status (driver-only)
- Upload validation (type, size)
- `.htaccess` protects upload directories from script execution

**Not production-ready without:** HTTPS enforcement, CSRF tokens, rate limiting, input sanitization hardening, audit logging.

---

## License

MIT License - see [LICENSE](LICENSE) for details.
