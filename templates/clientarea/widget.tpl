{* CRMModule — Client Area Homepage Widget *}
{* Injected via ClientAreaHomepage hook *}
{* Compatible with: WHMCS Six, Twenty-One, Lagom2 *}

<div class="crm-widget panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <i class="fas fa-user-tie"></i> Your Account Manager
        </h3>
    </div>
    <div class="panel-body">
        <div class="crm-widget-inner">

            <div class="crm-widget-avatar-wrap">
                <img src="{$crmWidgetImage|escape:'html'}"
                     alt="{$crmWidgetName|escape:'html'}"
                     class="crm-widget-avatar">
            </div>

            <div class="crm-widget-info">
                <strong class="crm-widget-name">{$crmWidgetName|escape:'html'}</strong>

                {if $crmWidgetDesignation}
                <span class="crm-widget-designation">{$crmWidgetDesignation|escape:'html'}</span>
                {/if}

                {if $crmWidgetBio}
                <p class="crm-widget-bio">{$crmWidgetBio|truncate:90:'...'|escape:'html'}</p>
                {/if}

                <a href="{$crmWidgetProfileUrl|escape:'html'}" class="btn btn-sm btn-primary crm-widget-btn">
                    <i class="fas fa-id-card"></i> View Profile
                </a>
            </div>

        </div>
    </div>
</div>
