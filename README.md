## 📋 RINGKASAN EKSEKUTIF

| Aspek | Teknologi |
|-------|-----------|
| **Frontend** | HTML5, CSS3, JavaScript (Native), Bootstrap 5 |
| **Backend** | PHP 8.x (Native/Vanilla) |
| **Database** | MySQL 8.x / MariaDB |
| **Payment Gateway** | Midtrans Sandbox API |
| **Video Streaming** | Google Drive API |
| **Web Server** | Apache (XAMPP) |

---

## 🎨 FRONTEND

### 1. HTML5 (HyperText Markup Language)

**Penggunaan:**
- Struktur semantik halaman web
- Form input untuk registrasi, login, checkout
- Tabel untuk menampilkan data
- Multimedia embedding (video iframe)

**Contoh Implementasi:**
```html
<!-- Struktur semantik -->
<header>...</header>
<main>
    <section class="hero-section">...</section>
    <section class="courses-section">...</section>
</main>
<footer>...</footer>

<!-- Form dengan validasi HTML5 -->
<form id="loginForm" novalidate>
    <input type="email" required>
    <input type="password" minlength="6" required>
</form>
```

**Fitur HTML5 yang digunakan:**
- Semantic elements (`<header>`, `<main>`, `<section>`, `<article>`, `<footer>`)
- Form validation attributes (`required`, `minlength`, `type="email"`)
- Data attributes (`data-course-id`, `data-lesson-id`)
- Responsive images dengan `srcset` dan `loading="lazy"`

---

### 2. CSS3 (Cascading Style Sheets)

**Penggunaan:**
- Styling seluruh komponen website
- Responsive design
- Animasi dan transisi
- Custom properties (CSS Variables)

**Fitur CSS3 yang digunakan:**

```css
/* CSS Variables */
:root {
    --primary: #4f46e5;
    --secondary: #10b981;
    --gradient-primary: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
}

/* Flexbox Layout */
.course-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Grid Layout */
.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

/* Transitions & Animations */
.course-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.course-card:hover {
    transform: translateY(-8px);
}

/* Media Queries (Responsive) */
@media (max-width: 768px) {
    .hero-title { font-size: 2rem; }
}
```

**Konsep CSS yang diterapkan:**
- Box Model (margin, padding, border)
- Positioning (relative, absolute, fixed, sticky)
- Flexbox untuk layout 1 dimensi
- CSS Grid untuk layout 2 dimensi
- Media Queries untuk responsive design
- Pseudo-classes (`:hover`, `:focus`, `:active`)
- Pseudo-elements (`::before`, `::after`)
- CSS Variables untuk theming
- Gradients (linear-gradient)
- Box-shadow dan text-shadow
- Border-radius untuk rounded corners
- Transitions untuk animasi halus

---

### 3. JavaScript (Native/Vanilla)

**Penggunaan:**
- Interaksi user interface
- AJAX requests ke API
- Form validation
- DOM manipulation
- Event handling

**Fitur JavaScript yang digunakan:**

```javascript
// ES6+ Features
// 1. Arrow Functions
const formatCurrency = (amount) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
};

// 2. Template Literals
const html = `<div class="course-card">
    <h5>${course.title}</h5>
    <span>${formatCurrency(course.price)}</span>
</div>`;

// 3. Destructuring
const { name, email, role } = userData;

// 4. Async/Await untuk AJAX
async function loadCourses() {
    try {
        const response = await fetch('/api/courses/list.php');
        const data = await response.json();
        renderCourses(data);
    } catch (error) {
        console.error('Error:', error);
    }
}

// 5. Fetch API
const API = {
    async post(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        return response.json();
    }
};

// 6. Event Listeners
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    // Handle form submission
});

// 7. DOM Manipulation
document.getElementById('coursesGrid').innerHTML = coursesHTML;

// 8. Local Storage (untuk preferences)
localStorage.setItem('theme', 'dark');

// 9. Classes
class Toast {
    static show(message, type = 'success') {
        // Show toast notification
    }
}
```

**Konsep JavaScript yang diterapkan:**
- DOM Manipulation (getElementById, querySelector, innerHTML)
- Event Handling (addEventListener, event delegation)
- Fetch API untuk AJAX requests
- Promises dan Async/Await
- Error Handling (try-catch)
- ES6 Modules pattern
- Object-Oriented Programming (Classes)
- Closures dan Callbacks
- Debounce dan Throttle untuk performance
- Form Validation

---

### 4. Bootstrap 5

**Penggunaan:**
- Grid system untuk responsive layout
- Pre-built components (cards, modals, dropdowns)
- Utility classes
- Icons (Bootstrap Icons)

**Komponen Bootstrap yang digunakan:**

```html
<!-- Grid System -->
<div class="container">
    <div class="row g-4">
        <div class="col-lg-3 col-md-6">...</div>
    </div>
</div>

<!-- Cards -->
<div class="card course-card h-100">
    <img class="card-img-top">
    <div class="card-body">...</div>
    <div class="card-footer">...</div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
    <div class="container">...</div>
</nav>

<!-- Modal -->
<div class="modal fade" id="previewModal">
    <div class="modal-dialog modal-lg">...</div>
</div>

<!-- Buttons -->
<button class="btn btn-primary btn-lg">
    <i class="bi bi-play-circle me-2"></i>Mulai Belajar
</button>

<!-- Alerts -->
<div class="alert alert-success alert-dismissible fade show">...</div>

<!-- Forms -->
<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" class="form-control">
</div>

<!-- Dropdown -->
<div class="dropdown">
    <button class="dropdown-toggle" data-bs-toggle="dropdown">
    <ul class="dropdown-menu">...</ul>
</div>

<!-- Progress Bar -->
<div class="progress">
    <div class="progress-bar" style="width: 75%"></div>
</div>

<!-- Accordion -->
<div class="accordion" id="curriculumAccordion">
    <div class="accordion-item">...</div>
</div>

<!-- Toast -->
<div class="toast" role="alert">...</div>

<!-- Badges -->
<span class="badge bg-primary">Web Development</span>

<!-- Pagination -->
<nav>
    <ul class="pagination">...</ul>
</nav>
```

**Utility Classes yang sering digunakan:**
- Spacing: `m-3`, `p-4`, `mt-5`, `mb-3`, `gap-3`
- Flexbox: `d-flex`, `justify-content-between`, `align-items-center`
- Display: `d-none`, `d-lg-block`, `d-flex`
- Text: `text-center`, `text-muted`, `fw-bold`, `fs-4`
- Colors: `text-primary`, `bg-light`, `text-white`
- Borders: `rounded`, `border-0`, `rounded-circle`
- Shadows: `shadow`, `shadow-sm`, `shadow-lg`

---

## 🔧 BACKEND

### 1. PHP 8.x (Native/Vanilla)

**Penggunaan:**
- Server-side processing
- Database operations
- Session management
- API endpoints
- File handling

**Fitur PHP yang digunakan:**

```php
<?php
// 1. PDO untuk Database Connection
class Database {
    public function getConnection() {
        $conn = new PDO(
            "mysql:host=localhost;dbname=lgn_elearning",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $conn;
    }
}

// 2. Prepared Statements (SQL Injection Prevention)
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Password Hashing
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$isValid = password_verify($inputPassword, $hashedPassword);

// 4. Session Management
session_start();
$_SESSION['user_id'] = $userId;
$_SESSION['user_role'] = $role;

// 5. JSON API Response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => $courses
]);

// 6. File Upload
if ($_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    move_uploaded_file(
        $_FILES['thumbnail']['tmp_name'],
        $targetPath
    );
}

// 7. cURL untuk External API (Midtrans)
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($params),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($serverKey . ':')
    ]
]);
$response = curl_exec($ch);

// 8. Input Sanitization
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// 9. Authentication Middleware
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        header('Location: /index.php');
        exit;
    }
}

// 10. Error Handling
try {
    // Database operations
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
}
```

**Konsep PHP yang diterapkan:**
- Object-Oriented Programming (Classes, Methods)
- PDO untuk database abstraction
- Prepared Statements untuk keamanan
- Session handling untuk autentikasi
- File handling untuk upload
- cURL untuk HTTP requests
- JSON encoding/decoding
- Error handling dengan try-catch
- Input validation dan sanitization
- RESTful API design

---

### 2. MySQL Database

**Penggunaan:**
- Penyimpanan data persistent
- Relational data management
- Query optimization

**Struktur Database:**

```sql
-- Tabel Utama
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'tutor', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tutor_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    price DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0,
    UNIQUE KEY unique_enrollment (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);
```

**Konsep Database yang diterapkan:**
- Normalization (1NF, 2NF, 3NF)
- Primary Keys dan Auto Increment
- Foreign Keys untuk relasi
- Indexes untuk optimasi query
- UNIQUE constraints
- ENUM untuk fixed values
- Timestamps untuk audit trail
- Cascade delete untuk referential integrity
- JOIN queries untuk relasi

**Contoh Query yang digunakan:**

```sql
-- JOIN untuk mengambil kursus dengan info tutor dan kategori
SELECT 
    c.*,
    cat.name as category_name,
    u.name as tutor_name,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count,
    (SELECT AVG(rating) FROM reviews WHERE course_id = c.id) as avg_rating
FROM courses c
JOIN categories cat ON c.category_id = cat.id
JOIN users u ON c.tutor_id = u.id
WHERE c.is_published = 1
ORDER BY c.created_at DESC;

-- Subquery untuk statistik
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN transaction_status = 'success' THEN final_amount ELSE 0 END) as revenue
FROM transactions;

-- Update dengan kondisi
UPDATE enrollments 
SET progress_percentage = ?,
    completed_at = CASE WHEN ? >= 100 THEN NOW() ELSE NULL END
WHERE user_id = ? AND course_id = ?;
```

---

## 🔌 INTEGRASI API

### 1. Midtrans Payment Gateway

**Tujuan:** Memproses pembayaran online

**Implementasi:**

```php
<?php
// config/midtrans.php

class MidtransConfig {
    const IS_PRODUCTION = false; // Sandbox mode
    const CLIENT_KEY = 'SB-Mid-client-xxxx';
    const SERVER_KEY = 'SB-Mid-server-xxxx';
    
    const SANDBOX_SNAP_URL = 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    
    public static function createSnapToken($params) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::SANDBOX_SNAP_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode(self::SERVER_KEY . ':')
            ]
        ]);
        
        $response = curl_exec($ch);
        return json_decode($response, true);
    }
}

// Parameter transaksi
$params = [
    'transaction_details' => [
        'order_id' => 'LGN-20240115-ABC123',
        'gross_amount' => 499000
    ],
    'customer_details' => [
        'first_name' => 'John',
        'email' => 'john@example.com'
    ],
    'item_details' => [
        [
            'id' => 'COURSE-1',
            'price' => 499000,
            'quantity' => 1,
            'name' => 'Web Development Bootcamp'
        ]
    ]
];
```

**Flow Pembayaran:**
1. User klik "Beli Sekarang"
2. Frontend request ke `/api/payment/create_transaction.php`
3. Backend generate Snap Token dari Midtrans
4. Frontend menampilkan popup Midtrans Snap
5. User memilih metode pembayaran
6. Midtrans kirim notification ke webhook
7. Backend update status transaksi
8. User di-enroll ke kursus

---

### 2. Google Drive API

**Tujuan:** Streaming video pembelajaran

**Implementasi:**

```php
<?php
// config/google_drive.php

class GoogleDriveAPI {
    private $apiKey = 'YOUR_GOOGLE_API_KEY';
    
    // Generate embed URL untuk iframe
    public function getEmbedUrl($fileId) {
        return "https://drive.google.com/file/d/{$fileId}/preview";
    }
    
    // Get file metadata
    public function getFileMetadata($fileId) {
        $url = "https://www.googleapis.com/drive/v3/files/{$fileId}";
        $url .= "?fields=id,name,mimeType,size";
        $url .= "&key={$this->apiKey}";
        
        $response = file_get_contents($url);
        return json_decode($response, true);
    }
    
    // Extract file ID dari URL
    public function extractFileId($url) {
        preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches);
        return $matches[1] ?? null;
    }
}
```

**Cara Penggunaan:**
1. Upload video ke Google Drive
2. Share dengan "Anyone with the link"
3. Salin File ID dari URL
4. Simpan File ID di database
5. Generate embed URL saat user mengakses lesson

---

## 🔒 KEAMANAN

### Implementasi Keamanan:

```php
<?php
// 1. SQL Injection Prevention
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]); // Parameterized query

// 2. XSS Prevention
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// 3. CSRF Protection (via session)
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// 4. Password Hashing
$hash = password_hash($password, PASSWORD_DEFAULT);

// 5. Input Validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Email tidak valid');
}

// 6. Session Security
session_start();
session_regenerate_id(true); // Prevent session fixation

// 7. HTTP Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// 8. File Upload Validation
$allowedTypes = ['image/jpeg', 'image/png'];
if (!in_array($_FILES['file']['type'], $allowedTypes)) {
    throw new Exception('Tipe file tidak diizinkan');
}
```

---

## 📁 ARSITEKTUR PROYEK

```
lgn-elearning/
│
├── config/                 # Konfigurasi
│   ├── database.php       # Koneksi database
│   ├── midtrans.php       # Konfigurasi Midtrans
│   └── google_drive.php   # Konfigurasi Google Drive
│
├── includes/              # File yang di-include
│   ├── functions.php      # Helper functions
│   ├── header.php         # HTML header
│   ├── navbar.php         # Navigasi
│   └── footer.php         # HTML footer
│
├── api/                   # REST API Endpoints
│   ├── auth/
│   │   ├── login.php
│   │   ├── register.php
│   │   └── logout.php
│   ├── courses/
│   │   ├── list.php
│   │   └── detail.php
│   ├── payment/
│   │   ├── create_transaction.php
│   │   └── notification.php    # Webhook
│   └── videos/
│       ├── get_video.php
│       └── update_progress.php
│
├── pages/                 # Halaman Frontend
│   ├── courses.php
│   ├── course_detail.php
│   ├── checkout.php
│   ├── my_learning.php
│   ├── learn.php
│   ├── profile.php
│   └── tutor/
│       ├── dashboard.php
│       └── add_course.php
│
├── admin/                 # Admin Panel
│   ├── index.php
│   ├── users.php
│   ├── courses.php
│   └── transactions.php
│
├── assets/                # Static Files
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   ├── main.js
│   │   ├── auth.js
│   │   ├── courses.js
│   │   ├── payment.js
│   │   └── video-player.js
│   └── images/
│
├── index.php              # Homepage
├── login.php              # Login page
├── register.php           # Register page
└── database.sql           # Database schema
```

---

## 📊 FITUR UTAMA

| No | Fitur | Teknologi Frontend | Teknologi Backend |
|----|-------|-------------------|-------------------|
| 1 | Autentikasi (Login/Register) | HTML Form, JS Validation | PHP Session, Password Hash |
| 2 | Katalog Kursus | Bootstrap Cards, JS Filter | PHP + MySQL Query |
| 3 | Detail Kursus | Accordion, Tabs | JOIN Query, JSON API |
| 4 | Video Streaming | iframe Embed | Google Drive API |
| 5 | Progress Tracking | Progress Bar, JS | MySQL Update |
| 6 | Pembayaran | Midtrans Snap JS | Midtrans API, Webhook |
| 7 | Dashboard Tutor | Charts, Tables | Aggregate Query |
| 8 | Admin Panel | DataTables, Forms | CRUD Operations |
| 9 | Responsive Design | Bootstrap Grid, Media Query | - |
| 10 | Notifikasi | Toast, Alert | PHP Session Flash |

---

## 🎯 LEARNING OUTCOMES

Melalui proyek ini, mahasiswa mempelajari:

### Frontend:
1. ✅ Semantic HTML5 structure
2. ✅ CSS3 modern features (Flexbox, Grid, Variables)
3. ✅ Vanilla JavaScript (ES6+, Async/Await, Fetch API)
4. ✅ Bootstrap 5 framework
5. ✅ Responsive web design
6. ✅ Form validation
7. ✅ AJAX communication

### Backend:
1. ✅ PHP OOP concepts
2. ✅ PDO database abstraction
3. ✅ RESTful API design
4. ✅ Session management
5. ✅ Security best practices
6. ✅ Third-party API integration
7. ✅ File upload handling

### Database:
1. ✅ Database design & normalization
2. ✅ SQL queries (JOIN, Subquery, Aggregate)
3. ✅ Indexing & optimization
4. ✅ Referential integrity

### Integration:
1. ✅ Payment gateway (Midtrans)
2. ✅ Video streaming (Google Drive)
3. ✅ Webhook handling
