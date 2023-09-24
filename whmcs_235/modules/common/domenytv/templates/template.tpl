{foreach from=$notifications item=notification}
    {if $notification['type'] eq 'error'}
        <div class="errorbox"><strong><span class="title">{WHMCS\Module\Addon\domenytv\Lang::t('notification_error_title')}</span></strong><br>{$notification['message']}</div>
    {elseif $notification['type'] eq 'success'}
           <div class="successbox"><strong><span class="title">{WHMCS\Module\Addon\domenytv\Lang::t('notification_success_title')}!</span></strong><br>{$notification['message']}</div>
    {/if}
{/foreach}
{include file="$template.tpl"}


