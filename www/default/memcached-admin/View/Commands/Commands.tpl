    <div style="float:left;">
        <div class="sub-header corner full-size padding">Console</div>
        <div class="container corner full-size padding">
            <pre id="container" style="font-size:11px;overflow:auto;min-height:180px;max-height:500px;" class="full-size"></pre>
        </div>
        <div class="container corner full-size padding" style="text-align:right;">
            <span style="float:left;">
                <input class="header loading" type="submit" id="loading" value="Waiting for server response ..."/>
            </span>
            <input class="header" type="submit" onclick="javascript:executeClear('container')" value="Clear Console"/>
        </div>

        <div class="sub-header corner full-size padding">Execute predefined <span class="green">Command</span></div>
        <div class="container corner full-size padding">
            <table>
                <tr valign="top">
                    <td class="size-0 padding" style="padding-right:14px;">
                    <form action="commands.php" onsubmit="return false">
                    <div class="line" style="text-align:center;">
                        Execute command on one or all memcached servers<br/>
                        <hr/>
                    </div>
                    <div class="line">
                        <span class="left">Command</span>
                        <span class="right">
                            <select id="request_command" onchange="javascript:changeCommand(this);">
                                <option value="">Choose a Command</option>
                                <option value="get">get</option>
                                <option value="set">set</option>
                                <option value="delete">delete</option>
                                <option value="increment">increment</option>
                                <option value="decrement">decrement</option>
                                <option value="flush_all">flush all</option>
                            </select>
                        </span>
                    </div>
                    <div id="div_key" class="line" style="display:none;">
                        <span class="left">Key</span>
                        <span class="right">
                            <input id="request_key"/>
                        </span>
                    </div>
                    <div id="div_duration" class="line" style="display:none;">
                        <span class="left">Duration</span>
                        <span class="right">
                            <input id="request_duration"/>
                        </span>
                    </div>
                    <div id="div_value" class="line" style="display:none;">
                        <span class="left">Value</span>
                        <span class="right">
                            <input id="request_value"/>
                        </span>
                    </div>
                    <div id="div_data" class="line" style="display:none;">
                        <span class="left">Data</span>
                        <span class="right">
                            <textarea id="request_data" rows="2" cols="2"></textarea>
                        </span>
                    </div>
                    <div id="div_delay" class="line" style="display:none;">
                        <span class="left">Delay</span>
                        <span class="right">
                            <input id="request_delay"/>
                        </span>
                    </div>
                    <div class="line">
                        <span class="left">Server</span>
                        <span class="right">
                            <?php echo Library_HTML_Components::serverSelect('request_server'); ?>
                        </span>
                    </div>
                    <div class="line">
                        <span class="left">API</span>
                        <span class="right">
                            <?php echo Library_HTML_Components::apiList($_ini->get('get_api'), 'request_api'); ?>
                        </span>
                    </div>
                    <div class="line" style="text-align:center;">
                        <hr/>
                        <input class="header" type="submit"
                            onclick="javascript:executeCommand('container');javascript:this.blur();" value="Execute Command"/>
                    </div>
                    </form>
                    </td>
                    <td class="padding" style="border-left:1px solid #ffffff;padding-left:14px;">
					Available commands :
					<ul>
						<li>get : retreive a key value</li>
						<li>set : set a key/value pair</li>
						<li>delete : delete a specific key</li>
						<li>increment : increment a numeric key value</li>
						<li>decrement : decrement a numeric key value</li>
						<li>flush all : flush a Memcached server</li>
					</ul>
                    <br/>
                    </td>
                </tr>
            </table>
        </div>

        <div class="sub-header corner full-size padding">Execute Telnet <span class="green">Commands</span></div>
        <div class="container corner full-size padding">
            <table>
                <tr valign="top">
                    <td class="size-0 padding" style="padding-right:14px;">
                    <div class="line" style="text-align:center;">
                        Execute telnet command on one or all memcached servers<br/>
                        <hr/>
                    </div>
                    <div class="line" style="text-align:center;">
                        <textarea id="request_telnet" rows="2" cols="2"></textarea>
                    </div>
                    <div class="line">
                        <span class="left">Server</span>
                        <span class="right">
                            <?php echo Library_HTML_Components::serverSelect('request_telnet_server'); ?>
                        </span>
                    </div>
                    <div class="line" style="text-align:center;">
                        <hr/>
                        <input class="header" type="submit"
                            onclick="javascript:executeTelnet('container');javascript:this.blur();" value="Execute Telnet Command"/>
                    </div>
                    </td>
                    <td class="padding" style="border-left:1px solid #ffffff;padding-left:14px;">
                        You can use this thing to execute any telnet command to any memcached server
                        <br/>
                        It will connect to the server, execute the command and return it in the console
                        <br/>
                        <br/>
                        <br/>
                        <br/>
                        For more informations about memcached commands, see memcached protocol
                        <a href="http://github.com/memcached/memcached/blob/master/doc/protocol.txt" target="_blank"><span class="green">here</span></a>
                    </td>
                </tr>
            </table>
        </div>

        <div class="sub-header corner full-size padding">Search <span class="green">Key</span></div>
        <div class="container corner full-size padding">
            <table>
                <tr valign="top">
                    <td class="size-0 padding" style="padding-right:14px;">
                    <div class="line" style="text-align:center;">
                        Search for a key on one or all memcached servers<br/>
                        <hr/>
                    </div>
                    <div class="line">
                        <span class="left">Key</span>
                        <span class="right">
                            <input id="search_key" name="search_key"/>
                        </span>
                    </div>
                    <div class="line">
                        <span class="left">Server</span>
                        <span class="right">
                            <?php echo Library_HTML_Components::serverSelect('search_server'); ?>
                        </span>
                    </div>
                    <div class="line" style="text-align:center;">
                        <hr/>
                        <input class="header" type="submit"
                            onclick="javascript:searchKey('container');javascript:this.blur();" value="Search Key"/>
                    </div>
                    </td>
                    <td class="padding" style="border-left:1px solid #ffffff;padding-left:14px;">
                    <span class="red">Warning !</span><br/>This thing is only for debuging issue, do not use it in a production environment as it can lock
                    or impact your memcached servers performances.
                    <br/>Also keep in mind that it does not list all keys. It lists keys up to a certain buffer size (1 or 2MB), and it list key that are expired.
                    <br/>
                    <br/>You can also use a PCRE regular expression
                    </td>
                </tr>
            </table>
        </div>
    </div>