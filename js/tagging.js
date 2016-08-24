// taggingJS v1.3.1
//    2014-04-28

// Copyright (c) 2014 Fabrizio Fallico

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in
//  all copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
(function( $, window, document, undefined ) {

    /**
     * taggingJS Constructor
     *
     * @param obj elem     DOM object of tag box
     * @param obj options  Custom JS options
     */
    var Tagging = function( elem, options ) {
        this.elem    = elem;          // The tag box
        this.$elem   = $( elem );     // jQuerify tag box
        this.options = options;       // JS custom options
        // this.$type_zone = void 0;  // The tag box's input zone
    };

    /**
     * taggingJS Prototype
     */
    Tagging.prototype = {

        // We store here all tags
        tags: [],

        // All special Keys
        keys: {
            // Special keys to add a tag
            add: {
                comma:    188,
                enter:    13,
                spacebar: 32,
            },

            // Special keys to remove last tag
            remove: {
                del: 46,
                backspace: 8,
            }
        },

        // Default options value
        defaults: {
            "case-sensitive": false,                        // True to allow differences between lowercase and uppercase
            "close-char": "&times;",                        // Single Tag close char
            "close-class": "tag-i",                         // Single Tag close class
            "edit-on-delete": true,                         // True to edit tag that has just been removed from tag box
            "forbidden-chars": [ ".", "_", "?" ],           // Array of forbidden characters
            "forbidden-chars-callback": window.alert,       // Function to call when there is a forbidden chars
            "forbidden-chars-text": "Forbidden character:", // Basic text passed to forbidden-chars callback
            "forbidden-words": [],                          // Array of forbidden words
            "forbidden-words-callback": window.alert,       // Function to call when there is a forbidden words
            "forbidden-words-text": "Forbidden word:",      // Basic text passed to forbidden-words callback
            "no-backspace": false,                          // Backspace key remove last tag, true to avoid that
            "no-comma": false,                              // Comma "," key add a new tag, true to avoid that
            "no-del": false,                                // Delete key remove last tag, true to avoid that
            "no-duplicate": true,                           // No duplicate in tag box
            "no-duplicate-callback": window.alert,          // Function to call when there is a duplicate tag
            "no-duplicate-text": "Duplicate tag:",          // Basic text passed to no-duplicate callback
            "no-enter": false,                              // Enter key add a new tag, true to avoid that
            "no-spacebar": false,                           // Spacebar key add a new tag by default, true to avoid that
            "pre-tags-separator": ", ",                     // By default, you must put new tags using a new line
            "tag-box-class": "tagging",                     // Class of the tag box
            "tag-char": "#",                                // Single Tag char
            "tag-class": "tag",                             // Single Tag class
            "tags-input-name": "tag",                       // Name to use as name="" in single tags (by default tag[])
            "type-zone-class": "type-zone",                 // Class of the type-zone
        },

        /**
         * Add a tag
         *
         * @param string            text  Text to add as tag, if null we get the content of tag box type_zone.
         * @return boolean|funtion        true if OK; false if NO; function with some config error.
         */
        add: function( text ) {

            // console.log( 'add' );

            var $tag, l, self,
                index, forbidden_words,
                callback_f, callback_t;

            // Caching this
            self = this;

            // If text is an array, call add on each element
            if ( $.isArray( text ) ) {
                // Adding text present on type_zone as tag on first call
                return $.each( text, function() {
                    self.add( this + "" );
                });
            }

            // Forbidden Words shortcut
            forbidden_words = self.config[ "forbidden-words" ];

            // If no text is passed, take it as text of $type_zone and then empty it
            if ( ! text ) {
                text = self.valInput();
                self.emptyInput();
            }

            // If text is empty too, then go out
            if ( ! text || ! text.length ) {
                return false;
            }

            // If case-sensitive is true, write everything in lowercase
            if ( ! self.config[ "case-sensitive" ] ) {
                text = text.toLowerCase();
            }

            // Checking if text is a Forbidden Word
            l = forbidden_words.length;
            while ( l-- ) {

                // Looking for a forbidden words
                index = text.indexOf( forbidden_words[ l ] );

                // There is a forbidden word
                if ( index >= 0 ) {

                    // Removing all text and ','
                    self.emptyInput();

                    // Renaiming
                    callback_f = self.config[ "forbidden-words-callback" ];
                    callback_t = self.config[ "forbidden-words-text" ];

                    // Remove as a duplicate
                    return self.throwError( callback_f, callback_t, text );
                }
            }

            // If no-duplicate is true, check that the text is not already present
            if ( self.config[ "no-duplicate" ] ) {

                // Looking for each text inside tags
                l = self.tags.length;
                while ( l-- ) {
                    if ( self.tags[ l ].pure_text === text ) {

                        // Removing all text and ','
                        self.emptyInput();

                        // Renaiming
                        callback_f = self.config[ "no-duplicate-callback" ];
                        callback_t = self.config[ "no-duplicate-text" ];

                        // Remove the duplicate
                        return self.throwError( callback_f, callback_t, text );

                    }
                }
            }

            // Creating a new div for the new tag
            $tag = $( document.createElement( "div" ) )
                        .addClass( self.config[ "tag-class" ] )
                        .html( "<span>" + self.config[ "tag-char" ] + "</span> " + text );

            // Creating and Appending hidden input
            $( document.createElement( "input" ) )
                .attr( "type", "hidden" )
                // custom input name
                .attr( "name", self.config[ "tags-input-name" ] + "[]" )
                .val( text )
                .appendTo( $tag );

            // Creating and tag button (with "x" to remove tag)
            $( document.createElement( "a" ) )
                .attr( "role", "button" )
                // adding custom class
                .addClass( self.config[ "close-class" ] )
                // using custom char
                .html( self.config[ "close-char" ] )
                // click addEventListener
                .click(function() {
                    self.remove( $tag );
                })
                // finally append close button to tag element
                .appendTo( $tag );

            // Adding pure_text and position property to $tag
            $tag.pure_text = text;

            // Adding to tags the new tag (as jQuery Object)
            self.tags.push( $tag );

            // Adding tag in the type zone
            self.$type_zone.before( $tag );

            return true;
        },

        /**
         * Add a special keys
         *
         * @param  array       arr  Array like ['type', obj], where 'type' is 'add' or 'remove', obj is { key_name: key_num }
         * @return string|obj       Error message or actually 'type'_key (add_key or remove_key).
         */
        addSpecialKeys: function( arr ) {
            // console.log( 'addSpecialKeys' );

            var self, value, to_add, obj, type;

            self   = this;
            type   = arr[0];
            obj    = arr[1];
            to_add = {};

            // If obj is an array, call addSpecialKeys on each element
            if ( $.isArray( obj ) ) {
                return $.each( obj, function() {
                    self.addSpecialKeys( [ type, this ] );
                });
            }

            // Check if obj is really an object
            // @link http://stackoverflow.com/a/16608045
            if ( ( ! obj ) && ( obj.constructor !== Object ) ) {
                return "Error -> The second argument is not an Object!";
            }

            for ( value in obj ) {
                if ( obj.hasOwnProperty( value ) ) {
                    // @link stackoverflow.com/a/3885844
                    if ( obj[ value ] === +obj[ value ] && obj[ value ] === ( obj[ value ]|0 ) ) {
                        $.extend( to_add, obj );
                    }
                }
            }

            self.keys[ type ] = $.extend( {}, to_add, self.keys[ type ] );

            return self.keys[ type ];
        },

        /**
         * Opposite of init, remove type_zone, all tags and other things.
         *
         * @return boolean
         */
        destroy: function() {
            // console.log( 'destroy' );

            // Removing the type-zone
            this.$elem.find( "." + this.config[ "type-zone-class" ] ).remove();

            // Removing all tags
            this.$elem.find( "." + this.config[ "tag-class" ] ).remove();

            // Destroy tag-box parameters
            this.$elem.data( "tag-box", null );

            // Exit with success
            return true;
        },

        /**
         * Empty tag box's type_zone
         *
         * @return $_obj       The type_zone itself
         */
        emptyInput: function() {
            // console.log( 'emptyInput' );

            this.focusInput();

            return this.valInput( "" );
        },

        /**
         * Trigger focus on tag box's input
         *
         * @return $_obj The tag box's input
         */
        focusInput: function() {
            // console.log( 'focusInput' );

            return this.$type_zone.focus();
        },

        /**
         * Get Data attributes custom options
         *
         * @return object  Tag-box data attributes options
         */
        getDataOptions: function() {

            var key, data_option, data_options;

            // Here we store all data_options
            data_options = {};

            // For each option
            for ( key in this.defaults ) {

                // Getting value
                data_option = this.$elem.data( key );

                // Checking if it is not undefined
                if ( data_option /*!= null*/ ) {

                    // Saving in data_options object
                    data_options[ key ] = data_option;

                }
            }

            return data_options;
        },

        /**
         * Return all special keys inside an object (without distinction)
         *
         * @return obj
         */
        getSpecialKeys: function() {
            return $.extend( {}, this.keys.add, this.keys.remove );
        },

        /**
         * Return all special keys inside an object (with distinction)
         *
         * @return obj
         */
        getSpecialKeysD: function() {
            return this.keys;
        },

        /**
         * Return all tags as string
         *
         * @return array   All tags as member of strings.
         */
        getTags: function() {
            // console.log( 'getTags' );

            var all_txt_tags, i, l;

            l = this.tags.length;
            all_txt_tags = [];

            for ( i = 0; i < l; i += 1 ) {
                all_txt_tags.push( this.tags[ i ].pure_text );
            }

            return all_txt_tags;
        },

        /**
         * Return all tags as object
         *
         * @return array   All tags as member of objects.
         */
        getTagsObj: function() {
            // console.log( 'getTagsObj' );

            return this.tags;
        },

        /**
         * Init method to bootstrap all things
         *
         * @return $_obj   The jQuerify tag box
         */
        init: function() {
            // console.log( 'init' );

            var init_text, self;

            self = this;

            // Getting all data Parameters to fully customize the single tag box selecteds
            self.config = $.extend( {}, self.defaults, self.options, self.getDataOptions() );

            // Pre-existent text
            init_text = self.$elem.text();

            // Empty the original div
            self.$elem.empty();

            // Create the type_zone input using custom class and contenteditable attribute
            self.$type_zone = $( document.createElement( "input" ) )
                             .addClass( self.config[ "type-zone-class" ] )
                             .attr( "contenteditable", true );

            // Adding tagging class and appending the type zone
            self.$elem
                .addClass( self.config[ "tag-box-class" ] )
                .append( self.$type_zone );

            // Keydown event listener on tag box type_zone
            self.$type_zone.keydown(function( e ) {
                var key, index, l, pressed_key, all_keys,
                    forbidden_chars, actual_text,
                    callback_f, callback_t;

                all_keys = self.getSpecialKeys();

                // Forbidden Chars shortcut
                forbidden_chars = self.config[ "forbidden-chars" ];

                // Actual text in the type_zone
                actual_text     = self.valInput();

                // The pressed key
                pressed_key     = e.which;

                // console.log( pressed_key );

                // For in loop to look to Remove Keys
                if ( ! actual_text ) {

                    for ( key in all_keys ) {

                        // Some special key
                        if ( pressed_key === all_keys[ key ] ) {

                            // Enter or comma or spacebar - We cannot add an empty tag
                            if ( self.keys.add[ key ] /*!= null*/ ) {

                                // Prevent Default
                                e.preventDefault();

                                // Exit with 'true'
                                return true;
                            }

                            // Backspace or Del
                            if ( self.keys.remove[ key ] /*!= null*/ ) {

                                // Checking if it enabled
                                if ( ! self.config[ "no-" + key ] ) {

                                    // Prevent Default
                                    e.preventDefault();

                                    return self.remove();

                                }
                            }
                        }
                    }
                } else {

                    // For loop to remove Forbidden Chars from Text
                    l = forbidden_chars.length;
                    while ( l-- ) {

                        // Looking for a forbidden char
                        index = actual_text.indexOf( forbidden_chars[ l ] );

                        // There is a forbidden text
                        if ( index >= 0 ) {

                            // Prevent Default
                            e.preventDefault();

                            // Removing Forbidden Char
                            actual_text = actual_text.replace( forbidden_chars[ l ], "" );

                            // Update type_zone text
                            self.focusInput();
                            self.valInput( actual_text );

                            // Renaiming
                            callback_f = self.config[ "forbidden-chars-callback" ];
                            callback_t = self.config[ "forbidden-chars-text" ];

                            // Remove the duplicate
                            return self.throwError( callback_f, callback_t, forbidden_chars[ l ] );
                        }
                    }

                    // For in to look in Add Keys
                    for ( key in self.keys.add ) {

                        // Enter or comma or spacebar if enabled
                        if ( pressed_key === self.keys.add[ key ] ) {

                            if ( ! self.config[ "no-" + key ] ) {

                                // Prevent Default
                                e.preventDefault();

                                // Adding tag with no text
                                return self.add();
                            }
                        }
                    }
                }

                // Exit with success
                return true;
            });

            // On click, we focus the type_zone
            self.$elem.on( "click", function() {
                self.focusInput();
            });

            // Refresh tag box using refresh public method with a text
            self.refresh( init_text );

            // We don't break the chain, right?
            return self;
        },

        /**
         * Remove and insert all tag
         *
         * @param  string  text String with all tags (if null, simply we call getTags method)
         * @return boolean
         */
        refresh: function( text ) {
            // console.log( 'refresh' );

            var self, separator;

            self = this;
            separator = self.config[ "pre-tags-separator" ];

            text = text || self.getTags().join( separator );

            self.reset();

            // Adding text present on type_zone as tag on first call
            $.each( text.split( separator ), function() {
                self.add( this + "" );
            });

            return true;
        },

        /**
         * Remove last tag in tag box's type_zone or a specified one.
         *
         * @param  string|$_obj         The text of tag to remove or the $_obj of itself.
         * @return string|$_obj         An error if the tag is not found, or the $_obj of removed tag.
         */
        remove: function( $tag ) {
            // console.log( 'remove' );

            var self, text, l;

            self = this;

            // If $tag is an array, call remove on each element
            if ( $.isArray( $tag ) ) {
                // Adding text present on type_zone as tag on first call
                return $.each( $tag, function() {
                    self.remove( this + "" );
                });
            }

            // If $tag is a string, we must find the $_obj of the tag
            if ( typeof $tag === "string" ) {

                // Renaiming
                text = $tag;

                // Retrieving the $_obj of the tag
                $tag = self.$elem.find( "input[value=" + text + "]" ).parent();

                // If nothing is found, return an error
                if ( ! $tag.length ) {
                    return "Error -> Tag not found";
                }
            }

            // Not specified any tags
            if ( ! $tag ) {

                // Retrieving the last
                $tag = self.tags.pop();

            } else {

                // Iterate the tags array and removing the specified tags
                l = self.tags.length;
                while ( l-- ) {
                    // Confront the content of $tag and the tags array
                    if ( self.tags[ l ][0].innerHTML === $tag[0].innerHTML ) {
                        // Removing definitively
                        self.tags.splice( l, 1 );
                    }
                }
            }

            // Getting text if not alredy setted
            text = text || $tag.pure_text;

            // Removing last tag
            $tag.remove();

            // If you want to change the text when a tag is deleted
            if ( self.config[ "edit-on-delete" ] ) {

                // Empting input
                self.emptyInput();

                // Set the new text
                self.valInput( $tag.pure_text );
            }

            return $tag;

        },

        /**
         * Alias of reset
         *
         * @return array  All removed tags
         */
        removeAll: function() {
            // console.log( 'removeAll' );

            return this.reset();
        },

        /**
         * Remove a special keys
         *
         * @param  array  arr  Array like ['type', key_code], where 'type' is 'add' or 'remove', key_code is the key number
         * @return obj         Actually 'type'_key (add_key or remove_key).
         */
        removeSpecialKeys: function( arr ) {
            // console.log( 'removeSpecialKeys' );

            var self, value, to_add, key_code, type;

            self     = this;
            type     = arr[0];
            key_code = arr[1];
            to_add   = {};

            // If key_code is an array, call removeSpecialKeys on each element
            if ( $.isArray( key_code ) ) {
                return $.each( key_code, function() {
                    self.removeSpecialKeys( [ type, this ] );
                });
            }

            // Iterate proper array
            for ( value in self.keys[ type ] ) {
                if ( self.keys[ type ].hasOwnProperty( value ) ) {

                    // If the key_code is equal to the actual key_code
                    if ( self.keys[ type ][ value ] === key_code ) {
                        // We set to undefined the property
                        self.keys[ type ][ value ] = undefined;
                    }
                }
            }

            return self.keys[ type ];
        },

        /**
         * Remove all tags from tag box's type_zone
         *
         * @return array  All removed tags
         */
        reset: function() {
            // console.log( 'reset' );

            var l;

            l = this.tags.length;
            while ( l-- ) {
                this.remove( this.tags[ l ] );
            }

            this.emptyInput();

            return this.tags;

        },

        /**
         * Raise a callback with some text
         *
         * @param  function callback_f Callback function
         * @param  string   callback_t Basic text
         * @param  string   tag_text   Tag text
         * @return function
         */
        throwError: function( callback_f, callback_t, tag_text ) {
            // Calling the callback with t as th
            return callback_f( [ callback_t + " '" + tag_text + "'." ] );
        },

        /**
         * Get or Set the tag box type_zone's value
         *
         * @param  string        text String to put as tag box type_zone's value
         * @return string|$_obj       The value of tag box's type_zone or the type_zone itself
         */
        valInput: function( text ) {
            // console.log( 'valInput' );

            if ( text == null ) {
                return this.$type_zone.val();
            }

            return this.$type_zone.val( text );

        },

    };

    /**
     * Registering taggingJS
     *
     * @param  obj|string arg1 Object with custom options or string with a method
     * @param  string     arg2 Argument to pass to the method
     * @return $_Obj           All tag-box or result from "arg2" public method.
     */
    $.fn.tagging = function( arg1, arg2 ) {
        var results = [];

        this.each(function() {
            var $this, tagging, val;

            $this   = $( this );
            tagging = $this.data( "tag-box" );

            // Initialize a new tags input
            if ( ! tagging ) {

                tagging = new Tagging( this, arg1 );

                $this.data( "tag-box", tagging );

                tagging.init();

                results.push( tagging.$elem );

            } else {

                // Invoke function on existing tags input
                val = tagging[ arg1 ]( arg2 );

                if ( val /*!= null*/ ) {
                    results.push( val );
                }
            }
        });

        if ( typeof arg1 === "string") {
            // Return the results from the invoked function calls
            return ( results.length > 1 ) ? results : results[0];
        }

        return results;
    };

})( window.jQuery, window, document );

// jQuery on Ready example
// (function( $, window, document, undefined ) {
//     $( document ).ready(function() {
//         var t = $( "#tag" ).tagging();
//         t[0].addClass( "form-control" );
//         // console.log( t[0] );
//     });
// })( window.jQuery, window, document );
