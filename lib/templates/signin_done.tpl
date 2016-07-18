{include 'layout_header.tpl'}
<h1>{t key='signin_heading'}</h1>
{include 'layout_messages.tpl'}
{if 0==count($errors)}
<div class="form">
    <p>
        {t key='signin_message'}
    </p>
</div>
{/if}
{include 'layout_footer.tpl'}