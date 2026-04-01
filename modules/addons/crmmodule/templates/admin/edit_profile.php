<?php if (!defined('WHMCS')) { die('This file cannot be accessed directly.'); } ?>
<link rel="stylesheet" href="<?php echo $GLOBALS['CONFIG']['SystemURL']; ?>/modules/addons/crmmodule/assets/css/crmmodule.css">

<div class="crm-admin-wrap">

    <!-- Page Header -->
    <div class="crm-page-header">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h2 class="crm-page-title">
                    <i class="fas fa-user-edit"></i>
                    <?php echo $profile ? 'Edit CRM Profile' : 'Create CRM Profile'; ?>
                </h2>
                <?php if ($adminInfo): ?>
                <p class="crm-page-subtitle">
                    Admin: <strong><?php echo htmlspecialchars($adminInfo['fullname']); ?></strong>
                    (<?php echo htmlspecialchars($adminInfo['email']); ?>)
                </p>
                <?php endif; ?>
            </div>
            <div class="col-sm-4 text-right">
                <a href="<?php echo htmlspecialchars($moduleLink); ?>&action=profiles" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Back to Profiles
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

    <!-- Admin Selector (if no specific admin pre-selected) -->
    <?php if (!$adminId): ?>
    <div class="panel panel-default crm-panel">
        <div class="panel-body">
            <p>Select an admin to create a profile for:</p>
            <form method="get" action="">
                <?php
                // Preserve existing query params
                parse_str(parse_url($moduleLink, PHP_URL_QUERY), $mlParams);
                foreach ($mlParams as $k => $v): ?>
                    <input type="hidden" name="<?php echo htmlspecialchars($k); ?>" value="<?php echo htmlspecialchars($v); ?>">
                <?php endforeach; ?>
                <input type="hidden" name="action" value="edit_profile">
                <div class="form-inline">
                    <select name="admin_id" class="form-control" required>
                        <option value="">— Select Admin —</option>
                        <?php foreach ($admins as $admin): ?>
                        <option value="<?php echo (int)$admin['id']; ?>">
                            <?php echo htmlspecialchars($admin['fullname']); ?>
                            (<?php echo htmlspecialchars($admin['email']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary ml-2">Continue</button>
                </div>
            </form>
        </div>
    </div>
    <?php return; endif; ?>

    <!-- Profile Form -->
    <form method="post" action="<?php echo htmlspecialchars($moduleLink); ?>&action=save_profile"
          enctype="multipart/form-data" id="crm-profile-form">
        <input type="hidden" name="admin_id" value="<?php echo (int)$adminId; ?>">

        <div class="row">
            <!-- Left column: image + basic info -->
            <div class="col-md-4">
                <div class="panel panel-default crm-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fas fa-camera"></i> Profile Image</h3>
                    </div>
                    <div class="panel-body text-center">

                        <!-- Image Preview -->
                        <?php
                        $previewSrc = '';
                        if ($profile && !empty($profile['profile_image'])) {
                            if (filter_var($profile['profile_image'], FILTER_VALIDATE_URL)) {
                                $previewSrc = $profile['profile_image'];
                            } else {
                                $previewSrc = $GLOBALS['CONFIG']['SystemURL'] . '/' . $profile['profile_image'];
                            }
                        } elseif ($adminInfo) {
                            $previewSrc = \CRMModule\CrmHelper::resolveProfileImage('', $adminInfo['email']);
                        }
                        ?>
                        <div class="crm-preview-wrap">
                            <img id="crm-img-preview"
                                 src="<?php echo htmlspecialchars($previewSrc); ?>"
                                 class="crm-avatar-preview"
                                 alt="Profile Preview">
                        </div>

                        <hr>

                        <div class="form-group">
                            <label class="control-label">Upload Image</label>
                            <input type="file" name="profile_image" id="crm-img-upload"
                                   accept="image/jpeg,image/png,image/gif,image/webp"
                                   class="form-control">
                            <p class="help-block">JPG, PNG, GIF, WebP — Max 2MB</p>
                        </div>

                        <div class="form-group">
                            <label class="control-label">— or — Image URL</label>
                            <input type="url" name="profile_image_url" class="form-control"
                                   placeholder="https://example.com/photo.jpg"
                                   value="<?php echo ($profile && filter_var($profile['profile_image'] ?? '', FILTER_VALIDATE_URL))
                                       ? htmlspecialchars($profile['profile_image']) : ''; ?>">
                            <p class="help-block">Leave blank to use Gravatar</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column: profile fields -->
            <div class="col-md-8">
                <div class="panel panel-default crm-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fas fa-user"></i> Profile Details</h3>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label class="control-label" for="display_name">Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" id="display_name" class="form-control" required
                                   maxlength="150"
                                   placeholder="e.g. John Smith"
                                   value="<?php echo htmlspecialchars($profile['display_name'] ?? ($adminInfo['fullname'] ?? '')); ?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="designation">Designation / Role</label>
                            <input type="text" name="designation" id="designation" class="form-control"
                                   maxlength="100"
                                   placeholder="e.g. Senior Account Manager"
                                   value="<?php echo htmlspecialchars($profile['designation'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="bio">Short Bio</label>
                            <textarea name="bio" id="bio" class="form-control" rows="4"
                                      placeholder="Brief introduction visible to clients..."
                                      ><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label" for="contact_email">Contact Email</label>
                                    <input type="email" name="contact_email" id="contact_email" class="form-control"
                                           maxlength="150"
                                           placeholder="crm@yourcompany.com"
                                           value="<?php echo htmlspecialchars($profile['contact_email'] ?? ($adminInfo['email'] ?? '')); ?>">
                                    <p class="help-block">Shown to clients for ticket contact</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label" for="phone">Phone Number</label>
                                    <input type="text" name="phone" id="phone" class="form-control"
                                           maxlength="50"
                                           placeholder="+1 555 000 0000"
                                           value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="whatsapp">WhatsApp Number</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fab fa-whatsapp"></i></span>
                                <input type="text" name="whatsapp" id="whatsapp" class="form-control"
                                       maxlength="50"
                                       placeholder="+1 555 000 0000"
                                       value="<?php echo htmlspecialchars($profile['whatsapp'] ?? ''); ?>">
                            </div>
                            <p class="help-block">Include country code for click-to-chat links</p>
                        </div>

                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="crm-form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i>
                        <?php echo $profile ? 'Update Profile' : 'Create Profile'; ?>
                    </button>
                    <a href="<?php echo htmlspecialchars($moduleLink); ?>&action=profiles" class="btn btn-default btn-lg">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>

            </div>
        </div>

    </form>

</div><!-- /.crm-admin-wrap -->

<script src="<?php echo $GLOBALS['CONFIG']['SystemURL']; ?>/modules/addons/crmmodule/assets/js/crmmodule.js"></script>
