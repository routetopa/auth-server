{include 'layout_header.tpl'}
    <h1>Manage OpenID accounts</h1>

    <a href="./admin_users_create">Add new account</a>

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