# 💻 TechStock Kenya — PHP + MySQL ERP System

## Setup
1. Copy `techstock/` to `C:\xampp\htdocs\`
2. Import `techstock_db.sql` in phpMyAdmin
3. Visit `http://localhost/techstock/`

## Default Logins
| Email | Password | Role |
|-------|----------|------|
| admin@techstock.co.ke | password | Owner |
| john@techstock.co.ke | password | Employee |

## Pages
### Public
- `/techstock/` — Home
- `/techstock/marketplace.php` — Browse PCs
- `/techstock/shops.php` — View Shops
- `/techstock/product.php?id=1` — Product Detail

### Owner Dashboard
- `/techstock/owner/dashboard.php`
- `/techstock/owner/shops.php`
- `/techstock/owner/employees.php`
- `/techstock/owner/products.php`
- `/techstock/owner/orders.php`
- `/techstock/owner/analytics.php`
- `/techstock/owner/audit.php`

### Employee Dashboard
- `/techstock/employee/dashboard.php`
- `/techstock/employee/products.php`
- `/techstock/employee/add_product.php`
- `/techstock/employee/orders.php`

## Key Business Rules
- Sold products are PERMANENTLY LOCKED (non-editable, non-deletable)
- Employees can only access their assigned shop
- All actions are logged in audit_logs table
- WhatsApp button on every product
