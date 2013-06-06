<?php
/**
 * Copyright 2010 Cyrille Mahieux
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations
 * under the License.
 *
 * ><)))°> ><)))°> ><)))°> ><)))°> ><)))°> ><)))°> ><)))°> ><)))°> ><)))°>
 *
 * Analysis of memcached command response
 *
 * @author c.mahieux@of2m.fr
 * @since 20/03/2010
 */
class Library_Data_Analysis
{
    /**
     * Merge two arrays of stats from Command_XX::stats()
     *
     * @param Array $array Statistic from Command_XX::stats()
     * @param Array $stats Statistic from Command_XX::stats()
     *
     * @return Array
     */
    public static function merge($array, $stats)
    {
        # Checking input
        if(!is_array($array))
        {
            return $stats;
        }
        elseif(!is_array($stats))
        {
            return $array;
        }

        # Merging Stats
        foreach($stats as $key => $value)
        {
            if(isset($array[$key]) && ($key != 'version') && ($key != 'uptime'))
            {
                $array[$key] += $value;
            }
            else
            {
                $array[$key] = $value;
            }
        }
        return $array;
    }

    /**
     * Diff two arrays of stats from Command_XX::stats()
     *
     * @param Array $array Statistic from Command_XX::stats()
     * @param Array $stats Statistic from Command_XX::stats()
     *
     * @return Array
     */
    public static function diff($array, $stats)
    {
        # Checking input
        if(!is_array($array))
        {
            return $stats;
        }
        elseif(!is_array($stats))
        {
            return $array;
        }

        # Diff for each key
        foreach($stats as $key => $value)
        {
            if(isset($array[$key]))
            {
                $stats[$key] = $value - $array[$key];
            }
        }

        return $stats;
    }

    /**
     * Analyse and return memcache stats command
     *
     * @param Array $stats Statistic from Command_XX::stats()
     *
     * @return Array
     */
    public static function stats($stats)
    {
        if(!is_array($stats) || (count($stats) == 0))
        {
            return false;
        }

        # Command set()
        $stats['set_rate'] = ($stats['cmd_set'] == 0) ? '0.0' : sprintf('%.1f', $stats['cmd_set'] / $stats['uptime'], 1);

        # Command get()
        $stats['get_hits_percent'] = ($stats['cmd_get'] == 0) ? ' - ' : sprintf('%.1f', $stats['get_hits'] / $stats['cmd_get'] * 100, 1);
        $stats['get_misses_percent'] = ($stats['cmd_get'] == 0) ? ' - ' : sprintf('%.1f', $stats['get_misses'] / $stats['cmd_get'] * 100, 1);
        $stats['get_rate'] = ($stats['cmd_get'] == 0) ? '0.0' : sprintf('%.1f', $stats['cmd_get'] / $stats['uptime'], 1);

        # Command delete(), version > 1.2.X
        if(isset($stats['delete_hits'], $stats['delete_misses']))
        {
            $stats['cmd_delete'] = $stats['delete_hits'] + $stats['delete_misses'];
            $stats['delete_hits_percent'] = ($stats['cmd_delete'] == 0) ? ' - ' : sprintf('%.1f', $stats['delete_hits'] / $stats['cmd_delete'] * 100, 1);
            $stats['delete_misses_percent'] = ($stats['cmd_delete'] == 0) ? ' - ' : sprintf('%.1f', $stats['delete_misses'] / $stats['cmd_delete'] * 100, 1);
        }
        else
        {
            $stats['cmd_delete'] = 0;
            $stats['delete_hits_percent'] = ' - ';
            $stats['delete_misses_percent'] = ' - ';
        }
        $stats['delete_rate'] = ($stats['cmd_delete'] == 0) ? '0.0' : sprintf('%.1f', $stats['cmd_delete'] / $stats['uptime'], 1);

        # Command cas(), version > 1.2.X
        if(isset($stats['cas_hits'], $stats['cas_misses'], $stats['cas_badval']))
        {
            $stats['cmd_cas'] = $stats['cas_hits'] + $stats['cas_misses'] + $stats['cas_badval'];
            $stats['cas_hits_percent'] = ($stats['cmd_cas'] == 0) ? ' - ' : sprintf('%.1f', $stats['cas_hits'] / $stats['cmd_cas'] * 100, 1);
            $stats['cas_misses_percent'] = ($stats['cmd_cas'] == 0) ? ' - ' : sprintf('%.1f', $stats['cas_misses'] / $stats['cmd_cas'] * 100, 1);
            $stats['cas_badval_percent'] = ($stats['cmd_cas'] == 0) ? ' - ' : sprintf('%.1f', $stats['cas_badval'] / $stats['cmd_cas'] * 100, 1);
        }
        else
        {
            $stats['cmd_cas'] = 0;
            $stats['cas_hits_percent'] = ' - ';
            $stats['cas_misses_percent'] = ' - ';
            $stats['cas_badval_percent'] = ' - ';
        }
        $stats['cas_rate'] = ($stats['cmd_cas'] == 0) ? '0.0' : sprintf('%.1f', $stats['cmd_cas'] / $stats['uptime'], 1);

        # Command increment(), version > 1.2.X
        if(isset($stats['incr_hits'], $stats['incr_misses']))
        {
            $stats['cmd_incr'] = $stats['incr_hits'] + $stats['incr_misses'];
            $stats['incr_hits_percent'] = ($stats['cmd_incr'] == 0) ? ' - ' : sprintf('%.1f', $stats['incr_hits'] / $stats['cmd_incr'] * 100, 1);
            $stats['incr_misses_percent'] = ($stats['cmd_incr'] == 0) ? ' - ' : sprintf('%.1f', $stats['incr_misses'] / $stats['cmd_incr'] * 100, 1);
        }
        else
        {
            $stats['cmd_incr'] = 0;
            $stats['incr_hits_percent'] = ' - ';
            $stats['incr_misses_percent'] = ' - ';

        }
        $stats['incr_rate'] = ($stats['cmd_incr'] == 0) ? '0.0' : sprintf('%.1f', $stats['cmd_incr'] / $stats['uptime'], 1);

        # Command decrement(), version > 1.2.X
        if(isset($stats['decr_hits'], $stats['decr_misses']))
        {
            $stats['cmd_decr'] = $stats['decr_hits'] + $stats['decr_misses'];
            $stats['decr_hits_percent'] = ($stats['cmd_decr'] == 0) ? ' - ' : sprintf('%.1f', $stats['decr_hits'] / $stats['cmd_decr'] * 100, 1);
            $stats['decr_misses_percent'] = ($stats['cmd_decr'] == 0) ? ' - ' : sprintf('%.1f', $stats['decr_misses'] / $stats['cmd_decr'] * 100, 1);
        }
        else
        {
            $stats['cmd_decr'] = 0;
            $stats['decr_hits_percent'] = ' - ';
            $stats['decr_misses_percent'] = ' - ';
        }
        $stats['decr_rate'] = ($stats['cmd_decr'] == 0) ? '0.0' : sprintf('%.1f', $stats['cmd_decr'] / $stats['uptime'], 1);

        # Command decrement(), version > 1.4.7
        if(isset($stats['touch_hits'], $stats['touch_misses']))
        {
            $stats['cmd_touch'] = $stats['touch_hits'] + $stats['touch_misses'];
            $stats['touch_hits_percent'] = ($stats['cmd_touch'] == 0) ? ' - ' : sprintf('%.1f', $stats['touch_hits'] / $stats['cmd_touch'] * 100, 1);
            $stats['touch_misses_percent'] = ($stats['cmd_touch'] == 0) ? ' - ' : sprintf('%.1f', $stats['touch_misses'] / $stats['cmd_touch'] * 100, 1);
        }
        else
        {
            $stats['cmd_touch'] = 0;
            $stats['touch_hits_percent'] = ' - ';
            $stats['touch_misses_percent'] = ' - ';
        }
        $stats['touch_rate'] = ($stats['cmd_touch'] == 0) ? '0.0' : sprintf('%.1f', $stats['cmd_touch'] / $stats['uptime'], 1);

        # Total hit & miss
        #$stats['cmd_total'] = $stats['cmd_get'] + $stats['cmd_set'] + $stats['cmd_delete'] + $stats['cmd_cas'] + $stats['cmd_incr'] + $stats['cmd_decr'];
        #$stats['hit_percent'] = ($stats['cmd_get'] == 0) ? '0.0' : sprintf('%.1f', ($stats['get_hits']) / ($stats['get_hits'] + $stats['get_misses']) * 100, 1);
        #$stats['miss_percent'] = ($stats['cmd_get'] == 0) ? '0.0' : sprintf('%.1f', ($stats['get_misses']) / ($stats['get_hits'] + $stats['get_misses']) * 100, 1);

        # Command flush_all
        if(isset($stats['cmd_flush']))
        {
            $stats['flush_rate'] = ($stats['cmd_flush'] == 0) ? '0.0' : sprintf('%.1f', $stats['cmd_flush'] / $stats['uptime'], 1);
        }
        else
        {
            $stats['flush_rate'] = '0.0';
        }

        # Cache size
        $stats['bytes_percent'] = ($stats['limit_maxbytes'] == 0) ? '0.0' : sprintf('%.1f', $stats['bytes'] / $stats['limit_maxbytes'] * 100, 1);

        # Request rate
        $stats['request_rate'] = sprintf('%.1f', ($stats['cmd_get'] + $stats['cmd_set'] + $stats['cmd_delete'] + $stats['cmd_cas'] + $stats['cmd_incr'] + $stats['cmd_decr']) / $stats['uptime'], 1);
        $stats['hit_rate'] = sprintf('%.1f', ($stats['get_hits']) / $stats['uptime'], 1);
        $stats['miss_rate'] = sprintf('%.1f', ($stats['get_misses']) / $stats['uptime'], 1);

        # Eviction & reclaimed rate
        $stats['eviction_rate'] = ($stats['evictions'] == 0) ? '0.0' : sprintf('%.1f', $stats['evictions'] / $stats['uptime'], 1);
        $stats['reclaimed_rate'] = (!isset($stats['reclaimed']) || ($stats['reclaimed'] == 0)) ? '0.0' : sprintf('%.1f', $stats['reclaimed'] / $stats['uptime'], 1);

        return $stats;
    }

    /**
     * Analyse and return memcache slabs command
     *
     * @param Array $slabs Statistic from Command_XX::slabs()
     *
     * @return Array
     */
    public static function slabs($slabs)
    {
        # Initializing Used Slabs
        $slabs['used_slabs'] = 0;
        $slabs['total_wasted'] = 0;

        # Request Rate par Slabs
        foreach($slabs as $id => $slab)
        {
            # Check if it's a Slab
            if(is_numeric($id))
            {
                # Check if Slab is used
                if($slab['used_chunks'] > 0)
                {
                    $slabs['used_slabs']++;
                }
                $slabs[$id]['request_rate'] = sprintf('%.1f', ($slab['get_hits'] + $slab['cmd_set'] + $slab['delete_hits'] + $slab['cas_hits'] + $slab['cas_badval'] + $slab['incr_hits'] + $slab['decr_hits']) / $slabs['uptime'], 1);
                $slabs[$id]['mem_wasted'] = (($slab['total_chunks'] * $slab['chunk_size']) < $slab['mem_requested']) ?(($slab['total_chunks'] -  $slab['used_chunks']) * $slab['chunk_size']):(($slab['total_chunks'] * $slab['chunk_size']) - $slab['mem_requested']);
                $slabs['total_wasted'] += $slabs[$id]['mem_wasted'];
            }
        }

        # Cheking server total malloced > 0
        if(!isset($slabs['total_malloced']))
        {
            $slabs['total_malloced'] = 0;
        }

        return $slabs;
    }

    /**
     * Calculate Uptime
     *
     * @param Integer $uptime Uptime timestamp
     *
     * @return String
     */
    public static function uptime($uptime)
    {
        if($uptime > 0)
        {
            $days = floor($uptime/60/60/24);
            $hours = $uptime/60/60%24;
            $mins = $uptime/60%60;
            if(($days + $hours + $mins) == 0)
            {
                return ' less than 1 min';
            }
            return $days . ' days ' . $hours . ' hrs ' . $mins . ' min';
        }
        return ' - ';
    }

    /**
     * Resize a byte value
     *
     * @param Integer $value Value to resize
     *
     * @return String
     */
    public static function byteResize($value)
    {
        # Unit list
        $units = array('', 'K', 'M', 'G', 'T');

        # Resizing
        foreach($units as $unit)
        {
            if($value < 1024)
            {
                break;
            }
            $value /= 1024;
        }
        return sprintf('%.1f %s', $value, $unit);
    }

    /**
     * Resize a value
     *
     * @param Integer $value Value to resize
     *
     * @return String
     */
    public static function valueResize($value)
    {
        # Unit list
        $units = array('', 'K', 'M', 'G', 'T');

        # Resizing
        foreach($units as $unit)
        {
            if($value < 1000)
            {
                break;
            }
            $value /= 1000;
        }
        return sprintf('%.1f%s', $value, $unit);
    }

    /**
     * Resize a hit value
     *
     * @param Integer $value Hit value to resize
     *
     * @return String
     */
    public static function hitResize($value)
    {
        # Unit list
        $units = array('', 'K', 'M', 'G', 'T');

        # Resizing
        foreach($units as $unit)
        {
            if($value < 10000000)
            {
                break;
            }
            $value /= 1000;
        }
        return sprintf('%.0f%s', $value, $unit);
    }
}