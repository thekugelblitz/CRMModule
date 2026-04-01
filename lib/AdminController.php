<?php
/**
 * CRMModule — AdminController
 *
 * Handles all admin-area form rendering and action processing.
 * All template output is rendered via include with extracted variables.
 *
 * @package    CRMModule
 * @author     HostingSpell LLP
 */

namespace CRMModule;

use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly.');
}

class AdminController
{
    /** @var array WHMCS module vars (includes modulelink) */
    private array $vars;

    /** @var CrmHelper */
    private CrmHelper $helper;

    /** @var string Base path for templates */
    private string $templatePath;

    /** @var string URL base for this module */
    private string $moduleLink;

    /** @var string Path for image uploads */
    private string $uploadPath;

    /** @var string Web-accessible uploads path relative to WHMCS root */
    private string $uploadWebPath;

    public function __construct(array $vars)
    {
        $this->vars         = $vars;
        $this->helper       = new CrmHelper();
        $this->templatePath = __DIR__ . '/../templates/admin/';
        $this->moduleLink   = $vars['modulelink'] ?? 'addonmodules.php?module=crmmodule';
        $this->uploadPath   = __DIR__ . '/../assets/uploads/';
        $this->uploadWebPath = 'modules/addons/crmmodule/assets/uploads/';
    }

    // -------------------------------------------------------------------------
    // Render methods
    // -------------------------------------------------------------------------

    /**
     * Dashboard: group → CRM mapping table.
     */
    public function renderDashboard(): void
    {
        $mappings  = $this->helper->getAllGroupMappings();
        $admins    = $this->helper->getAllAdmins();
        $flash     = $this->getFlash();
        $moduleLink = $this->moduleLink;

        $this->render('dashboard', compact('mappings', 'admins', 'flash', 'moduleLink'));
    }

    /**
     * Profiles: list all CRM profiles.
     */
    public function renderProfiles(): void
    {
        $profiles   = $this->helper->getAllProfiles();
        $admins     = $this->helper->getAllAdmins();
        $flash      = $this->getFlash();
        $moduleLink = $this->moduleLink;

        // Build a quick lookup of admin IDs that already have a profile
        $profiledAdminIds = array_column($profiles, 'admin_id');

        $this->render('profiles', compact('profiles', 'admins', 'profiledAdminIds', 'flash', 'moduleLink'));
    }

    /**
     * Edit/Create profile form.
     */
    public function renderEditProfile(): void
    {
        $adminId    = (int) ($_GET['admin_id'] ?? 0);
        $admins     = $this->helper->getAllAdmins();
        $moduleLink = $this->moduleLink;
        $profile    = null;
        $adminInfo  = null;

        if ($adminId) {
            $profile   = $this->helper->getCrmProfile($adminId);
            foreach ($admins as $a) {
                if ($a['id'] === $adminId) {
                    $adminInfo = $a;
                    break;
                }
            }
        }

        $flash = $this->getFlash();

        $this->render('edit_profile', compact('adminId', 'admins', 'profile', 'adminInfo', 'flash', 'moduleLink'));
    }

    // -------------------------------------------------------------------------
    // Action methods (POST handlers)
    // -------------------------------------------------------------------------

    /**
     * Save (upsert) a group → CRM mapping.
     */
    public function saveMapping(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('dashboard');
            return;
        }

        $groupId = (int) ($_POST['group_id'] ?? 0);
        $adminId = (int) ($_POST['admin_id'] ?? 0);

        if (!$groupId) {
            $this->setFlash('error', 'Invalid client group.');
            $this->redirect('dashboard');
            return;
        }

        try {
            if ($adminId) {
                // Verify admin exists (read-only check)
                $adminExists = Capsule::table('tbladmins')->where('id', $adminId)->exists();
                if (!$adminExists) {
                    $this->setFlash('error', 'Selected admin does not exist.');
                    $this->redirect('dashboard');
                    return;
                }

                Capsule::table('mod_crm_group_map')->updateOrInsert(
                    ['group_id' => $groupId],
                    ['admin_id' => $adminId, 'updated_at' => date('Y-m-d H:i:s'), 'created_at' => date('Y-m-d H:i:s')]
                );
                $this->setFlash('success', 'CRM assignment saved successfully.');
            } else {
                // admin_id = 0 means remove the mapping
                Capsule::table('mod_crm_group_map')->where('group_id', $groupId)->delete();
                $this->setFlash('success', 'CRM assignment removed.');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to save mapping: ' . htmlspecialchars($e->getMessage()));
        }

        $this->redirect('dashboard');
    }

    /**
     * Delete a group → CRM mapping.
     */
    public function deleteMapping(): void
    {
        $groupId = (int) ($_GET['group_id'] ?? 0);

        if (!$groupId) {
            $this->setFlash('error', 'Invalid group ID.');
            $this->redirect('dashboard');
            return;
        }

        try {
            Capsule::table('mod_crm_group_map')->where('group_id', $groupId)->delete();
            $this->setFlash('success', 'CRM assignment removed.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to remove mapping: ' . htmlspecialchars($e->getMessage()));
        }

        $this->redirect('dashboard');
    }

    /**
     * Save (create or update) a CRM profile.
     */
    public function saveProfile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('profiles');
            return;
        }

        $adminId     = (int) ($_POST['admin_id'] ?? 0);
        $displayName = $this->sanitizeString($_POST['display_name'] ?? '', 150);
        $bio         = $this->sanitizeText($_POST['bio'] ?? '');
        $contactEmail = $this->sanitizeEmail($_POST['contact_email'] ?? '');
        $phone       = $this->sanitizeString($_POST['phone'] ?? '', 50);
        $whatsapp    = $this->sanitizeString($_POST['whatsapp'] ?? '', 50);
        $designation = $this->sanitizeString($_POST['designation'] ?? '', 100);

        if (!$adminId) {
            $this->setFlash('error', 'Invalid admin ID.');
            $this->redirect('profiles');
            return;
        }

        // Verify admin exists
        $adminExists = Capsule::table('tbladmins')->where('id', $adminId)->exists();
        if (!$adminExists) {
            $this->setFlash('error', 'Admin not found.');
            $this->redirect('profiles');
            return;
        }

        // Handle profile image upload
        $profileImage = $this->sanitizeString($_POST['profile_image_url'] ?? '', 255);
        if (!empty($_FILES['profile_image']['name'])) {
            $uploaded = $this->handleImageUpload($_FILES['profile_image'], $adminId);
            if ($uploaded === false) {
                $this->setFlash('error', 'Image upload failed. Allowed types: jpg, png, gif, webp. Max size: 2MB.');
                $this->redirect('edit_profile', ['admin_id' => $adminId]);
                return;
            }
            $profileImage = $uploaded;
        }

        $data = [
            'display_name'  => $displayName,
            'profile_image' => $profileImage,
            'bio'           => $bio,
            'contact_email' => $contactEmail,
            'phone'         => $phone,
            'whatsapp'      => $whatsapp,
            'designation'   => $designation,
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        try {
            $exists = Capsule::table('mod_crm_profiles')->where('admin_id', $adminId)->exists();
            if ($exists) {
                Capsule::table('mod_crm_profiles')->where('admin_id', $adminId)->update($data);
            } else {
                $data['admin_id']   = $adminId;
                $data['created_at'] = date('Y-m-d H:i:s');
                Capsule::table('mod_crm_profiles')->insert($data);
            }
            $this->setFlash('success', 'CRM profile saved successfully.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to save profile: ' . htmlspecialchars($e->getMessage()));
        }

        $this->redirect('profiles');
    }

    /**
     * Delete a CRM profile.
     */
    public function deleteProfile(): void
    {
        $adminId = (int) ($_GET['admin_id'] ?? 0);

        if (!$adminId) {
            $this->setFlash('error', 'Invalid admin ID.');
            $this->redirect('profiles');
            return;
        }

        try {
            Capsule::table('mod_crm_profiles')->where('admin_id', $adminId)->delete();
            $this->setFlash('success', 'CRM profile deleted.');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to delete profile: ' . htmlspecialchars($e->getMessage()));
        }

        $this->redirect('profiles');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Renders a template file with extracted variables.
     *
     * @param string $template  Template filename without .php
     * @param array  $data      Variables to extract into template scope
     */
    private function render(string $template, array $data = []): void
    {
        $templateFile = $this->templatePath . $template . '.php';
        if (!file_exists($templateFile)) {
            echo '<div class="alert alert-danger">Template not found: ' . htmlspecialchars($template) . '</div>';
            return;
        }
        extract($data, EXTR_SKIP);
        include $templateFile;
    }

    /**
     * Redirects to a module action.
     *
     * @param string $action
     * @param array  $params Additional query params
     */
    private function redirect(string $action, array $params = []): void
    {
        $url = $this->moduleLink . '&action=' . urlencode($action);
        foreach ($params as $k => $v) {
            $url .= '&' . urlencode($k) . '=' . urlencode((string) $v);
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * Sets a flash message in the session.
     */
    private function setFlash(string $type, string $message): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION['crmmodule_flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * Reads and clears the flash message.
     */
    private function getFlash(): ?array
    {
        if (!empty($_SESSION['crmmodule_flash'])) {
            $flash = $_SESSION['crmmodule_flash'];
            unset($_SESSION['crmmodule_flash']);
            return $flash;
        }
        return null;
    }

    /**
     * Sanitizes a string field to a maximum length.
     */
    private function sanitizeString(string $value, int $maxLen): string
    {
        return mb_substr(strip_tags(trim($value)), 0, $maxLen);
    }

    /**
     * Sanitizes a multi-line text field.
     */
    private function sanitizeText(string $value): string
    {
        return strip_tags(trim($value));
    }

    /**
     * Validates and returns a clean email, or empty string.
     */
    private function sanitizeEmail(string $value): string
    {
        $value = trim($value);
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : '';
    }

    /**
     * Handles profile image file upload.
     *
     * @param array  $file     $_FILES entry
     * @param int    $adminId  Used to name the stored file
     * @return string|false    Relative web path on success, false on failure
     */
    private function handleImageUpload(array $file, int $adminId)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Size limit: 2MB
        if ($file['size'] > 2 * 1024 * 1024) {
            return false;
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // Validate MIME via finfo
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $allowedMimes, true)) {
            return false;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            return false;
        }

        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }

        $filename = 'crm_' . $adminId . '_' . time() . '.' . $ext;
        $destPath = $this->uploadPath . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return false;
        }

        return $this->uploadWebPath . $filename;
    }
}
