<?php if (!defined('WHMCS')) { die('This file cannot be accessed directly.'); } ?>
<link rel="stylesheet" href="<?php echo $GLOBALS['CONFIG']['SystemURL']; ?>/modules/addons/crmmodule/assets/css/crmmodule.css">

<div class="crm-admin-wrap">

    <!-- Page Header -->
    <div class="crm-page-header">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h2 class="crm-page-title">
                    <i class="fas fa-id-card"></i> CRM Profiles
                </h2>
                <p class="crm-page-subtitle">Manage CRM manager profiles visible to clients</p>
            </div>
            <div class="col-sm-4 text-right">
                <a href="<?php echo htmlspecialchars($moduleLink); ?>" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Back to Assignments
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Message -->
    <?php if ($flash): ?>
    <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php echo htmlspecialchars($flash['message']); ?>
    </div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs crm-nav-tabs">
        <li><a href="<?php echo htmlspecialchars($moduleLink); ?>">
            <i class="fas fa-link"></i> Group Assignments
        </a></li>
        <li class="active"><a href="<?php echo htmlspecialchars($moduleLink); ?>&action=profiles">
            <i class="fas fa-id-card"></i> CRM Profiles
        </a></li>
    </ul>

    <div class="tab-content crm-tab-content">

        <!-- Create Profile for Admin -->
        <?php
        $unprofiledAdmins = array_filter($admins, function($a) use ($profiledAdminIds) {
            return !in_array($a['id'], $profiledAdminIds, true);
        });
        ?>
        <?php if (!empty($unprofiledAdmins)): ?>
        <div class="panel panel-default crm-panel">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fas fa-plus-circle"></i> Create New CRM Profile</h3>
            </div>
            <div class="panel-body">
                <p class="text-muted">Select an admin to create a CRM profile for:</p>
                <div class="crm-new-profile-grid">
                    <?php foreach ($unprofiledAdmins as $admin): ?>
                    <a href="<?php echo htmlspecialchars($moduleLink); ?>&action=edit_profile&admin_id=<?php echo (int)$admin['id']; ?>"
                       class="btn btn-sm btn-default crm-new-profile-btn">
                        <i class="fas fa-user-plus"></i>
                        <?php echo htmlspecialchars($admin['fullname']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Existing Profiles -->
        <?php if (empty($profiles)): ?>
        <div class="crm-empty-state">
            <i class="fas fa-id-card fa-3x text-muted"></i>
            <p class="text-muted mt-2">No CRM profiles yet. Create one above.</p>
        </div>
        <?php else: ?>
        <div class="row crm-profile-cards">
            <?php foreach ($profiles as $profile): ?>
            <div class="col-sm-6 col-md-4">
                <div class="panel panel-default crm-profile-card">
                    <div class="panel-body">
                        <div class="crm-profile-card-top">
                            <?php
                            $imgUrl = \CRMModule\CrmHelper::resolveProfileImage(
                                $profile['profile_image'],
                                $profile['admin_email']
                            );
                            if (!empty($profile['profile_image']) && !filter_var($profile['profile_image'], FILTER_VALIDATE_URL)) {
                                $imgUrl = $GLOBALS['CONFIG']['SystemURL'] . '/' . $profile['profile_image'];
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($imgUrl); ?>"
                                 class="crm-avatar-lg" alt="<?php echo htmlspecialchars($profile['display_name']); ?>">
                            <div class="crm-profile-card-info">
                                <strong class="crm-profile-name"><?php echo htmlspecialchars($profile['display_name']); ?></strong>
                                <?php if ($profile['designation']): ?>
                                <span class="crm-profile-designation"><?php echo htmlspecialchars($profile['designation']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($profile['bio']): ?>
                        <p class="crm-profile-bio"><?php echo htmlspecialchars(mb_substr($profile['bio'], 0, 100)) . (mb_strlen($profile['bio']) > 100 ? '...' : ''); ?></p>
                        <?php endif; ?>

                        <div class="crm-profile-contact">
                            <?php if ($profile['contact_email']): ?>
                            <small><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($profile['contact_email']); ?></small><br>
                            <?php endif; ?>
                            <?php if ($profile['phone']): ?>
                            <small><i class="fas fa-phone"></i> <?php echo htmlspecialchars($profile['phone']); ?></small><br>
                            <?php endif; ?>
                            <?php if ($profile['whatsapp']): ?>
                            <small><i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($profile['whatsapp']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="panel-footer crm-profile-card-footer">
                        <a href="<?php echo htmlspecialchars($moduleLink); ?>&action=edit_profile&admin_id=<?php echo (int)$profile['admin_id']; ?>"
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?php echo htmlspecialchars($moduleLink); ?>&action=delete_profile&admin_id=<?php echo (int)$profile['admin_id']; ?>"
                           class="btn btn-sm btn-danger crm-confirm-delete"
                           data-confirm="Delete this CRM profile? This will not remove group assignments.">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div><!-- /.tab-content -->
</div><!-- /.crm-admin-wrap -->

<script src="<?php echo $GLOBALS['CONFIG']['SystemURL']; ?>/modules/addons/crmmodule/assets/js/crmmodule.js"></script>
