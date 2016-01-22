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
    <div class="ow_console_right">
        {foreach $right_menu|default:array() as $menu_entry}
            <a class="ow_console_item" href="{$menu_entry.url}">{$menu_entry.title}</a>
        {/foreach}
    </div>
</div>
<div id="content">