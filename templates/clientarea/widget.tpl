{* CRMModule — Client Area Homepage Widget (reference; hook builds HTML inline) *}

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

                <div class="crm-widget-actions">
                    <a href="submitticket.php" class="btn btn-sm btn-primary crm-widget-btn">
                        <i class="fas fa-ticket-alt"></i> Contact via support ticket
                    </a>
                    <a href="{$crmWidgetProfileUrl|escape:'html'}" class="btn btn-sm btn-default crm-widget-btn crm-widget-btn-secondary">
                        <i class="fas fa-id-card"></i> View profile
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
