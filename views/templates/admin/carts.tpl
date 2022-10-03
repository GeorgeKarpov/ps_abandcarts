<style>
    .tab-content {
        border: 1px solid #ddd;
        padding: 10px;
    }
</style>
<div class="panel col-lg-12">
    <div class="panel-heading">
        <h4>{l s='Abandoned Carts' mod='abandcarts'}</h4>
    </div>
    {* {if count($warehouses) > 1}<div style="float: right;">
    <h4 style="display: inline;">{l s='Selected warehouse: ' mod='venipakcarrier'}</h4>&nbsp;&nbsp;
        <select style="width: 200px;display:inline;" class="change-warehouse">
        {foreach from=$warehouses item=warehouse}
            <option value="{$warehouse.id}" {if $warehouse.id == $warehouseId}selected{/if}>{$warehouse.address_title}</option>
        {/foreach}
    </select>
    </div>
    {/if} *}
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-new" data-toggle="tab">{l s='Not yet processed' mod='abandcarts'}</a></li>
        <li><a href="#tab-first" data-toggle="tab">{l s='First reminder sent' mod='abandcarts'}</a></li>
        <li><a href="#tab-second" data-toggle="tab">{l s='Second reminder sent' mod='abandcarts'}</a></li>
    </ul>
    <div class="tab-content">
        <!-- New Carts -->
        <div class="tab-pane active" id="tab-new">
            {if !empty($carts)}

                {* {foreach from=$carts key=id_cart item=cart} *}
                    {* <h4>{l s='Cart id:' mod='abandcarts'} <b>{$id_cart}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Date:' mod='abandcarts'} <b>{$manifest.manifest.manifest_date}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Number of orders:' mod='venipakcarrier'} <b>{$manifest.manifest.orders_count}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Number of packets:' mod='venipakcarrier'} <b>{$manifest.manifest.packets_count}</b></h4> *}
                    <table class="table order">
                        <thead>
                            <tr class="nodrag nodrop">
                                <th width='1%'>
                                    <span class="title_box active"><input type="checkbox" class="check-all"
                                            data-group="carts" /></span>
                                </th>
                                <th width='5%'>
                                    <span class="title_box active">{l s='Cart ID' mod='abandcarts'}</span>
                                </th>
                                <th width='15%'>
                                    <span class="title_box">{l s='Customer' mod='abandcarts'}</span>
                                </th>
                                <th width='15%'>
                                    <span class="title_box">{l s='Added' mod='abandcarts'}</span>
                                </th>
                                {* <th width='5%'>
                            <span class="title_box">{l s='Pack count' mod='abandcarts'}</span>
                        </th> *}
                                <th width='15%'>
                                    <span class="title_box">{l s='Updated' mod='abandcarts'}</span>
                                </th>
                                {* <th width='5%'>
                            <span class="title_box">{l s='C.O.D' mod='abandcarts'}</span>
                        </th>
                        <th width='10%'>
                            <span class="title_box">{l s='Total' mod='abandcarts'}</span>
                        </th>
                        <th width='5%'>
                            <span class="title_box">{l s='Labels' mod='abandcarts'}</span>
                        </th> *}
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$carts key=id_cart item=cart}
                                <tr>
                                    <td><input type="checkbox" class="carts" value="{$id_cart}" /></td>
                                    <td><a href="{$cartLink}&id_cart={$cart.id_cart}" target="_blank">{$cart.id_cart}</td>
                                    <td>{$cart.firstname} {$cart.lastname}</td>
                                    <td>{$cart.date_add|date_format:$config.date}</td>
                                    <td>{$cart.date_upd|date_format:$config.date}</td>
                                    {* <td>{$order.date_upd}</td>
                            <td>{if $order.is_cod == 1}{l s='Yes' mod='venipakcarrier'}{else}{l s='No' mod='venipakcarrier'}{/if}</td>
                            <td>{$order.total_paid_tax_incl}</td>
                            <td><a href="{$printlabelsurl}&order_ids={$order.order_id}" class="btn btn-info btn-xs" target="_blank">{l s='Labels' mod='venipakcarrier'}</a></td> *}
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    <br>

                    {* <form method="POST" action="{$printlabelsurl}" target="_blank" style="display:inline;">
                        <button type="button" value="" class="veni-print-labels btn btn-info btn-xs"
                            data-group="manifest-{$manifest_no}">{l s='Print Labels'}</button>
                    </form>&nbsp;&nbsp;
                    <a href="{$printmanifesturl}&manifest_no={$manifest_no}" class="btn btn-warning btn-xs"
                        target='_blank'>{l s='Print Manifest' mod='venipakcarrier'}</a>
                    &nbsp;&nbsp;
                    <a href="{$closemanifesturl}&manifest_no={$manifest_no}"
                        onclick="return confirm('{l s='Are you sure you want to close manifest?' mod='venipakcarrier'}');"
                        class="btn btn-danger btn-xs">{l s='Close Manifest' mod='venipakcarrier'}</a>
                    <br /> *}
                    <hr />
                {* {/foreach} *}
            {else}
                <div class="text-center">{l s='No carts for period found' mod='abandcarts'}</div>
            {/if}
        </div>
        <!-- First reminder Carts -->
        <div class="tab-pane" id="tab-first">
            {if !empty($cartsfirst)}

                {* {foreach from=$carts key=id_cart item=cart} *}
                    {* <h4>{l s='Cart id:' mod='abandcarts'} <b>{$id_cart}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Date:' mod='abandcarts'} <b>{$manifest.manifest.manifest_date}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Number of orders:' mod='venipakcarrier'} <b>{$manifest.manifest.orders_count}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Number of packets:' mod='venipakcarrier'} <b>{$manifest.manifest.packets_count}</b></h4> *}
                    <table class="table order">
                        <thead>
                            <tr class="nodrag nodrop">
                                <th width='1%'>
                                    <span class="title_box active"><input type="checkbox" class="check-all"
                                            data-group="carts" /></span>
                                </th>
                                <th width='5%'>
                                    <span class="title_box active">{l s='Cart ID' mod='abandcarts'}</span>
                                </th>
                                <th width='15%'>
                                    <span class="title_box">{l s='Customer' mod='abandcarts'}</span>
                                </th>
                                <th width='15%'>
                                    <span class="title_box">{l s='Added' mod='abandcarts'}</span>
                                </th>
                                {* <th width='5%'>
                        <span class="title_box">{l s='Pack count' mod='abandcarts'}</span>
                    </th> *}
                                <th width='15%'>
                                    <span class="title_box">{l s='Updated' mod='abandcarts'}</span>
                                </th>
                                <th width='15%'>
                                    <span class="title_box">{l s='Sent' mod='abandcarts'}</span>
                                </th>
                                {* <th width='5%'>
                        <span class="title_box">{l s='C.O.D' mod='abandcarts'}</span>
                    </th>
                    <th width='10%'>
                        <span class="title_box">{l s='Total' mod='abandcarts'}</span>
                    </th>
                    <th width='5%'>
                        <span class="title_box">{l s='Labels' mod='abandcarts'}</span>
                    </th> *}
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$cartsfirst key=id_cart item=cart}
                                <tr>
                                    <td><input type="checkbox" class="carts" value="{$id_cart}" /></td>
                                    <td><a href="{$cartLink}&id_cart={$cart.id_cart}" target="_blank">{$cart.id_cart}</td>
                                    <td>{$cart.firstname} {$cart.lastname}</td>
                                    <td>{$cart.date_add|date_format:$config.date}</td>
                                    <td>{$cart.date_upd|date_format:$config.date}</td>
                                    <td>{$cart.date_sent|date_format:$config.date}</td>
                                    {* <td>{$order.date_upd}</td>
                        <td>{if $order.is_cod == 1}{l s='Yes' mod='venipakcarrier'}{else}{l s='No' mod='venipakcarrier'}{/if}</td>
                        <td>{$order.total_paid_tax_incl}</td>
                        <td><a href="{$printlabelsurl}&order_ids={$order.order_id}" class="btn btn-info btn-xs" target="_blank">{l s='Labels' mod='venipakcarrier'}</a></td> *}
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    <br>

                    {* <form method="POST" action="{$printlabelsurl}" target="_blank" style="display:inline;">
                    <button type="button" value="" class="veni-print-labels btn btn-info btn-xs"
                        data-group="manifest-{$manifest_no}">{l s='Print Labels'}</button>
                </form>&nbsp;&nbsp;
                <a href="{$printmanifesturl}&manifest_no={$manifest_no}" class="btn btn-warning btn-xs"
                    target='_blank'>{l s='Print Manifest' mod='venipakcarrier'}</a>
                &nbsp;&nbsp;
                <a href="{$closemanifesturl}&manifest_no={$manifest_no}"
                    onclick="return confirm('{l s='Are you sure you want to close manifest?' mod='venipakcarrier'}');"
                    class="btn btn-danger btn-xs">{l s='Close Manifest' mod='venipakcarrier'}</a>
                <br /> *}
                    <hr />
                {* {/foreach} *}
            {else}
                <div class="text-center">{l s='No carts for period found' mod='abandcarts'}</div>
            {/if}
        </div>

        <!-- Second reminder Carts -->
        <div class="tab-pane" id="tab-second">
            {if !empty($cartssecond)}

                {* {foreach from=$carts key=id_cart item=cart} *}
                    {* <h4>{l s='Cart id:' mod='abandcarts'} <b>{$id_cart}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Date:' mod='abandcarts'} <b>{$manifest.manifest.manifest_date}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Number of orders:' mod='venipakcarrier'} <b>{$manifest.manifest.orders_count}</b>&nbsp;&nbsp;&nbsp;&nbsp;{l s='Number of packets:' mod='venipakcarrier'} <b>{$manifest.manifest.packets_count}</b></h4> *}
                    <table class="table order">
                        <thead>
                            <tr class="nodrag nodrop">
                                <th width='1%'>
                                    <span class="title_box active"><input type="checkbox" class="check-all"
                                            data-group="carts" /></span>
                                </th>
                                <th width='5%'>
                                    <span class="title_box active">{l s='Cart ID' mod='abandcarts'}</span>
                                </th>
                                <th width='15%'>
                                    <span class="title_box">{l s='Customer' mod='abandcarts'}</span>
                                </th>
                                <th width='15%'>
                                    <span class="title_box">{l s='Added' mod='abandcarts'}</span>
                                </th>
                                {* <th width='5%'>
                        <span class="title_box">{l s='Pack count' mod='abandcarts'}</span>
                    </th> *}
                                <th width='15%'>
                                    <span class="title_box">{l s='Updated' mod='abandcarts'}</span>
                                </th>
                                <th width='15%'>
                                <span class="title_box">{l s='Sent' mod='abandcarts'}</span>
                            </th>
                                {* <th width='5%'>
                        <span class="title_box">{l s='C.O.D' mod='abandcarts'}</span>
                    </th>
                    <th width='10%'>
                        <span class="title_box">{l s='Total' mod='abandcarts'}</span>
                    </th>
                    <th width='5%'>
                        <span class="title_box">{l s='Labels' mod='abandcarts'}</span>
                    </th> *}
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$cartssecond key=id_cart item=cart}
                                <tr>
                                    <td><input type="checkbox" class="carts" value="{$id_cart}" /></td>
                                    <td><a href="{$cartLink}&id_cart={$cart.id_cart}" target="_blank">{$cart.id_cart}</td>
                                    <td>{$cart.firstname} {$cart.lastname}</td>
                                    <td>{$cart.date_add|date_format:$config.date}</td>
                                    <td>{$cart.date_upd|date_format:$config.date}</td>
                                    <td>{$cart.date_sent|date_format:$config.date}</td>
                                    {* <td>{$order.date_upd}</td>
                        <td>{if $order.is_cod == 1}{l s='Yes' mod='venipakcarrier'}{else}{l s='No' mod='venipakcarrier'}{/if}</td>
                        <td>{$order.total_paid_tax_incl}</td>
                        <td><a href="{$printlabelsurl}&order_ids={$order.order_id}" class="btn btn-info btn-xs" target="_blank">{l s='Labels' mod='venipakcarrier'}</a></td> *}
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    <br>

                    {* <form method="POST" action="{$printlabelsurl}" target="_blank" style="display:inline;">
                    <button type="button" value="" class="veni-print-labels btn btn-info btn-xs"
                        data-group="manifest-{$manifest_no}">{l s='Print Labels'}</button>
                </form>&nbsp;&nbsp;
                <a href="{$printmanifesturl}&manifest_no={$manifest_no}" class="btn btn-warning btn-xs"
                    target='_blank'>{l s='Print Manifest' mod='venipakcarrier'}</a>
                &nbsp;&nbsp;
                <a href="{$closemanifesturl}&manifest_no={$manifest_no}"
                    onclick="return confirm('{l s='Are you sure you want to close manifest?' mod='venipakcarrier'}');"
                    class="btn btn-danger btn-xs">{l s='Close Manifest' mod='venipakcarrier'}</a>
                <br /> *}
                    <hr />
                {* {/foreach} *}
            {else}
                <div class="text-center">{l s='No carts for period found' mod='abandcarts'}</div>
            {/if}
        </div>
        
</div>