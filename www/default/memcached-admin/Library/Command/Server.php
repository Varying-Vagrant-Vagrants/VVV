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
 * Sending command to memcache server
 *
 * @author c.mahieux@of2m.fr
 * @since 20/03/2010
 */
class Library_Command_Server implements Library_Command_Interface
{
    private static $_ini;
    private static $_log;

    /**
     * Constructor
     *
     * @param Array $ini Array from ini_parse
     *
     * @return void
     */
    public function __construct()
    {
        # Importing configuration
        self::$_ini = Library_Configuration_Loader::singleton();
    }

    /**
     * Executing a Command on a MemCache Server
     * With the help of http://github.com/memcached/memcached/blob/master/doc/protocol.txt
     * Return the response, or false otherwise
     *
     * @param String $command Command
     * @param String $server Server Hostname
     * @param Integer $port Server Port
     *
     * @return String|Boolean
     */
    public function exec($command, $server, $port)
    {
        # Variables
        $buffer = '';
        $handle = null;

        # Socket Opening
        if(!($handle = @fsockopen($server, $port, $errno, $errstr, self::$_ini->get('connection_timeout'))))
        {
            # Adding error to log
            self::$_log = utf8_encode($errstr);
            Library_Data_Error::add(utf8_encode($errstr));
            return false;
        }

        # Sending Command ...
        fwrite($handle, $command . "\r\n");

        # Getting first line
        $buffer = fgets($handle);

        # Checking if result is valid
        if($this->end($buffer, $command))
        {
            # Closing socket
            fclose($handle);

            # Adding error to log
            self::$_log = $buffer;

            return false;
        }

        # Reading Results
        while((!feof($handle)))
        {
            # Getting line
            $line = fgets($handle);

            $buffer .= $line;

            # Checking for end of MemCache command
            if($this->end($line, $command))
            {
                break;
            }
        }
        # Closing socket
        fclose($handle);

        return $buffer;
    }

    /**
     * Check if response is at the end from memcached server
     * Return true if response end, true otherwise
     *
     * @param String $buffer Buffer received from memcached server
     * @param String $command Command issued to memcached server
     *
     * @return Boolean
     */
    private function end($buffer, $command)
    {
        # incr or decr also return integer
        if((preg_match('/^(incr|decr)/', $command)))
        {
            if(preg_match('/^(END|ERROR|SERVER_ERROR|CLIENT_ERROR|NOT_FOUND|[0-9]*)/', $buffer))
            {
                return true;
            }
        }
        else
        {
            # Checking command response end
            if(preg_match('/^(END|DELETED|OK|ERROR|SERVER_ERROR|CLIENT_ERROR|NOT_FOUND|STORED|RESET|TOUCHED)/', $buffer))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Parse result to make an array
     *
     * @param String $string String to parse
     * @param Boolean $string (optionnal) Parsing stats ?
     *
     * @return Array
     */
    public function parse($string, $stats = true)
    {
        # Variable
        $return = array();

        # Exploding by \r\n
        $lines = preg_split('/\r\n/', $string);

        # Stats
        if($stats)
        {
            # Browsing each line
            foreach($lines as $line)
            {
                $data = preg_split('/ /', $line);
                if(isset($data[2]))
                {
                    $return[$data[1]] = $data[2];
                }
            }
        }
        # Items
        else
        {
            # Browsing each line
            foreach($lines as $line)
            {
                $data = preg_split('/ /', $line);
                if(isset($data[1]))
                {
                    $return[$data[1]] = array(substr($data[2], 1), $data[4]);
                }
            }
        }
        return $return;
    }

    /**
     * Send stats command to server
     * Return the result if successful or false otherwise
     *
     * @param String $server Hostname
     * @param Integer $port Hostname Port
     *
     * @return Array|Boolean
     */
    public function stats($server, $port)
    {
        # Executing command
        if(($return = $this->exec('stats', $server, $port)))
        {
            return $this->parse($return);
        }
        return false;
    }

    /**
     * Send stats settings command to server
     * Return the result if successful or false otherwise
     *
     * @param String $server Hostname
     * @param Integer $port Hostname Port
     *
     * @return Array|Boolean
     */
    public function settings($server, $port)
    {
        # Executing command
        if(($return = $this->exec('stats settings', $server, $port)))
        {
            return $this->parse($return);
        }
        return false;
    }

    /**
     * Send stats items command to server to retrieve slabs stats
     * Return the result if successful or false otherwise
     *
     * @param String $server Hostname
     * @param Integer $port Hostname Port
     *
     * @return Array|Boolean
     */
    public function slabs($server, $port)
    {
        # Initializing
        $slabs = array();

        # Finding uptime
        $stats = $this->stats($server, $port);
        $slabs['uptime'] = $stats['uptime'];
        unset($stats);

        # Executing command : slabs stats
        if(($result = $this->exec('stats slabs', $server, $port)))
        {
            # Parsing result
            $result = $this->parse($result);
            $slabs['active_slabs'] = $result['active_slabs'];
            $slabs['total_malloced'] = $result['total_malloced'];
            unset($result['active_slabs']);
            unset($result['total_malloced']);

            # Indexing by slabs
            foreach($result as $key => $value)
            {
                $key = preg_split('/:/', $key);
                $slabs[$key[0]][$key[1]] = $value;
            }

            # Executing command : items stats
            if(($result = $this->exec('stats items', $server, $port)))
            {
                # Parsing result
                $result = $this->parse($result);

                # Indexing by slabs
                foreach($result as $key => $value)
                {
                    $key = preg_split('/:/', $key);
                    $slabs[$key[1]]['items:' . $key[2]] = $value;
                }

                return $slabs;
            }
        }
        return false;
    }

    /**
     * Send stats cachedump command to server to retrieve slabs items
     * Return the result if successful or false otherwise
     *
     * @param String $server Hostname
     * @param Integer $port Hostname Port
     * @param Interger $slab Slab ID
     *
     * @return Array|Boolean
     */
    public function items($server, $port, $slab)
    {
        # Initializing
        $items = false;

        # Executing command : stats cachedump
        if(($result = $this->exec('stats cachedump ' . $slab . ' ' . self::$_ini->get('max_item_dump'), $server, $port)))
        {
            # Parsing result
            $items = $this->parse($result, false);
        }
        return $items;
    }

    /**
     * Send get command to server to retrieve an item
     * Return the result if successful or false otherwise
     *
     * @param String $server Hostname
     * @param Integer $port Hostname Port
     * @param String $key Key to retrieve
     *
     * @return String
     */
    public function get($server, $port, $key)
    {
        # Executing command : get
        if(($string = $this->exec('get ' . $key, $server, $port)))
        {
            return preg_replace('/^VALUE ' . preg_quote($key, '/') . '[0-9 ]*\r\n/', '', $string);
        }
        return self::$_log;
    }

    /**
     * Set an item
     * Return the result
     *
     * @param String $server Hostname
     * @param Integer $port Hostname Port
     * @param String $key Key to store
     * @param Mixed $data Data to store
     * @param Integer $duration Duration
     *
     * @return String
     */
    function set($server, $port, $key, $data, $duration)
    {
        # Formatting data
        $data = preg_replace('/\r/', '', $data);

        # Executing command : set
        if(($result = $this->exec('set ' . $key . ' 0 ' . $duration . ' ' . strlen($data) . "\r\n" . $data, $server, $port)))
        {
            return $result;
        }
        return self::$_log;
    }

    /**
     * Delete an item
     * Return true if successful, false otherwise
     *
     * @param String $server Hostname
     * @param Integer $port Hostname Port
     * @param String $key Key to delete
     *
     * @return String
     */
    public function delete($server, $port, $key)
    {
        # Executing command : delete
        if(($result = $this->exec('delete ' . $key, $server, $port)))
        {
            return $result;
        }
        return self::$_log;
    }

    /**
     * Increment the key by value
     * Return the result
     *
     * @param String $server Hostname
     * @param Integer $port Hostname Port
     * @param String $key Key to increment
     * @param Integer $value Value to increment
     *
     * @return String
     */
    function increment($server, $port, $key, $value)
    {
        # Executing command : increment
        if(($result = $this->exec('incr ' . $key . ' ' . $value, $server, $port)))
        {
            return $result;
        }
        return self::$_log;
    }

    /**
     * Decrement the key by value
     * Return the result
     *
     * @param String $server Hostname
     * @param Integer $port Hostname Port
     * @param String $key Key to decrement
     * @param Integer $value Value to decrement
     *
     * @return String
     */
    function decrement($server, $port, $key, $value)
    {
        # Executing command : decrement
        if(($result = $this->exec('decr ' . $key . ' ' . $value, $server, $port)))
        {
            return $result;
        }
        return self::$_log;
    }

    /**
     * Flush all items on a server
     * Return the result
     *
     * @param String $server Hostname
     * @param Integer $port Hostname Port
     * @param Integer $delay Delay before flushing server
     *
     * @return String
     */
    function flush_all($server, $port, $delay)
    {
        # Executing command : flush_all
        if(($result = $this->exec('flush_all ' . $delay, $server, $port)))
        {
            return $result;
        }
        return self::$_log;
    }

    /**
     * Search for item
     * Return all the items matching parameters if successful, false otherwise
     *
     * @param String $server Hostname
     * @param Integer $port Hostname Port
     * @param String $key Key to search
     *
     * @return array
     */
    function search($server, $port, $search)
    {
        $slabs = array();
        $items = false;

        # Executing command : slabs stats
        if(($result = $this->exec('stats slabs', $server, $port)))
        {
            # Parsing result
            $result = $this->parse($result);
            unset($result['active_slabs']);
            unset($result['total_malloced']);
            # Indexing by slabs
            foreach($result as $key => $value)
            {
                $key = preg_split('/:/', $key);
                $slabs[$key[0]] = true;
            }
        }

        # Exploring each slabs
        foreach($slabs as $slab => $unused)
        {
            # Executing command : stats cachedump
            if(($result = $this->exec('stats cachedump ' . $slab . ' 0', $server, $port)))
            {
                # Parsing result
                preg_match_all('/^ITEM ((?:.*)' . preg_quote($search, '/') . '(?:.*)) \[(?:.*)\]\r\n/imU', $result, $matchs, PREG_SET_ORDER);

                foreach($matchs as $item)
                {
                    $items[] = $item[1];
                }
            }
            unset($slabs[$slab]);
        }

        if(is_array($items))
        {
            sort($items);
        }

        return $items;
    }

    /**
     * Execute a telnet command on a server
     * Return the result
     *
     * @param String $server Hostname
     * @param Integer $port Hostname Port
     * @param String $command Command to execute
     *
     * @return String
     */
    function telnet($server, $port, $command)
    {
        # Executing command
        if(($result = $this->exec($command, $server, $port)))
        {
            return $result;
        }
        return self::$_log;
    }
}
