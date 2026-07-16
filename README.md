# Munchly

# 🍽️ Munchly

A web-based recipe sharing platform where users can discover, create, and manage recipes across different meal categories.

---

## 📋 Table of Contents

- [About](#about)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [Database Setup](#database-setup)
- [Usage](#usage)

---

## About

Munchly is a web-based recipe sharing project built as a full-stack PHP web application. It allows users to register, log in, browse recipes by category, submit their own recipes, rate and comment on others' recipes, and contact the site administrators.

---

## Features

- **User Authentication** — Register and log in with secure password handling
- **Recipe Management** — Create, edit, and delete your own recipes with images
- **Category Browsing** — Filter recipes by Breakfast, Lunch, Dinner, Snacks, and Desserts
- **Search** — Search recipes by title
- **Ratings** — Rate recipes on a scale of 1–5
- **Comments** — Leave comments on recipe detail pages
- **Admin Panel** — Admin users can manage all recipes and users
- **Contact Form** — Users can send messages to the site team
- **REST API** — JSON endpoints for recipe search and details

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.0 |
| Database | MySQL |
| Frontend | HTML, CSS, JavaScript |
| DB Access | PDO (PHP Data Objects) |
| Server | Apache (XAMPP ) |

---

## Project Structure

```
Munchly/
├── api/
│   ├── recipedetail.php      # API: get single recipe details
│   └── search_recipes.php    # API: search recipes by keyword
├── assets/
│   ├── css/style.css
│   ├── img/                  # Category images
│   └── js/main.js
├── database/
│   └── munchly.sql           # Schema (minimal)
├── uploads/                  # User-uploaded recipe images
├── addrecipe.php             # Add new recipe form
├── admin.php                 # Admin dashboard
├── comments.php              # Comments handler
├── contact.php               # Contact page
├── dashboard.php             # User dashboard
├── db.php                    # Database connection (PDO)
├── editrecipe.php            # Edit recipe form
├── login.php                 # Login page
├── logout.php                # Session logout
├── ratings.php               # Ratings handler
├── recipe.php                # Recipe listing & category view
├── recipedetail.php          # Single recipe detail page
├── register.php              # Registration page
├── session.php               # Session helper
└── user.php                  # User helper
```

---

## Getting Started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) installed
- PHP 8.0+
- MySQL 

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Yuta020/Munchly.git
   ```

2. **Move the project to your server root**
   - XAMPP: copy the `Munchly/` folder into `C:/xampp/htdocs/`

3. **Set up the database** (see below)

4. **Configure the database connection**

   Open `db.php` and update if needed:
   ```php
   $host = 'localhost';
   $db   = 'munchly';
   $user = 'root';
   $pass = '';
   ```

5. **Start Apache and MySQL** from the XAMPP/WAMP control panel

6. **Open in browser**
   ```
   http://localhost/Munchly/recipe.php
   ```

---

## Database Setup

1. Open **phpMyAdmin** at `http://localhost/phpmyadmin`
2. Create a new database named `munchly`
3. Import the SQL file:
   - Go to the **Import** tab
   - Select `Database/munchly (1).sql`
   - Click **Go**

This will create the following tables:

| Table | Description |
|-------|-------------|
| `users` | Registered users with roles (admin/user) |
| `categories` | Meal categories (Breakfast, Lunch, etc.) |
| `recipes` | All recipe entries |
| `ratings` | User ratings (1–5) per recipe |
| `messages` | Comments on recipes |
| `contact_messages` | Messages submitted via contact form |

---

## Usage

| Action | URL |
|--------|-----|
| Browse recipes | `/recipe.php` |
| View recipe detail | `/recipedetail.php?id={id}` |
| Register | `/register.php` |
| Login | `/login.php` |
| Add a recipe | `/addrecipe.php` (login required) |
| Admin panel | `/admin.php` (admin role required) |
| Contact | `/contact.php` |

### API Endpoints

```
GET /api/search_recipes.php?q={keyword}   → returns JSON list of matching recipes
GET /api/recipedetail.php?id={id}         → returns JSON details of a single recipe
```

---
