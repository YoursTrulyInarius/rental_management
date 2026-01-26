# KINGSLAYER

> [!IMPORTANT]
> **PROTOTYPE DISCLAIMER**: This project is currently a **PROTOTYPE** and is **NOT COMPLETE**. It is intended for demonstration purposes and is under active development.

The **KINGSLAYER** is a web-based platform designed for **KINGSLAYER** in Gatas, Pagadian City. It streamlines the management of food stalls, karaoke rooms, fitness/gym facilities, and fashion boutiques within the building.

## 🚀 Key Features

### 👤 Role-Based Access
- **Admin**: Oversee all tenants, manage stall assignments, track overall revenue, and manage system users.
- **Tenant (Occupant)**: Manage their own business profile, track inventory, record sales transactions, and view rent payment history.

### 📦 Core Functionalities
- **Inventory Management**: Tenants can catalog items, set prices, and track stock levels.
- **Sales Tracking**: Record transactions with multiple payment methods (e.g., Cash) and operator tracking.
- **Rent Management**: Systematic tracking of monthly rent payments and status (Paid/Pending).
- **Authentication**: Secure login and registration system for both administrators and tenants.

## 🛠️ Technology Stack

- **Backend**: PHP
- **Database**: MySQL (MariaDB)
- **Frontend**: HTML5, CSS3 (Inter Font), JavaScript (jQuery)
- **Styling**: Bootstrap 5.3.0
- **Icons**: Bootstrap Icons

## ⚙️ Setup Instructions

### Prerequisites
- XAMPP / WAMP / MAMP (PHP 7.4+ and MySQL)

### Installation
1.  **Clone or Download**: Place the project folder in your `htdocs` directory.
2.  **Database Setup**:
    - Open `phpMyAdmin`.
    - Create a new database named `kingslayer_rental`.
    - Import the [`database.sql`](file:///c:/xampp/htdocs/rental_management/database.sql) file.
3.  **Configuration**:
    - Ensure your database connection settings in `config/` (if applicable) match your local environment.
4.  **Access**:
    - Open your browser and navigate to `http://localhost/rental_management`.

## 📂 Project Structure

- `/admin`: Administrative dashboard and functions.
- `/tenant`: Tenant-specific dashboard and features.
- `/api`: Backend logic and data handling.
- `/auth`: Login and registration scripts.
- `/assets`: Images, CSS, and client-side scripts.
- `/includes`: Reusable UI components (headers, footers).
- `index.php`: Main landing page and authentication portal.
- `database.sql`: Central database schema.

---
© 2025 KINGSLAYER | KINGSLAYER
