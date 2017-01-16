==== Search All ====

Tags: search, pages, comments, attachments, drafts
Contributors: cori

The Search All Plugin adds capability to search pages, attachments, draft posts, and comments to the WP default search, with an admin console to contol search options.

Installation is simple. Download the archive file and extract it. Copy the search_all.php file to your plugins directory. Enable the plugin and visit the options page to set the options.

Option Descriptions

    * Search Pages - search the content of pages for the searched for string
    * Search Comments - search for posts with comment containing the searched for string
          o Approved Comments Only - if checked will only check the contents of approved comments; otherwise will search all comments
    * Search Drafts - include posts with Draft status in the search
    * Search Attachments - include uploaded file’s title’s and descriptions for the searched for string.

Each checked option will be added to the default WordPress search function.

Change Log

Version 0.1

    * initial release

Acknowledgements

Thanks to Dan Cameron (http://dancameron.org/) for Search Everything (http://dancameron.org/wordpress/wordpress-plugins/search-everything-wordpress-plugin/), and via Search Everything, thanks to to David B. Nagle (http://randomfrequency.net/) for Search Pages (http://randomfrequency.net/wordpress/search-pages/) and before that to Rob Schlüter (http://kwebble.com/) for his hack (http://www.kwebble.com/blog/2005/02/20/searching-pages-in-wordpress-15/).

This plugin discards some of the options for exact and sentence searches included in Search Everything because there’s apparently no way for user easily to access these options (aside from typing into the URL). It also removes some of the query-string safety functions in Search Everything, because it seems to me that those are adequately handled in the wp-db class in WordPress.

Support

Please leave a comment at http://kinrowan.net/blog/wordpress/search-all#support.