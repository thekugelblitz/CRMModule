<?php if (!defined('WHMCS')) { die('This file cannot be accessed directly.'); } ?>
<link rel="stylesheet" href="<?php echo $GLOBALS['CONFIG']['SystemURL']; ?>/modules/addons/crmmodule/assets/css/crmmodule.css">

<div class="crm-admin-wrap">

    <!-- Page Header -->
    <div class="crm-page-header">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h2 class="crm-page-title">
                    <i class="fas fa-users-cog"></i> CRM Module
                </h2>
                <p class="crm-page-subtitle">Assign CRM managers to client groups</p>
            </div>
            <div class="col-sm-4 text-right">
                <a href="<?php echo htmlspecialchars($moduleLink); ?>&action=profiles" class="btn btn-default">
                    <i class="fas fa-id-card"></i> Manage CRM Profiles
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
        <li class="active"><a href="<?php echo htmlspecialchars($moduleLink); ?>">
            <i class="fas fa-link"></i> Group Assignments
        </a></li>
        <li><a href="<?php echo htmlspecialchars($moduleLink); ?>&action=profiles">
            <i class="fas fa-id-card"></i> CRM Profiles
        </a></li>
    </ul>

    <div class="tab-content crm-tab-content">

        <!-- Assignments Table -->
        <div class="panel panel-default crm-panel">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fas fa-table"></i> Client Group → CRM Assignments
                </h3>
            </div>
            <div class="panel-body p-0">
                <?php if (empty($mappings)): ?>
                    <div class="crm-empty-state">
                        <i class="fas fa-users fa-3x text-muted"></i>
                        <p class="text-muted mt-2">No client groups found. Create client groups in WHMCS first.</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover crm-table">
                        <thead>
                            <tr>
                                <th>Client Group</th>
                                <th>Assigned CRM</th>
                                <th>Designation</th>
                                <th>Contact</th>
                                <th width="180">Update Assignment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mappings as $map): ?>
                            <tr>
                                <td>
                                    <span class="crm-group-badge" style="background-color: <?php echo htmlspecialchars($map['group_color']); ?>">
                                        <?php echo htmlspecialchars($map['group_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($map['admin_id']): ?>
                                        <div class="crm-admin-cell">
                                            <?php if (!empty($map['profile_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($GLOBALS['CONFIG']['SystemURL'] . '/' . $map['profile_image']); ?>"
                                                     class="crm-avatar-sm" alt="">
                                            <?php else: ?>
                                                <span class="crm-avatar-placeholder-sm"><i class="fas fa-user"></i></span>
                                            <?php endif; ?>
                                            <strong><?php echo htmlspecialchars($map['admin_name']); ?></strong>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted"><em>Unassigned</em></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $map['designation'] ? htmlspecialchars($map['designation']) : '<span class="text-muted">—</span>'; ?>
                                </td>
                                <td>
                                    <?php if ($map['contact_email']): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($map['contact_email']); ?>">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" action="<?php echo htmlspecialchars($moduleLink); ?>&action=save_mapping" class="form-inline crm-inline-form">
                                        <input type="hidden" name="group_id" value="<?php echo (int)$map['group_id']; ?>">
                                        <select name="admin_id" class="form-control input-sm crm-select-admin">
                                            <option value="0">— Remove —</option>
                                            <?php foreach ($admins as $admin): ?>
                                            <option value="<?php echo (int)$admin['id']; ?>"
                                                <?php echo ((int)$map['admin_id'] === (int)$admin['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($admin['fullname']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info card -->
        <div class="panel panel-info crm-info-panel">
            <div class="panel-heading">
                <h4 class="panel-title"><i class="fas fa-info-circle"></i> How It Works</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="crm-how-step">
                            <span class="crm-step-number">1</span>
                            <p>Each client belongs to a <strong>Client Group</strong> in WHMCS.</p>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="crm-how-step">
                            <span class="crm-step-number">2</span>
                            <p>Assign an <strong>Admin</strong> as CRM for each group using the table above.</p>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="crm-how-step">
                            <span class="crm-step-number">3</span>
                            <p>Clients see their CRM in the <strong>Client Area</strong> and on support tickets.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.tab-content -->
</div><!-- /.crm-admin-wrap -->
