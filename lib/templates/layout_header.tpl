<html>
<head>
    <meta http-equiv="cache-control" content="no-cache"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <title>{$page_title|default:'ROUTE-TO-PA Authentication Server'}</title>
    <link href="http://spod.routetopa.eu/ow_static/themes/rtpa_matter/base.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,100,100italic,300,300italic,400italic,500,500italic,700,700italic,900,900italic" rel="stylesheet" type="text/css">
    <link href="/openid/openid-server.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="ow_site_panel clearfix">
    <ul class="ow_console_right">
        {foreach $right_menu|default:array() as $menu_entry}
            <li class="ow_console_item"><a  href="{$menu_entry.url}">{if isset($menu_entry.key)}{t key=$menu_entry.key}{else}{$menu_entry.title}{/if}</a></li>
        {/foreach}
            <li class="ow_console_item">
                <a href="#" class="lang lang-{$current_lang}">{$current_lang|upper}</a>
                <ul>
                    {assign var="ret" value=$smarty.server.REQUEST_URI|escape:"url"}
                    {foreach ['en', 'it', 'nl', 'fr'] as $lang}
                    <li><a class="lang lang-{$lang}" href="{buildURL("language?lang=$lang&ret=$ret")}">{$lang|upper}</a></li>
                    {/foreach}
                </ul>
            </li>
    </ul>
</div>
<div id="content">