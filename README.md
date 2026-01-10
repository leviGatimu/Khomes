# 🏠 Khomes.rw | Rwanda's Best Rentals

![Language](https://img.shields.io/badge/Language-PHP-orange)
![Database](https://img.shields.io/badge/Database-MySQL-blue)
![Theme](https://img.shields.io/badge/Theme-Orange-F39C47)

**Khomes.rw** is a high-performance real estate and rental platform designed for the Rwandan market. It connects guests looking for homes, apartments, or commercial spaces with trusted local hosts.

---

## ✨ Key Features

- **Hybrid Search**: Combines local database results with Google Places API for a "Super Search" experience.
- **Smart Booking System**: Real-time stay requests with Host approval/decline logic.
- **AI-Powered Insights**: Brief property descriptions and reviews fetched via Gemini/Google AI integration.
- **Admin Control Center**: Manage users, promote roles, and monitor flagged listings for safety.
- **Interactive DMs**: Real-time chat system with a floating widget for direct communication between guests and hosts.
- **Community Safety**: A robust reporting system to flag suspicious listings and ban bad actors.

## 🎨 Branding & Design

- **Primary Color**: Orange (`#F39C47`)
- **Secondary Color**: Dark Blue (`#1D1D35`)
- **Icons**: Professional iconography using Font Awesome.
- **Responsiveness**: Fully optimized for mobile, tablet, and desktop views.

---

## 🚀 Getting Started

### Prerequisites

To run this project locally, you will need:
- **XAMPP** or **WAMP** (PHP 8.x + MySQL)
- A **Google Maps API Key** (with Maps JS, Places, and Geolocation APIs enabled)

### Installation
Database Setup

Open PHPMyAdmin.

Create a new database named khomes.

Import the provided .sql file (found in the root directory).

Configure the Project

Open includes/db.php and update your database credentials.

Open includes/header.php and paste your Google Maps API Key in the script section.

Launch

Move the folder to your htdocs directory.

Open your browser and go to http://localhost/Khomes.

🛠️ Tech Stack
Backend: PHP (PDO for secure database interaction)

Frontend: HTML5, CSS3 (Custom Grid & Flexbox), JavaScript (Vanilla/AJAX)

Database: MySQL

APIs: Google Maps Platform, Google Places API (New)

🚩 Project Status
Currently in Active Development. Upcoming features include:

[ ] Momo (Mobile Money) payment integration

[ ] SMS notifications for hosts

[ ] Advanced User Verification badges

📄 License
This project is for educational purposes as part of the Khomes.rw development journey.

Developed with passion for the Rwandan rental market.


### Pro-Tips for your GitHub:
1. **Screenshots**: GitHub users love visuals. Create a folder named `screenshots` in your project and add a few images of your Homepage and Admin Dashboard. You can then add them to the README using `![Alt Text](screenshots/home.png)`.
2. **The SQL File**: Don't forget to export your current database from PHPMyAdmin and include it i
1. **Clone the repository**
   ```bash
   git clone [https://github.com/yourusername/Khomes.git](https://github.com/yourusername/Khomes.git)
