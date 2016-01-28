{include 'layout_header.tpl'}
<h1>{t key='pwdrst_heading'}</h1>
{include 'layout_messages.tpl'}
<form method="post">
    <input type="hidden" name="token" value="{$token}" />
    <input type="hidden" name="email" value="{$email}" />
    <input type="hidden" name="action" value="do_reset" />
    <table>
        <tbody>
        {*
        <tr>
            <th><label>UUID</label></th>
            <td>
                {$user.uuid}
                <input type="hidden" name="uuid" value="{$user.uuid}" />
            </td>
        </tr>
        *}
        <tr>
            <th><label for="email">{t key='pwdrst_form_email'}</label></th>
            <td>{$email}</td>
        </tr><tr>
            <th><label for="password">{t key='pwdrst_form_password'}</label></th>
            <td><input type="password" name="password" id="password" value="" /></td>
        </tr><tr>
            <th><label for="password_confirm">{t key='pwdrst_form_password2'}</label></th>
            <td><input type="password" name="password_confirm" id="password_confirm" value="" /></td>
        </tr>
        <td colspan="2" class="buttons">
                <span class="ow_button">
                    <span class="ow_positive">
                        <input type="submit" value="{t key='pwdrst_form_btn_submit'}" class="ow_positive" />
                    </span>
                </span>
		        <span class="ow_button">
                    <span class="ow_positive">
				        <input type="submit" name="cancel" value="{t key='pwdrst_form_btn_cancel'}" class="ow_positive ow_ic_close" />
			        </span>
                </span>
        </td>
        </tr>
        </tbody>
    </table>

</form>
{include 'layout_footer.tpl'}