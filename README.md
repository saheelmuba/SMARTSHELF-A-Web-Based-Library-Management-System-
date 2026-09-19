# 📚 SmartShelf — Smart Library Management System

A complete, modern **Library Management System** built with **HTML, CSS, JavaScript, PHP and MySQL** — no frameworks (no Laravel, React, WordPress or Joomla). Built for the **HDIT 21193 — User Experience & Interface Design** final project, and engineered to *exceed* the brief.

![Stack](https://img.shields.io/badge/PHP-MySQL-blue) ![No Frameworks](https://img.shields.io/badge/frameworks-none-success) ![Responsive](https://img.shields.io/badge/UI-responsive-6d5efc)

---

## ✨ Highlights

- 🎨 **Unique, modern UI** with a custom design system, **dark / light mode**, glassmorphism navbar, animated counters, scroll reveals and pure-CSS charts.
- 👥 **Three roles** — Administrator, Librarian and Member — each with a tailored, secure dashboard.
- 🔐 **Full security** — `password_hash()`, prepared statements everywhere, input sanitisation, CSRF tokens, session hardening & fixation protection, role guards.
- 📱 **Fully responsive** — desktop, tablet and mobile.
- 🧩 **Complete CRUD** for Books, Features, Users and Categories (plus borrowings, reservations and fines management).

## 🚀 Modern features (beyond the brief)

| Feature | What it does |
|---|---|
| 🔎 **Smart search & filters** | Live client-side search + server-side filtering by title/author/ISBN, category and availability |
| 🤖 **AI book recommendations** | Personalised picks from your reading history, with a trending fallback |
| 🔲 **QR / barcode** | Every book generates a scannable QR code for fast issue/return |
| 📖 **Digital library** | Read & download eBooks (PDF) in an embedded reader |
| 📨 **Auto reminders & notifications** | Due-soon, overdue, reservation-ready and fine alerts |
| ⏳ **Reservation queue** | Reserve borrowed books and auto-advance the queue on return |
| 💰 **Automated fines** | Daily overdue calculation with a transparent ledger |
| 📊 **Analytics dashboards** | Borrowing trends, top books/readers, category mix, inventory utilisation |
| 🔄 **Real-time inventory** | Copy counts update instantly on every borrow/return/reserve |
| 📝 **Reviews & ratings** | Members rate and review books |

---

## 🛠️ Installation (XAMPP)

1. **Copy** this whole folder into your XAMPP web root and rename it (e.g. `C:\xampp\htdocs\smartshelf`).
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open **phpMyAdmin** → http://localhost/phpmyadmin
4. Click **Import** → choose `database/smartshelf_library.sql` → **Go**.
   *(The script creates the `smartshelf_library` database automatically.)*
5. Open the app: **http://localhost/smartshelf/**

### Database configuration
Connection settings live in [`includes/config.php`](includes/config.php). The defaults match a standard XAMPP install:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');   // ← if your MySQL root has a password, set it here
define('DB_NAME', 'smartshelf_library');
```

> If you see a database connection error, set `DB_PASS` to your MySQL root password.

---

## 🔑 Demo accounts

| Role | Email | Password |
|---|---|---|
| **Administrator** | `admin@smartshelf.lk` | `admin123` |
| **Librarian** | `librarian@smartshelf.lk` | `lib123` |
| **Member** | `member@smartshelf.lk` | `member123` |

*(All other seeded members also use `member123`.)*

---

## 📂 Folder structure

```
smartshelf/
├── assets/
│   ├── css/style.css          # Full design system (light/dark)
│   ├── js/script.js           # Theme, validation, live search, QR, charts
│   └── images/                # Image assets (covers/avatars)
├── includes/
│   ├── config.php             # DB connection + constants + session
│   ├── functions.php          # Helpers: auth, CSRF, fines, recommendations
│   ├── header.php             # Shared navbar + <head>
│   ├── footer.php             # Shared footer
│   └── admin_sidebar.php      # Staff dashboard navigation
├── admin/                     # Staff area (role-guarded)
│   ├── admin_dashboard.php    # Analytics dashboard
│   ├── manage_books.php       # Books CRUD …
│   ├── add_book.php  edit_book.php  delete_book.php
│   ├── manage_features.php    # Features CRUD …
│   ├── add_features.php  edit_features.php  delete_features.php
│   ├── manage_user.php        # Users CRUD …
│   ├── add_user.php  edit_user.php  delete_user.php
│   ├── manage_borrowings.php  # Issue / return
│   ├── manage_reservations.php
│   ├── manage_fines.php
│   ├── manage_categories.php
│   ├── messages.php           # Contact inbox
│   ├── reports.php            # Analytics & charts
│   └── activity_log.php       # Audit trail
├── uploads/
│   ├── covers/                # Uploaded book covers
│   └── ebooks/                # eBook PDFs (drop sample PDFs here)
├── database/
│   └── smartshelf_library.sql # Full schema + sample data
├── index.php                  # Home (hero, featured, features)
├── About-Us.php  features.php  Contact-Us.php
├── catalog.php   book.php      # Browse + book details
├── register.php  login.php  logout.php  profile.php
├── dashboard.php              # Member dashboard
├── notifications.php  read.php # Notifications + eBook reader
└── 404.php
```

---

## 🗄️ Database (11 tables)

`users`, `categories`, `books`, `features`, `borrowings`, `reservations`, `fines`, `reviews`, `notifications`, `contact_messages`, `activity_log` — each seeded with 10+ sample records where applicable.

---

## 🔒 Security checklist (assignment requirements)

- ✅ Passwords hashed with `password_hash()` / verified with `password_verify()`
- ✅ **All** queries use MySQLi **prepared statements**
- ✅ Input sanitised (`clean()`) and output escaped (`e()` → `htmlspecialchars`)
- ✅ **CSRF tokens** on every form
- ✅ Session hardening: `HttpOnly`, `SameSite`, `session_regenerate_id()`
- ✅ Role-based access guards (`require_login` / `require_staff` / `require_admin`)
- ✅ Both **JavaScript and PHP** form validation

---

## 💡 Tips for the demo / viva

- Toggle **dark mode** with the moon/sun icon in the navbar.
- Log in as a **member**, borrow a book, then watch the **admin dashboard** stats update.
- Show the **QR code** button on any book.
- Add a book in the admin panel and see it appear instantly in the catalog.
- To activate the eBook reader, drop any PDF into `uploads/ebooks/` and set its filename on a book.

---

*Built with ❤️ using vanilla PHP & MySQL. No frameworks, no shortcuts.*
