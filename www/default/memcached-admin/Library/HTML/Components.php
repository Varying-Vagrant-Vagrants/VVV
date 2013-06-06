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
 * Manipulation of HTML
 *
 * @author c.mahieux@of2m.fr
 * @since 05/04/2010
 */
class Library_HTML_Components
{
    /**
     * Dump server list in an HTML select
     *
     * @return String
     */
    public static function serverSelect($name, $selected = '', $class = '', $events = '')
    {
        # Loading ini file
        $_ini = Library_Configuration_Loader::singleton();

        # Select Name
        $serverList = '<select id="' . $name . '" ';

        # CSS Class
        $serverList .= ($class != '') ? 'class="' . $class . '"' : '';

        # Javascript Events
        $serverList .= ' ' . $events .'>';

        foreach($_ini->get('servers') as $cluster => $servers)
        {
            # Cluster
            $serverList .= '<option value="' . $cluster . '" ';
            $serverList .= ($selected == $cluster) ? 'selected="selected"' : '';
            $serverList .= '>' . $cluster . ' cluster</option>';

            # Cluster server
            foreach($servers as $name => $servers)
            {
                $serverList .= '<option value="' . $name . '" ';
                $serverList .= ($selected == $name) ? 'selected="selected"' : '';
                $serverList .= '>&nbsp;&nbsp;-&nbsp;' . ((strlen($name) > 38) ? substr($name, 0, 38) . ' [...]' : $name) . '</option>';
            }
        }
        return $serverList . '</select>';
    }

    /**
     * Dump cluster list in an HTML select
     *
     * @return String
     */
    public static function clusterSelect($name, $selected = '', $class = '', $events = '')
    {
        # Loading ini file
        $_ini = Library_Configuration_Loader::singleton();

        # Select Name
        $clusterList = '<select id="' . $name . '" ';

        # CSS Class
        $clusterList .= ($class != '') ? 'class="' . $class . '"' : '';

        # Javascript Events
        $clusterList .= ' ' . $events .'>';

        foreach($_ini->get('servers') as $cluster => $servers)
        {
            # Option value and selected case
            $clusterList .= '<option value="' . $cluster . '" ';
            $clusterList .= ($selected == $cluster) ? 'selected="selected"' : '';
            $clusterList .= '>' . $cluster . ' cluster</option>';
        }
        return $clusterList . '</select>';
    }

    /**
     * Dump server response in proper formatting
     *
     * @param String $hostname Hostname
     * @param String $port Port
     * @param Mixed $data Data (reponse)
     *
     * @return String
     */
    public static function serverResponse($hostname, $port, $data)
    {
        $header = '<span class="red">Server ' . $hostname . ':' . $port . "</span>\r\n";
        $return = '';
        if(is_array($data))
        {
            foreach($data as $string)
            {
                $return .= $string . "\r\n";
            }
            return $header . htmlentities($return, ENT_NOQUOTES | 0, "UTF-8") . "\r\n";
        }
        return $header . $return . $data . "\r\n";
    }

    /**
     * Dump api list un HTML select with select name
     *
     * @param String $iniAPI API Name from ini file
     * @param String $id Select ID
     *
     * @return String
     */
    public static function apiList($iniAPI = '', $id)
    {
        return '<select id="' . $id . '" name="' . $id . '">
                <option value="Server" ' . self::selected('Server', $iniAPI) . '>Server API</option>
                <option value="Memcache" ' . self::selected('Memcache', $iniAPI) . '>Memcache API</option>
                <option value="Memcached" ' . self::selected('Memcached', $iniAPI) . '>Memcached API</option>
                </select>';
    }

    /**
     * Used to see if an option is selected
     *
     * @param String $actual Actual value
     * @param String $selected Selected value
     *
     * @return String
     */
    private static function selected($actual, $selected)
    {
        if($actual == $selected)
        {
            return 'selected="selected"';
        }
    }
}