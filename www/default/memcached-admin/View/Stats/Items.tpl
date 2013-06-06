     <div class="sub-header corner full-size padding">Console</div>
        <div class="container corner full-size padding" id="console" style="visibility:visible;display:block;">
            <pre id="container" style="font-size:11px;overflow:auto;min-height:80px;max-height:196px;" class="full-size">
            
            
                                         Click on an item's key below to see it's content here

</pre>
        </div>
        <div class="container corner full-size padding" style="text-align:right;">
            <span style="float:left;">
                <input style="witdh:200px;" class="header loading" type="submit" id="loading" value="Waiting for server response ..."/>
            </span>
            <input class="header" type="submit" onclick="javascript:executeClear('container')" value="Clear Console"/>
            <input class="header" id="hide" type="submit" onclick="javascript:executeHideShow('console', 'hide');javascript:this.blur();" value="Hide Console"/>
        </div>
        
    <div class="sub-header corner full-size padding">
        Items in Slab <?php echo $_GET['slab']; ?>, only showing first <?php echo $_ini->get('max_item_dump'); ?> items
        <span style="float:right;">
            <a href="?server=<?php echo $_GET['server']; ?>&amp;show=slabs">Back to Server Slabs</a>
        </span>
    </div>
    <div class="container corner full-size padding">
<?php
$notFirst = false;

# Items
foreach($items as $key => $data)
{
    # Checking if first item
    if($notFirst) { echo '<hr/>'; }
    ?>       
        <a class="green item" style="" 
        onclick="javascript:executeHideShow('console', 'hide', true);javascript:executeCommand('container', 'request_key=<?php echo $key; ?>&amp;request_api=<?php echo $_ini->get('get_api'); ?>&amp;request_command=get&amp;request_server=<?php echo $_GET['server']; ?>');"><?php echo ((strlen($key) > 70) ? substr($key, 0, 70) . '[..]' : $key); ?></a>

        <span class="right" style="clear:right;">
            <strong>Size</strong> : <?php echo Library_Data_Analysis::byteResize($data[0]); ?>Bytes,
            <strong>Expiration</strong> :
<?php
            # Infinite expiration
            if($data[1] == $infinite)
            {
                echo '&#8734;';
            }
            # Timestamp expiration
            else
            {
                echo Library_Data_Analysis::uptime($data[1] - time());
            } ?>
        </span>
<?php
    # First item done
    $notFirst = true;
} ?>
    </div>