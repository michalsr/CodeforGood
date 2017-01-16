<?php
/*
Plugin Name: Sucuri Security
Plugin URI: http://wordpress.sucuri.net/
Description: WordPress Audit log and integrity checking plugin (by Sucuri Security). This plugin will monitor your Wordpress installation for the latest attacks and provide visibility to what is happening inside (auditing). It will also keep track of system events, like logins, logouts, failed logins, new users, file changes, etc. When an attack is detected it will also block the IP address from accessing the site. 
Author: sucuri.net
Network: true
Version: 2.6
Author URI: http://sucuri.net
*/


/* Sucuri Security WordPress Plugin
 * Copyright (C) 2010-2013 Sucuri Security - http://sucuri.net
 * Released under the GPL - see LICENSE file for details.
 */


/* No direct access. */
if(!function_exists('add_action'))
{
    exit(0);
}


/* Constants. */
define("SUCURIWPSTARTED", TRUE);
define('SUCURI','sucurisec' );
define('SUCURI_VERSION','2.6');
define('SUCURI_DEBUG', FALSE);
define('SUCURI_IMG',plugin_dir_url( __FILE__ ).'images/' );



/* Requires files. */
//add_action('add_attachment', 'sucuri_add_attachment', 50); 
add_action('create_category', 'sucuri_create_category', 50); 
add_action('delete_post', 'sucuri_delete_post', 50); 
add_action('private_to_published', 'sucuri_private_to_published', 50); 
add_action('publish_page', 'sucuri_publish_post', 50); 
add_action('publish_post', 'sucuri_publish_post', 50); 
add_action('publish_phone', 'sucuri_publish_post', 50); 
add_action('xmlrpc_publish_post', 'sucuri_publish_post', 50); 
add_action('add_link', 'sucuri_add_link', 50); 
add_action('switch_theme', 'sucuri_switch_theme', 50); 
add_action('delete_user', 'sucuri_delete_user', 50); 
add_action('retrieve_password', 'sucuri_retrieve_password', 50); 
add_action('user_register', 'sucuri_user_register', 50); 
add_action('wp_login', 'sucuri_wp_login', 50); 
add_action('wp_login_failed', 'sucuri_wp_login_failed', 50); 
add_action('login_form_resetpass', 'sucuri_reset_password', 50); 

/* sucuri_dir_filepath:
 * Returns the system filepath to the relevant user uploads
 * directory for this site. Multisite capable.
 */
function sucuri_dir_filepath($path = '')
{
    $wp_dir_array = wp_upload_dir();
    $wp_dir_array['basedir'] = untrailingslashit($wp_dir_array['basedir']);
    return($wp_dir_array['basedir']."/sucuri/$path");
}



/* sucuri_debug_log:
 * Prints a debug message inside debug_log.php.
 */
function sucuri_debug_log($msg)
{
    if(!is_file(sucuri_dir_filepath('debug_log.php' )))
    {
        @file_put_contents(sucuri_dir_filepath('debug_log.php'), "<?php exit(0);\n");
    }
    @file_put_contents(sucuri_dir_filepath('debug_log.php'), date('Y-m-d h:i:s ')."$msg\n", FILE_APPEND);
}



/* sucuri_create_files:
 * Creates the internal files / directories used by the plugin.
 * Returns 0 on error and 1 on success.
 */
function sucuri_create_files() 
{
    $wp_dir_array = wp_upload_dir();
    if(!is_dir($wp_dir_array['basedir']))
    {
        @mkdir($wp_dir_array['basedir']);
        if(!is_dir($wp_dir_array['basedir']))
        {
            return(0);
        }
    }
    @mkdir(sucuri_dir_filepath());
    @touch(sucuri_dir_filepath('index.html'));
    @mkdir(sucuri_dir_filepath('blocks'));
    @mkdir(sucuri_dir_filepath('whitelist'));
    @touch(sucuri_dir_filepath('blocks/index.html'));
    @touch(sucuri_dir_filepath('whitelist/index.html'));
    @file_put_contents(sucuri_dir_filepath('.htaccess'), "\ndeny from all\n");

    if(!is_file(sucuri_dir_filepath('blocks/blocks.php')))
    {
        @file_put_contents(sucuri_dir_filepath('blocks/blocks.php'), "\n<?php exit(0);\n\n", FILE_APPEND);
    }
    return(1);
}




/* sucuri_deactivate_plugin:
 * Function to run when plugin is deactivated.
 */
function sucuri_deactivate_plugin() 
{
   //wp_clear_scheduled_hook('sucuri_hourly_scan');
}



/* sucuri_verify_run:
 * Checks last time we ran to avoid running twice (or too often).
 */
function sucuri_verify_run($runtime)
{
    if(!is_readable(sucuri_dir_filepath('.firstrun')))
    {
        if(!is_dir(sucuri_dir_filepath()))
        {
            sucuri_create_files();
        }
        touch(sucuri_dir_filepath('.firstrun'));
        return(true);
    }

    if(!is_readable(sucuri_dir_filepath(".runtime")))
    {
        if(!is_dir(sucuri_dir_filepath()))
        {
            sucuri_create_files();
        }
        touch(sucuri_dir_filepath(".runtime"));
        return(true);
    }

    $lastchanged = filemtime(sucuri_dir_filepath(".runtime"));
    if($lastchanged > (time(0) - $runtime))
    {
        return(FALSE);
    }

    touch(sucuri_dir_filepath(".$type"));
    return(true);
}



/* sucuri_scanallfiles:
 * Loops through the filesystem and generates the md5 checksum of the files.
 */
function sucuri_scanallfiles($dir, $output)
{
    $dh = opendir($dir);
    if(!$dh)
    {
        return(0);
    }
    $printdir = $dir;

    while(($myfile = readdir($dh)) !== false)
    {
        if($myfile == "." || $myfile == "..")
        {
            continue;
        }

        /* Ignoring backup files from our clean ups. */
        if(strpos($myfile, "_sucuribackup.") !== FALSE)
        {
            continue;
        }
        if(is_dir($dir."/".$myfile))
        {
            if(($myfile == "cache") && (strpos($dir, "wp-content") !== FALSE))
            {
                continue;
            }
            if(($myfile == "w3tc") && (strpos($dir."/".$myfile, "wp-content/w3tc") !== FALSE))
            {
                continue;
            }
            if($myfile == "sucuri")
            {
                continue;
            }
            $output = sucuri_scanallfiles($dir."/".$myfile, $output);
        }
       
        else if((strpos($myfile, ".php") !== FALSE) ||
                (strpos($myfile, ".htm") !== FALSE) ||
                (strcmp($myfile, ".htaccess") == 0) ||
                (strcmp($myfile, "php.ini") == 0) ||
                (strpos($myfile, ".js") !== FALSE))
        {
            $output = $output.md5_file($dir."/".$myfile).filesize($dir."/".$myfile)." ".$dir."/".$myfile."\n";
        }

    }
    closedir($dh);
    return($output);
}



/* sucuri_do_scan:
 * Executes the integrity / version checks.
 */
function sucuri_do_scan() 
{
    global $wp_version;
    $sucuri_wp_key = get_option('sucuri_wp_key');
    

    sucuri_debug_log("Running wp-cron (doscan).");
    if($sucuri_wp_key === FALSE)
    {
        return(NULL);
    }

    sucuri_debug_log("Running wp-cron (valid key)");
    if(sucuri_verify_run(5000) === FALSE)
    {
        return(NULL);
    }

    sucuri_debug_log("Running wp-cron (verify run passed)");
    $output = "";
    sucuri_send_log($sucuri_wp_key, "WordPress version: $wp_version", 1);
    if(strcmp($wp_version, "3.4") >= 0)
    {
        $output = sucuri_scanallfiles(ABSPATH, $output);
        sucuri_send_hashes($sucuri_wp_key, $output);
    }
    sucuri_debug_log("Running wp-cron (finished)");
}



/* sucuri_send_hashes:
 * Sends the hashes to sucuri backend. 
 */
function sucuri_send_hashes($sucuri_wp_key, $final_message)
{
    $response = wp_remote_post("https://wordpress.sucuri.net/", array(
	'method' => 'POST',
	'timeout' => 30,
	'redirection' => 5,
	'httpversion' => '1.0',
	'blocking' => true,
	'body' => array( 'k' => $sucuri_wp_key, 'send-hashes' => $final_message),
    ));

    if(is_wp_error($response))
    {
        return(1);
    }

    $doresult = $response['body'];
    if(strpos($doresult,"ERROR:") === FALSE)
    {
        if(strpos($doresult, "ERROR: Invalid") !== FALSE)
        {
            delete_option('sucuri_wp_key');
        }
        return(1);
    }
    else
    {
        return(0);
    }
}



/* sucuri_block_wpadmin:
 * Blocks an IP via our plugin (wp-admin only). 
 */
function sucuri_block_wpadmin()
{
    if(isset($_SERVER['SUCURI_RIP']))
    {
        @touch(sucuri_dir_filepath('blocks/').$_SERVER['SUCURI_RIP']);
    }
}



/* sucuri_send_log:
 * Sends the events (audit log) to sucuri. 
 */
function sucuri_send_log($sucuri_wp_key, $final_message, $ignore_res = 0)
{
    $response = wp_remote_post("https://wordpress.sucuri.net/", array(
	'method' => 'POST',
	'timeout' => 12,
	'redirection' => 5,
	'httpversion' => '1.0',
	'blocking' => true,
	'body' => array( 'k' => $sucuri_wp_key, 'send-event' => $final_message),
    ));

    if(is_wp_error($response))
    {
        return(-1);
    }

    $doresult = $response['body'];
    
    if($ignore_res == 1)
    {
        return(1);
    }

    if(strpos($doresult,"ERROR:") !== FALSE)
    {
        if(strpos($doresult, "ERROR: Invalid") !== FALSE)
        {
            delete_option('sucuri_wp_key');
        }
        return(0);
    }
    else if(strpos($doresult, "OK:") !== FALSE)
    {
        return(1);
    }
    else
    {
        return(0);
    }
}



/* sucuri_event:
 * Generates an audit event log (to be sent later). 
 */
function sucuri_event($severity, $location, $message) 
{
    $severity = trim($severity);
    $location = trim($location);
    $message = trim($message);

	
    $username = NULL;
    //$user = wp_get_current_user();
    if(!empty($user->ID))
    {
        $username = $user->user_login;
    }
    $time = date('Y-m-d H:i:s', time());
	

    /* Fixing severity */
    $severity = (int)$severity;
    if ($severity < 0)
    {
        $severity = 1;
    }
    else if($severity > 5)
    {
        $severity = 5;
    }
        
    /* Setting remote ip. */
    $remote_ip = "local";
    if(isset($_SERVER['SUCURI_RIP']) && strlen($_SERVER['SUCURI_RIP']) > 6)
    {
        $remote_ip = $_SERVER['SUCURI_RIP'];
    }


    /* Setting header block */
    $header = NULL;
    if($username !== NULL)
    {
        $header = '['.$remote_ip.' '.$username.']';
    }
    else
    {
        $header = '['.$remote_ip.']';
    }


    /* Making sure we escape everything. */
    $header = htmlspecialchars($header);
    $message = htmlspecialchars($message);

    /* To avoid double lines. */
    $message = str_replace("\n", "", $message);
    $message = str_replace("\r", "", $message);


    /* Getting severity. */
    $severityname = "Info";
    if($severity == 0)
    {
        $severityname = "Debug";
    }
    else if($severity == 1)
    {
        $severityname = "Notice";
    }
    else if($severity == 2)
    {
        $severityname = "Info";
    }
    else if($severity == 3)
    {
        $severityname = "Warning";
    }
    else if($severity == 4)
    {
        $severityname = "Error";
    }
    else if($severity == 5)
    {
        $severityname = "Critical";
    }

    $sucuri_wp_key = get_option('sucuri_wp_key');
    if($sucuri_wp_key !== FALSE)
    {
        sucuri_send_log($sucuri_wp_key, "$severityname: $header: $message", 1);
    }

    return(true);
}



function sucuri_harden_error($message)
{
    return('<div id="message" class="error"><p>'.$message.'</p></div>');
}

function sucuri_harden_ok($message)
{
    return( '<div id="message" class="updated"><p>'.$message.'</p></div>');
}


function sucuri_harden_status($status, $type, $messageok, $messagewarn, 
                              $desc = NULL, $updatemsg = NULL)
{
    if($status == 1)
    {
        echo '<h4>'.
             '<img style="position:relative;top:5px" height="22" width="22"'. 
             'src="'.SUCURI_IMG.'ok.png" /> &nbsp; '.
             $messageok.'.</h4>';

        if($updatemsg != NULL){ echo $updatemsg; }
    }
    else
    {
        echo '<h4>'.
             '<img style="position:relative;top:5px" height="22" width="22"'. 
             'src="'.SUCURI_IMG.'warn.png" /> &nbsp; '.
             $messagewarn. '.</h4>';

        if($updatemsg != NULL){ echo $updatemsg; }

        if($type != NULL)
        {
            echo '<input class="button-primary" type="submit" name="'.$type.'" 
                         value="Harden it!" /><br /><br />';
        }
    }
    if($desc != NULL)
    {
        echo "<i>$desc</i>";
    }

}


function sucuri_harden_version()
{
    global $wp_version;
    $cp = 0;
    $updates = get_core_updates();
    if (!is_array($updates))
    {
        $cp = 1;
    }
    else if(empty($updates))
    {
        $cp = 1;
    }
    else if($updates[0]->response == 'latest')
    {
        $cp = 1;
    }
    if(strcmp($wp_version, "3.4.2") < 0)
    {
        $cp = 0;
    }
    $wp_version = htmlspecialchars($wp_version);


    __ss_wraphardeningboxopen("Verify WordPress Version");


    sucuri_harden_status($cp, NULL, 
                         "WordPress is updated", "WordPress is not updated",
                         NULL);

    if($cp == 0)
    {
        echo "<i>Your current version ($wp_version) is not current. Please update it <a href='update-core.php'>now!</a></i>";
    }
    else
    {
        echo "<i>Your WordPress installation ($wp_version) is current.</i>";
    }
    __ss_wraphardeningboxclose();
}



function sucuri_harden_removegenerator()
{
    /* Enabled by default with this plugin. */
    $cp = 1;
    
    __ss_wraphardeningboxopen("Remove WordPress Version");

    sucuri_harden_status($cp, "sucuri_harden_removegenerator", 
                         "WordPress version properly hidden", NULL,
                         "It checks if your WordPress version is being hidden".
                         " from being displayed in the generator tag ".
                         "(enabled by default with this plugin).");

    __ss_wraphardeningboxclose();
}



function sucuri_harden_upload()
{
    $cp = 1;
    $upmsg = NULL;
    $htaccess_upload = dirname(sucuri_dir_filepath())."/.htaccess";

    if(!is_readable($htaccess_upload))
    {
        $cp = 0;
    }
    else
    {
        $cp = 0;
        $fcontent = file($htaccess_upload);
        foreach($fcontent as $fline)
        {
            if(strpos($fline, "deny from all") !== FALSE)
            {
                $cp = 1;
                break;
            }
        }
    }

    if(isset($_POST['sucuri_harden_upload']) &&
       isset($_POST['wpsucuri-doharden']) &&
       $cp == 0)
    {
        if(file_put_contents("$htaccess_upload",
                             "\n<Files *.php>\ndeny from all\n</Files>")===FALSE)
        {
            $upmsg = sucuri_harden_error("ERROR: Unable to create .htaccess file.");
        }
        else
        {
            $upmsg = sucuri_harden_ok("Completed. Upload directory successfully secured.");
            $cp = 1;
        }
    }

    __ss_wraphardeningboxopen("Protect uploads directory");
    sucuri_harden_status($cp, "sucuri_harden_upload", 
                         "Upload directory properly protected",
                         "Upload directory not protected",
                         "It checks if your upload directory allows PHP ".
                         "execution or if it is browsable.", $upmsg);
    __ss_wraphardeningboxclose();
}   



function sucuri_harden_wpcontent()
{
    $cp = 1;
    $upmsg = NULL;
    $htaccess_content = ABSPATH."/wp-content/.htaccess";

    if(!is_readable($htaccess_content))
    {
        $cp = 0;
    }
    else
    {
        $cp = 0;
        $fcontent = file($htaccess_content);
        foreach($fcontent as $fline)
        {
            if(strpos($fline, "deny from all") !== FALSE)
            {
                $cp = 1;
                break;
            }
        }
    }

    if(isset($_POST['sucuri_harden_wpcontent']) &&
       isset($_POST['wpsucuri-doharden']) &&
       $cp == 0)
    {
        if(file_put_contents("$htaccess_content",
                             "\n<Files *.php>\ndeny from all\n</Files>")===FALSE)
        {
            $upmsg = sucuri_harden_error("ERROR: Unable to create .htaccess file.");
        }
        else
        {
            $upmsg = sucuri_harden_ok("Completed. wp-content directory successfully secured.");
            $cp = 1;
        }
    }

    __ss_wraphardeningboxopen("Restrict access to wp-content");
    sucuri_harden_status($cp, "sucuri_harden_wpcontent", 
                         "WP-content directory properly protected",
                         "WP-content directory not protected",
                         "This option blocks direct PHP access to any file inside wp-content. <b>Warn: Do not use it if ".
                         "your site uses timthumb or similar (insecure) scripts.</b> If something breaks, just remove the .htaccess from wp-content.", $upmsg);
    __ss_wraphardeningboxclose();
}   



function sucuri_harden_wpincludes()
{
    $cp = 1;
    $upmsg = NULL;
    $htaccess_content = ABSPATH."/wp-includes/.htaccess";

    if(!is_readable($htaccess_content))
    {
        $cp = 0;
    }
    else
    {
        $cp = 0;
        $fcontent = file($htaccess_content);
        foreach($fcontent as $fline)
        {
            if(strpos($fline, "deny from all") !== FALSE)
            {
                $cp = 1;
                break;
            }
        }
    }

    if(isset($_POST['sucuri_harden_wpincludes']) &&
       isset($_POST['wpsucuri-doharden']) &&
       $cp == 0)
    {
        if(file_put_contents("$htaccess_content",
                             "\n<Files *.php>\ndeny from all\n</Files>\n<Files wp-tinymce.php>\nallow from all\n</Files>\n<Files ms-files.php>\nallow from all\n</Files>\n")===FALSE)
        {
            $upmsg = sucuri_harden_error("ERROR: Unable to create .htaccess file.");
        }
        else
        {
            $upmsg = sucuri_harden_ok("Completed. wp-includes directory successfully secured.");
            $cp = 1;
        }
    }

    __ss_wraphardeningboxopen("Restrict access to wp-includes");
    sucuri_harden_status($cp, "sucuri_harden_wpincludes", 
                         "WP-includes directory properly protected",
                         "WP-includes directory not protected",
                         "This option blocks direct PHP access to any file inside wp-includes. ", $upmsg);
    __ss_wraphardeningboxclose();
}   



function sucuri_harden_keys()
{
    $upmsg = NULL;
    $cp = 1;
    $wpconf = NULL;
    if(is_readable(ABSPATH."/wp-config.php"))
    {
        $wpconf = ABSPATH."/wp-config.php";
    }
    else if(is_readable(ABSPATH."/../wp-config.php"))
    {
        $wpconf = ABSPATH."/../wp-config.php";
    }
    else
    {
        /* Unable to find? */
        $cp = 1;
    }

    __ss_wraphardeningboxopen("Verify proper usage of the secret keys");
    sucuri_harden_status($cp, NULL, 
                         "WordPress secret keys and salts properly created",
                         "WordPress secret keys and salts not set. We recommend creating them for security reasons",
                         "It checks whether you have proper random keys/salts ".
                         "created for WordPress. They should be created when ".
                         "you first install WordPress and regenerated if you ".
                         "have been hacked recently.", 
                         $upmsg);
    __ss_wraphardeningboxclose();
}


function sucuri_harden_dbtables()
{
    global $table_prefix;
    if($table_prefix == "wp_")
    {
        $cp = 0;
    }
    else
    {
        $cp = 1;
    }


    __ss_wraphardeningboxopen("Change Default Database Table Prefix");
    sucuri_harden_status($cp, "sucuri_harden_dbtables",
        "Database table prefix properly modified",
        "Database table set to the default value. Not recommended",
        "It checks whether your database table prefix has ".
        "been changed from the default 'wp_'.", NULL);
    if($cp == 0)
    {
        echo '<br /><i>*We do not offer the option to automatically change the table prefix, but it will be available soon on a next release.</i>';
    }
    __ss_wraphardeningboxclose();
}



function sucuri_harden_adminuser()
{
    global $table_prefix;
    global $wpdb;
    
    $res = $wpdb->get_results("SELECT user_login from ".
                              $table_prefix."users where user_login='admin'");

    $cp = 0;
    if(count($res) == 0)
    {
        $cp = 1;
    }
    __ss_wraphardeningboxopen("Change Default Admin user name");
    sucuri_harden_status($cp, NULL,
                          "Default admin user name (admin) not being used",
                          "Default admin user name (admin) being used. Not recommended",
                          "It checks whether you have the default 'admin' ".
                          "account enabled. Security guidelines recommend ".
                          "creating a new admin user name.", NULL);

    if($cp == 0)
    {
        echo '<br /><i>*We do not offer the option to automatically change the user name. Go to the <a href="users.php">user list</a> and create a new admin user name. Once created, log in as that user and remove the default "admin" from there (make sure to assign all the admin posts to the new user too!).</i>';
    }
    __ss_wraphardeningboxclose();
}



function sucuri_harden_wpconfig()
{
    $upmsg = NULL;
    $cp = 0;
    if(!is_readable(ABSPATH."/wp-config.php"))
    {
        $cp = 1;
    }
    else if(is_readable(ABSPATH."/../wp-config.php"))
    {
        $cp = 1;
    }
    if(is_readable(ABSPATH."/../index.php"))
    {
        $cp = 1;
    }
    if(is_readable(ABSPATH."/../index.html"))
    {
        $cp = 1;
    }

    if(isset($_POST['sucuri_harden_wpconfig']) &&
       isset($_POST['wpsucuri-doharden']) &&
       $cp == 0)
    {
        if(rename(ABSPATH."/wp-config.php",ABSPATH."/../wp-config.php")===FALSE)
        {
            $upmsg = sucuri_harden_error("Unable to rename wp-config.php.");
        }
        else
        {
            $cp = 1;
            $upmsg = sucuri_harden_ok("Completed. WP-config renamed.");
        }
    }

    __ss_wraphardeningboxopen("Move wp-config one directory UP");
    sucuri_harden_status($cp, "sucuri_harden_wpconfig", 
                         "Configuration file (wp-config.php) properly secured",
                         "Configuration file (wp-config.php) in the main WordPress directory. Not recommended",
                         "It checks whether your wp-config.php file is present ".
                         "in the default directory (instead of above one dir)", 
                         $upmsg);
    __ss_wraphardeningboxclose();
}



function sucuri_harden_readme()
{
    $upmsg = NULL;
    $cp = 0;
    if(!is_readable(ABSPATH."/readme.html"))
    {
        $cp = 1;
    }

    if(isset($_POST['sucuri_harden_readme']) &&
       isset($_POST['wpsucuri-doharden']) &&
       $cp == 0)
    {
        if(unlink(ABSPATH."/readme.html") === FALSE)
        {
            $upmsg = sucuri_harden_error("Unable to remove readme file.");
        }
        else
        {
            $cp = 1;
            $upmsg = sucuri_harden_ok("Readme file removed.");
        }
    }

    __ss_wraphardeningboxopen("Remove readme.html (information leakage)");
    sucuri_harden_status($cp, "sucuri_harden_readme", 
                         "Readme file properly deleted",
                         "Readme file not deleted and leaking the WordPress version",
                         "It checks whether you have the readme.html file ".
                         "available that leaks your WordPress version.", $upmsg);
    __ss_wraphardeningboxclose();
}



function sucuri_harden_phpversion()
{
    $phpv = phpversion();

    if(strncmp($phpv, "5.", 2) < 0)
    {
        $cp = 0;
    }
    else
    {
        $cp = 1;
    }

    __ss_wraphardeningboxopen("Verify PHP version");
    sucuri_harden_status($cp, NULL, 
                         "Using an updated version of PHP (v $phpv)",
                         "The version of PHP you are using ($phpv) is not current. Not recommended and not supported",
                         "It checks if you have the latest version of PHP installed.", NULL);
    __ss_wraphardeningboxclose();
}
function sucuri_add_attachment($id = NULL)
{
    if (is_numeric($id)) 
    {
        $postdata = get_post($id);
        $postname = $postdata->post_title;
    }
    sucuri_event(1, "core", "Attachment added to post. Id: $id, Name: $postname");
}


function sucuri_create_category($categoryid = NULL)
{
    if(is_numeric($categoryid))
    {
        $name = get_cat_name($categoryid);
    }
    sucuri_event(1, "core", "Category created. Id: $categoryid, Name: $name");
}

function sucuri_delete_post($id = NULL)
{
    //sucuri_event(3, "core", "Post deleted. Id: $id");
}

function sucuri_private_to_published($id = NULL)
{
    if (is_numeric($id)) 
    {
        $postdata = get_post($id);
        $postname = $postdata->post_title;
    }
    sucuri_event(2, "core", "Post state changed from private to published. Id: $id, Name: $postname");
}

function sucuri_publish_post($id = NULL)
{
    if (is_numeric($id)) 
    {
        $postdata = get_post($id);
        $postname = $postdata->post_title;
    }
    sucuri_event(2, "core", "Post published (or edited after being published). Id: $id, Name: $postname");
}


function sucuri_add_link($id)
{
    if(is_numeric($id))
    {
        $linkdata = get_bookmark($id);
        $name = $linkdata->link_name;
        $url = $linkdata->link_url;
    }
    sucuri_event(2, "core", "New link added. Id: $id, Name: $name, URL: $url");
}


function sucuri_switch_theme($themename)
{
    sucuri_event(3, "core", "Theme modified to: $themename");
}

function sucuri_delete_user($id)
{
    sucuri_event(3, "core", "User deleted. Id: $id");
}

function sucuri_retrieve_password($name)
{
    sucuri_event(3, "core", "Password retrieval attempt by user $name");
}

function sucuri_user_register($id)
{
    if(is_numeric($id))
    {
        $userdata = get_userdata($id);
        $name = $userdata->display_name;
    }
    sucuri_event(3, "core", "New user registered: Id: $id, Name: $name");
}


function sucuri_wp_login($name)
{
    sucuri_event(2, "core","User logged in. Name: $name");
}

function sucuri_wp_login_failed($name)
{
    sucuri_event(3, "core","User authentication failed. User name: $name");
}


function sucuri_reset_password($arg = NULL)
{
    if(isset($_GET['key']) )
    {
        /* Detecting wordpress 2.8.3 vulnerability - $key is array */
        if(is_array($_GET['key']))
        {
            sucuri_event(3, 'core', "IDS: Attempt to reset password by attacking wp2.8.3 bug.");
        }
    }
}



function sucuri_process_prepost()
{
    $doblock = 0;
    if($_SERVER['REQUEST_METHOD'] != "POST")
    {
        return(0);
    }

    $remediation = get_option('sucuri_wp_re');
    if($remediation !== FALSE && $remediation == 'disabled')
    {
        return(0);
    }


    /* Using the right ip address here. */
    if(isset($_SERVER['SUCURI_RIP']) && strlen($_SERVER['SUCURI_RIP']) > 5)
    {
        $myip = $_SERVER['SUCURI_RIP'];
    }
    else
    {
        return(0);
    }


    if(is_file(sucuri_dir_filepath("whitelist/$myip")))
    {
        return(0);
    }


    /* Blocking IP addresses in our block list. */
    if(is_file(sucuri_dir_filepath("blocks/$myip")))
    {
        wp_die("Denied access by <a href='http://sucuri.net'>Sucuri</a> (ip blacklisted). Please contact the site owner to get it re-opened. Your IP address: ".htmlspecialchars($myip));
    }        


    /* WordPress specific ignores */
    if($doblock == 0)
    {
        $_SERVER['REQUEST_URI'] = trim($_SERVER['REQUEST_URI']);
        if(strpos($_SERVER['REQUEST_URI'],"/wp-admin/admin-ajax.php") !== FALSE)
        {
            return(0);
        }
        else if(strpos($_SERVER['REQUEST_URI'], "/ajax_search.php") !== FALSE)
        {
            return(0);
        }
        else if(strpos($_SERVER['REQUEST_URI'], "/wp-admin/post.php") !== FALSE)
        {
            return(0);
        }
        else if(strpos($_SERVER['REQUEST_URI'], "/wp-cron.php") !== FALSE)
        {
            return(0);
        }
    }


    $response = wp_remote_post("http://cc.sucuri.net", array(
        'method' => 'POST',
        'timeout' => 10,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'body' => array( 'ip' => $myip, 
                         'from' => $_SERVER['SERVER_NAME'],
                         'path' => $_SERVER['REQUEST_URI'],
                         'ua' => $_SERVER['HTTP_USER_AGENT'],
                         'data' => print_r($_POST,1)),
    ));

    if(is_wp_error($response))
    {
        return(1);
    }

    $doresult = $response['body'];
    if(strpos($doresult, "BLOCK") !== FALSE)
    {
        $doblock = 1;
    }
    else
    {
        $doblock = 0;
    }


    if($doblock == 1)
    {
        $sucuri_wp_key = get_option('sucuri_wp_key');
        if($sucuri_wp_key != NULL)
        {
            sucuri_event(3, 'core', "IDS: Web firewall blocked access from: ".$myip);
        }
        sucuri_block_wpadmin();
        wp_die("Denied access by <a href='http://sucuri.net'>Sucuri</a> (ip blacklisted 2). Please contact the site owner to get it re-opened. Your IP address: ".htmlspecialchars($myip));
    }
}



function sucuri_events_without_actions()
{
    /* Using the right ip address here. */
    if(isset($_SERVER['SUCURI_RIP']))
    {
        $myip = $_SERVER['SUCURI_RIP'];
    }
    else
    {
        return(0);
    }


    $remediation = get_option('sucuri_wp_re');
    if($remediation !== FALSE && $remediation == 'disabled')
    {
        return(0);
    }


    /* Blocking IP addresses in our block list. */
    if(is_file(sucuri_dir_filepath("blocks/$myip")))
    {
        if(!is_file(sucuri_dir_filepath("whitelist/$myip")))
        {
            wp_die("Denied access by <a href='http://sucuri.net'>Sucuri</a> (ip blacklisted 3). Please contact the site owner to get it re-opened. Your IP address: ".htmlspecialchars($myip));
        }
    }        


    /* Plugin activated */
    if(isset($_GET['action']) && $_GET['action'] == "activate" && !empty($_GET['plugin']) && 
           strpos($_SERVER['REQUEST_URI'], 'plugins.php') !== false &&
           current_user_can('activate_plugins'))
    {
        $plugin = $_GET['plugin'];
        $plugin = strip_tags($plugin);
        $plugin = mysql_real_escape_string($plugin);
        sucuri_event(3, 'core', "Plugin activated: $plugin.");
    }

    /* Plugin deactivated */
    else if(isset($_GET['action']) && $_GET['action'] == "deactivate" && !empty($_GET['plugin']) && 
           strpos($_SERVER['REQUEST_URI'], 'plugins.php') !== false &&
           current_user_can('activate_plugins'))
    {
        $plugin = $_GET['plugin'];
        $plugin = strip_tags($plugin);
        $plugin = mysql_real_escape_string($plugin);
        sucuri_event(3, 'core', "Plugin deactivated: $plugin.");
    }

    /* Plugin updated */
    else if(isset($_GET['action']) && $_GET['action'] == "upgrade-plugin" && !empty($_GET['plugin']) && 
           strpos($_SERVER['REQUEST_URI'], 'wp-admin/update.php') !== false &&
           current_user_can('update_plugins'))
    {
        $plugin = $_GET['plugin'];
        $plugin = strip_tags($plugin);
        $plugin = mysql_real_escape_string($plugin);
        sucuri_event(3, 'core', "Plugin request to be updated: $plugin.");
    }

    /* WordPress updated */
    else if(isset($_POST['upgrade']) && isset($_POST['version']) && 
           strpos($_SERVER['REQUEST_URI'], 'update-core.php?action=do-core-reinstall') !== false &&
           current_user_can('update_core'))
    {
        $version = $_POST['version'];
        $version = strip_tags($version);
        $version = mysql_real_escape_string($version);
        sucuri_event(3, 'core', "WordPress updated (or re-installed) to version: $version.");
    }

    /* Theme editor */
    else if(isset($_POST['action']) && $_POST['action'] == 'update' && 
            isset($_POST['file']) && isset($_POST['theme']) &&
            strpos($_SERVER['REQUEST_URI'], 'theme-editor.php') !== false)
    {
        $myfile = mysql_real_escape_string(htmlspecialchars(trim($_POST['file'])));
        $mytheme = mysql_real_escape_string(htmlspecialchars(trim($_POST['theme'])));
        sucuri_event(3, 'core', "Theme editor used to modify file: $mytheme/$myfile.");
    }

    /* Plugin editor */
    else if(isset($_POST['action']) && $_POST['action'] == 'update' &&
            isset($_POST['file']) && isset($_POST['plugin']) &&
            strpos($_SERVER['REQUEST_URI'], 'plugin-editor.php') !== false)
    {
        $myfile = mysql_real_escape_string(htmlspecialchars(trim($_POST['file'])));
        sucuri_event(3, 'core', "Plugin editor used to modify file: $myfile.");
    }   

}


/* sucuri_wpmenu: 
 * Adds the proper menus on the wp-admin sidebar.
 */
function sucuri_wpmenu() 
{
    /* We auto white list admins. They can disable / modify settings anyway. */
    if(current_user_can('manage_options') && isset($_SERVER['SUCURI_RIP']))
    {
        if(!is_file(sucuri_dir_filepath("whitelist/".$_SERVER['SUCURI_RIP'])))
        {
            @touch(sucuri_dir_filepath("whitelist/".$_SERVER['SUCURI_RIP']));
        }
    }

    add_menu_page('Sucuri Security', 'Sucuri Security', 'manage_options', 
                  'sucurisec', 'sucuri_admin_page', SUCURI_IMG.'menu-icon.png');

    add_submenu_page('sucurisec', 'Dashboard', 'Dashboard', 'manage_options',
                     'sucurisec', 'sucuri_admin_page');

    add_submenu_page('sucurisec', 'Settings', 'Settings', 'manage_options',
                     'sucurisec_settings', 'sucuri_settings_page');

    add_submenu_page('sucurisec', 'Block List', 'Block List', 'manage_options',
                     'sucurisec_blacklist', 'sucuri_blacklist_page');

    add_submenu_page('sucurisec', 'Malware Scanning', 'Malware Scanning', 
                     'manage_options',
                     'sucurisec_malwarescan', 'sucuri_malwarescan_page');

    add_submenu_page('sucurisec', '1-Click Hardening', '1-Click Hardening', 
                     'manage_options',
                     'sucurisec_hardening', 'sucuri_hardening_page');
}



/* sucuri_pagestop:
 * Prints the top (header) for all internal pages.
 */
function sucuri_pagestop($sucuri_title = 'Sucuri Plugin')
{
    if(!current_user_can('manage_options'))
    {
        wp_die(__('You do not have sufficient permissions to access this page.') );
    }
    ?>
    <div class="wrap">
    <div id="icon-link-manager" class="icon32"><br /></div>
    <h2><?php echo htmlspecialchars($sucuri_title); ?></h2>
    <br class="clear"/>
    <?php
}






function sucuri_blacklist_page()
{
    sucuri_pagestop("Sucuri Block and White Lists");
    $errrm = NULL;


    /* Unblocking IP addresses */
    if(isset($_POST['wpsucuri_removeip']) && strlen($_POST['wpsucuri_removeip']) > 6)
    {
        $iptorm = explode(' ', htmlspecialchars($_POST['wpsucuri_removeip']));
        $pattern = "/^[0-9]+[\.][0-9]+[\.][0-9]+[\.][0-9]+$/";
        if(preg_match($pattern, $iptorm[0], $regs, PREG_OFFSET_CAPTURE, 0))
        {
            if($iptorm !== FALSE && !empty($iptorm) && strlen($iptorm[0]) > 4)
            {
                @unlink(sucuri_dir_filepath('blocks/'.$iptorm[0]));
            }
        }
    }

    /* White list removal */
    if(isset($_POST['wpsucuri_whiteremoveip']) && strlen($_POST['wpsucuri_whiteremoveip']) > 6)
    {
        $iptorm = explode(' ', htmlspecialchars($_POST['wpsucuri_whiteremoveip']));
        $pattern = "/^[0-9]+[\.][0-9]+[\.][0-9]+[\.][0-9]+$/";
        if(preg_match($pattern, $iptorm[0], $regs, PREG_OFFSET_CAPTURE, 0))
        {
            @unlink(sucuri_dir_filepath('whitelist/'.$iptorm[0]));
        }
    }

    /* Adding to the white list. */
    if(isset($_POST['wpsucuri_whiteaddip']) && strlen($_POST['wpsucuri_whiteaddip']) > 6)
    {
        $iptorm = htmlspecialchars(trim($_POST['wpsucuri_whiteaddip']));
        $pattern = "/^[0-9]+[\.][0-9]+[\.][0-9]+[\.][0-9]+$/";
        if(preg_match($pattern, $iptorm, $regs, PREG_OFFSET_CAPTURE, 0))
        {
            @touch(sucuri_dir_filepath('whitelist/'.$iptorm));
        }
    }


    /* List of blocked ip addresses */
    $myblockedips = array();
    if(is_dir(sucuri_dir_filepath('blocks/')))
    {
        $listofips = scandir(sucuri_dir_filepath('blocks/'));
        if(count($listofips > 3))
        {
            ?>
            <?php
            foreach($listofips as $uniqip)
            {
                if(strncmp($uniqip, "<", 1) == 0 ||
                   strncmp($uniqip, "#", 1) == 0 ||
                   strncmp($uniqip, ".", 1) == 0 ||
                   strncmp($uniqip, "b", 1) == 0 ||
                   strncmp($uniqip, "i", 1) == 0)
                {
                    continue;
                }
                $uniqip = htmlspecialchars($uniqip);
                $myblockedips[] = $uniqip; 
            }
        }
    }



    /* List of Whitelisted addresses */
    $mywhitelistips = array();
    if(is_dir(sucuri_dir_filepath('whitelist/')))
    {
        $listofips = scandir(sucuri_dir_filepath('whitelist/'));
        if(count($listofips > 3))
        {
            ?>
            <?php
            foreach($listofips as $uniqip)
            {
                if(strncmp($uniqip, "<", 1) == 0 ||
                   strncmp($uniqip, "#", 1) == 0 ||
                   strncmp($uniqip, ".", 1) == 0 ||
                   strncmp($uniqip, "b", 1) == 0 ||
                   strncmp($uniqip, "i", 1) == 0)
                {
                    continue;
                }
                $uniqip = htmlspecialchars($uniqip);
                $mywhitelistips[] = $uniqip; 
                $totalwips++;
            }
        }
    }


    if($errrm != NULL)
    {
        echo '<div id="message" class="updated"><p>'.$errrm.'</p></div>';
    }
    else if(isset($_POST['wpsucuri_removeip']))
    {
        echo '<div id="message" class="updated"><p>IP address removed.</p></div>';
    }


    $remediation = get_option('sucuri_wp_re');
    if($remediation !== FALSE && $remediation == 'disabled')
    {
        $remediation = '<h3>Warning: Active response is disabled. No blocking will be done. You can re-enable on the plugin settings.</h3>';
    }
    else
    {
        $remediation = '';
    }

    ?>




     <div id="poststuff">
        <div class="postbox">
        <?php echo $remediation; ?>
            <h3>White listed IP Addresses</h3>
            <div class="inside">

                <form action="" method="post">
                <?php 
                $totalips = 0;
                foreach($mywhitelistips as $mybip)
                {
                    echo '<input type="submit" name="wpsucuri_whiteremoveip" value="'.$mybip.' - Click to remove" /><br />';
                    $totalips = 1;
                }
                if($totalips == 0)
                {
                    echo "<p>No IP Address whitelisted so far.</p>";
                }
                ?>
                <br />
                <i>*Your current IP address: <?php echo $_SERVER["REMOTE_ADDR"]; if(isset($_SERVER['SUCURI_RIP'])){echo "(".$_SERVER['SUCURI_RIP'].")"; } ?></i>
                White list IP: <input type="text" name="wpsucuri_whiteaddip" id="wpsucuri_whiteaddip" value="" size="15" />
                <input type="submit" name="wpsucuri_whiteaddipbutton" value="White list" />
                
                </form>
            </div>
        </div>
    </div>

    
    
    <div id="poststuff">
        <div class="postbox">
            <h3>Blocked IP Addresses</h3>
            <div class="inside">

                <form action="" method="post">
                <?php 
                $totalips = 0;
                foreach($myblockedips as $mybip)
                {
                    echo '<input type="submit" name="wpsucuri_removeip" value="'.$mybip.' - Click to remove" /><br />';
                    $totalips = 1;
                }
                if($totalips == 0)
                {
                    echo "<p>No IP Address blocked so far.</p>";
                }
                ?>
                </form>
            </div>
        </div>
    </div>


    </div>
       
    <?php
}






/* Sucuri's dashboard (main admin page) */
function sucuri_admin_page()
{
    $U_ERROR = NULL;
    if(!current_user_can('manage_options'))
    {
        wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    $sucuri_wp_key = NULL;
    $sucuri_wp_key = get_option('sucuri_wp_key');
    if(isset($_POST['wpsucuri-modify-values']))
    {
        sucuri_settings_page();
        return(1);
    }
    else if($sucuri_wp_key === FALSE)
    {
        sucuri_settings_page();
        return(1);
    }


    /* Admin header. */
    echo '<div class="wrap">';
    ?>
    <iframe style="overflow:hidden" width="100%" height="2250px" src='https://wordpress.sucuri.net/single.php?k=<?php echo $sucuri_wp_key;?>'>
    Unable to load iframe.
    </iframe>
    <br />
    <hr />
    <h3><i>Plugin developed by <a href="http://sucuri.net">Sucuri Security</a> | <a href="https://support.sucuri.net">Support & Help</a></i></h3>
    </div>
    <?php
}




function __ss_wraphardeningboxopen($msg)
{
    ?>
    <div class="postbox">
        <h3><?php echo $msg; ?></h3>
        <div class="inside">
    <?php
}
function __ss_wraphardeningboxclose()
{
    ?>
    </div>
    </div>
    <?php
}


/* Sucuri one-click hardening page. */
function sucuri_hardening_page()
{
    
    sucuri_pagestop("Sucuri 1-Click Hardening Options");

    if(isset($_POST['wpsucuri-doharden']))
    {
        if(!wp_verify_nonce($_POST['sucuri_wphardeningnonce'], 'sucuri_wphardeningnonce'))
        {
            unset($_POST['wpsucuri-doharden']);
        }
    }

    ?>
    <div id="poststuff">
        <div class="postbox">
        <h3>Sucuri 1-click Hardening</h3>
        <div class="inside">
        <h2>Help secure your WordPress install with <a href="http://sucuri.net/signup">Sucuri</a> 1-Click Hardening Options.</h2>
        </div>
        </div>

    <?php
        echo '<form action="" method="post">'.
             '<input type="hidden" name="sucuri_wphardeningnonce" 
                           value="'.wp_create_nonce('sucuri_wphardeningnonce').'" />'.
             '<input type="hidden" name="wpsucuri-doharden" value="wpsucuri-doharden" />';

            sucuri_harden_version();
            echo "<hr />";
            sucuri_harden_removegenerator();
            echo "<hr />";
            sucuri_harden_upload();
            echo "<hr />";
            sucuri_harden_wpcontent();
	    echo "<hr />";
            sucuri_harden_wpincludes();
	    echo "<hr />";
            sucuri_harden_keys();
            echo "<hr />";
            sucuri_harden_readme();
            echo "<hr />";
            sucuri_harden_dbtables();
            echo "<hr />";
            sucuri_harden_adminuser();
            echo "<hr />";
            sucuri_harden_phpversion();
            echo "<hr />";
            echo '<b>If you have any question about these checks or this plugin, contact us at support@sucuri.net or visit <a href="http://sucuri.net">http://sucuri.net</a></b>';
        ?>
    </div>       
    </div>

    <?php
}







/* Sucuri malware scan page. */
function sucuri_malwarescan_page()
{
    $U_ERROR = NULL;
    if(!current_user_can('manage_options'))
    {
        wp_die(__('You do not have sufficient permissions to access this page.') );
    }

    ?>


    <div class="wrap">
    <div id="icon-tools" class="icon32"><br /></div>
    <h2>Sucuri Malware Scanner</h2>
    <br class="clear"/>
    <div id="poststuff" style="width:55%; float:left;">
        <div class="postbox">
        <h3>Sucuri Malware Scanner</h3>
        <div class="inside">

        <h4>Execute an external malware scan on your site, using the <a href="http://sucuri.net">Sucuri</a> scanner. It will alert you if your site is compromised with malware, spam, defaced or blacklisted</h4>
            
        <a target="_blank" href="http://sitecheck.sucuri.net/results/<?php echo home_url();?>">Scan now!</a>
        </div>
        </div>
    </div>       
    </div>

    <?php
}





/* sucuri_modify_settings:
 * Process Post requests from the settings page.
 */
function sucuri_modify_settings()
{
    if(!isset($_POST['wpsucuri-modify-values']))
    {
        return(NULL);
    }

    if(!wp_verify_nonce($_POST['sucuri_wpsettingsnonce'], 'sucuri_wpsettingsnonce'))
    {
        return(NULL);
    }


    /* Make sure all our files are there. */
    sucuri_create_files();
    if(!is_dir(sucuri_dir_filepath('/blocks')))
    {
        return("ERROR: Unable to activate. Without permissions to modify ".sucuri_dir_filepath('blocks'));
    }
    if(!is_file(sucuri_dir_filepath('blocks/blocks.php')))
    {
        return("ERROR: Unable to activate. Without permissions to modify ".sucuri_dir_filepath());
    }


    /* White listing a few IP addresses by default. */
    if(isset($_SERVER['SUCURI_RIP']))
    {
        @touch(sucuri_dir_filepath('whitelist/'.$_SERVER['SUCURI_RIP']));
    }
    @touch(sucuri_dir_filepath('whitelist/'.$_SERVER["REMOTE_ADDR"]));
    @touch(sucuri_dir_filepath('whitelist/'.$_SERVER['SERVER_ADDR']));


    /* Enabling or disabling remediation. */
    if(isset($_POST['sucuri_activeresponses']))
    {
        update_option('sucuri_wp_re', 'enabled');
    }
    else
    {
        update_option('sucuri_wp_re', 'disabled');
    }


    /* Handling unsafe (user) content */
    if(isset($_POST['wpsucuri-newkey']))
    {
        $newkey = htmlspecialchars(trim($_POST['wpsucuri-newkey']));
        if(preg_match("/^[a-zA-Z0-9]+$/", $newkey, $regs, PREG_OFFSET_CAPTURE,0))
        {
            $res = sucuri_send_log($newkey, "INFO: Authentication key added and plugin enabled.");
            /* RES = 1 , key accepted. */
            if($res == 1)
            {
                update_option('sucuri_wp_key', $newkey);

                sucuri_debug_log("Activating key $newkey..");
                if(!wp_next_scheduled( 'sucuri_hourly_scan')) 
                {
                    sucuri_debug_log("Activating wp_schedule event..");
	            wp_schedule_event(time() + 10, 'hourly', 'sucuri_hourly_scan');
                }

                wp_schedule_single_event(time()+300, 'sucuri_hourly_scan');
                return(NULL);
            }
            else if($res == -1)
            {
                return("ERROR: Unable to connect to https://wordpress.sucuri.net (check for curl + SSL support on PHP).");
            }
            else
            {
                return("ERROR: Key invalid. Not accepted by sucuri.net.");
            }
        }
        else
        {
            return("ERROR: Invalid key.");
        }
    }
}



/* sucuri_inactive_settings_page:
 * Sucuri settings page when the key was not enabled.
 */
function sucuri_inactive_settings_page()
{
    ?>
    <div id="poststuff">
        <div class="postbox">
        <h3>Plugin not activated</h3>
        <div class="inside">
        Your plugin is not configured yet. Please login to <a target="_blank" href="https://wordpress.sucuri.net">https://wordpress.sucuri.net</a> to get your authentication (API) key. <br />&nbsp;<br />Note, this plugin is only for Sucuri users. If you do not have an account, please sign up here: <a href="http://sucuri.net/signup">http://sucuri.net/signup</a>.
        </div>
        </div>
    </div>       
    <?php
}



/* sucuri_settings_page:
 * Sucuri main setting's page. 
 */
function sucuri_settings_page()
{
    $anywarn = NULL;
    if(!current_user_can('manage_options'))
    {
        wp_die(__('You do not have sufficient permissions to access this page.') );
    }


    /* If the scan was not done recently, try now. */
    if(!isset($_SESSION['sucuri_scan_just_done']))
    {
        $_SESSION['sucuri_scan_just_done'] = true;
        sucuri_do_scan();
    }


    /* Process post */
    $anywarn = sucuri_modify_settings();


    /* Default values */
    $sucuri_wp_key = NULL;


    /* If remediation (ip blocking is enabled). */
    $remediation = get_option('sucuri_wp_re');
    if($remediation !== FALSE && $remediation == 'disabled')
    {
        $remediation = '';
    }
    else
    {
        $remediation = ' checked="checked"';
    }


    /* Getting current key */
    $sucuri_wp_key = get_option('sucuri_wp_key');
    if($sucuri_wp_key !== FALSE)
    {
        $sucuri_wp_key = htmlspecialchars(trim($sucuri_wp_key));
    }

    sucuri_pagestop("Sucuri Plugin");
    ?> 


    <?php
    if($anywarn != NULL)
    {
        echo '<div id="message" class="updated"><p>'.htmlspecialchars($anywarn).'</p></div>';
    }
    if($sucuri_wp_key == NULL)
    {
        sucuri_inactive_settings_page();
    }

    ?>

    <div id="poststuff">
        <div class="postbox">
        <div class="handlediv" title="Click to toggle"><br /></div>
        <h3>Main settings</h3>
        <div class="inside">
            <form method="post">
            <input type="hidden" name="sucuri_wpsettingsnonce" 
                                 value="<?php echo wp_create_nonce('sucuri_wpsettingsnonce'); ?>" />
            <input type="hidden" name="wpsucuri-modify-values" value="wpsucuri-modify-values" />
            <table class="form-table" style="margin-bottom:5px;">
            <tbody>
                <tr><td>
                <p>SUCURI API KEY (<i> get it from <a target="_blank" href="https://wordpress.sucuri.net">here</a></i>):<br />
                <input type="text" name="wpsucuri-newkey" id="wpsucuri-newkey" value="<?php if($sucuri_wp_key != NULL){echo $sucuri_wp_key;}?>" size="40" /></p>
                </td></tr>

                <tr><td><input type="checkbox" id="" checked="checked" disabled="disabled" /> Enables integrity monitoring for your files.</td></tr>

                <tr><td><input type="checkbox" id="" checked="checked" disabled="disabled" /> Enables audit trails and internal logs.</td></tr>

                <tr><td><input type="checkbox" name="sucuri_activeresponses" value="1" <?php echo $remediation;?>  /> Enables active response (to block suspicious IP addresses and attacks)</td></tr>

                <tr><td> &nbsp; <input class="button-primary" type="submit" name="wpsucuri_domodify" value="Save values"></td><td> &nbsp; </td></tr>
            </tbody>
            </table>
            </form>
        </div>
        </div>
    </div>
    <?php
}


/* Requires files. */



/* Getting correct remote ip  */
if(isset($_SERVER['SUCURI_RIP']))
{
    unset($_SERVER['SUCURI_RIP']);
}


/* For Cloudflare. */
if(isset($_SERVER['HTTP_CF_CONNECTING_IP']))
{
    $_SERVER['SUCURI_RIP'] = trim($_SERVER['HTTP_CF_CONNECTING_IP']);
}
/* More gateway requests. */
else if(isset($_SERVER['HTTP_X_ORIG_CLIENT_IP']))
{
    $_SERVER['SUCURI_RIP'] = $_SERVER['HTTP_X_ORIG_CLIENT_IP'];
}
/* Proxy requests. */
else if(isset($_SERVER['HTTP_TRUE_CLIENT_IP']))
{
    $_SERVER['SUCURI_RIP'] = $_SERVER['HTTP_TRUE_CLIENT_IP'];
}
else if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && 
   $_SERVER["HTTP_X_FORWARDED_FOR"] != $_SERVER['REMOTE_ADDR'] &&
   preg_match("/^[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*$/", $_SERVER["HTTP_X_FORWARDED_FOR"]))
{
    $_SERVER['SUCURI_RIP'] = trim($_SERVER["HTTP_X_FORWARDED_FOR"], "a..zA..Z%/. \t\n");
    $_SERVER['SUCURI_RIP'] = trim($_SERVER['SUCURI_RIP']);        
}
else if(preg_match("/^[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*$/", $_SERVER["REMOTE_ADDR"]))
{
    $_SERVER['SUCURI_RIP'] = $_SERVER['REMOTE_ADDR'];
}
$_SERVER['SUCURI_RIP'] = basename($_SERVER['SUCURI_RIP']);

if(!preg_match("/^[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*$/", $_SERVER["SUCURI_RIP"]))
{
    unset($_SERVER['SUCURI_RIP']);
}


/* Inspect every post request */
if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_SERVER['SUCURI_RIP']))
{
    add_action('init', 'sucuri_process_prepost');
}


if(is_multisite())
{
    if(is_network_admin())
    {

        add_action('network_admin_menu', 'sucuri_wpmenu');
        add_action('admin_init', 'sucuri_events_without_actions');
        add_action('login_form', 'sucuri_events_without_actions');
    }
    else if(is_admin())
    {
        add_action('admin_init', 'sucuri_events_without_actions');
        add_action('login_form', 'sucuri_events_without_actions');
    }
}

/* Admin specific actions. */
else if(is_admin())
{


    add_action('admin_menu', 'sucuri_wpmenu');
    add_action('admin_init', 'sucuri_events_without_actions');
    add_action('login_form', 'sucuri_events_without_actions');
}



/* Activation / Deactivation actions. */
register_activation_hook(__FILE__, 'sucuri_create_files');


/* Hooks our scanner function to the hourly cron. */
add_action('sucuri_hourly_scan', 'sucuri_do_scan');


/* Removing generator */
remove_action('wp_head', 'wp_generator');



/* EOF */
