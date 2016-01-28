{include 'layout_header.tpl'}
<h1>{t key='pwdrst_heading'}</h1>
{include 'layout_messages.tpl'}
<div class="form">
    <p>
        {t key='pwdrst_message'}
    </p>

    <form method="post" action="{$reset_url}">
        <table>
            <tr>
                <th><label for="openid_url">{t key='pswrst_email'}</label></th>
                <td><input type="text" name="openid_url" value="{$email}" id="openid_url" /></td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="ow_button">
                        <span class="ow_positive">
                            <input type="submit" value="{t key='pwdrst_btn_submit'}" class="ow_positive" />
                        </span>
                    </span>
                    <span class="ow_button">
                                    <span class="ow_positive">
                            <input type="submit" name="cancel" value="{t key='pwdrst_btn_cancel'}" class="ow_positive ow_ic_close" />
                        </span>
                    </span>
                </td>
            </tr>
        </table>
    </form>
</div>
{include 'layout_footer.tpl'}