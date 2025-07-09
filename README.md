# Kahuna Smart Appliance API & Portal - Final Project

This project is a full-stack application for the Kahuna Smart Appliance company, developed as the final project for the MySuccess Website Developer course. It features a complete PHP REST API backend and a dynamic, single-page front-end interface.

The live project can be found at this repository: [https://github.com/Layalin/Kahuna-project.git](https://github.com/Layalin/Kahuna-project.git)

## Features

* **Dynamic UI:** A single-page front end that changes based on user roles.
* **Two User Roles:**
    * **Client:** Can create an account, log in, view their registered products (with warranty info), and register new products from a master list by selecting the product name and providing a purchase date.
    * **Admin:** Can do everything a client can, plus view all registered products by all users, filter the product list by username, add new products to the master list, and create new user or admin accounts.
* **Secure Backend:** A complete PHP REST API with token-based authentication.
* **Database Integration:** Uses a MariaDB/MySQL database to persist all data.

## Technology Stack

* **Frontend:** HTML5, CSS3, Vanilla JavaScript (ES6+)
* **Backend:** PHP
* **Database:** MariaDB (via XAMPP)
* **Web Server:** Apache (via XAMPP)

## Setup and Installation

1.  **Install Local Server:** Ensure you have a local server environment like **XAMPP** running with Apache and MySQL services started.
2.  **Get the Code:** Clone or download this repository (`https://github.com/Layalin/Kahuna-project.git`) into your server's web root directory (e.g., `htdocs` for XAMPP).
3.  **Run Database Setup:** In your web browser, navigate to `http://localhost/kahuna-api/setup-database.php`. This will automatically create the `kahuna_db` database and all necessary tables, and populate the initial product list.
4.  **Create the Admin User:** The setup does not create any users. Use a tool like Postman to create the initial admin account needed for testing.
    * Send a `POST` request to `http://localhost/kahuna-api/register-user.php`
    * Use the following JSON in the request body:
        ```json
        {
            "username": "superadmin",
            "password": "adminpassword",
            "role": "admin"
        }
        ```
5.  **Launch the Application:** Go to `http://localhost/kahuna-api/`. The application is now ready to use. You can log in with the `superadmin` credentials.

## Database Design (ERD)

The database consists of three main tables: Users, Products, and a linking table for Registered Products.

```mermaid
erDiagram
    USERS {
        int id PK "User ID"
        varchar username "Unique username"
        varchar password_hash "Hashed password"
        varchar role "'client' or 'admin'"
        varchar session_token "Session Token"
    }

    PRODUCTS {
        varchar serial_number PK "Unique Serial Number"
        varchar product_name "Product Name"
        int warranty_years "Warranty Period in Years"
    }

    REGISTERED_PRODUCTS {
        int registration_id PK "Registration ID"
        int user_id FK "Foreign key to USERS table"
        varchar product_serial_number FK "Foreign key to PRODUCTS table"
        date purchase_date "Date of purchase"
    }

    USERS ||--o{ REGISTERED_PRODUCTS : "registers"
    PRODUCTS ||--o{ REGISTERED_PRODUCTS : "is registered"