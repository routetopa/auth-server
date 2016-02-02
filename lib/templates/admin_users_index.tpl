{include 'layout_header.tpl'}
    <h1>Manage OpenID accounts</h1>

    <form action="./admin_users_create" style="margin-bottom: 2em;">
        <span class="ow_button">
            <span class="ow_positive">
                <input type="submit" name="cancel" value="Add new account" class="ow_positive" />
            </span>
        </span>
    </form>

    <table>
        <thead>
        <tr>
            <td>Role</td>
            <td>UUID</td>
            <td>Email</td>
            <td></td>
            <td></td>
        </tr>
        </thead>
        <tbody>
{while $user=$users->fetchRow()}
        <tr>
            <td>{if $user.is_admin}admin{/if}</td>
            <td><span class="uuid">{$user.uuid}</span></td>
            <td>{$user.email}</td>
            <td><a href="./admin_users_edit/{$user.uuid}">edit</a></td>
            <td><a href="./admin_users_delete/{$user.uuid}">del</></td>
        </tr>
{/while}
        </tbody>
    </table>
{include 'layout_footer.tpl'}