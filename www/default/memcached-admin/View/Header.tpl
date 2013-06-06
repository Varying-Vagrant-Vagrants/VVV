<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title>phpMemcachedAdmin <?php echo CURRENT_VERSION; ?></title>
    <link rel="stylesheet" type="text/css" href="Public/Styles/Style.css"/>
    <script type="text/javascript" src="Public/Scripts/Script.js"></script>
</head>
<body>
<div style="margin:0pt auto; width:1000px; clear:both;">
        <div style="font-weight:bold;font-size:1.2em;">phpMemcachedAdmin <sup><?php echo CURRENT_VERSION; ?></sup>
        </div>
        <div class="header corner full-size padding" style="text-align:center;margin-top:5px;">
<?php
# Live Stats view
if(basename($_SERVER['PHP_SELF']) == 'stats.php')
{ ?>
        Live Stats |
<?php
} else { ?>
        <a href="stats.php">See Live Stats </a> |
<?php
}
# Stats view
if(basename($_SERVER['PHP_SELF']) == 'index.php')
{ ?>
        Actually seeing
<?php
} else { ?>
        <a href="index.php">See Stats for </a>
<?php
}
# Server HTML select
echo Library_HTML_Components::serverSelect('server_select', (isset($_GET['server'])) ? $_GET['server'] : '', 'list', 'onchange="changeServer(this)"'); ?>
        |
<?php
# Commands view
if(basename($_SERVER['PHP_SELF']) == 'commands.php')
{ ?>
        Executing Commands on Servers
<?php
} else { ?>
        <a href="commands.php">Execute Commands on Servers</a>
<?php
}?>
        |
<?php
# Configure view
if(basename($_SERVER['PHP_SELF']) == 'configure.php')
{ ?>
        Editing Configuration
<?php
} else { ?>
        <a href="configure.php">Edit Configuration</a>
<?php
} ?>
    </div>

<!--[if IE]>
<div class="header corner full-size padding" style="text-align:center;margin-top:10px;">
Support browsers that contribute to open source, try <a href="http://www.firefox.com" target="_blank">Firefox</a> or <a href="http://www.google.com/chrome" target="_blank">Google Chrome</a>.
</div>
<![endif]-->
