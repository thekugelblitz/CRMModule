<?php
/**
 * CRMModule — Hooks
 *
 * Hooks are loaded on every WHMCS page when the module is active.
 * Keeps processing minimal — fetches are only performed on relevant pages.
 *
 * @package    CRMModule
 * @author     HostingSpell LLP
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly.');
}

use WHMCS\Database\Capsule;

require_once __DIR__ . '/lib/CrmHelper.php';

// =============================================================================
// Hook 1: Admin — Support Ticket View
//
// Injects a "Client Group" badge and "Assigned CRM" info block into the
// admin ticket view page. Uses AdminAreaPageViewTicket vars to get client ID.
// =============================================================================
add_hook('AdminAreaPageViewTicket', 1, function (array $vars) {

    $clientId = isset($vars['client']['id']) ? (int) $vars['client']['id'] : 0;

    if (!$clientId) {
        return;
    }

    $helper  = new CRMModule\CrmHelper();
    $summary = $helper->getTicketCrmSummary($clientId);

    if (!$summary) {
        return;
    }

    $groupName   = htmlspecialchars($summary['group_name']);
    $groupColor  = htmlspecialchars($summary['group_color']);
    $crmName     = $summary['crm_name'] ? htmlspecialchars($summary['crm_name']) : null;
    $designation = $summary['designation'] ? htmlspecialchars($summary['designation']) : null;
    $systemUrl   = $GLOBALS['CONFIG']['SystemURL'] ?? '';

    // Build the HTML badge block
    $html  = '<link rel="stylesheet" href="' . htmlspecialchars($systemUrl) . '/modules/addons/crmmodule/assets/css/crmmodule.css">';
    $html .= '<div class="crm-ticket-sidebar-block">';

    // Client Group badge
    $html .= '<div class="crm-ticket-group-row">';
    $html .= '<span class="crm-ticket-label">Client Group</span>';
    $html .= '<span class="crm-group-badge" style="background-color:' . $groupColor . '">'
           . $groupName . '</span>';
    $html .= '</div>';

    // Assigned CRM
    $html .= '<div class="crm-ticket-crm-row">';
    $html .= '<span class="crm-ticket-label">Account Manager</span>';
    if ($crmName) {
        $profileUrl = htmlspecialchars($systemUrl . '/addonmodules.php?module=crmmodule');
        $html .= '<span class="crm-ticket-crm-name">';
        $html .= '<i class="fas fa-user-tie"></i> ' . $crmName;
        if ($designation) {
            $html .= '<small class="crm-ticket-designation"> — ' . $designation . '</small>';
        }
        $html .= '</span>';
    } else {
        $html .= '<span class="text-muted crm-ticket-unassigned"><em>Not assigned</em></span>';
    }
    $html .= '</div>';

    $html .= '</div><!-- /.crm-ticket-sidebar-block -->';

    // Return value from AdminAreaPageViewTicket is prepended to the sidebar HTML
    return $html;
});

// =============================================================================
// Hook 2: Client Area — Homepage Widget
//
// Injects a CRM info widget into the client area homepage output.
// Only runs for logged-in clients that have an assigned CRM.
// =============================================================================
add_hook('ClientAreaHomepage', 1, function (array $vars) {

    $clientId = isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : 0;

    if (!$clientId) {
        return;
    }

    $helper  = new CRMModule\CrmHelper();
    $crm     = $helper->getCrmForClient($clientId);

    if (!$crm) {
        return;
    }

    $systemUrl  = $GLOBALS['CONFIG']['SystemURL'] ?? '';
    $profileUrl = $systemUrl . '/index.php?m=crmmodule';

    $imgUrl = CRMModule\CrmHelper::resolveProfileImage(
        $crm['profile_image'],
        $crm['contact_email']
    );
    if (!empty($crm['profile_image']) && !filter_var($crm['profile_image'], FILTER_VALIDATE_URL)) {
        $imgUrl = $systemUrl . '/' . $crm['profile_image'];
    }

    $name        = htmlspecialchars($crm['display_name']);
    $designation = htmlspecialchars($crm['designation']);
    $bio         = htmlspecialchars(mb_substr($crm['bio'], 0, 90)) . (mb_strlen($crm['bio']) > 90 ? '…' : '');
    $imgSrc      = htmlspecialchars($imgUrl);
    $profileHref = htmlspecialchars($profileUrl);
    $cssUrl      = htmlspecialchars($systemUrl . '/modules/addons/crmmodule/assets/css/crmmodule.css');

    $html  = '<link rel="stylesheet" href="' . $cssUrl . '">';
    $html .= '<div class="crm-widget panel panel-default">';
    $html .= '<div class="panel-heading">';
    $html .= '<h3 class="panel-title"><i class="fas fa-user-tie"></i> Your Account Manager</h3>';
    $html .= '</div>';
    $html .= '<div class="panel-body">';
    $html .= '<div class="crm-widget-inner">';

    $html .= '<div class="crm-widget-avatar-wrap">';
    $html .= '<img src="' . $imgSrc . '" alt="' . $name . '" class="crm-widget-avatar">';
    $html .= '</div>';

    $html .= '<div class="crm-widget-info">';
    $html .= '<strong class="crm-widget-name">' . $name . '</strong>';

    if ($designation) {
        $html .= '<span class="crm-widget-designation">' . $designation . '</span>';
    }

    if ($bio) {
        $html .= '<p class="crm-widget-bio">' . $bio . '</p>';
    }

    $html .= '<a href="' . $profileHref . '" class="btn btn-sm btn-primary crm-widget-btn">';
    $html .= '<i class="fas fa-id-card"></i> View Profile';
    $html .= '</a>';

    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
});

// =============================================================================
// Hook 3: Client Area — Account dropdown (user menu / Lagom profile menu)
//
// Adds a "Client Relationship Manager" link at the top of the Account dropdown
// (below the header with the client name, above "Account Details"). Uses
// ClientAreaSecondaryNavbar per WHMCS docs; parent item is usually "Account".
// See: https://docs.whmcs.com/Client_Area_Navigation_Menus_Cheatsheet
// =============================================================================
add_hook('ClientAreaSecondaryNavbar', 1, function ($secondaryNavbar) {

    $clientId = isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : 0;
    if ($clientId <= 0) {
        return;
    }

    $helper = new CRMModule\CrmHelper();
    $crm    = $helper->getCrmForClient($clientId);
    if (!$crm) {
        return;
    }

    if (!is_object($secondaryNavbar) || !method_exists($secondaryNavbar, 'getChild')) {
        return;
    }

    $accountMenu = null;
    foreach (['Account', 'My Account', 'User'] as $menuName) {
        $accountMenu = $secondaryNavbar->getChild($menuName);
        if ($accountMenu !== null) {
            break;
        }
    }

    if ($accountMenu === null && method_exists($secondaryNavbar, 'getChildren')) {
        $topLevel = $secondaryNavbar->getChildren();
        if (!is_array($topLevel) && !($topLevel instanceof \Traversable)) {
            $topLevel = [];
        }
        foreach ($topLevel as $topItem) {
            if (!is_object($topItem) || !method_exists($topItem, 'getChildren')) {
                continue;
            }
            $subs = $topItem->getChildren();
            if (!is_array($subs) && !($subs instanceof \Traversable)) {
                continue;
            }
            foreach ($subs as $subItem) {
                if (!method_exists($subItem, 'getUri')) {
                    continue;
                }
                $uri = (string) $subItem->getUri();
                if ($uri !== '' && (
                    stripos($uri, 'clientarea.php') !== false && stripos($uri, 'action=details') !== false
                )) {
                    $accountMenu = $topItem;
                    break 2;
                }
            }
        }
    }

    if ($accountMenu === null || !method_exists($accountMenu, 'addChild')) {
        return;
    }

    $name = $crm['display_name'];
    if (function_exists('mb_strlen') && mb_strlen($name) > 42) {
        $name = mb_substr($name, 0, 39) . '…';
    }

    $label = 'Client Relationship Manager: ' . $name;

    $accountMenu->addChild('crm_module_relationship_manager', [
        'label' => $label,
        'uri'   => 'index.php?m=crmmodule',
        'order' => 1,
        'icon'  => 'fas fa-user-tie',
    ]);
});

// =============================================================================
// Hook 4: Client Area — Ticket View (optional: show client group on client side)
//
// Injects a subtle "Your Account Manager" info strip on the client-facing
// ticket view. Only shows if the client has a CRM assigned.
// =============================================================================
add_hook('ClientAreaPageViewTicket', 1, function (array $vars) {

    $clientId = isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : 0;

    if (!$clientId) {
        return;
    }

    $helper = new CRMModule\CrmHelper();
    $crm    = $helper->getCrmForClient($clientId);

    if (!$crm) {
        return;
    }

    $systemUrl  = $GLOBALS['CONFIG']['SystemURL'] ?? '';
    $profileUrl = $systemUrl . '/index.php?m=crmmodule';
    $cssUrl     = htmlspecialchars($systemUrl . '/modules/addons/crmmodule/assets/css/crmmodule.css');

    $name    = htmlspecialchars($crm['display_name']);
    $imgUrl  = CRMModule\CrmHelper::resolveProfileImage($crm['profile_image'], $crm['contact_email']);
    if (!empty($crm['profile_image']) && !filter_var($crm['profile_image'], FILTER_VALIDATE_URL)) {
        $imgUrl = $systemUrl . '/' . $crm['profile_image'];
    }

    $html  = '<link rel="stylesheet" href="' . $cssUrl . '">';
    $html .= '<div class="crm-ticket-client-strip">';
    $html .= '<img src="' . htmlspecialchars($imgUrl) . '" class="crm-strip-avatar" alt="">';
    $html .= '<span class="crm-strip-text">';
    $html .= 'Your account manager is <strong>' . $name . '</strong>.';
    $html .= ' <a href="' . htmlspecialchars($profileUrl) . '">View profile &rarr;</a>';
    $html .= '</span>';
    $html .= '</div>';

    return $html;
});
