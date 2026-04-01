<?php
/**
 * CRMModule — CrmHelper
 *
 * Read-only query helper. Fetches data from WHMCS core tables (read-only)
 * and from module-owned tables via Capsule ORM.
 *
 * @package    CRMModule
 * @author     HostingSpell LLP
 */

namespace CRMModule;

use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly.');
}

class CrmHelper
{
    /** @var bool|null Cached: tblclientgroups.color exists (added in newer WHMCS; absent on older installs) */
    private static ?bool $clientGroupsHasColor = null;

    /** Default badge colour when tblclientgroups.color is unavailable or empty */
    private const DEFAULT_GROUP_COLOR = '#337ab7';

    /**
     * Whether tblclientgroups has a color column (detected once per request).
     */
    private static function clientGroupsHasColorColumn(): bool
    {
        if (self::$clientGroupsHasColor === null) {
            try {
                self::$clientGroupsHasColor = Capsule::schema()->hasColumn('tblclientgroups', 'color');
            } catch (\Throwable $e) {
                self::$clientGroupsHasColor = false;
            }
        }

        return self::$clientGroupsHasColor;
    }

    /**
     * Active client ID in the client area (WHMCS 8+ User model + legacy session).
     */
    public static function currentClientId(): int
    {
        try {
            if (class_exists(\WHMCS\Authentication\CurrentUser::class)) {
                $current = new \WHMCS\Authentication\CurrentUser();
                if (method_exists($current, 'client')) {
                    $client = $current->client();
                    if ($client) {
                        $id = 0;
                        if (method_exists($client, 'getId')) {
                            $id = (int) $client->getId();
                        } elseif (isset($client->id)) {
                            $id = (int) $client->id;
                        }
                        if ($id > 0) {
                            return $id;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fall back to session
        }

        return (int) ($_SESSION['uid'] ?? 0);
    }

    /**
     * Returns the client's group information (read-only from core tables).
     *
     * @param int $clientId
     * @return array|null  Keys: group_id, group_name, group_color
     */
    public function getClientGroup(int $clientId): ?array
    {
        $select = ['tblclients.groupid', 'tblclientgroups.groupname'];
        if (self::clientGroupsHasColorColumn()) {
            $select[] = 'tblclientgroups.color';
        }

        $row = Capsule::table('tblclients')
            ->select($select)
            ->leftJoin('tblclientgroups', 'tblclients.groupid', '=', 'tblclientgroups.id')
            ->where('tblclients.id', $clientId)
            ->first();

        if (!$row || !$row->groupid) {
            return null;
        }

        $color = self::DEFAULT_GROUP_COLOR;
        if (self::clientGroupsHasColorColumn() && !empty($row->color)) {
            $color = $row->color;
        }

        return [
            'group_id'    => (int) $row->groupid,
            'group_name'  => $row->groupname ?? 'Unknown Group',
            'group_color' => $color,
        ];
    }

    /**
     * Returns the assigned CRM data for a client, joined from mapping + profile + admin tables.
     *
     * @param int $clientId
     * @return array|null
     */
    public function getCrmForClient(int $clientId): ?array
    {
        $row = Capsule::table('tblclients')
            ->select(
                'tbladmins.id as admin_id',
                'tbladmins.firstname',
                'tbladmins.lastname',
                'tbladmins.email as admin_email',
                'mod_crm_profiles.display_name',
                'mod_crm_profiles.profile_image',
                'mod_crm_profiles.bio',
                'mod_crm_profiles.contact_email',
                'mod_crm_profiles.phone',
                'mod_crm_profiles.whatsapp',
                'mod_crm_profiles.designation',
                'mod_crm_profiles.extra_fields'
            )
            ->join('mod_crm_group_map', 'tblclients.groupid', '=', 'mod_crm_group_map.group_id')
            ->join('tbladmins', 'mod_crm_group_map.admin_id', '=', 'tbladmins.id')
            ->leftJoin('mod_crm_profiles', 'tbladmins.id', '=', 'mod_crm_profiles.admin_id')
            ->where('tblclients.id', $clientId)
            ->first();

        if (!$row) {
            return null;
        }

        $extraFields = [];
        if (!empty($row->extra_fields)) {
            $decoded = json_decode($row->extra_fields, true);
            $extraFields = is_array($decoded) ? $decoded : [];
        }

        return [
            'admin_id'      => (int) $row->admin_id,
            'display_name'  => $row->display_name ?: trim($row->firstname . ' ' . $row->lastname),
            'profile_image' => $row->profile_image ?? '',
            'bio'           => $row->bio ?? '',
            'contact_email' => $row->contact_email ?: $row->admin_email,
            'phone'         => $row->phone ?? '',
            'whatsapp'      => $row->whatsapp ?? '',
            'designation'   => $row->designation ?? '',
            'extra_fields'  => $extraFields,
        ];
    }

    /**
     * Returns CRM profile data by admin ID.
     *
     * @param int $adminId
     * @return array|null
     */
    public function getCrmProfile(int $adminId): ?array
    {
        $row = Capsule::table('mod_crm_profiles')
            ->where('admin_id', $adminId)
            ->first();

        if (!$row) {
            return null;
        }

        $extraFields = [];
        if (!empty($row->extra_fields)) {
            $decoded = json_decode($row->extra_fields, true);
            $extraFields = is_array($decoded) ? $decoded : [];
        }

        return [
            'id'            => (int) $row->id,
            'admin_id'      => (int) $row->admin_id,
            'display_name'  => $row->display_name ?? '',
            'profile_image' => $row->profile_image ?? '',
            'bio'           => $row->bio ?? '',
            'contact_email' => $row->contact_email ?? '',
            'phone'         => $row->phone ?? '',
            'whatsapp'      => $row->whatsapp ?? '',
            'designation'   => $row->designation ?? '',
            'extra_fields'  => $extraFields,
        ];
    }

    /**
     * Returns all client group → CRM mappings with joined group and admin names.
     *
     * @return array
     */
    public function getAllGroupMappings(): array
    {
        $select = [
            'tblclientgroups.id as group_id',
            'tblclientgroups.groupname',
        ];
        if (self::clientGroupsHasColorColumn()) {
            $select[] = 'tblclientgroups.color';
        }
        $select = array_merge($select, [
            'mod_crm_group_map.admin_id',
            'tbladmins.firstname',
            'tbladmins.lastname',
            'mod_crm_profiles.display_name',
            'mod_crm_profiles.profile_image',
            'mod_crm_profiles.contact_email',
            'mod_crm_profiles.designation',
        ]);

        $rows = Capsule::table('tblclientgroups')
            ->select($select)
            ->leftJoin('mod_crm_group_map', 'tblclientgroups.id', '=', 'mod_crm_group_map.group_id')
            ->leftJoin('tbladmins', 'mod_crm_group_map.admin_id', '=', 'tbladmins.id')
            ->leftJoin('mod_crm_profiles', 'tbladmins.id', '=', 'mod_crm_profiles.admin_id')
            ->orderBy('tblclientgroups.groupname')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $adminName = '';
            if ($row->admin_id) {
                $adminName = $row->display_name ?: trim($row->firstname . ' ' . $row->lastname);
            }
            $groupColor = self::DEFAULT_GROUP_COLOR;
            if (self::clientGroupsHasColorColumn() && !empty($row->color)) {
                $groupColor = $row->color;
            }
            $result[] = [
                'group_id'    => (int) $row->group_id,
                'group_name'  => $row->groupname,
                'group_color' => $groupColor,
                'admin_id'    => $row->admin_id ? (int) $row->admin_id : null,
                'admin_name'  => $adminName,
                'designation' => $row->designation ?? '',
                'contact_email' => $row->contact_email ?? '',
                'profile_image' => $row->profile_image ?? '',
            ];
        }

        return $result;
    }

    /**
     * Returns all admins from tbladmins (read-only) for dropdown population.
     *
     * @return array  Each item: [id, firstname, lastname, fullname, email]
     */
    public function getAllAdmins(): array
    {
        $rows = Capsule::table('tbladmins')
            ->select('id', 'firstname', 'lastname', 'email')
            ->where('disabled', 0)
            ->orderBy('firstname')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'        => (int) $row->id,
                'firstname' => $row->firstname,
                'lastname'  => $row->lastname,
                'fullname'  => trim($row->firstname . ' ' . $row->lastname),
                'email'     => $row->email,
            ];
        }

        return $result;
    }

    /**
     * Returns all CRM profiles joined with admin names.
     *
     * @return array
     */
    public function getAllProfiles(): array
    {
        $rows = Capsule::table('mod_crm_profiles')
            ->select(
                'mod_crm_profiles.*',
                'tbladmins.firstname',
                'tbladmins.lastname',
                'tbladmins.email as admin_email'
            )
            ->leftJoin('tbladmins', 'mod_crm_profiles.admin_id', '=', 'tbladmins.id')
            ->orderBy('mod_crm_profiles.display_name')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $extraFields = [];
            if (!empty($row->extra_fields)) {
                $decoded = json_decode($row->extra_fields, true);
                $extraFields = is_array($decoded) ? $decoded : [];
            }
            $result[] = [
                'id'            => (int) $row->id,
                'admin_id'      => (int) $row->admin_id,
                'display_name'  => $row->display_name ?: trim($row->firstname . ' ' . $row->lastname),
                'profile_image' => $row->profile_image ?? '',
                'bio'           => $row->bio ?? '',
                'contact_email' => $row->contact_email ?: $row->admin_email,
                'phone'         => $row->phone ?? '',
                'whatsapp'      => $row->whatsapp ?? '',
                'designation'   => $row->designation ?? '',
                'extra_fields'  => $extraFields,
                'admin_email'   => $row->admin_email ?? '',
            ];
        }

        return $result;
    }

    /**
     * Resolves the display URL for a profile image.
     * Falls back to Gravatar if no custom image is set.
     *
     * @param string $profileImage  Stored path or URL
     * @param string $email         Used for Gravatar fallback
     * @return string               Absolute URL or data-URI-safe path
     */
    public static function resolveProfileImage(string $profileImage, string $email): string
    {
        if (!empty($profileImage)) {
            if (filter_var($profileImage, FILTER_VALIDATE_URL)) {
                return $profileImage;
            }
            // Relative path stored — return as-is for template to resolve
            return $profileImage;
        }

        // Gravatar fallback
        $hash = md5(strtolower(trim($email)));
        return 'https://www.gravatar.com/avatar/' . $hash . '?s=120&d=mp';
    }

    /**
     * Returns a brief summary of a client's CRM assignment for hook injection.
     * Returns null if no mapping exists.
     *
     * @param int $clientId
     * @return array|null  Keys: group_name, group_color, crm_name, designation
     */
    public function getTicketCrmSummary(int $clientId): ?array
    {
        $group = $this->getClientGroup($clientId);
        if (!$group) {
            return null;
        }

        $crm = $this->getCrmForClient($clientId);

        return [
            'group_name'  => $group['group_name'],
            'group_color' => $group['group_color'],
            'crm_name'    => $crm ? $crm['display_name'] : null,
            'designation' => $crm ? $crm['designation'] : null,
            'admin_id'    => $crm ? $crm['admin_id'] : null,
        ];
    }
}
