<script type="text/javascript">
    var timeout = <?php echo $refresh_rate * 1000; ?>;
    var page = 'stats.php?request_command=live_stats&cluster=<?php echo $cluster; ?>';
    setTimeout("ajax(page,'stats')", <?php echo (5 + $refresh_rate - $_ini->get('refresh_rate')) * 1000; ?>);
</script>

<div style="float:left;">
    <div class="sub-header corner full-size padding">Live <span class="green">Stats</span></div>
    <?php
    # Refresh rate increased
    if($refresh_rate > $_ini->get('refresh_rate'))
    { ?>
        <div class="container corner" style="padding:9px;">
            Connections errors were discovered, to prevent any problem, refresh rate was increased by
            <?php echo sprintf('%.1f', $refresh_rate - $_ini->get('refresh_rate')); ?> seconds.
        </div>
    <?php
    } ?>

    <div class="full-size padding">
        <br/>
        <span class="live">Actually looking at <?php echo Library_HTML_Components::clusterSelect('cluster_select', (isset($_GET['cluster'])) ? $_GET['cluster'] : '', 'live', 'onchange="changeCluster(this);"'); ?> stats</span>
        <pre id="stats" class="live">

        Loading live stats, please wait ~<?php echo sprintf('%.0f', 5 + $refresh_rate - $_ini->get('refresh_rate')); ?> seconds ...
        </pre>
    </div>
    <div class="container corner full-size padding">
        <div class="line">
            <span class="left setting">SIZE</span>
            Total cache size on this server
        </div>
        <div class="line">
            <span class="left setting">%MEM</span>
            Percentage of total cache size used on this server
        </div>
        <div class="line">
            <span class="left setting">%HIT</span>
            Global hit percent on this server : get_hits / (get_hits + get_misses)
        </div>
        <div class="line">
            <span class="left setting">TIME</span>
            Time taken to connect to the server and proceed the request, high value can indicate a latency or server problem
        </div>
        <div class="line">
            <span class="left setting">REQ/s</span>
            Total request per second (get, set, delete, incr, ...) issued to this server
        </div>
        <div class="line">
            <span class="left setting">CONN</span>
            Current connections, monitor that this number doesn't come too close to the server max connection setting
        </div>
        <div class="line">
            <span class="left setting">GET/s, SET/s, DEL/s</span>
            Get, set or delete commands per second issued to this server
        </div>
        <div class="line">
            <span class="left setting">EVI/s</span>
            Number of times an item which had an explicit expire time set had to be evicted before it expired
        </div>
        <div class="line">
            <span class="left setting">READ/s</span>
             Total number of bytes read by this server from network
        </div>
        <div class="line">
            <span class="left setting">WRITE/s</span>
            Total number of bytes sent by this server to network
        </div>
    </div>
</div>
