<?php
/**
 * Copyright 2011 Cyrille Mahieux
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
 * Version container
 *
 * @author cyrille.mahieux@free.fr
 * @since 24/08/2011
 */
class Library_Data_Version
{
    # Version file
    protected static $_file = 'latest';

    # Google Code latest version data file
    protected static $_latest = 'http://phpmemcacheadmin.googlecode.com/files/latest';

    # Time between HTTP check
    protected static $_time = 1296000; # 15 days

    /**
     * Check for the latest version, from local cache or via http
     * Return true if a newer version is available, false otherwise
     *
     * @return Boolean
     */
    public static function check()
    {
        # Loading ini file
        $_ini = Library_Configuration_Loader::singleton();

        # Version definition file path
        $path = rtrim($_ini->get('file_path'), '/') . DIRECTORY_SEPARATOR . self::$_file;

        # Checking if file was modified for less than 15 days ago
        if((is_array($stats = @stat($path))) && (isset($stats['mtime'])) && ($stats['mtime'] > (time() - self::$_time)))
        {
            # Opening file and checking for latest version
            return (version_compare(CURRENT_VERSION, file_get_contents($path)) == -1);
        }
        else
        {
            # Getting last version from Google Code
            if($latest = @file_get_contents(self::$_latest))
            {
                # Saving latest version in file
                file_put_contents($path, $latest);

                # Checking for latest version
                return (version_compare(CURRENT_VERSION, $latest) == -1);
            } else {
                # To avoid error spam
                file_put_contents($path, 'Net unreachable');
                return true;
            }
        }
    }
}