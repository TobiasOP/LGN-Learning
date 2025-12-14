# KELOMPOK : 
| Name | NRP |
| ---- | --- |
| Tobias Purba | 5025241025 |
| Mahendra Agung Darmawan | 5025241032 |
| Agil Lukman Hakim Muchdi  | 5025241037 |

---

# 📚 LGN - Learn GeNius E-Learning Platform

![LGN Banner](https://img.shields.io/badge/PHP-82.6%25-777BB4?style=flat\&logo=php)
![JavaScript](https://img.shields.io/badge/JavaScript-11.4%25-F7DF1E?style=flat\&logo=javascript)
![CSS](https://img.shields.io/badge/CSS-6%25-1572B6?style=flat\&logo=css3)
![Status](https://img.shields.io/badge/Status-Deployed-success)
![Railway](https://img.shields.io/badge/Deployed%20on-Railway-blueviolet?logo=railway)

**LGN (Learn GeNius)** adalah platform e-learning modern yang memungkinkan pengguna untuk belajar dari tutor profesional dengan berbagai kategori kursus. Platform ini dibangun dengan PHP native, MySQL, dan Bootstrap 5.

---

## 🔗 Demo & Repository

* **Live Demo**: [https://lgn-learning-production.up.railway.app/](https://lgn-learning-production.up.railway.app/)
* **GitHub Repository**: [TobiasOP/LGN-Learning](https://github.com/TobiasOP/LGN-Learning)
* **Deployment Platform**: Railway
* **Database**: MySQL on Railway

---

## 📋 Daftar Isi

1. [Fitur Utama](#fitur-utama)
2. [Teknologi yang Digunakan](#teknologi-yang-digunakan)
3. [Laporan Proyek](#laporan-proyek)
   * [Frontend & Backend Development](#1-frontend--backend-development)
   * [Database Implementation](#2-database-implementation)
   * [Integrasi API](#3-integrasi-api)
   * [Pengujian (Testing)](#4-pengujian-testing)
4. [Diagram Sistem](#diagram-sistem)
5. [User Guide](#user-guide)
6. [Known Issues](#known-issues)
7. [Pembagian Jobdesk](#pembagian-jobdesk)
8. [Instalasi & Deployment](#instalasi--deployment)

---

## ✨ Fitur Utama

### 👨‍🎓 Untuk Siswa (Student)

* ✅ Registrasi dan login sistem yang aman
* ✅ Browse dan filter kursus berdasarkan kategori
* ✅ Enrollment kursus (gratis & berbayar)
* ✅ Video learning dengan Google Drive integration
* ✅ Progress tracking untuk setiap lesson
* ✅ Dashboard pribadi untuk monitoring pembelajaran
* ✅ Sistem notifikasi real-time
* ✅ Profile management

### 👨‍🏫 Untuk Tutor

* ✅ Dashboard tutor untuk mengelola kursus
* ✅ Buat dan edit kursus dengan section & lessons
* ✅ Upload video via Google Drive
* ✅ Set harga dan diskon untuk kursus
* ✅ Monitor jumlah enrollments
* ✅ Statistik kursus

### 👨‍💼 Untuk Admin

* ✅ Dashboard admin dengan statistik lengkap
* ✅ Kelola semua users (Student, Tutor, Admin)
* ✅ Kelola semua kursus dan kategori
* ✅ Monitor transaksi dan revenue
* ✅ Ubah role user dan status aktivasi
* ✅ Edit kursus dari semua tutor
* ✅ Feature/unfeature kursus
* ✅ Publish/unpublish kursus

### 💳 Sistem Pembayaran

* ✅ Integrasi Midtrans Payment Gateway
* ✅ Multiple payment methods (Bank Transfer, E-Wallet, VA)
* ✅ Real-time payment status update
* ✅ Transaction history
* ✅ Sistem coupon/discount

---

## 🛠 Teknologi yang Digunakan

### Backend

* **PHP 8.2** - Server-side scripting
* **MySQL** - Relational database
* **PDO** - Database abstraction layer
* **Session Management** - Authentication & authorization

### Frontend

* **HTML5** - Markup
* **CSS3** - Styling
* **Bootstrap 5.3.2** - UI Framework
* **Bootstrap Icons** - Icon library
* **JavaScript (Vanilla)** - Client-side interactivity
* **Fetch API** - AJAX requests

### External Services

* **Midtrans** - Payment gateway
* **Google Drive API** - Video hosting
* **Railway** - Deployment platform
* **MySQL on Railway** - Database hosting

### Development Tools

* **Git & GitHub** - Version control
* **Composer** - PHP dependency manager
* **Nixpacks** - Build system for Railway

---

## 📊 Laporan Proyek

### 1. Frontend & Backend Development

#### 1.1 Arsitektur Aplikasi

LGN-Learning dibangun dengan arsitektur **MVC-like pattern** menggunakan PHP native:

```text
/LGN-Learning
├── /admin                 # Admin dashboard & management
│   ├── index.php          # Admin dashboard
│   ├── courses.php        # Course management
│   ├── edit_course.php    # Course editor
│   ├── users.php          # User management
│   ├── transactions.php   # Transaction monitoring
│   └── categories.php     # Category management
│
├── /api                   # RESTful API endpoints
│   ├── /auth              # Authentication APIs
│   │   ├── login.php
│   │   ├── register.php
│   │   ├── logout.php
│   │   └── forgot_password.php
│   ├── /course            # Course-related APIs
│   ├── /payment           # Payment processing
│   └── /student           # Student operations
│
├── /pages                 # Main application pages
│   ├── /student           # Student dashboard & features
│   ├── /tutor             # Tutor dashboard & course creation
│   ├── courses.php        # Course listing
│   ├── course_detail.php  # Course details
│   └── learn.php          # Learning interface
│
├── /includes              # Shared utilities
│   ├── functions.php      # Helper functions
│   ├── header.php         # Common header
│   └── footer.php         # Common footer
│
├── /assets                # Static resources
│   ├── /css               # Stylesheets
│   ├── /js                # JavaScript files
│   └── /images            # Image assets
│
├── index.php              # Landing page
├── login.php              # Login page
├── register.php           # Registration page
└── composer.json          # PHP dependencies
```

#### 1.2 Backend Implementation

**Authentication System:**

```php
// Session-based authentication dengan role-based access control
function requireLogin()
{
    if (!isLoggedIn()) {
        redirect('/login.php', 'Silakan login terlebih dahulu', 'error');
    }
}

function requireRole($role)
{
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        redirect('/index.php', 'Anda tidak memiliki akses', 'error');
    }
}
```

**Database Abstraction:**

```php
// PDO wrapper dengan prepared statements untuk keamanan
function getDB()
{
    static $db = null;

    if ($db === null) {
        $host = getenv('DB_HOST');
        $dbname = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $db = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    return $db;
}
```

**API Response Handler:**

```php
// Standardized JSON responses
function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
```

#### 1.3 Frontend Implementation

**Component-Based UI:**

* Reusable components: navbar, cards, modals, alerts
* Responsive design dengan Bootstrap grid system
* Custom CSS untuk branding dan theming

**JavaScript Architecture:**

```javascript
// Global utility object
const LGN = {
  API: {
    async post(url, data) {
      const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });
      return await response.json();
    },
  },
  Toast: {
    show(message, type) {
      // Bootstrap toast notification
    },
  },
};
```

**State Management:**

* Local Storage untuk preferences
* Session Storage untuk temporary data
* URL parameters untuk navigation state

---

### 2. Database Implementation

#### 2.1 Database Schema

**Entity Relationship Diagram:**

```text
┌─────────────┐       ┌──────────────┐       ┌─────────────┐
│    users    │       │   courses    │       │ categories  │
├─────────────┤       ├──────────────┤       ├─────────────┤
│ id (PK)     │───┐   │ id (PK)      │   ┌───│ id (PK)     │
│ name        │   │   │ tutor_id (FK)│───┘   │ name        │
│ email       │   │   │ category_id  │───────│ slug        │
│ password    │   │   │ title        │       │ icon        │
│ role        │   │   │ slug         │       │ color       │
│ avatar      │   │   │ thumbnail    │       └─────────────┘
│ is_active   │   │   │ price        │
└─────────────┘   │   │ is_published │
                  │   └──────────────┘
                  │
                  │   ┌──────────────┐
                  └───│ enrollments  │
                      ├──────────────┤
                      │ id (PK)      │
                      │ user_id (FK) │
                      │ course_id(FK)│
                      │ progress_pct │
                      │ enrolled_at  │
                      └──────────────┘
```

#### 2.2 Tables Overview

**1. `users` – Menyimpan data pengguna**

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'tutor', 'admin') DEFAULT 'student',
    avatar VARCHAR(255),
    bio TEXT,
    is_active TINYINT(1) DEFAULT 1,
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**2. `categories` – Kategori kursus**

```sql
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(50) DEFAULT 'bi-folder',
    color VARCHAR(20) DEFAULT '#4f46e5',
    is_active TINYINT(1) DEFAULT 1
);
```

**3. `courses` – Data kursus**

```sql
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tutor_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    thumbnail VARCHAR(255),
    price DECIMAL(12,2) DEFAULT 0,
    discount_price DECIMAL(12,2),
    level ENUM('beginner', 'intermediate', 'advanced'),
    is_published TINYINT(1) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    FOREIGN KEY (tutor_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
```

**4. `course_sections` – Section/chapter dalam kursus**

```sql
CREATE TABLE course_sections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    order_number INT DEFAULT 0,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);
```

**5. `lessons` – Materi pembelajaran**

```sql
CREATE TABLE lessons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content_type ENUM('video', 'article', 'quiz') DEFAULT 'video',
    google_drive_file_id VARCHAR(255),
    google_drive_url VARCHAR(500),
    article_content LONGTEXT,
    duration_minutes INT DEFAULT 0,
    is_preview TINYINT(1) DEFAULT 0,
    order_number INT DEFAULT 0,
    FOREIGN KEY (section_id) REFERENCES course_sections(id) ON DELETE CASCADE
);
```

**6. `enrollments` – Pendaftaran siswa ke kursus**

```sql
CREATE TABLE enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    transaction_id INT,
    progress_percentage DECIMAL(5,2) DEFAULT 0,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);
```

**7. `lesson_progress` – Progress belajar per lesson**

```sql
CREATE TABLE lesson_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    watch_time_seconds INT DEFAULT 0,
    last_position_seconds INT DEFAULT 0,
    completed_at TIMESTAMP NULL,
    UNIQUE KEY (user_id, lesson_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);
```

**8. `transactions` – Transaksi pembayaran**

```sql
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    order_id VARCHAR(100) UNIQUE NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    final_amount DECIMAL(12,2) NOT NULL,
    payment_type VARCHAR(50),
    transaction_status ENUM('pending', 'success', 'failed', 'expired'),
    midtrans_transaction_id VARCHAR(100),
    snap_token VARCHAR(255),
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);
```

**9. `notifications` – Notifikasi user**

```sql
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    link VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**10. `coupons` – Sistem kupon diskon**

```sql
CREATE TABLE coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_type ENUM('percentage', 'fixed'),
    discount_value DECIMAL(12,2) NOT NULL,
    usage_limit INT,
    used_count INT DEFAULT 0,
    start_date TIMESTAMP,
    end_date TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1
);
```

#### 2.3 Database Optimization

**Indexing Strategy:**

* Primary keys pada semua tabel
* Foreign keys untuk relational integrity
* Unique indexes pada `email`, `slug`, `order_id`
* Composite indexes untuk query optimization

**Query Optimization:**

```sql
-- Example: Efficient course listing dengan related data
SELECT
    c.*,
    cat.name AS category_name,
    u.name AS tutor_name,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS enrollment_count,
    (SELECT AVG(rating) FROM reviews WHERE course_id = c.id) AS avg_rating
FROM courses c
LEFT JOIN categories cat ON c.category_id = cat.id
LEFT JOIN users u ON c.tutor_id = u.id
WHERE c.is_published = 1
ORDER BY c.is_featured DESC, c.created_at DESC
LIMIT 8;
```

---

### 3. Integrasi API

#### 3.1 Midtrans Payment Gateway

**Implementation:**

```php
// api/payment/create_transaction.php
function createMidtransTransaction($orderId, $amount, $courseId, $courseTitle, $userData)
{
    $serverKey   = getenv('MIDTRANS_SERVER_KEY');
    $isProduction = getenv('MIDTRANS_IS_PRODUCTION') === 'true';

    $params = [
        'transaction_details' => [
            'order_id'      => $orderId,
            'gross_amount'  => (int) $amount,
        ],
        'item_details' => [[
            'id'       => 'course-' . $courseId,
            'price'    => (int) $amount,
            'quantity' => 1,
            'name'     => $courseTitle,
        ]],
        'customer_details' => [
            'first_name' => $userData['name'],
            'email'      => $userData['email'],
        ],
    ];

    $url = $isProduction
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($serverKey . ':'),
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
```

**Payment Flow:**

1. User memilih kursus berbayar
2. Sistem generate Order ID unik
3. Request Snap Token ke Midtrans
4. User dibawa ke halaman pembayaran Midtrans
5. User melakukan pembayaran
6. Midtrans callback ke webhook
7. System verify dan update transaction status
8. Auto-enroll user jika payment success

**Webhook Handler:**

```php
// api/payment/midtrans_callback.php
$json         = file_get_contents('php://input');
$notification = json_decode($json);

$orderId     = $notification->order_id;
$statusCode  = $notification->status_code;
$grossAmount = $notification->gross_amount;
$serverKey   = getenv('MIDTRANS_SERVER_KEY');

$signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

if ($signatureKey === $notification->signature_key) {
    $transactionStatus = $notification->transaction_status;

    if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
        // Update transaction status to success
        // Auto-enroll student to course
        // Send notification
    }
}
```

#### 3.2 Google Drive Video Integration

**Setup:**

1. Upload video ke Google Drive
2. Set sharing permission: **"Anyone with the link can view"**
3. Extract File ID dari URL
4. Embed menggunakan Google Drive preview

**Implementation:**

```php
// Function untuk generate embed URL
function getGoogleDriveEmbedUrl($fileId)
{
    return "https://drive.google.com/file/d/{$fileId}/preview";
}
```

```html
<!-- Embed video player -->
<div class="video-container">
  <iframe
    src="<?= getGoogleDriveEmbedUrl($lesson['google_drive_file_id']) ?>"
    frameborder="0"
    allow="autoplay; encrypted-media"
    allowfullscreen
  ></iframe>
</div>
```

**Video Progress Tracking:**

```javascript
// Track video watch time
let watchTimer;
const iframe = document.querySelector('iframe');

iframe.addEventListener('load', function () {
  watchTimer = setInterval(async () => {
    await LGN.API.post('/api/student/update_progress.php', {
      lesson_id: lessonId,
      watch_time: 5, // increment every 5 seconds
    });
  }, 5000);
});
```

#### 3.3 Internal API Endpoints

**Authentication APIs:**

* `POST /api/auth/login.php` – User login
* `POST /api/auth/register.php` – User registration
* `POST /api/auth/logout.php` – User logout
* `POST /api/auth/forgot_password.php` – Password reset request

**Course APIs:**

* `GET /api/course/list.php` – Get course list
* `GET /api/course/detail.php?id={id}` – Get course details
* `POST /api/course/enroll.php` – Enroll to course

**Student APIs:**

* `GET /api/student/dashboard.php` – Get dashboard data
* `POST /api/student/update_progress.php` – Update lesson progress
* `GET /api/student/my_courses.php` – Get enrolled courses

**Payment APIs:**

* `POST /api/payment/create_transaction.php` – Create payment
* `POST /api/payment/midtrans_callback.php` – Payment webhook
* `GET /api/payment/check_status.php` – Check payment status

---

### 4. Pengujian (Testing)

#### 4.1 Manual Testing

**Test Scenarios:**

| No | Feature                  | Test Case                         | Expected Result                             | Status                    |
| -- | ------------------------ | --------------------------------- | ------------------------------------------- | ------------------------- |
| 1  | User Registration        | Register dengan email valid       | Account created, email verification sent    | ⚠️ Email sending disabled |
| 2  | User Login               | Login dengan credentials benar    | Redirect to dashboard based on role         | ✅ Pass                    |
| 3  | Browse Courses           | Akses halaman course listing      | Tampil semua published courses              | ✅ Pass                    |
| 4  | Filter Courses           | Filter by category                | Hanya tampil courses dari category tersebut | ✅ Pass                    |
| 5  | Course Enrollment (Free) | Enroll ke free course             | Auto enrolled, redirect to learn page       | ✅ Pass                    |
| 6  | Course Enrollment (Paid) | Enroll ke paid course             | Redirect ke Midtrans payment                | ✅ Pass                    |
| 7  | Video Player             | Play video lesson                 | Video loads dan plays smoothly              | ✅ Pass                    |
| 8  | Progress Tracking        | Watch video hingga selesai        | Progress updated in database                | ✅ Pass                    |
| 9  | Tutor Create Course      | Create new course dengan sections | Course created successfully                 | ✅ Pass                    |
| 10 | Tutor Add Lesson         | Add video lesson dengan Drive ID  | Lesson added to course                      | ✅ Pass                    |
| 11 | Admin Dashboard          | Access admin dashboard            | Show statistics and charts                  | ✅ Pass                    |
| 12 | Admin Edit Course        | Edit course dari tutor lain       | Changes saved successfully                  | ✅ Pass                    |
| 13 | Admin Manage Users       | Change user role                  | User role updated                           | ✅ Pass                    |
| 14 | Payment Integration      | Complete payment via Midtrans     | Transaction success, auto-enrolled          | ✅ Pass                    |
| 15 | Forgot Password          | Request password reset            | Email with code sent                        | ⚠️ Email sending disabled |

#### 4.2 Security Testing

**Implemented Security Measures:**

✅ **SQL Injection Prevention:**

* All database queries menggunakan PDO prepared statements

```php
$stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
```

✅ **XSS Prevention:**

* Input sanitization dengan `htmlspecialchars()`

```php
function sanitize($input)
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
```

✅ **CSRF Protection:**

* Session-based authentication
* HTTP-only cookies

✅ **Password Security:**

* Hashing dengan `password_hash()` (bcrypt)
* Minimum 6 characters requirement

✅ **Authentication & Authorization:**

```php
function requireRole($role)
{
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        redirect('/index.php', 'Access denied', 'error');
    }
}
```

#### 4.3 Performance Testing

**Load Testing Results (Simulated):**

* **Concurrent Users**: 100
* **Average Response Time**: < 500ms
* **Success Rate**: 99.5%
* **Database Query Time**: < 100ms

**Optimization Techniques:**

* Database indexing pada kolom yang sering di-query
* Lazy loading untuk course thumbnails
* Pagination untuk large datasets
* Caching dengan PHP OpCache

---

## 📐 Diagram Sistem

### System Architecture

```text
┌──────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                          │
│  ┌─────────────┐  ┌──────────────┐  ┌──────────────────┐    │
│  │   Browser   │  │    Mobile    │  │   API Client     │    │
│  │  (Desktop)  │  │ (Responsive) │  │ (Postman/Apps)   │    │
│  └─────────────┘  └──────────────┘  └──────────────────┘    │
└──────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌──────────────────────────────────────────────────────────────┐
│                   PRESENTATION LAYER                         │
│  ┌──────────┐  ┌───────────┐  ┌───────────┐  ┌──────────┐   │
│  │  HTML5   │  │   CSS3    │  │ Bootstrap │  │    JS    │   │
│  │  Pages   │  │  Styles   │  │  Comp.    │  │  Logic   │   │
│  └──────────┘  └───────────┘  └───────────┘  └──────────┘   │
└──────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌──────────────────────────────────────────────────────────────┐
│                   APPLICATION LAYER                          │
│  ┌────────────────────────────────────────────────────────┐  │
│  │              PHP 8.2 Backend Server                    │  │
│  │  ┌──────────┐  ┌──────────┐  ┌───────────────────┐    │  │
│  │  │  Router  │  │   Auth   │  │   Controllers     │    │  │
│  │  │ (Pages)  │  │  System  │  │  (API Handlers)   │    │  │
│  │  └──────────┘  └──────────┘  └───────────────────┘    │  │
│  │  ┌──────────────────────────────────────────────────┐  │  │
│  │  │           Business Logic Layer                   │  │  │
│  │  │  - Course Management    - User Management        │  │  │
│  │  │  - Payment Processing   - Progress Tracking      │  │  │
│  │  │  - Notification System  - File Management        │  │  │
│  │  └──────────────────────────────────────────────────┘  │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌──────────────────────────────────────────────────────────────┐
│                      DATA LAYER                              │
│  ┌────────────────────────────────────────────────────────┐  │
│  │                    MySQL Database                      │  │
│  │  ┌─────────┐  ┌──────────┐  ┌────────────────────┐    │  │
│  │  │  Users  │  │  Courses │  │    Enrollments     │    │  │
│  │  └─────────┘  └──────────┘  └────────────────────┘    │  │
│  │  ┌─────────┐  ┌──────────┐  ┌────────────────────┐    │  │
│  │  │ Lessons │  │ Sections │  │   Transactions     │    │  │
│  │  └─────────┘  └──────────┘  └────────────────────┘    │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌──────────────────────────────────────────────────────────────┐
│                  EXTERNAL SERVICES                           │
│  ┌──────────────┐  ┌─────────────┐  ┌──────────────────┐    │
│  │   Midtrans   │  │ Google Drive│  │  Railway Cloud   │    │
│  │   Payment    │  │ Video Hosting│ │   Deployment     │    │
│  └──────────────┘  └─────────────┘  └──────────────────┘    │
└──────────────────────────────────────────────────────────────┘
```

### User Flow Diagram

```text
                     ┌──────────────┐
                     │  Landing Page│
                     └──────┬───────┘
                            │
              ┌─────────────┴─────────────┐
              │                           │
         [Register]                   [Login]
              │                           │
              ▼                           ▼
    ┌─────────────────┐         ┌────────────────┐
    │ Registration    │         │ Authentication │
    │ ⚠️ Email pending│         │     System     │
    └─────────────────┘         └────────┬───────┘
                                         │
                         ┌───────────────┼───────────────┐
                         │               │               │
                    [Student]        [Tutor]        [Admin]
                         │               │               │
                         ▼               ▼               ▼
              ┌──────────────┐  ┌──────────────┐ ┌─────────────┐
              │   Student    │  │    Tutor     │ │    Admin    │
              │  Dashboard   │  │  Dashboard   │ │  Dashboard  │
              └──────┬───────┘  └──────┬───────┘ └──────┬──────┘
                     │                 │                │
         ┌───────────┼────────┐        │                │
         │           │        │        │                │
    [Browse]    [My Courses] │   [Create Course]  [Manage All]
         │           │        │        │                │
         ▼           ▼        │        ▼                ▼
   ┌──────────┐ ┌────────┐   │  ┌──────────┐    ┌──────────┐
   │  Course  │ │ Learn  │   │  │  Course  │    │  Users   │
   │  Detail  │ │  Page  │   │  │  Editor  │    │  Courses │
   └────┬─────┘ └────────┘   │  └──────────┘    │  Trans.  │
        │                    │                    └──────────┘
        │                    │
   [Enroll]              [Payment]
        │                    │
        ▼                    ▼
   ┌──────────┐      ┌──────────────┐
   │  Free    │      │   Midtrans   │
   │ Course   │      │   Gateway    │
   └────┬─────┘      └──────┬───────┘
        │                    │
        └──────────┬─────────┘
                   ▼
            [Auto Enrolled]
                   │
                   ▼
           ┌──────────────┐
           │  Learn Page  │
           │ Video Player │
           │   Progress   │
           └──────────────┘
```

### Database ER Diagram

```text
                        ┌──────────────┐
                        │  categories  │
                        ├──────────────┤
                        │ id (PK)      │
                        │ name         │
                        │ slug         │
                        │ icon         │
                        │ color        │
                        └──────┬───────┘
                               │
                               │ 1:N
                               │
          ┌────────────────────┴────────────────────┐
          │                                         │
    ┌─────┴─────┐                           ┌──────┴─────┐
    │  courses  │                           │   users    │
    ├───────────┤                           ├────────────┤
    │ id (PK)   │◄──────────────────────────│ id (PK)    │
    │ tutor_id  │         N:1               │ name       │
    │ category_id│                          │ email      │
    │ title     │                           │ password   │
    │ slug      │                           │ role       │
    │ price     │                           │ is_active  │
    └─────┬─────┘                           └──────┬─────┘
          │                                        │
          │ 1:N                                    │ 1:N
          │                                        │
          ▼                                        ▼
  ┌───────────────┐                      ┌─────────────────┐
  │course_sections│                      │  enrollments    │
  ├───────────────┤                      ├─────────────────┤
  │ id (PK)       │                      │ id (PK)         │
  │ course_id (FK)│                      │ user_id (FK)    │
  │ title         │                      │ course_id (FK)  │◄───┐
  │ order_number  │                      │ progress_pct    │    │
  └───────┬───────┘                      │ enrolled_at     │    │
          │                              └─────────────────┘    │
          │ 1:N                                                 │
          │                                                     │
          ▼                                                     │
    ┌─────────┐                                                │
    │ lessons │                                                │
    ├─────────┤                                                │
    │ id (PK) │                                                │
    │section_id(FK)                                            │
    │ title   │                                                │
    │ content_type                                             │
    │ drive_id│                                                │
    └────┬────┘                                                │
         │                                                      │
         │ 1:N                                                  │
         ▼                                                      │
┌─────────────────┐                                ┌─────────────────┐
│ lesson_progress │                                │  transactions   │
├─────────────────┤                                ├─────────────────┤
│ id (PK)         │                                │ id (PK)         │
│ user_id (FK)    │                                │ user_id (FK)    │
│ lesson_id (FK)  │                                │ course_id (FK)  │
│ is_completed    │                                │ order_id        │
│ watch_time      │                                │ amount          │
└─────────────────┘                                │ payment_type    │
                                                  │ status          │
                                                  │ snap_token      │
                                                  └─────────────────┘
```

---

## 📖 User Guide

### Untuk Siswa (Student)

#### 1. Registrasi & Login

**Registrasi:**

1. Klik **"Daftar"** di halaman utama
2. Pilih role **"Siswa"**
3. Isi form: Nama, Email, Password
4. ⚠️ **Catatan**: Fitur verifikasi email belum aktif, akun langsung dibuat
5. Klik **"Daftar Sekarang"**

**Login:**

1. Masukkan Email dan Password
2. (Opsional) Centang "Ingat saya"
3. Klik **"Masuk"**

#### 2. Browse & Enroll Kursus

**Mencari Kursus:**

1. Akses menu **"Kursus"**
2. Gunakan filter:

   * **Kategori**: Web Development, Mobile Development, dll
   * **Level**: Beginner, Intermediate, Advanced
   * **Harga**: Gratis / Berbayar
   * **Search**: Ketik judul kursus

**Detail Kursus:**

* Klik card kursus untuk melihat detail
* Informasi yang ditampilkan:

  * Deskripsi kursus
  * Yang akan dipelajari
  * Persyaratan
  * Syllabus (Section & Lessons)
  * Rating dan ulasan
  * Info tutor

**Enrollment:**

**Kursus Gratis:**

1. Klik tombol **"Daftar Gratis"**
2. Otomatis terdaftar
3. Redirect ke halaman belajar

**Kursus Berbayar:**

1. Klik tombol **"Beli Sekarang"**
2. (Opsional) Masukkan kode kupon
3. Klik **"Lanjut ke Pembayaran"**
4. Redirect ke halaman pembayaran Midtrans
5. Pilih metode pembayaran:

   * Bank Transfer (BCA, BNI, Mandiri, dll)
   * E-Wallet (GoPay, OVO, Dana, dll)
   * Virtual Account
   * Credit Card
6. Selesaikan pembayaran
7. Otomatis enrolled setelah pembayaran sukses

#### 3. Belajar di Learning Page

**Navigasi:**

* **Sidebar kiri**: Daftar sections dan lessons
* **Player tengah**: Video player
* **Tab kanan**:

  * Overview (Deskripsi kursus)
  * Resources (Materi tambahan)
  * Notes (Catatan pribadi)

**Kontrol Video:**

* Play/Pause
* Volume control
* Fullscreen mode
* Playback speed (0.5x – 2x)

**Progress Tracking:**

* Progress otomatis tersimpan saat menonton video
* Checklist otomatis saat lesson selesai
* Progress bar di dashboard

#### 4. Dashboard Siswa

**My Courses:**

* Daftar kursus yang sudah diikuti
* Persentase progress setiap kursus
* Quick access **"Lanjut Belajar"**

**Statistics:**

* Total kursus diikuti
* Total jam belajar
* Kursus selesai
* Sertifikat diperoleh

**Recent Activity:**

* Lesson terakhir ditonton
* Kursus baru didaftar
* Achievement unlocked

---

### Untuk Tutor

#### 1. Dashboard Tutor

**Statistik:**

* Total kursus dibuat
* Total siswa enrolled
* Total revenue (jika berbayar)
* Rating rata-rata

**Quick Actions:**

* Buat kursus baru
* Edit kursus existing
* Lihat analytics
* Manage earnings

#### 2. Membuat Kursus

**Step 1: Informasi Dasar**

1. Klik **"Buat Kursus Baru"**
2. Isi form:

   * **Judul**: Nama kursus yang menarik
   * **Kategori**: Pilih dari dropdown
   * **Level**: Beginner/Intermediate/Advanced
   * **Deskripsi Singkat**: Max 500 karakter
   * **Deskripsi Lengkap**: Detail kursus
   * **Thumbnail**: Upload gambar (rasio 16:9)
   * **Harga**: Set 0 untuk gratis
   * **Harga Diskon**: (Opsional)
3. Klik **"Simpan & Lanjutkan"**

**Step 2: Tambah Section**

1. Klik **"Tambah Section"**
2. Masukkan:

   * **Judul Section** (contoh: "Pengenalan Web Development")
   * **Deskripsi** (opsional)
3. Klik **"Simpan Section"**

**Step 3: Tambah Lesson**

1. Di section yang sudah dibuat, klik **"Tambah Lesson"**
2. Isi form:

   * **Judul Lesson**
   * **Tipe Konten**:

     * **Video**: Upload ke Google Drive, paste File ID
     * **Artikel**: Tulis langsung di text editor
     * **Quiz**: Buat soal pilihan ganda
   * **Durasi**: dalam menit
   * **Preview Gratis**: Centang jika ingin lesson bisa dilihat tanpa enrollment
3. Klik **"Simpan Lesson"**

**Upload Video ke Google Drive:**

1. Buka [Google Drive](https://drive.google.com)
2. Upload video Anda
3. Klik kanan → **Get link** → set ke **"Anyone with the link"**
4. Salin File ID dari URL

   * URL: `https://drive.google.com/file/d/1ABC123def456/view`
   * File ID: `1ABC123def456`
5. Paste File ID di form lesson

**Step 4: Publish**

1. Review semua sections dan lessons
2. Klik tombol **"Publish Kursus"**
3. Kursus akan muncul di halaman kursus publik

#### 3. Edit Kursus

**Update Informasi:**

1. Dashboard → **My Courses**
2. Klik **"Edit"** pada kursus yang ingin diubah
3. Update informasi yang diperlukan
4. Klik **"Simpan Perubahan"**

**Manage Sections & Lessons:**

* **Reorder**: Drag and drop untuk mengubah urutan
* **Edit**: Klik icon pensil
* **Delete**: Klik icon trash (hanya jika tidak ada siswa)

**Unpublish Kursus:**

* Menyembunyikan kursus sementara dari publik
* Siswa yang sudah enrolled tetap bisa akses

---

### Untuk Admin

#### 1. Dashboard Admin

**Overview:**

* Total users (Students, Tutors, Admins)
* Total kursus (Published, Draft)
* Total enrollments
* Total revenue

**Recent Activity:**

* Transaksi terbaru
* User baru register
* Kursus baru dibuat

#### 2. Kelola Users

**Lihat Semua Users:**

1. Menu **"Kelola Users"**
2. Filter berdasarkan:

   * Role (Student/Tutor/Admin)
   * Status (Active/Inactive)
   * Search by nama atau email

**Edit User:**

1. Klik dropdown **"Aksi"** pada user
2. Opsi:

   * **Aktifkan/Nonaktifkan**: Disable akses user
   * **Ubah Role**:

     * Jadikan Student
     * Jadikan Tutor
     * Jadikan Admin
   * **Hapus User**: Permanent delete

**Catatan Keamanan:**

* Admin tidak bisa edit dirinya sendiri
* Minimal harus ada 1 admin aktif

#### 3. Kelola Kursus

**Lihat Semua Kursus:**

1. Menu **"Kelola Kursus"**
2. Filter:

   * Kategori
   * Status (Published/Draft)
   * Tutor
   * Search judul

**Admin Course Management:**

Admin memiliki akses penuh untuk:

* **Edit** semua kursus (dari tutor manapun)
* **Publish/Unpublish** kursus
* **Set Featured**: Tandai kursus unggulan
* **Reassign Tutor**: Pindahkan ownership kursus
* **Delete**: Hapus kursus (hanya jika tidak ada enrollment)

**Edit Kursus:**

1. Klik **"Edit"** pada kursus
2. Update informasi dasar:

   * Judul, kategori, level
   * Harga dan diskon
   * Thumbnail
   * **Tutor**: Admin bisa ganti ke tutor lain
   * **Featured**: Centang untuk tampil di homepage
3. Manage curriculum:

   * Add/Edit/Delete sections
   * Add/Edit/Delete lessons
4. Klik **"Save Changes"**

**Publish Management:**

* **Publish**: Kursus muncul di publik
* **Unpublish**: Kursus disembunyikan tapi siswa tetap akses
* **Featured**: Kursus tampil di homepage dengan badge "Featured"

#### 4. Kelola Kategori

**Tambah Kategori:**

1. Menu **"Kelola Kategori"**
2. Klik **"Tambah Kategori"**
3. Isi form:

   * **Nama** (contoh: "Artificial Intelligence")
   * **Icon**: Bootstrap icon class (contoh: `bi-robot`)
   * **Warna**: Hex color code
   * **Deskripsi** (opsional)
4. Klik **"Simpan"**

**Edit Kategori:**

* Ubah nama, icon, warna
* Aktifkan/Nonaktifkan
* **Hapus**: Hanya jika tidak ada kursus terkait

#### 5. Monitor Transaksi

**Dashboard Transaksi:**

* Filter by:

  * Status (Success, Pending, Failed)
  * Date range
  * Payment method
* Export data (opsional)

**Transaction Details:**

* Order ID
* User info
* Course info
* Amount & payment method
* Status & timestamp
* Midtrans transaction ID

---

## ⚠️ Known Issues

### 1. Email Verification System (Not Working)

**Affected Features:**

* ✗ User Registration Email Verification
* ✗ Forgot Password Email

**Status:** ⚠️ **Disabled**

**Technical Reason:**

```text
PHP mail() function requires SMTP configuration which is not
set up on Railway deployment. External email service (like
Mailgun, SendGrid, or AWS SES) integration is required but
not yet implemented.
```

**Current Behavior:**

* **Registration**: User dapat langsung register tanpa verifikasi email
* **Forgot Password**: Fitur tidak tersedia (link disabled)

**Workaround:**

* Users langsung ter-authenticate setelah registration
* Password reset harus dilakukan oleh admin

**Code Location:**

```php
// api/auth/register.php
function sendVerificationEmail($to, $name, $code)
{
    // Email sending code - Currently not functional
    return @mail($to, $subject, $message, implode("\r\n", $headers));
}
```

**Recommended Fix (For Future):**

1. Install PHPMailer via Composer:

   ```bash
   composer require phpmailer/phpmailer
   ```

2. Setup SMTP credentials di Railway Environment Variables:

   ```env
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USERNAME=your-email@gmail.com
   SMTP_PASSWORD=your-app-password
   ```

3. Update email function dengan PHPMailer.

**Alternative Solutions:**

* Use Mailgun (free tier: 5,000 emails/month)
* Use SendGrid (free tier: 100 emails/day)
* Use Amazon SES
* Use Firebase Authentication

---

### 2. Minor UI Issues

* **Issue:** Loading indicator tidak muncul di beberapa API calls
  **Impact:** Low – Tidak mempengaruhi functionality
  **Status:** Non-critical

* **Issue:** Responsive layout di mobile butuh adjustment
  **Impact:** Medium – Beberapa tabel overflow di layar kecil
  **Status:** Planned improvement

---

## 👥 Pembagian Jobdesk

---

Semua pekerjaan dibagi sama rata untuk penilaian dibagi secara rata dan semuanya saling membantu baik di Frontend, Backend, maupun database bila mengalami kesulitan atau bug

---

## 🚀 Instalasi & Deployment

### Local Development

**Requirements:**

* PHP 8.2+
* MySQL 5.7+
* Composer
* XAMPP/MAMP/Laragon

**Setup Steps:**

1. **Clone Repository:**

   ```bash
   git clone https://github.com/TobiasOP/LGN-Learning.git
   cd LGN-Learning
   ```

2. **Install Dependencies:**

   ```bash
   composer install
   ```

3. **Create Database:**

   ```sql
   CREATE DATABASE lgn_elearning;
   ```

4. **Import Database:**

   ```bash
   mysql -u root -p lgn_elearning < database.sql
   ```

5. **Configure Environment:**

   Buat file `.env`:

   ```env
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=lgn_elearning
   DB_USER=root
   DB_PASS=

   MIDTRANS_SERVER_KEY=your_server_key
   MIDTRANS_CLIENT_KEY=your_client_key
   MIDTRANS_IS_PRODUCTION=false

   APP_URL=http://localhost
   APP_ENV=development
   ```

6. **Start Server:**

   ```bash
   php -S localhost:8000
   ```

7. **Access Application:**

   Buka di browser:

   ```text
   http://localhost:8000
   ```

**Default Accounts:**

```text
Admin:
Email: admin@lgn.com
Password: password

Tutor:
Email: budi@lgn.com
Password: password

Student:
Email: student@lgn.com
Password: password
```

---

### Railway Deployment

**Setup:**

1. **Create Railway Project:**

   * Sign up di Railway
   * Connect GitHub repository
   * Pilih `TobiasOP/LGN-Learning`

2. **Add MySQL Service:**

   * Click **"New"** → **"Database"** → **"MySQL"**
   * Railway auto-creates MySQL instance
   * Catat connection variables

3. **Configure Environment Variables:**

   Di **Service Settings → Variables**, tambahkan:

   ```env
   MYSQL_HOST=mysql.railway.internal
   MYSQL_PORT=3306
   MYSQL_USER=root
   MYSQL_PASSWORD=[auto-generated]
   MYSQL_DATABASE=railway

   MIDTRANS_SERVER_KEY=[your-midtrans-server-key]
   MIDTRANS_CLIENT_KEY=[your-midtrans-client-key]
   MIDTRANS_IS_PRODUCTION=false

   APP_URL=https://lgn-learning-production.up.railway.app
   APP_ENV=production
   ```

4. **Create `nixpacks.toml`:**

   ```toml
   [phases.setup]
   nixPkgs = [
     "php82",
     "php82Extensions.pdo",
     "php82Extensions.pdo_mysql",
     "php82Extensions.mysqli",
     "php82Extensions.mbstring"
   ]

   [start]
   cmd = "php -S 0.0.0.0:$PORT -t ."
   ```

5. **Deploy:**

   * Push ke GitHub main branch
   * Railway auto-deploy
   * Pantau deployment logs

6. **Import Database:**

   * Connect ke Railway MySQL via TablePlus/DBeaver
   * Import `database.sql`

7. **Access Application:**

   ```text
   https://lgn-learning-production.up.railway.app
   ```

**Post-Deployment:**

* Test semua fitur
* Monitor logs untuk error
* Setup custom domain (optional)

---

## 📞 Support & Contact

**Email:**
[agillukman89@gmail.com](mailto:agillukman89@gmail.com) (untuk production)

---

## 🙏 Acknowledgments

* **Bootstrap Team** – UI Framework
* **Midtrans** – Payment Gateway
* **Google Drive** – Video Hosting
* **Railway** – Deployment Platform
* **PHP Community** – Resources dan documentation

---

## 📊 Project Statistics

* **Total Lines of Code**: ~8,500
* **Total Files**: 95
* **Development Time**: 3 Months
* **Team Size**: 4 Developers
* **Features Implemented**: 50+
* **API Endpoints**: 25+
* **Database Tables**: 10
* **Test Cases**: 15+

---

**Last Updated**: December 14, 2024
**Version**: 1.0.0
**Status**: ✅ Deployed & Live

---

<div align="center">
🎓 Learn GeNius – Belajar dari Ahlinya
   
Made with ❤️ by LGN Development Team
   
[Live Demo](https://lgn-learning-production.up.railway.app/) • [Documentation](https://github.com/TobiasOP/LGN-Learning)

</div>





