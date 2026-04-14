# Security and hardening notes

## Applied changes
- Removed hardcoded database credentials from `core/Database.php`.
- Removed hardcoded SMTP credentials from `services/Mail/MailerService.php`.
- Added `.env.example` for hosting-friendly configuration.
- Added secure session bootstrapping and centralized security headers.
- Added runtime logging and production-safe exception handling.
- Added dynamic route support with `{param}` placeholders.
- Added global CSRF validation for all POST form requests.
- Added missing CSRF tokens to public contact, PQRS, tickets and admin logout forms.
- Added `_419` and `_500` error views.
- Hardened upload directory permissions and `is_uploaded_file` checks.
- Split route registration into `routes/web.php`, `routes/admin.php`, `routes/api.php`.
- Added `.htaccess` rules for Apache/shared hosting.

## Still recommended before production
- Configure real SMTP and DB credentials in `.env`.
- Point the web root to `/public` whenever your hosting allows it.
- Enable HTTPS and force it at the hosting layer.
- Review all remaining forms and add domain validation rules per module.
- Add backups and external monitoring/log rotation.
- Add rate limiting for auth and public intake forms if traffic grows.
- Implement CRM persistence for contact/PQRS flows instead of inbox-only handling.
