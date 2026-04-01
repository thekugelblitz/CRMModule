{* CRMModule — Client Area profile (flat templates/profile.tpl for WHMCS addon resolution) *}

<link rel="stylesheet" href="{$crm_css_href|escape:'html'}">

<div class="crm-client-profile-wrap">

{if $crm}

    <div class="row crm-profile-layout">

        <div class="col-sm-12 col-md-4 crm-profile-sidebar">
            <div class="crm-profile-avatar-card">

                <div class="crm-avatar-circle">
                    <img src="{$profileImageUrl|escape:'html'}"
                         alt="{$crm.display_name|escape:'html'}"
                         class="crm-client-avatar">
                </div>

                <h3 class="crm-client-name">{$crm.display_name|escape:'html'}</h3>

                {if $crm.designation}
                <p class="crm-client-designation">{$crm.designation|escape:'html'}</p>
                {/if}

                {if $clientGroup}
                <div class="crm-group-tag" style="border-color:{$clientGroup.group_color|escape:'html'}; color:{$clientGroup.group_color|escape:'html'}">
                    <i class="fas fa-users"></i> {$clientGroup.group_name|escape:'html'}
                </div>
                {/if}

                <div class="crm-contact-buttons">
                    <a href="submitticket.php" class="btn btn-primary btn-block crm-btn-contact">
                        <i class="fas fa-ticket-alt"></i> Contact via support ticket
                    </a>
                    <p class="crm-contact-hint text-muted small">Opens the support ticket form so you can reach your account manager.</p>
                </div>

            </div>
        </div>

        <div class="col-sm-12 col-md-8 crm-profile-main">

            <div class="panel panel-default crm-client-panel">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <i class="fas fa-user-tie"></i> About your account manager
                    </h4>
                </div>
                <div class="panel-body">

                    {if $crm.bio}
                    <div class="crm-bio-text">
                        {$crm.bio|escape:'html'|nl2br}
                    </div>
                    {else}
                    <p class="text-muted">
                        <em>Your account manager is here to help with any questions or concerns.</em>
                    </p>
                    {/if}

                </div>
            </div>

            <div class="panel panel-info crm-howto-panel">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-ticket-alt"></i> Contact your account manager</h4>
                </div>
                <div class="panel-body">
                    <p>Use <a href="submitticket.php"><strong>Contact via support ticket</strong></a> to open the ticket page and send a message.</p>
                </div>
            </div>

        </div>
    </div>

{else}

    <div class="crm-no-manager-wrap text-center">
        <div class="crm-no-manager-icon">
            <i class="fas fa-user-slash fa-4x text-muted"></i>
        </div>
        <h3>No account manager assigned</h3>
        <p class="text-muted">
            You do not have a dedicated account manager assigned yet.
            Please use <a href="submitticket.php">Contact via support ticket</a> to open the ticket page.
        </p>
        <a href="clientarea.php" class="btn btn-default">
            <i class="fas fa-home"></i> Back to dashboard
        </a>
    </div>

{/if}

</div>
