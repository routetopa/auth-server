{include 'layout_header.tpl'}
    <h1>Manage OpenID accounts</h1>

    <table class="table" style="margin-bottom: 2em;">
        <tr>
            <td>
                {if $allow_signup}
                    <p>User registrations are <b style="color:#0c0;">open</b>: users can sign-up through SPOD.</p>
                {else}
                    <p>User registrations are <b style="color:#c00;">closed</b>: only Administrators can add new users.</p>
                {/if}
            </td>
            <td>
                <form action="{buildURL('admin_users_signup_status')}" method="POST">
                    <span class="ow_button">
                        <span class="ow_positive">
                            {if $allow_signup}
                                <input type="hidden" name="status" value="0"/>
                                <input type="submit" value="Disable user registration" class="ow_positive" />
                            {else}
                                <input type="hidden" name="status" value="1"/>
                                <input type="submit" value="Enable user registration" class="ow_positive" />
                            {/if}
                        </span>
                    </span>
                </form>
            </td>
        </tr>
    </table>


    <form action="./admin_users_create" style="margin-bottom: 2em;">
        <span class="ow_button">
            <span class="ow_positive">
                <input type="submit" name="cancel" value="Add new account" class="ow_positive" />
            </span>
        </span>
    </form>

    <table class="table">
        <thead>
        <tr>
            <th>Role</th>
            <th>UUID</th>
            <th>Email</th>
            <th></th>
            <th></th>
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