<h2>{\WHMCS\Module\Addon\domenytv\Lang::t('ssl_orders_title')}</h2>

<div class="tablebg">
    <table id="sortabletbl0" class="datatable" width="100%" cellspacing="1" cellpadding="3" border="0">
        <tbody>
            <tr>
                <th>#{WHMCS\Module\Addon\domenytv\Lang::t('id')}</th>
                <th>{WHMCS\Module\Addon\domenytv\Lang::t('order_id')}</th>
                <th>{WHMCS\Module\Addon\domenytv\Lang::t('domain')}</th>
                <th>{WHMCS\Module\Addon\domenytv\Lang::t('ssl_type')}</th>
                <th>{WHMCS\Module\Addon\domenytv\Lang::t('email')}</th>
                <th>{WHMCS\Module\Addon\domenytv\Lang::t('status')}</th>
                <th>{WHMCS\Module\Addon\domenytv\Lang::t('date')}</th>
                <th>{WHMCS\Module\Addon\domenytv\Lang::t('action')}</th>
            </tr>

            {foreach from=$orders item=order}
                <tr class="order-{$order->id}">
                    <td>{$order->id}</td>
                    <td>{$order->order_id}</td>
                    <td>{$order->domain}</td>
                    <td>{$order->product}</td>
                    <td>{$order->email}</td>
                    <td>
                        {if $order->status == 0}
                            <span class="label pending">{WHMCS\Module\Addon\domenytv\Lang::t('ssl_status_pending')}</span>
                        {elseif $order->status == 1}
                            <span class="label active">{WHMCS\Module\Addon\domenytv\Lang::t('ssl_status_done')}</span>
                        {elseif $order->status == 2}
                            <span class="label terminated">{WHMCS\Module\Addon\domenytv\Lang::t('ssl_status_canceled')}</span>
                        {elseif $order->status == 3}
                             <span class="label fraud">{WHMCS\Module\Addon\domenytv\Lang::t('ssl_status_unknown')}</span>
                        {/if}                        
                    </td>
                    <td>{$order->add_date}</td>
                    <td>
                        <a href="addonmodules.php?module=domenytv&action=sslCheckOrderStatus&id={$order->id}"><img src="images/icons/import.png" class="absmiddle check-order" width="16" height="16"></a>
                        <a href="addonmodules.php?module=domenytv&action=sslDeleteOrder&id={$order->id}"><img src="images/icons/delete.png" class="absmiddle confirmation" width="16" height="16"></a>
                    </td>
                </tr>
            {/foreach}
            {if count($order) == 0}
               <tr><td colspan="8" align="center">{WHMCS\Module\Addon\domenytv\Lang::t('ssl_orders_empty_list')}</td></tr>
            {/if}
        </tbody>
    </table>
</div>