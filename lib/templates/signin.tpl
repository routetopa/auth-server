{include 'layout_header.tpl'}
<h1>{t key='signin_heading'}</h1>
{include 'layout_messages.tpl'}
<div class="form">
    <p>
        {t key='signin_message'}
    </p>

    <form method="post" action="{$signin_url}">
        <table>
            <tr>
                <th><label for="openid_url">{t key='signin_email'}</label></th>
                <td><input type="text" name="openid_url" value="" id="openid_url" /></td>
            </tr>
            <tr>
                <th><label for="password">{t key='signin_pass'}</label></th>
                <td><input type="password" name="password" valueword="" id="password" /></td>
            </tr>
            <tr>
                <th><label for="password_confirm">{t key='signin_password_confirm'}</label></th>
                <td><input type="password" name="password_confirm" value="" id="password_confirm" /></td>
            </tr>
            <tr>
                <td colspan="2">
                    {foreach from=$policies item=p name=policies}
                        <div style="padding: 1em;">
                            <label>
                                <input type="checkbox" name="policies_{$smarty.foreach.policies.iteration}">
                                {t key='signin_policy_checkbox'} <a href="{$p.url}" target="_blank">{$p.title}</a>
                            </label>
                        </div>
                    {/foreach}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="ow_button">
                        <span class="ow_positive">
                            <input type="submit" value="{t key='signin_btn_submit'}" class="ow_positive" />
                        </span>
                    </span>
                    <span class="ow_button">
                                    <span class="ow_positive">
                            <input type="submit" name="cancel" value="{t key='signin_btn_cancel'}" class="ow_positive ow_ic_close" />
                        </span>
                    </span>
                </td>
            </tr>
        </table>
    </form>
</div>
{include 'layout_footer.tpl'}