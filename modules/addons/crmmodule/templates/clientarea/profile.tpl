{* CRMModule — Client Area CRM Profile Page *}
{* Compatible with: WHMCS Six, Twenty-One, Lagom2 *}

<link rel="stylesheet" href="{$WEB_ROOT}/modules/addons/crmmodule/assets/css/crmmodule.css">

<div class="crm-client-profile-wrap">

    {if $crm}

    <div class="row crm-profile-layout">

        {* ── Left: Avatar + Quick Contact ─────────────────── *}
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

                {* Quick Contact Buttons *}
                <div class="crm-contact-buttons">
                    {if $crm.contact_email}
                    <a href="submitticket.php" class="btn btn-primary btn-block crm-btn-contact">
                        <i class="fas fa-ticket-alt"></i> {$LANG.submitticket|default:'Open a Ticket'}
                    </a>
                    {/if}

                    {if $crm.phone}
                    <a href="tel:{$crm.phone|escape:'html'|regex_replace:'/[^0-9+]/'':''}"
                       class="btn btn-default btn-block crm-btn-phone">
                        <i class="fas fa-phone"></i> {$crm.phone|escape:'html'}
                    </a>
                    {/if}

                    {if $crm.whatsapp}
                    {assign var="waNumber" value=$crm.whatsapp|regex_replace:'/[^0-9]/':''}
                    <a href="https://wa.me/{$waNumber}" target="_blank" rel="noopener"
                       class="btn btn-success btn-block crm-btn-whatsapp">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    {/if}
                </div>

            </div>
        </div>

        {* ── Right: Bio + Details ──────────────────────────── *}
        <div class="col-sm-12 col-md-8 crm-profile-main">

            <div class="panel panel-default crm-client-panel">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <i class="fas fa-user-tie"></i> About Your Account Manager
                    </h4>
                </div>
                <div class="panel-body">

                    {if $crm.bio}
                    <div class="crm-bio-text">
                        {$crm.bio|escape:'html'|nl2br}
                    </div>
                    {else}
                    <p class="text-muted">
                        <em>Your dedicated account manager is here to help with any questions or concerns.</em>
                    </p>
                    {/if}

                    {* Contact Details *}
                    {if $crm.contact_email or $crm.phone or $crm.whatsapp}
                    <hr>
                    <h5><i class="fas fa-address-card"></i> Contact Details</h5>
                    <dl class="crm-contact-details dl-horizontal">
                        {if $crm.contact_email}
                        <dt><i class="fas fa-envelope"></i> Email</dt>
                        <dd><a href="mailto:{$crm.contact_email|escape:'html'}">{$crm.contact_email|escape:'html'}</a></dd>
                        {/if}
                        {if $crm.phone}
                        <dt><i class="fas fa-phone"></i> Phone</dt>
                        <dd>{$crm.phone|escape:'html'}</dd>
                        {/if}
                        {if $crm.whatsapp}
                        <dt><i class="fab fa-whatsapp"></i> WhatsApp</dt>
                        <dd>{$crm.whatsapp|escape:'html'}</dd>
                        {/if}
                    </dl>
                    {/if}

                    {* Extra Fields *}
                    {if $crm.extra_fields}
                    <hr>
                    <h5><i class="fas fa-info-circle"></i> Additional Information</h5>
                    <dl class="crm-contact-details dl-horizontal">
                        {foreach $crm.extra_fields as $label => $value}
                        {if $value}
                        <dt>{$label|escape:'html'}</dt>
                        <dd>{$value|escape:'html'}</dd>
                        {/if}
                        {/foreach}
                    </dl>
                    {/if}

                </div>
            </div>

            {* How to reach out panel *}
            <div class="panel panel-info crm-howto-panel">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-lightbulb"></i> How to Reach Your Account Manager</h4>
                </div>
                <div class="panel-body">
                    <p>The fastest way to reach your account manager is by
                       <a href="submitticket.php"><strong>submitting a support ticket</strong></a>.
                       Your ticket will be routed directly to them.
                    </p>
                </div>
            </div>

        </div>
    </div>

    {else}

    {* No CRM assigned *}
    <div class="crm-no-manager-wrap text-center">
        <div class="crm-no-manager-icon">
            <i class="fas fa-user-slash fa-4x text-muted"></i>
        </div>
        <h3>No Account Manager Assigned</h3>
        <p class="text-muted">
            You don't have a dedicated account manager assigned yet.
            Please <a href="submitticket.php">open a support ticket</a> for assistance.
        </p>
        <a href="clientarea.php" class="btn btn-default">
            <i class="fas fa-home"></i> Back to Dashboard
        </a>
    </div>

    {/if}

</div>
