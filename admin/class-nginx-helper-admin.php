<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://rtcamp.com/nginx-helper/
 * @since      2.0.0
 *
 * @package    nginx-helper
 * @subpackage nginx-helper/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    nginx-helper
 * @subpackage nginx-helper/admin
 * @author     rtCamp
 */
class Nginx_Helper_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
    
    /**
	 * Various settings tabs.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $settings_tabs    Various settings tabs.
	 */
	private $settings_tabs;
    
    /**
	 * Purge options.
	 *
	 * @since    2.0.0
	 * @access   public
	 * @var      string    $options    Purge options.
	 */
	public $options;
    
    /**
	 * WP-CLI Command.
	 *
	 * @since    2.0.0
	 * @access   public
	 * @var      string    $options    WP-CLI Command.
	 */
	const WP_CLI_COMMAND = 'nginx-helper';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        /**
          * Define settings tabs
          */
        $this->settings_tabs = apply_filters( 'rt_nginx_helper_settings_tabs', array(
            'general' => array(
                'menu_title'    => __( 'General', 'nginx-helper' ),
                'menu_slug'     => 'general'
            ),
            'support' => array(
                'menu_title'    => __( 'Support', 'nginx-helper' ),
                'menu_slug'     => 'support'
            ) )
        );
        
        $this->options = $this->nginx_helper_settings();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles( $hook ) {
        
        /**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nginx_Helper_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nginx_Helper_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
        
        if ( 'settings_page_nginx' != $hook ) {
            return;
        }
        wp_enqueue_style( $this->plugin_name.'-icons', plugin_dir_url( __FILE__ ) . 'icons/css/nginx-fontello.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/nginx-helper-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts( $hook ) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nginx_Helper_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nginx_Helper_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
        
        if ( 'settings_page_nginx' != $hook ) {
            return;
        }
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/nginx-helper-admin.js', array( 'jquery' ), $this->version, false );
    }
    
    /**
	 * Add admin menu.
	 *
	 * @since    2.0.0
	 */
    public function nginx_helper_admin_menu() {
        
        if ( is_multisite() ) {
            add_submenu_page(
                'settings.php',
                __( 'Nginx Helper', 'nginx-helper' ),
                __( 'Nginx Helper', 'nginx-helper' ),
                'manage_options',
                'nginx',
                array( &$this, 'nginx_helper_setting_page' ) 
            );
        } else {
            add_submenu_page(
                'options-general.php',
                __( 'Nginx Helper', 'nginx-helper' ),
                __( 'Nginx Helper', 'nginx-helper' ),
                'manage_options',
                'nginx',
                array( &$this, 'nginx_helper_setting_page' ) 
            );
        }
    }
    
    public function nginx_helper_toolbar_purge_link( $wp_admin_bar ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        $purge_url = add_query_arg( array( 'nginx_helper_action' => 'purge', 'nginx_helper_urls' => 'all' ) );
        $nonced_url = wp_nonce_url( $purge_url, 'nginx_helper-purge_all' );
        $wp_admin_bar->add_menu(
            array(
                'id'    => 'nginx-helper-purge-all',
                'title' => __( 'Purge Cache', 'nginx-helper' ),
                'href'  => $nonced_url,
                'meta'  => array( 'title' => __( 'Purge Cache', 'nginx-helper' ) ), 
            ) 
        );
    }
    
    /**
     * Display settings.
     * @global $string $pagenow Contain current admin page.
     * 
     * @since    2.0.0
     */
    public function nginx_helper_setting_page() {
        include 'partials/nginx-helper-admin-display.php';
    }
    
    /**
     * Default settings.
     * 
     * @since    2.0.0
     * @return array
     */
    public function nginx_helper_default_settings() {
        return array(
            'enable_purge'                      => 0,
            'cache_method'                      => '',
            'purge_method'                      => '',
            'enable_map'                        => 0,
            'enable_log'                        => 0,
            'log_level'                         => 'INFO',
            'log_filesize'                      => '5',
            'enable_stamp'                      => 0,
            'purge_homepage_on_new'             => 0,
            'purge_homepage_on_edit'            => 0,
            'purge_homepage_on_del'             => 0,
            'purge_archive_on_new'              => 0,
            'purge_archive_on_edit'             => 0,
            'purge_archive_on_del'              => 0,
            'purge_archive_on_new_comment'      => 0,
            'purge_archive_on_deleted_comment'  => 0,
            'purge_page_on_mod'                 => 0,
            'purge_page_on_new_comment'         => 0,
            'purge_page_on_deleted_comment'     => 0,
            'redis_hostname'                    => '127.0.0.1',
            'redis_port'                        => '6379',
            'redis_prefix'                      => 'nginx-cache:',
            'purge_url'                         => '',
        );
    }
    
    /**
     * Get settings.
     * 
     * @since    2.0.0
     */
    public function nginx_helper_settings() {
        return wp_parse_args( 
            get_site_option( 'rt_wp_nginx_helper_options', array() ), 
            $this->nginx_helper_default_settings()
        );
    }
    
    public function nginx_helper_settings_link( $links ) {
        
        if ( is_network_admin() ) {
            $setting_page = 'settings.php';
        } else {
            $setting_page = 'options-general.php';
        }
        
        $settings_link = '<a href="' . admin_url( $setting_page . '?page=nginx' ). '">' . __( 'Settings', 'nginx-helper' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }
    
    /**
	 * Retrieve the log path.
	 *
	 * @since     2.0.0
	 * @return    string    log path of the plugin.
	 */
	public function get_log_path() {
		$log_dir = wp_upload_dir();
        $log_path = $log_dir['basedir'] . '/nginx-helper/';
        
        return apply_filters( 'rt_nginx_helper_log_path', $log_path );
	}
    
    /**
	 * Retrieve the log url.
	 *
	 * @since     2.0.0
	 * @return    string    log url of the plugin.
	 */
	public function get_log_url() {
		$log_dir = wp_upload_dir();
        $log_url = $log_dir['baseurl'] . '/nginx-helper/';
        
        return apply_filters( 'rt_nginx_helper_log_url', $log_url );
	}
    
    /**
     * Get latest news.
     * 
     * @since     2.0.0
     */
    public function nginx_helper_get_feeds() {
        // Get RSS Feed(s)
		require_once( ABSPATH . WPINC . '/feed.php' );
		$maxitems = 0;
		// Get a SimplePie feed object from the specified feed source.
		$rss = fetch_feed( 'http://rtcamp.com/blog/feed/' );
		if ( ! is_wp_error( $rss ) ) { // Checks that the object is created correctly
			// Figure out how many total items there are, but limit it to 5.
			$maxitems = $rss->get_item_quantity( 5 );
			// Build an array of all the items, starting with element 0 (first element).
			$rss_items = $rss->get_items( 0, $maxitems );
		}
		?>
		<ul role="list">
        <?php
			if ( $maxitems == 0 ) {
				echo '<li role="listitem">' . __( 'No items', 'nginx-helper' ) . '.</li>';
			} else {
				// Loop through each feed item and display each item as a hyperlink.
				foreach ( $rss_items as $item ) {
        ?>
					<li role="listitem">
						<a href='<?php echo $item->get_permalink(); ?>' title='<?php echo __( 'Posted ', 'nginx-helper' ) . $item->get_date( 'j F Y | g:i a' ); ?>'><?php echo $item->get_title(); ?></a>
					</li>
        <?php
				}
			}
        ?>
		</ul>
        <?php
        die();
    }
    
    /**
     * Add time stamps in html.
     */
    public function add_timestamps() {
       
        if ( is_admin() || $this->options['enable_purge'] != 1 || $this->options['enable_stamp'] != 1 ) {
            return;
        }
        
        foreach ( headers_list() as $header ) {
            list( $key, $value ) = explode( ':', $header, 2 );
            if ( 'Content-Type' == $key && strpos( trim( $value ), 'text/html' ) !== 0 ) {
                return;
            }
            if ( 'Content-Type' == $key ) {
                break;
            }
        }

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) { 
            return;
        }
        
        $timestamps = "\n<!--" .
                "Cached using Nginx-Helper on " . current_time('mysql') . ". " .
                "It took " . get_num_queries() . " queries executed in " . timer_stop() . " seconds." .
                "-->\n" .
                "<!--Visit http://wordpress.org/extend/plugins/nginx-helper/faq/ for more details-->";
        
        echo $timestamps;
    }
    
    /**
     * Get map
     * @global type $wpdb
     * @return string
     */
    public function get_map() {
        
        if ( !$this->options['enable_map'] ) {
            return;
        }

        if ( is_multisite() ) {
            global $wpdb;

            $rt_all_blogs = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT blog_id, domain, path FROM " . $wpdb->blogs . " WHERE site_id = %d AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0'",
                    $wpdb->siteid
                )
            );
            
            $wpdb->dmtable = $wpdb->base_prefix . 'domain_mapping';
            
            $rt_domain_map_sites = '';
            if ( $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->dmtable}'") == $wpdb->dmtable ) {
                $rt_domain_map_sites = $wpdb->get_results( "SELECT blog_id, domain FROM {$wpdb->dmtable} ORDER BY id DESC" );
            }
            
            $rt_nginx_map = "";
            $rt_nginx_map_array = array();

            if ( $rt_all_blogs )
                foreach ( $rt_all_blogs as $blog ) {
                    if ( "yes" == SUBDOMAIN_INSTALL ) {
                        $rt_nginx_map_array[ $blog->domain ] = $blog->blog_id;
                    } else {
                        if ( 1 != $blog->blog_id ) {
                            $rt_nginx_map_array[ $blog->path ] = $blog->blog_id;
                        }
                    }
                }

            if ( $rt_domain_map_sites ) {
                foreach ( $rt_domain_map_sites as $site ) {
                    $rt_nginx_map_array[ $site->domain ] = $site->blog_id;
                }
            }

            foreach ( $rt_nginx_map_array as $domain => $domain_id ) {
                $rt_nginx_map .= "\t" . $domain . "\t" . $domain_id . ";\n";
            }

            return $rt_nginx_map;
        }
    }

    /**
     * Update map
     */
    public function update_map() {
        
        if ( is_multisite() ) {
            $rt_nginx_map = $this->get_map();

            if ( $fp = fopen( $this->get_log_path() . 'map.conf', 'w+' ) ) {
                fwrite( $fp, $rt_nginx_map );
                fclose( $fp );
            }
        }
    }
    
    /**
     * Purge url when post status is changed.
     * 
     * @global type $blog_id
     * @global object $nginx_purger
     * @param string $new_status
     * @param string $old_status
     * @param object $post
     */
    public function set_future_post_option_on_future_status( $new_status, $old_status, $post ) {
        global $blog_id, $nginx_purger;
        
        if ( !$this->options['enable_purge'] ) {
            return;
        }
        
        $purge_status = array( 'publish', 'future' );
        
        if ( in_array( $old_status, $purge_status ) || in_array( $new_status, $purge_status ) ) {
            $nginx_purger->log( "Purge post on transition post STATUS from " . $old_status . " to " . $new_status );
            $nginx_purger->purgePost( $post->ID );
        }

        if ( 'future' == $new_status ) {
            if ( $post && 'future' == $post->post_status && 
               ( ( 'post' == $post->post_type || 'page' == $post->post_type ) || 
               ( isset( $this->options['custom_post_types_recognized'] ) &&
               in_array( $post->post_type, $this->options['custom_post_types_recognized'] ) ) ) ) {
                
                $nginx_purger->log( "Set/update future_posts option ( post id = " . $post->ID . " and blog id = " . $blog_id . " )" );
                $this->options['future_posts'][ $blog_id ][ $post->ID ] = strtotime( $post->post_date_gmt ) + 60;
                update_site_option( "rt_wp_nginx_helper_global_options", $this->options );
            }
        }
    }
    
    /**
     * Unset future post option on delete
     * @global type $blog_id
     * @global type $nginx_purger
     * @param type $post_id
     */
    public function unset_future_post_option_on_delete( $post_id ) {
        global $blog_id, $nginx_purger;
        
        if ( !$this->options['enable_purge'] ) {
            return;
        }
        
        if ( $post_id && !wp_is_post_revision( $post_id ) ) {
            if ( isset( $this->options['future_posts'][ $blog_id ][ $post_id ] ) &&
                    count( $this->options['future_posts'][ $blog_id ][ $post_id] ) ) {
                
                $nginx_purger->log( "Unset future_posts option ( post id = " . $post_id . " and blog id = " . $blog_id . " )" );
                unset( $this->options['future_posts'][ $blog_id ][ $post_id ] );
                update_site_option( "rt_wp_nginx_helper_global_options", $this->options );

                if ( !count( $this->options['future_posts'][ $blog_id ] ) ) {
                    unset( $this->options['future_posts'][ $blog_id ] );
                    update_site_option( "rt_wp_nginx_helper_global_options", $this->options );
                }
            }
        }
    }
    
    /**
     * Update map when new blog added in multisite.
     * @global type $nginx_purger
     * @param type $blog_id
     */
    public function update_new_blog_options( $blog_id ) {
        global $rt_wp_nginx_purger;
        
        $nginx_purger->log( "New site added ( id $blog_id )" );
        $this->update_map();
        $nginx_purger->log( "New site added to nginx map ( id $blog_id )" );
        $helper_options = $this->nginx_helper_default_settings();
        update_blog_option( $blog_id, "rt_wp_nginx_helper_options", $helper_options );
        $nginx_purger->log( "Default options updated for the new blog ( id $blog_id )" );
    }
    
    /**
     * Purge all urls.
     * @global type $nginx_purger
     */
    public function purge_all() {
        global $nginx_purger;
        
        if ( !isset( $_REQUEST['nginx_helper_action'] ) ) {
            return;
        }

        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( 'Sorry, you do not have the necessary privileges to edit these options.' );
        }

        $action = $_REQUEST['nginx_helper_action'];

        if ( 'done' == $action ) {
            add_action( 'admin_notices', array( &$this, 'display_notices' ) );
            add_action( 'network_admin_notices', array( &$this, 'display_notices' ) );
            return;
        }
        
        check_admin_referer( 'nginx_helper-purge_all' );

        switch ( $action ) {
            case 'purge':
                $nginx_purger->purgeAll();
                break;
        }
        
        wp_redirect( esc_url_raw( add_query_arg( array( 'nginx_helper_action' => 'done' ) ) ) );
    }
    
    /**
     * Dispay plugin notices.
     */
    public function display_notices() {
        echo '<div class="updated"><p>' . __( 'Purge initiated', 'nginx-helper' ) . '</p></div>';
    }
}
