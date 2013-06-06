        <div class="size-4" style="float:left;">
        <div class="sub-header corner padding">Slabs <span class="green">Stats</span></div>
        <div class="container corner padding">
            <div class="line">
                <span class="left">Slabs Used</span>
                <?php echo $slabs['active_slabs']; ?>
            </div>
            <div class="line">
                <span class="left">Memory Used</span>
                <?php echo Library_Data_Analysis::byteResize($slabs['total_malloced']); ?>Bytes
            </div>
            <div class="line">
                <span class="left">Wasted</span>
                <?php echo Library_Data_Analysis::byteResize($slabs['total_wasted']); ?>Bytes
            </div>
        </div>
    </div>

    <div class="size-2" style="float:left;padding-left:9px;margin-top:10px;">
        <div class="header corner padding size-3cols" style="text-align:center;">
            <a href="?server=<?php echo $_GET['server']; ?>">See this Server Stats</a>
        </div>
        <br/>
    </div>

    <div class="size-1" style="float:left;padding-left:9px;">
        <div class="container corner" style="padding:9px;">
                For more informations about memcached slabs stats, see memcached protocol
                <a href="http://github.com/memcached/memcached/blob/master/doc/protocol.txt#L470" target="_blank"><span class="green">here</span></a>
        </div>
    </div>

    <table class="full-size" cellspacing="0" cellpadding="0">
        <tr>
<?php
$actualSlab = 0;

# Slabs parsing
foreach($slabs as $id => $slab)
{
    # If Slab is Used
    if(is_numeric($id))
    {
        # Making a new line
        if($actualSlab >= 4)
        {
?>
        </tr>
        <tr>
<?php
            $actualSlab = 0;
        }
?>
        <td <?php if($actualSlab > 0) { echo 'style="padding-left:9px;"'; } ?> valign="top">
            <div class="sub-header corner padding size-5">Slab <?php echo $id; ?> <span class="green">Stats</span>
                <span style="float:right;"><a href="?server=<?php echo $_GET['server']; ?>&amp;show=items&amp;slab=<?php echo $id; ?>">See Slab Items</a></span>
            </div>
            <div class="container corner padding size-5">
                <div class="line">
                    <span class="left slabs">Chunk Size</span>
                    <?php echo Library_Data_Analysis::byteResize($slab['chunk_size']); ?>Bytes
                </div>
                <div class="line">
                    <span class="left slabs">Used Chunk</span>
                    <?php echo Library_Data_Analysis::hitResize($slab['used_chunks']); ?>
                    <span class="right">[<?php echo Library_Data_Analysis::valueResize($slab['used_chunks'] / $slab['total_chunks'] * 100); ?> %]</span>
                </div>
                <div class="line">
                    <span class="left slabs">Total Chunk</span>
                    <?php echo Library_Data_Analysis::hitResize($slab['total_chunks']); ?>
                </div>
                <div class="line">
                    <span class="left slabs">Total Page</span>
                    <?php echo $slab['total_pages']; ?>
                </div>
                <div class="line">
                    <span class="left slabs">Wasted</span>
                    <?php echo Library_Data_Analysis::byteResize($slab['mem_wasted']); ?>Bytes
                </div>
                <div class="line">
                    <span class="left slabs">Hits</span>
                    <?php echo ($slab['request_rate'] > 999) ? Library_Data_Analysis::hitResize($slab['request_rate']) : $slab['request_rate']; ?> Request/sec
                </div>
<?php
if($slab['used_chunks'] > 0)
{ ?>
                <div class="line">
                    <span class="left slabs">Evicted</span>
                    <?php echo (isset($slab['items:evicted'])) ? $slab['items:evicted'] : 'N/A'; ?>
                </div>
<!--
                <div class="line">
                    <span class="left slabs">Evicted Last</span>
                    <?php echo Library_Data_Analysis::uptime($slab['items:evicted_time']); ?>
                </div>
                <div class="line">
                    <span class="left slabs">Out of Memory</span>
                    <?php echo $slab['items:outofmemory']; ?>
                </div>
                <div class="line">
                    <span class="left slabs">Tail Repairs</span>
                    <?php echo $slab['items:tailrepairs']; ?>
                </div>
                -->
<?php }
else
{?>
                <div class="line">
                    <span class="left slabs">Slab is allocated but empty</span>
                </div>
<?php } ?>
            </div>
            </td>
<?php
            $actualSlab++;
    }
?>
<?php
}
for(true; $actualSlab < 4 ; $actualSlab++)
{
    echo '<td style="width:100%;"></td>';
}
?>
        </tr>
    </table>