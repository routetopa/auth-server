{include 'layout_header.tpl'}
<h1>Edit OpenID account</h1>

{if count($errors) > 0}
<div class="">
    <ul>
        {foreach $errors as $e}
            <li>{$e}</li>
        {/foreach}
    </ul>
</div>
{/if}

<form method="post">
    <table>
        <tbody>
        {if array_key_exists('uuid', $user)}
        <tr>
            <th><label>UUID</label></th>
            <td>
                {$user.uuid}
                <input type="hidden" name="uuid" value="{$user.uuid}" />
            </td>
        </tr>
        {/if}
        <tr>
            <th><label for="email">E-mail</label></th>
            <td><input type="email" name="email" id="email" value="{$user.email|default:''}" /></td>
        </tr><tr>
            <th><label for="password">Password</label></th>
            <td><input type="password" name="password" id="password" value="" /></td>
        </tr><tr>
            <th><label for="password_confirm">Confirm password</label></th>
            <td><input type="password" name="password_confirm" id="password_confirm" value="" /></td>
        </tr>
            <td colspan="2" class="buttons">
                <span class="ow_button">
                    <span class="ow_positive">
                        <input type="submit" value="Save" class="ow_positive" />
                    </span>
                </span>
		        <span class="ow_button">
                    <span class="ow_positive">
				        <input type="submit" name="cancel" value="Cancel" class="ow_positive ow_ic_close" />
			        </span>
                </span>
            </td>
        </tr>
        </tbody>
    </table>

</form>
{include 'layout_footer.tpl'}