# InstaApp

Aplikasi media sosial mirip Instagram yang dibangun dengan PHP (Backend API) dan HTML/CSS/JavaScript (Frontend).

## 🚀 Fitur

- ✅ **Register & Login** - Autentifikasi pengguna dengan JWT
- ✅ **Posting** - Upload gambar dengan caption
- ✅ **Like** - Like dan unlike post
- ✅ **Comment** - Komentar pada post
- ✅ **Authorization** - Hak akses untuk menghapus post dan komentar sendiri
- ✅ **UI/UX Modern** - Design premium dengan animasi dan gradient

## 📋 Teknologi

### Backend
- **PHP** - RESTful API
- **MySQL** - Database (RDBMS)
- **JWT** - Authentication token

### Frontend
- **HTML5** - Struktur
- **CSS3** - Styling dengan animasi modern
- **JavaScript (Vanilla)** - Logic dan API calls

## 🛠️ Instalasi

### Prerequisites
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx) atau PHP built-in server
- Browser modern

### Langkah Instalasi

1. **Clone atau extract project**
   ```bash
   cd d:/Work/test/Reild Meideant Pratama - TaskFS/instaapp
   ```

2. **Setup Database**
   - Buat database MySQL
   - Import file `database/schema.sql`
   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. **Konfigurasi Database**
   - Edit file `backend/config/database.php`
   - Sesuaikan kredensial database:
   ```php
   private $host = "localhost";
   private $db_name = "instaapp";
   private $username = "root";
   private $password = "";
   ```

4. **Konfigurasi JWT Secret**
   - Edit file `backend/config/jwt.php`
   - Ubah secret key untuk production:
   ```php
   private static $secret_key = "your-secret-key-change-this-in-production";
   ```

5. **Buat folder uploads**
   ```bash
   mkdir backend/uploads
   mkdir backend/uploads/posts
   chmod 777 backend/uploads
   chmod 777 backend/uploads/posts
   ```

6. **Jalankan Server**
   
   **Menggunakan PHP Built-in Server:**
   ```bash
   # Terminal 1 - Backend
   cd backend
   php -S localhost:8000

   # Terminal 2 - Frontend
   cd frontend
   php -S localhost:3000
   ```

   **Atau menggunakan XAMPP/WAMP:**
   - Copy folder `instaapp` ke `htdocs`
   - Akses via `http://localhost/instaapp/frontend`

7. **Akses Aplikasi**
   - Buka browser: `http://localhost:3000`
   - Register akun baru atau login dengan akun demo:
     - Username: `johndoe` / Password: `password`
     - Username: `janedoe` / Password: `password`

## 📁 Struktur Folder

```
instaapp/
├── backend/
│   ├── api/
│   │   ├── auth/
│   │   │   ├── register.php
│   │   │   └── login.php
│   │   ├── posts/
│   │   │   ├── create.php
│   │   │   ├── list.php
│   │   │   └── delete.php
│   │   ├── likes/
│   │   │   └── toggle.php
│   │   └── comments/
│   │       ├── create.php
│   │       ├── list.php
│   │       └── delete.php
│   ├── config/
│   │   ├── database.php
│   │   └── jwt.php
│   ├── models/
│   │   ├── User.php
│   │   ├── Post.php
│   │   ├── Like.php
│   │   └── Comment.php
│   └── uploads/
│       └── posts/
├── frontend/
│   ├── css/
│   │   ├── style.css
│   │   ├── auth.css
│   │   └── feed.css
│   ├── js/
│   │   ├── api.js
│   │   ├── auth.js
│   │   └── feed.js
│   ├── index.html
│   ├── login.html
│   ├── register.html
│   └── feed.html
└── database/
    └── schema.sql
```

## 🔐 API Endpoints

### Authentication
- `POST /api/auth/register.php` - Register user baru
- `POST /api/auth/login.php` - Login user

### Posts
- `GET /api/posts/list.php` - List semua posts
- `POST /api/posts/create.php` - Buat post baru (requires auth)
- `DELETE /api/posts/delete.php` - Hapus post (requires auth & ownership)

### Likes
- `POST /api/likes/toggle.php` - Toggle like/unlike (requires auth)

### Comments
- `GET /api/comments/list.php?post_id={id}` - List comments
- `POST /api/comments/create.php` - Buat comment (requires auth)
- `DELETE /api/comments/delete.php` - Hapus comment (requires auth & ownership)

## 🎨 Fitur UI/UX

- **Gradient Modern** - Purple, pink, dan blue gradients
- **Glassmorphism** - Backdrop blur effects
- **Smooth Animations** - Fade in, slide in, pulse effects
- **Responsive Design** - Mobile-friendly
- **Interactive Elements** - Hover effects dan micro-animations
- **Toast Notifications** - Real-time feedback
- **Modal Dialogs** - Create post modal

## 🔒 Security Features

- **JWT Authentication** - Token-based auth
- **Password Hashing** - BCrypt hashing
- **SQL Injection Prevention** - Prepared statements
- **XSS Protection** - Input sanitization
- **CSRF Protection** - Token validation
- **Authorization Checks** - Ownership verification

## 📝 Catatan

- Default password untuk sample users: `password`
- Ubah JWT secret key sebelum production
- Sesuaikan CORS settings untuk production
- Gunakan HTTPS untuk production
- Implementasi rate limiting untuk production

## 👨‍💻 Developer

Dibuat untuk memenuhi tugas TaskFS

## 📄 License

Free to use for educational purposes
