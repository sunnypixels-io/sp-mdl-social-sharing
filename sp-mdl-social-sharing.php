<?php
/**
 * Plugin Name:            MDL Social Sharing
 * Plugin URI:            https://sunnypixels.io/extension/mdl-social-sharing/
 * Description:            A simple plugin to add social share buttons to your posts.
 * Version:                1.0.0
 * Author:                SunnyPixels
 * Author URI:            https://sunnypixels.io/
 * Requires at least:    4.5.0
 * Tested up to:        5.2.2
 *
 * Text Domain: sp-mdl-social-sharing
 * Domain Path: /languages/
 *
 * @package SP_MDL_Social_Sharing
 * @category Core
 * @author SunnyPixels
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Returns the main instance of SP_MDL_Social_Sharing to prevent the need to use globals.
 *
 * @return object SP_MDL_Social_Sharing
 * @since  1.0.0
 */
function SP_MDL_Social_Sharing()
{
    return SP_MDL_Social_Sharing::instance();
}

SP_MDL_Social_Sharing();

/**
 * @class   SP_MDL_Social_Sharing
 * @version 1.0.0
 * @since   1.0.0
 * @package SP_MDL_Social_Sharing
 */
final class SP_MDL_Social_Sharing
{
    /**
     * SP_MDL_Social_Sharing The single instance of SP_MDL_Social_Sharing.
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $_instance = null;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $token;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $version;

    // Admin - Start
    /**
     * The admin object.
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $admin;

    /**
     * Constructor function.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function __construct($widget_areas = array())
    {
        $this->token = 'sp-mdl-social-sharing';
        $this->plugin_url = plugin_dir_url(__FILE__);
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->version = '1.0.0';

        register_activation_hook(__FILE__, array($this, 'install'));

        add_action('init', array($this, 'load_plugin_textdomain'));

        add_action('init', array($this, 'setup'));

        // TODO create plugin updater API
        // add_action('init', array($this, 'updater'), 1);
    }

    /**
     * Main SP_MDL_Social_Sharing Instance
     *
     * Ensures only one instance of SP_MDL_Social_Sharing is loaded or can be loaded.
     *
     * @return Main SP_MDL_Social_Sharing instance
     * @see SP_MDL_Social_Sharing()
     * @since 1.0.0
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance))
            self::$_instance = new self();
        return self::$_instance;
    }

    /**
     * Load the localisation file.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain('sp-mdl-social-sharing', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), '1.0.0');
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), '1.0.0');
    }

    /**
     * Installation.
     * Runs on activation. Logs the version number and assigns a notice message to a WordPress option.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function install()
    {
        $this->_log_version_number();
    }

    /**
     * Log the plugin version number.
     * @access  private
     * @return  void
     * @since   1.0.0
     */
    private function _log_version_number()
    {
        // Log the version number.
        update_option($this->token . '-version', $this->version);
    }

    /**
     * Setup all the things.
     * Only executes if Material Design Lite or a child theme using Material Design Lite as a parent is active and the extension specific filter returns true.
     * @return void
     */
    public function setup()
    {
        $theme = wp_get_theme();

        if ('Material Design Lite' == $theme->name || 'material-design-lite' == $theme->template) {
            //add_filter('sp_localize_array', array($this, 'localize_array'));
            add_action('wp_enqueue_scripts', array($this, 'public_scripts'), 10);

            add_action('material_design_lite_share_buttons_top', array($this, 'get_share_buttons_top'), 10);
            add_action('material_design_lite_share_buttons_footer', array($this, 'get_share_buttons_footer'), 10);
            add_action('wp_footer', array($this, 'get_share_buttons_floating'), 10);

            // if (is_admin()) {
            //     add_action('admin_enqueue_scripts', array($this, 'admin_scripts'), 999);
            // }

            $this->_kirki_customizer();
        }
    }

    /**
     * Customizer Controls and settings
     *
     * @since 1.0.0
     */
    private function _kirki_customizer()
    {
        if (class_exists('Kirki')) {
            /**
             * AddThis Tools
             * @since 1.0.0
             */
            Kirki::add_section('material_design_lite_theme_addthis_tools', array(
                'title' => esc_html__('Social Sharing - AddThis Tools', 'material-design-lite'),
                'description' => esc_html__('AddThis free website tools include share buttons, targeting tools and content recommendations help you get more likes, shares and followers and keep them ...', 'material-design-lite'),
                'priority' => 198,
            ));

            Kirki::add_field('material_design_lite', [
                'type' => 'custom',
                'settings' => 'addthis_tools_title_dashboard_link',
                'section' => 'material_design_lite_theme_addthis_tools',
                'default' => __('Dashboard:', 'material-design-lite') . ' <a href="https://www.addthis.com/dashboard" target="_blank">www.addthis.com</a>',
                'priority' => 110,
            ]);

            Kirki::add_field('material_design_lite', [
                'type' => 'toggle',
                'settings' => 'addthis_tools_enable',
                'label' => esc_html__('Enable AddThis scripts', 'material-design-lite'),
                'section' => 'material_design_lite_theme_addthis_tools',
                'default' => '1',
                'priority' => 120,
            ]);

            Kirki::add_field('material_design_lite', [
                'type' => 'text',
                'settings' => 'addthis_tools_pubid',
                'label' => esc_html__('Pub ID', 'material-design-lite'),
                'description' => esc_html__('Use your own PubID to keep tracking all your sharing activity and analytics.', 'material-design-lite'),
                'section' => 'material_design_lite_theme_addthis_tools',
                'default' => '',
                'priority' => 121,
                'active_callback' => [
                    [
                        'setting' => 'addthis_tools_enable',
                        'operator' => '==',
                        'value' => true,
                    ]
                ],
            ]);

            Kirki::add_field('material_design_lite', [
                'type' => 'custom',
                'settings' => 'addthis_tools_title_share_buttons',
                'section' => 'material_design_lite_theme_addthis_tools',
                'default' => kirki_custom_title('Share buttons'),
                'priority' => 125,
                'active_callback' => [
                    [
                        'setting' => 'addthis_tools_enable',
                        'operator' => '==',
                        'value' => true,
                    ]
                ],
            ]);

            Kirki::add_field('material_design_lite', [
                'type' => 'toggle',
                'settings' => 'addthis_tools_share_buttons_floating',
                'label' => esc_html__('Show floating share buttons', 'material-design-lite'),
                'section' => 'material_design_lite_theme_addthis_tools',
                'default' => '1',
                'priority' => 126,
                'active_callback' => [
                    [
                        'setting' => 'addthis_tools_enable',
                        'operator' => '==',
                        'value' => true,
                    ]
                ],
            ]);

            Kirki::add_field('material_design_lite', [
                'type' => 'custom',
                'settings' => 'addthis_tools_title_sharing_services',
                'section' => 'material_design_lite_theme_addthis_tools',
                'default' => kirki_custom_title('Sharing Services'),
                'priority' => 130,
                'active_callback' => [
                    [
                        'setting' => 'addthis_tools_enable',
                        'operator' => '==',
                        'value' => true,
                    ]
                ],
            ]);

            Kirki::add_field('material_design_lite', [
                'type' => 'toggle',
                'settings' => 'addthis_tools_compact',
                'label' => esc_html__('Show + icon for the AddThis sharing menu', 'material-design-lite'),
                'section' => 'material_design_lite_theme_addthis_tools',
                'default' => '1',
                'priority' => 140,
                'active_callback' => [
                    [
                        'setting' => 'addthis_tools_enable',
                        'operator' => '==',
                        'value' => true,
                    ]
                ],
            ]);

            Kirki::add_field('material_design_lite', [
                'type' => 'toggle',
                'settings' => 'addthis_tools_smart_sorting',
                'label' => esc_html__('Smart Sorting by AddThis', 'material-design-lite'),
                'section' => 'material_design_lite_theme_addthis_tools',
                'default' => '1',
                'priority' => 141,
                'active_callback' => [
                    [
                        'setting' => 'addthis_tools_enable',
                        'operator' => '==',
                        'value' => true,
                    ]
                ],
            ]);

            Kirki::add_field('material_design_lite', [
                'type' => 'custom',
                'settings' => 'addthis_tools_smart_sorting_enabled',
                'section' => 'material_design_lite_theme_addthis_tools',
                'default' => __('Each of your website visitors will see the social networks that they interact with most frequently.', 'material-design-lite'),
                'priority' => 142,
                'active_callback' => [
                    [
                        'setting' => 'addthis_tools_enable',
                        'operator' => '==',
                        'value' => true,
                    ], [
                        'setting' => 'addthis_tools_smart_sorting',
                        'operator' => '==',
                        'value' => true,
                    ]
                ],
            ]);

            Kirki::add_field('material_design_lite', [
                'type' => 'custom',
                'settings' => 'addthis_tools_smart_sorting_disabled',
                'section' => 'material_design_lite_theme_addthis_tools',
                'default' => __('Select your own social networks and customize the order.', 'material-design-lite'),
                'priority' => 144,
                'active_callback' => [
                    [
                        'setting' => 'addthis_tools_enable',
                        'operator' => '==',
                        'value' => true,
                    ], [
                        'setting' => 'addthis_tools_smart_sorting',
                        'operator' => '==',
                        'value' => false,
                    ]
                ],
            ]);

            Kirki::add_field('material_design_lite', [
                'type' => 'sortable',
                'settings' => 'addthis_tools_sharing_services',
                //'label'       => esc_html__( 'This is the label', 'material-design-lite' ),
                'description' => esc_html__('Try to select not more 10 services :)', 'material-design-lite'),
                'section' => 'material_design_lite_theme_addthis_tools',
                'default' => [
                    'facebook',
                    'google',
                    'twitter',
                    'print',
                    'email'
                ],
                'choices' => $this->addthis_get_share_vendors('all', 'customizer'),
                'priority' => 149,
                'active_callback' => [
                    [
                        'setting' => 'addthis_tools_enable',
                        'operator' => '==',
                        'value' => true,
                    ], [
                        'setting' => 'addthis_tools_smart_sorting',
                        'operator' => '==',
                        'value' => false,
                    ]
                ],
            ]);
        }
    }

    public function addthis_get_share_vendors($vendors, $return)
    {
        require_once $this->plugin_path . '/includes/addthis-share.php';

        return addthis_get_share_vendors($vendors, $return);
    }

    /**
     * Addthis share buttons
     *
     * @@link https://www.addthis.com/academy/customizing-the-addthis-toolbox/
     */
    public function get_share_buttons_top()
    {
        $content = '';

        if (is_404() || !get_theme_mod('addthis_tools_enable', true))
            return $content;

        if (get_theme_mod('addthis_tools_share_buttons_top', true)) :
            ob_start();
            ?>
            <div class="meta__share mdl-share-buttons mdl-share-buttons--top">
                <button class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon mdl-js-share-this">
                    <i class="material-icons" role="presentation">share</i>
                    <span class="visuallyhidden">share</span>
                </button>

                <div class="mdl-share-buttons__wrapper addthis_toolbox_wrapper">
                    <div class="addthis_toolbox addthis_default_style addthis_32x32_style addthis_mdl_style">
                        <?php
                        foreach ($this->_get_share_vendors() as $vendor) {
                            echo '<a class="addthis_toolbox__item addthis_button_' . $vendor . '"></a>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
            $content = ob_get_contents();
            ob_end_clean();
        endif;

        echo $content;
    }

    public function get_share_buttons_footer()
    {
        $content = '';

        if (is_404() || !get_theme_mod('addthis_tools_enable', true))
            return $content;

        if (get_theme_mod('addthis_tools_share_buttons_footer', true)) :
            $buttons_style = get_theme_mod('share_buttons_style_footer', 'addthis_mdl_style');
            ob_start();
            ?>
            <div class="mdl-share-buttons mdl-share-buttons--footer mdl-page-links mdl-card__actions mdl-card--border">
                        <span class="mdl-button mdl-js-button mdl-share-buttons__title" disabled>
                            <?php _e('Share:', 'material-design-lite'); ?>
                        </span>

                <div class="mdl-share-buttons__wrapper addthis_toolbox_wrapper">
                    <div class="addthis_toolbox addthis_default_style addthis_32x32_style <?php echo $buttons_style; ?>">
                        <?php
                        foreach ($this->_get_share_vendors() as $vendor) {
                            echo '<a class="addthis_toolbox__item addthis_button_' . $vendor . '"></a>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
            $content = ob_get_contents();
            ob_end_clean();
        endif;

        echo $content;
    }

    public function get_share_buttons_floating()
    {
        $content = '';

        if (is_404() || !get_theme_mod('addthis_tools_enable', true))
            return $content;

        if (get_theme_mod('addthis_tools_share_buttons_floating', true)) :
            ob_start();
            ?>
            <div class="mdl-share-buttons mdl-share-buttons--floating">
                <button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored">
                    <i class="material-icons">share</i>
                </button>

                <div class="mdl-share-buttons__wrapper addthis_toolbox_wrapper">
                    <div class="addthis_toolbox addthis_default_style addthis_32x32_style"
                         for="ami-share-buttons">
                        <?php
                        foreach (array_reverse($this->_get_share_vendors()) as $vendor) {
                            echo '<a class="addthis_toolbox__item addthis_button_' . $vendor . '"></a>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
            $content = ob_get_contents();
            ob_end_clean();
        endif;

        echo $content;
    }

    private function _get_share_vendors()
    {
        $sharing_services = array(
            'preferred_1',
            'preferred_2',
            'preferred_3',
            'preferred_4',
            'preferred_5',
        );

        if (!get_theme_mod('addthis_tools_smart_sorting', true))
            $sharing_services = get_theme_mod('addthis_tools_sharing_services', array('facebook', 'google', 'twitter', 'print', 'email'));

        if (get_theme_mod('addthis_tools_compact', true))
            array_push($sharing_services, 'compact');

        return $sharing_services;
    }

    /**
     * Enqueue public styles and scripts.
     *
     * @since 1.0.0
     */
    public function public_scripts()
    {
        // Load main stylesheet
        wp_enqueue_style('oss-social-share-style', plugins_url('/assets/css/public.css', __FILE__));

        // Load AddThis tools
        wp_enqueue_script('addthis', 'https://s7.addthis.com/js/300/addthis_widget.js', array(), null, true);
        wp_localize_script('addthis', 'addthis_config', array(
            'pubid' => !empty(get_theme_mod('addthis_tools_pubid')) ? get_theme_mod('addthis_tools_pubid', 'ra-5ce076c5b50856e4') : 'ra-5ce076c5b50856e4'
        ));

        // Load main script
        wp_enqueue_script('sp-mdl-social-share-script', plugins_url('/assets/js/public.js', __FILE__), array('jquery'), $this->version, true);
    }

    /**
     * Enqueue admin styles and scripts.
     *
     * @since 1.0.0
     */
    public function admin_scripts()
    {
        // Load main stylesheet
        wp_enqueue_style('sp-mdl-style', plugins_url('/assets/css/admin.css', __FILE__));

        // Load custom js methods.
        wp_enqueue_script('sp-mdl-social-share-js-admin', plugins_url('/assets/js/admin.js', __FILE__), array('jquery'), null, true);
    }

}