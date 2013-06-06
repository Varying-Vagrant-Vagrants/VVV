<?php
# CRLF/EOL
define('EOL', "\r\n");

# Header
echo 'Last update : ' . date('r', time()) . ' (refresh rate : ' . $_ini->get('refresh_rate') . ' sec)' . EOL . EOL;

# Table header
echo '<strong>' . sprintf('%-36s', 'NAME') . sprintf('%10s', 'SIZE') . sprintf('%7s', '%MEM') . sprintf('%8s', 'TIME') .
sprintf('%6s', 'CONN') . sprintf('%7s', '%HIT') . sprintf('%8s', 'REQ/s') . sprintf('%8s', 'GET/s') . sprintf('%8s', 'SET/s') .
sprintf('%8s', 'DEL/s') . sprintf('%8s', 'EVI/s') . sprintf('%11s', 'READ/s') . sprintf('%10s', 'WRITE/s') . '</strong>' . EOL . '<hr>';

# Showing stats for every server
foreach($stats as $server => $data)
{
    # Server name
    echo sprintf('%-36.36s', $server);

    # Checking for stats validity
    if((isset($data['time'], $data['bytes_percent'], $data['get_hits_percent'], $data['query_time'], $data['request_rate'], $data['curr_connections'],
    $data['get_rate'], $data['set_rate'], $data['delete_rate'], $data['eviction_rate'], $data['bytes_read'], $data['bytes_written'])) && ($data['time'] > 0))
    {
        # Total Memory
        echo sprintf('%10s', Library_Data_Analysis::byteResize($data['limit_maxbytes']) . 'b');

        # Memory Occupation / Alert State
        if($data['bytes_percent'] > $_ini->get('memory_alert'))
        {
            echo str_pad('', 7 - strlen($data['bytes_percent']), ' ') . '<span class="red">' . sprintf('%.1f', $data['bytes_percent']) . '</span>';
        }
        else
        {
            echo sprintf('%7.1f', $data['bytes_percent']);
        }

        # Query Time
        echo sprintf('%5.0f', Library_Data_Analysis::valueResize($data['query_time'])) . ' ms';

        # Current connection
        echo sprintf('%6s', $data['curr_connections']);

        # Hit percent (get, delete, cas, incr & decr)
        if($data['get_hits_percent'] < $_ini->get('hit_rate_alert'))
        {
            echo str_pad('', 7 - strlen($data['get_hits_percent']), ' ') . '<span class="red">' . sprintf('%.1f', $data['get_hits_percent']) . '</span>';
        }
        else
        {
            echo sprintf('%7.1f', $data['get_hits_percent']);
        }

        # Request rate
        echo sprintf('%8s', Library_Data_Analysis::valueResize($data['request_rate']));

        # Get rate
        echo sprintf('%8s', Library_Data_Analysis::valueResize($data['get_rate']));

        # Set rate
        echo sprintf('%8s', Library_Data_Analysis::valueResize($data['set_rate']));

        # Delete rate
        echo sprintf('%8s', Library_Data_Analysis::valueResize($data['delete_rate']));

        # Eviction rate
        if($data['eviction_rate'] > $_ini->get('eviction_alert'))
        {
            echo str_pad('', 8 - strlen(Library_Data_Analysis::valueResize($data['eviction_rate'])), ' ') . '<span class="red">' . Library_Data_Analysis::valueResize($data['eviction_rate']) . '</span>';
        }
        else
        {
            echo sprintf('%8s', Library_Data_Analysis::valueResize($data['eviction_rate']));
        }

        # Bytes read
        echo sprintf('%11s', Library_Data_Analysis::byteResize($data['bytes_read'] / $data['time']) . 'b');

        # Bytes written
        echo sprintf('%10s', Library_Data_Analysis::byteResize($data['bytes_written'] / $data['time']) . 'b');
    }
    else
    {
        echo str_pad('', 20, ' ') . '<span class="alert">An error has occured when retreiving or calculating stats</span>';
    }

    # End of Line
    echo EOL . '<hr>';
}