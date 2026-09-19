# Project Proposal
## SmartShelf — A Smarter Library Management System

**Module:** HDIT 21193 — User Experience and Interface Design
**Academic Year:** Year 2, Semester 1
**Project Type:** Final Project (Complete Web Application)
**Technologies:** HTML, CSS, JavaScript, PHP, MySQL (no frameworks)

---

## 1. Introduction

Walk into most libraries today and you will still find a surprising amount of pen and paper. Someone flips through a register to check if a book is in, due dates are scribbled on cards, and working out a member's fine usually means a bit of mental arithmetic at the counter. It works, but only just — and it leaves very little room for the things people actually expect from a modern service, like searching from their phone or getting a reminder before a book is overdue.

SmartShelf is my attempt to fix that. It is a complete library management system built from the ground up with plain HTML, CSS, JavaScript, PHP and MySQL — no Laravel, no React, no WordPress. I deliberately kept it framework-free so that every part of the system, from the database queries to the dark-mode toggle, is something I wrote and understand. The goal was not just to tick off the assignment requirements, but to build something I would genuinely be happy to hand to a real librarian.

This document explains the problem I set out to solve, what the system does, how it is put together, and why I made the design decisions I made.

---

## 2. The Problem

Before writing a single line of code, I spent time thinking about what actually goes wrong in traditional library systems. A few clear pain points kept coming up:

- **Finding a book is slow.** Without a proper search, members and staff waste time hunting through shelves or card catalogues.
- **Inventory is managed by hand.** Copy counts drift out of sync the moment someone forgets to update them, which leads to "the system says it's available but it isn't" moments.
- **Overdue books are a headache.** Fines are calculated manually, reminders rarely happen, and books quietly disappear for months.
- **Members can't do much themselves.** There is no way to reserve a book that is out, check their own history, or read anything online.
- **Reporting barely exists.** Managers have little visibility into what is popular, who borrows the most, or how stock is being used.
- **There are no digital services.** In an age where almost everything else is online, the library often isn't.

Each of these became a feature I wanted to address directly, rather than a box I simply ticked.

---

## 3. Objectives

What I wanted SmartShelf to achieve:

1. Give members an instant, friendly way to search, borrow, reserve and read books.
2. Take the manual work out of inventory — copy counts that update themselves in real time.
3. Calculate fines automatically and remind people before they fall due.
4. Provide librarians and administrators with clear dashboards and proper reports.
5. Add genuinely useful modern touches: QR codes, book recommendations, and a digital eBook library.
6. Keep the whole thing secure, responsive and pleasant to use on any device.

---

## 4. Who Uses It (User Roles)

SmartShelf is built around three kinds of people, each with their own view of the system:

**Members** are the readers. They browse the catalogue, borrow and return books, reserve titles that are currently out, read eBooks, track their reading history, and keep an eye on any fines.

**Librarians** handle the day-to-day running of the library — issuing and returning books at the counter, managing reservations, and keeping the catalogue up to date.

**Administrators** have the full picture. On top of everything a librarian can do, they manage user accounts, view the activity log, and see the high-level reports and analytics.

Each role only sees what it should, and the system quietly redirects anyone who tries to reach a page they are not allowed to.

---

## 5. What the System Does (Features)

### Core library features
- A complete borrowing and returning workflow, with copy counts that update the instant a book changes hands.
- A reservation queue: if a book is out, you can join the line, and the moment it comes back the next person is notified automatically.
- Automatic fine calculation that runs every time someone logs in, so the numbers are always current.
- User profiles, reading history, and book reviews and ratings.

### The modern touches I'm most proud of
- **Smart search and filters** — type a few letters and results appear instantly, with filters for category and availability on top.
- **AI-style recommendations** — the system looks at what you've borrowed and suggests books from the genres you actually read, falling back to trending titles for new members.
- **QR codes** — every book generates a scannable code, so issuing and returning at the counter can be as quick as a phone camera.
- **Digital library** — selected titles can be read and downloaded as eBooks right in the browser.
- **Notifications** — due-soon, overdue and reservation-ready alerts so nothing slips through the cracks.
- **Analytics dashboards** — borrowing trends over time, most popular books, top readers, and how much of the stock is currently out on loan.
- **Dark and light mode** — a small thing, but it makes the whole experience feel modern, and it remembers your choice.

---

## 6. How It's Built (Technology and Structure)

The stack is intentionally simple and entirely framework-free:

| Layer | Technology |
|-------|-----------|
| Structure | HTML5 |
| Styling | CSS3 (a custom design system with light/dark themes) |
| Interactivity | Vanilla JavaScript |
| Server logic | PHP |
| Database | MySQL |

The code is organised so that nothing is repeated unnecessarily. Shared pieces — the database connection, the header, the footer, and a library of helper functions — live in an `includes/` folder and are pulled into each page. The database connection itself sits in a single `config.php`, so if anything about the setup changes, there is exactly one place to update it. CSS, JavaScript and PHP are kept in separate files throughout, and the code is commented so that someone reading it for the first time can follow what is going on.

---

## 7. The Database

SmartShelf is backed by eleven related tables, comfortably more than the three the assignment asks for. The most important ones are:

- **users** — everyone's account details, role and status.
- **books** — the heart of the catalogue, including stock counts, cover images and eBook files.
- **categories** — how books are grouped.
- **features** — the content shown on the public Features page, fully editable by admins.
- **borrowings** — every issue and return.
- **reservations** — the waiting queues.
- **fines** — a transparent ledger of charges.
- **reviews, notifications, contact_messages** and an **activity_log** round things out.

Every table comes pre-loaded with realistic sample data, and — a detail I'm quite pleased with — the borrowing dates are generated relative to the day the database is imported. That means whenever someone sets the system up, the demo data looks current rather than stale, with a couple of active loans and one gently overdue book, exactly as a real library would look on any given afternoon.

---

## 8. Security

Because a library system holds real people's accounts, I treated security as a first-class concern rather than an afterthought:

- Passwords are never stored as plain text — they are hashed with PHP's `password_hash()` and checked with `password_verify()`.
- Every single database query uses prepared statements, which closes the door on SQL injection.
- All user input is sanitised on the way in and escaped on the way out, so cross-site scripting has nowhere to go.
- Every form carries a CSRF token, and the server rejects any submission without a valid one.
- Sessions are hardened with HttpOnly and SameSite cookies, and the session ID is regenerated on login to prevent fixation.
- Page-level guards make sure members can't wander into admin pages.

Both JavaScript (for instant feedback) and PHP (as the real gatekeeper) validate every form, so the experience is friendly without ever trusting the browser.

---

## 9. Design and User Experience

Since this is a UX and interface design module, how the system feels mattered as much as what it does. I built a small design system of my own — a consistent set of colours, spacing, buttons and cards — and used it everywhere so the whole application feels like one coherent product. The layout is fully responsive, adapting cleanly from a wide desktop dashboard down to a single-column mobile view, with a proper slide-in navigation menu on smaller screens.

Little details add up: animated counters on the homepage, cards that gently lift on hover, smooth scroll-reveal effects, a password-strength meter while you type, and that dark-mode toggle that remembers what you picked. None of it is decoration for its own sake — it's there to make the system feel responsive, trustworthy and genuinely nice to use.

---

## 10. Why This Project Stands Out

There are plenty of library management projects out there. What I think sets SmartShelf apart is that it is **complete and real** — there are no "coming soon" placeholders, every button does something, and the whole thing runs out of the box in XAMPP. It goes well beyond the brief with features like QR codes, recommendations and an eBook reader, and it has been tested end to end: logging in, borrowing, returning, reserving, paying fines and running reports all work against a live database, not just in theory.

More than anything, it's a system I built to be used, not just submitted.

---

## 11. Conclusion

SmartShelf takes the everyday frustrations of running a library — slow searches, manual inventory, overdue chaos, no digital presence — and answers each one with something practical. It meets every requirement of the assignment and then keeps going, layering on the modern, thoughtful features that make the difference between software that merely works and software people actually enjoy.

I set out to build the kind of library system I would want to use myself, and I'm proud of where it ended up.

---

*Prepared as the final project report for HDIT 21193 — User Experience and Interface Design.*
