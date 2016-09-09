{include 'layout_header.tpl'}

<h1>{t key='index_heading'}</h1>

{include 'layout_messages.tpl'}

{if $user}
    {t key='index_logged_info' 1='aaa'}

    <div class="padded">
    {foreach from=$login_urls key=title item=$url}
        <span class="ow_button">
            <span class="ow_positive">
                <a href="{$url}" class="">{t key='index_goto'} {$title}</a>
            </span>
        </span>
    {/foreach}
    </div>

{else}
    {t key='index_not_logged_info'}
{/if}

<hr>

<div class="padded">
    {t key='index_description'}
</div>

{include 'layout_footer.tpl'}