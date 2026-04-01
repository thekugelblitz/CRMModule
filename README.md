# CRMModule — WHMCS Addon Module

**Version:** 1.0.0  
**Author:** HostingSpell LLP  
**License:** GPL-3.0 (see [LICENSE](LICENSE))  
**Compatibility:** WHMCS 8.x+, PHP 7.4+  
**Themes:** Six, Twenty-One, Lagom2

---

## Overview

CRMModule enhances the native WHMCS Client Group system by introducing a CRM (Client Relationship Manager) assignment layer. Each Client Group is mapped to a specific admin who acts as the dedicated account manager for all clients in that group.

### Features

- **CRM Assignment** — Map any admin to any Client Group via a clean admin UI
- **CRM Profiles** — Rich admin profiles (photo, bio, phone, WhatsApp, designation)
- **Ticket Integration** — Client Group badge and assigned CRM name injected into the admin ticket view automatically
- **Client Area Widget** — CRM card with avatar and "View Profile" button on the client dashboard
- **Client Area Profile Page** — Full CRM profile at `index.php?m=crmmodule`
- **Client Ticket Strip** — Subtle CRM info shown to clients on the ticket view page
- **No core file modifications** — Hooks-only integration

---

## Requirements

| Requirement | Version |
|-------------|---------|
| WHMCS | 8.0 or higher |
| PHP | 7.4 or higher |
| MySQL/MariaDB | 5.7+ / 10.3+ |

---

## Installation

### Step 1 — Upload Files

Upload the entire `modules/addons/crmmodule/` directory to your WHMCS installation, preserving the directory structure:

```
<whmcs_root>/
└── modules/
    └── addons/
        └── crmmodule/
            ├── crmmodule.php
            ├── hooks.php
            ├── lib/
            ├── templates/
            └── assets/
```

### Step 2 — Set Directory Permissions

Ensure the uploads directory is writable by your web server:

```bash
chmod 755 modules/addons/crmmodule/assets/uploads/
```

### Step 3 — Activate the Module

1. Log in to your WHMCS Admin Area
2. Navigate to **Setup → Addon Modules**
3. Find **CRM Module** in the list and click **Activate**
4. The module will automatically create the required database tables (`mod_crm_group_map` and `mod_crm_profiles`)

### Step 4 — Set Access Permissions

After activating:

1. Click the **Configure** button next to CRM Module
2. Under **Access Control**, tick the admin roles that should have access to manage CRM assignments and profiles
3. Click **Save Changes**

---

## Configuration

### Creating CRM Profiles

1. Go to **Addons → CRM Module → CRM Profiles**
2. Click the admin name you want to create a profile for
3. Fill in display name, designation, bio, contact email, phone, WhatsApp, and profile image (upload or URL; Gravatar if blank)
4. Click **Create Profile**

### Assigning CRMs to Client Groups

1. Go to **Addons → CRM Module** (Dashboard tab)
2. For each Client Group, select an admin from the dropdown and save
3. To remove an assignment, choose **— Remove —** and save

---

## Client Area URLs

| Feature | URL |
|---------|-----|
| CRM Profile Page | `https://yourwhmcs.com/index.php?m=crmmodule` |

---

## Database Tables

- **`mod_crm_group_map`** — `group_id` (unique) → `admin_id`
- **`mod_crm_profiles`** — CRM profile fields per `admin_id` (unique)

Core WHMCS tables are used **read-only** only.

---

## File Structure

```
modules/addons/crmmodule/
├── crmmodule.php
├── hooks.php
├── lib/
│   ├── CrmHelper.php
│   └── AdminController.php
├── templates/
│   ├── admin/          # dashboard.php, profiles.php, edit_profile.php
│   └── clientarea/     # profile.tpl, widget.tpl
└── assets/
    ├── css/crmmodule.css
    ├── js/crmmodule.js
    └── uploads/
```

---

## Hooks Reference

| Hook | Purpose |
|------|---------|
| `AdminAreaPageViewTicket` | Client Group + CRM in admin ticket sidebar |
| `ClientAreaHomepage` | CRM widget on client dashboard |
| `ClientAreaPageViewTicket` | CRM strip on client ticket view |

---

## Support

For support or contributions, open an issue on [thekugelblitz/CRMModule](https://github.com/thekugelblitz/CRMModule).

Developed by **HostingSpell LLP**.
