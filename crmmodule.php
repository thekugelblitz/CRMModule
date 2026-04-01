<?php
/**
 * CRMModule — WHMCS Addon Module
 *
 * Enhances the WHMCS Client Group system with CRM admin assignment,
 * support ticket visibility, and client-facing CRM profile pages.
 *
 * @package    CRMModule
 * @author     HostingSpell LLP
 * @copyright  Copyright (c) HostingSpell LLP
 * @license    Proprietary
 * @version    1.0.0
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly.');
}

use WHMCS\Database\Capsule as WhmcsCapsule;

require_once __DIR__ . '/lib/CrmHelper.php';
require_once __DIR__ . '/lib/AdminController.php';

/**
 * Module configuration.
 */
function crmmodule_config()
{
    return [
        'name'        => 'CRM Module',
        'description' => 'Assigns CRM (Client Relationship Managers) to Client Groups, '
            . 'surfaces them in support tickets, and provides a client-facing CRM profile page.',
        'author'      => 'HostingSpell LLP',
        'language'    => 'english',
        'version'     => '1.0.0',
        'fields'      => [],
    ];
}

/**
 * Activate: create custom module tables.
 */
function crmmodule_activate()
{
    try {
        WhmcsCapsule::schema()->create('mod_crm_group_map', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('group_id')->unique();
            $table->unsignedInteger('admin_id')->index();
            $table->timestamps();
        });

        WhmcsCapsule::schema()->create('mod_crm_profiles', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('admin_id')->unique()->index();
            $table->string('display_name', 150)->nullable();
            $table->string('profile_image', 255)->nullable();
            $table->text('bio')->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('designation', 100)->nullable();
            $table->text('extra_fields')->nullable()->comment('JSON encoded extra fields');
            $table->timestamps();
        });

        return [
            'status'      => 'success',
            'description' => 'CRMModule activated. You can now assign CRM admins to client groups via Addons > CRM Module.',
        ];
    } catch (\Exception $e) {
        return [
            'status'      => 'error',
            'description' => 'Activation failed: ' . $e->getMessage(),
        ];
    }
}

/**
 * Deactivate: drop custom module tables.
 */
function crmmodule_deactivate()
{
    try {
        WhmcsCapsule::schema()->dropIfExists('mod_crm_group_map');
        WhmcsCapsule::schema()->dropIfExists('mod_crm_profiles');

        return [
            'status'      => 'success',
            'description' => 'CRMModule deactivated and all module data removed.',
        ];
    } catch (\Exception $e) {
        return [
            'status'      => 'error',
            'description' => 'Deactivation failed: ' . $e->getMessage(),
        ];
    }
}

/**
 * Admin area output — routes to AdminController based on ?action=
 *
 * @param array $vars Module variables including modulelink
 */
function crmmodule_output($vars)
{
    $controller = new CRMModule\AdminController($vars);

    $action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
    $action = preg_replace('/[^a-z_]/', '', strtolower($action));

    switch ($action) {
        case 'profiles':
            $controller->renderProfiles();
            break;

        case 'edit_profile':
            $controller->renderEditProfile();
            break;

        case 'save_profile':
            $controller->saveProfile();
            break;

        case 'delete_profile':
            $controller->deleteProfile();
            break;

        case 'save_mapping':
            $controller->saveMapping();
            break;

        case 'delete_mapping':
            $controller->deleteMapping();
            break;

        case 'dashboard':
        default:
            $controller->renderDashboard();
            break;
    }
}

/**
 * Client area output — CRM profile page at index.php?m=crmmodule
 *
 * @param array $vars Module variables
 * @return array
 */
function crmmodule_clientarea($vars)
{
    $systemUrl = rtrim($GLOBALS['CONFIG']['SystemURL'] ?? '', '/');
    $cssHref   = $systemUrl . '/modules/addons/crmmodule/assets/css/crmmodule.css';

    $clientId = CRMModule\CrmHelper::currentClientId();

    if (!$clientId) {
        return [
            'pagetitle'    => 'Your Account Manager',
            'breadcrumb'   => ['index.php?m=crmmodule' => 'Account Manager'],
            'templatefile' => 'profile',
            'requirelogin' => true,
            'vars'         => [
                'crm_css_href' => $cssHref,
            ],
        ];
    }

    $helper  = new CRMModule\CrmHelper();
    $crmData = $helper->getCrmForClient($clientId);
    $group   = $helper->getClientGroup($clientId);

    $profileImageUrl = '';
    if ($crmData) {
        $profileImageUrl = CRMModule\CrmHelper::resolveProfileImage(
            $crmData['profile_image'] ?? '',
            $crmData['contact_email'] ?? ''
        );
        if (!empty($crmData['profile_image']) && !filter_var($crmData['profile_image'], FILTER_VALIDATE_URL)) {
            $profileImageUrl = $systemUrl . '/' . $crmData['profile_image'];
        }
    }

    return [
        'pagetitle'    => 'Your Account Manager',
        'breadcrumb'   => ['index.php?m=crmmodule' => 'Account Manager'],
        'templatefile' => 'profile',
        'requirelogin' => true,
        'vars'         => [
            'crm'             => $crmData,
            'clientGroup'     => $group,
            'profileImageUrl' => $profileImageUrl,
            'moduleLink'      => $vars['modulelink'],
            'crm_css_href'    => $cssHref,
        ],
    ];
}
