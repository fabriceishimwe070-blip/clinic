# Pro-Clinic — Telemedicine System

## Quick Setup (InfinityFree / Shared Hosting)

### 1. Database
1. Create a MySQL database in your InfinityFree cPanel.
2. Import `clinic_schema.sql` using phpMyAdmin.
3. If upgrading an existing DB, also run `call_schema_patch.sql`.

### 2. Config
Edit **`config.php`** with your real credentials:
```php
define('DB_HOST', 'sqlXXX.infinityfree.com');  // from cPanel
define('DB_NAME', 'epiz_XXXXXXXX_clinic');
define('DB_USER', 'epiz_XXXXXXXX');
define('DB_PASS', 'your_password');
```

### 3. Upload files
Upload all files to `htdocs/` (or your public root).

### 4. Default admin login
- Email: `admin@medicare.com`
- Password: (set via `create_admin.php` then **delete that file**)

---

## What was fixed (v2)

| File | Fix |
|------|-----|
| `config.php` | **NEW** — DB credentials separated from logic |
| `session.php` | Single authoritative session bootstrap; fixed CRLF line endings |
| `db.php` | Removed localhost assumption; loads `config.php`; removed duplicate `session_start()` |
| `doctor_dashboard.php` | **Removed duplicate polling script** (was two separate JS blocks both polling `call_poll.php` and showing two different call modals); added **Call History tab** with patient name, contact, status, time, room link |
| `call_poll.php` | Added `patient_email` and `patient_phone` to SELECT (overlay was referencing undefined fields) |
| `call_notify.php` | Added doctor existence check; standardized auth via `require_patient()` |
| `call_status.php` | Session standardized |
| `call_schema_patch.sql` | Fixed broken first line (` table` → proper comment) |
| `book_appointment.php` | Removed unreachable code (notification INSERT after `exit`) |
| `chat.php` | Added auth check (`require_patient`); divisions now loaded from DB instead of hardcoded JS array; routes to `video_call.php` properly |
| `doctor_video_call.php` | Added URL validation to prevent XSS/open-redirect; added proper top bar |
| `logout.php` | Proper session destroy (clears cookie + session data) |
| `home.php` | Fixed nav link "💬 Live Chat" → `chat.php` (was wrongly pointing to `video_call.php`) |
| `.htaccess` | **NEW** — blocks direct access to config/db/session files; sets security headers; disables directory listing |
| All PHP files | Removed duplicate `session_start()` calls (now handled by `db.php` → `session.php`) |

## Video Call Flow
1. Patient clicks a doctor → `video_call.php?doctor_id=N`
2. Patient clicks "Call Doctor" → `call_notify.php` (POST) stores call in `call_requests` with status=`ringing`
3. Doctor's dashboard polls `call_poll.php` every 1.5 s → shows incoming call overlay
4. Doctor clicks Answer → `call_poll.php` (POST, action=answer) → opens `doctor_video_call.php?room=URL`
5. Patient polls `call_status.php` every 2 s → auto-opens room when status=`answered`
