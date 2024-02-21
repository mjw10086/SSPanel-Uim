{foreach $list as $item}
    {if $item.is_available}
        <option value="{$item.network} {$item.currency}">
            <div>{$item.currency}</div>
            <div>({$item.network})</div>
        </option>
    {/if}
{/foreach}