{include 'layout_header.tpl'}
<h1>{t key='login_heading'}</h1>
{include 'layout_messages.tpl'}
<div class="form">
    <p>
        {t key='login_message'}
        <pre>{$id_url}</pre>
    </p>

    <form method="post" action="{$login_url}">
        <table>
            <tr>
                <th><label for="openid_url">{t key='login_email'}</label></th>
                <td><input type="text" name="openid_url" value="{$email}" id="openid_url" /></td>
            </tr>
            <tr>
                <th><label for="openid_pass">{t key='login_password'}</label></th>
                <td><input type="password" name="openid_pass" value="" id="openid_pass" /></td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="ow_button">
                        <span class="ow_positive">
                            <input type="submit" value="{t key='login_btn_login'}" class="ow_positive" />
                        </span>
                    </span>
                    <span class="ow_button">
                                    <span class="ow_positive">
                            <input type="submit" name="cancel" value="{t key='login_btn_cancel'}" class="ow_positive ow_ic_close" />
                        </span>
                    </span>
                    <a class="password_reset" href="{$password_reset_url}">I forgot my password</a>
                </td>
            </tr>
        </table>
    </form>
</div>
{include 'layout_footer.tpl'}