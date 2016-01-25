{if isset($errors) && count($errors) > 0}
    <div class="errors">
        <ul>
            {foreach $errors as $e}
                <li>{t key=$e}</li>
            {/foreach}
        </ul>
    </div>
{/if}
{flasht key='message'}