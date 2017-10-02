<?php
/**
* Plugin Name:		 WordPress Grayscale
* Plugin URI:		 https://www.betteridea.co.th
* Description:		 ปลั้กอินสำหรับเปลี่ยนสีเว็บไซต์ที่ใช้ WordPress เป็นสีขาวดำ 
* Version:			 1.0.3
* Author:			 Q_Q
* Author 			 URI: https://www.betteridea.co.th
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       wp-grayscale
* Domain Path:       /languages
*/



add_action( 'wp_enqueue_scripts', 'wp_grayscale_styles' );

function load_jquery() {
    if ( ! wp_script_is( 'jquery', 'enqueued' )) {

        //Enqueue
        wp_enqueue_script( 'jquery' );

    }
}
add_action( 'wp_enqueue_scripts', 'load_jquery' );

function add_black_ribbon() {
    echo "<img src='" . plugins_url( 'img/black-ribbon.png', __FILE__ ) . "' class='black-ribbon' />";
}

function add_remove_grayscale() {

    $local_key = str_replace(".", "_", $_SERVER["SERVER_NAME"]);

    echo "<div class=\"remove-filter\"><a href=\"#\" class=\"btn-remove-filter\">ปิดโหมดสีเทา</a></div>";
    echo '

    <script>
    
    jQuery(document).ready(function($) {

        var is_grayscale = localStorage.getItem("' . $local_key . '_wp_easy_grayscale");

        if(is_grayscale==1) {
            clear_grayscale();
        }

        $( ".btn-remove-filter" ).click(function(e) {
            e.preventDefault();
            clear_grayscale();
            localStorage.setItem("' . $local_key . '_wp_easy_grayscale", 1);
        });

        function clear_grayscale() {
            $("html").addClass("no-grayscale");
            $(".remove-filter").remove();
        }

    });   

    </script>    
    ';
}

function wp_grayscale_styles() {
	if(!is_admin()) {
		$option = get_option( 'wp_grayscale_option' );

		if(($option) and ($option!==null) and !empty($option)):
			$percent = $option['percent_number'];
			$percent_divide = $percent / 100;
		else:
			$percent = 40;
			$percent_divide = 4;
        endif;
        

		wp_enqueue_style(
			'wp-grayscale',
            plugin_dir_url( __FILE__ ) . 'css/wp-grayscale.css', array(),
            '1.1.2.000001',
            'all'
        );

        $custom_css = "html {
				/* IE */
				filter: progid:DXImageTransform.Microsoft.BasicImage(grayscale=$percent_divide);
				/* Chrome, Safari */
				-webkit-filter: grayscale($percent_divide);
				/* Firefox */
				filter: grayscale($percent_divide);
				filter: grayscale($percent%);
				filter: gray; 
				-moz-filter: grayscale($percent%);
				-webkit-filter: grayscale($percent%);
			}";
        wp_add_inline_style( 'wp-grayscale', $custom_css );
        
        if(isset( $option['wp_ribbon'] )) {
            add_action('wp_footer', 'add_black_ribbon');
        }

        add_action('wp_footer', 'add_remove_grayscale');
	}
}

class WP_Grayscale_Page
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'WP Grayscale', 
            'manage_options', 
            'wp-grayscale', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'wp_grayscale_option' );
        ?>
        <div class="wrap">
            <h1>WordPress Grayscale</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );
                do_settings_sections( 'wp-grayscale' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'my_option_group', // Option group
            'wp_grayscale_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            '', // Title
            array( $this, 'print_section_info' ), // Callback
            'wp-grayscale' // Page
        );  

        add_settings_field(
            'percent_number', // ID
            'ค่าสีขาวดำ (1-100%)', // Title 
            array( $this, 'percent_number_callback' ), // Callback
            'wp-grayscale', // Page
            'setting_section_id' // Section           
        );

        add_settings_field(
            'wp_ribbon', // ID
            'ริบบิ้น', // Title 
            array( $this, 'wp_ribbon_callback' ), // Callback
            'wp-grayscale', // Page
            'setting_section_id' // Section           
        );

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['percent_number'] ) )
            $new_input['percent_number'] = absint( $input['percent_number'] );
        if( isset( $input['wp_ribbon'] ) )
            $new_input['wp_ribbon'] = absint( $input['wp_ribbon'] );

        return $new_input;
    }

    public function print_section_info()
    {
        print 'ปรับค่าสีขาวดำของเว็บไซต์:';
    }

    public function percent_number_callback()
    {
        printf(
            '<input type="text" id="percent_number" name="wp_grayscale_option[percent_number]" value="%s" />',
            isset( $this->options['percent_number'] ) ? esc_attr( $this->options['percent_number']) : '40'
        );
    }

    public function wp_ribbon_callback()
    {
        printf(
            '<input type="checkbox" id="wp_ribbon" name="wp_grayscale_option[wp_ribbon]" %s />',
            isset( $this->options['wp_ribbon'] ) ? 'checked' : ''
        );
    }

}

if( is_admin() )
    $my_settings_page = new WP_Grayscale_Page();