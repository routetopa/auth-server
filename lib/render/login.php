<?php

require_once "lib/session.php";
require_once "lib/render.php";

define('login_form_pat',
       '<div class="form">
  <p>
	Please log in using your email and password. Your identity URL
	will be in this form:
	<pre>%s</pre>
  </p>

  <form method="post" action="%s">
    <table>
      <tr>
        <th><label for="openid_url">Email:</label></th>
        <td><input type="text" name="openid_url"
                   value="%s" id="openid_url" /></td>
      </tr>
      <tr>
        <th><label for="openid_pass">Password:</label></th>
        <td><input type="password" name="openid_pass"
                   value="" id="openid_pass" /></td>
      </tr>
      <tr>
        <td colspan="2">
		<span class="ow_button">
			<span class="ow_positive">
				<input type="submit" value="Log in" class="ow_positive" />
			</span>
		</span>
		<span class="ow_button">
                        <span class="ow_positive">
				<input type="submit" name="cancel" value="Cancel" class="ow_positive ow_ic_close" />
			</span>
                </span>

        </td>
      </tr>
    </table>
  </form>
</div>
');

define('login_needed_pat',
       'You must be logged in as %s to approve this request.');

function login_render($errors=null, $input=null, $needed=null)
{
    $current_user = getLoggedInUser();
    if ($input === null) {
        $input = $current_user;
    }
    if ($needed) {
        $errors[] = sprintf(login_needed_pat, link_render($needed));
    }

    $esc_input = htmlspecialchars($input, ENT_QUOTES);
    $login_url = buildURL('login', true);
    $body = sprintf(login_form_pat, idURL('USERNAME'), $login_url, $esc_input);
    if ($errors) {
        $body = loginError_render($errors) . $body;
    }
    return page_render($body, $current_user, 'Log In', null, true);
}

function loginError_render($errors)
{
    $text = '';
    foreach ($errors as $error) {
        $text .= sprintf("<li>%s</li>\n", $error);
    }
    return sprintf("<ul class=\"error\">\n%s</ul>\n", $text);
}
?>
