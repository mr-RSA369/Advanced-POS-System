# Advance-POS-System

A comprehensive and modern Point of Sale (POS) system for restaurants and retail businesses with complete inventory management, order processing, and financial reporting capabilities.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation & Setup](#installation--setup)

---

## 🎯 Overview

The Advance-POS-System is a full-featured restaurant and retail management solution designed to streamline operations from order taking to financial reporting. The system supports multiple order types (Dine-in, Takeaway, Delivery), real-time inventory tracking, thermal printing integration, and comprehensive financial analytics with profit/loss calculations.

The system follows a modern architecture with Laravel backend providing RESTful APIs and Vue.js frontend with Inertia.js for a seamless single-page application experience.

---

## ✨ Features

### 🍽️ Menu & Inventory Management
- **Add, Edit, Delete** menu items with categories
- Real-time inventory tracking
- Price management and updates
- Stock level monitoring
- Category-based organization

### 📋 Order Processing
- **Real-time order creation** with live item display
- Support for **Dine-in, Takeaway, and Delivery** orders
- **Kitchen bill printing** (optional)
- **Save orders** for later processing
- **Cancel orders** with reason tracking
- **Close orders** after payment completion
- Multiple payment method support
- Order status tracking

### 🧾 Billing System
- **Dynamic bill generation** with itemized breakdown
- Apply **Discounts, Service Charges, and Delivery Charges**
- **Print customer receipts** with thermal printer
- Tax calculation and breakdown
- Bill customization options

### 📊 Sales Management
- **Daily sales aggregation** (closed orders grouped by day)
- **PDF report generation** for sales data
- **Filter sales** by specific days, months, or custom date ranges
- **Search orders** by item name or customer details
- View orders by type (Delivery, Dine-in, Takeaway)
- Sales trend analysis

### 📦 Purchase Management
- **Add daily purchases** with supplier details
- **Purchase list** with date-wise organization
- **PDF report generation** for purchase records
- **Filter purchases** by specific dates and months
- **Summary and Detailed purchase reports**
- Supplier management

### 📈 Dashboard & Analytics
- **Monthly Sales** (PKR) overview
- **Monthly Purchases** (PKR) tracking
- **Real-time Profit/Loss calculation** (Sales - Purchases)
- **Order type statistics** for current month:
  - Delivery orders count
  - Takeaway orders count
  - Dine-in orders count
- Visual charts and graphs for business insights
- Performance metrics

### 📄 Reporting System
- **Generate PDF reports** for:
  - Sales reports (daily, monthly, custom)
  - Purchase reports (daily, monthly, custom)
  - Summary financial reports
  - Detailed transaction reports
- **Export capabilities** to multiple formats
- **Filtering options** for all report types
- Scheduled report generation

---

## 🛠 Technology Stack

### Backend
- **PHP 8.2+** - Server-side programming language
- **Laravel 10.x** - PHP framework with RESTful APIs
- **MySQL 8.0+** - Relational database
- **Mike42/ESC-POS** - Thermal printing library

### Frontend
- **Vue.js 3** - Progressive JavaScript framework
- **Inertia.js** - Server-side routing for single-page apps
- **Tailwind CSS** - Utility-first CSS framework
- **Axios** - HTTP client for API calls
- **jsPDF** - Client-side PDF generation
- **Chart.js** - Data visualization

### Development Tools
- **Composer** - PHP dependency manager
- **NPM** - JavaScript package manager
- **Git** - Version control

---

## 📦 Installation & Setup

### Prerequisites

Ensure you have the following installed on your system:

- PHP >= 8.2
- Composer
- Node.js >= 16.x
- MySQL >= 8.0
- NPM/Yarn
- Git

### Step-by-Step Installation

```bash
#Step 1: Clone the Repository
git clone https://github.com/yourusername/advance-pos-system.git
cd advance-pos-system
#Step 2: Install Backend Dependencies
composer install
composer install mike42/escpos-php
composer install barryvdh/laravel-dompdf
#step 3: Install Frontend Dependencies
npm install
# Step 4: Environment Configuration
cp .env.example .env
# Update .env with your database and other configurations
# Step 5: Generate Application Key
php artisan key:generate
# Step 6: Run Migrations and Seed Database
php artisan migrate --seed
# Step 7: Build Frontend Assets
npm run dev
# Step 8: Start the Development Server
php artisan serve
```
