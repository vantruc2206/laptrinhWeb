# 📰 laptrinhWeb - Website Tin Tức PHP

Web ứng dụng tin tức đơn giản xây dựng bằng PHP thuần + MySQL.

---

## ⚙️ Yêu cầu cài đặt

- **XAMPP** (Apache + MySQL)
- **PHP** >= 7.4
- **MySQL** >= 5.7

---

## 🚀 Hướng dẫn cài đặt

1. Copy toàn bộ thư mục `laptrinhWeb` vào `C:\xampp\htdocs\`
2. Mở **phpMyAdmin** tại `http://localhost/phpmyadmin`
3. Tạo database mới tên: `news_db`
4. Import file `database.sql` vào database vừa tạo
5. Truy cập web tại: `http://localhost/laptrinhWeb`

### Tài khoản Admin mặc định
| Username | Password |
|----------|----------|
| admin    | admin123 |

---

## 📁 Cấu trúc thư mục

```
laptrinhWeb/
│
├── 📄 index.php                  # Trang chủ - hiển thị danh sách bài viết
├── 📄 article.php                # Trang chi tiết một bài viết
├── 📄 category.php               # Trang danh sách bài viết theo danh mục
├── 📄 database.sql               # Script tạo database và dữ liệu mặc định
├── 📄 add_sample.php             # Script thêm dữ liệu mẫu (dùng khi setup)
├── 📄 reset.php                  # Reset lại dữ liệu database
├── 📄 update_password.php        # Cập nhật password (tiện ích)
│
├── 📁 auth/                      # Xử lý xác thực người dùng
│   ├── 📄 login.php              # Trang đăng nhập
│   ├── 📄 logout.php             # Xử lý đăng xuất
│   └── 📄 register.php           # Trang đăng ký tài khoản
│
├── 📁 admin/                     # Khu vực quản trị (yêu cầu đăng nhập admin)
│   ├── 📄 index.php              # Dashboard admin - thống kê tổng quan
│   ├── 📄 articles.php           # Quản lý bài viết (thêm/sửa/xóa)
│   ├── 📄 categories.php         # Quản lý danh mục
│   ├── 📄 users.php              # Quản lý người dùng
│   └── 📁 includes/              # Layout dùng chung cho trang admin
│       ├── 📄 header.php         # Header + sidebar của trang admin
│       └── 📄 footer.php         # Footer của trang admin
│
├── 📁 includes/                  # Các file dùng chung cho toàn site
│   ├── 📄 config.php             # Cấu hình kết nối database (DB_HOST, DB_NAME...)
│   ├── 📄 functions.php          # Các hàm tiện ích (sanitize, redirect, generateSlug...)
│   ├── 📄 header.php             # Header HTML của trang người dùng (nav, menu)
│   └── 📄 footer.php             # Footer HTML của trang người dùng
│
├── 📁 assets/                    # Tài nguyên tĩnh (CSS, ảnh)
│   ├── 📁 css/
│   │   ├── 📄 style.css          # CSS chính cho trang người dùng
│   │   └── 📄 admin.css          # CSS riêng cho trang admin
│   └── 📁 images/
│       └── 📁 articles/          # Ảnh đại diện của các bài viết (upload lên đây)
│
└── 📁 .vscode/                   # Cấu hình VS Code (có thể bỏ qua hoặc xóa)
```

---

## 🗄️ Cấu trúc Database (`news_db`)

### Bảng `users` - Người dùng
| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | INT | Khóa chính, tự tăng |
| username | VARCHAR(50) | Tên đăng nhập (duy nhất) |
| password | VARCHAR(255) | Mật khẩu đã mã hóa bcrypt |
| email | VARCHAR(100) | Email (duy nhất) |
| role | ENUM | Vai trò: `user` hoặc `admin` |
| created_at | TIMESTAMP | Thời gian tạo |

### Bảng `categories` - Danh mục
| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | INT | Khóa chính, tự tăng |
| name | VARCHAR(100) | Tên danh mục |
| slug | VARCHAR(100) | Đường dẫn thân thiện (duy nhất) |
| description | TEXT | Mô tả danh mục |
| created_at | TIMESTAMP | Thời gian tạo |

### Bảng `articles` - Bài viết
| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | INT | Khóa chính, tự tăng |
| category_id | INT | Liên kết tới bảng categories |
| author_id | INT | Liên kết tới bảng users |
| title | VARCHAR(255) | Tiêu đề bài viết |
| slug | VARCHAR(255) | Đường dẫn thân thiện (duy nhất) |
| summary | TEXT | Tóm tắt nội dung |
| content | LONGTEXT | Nội dung đầy đủ (hỗ trợ HTML) |
| image | VARCHAR(255) | Tên file ảnh đại diện |
| views | INT | Số lượt xem |
| status | ENUM | Trạng thái: `published` hoặc `draft` |
| created_at | TIMESTAMP | Thời gian tạo |
| updated_at | TIMESTAMP | Thời gian cập nhật gần nhất |

---

## 📌 Lưu ý quan trọng

- **Ảnh bài viết** được lưu tại `assets/images/articles/` — database chỉ lưu tên file
- File `test_hash.php`, `add_sample.php`, `reset.php` chỉ dùng khi dev, nên xóa trước khi deploy
- Thư mục `.vscode/` là cấu hình VS Code, không ảnh hưởng đến code, có thể xóa
