<?php

namespace ES_Simple_Admin_Branding;

class Branding {

	/**
	 * Singleton instance.
	 *
	 * @var self
	 */
	private static $instance;
    
    /**
	 * Singleton get.
	 * @return this
	 */
	public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

	/**
	 * Initialize the class, loads hooks.
	 */
	public function __construct() {
		add_action( 'init', array($this,'load_textdomain') );
		add_action( 'login_head', array($this, 'wp_login_favicon' ) ); // Set the login favicon
		add_action( 'login_head', array($this, 'wp_login_logo' ) ); // Set the branded logo		
		add_action( 'password_protected_login_head', array($this, 'wp_login_logo' ) );
		add_action( 'login_footer', array($this, 'wp_print_footer_scripts' ), 20 ); // Add our footer
        
        // We want to place it higher in the header
        remove_action( 'wp_head', 'wp_site_icon', 99 );
		add_action( 'wp_head', 'wp_site_icon', 10 );
		
		add_filter( 'login_headerurl', array($this,'custom_login_url') );
		add_filter( 'login_message', array( $this, 'custom_login_message' ) );
		add_filter( 'login_headertext', array( $this, 'custom_login_headertext' ) );
		add_action( 'customize_register', array($this, 'customize_register_options') );
	}

	/**
	 * Load the text domain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'simple-admin-branding', false, SIMPLE_ADMIN_BRANDING_PLUGIN_PATH . '/languages' ); 
	}

	/**
	 * Register our customizer section/settings.
	 *
	 * @param WP_Customize_Manager $wp_customize
	 * @return void
	 */
	public function customize_register_options( $wp_customize ) {
		$wp_customize->add_section( 
			'simple_wplogin_settings', 
			array(
				'title'				   => __( 'Simple Admin Branding', 'simple-admin-branding' ),
				'priority'			 => 100,
			) 
		);
	  
		$wp_customize->add_setting( 
			'simple_wplogin_logo', 
			array(
			'transport'         => 'postMessage'
			) 
		);
		$wp_customize->add_setting(
			'simple_wplogin_bg_color', 
			array(
				'default' => '#f1f1f1',
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);
		$wp_customize->add_setting(
			'simple_wplogin_text_color', 
			array(
				'default' => '#3c434a',
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);
		$wp_customize->add_setting( 
			'simple_wplogin_logo_header_msg', 
			array(
				'default'				=> "",
				'type'					=> 'option',
				'autoload'				=> false,
				'transport'				=> 'postMessage',
				'sanitize_callback'		=> 'wp_kses_post'
			) 
		);
		$wp_customize->add_setting( 
			'simple_wplogin_logo_footer', 
			array(
				/* translators: 1: current year, 2: Site name. */
				'default'				=> sprintf( __('&copy; %1$s %2$s. All Rights Reserved.', 'simple-admin-branding'), '{year}' , get_bloginfo('name') ),
				'type'					=> 'option',
				'autoload'				=> false,
				'transport'				=> 'postMessage',
				'sanitize_callback'		=> 'wp_kses_post'
			) 
		);
		$wp_customize->add_control(
		   new \WP_Customize_Color_Control(
			   $wp_customize,
			   'simple_wplogin_bg_color',
			   array(
				   'label'      => __( 'Login Background Color', 'simple-admin-branding' ),
				   'section'    => 'simple_wplogin_settings',
				   'settings'   => 'simple_wplogin_bg_color',
				   'priority'	=> 100, 
			   )
		   )
		);
		$wp_customize->add_control(
			new \WP_Customize_Color_Control(
				$wp_customize,
				'simple_wplogin_text_color',
				array(
					'label'      => __( 'Login Text Color', 'simple-admin-branding' ), 
					'section'    => 'simple_wplogin_settings',
					'settings'   => 'simple_wplogin_text_color',
					'priority'	=> 105, 
				)
			)
		 );
		 $wp_customize->add_control( new \WP_Customize_Cropped_Image_Control( $wp_customize, 'simple_wplogin_logo', array(
			'label'             => __( 'Login Logo', 'simple-admin-branding' ),
			'description'       => __( 'Overrides the default WordPress site logo, specifically for the login page.', 'simple-admin-branding' ),
			'flex_width'        => true, 
			'flex_height'       => true,
			'width'             => 200,
			'height'            => 200,
			'priority'			=> 110, 
			'settings'          => 'simple_wplogin_logo',
			'section'    		=> 'simple_wplogin_settings',
		) ) );
		$wp_customize->add_control( 'simple_wplogin_logo_header_msg', array(
			'label'				=> __( 'Login Message', 'simple-admin-branding' ),
			'description'		=>  __( 'In between the logo and login box. HTML tags supported.', 'simple-admin-branding' ),
			'type'				=> 'textarea',
			'section'			=> 'simple_wplogin_settings',
			'priority'			=> 113,
			'settings'			=> 'simple_wplogin_logo_header_msg'
		) );
		$wp_customize->add_control( 'simple_wplogin_logo_footer', array(
			'label'				=> __( 'Footer Text', 'simple-admin-branding' ),
			/* translators: 1: current year. */
			'description'		=>  sprintf( __( '%1$s for dynamic year replacement. HTML tags supported.', 'simple-admin-branding' ), '<code>{year}</code>' ),
			'type'				=> 'textarea',
			'section'			=> 'simple_wplogin_settings',
			'priority'			=> 115,
			'settings'			=> 'simple_wplogin_logo_footer'
		) );
	
	}

	/**
	 * Replace the PoweredByWordPress with the site name.
	 *
	 * @param string $header_text
	 * @return string
	 */
	public function custom_login_headertext( $header_text = '' ) {
		return get_bloginfo('name');
	}

	/**
	 * Maybe append a custom login message.
	 *
	 * @param string $message
	 * @return string
	 */
	public function custom_login_message( $message = '' ) {
		$header_option = get_option('simple_wplogin_logo_header_msg');
		if (!empty($header_option)) {
			$message = sprintf('<div class="login-message">%s</div>', wp_kses_post( $header_option) );
		}
		return $message;
	}

	/**
	 * Replace logo URL with siteurl.
	 *
	 * @param string $url
	 * @return string
	 */
	public function custom_login_url($url) {
		return get_site_url();
	}

	/**
	 * Set the favicon for our login page
	 * @return void
	 */
	public function wp_login_favicon()
	{
		$favicon_url = esc_url( get_site_icon_url( 32 ) );
		if (!empty($favicon_url)) {
			echo '<link rel="shortcut icon" href="' . esc_url( $favicon_url ) . '" />';
		}
	}

	/**
	 * Set the custom CSS for the login page.
	 * @return void
	 */
	public function wp_login_logo() 
	{ 	
		$override_logo = get_theme_mod('simple_wplogin_logo');
		if (!empty($override_logo)) {
			$logo = wp_get_attachment_image_src( $override_logo , 'full' );	
			$url = !empty( $logo[0] ) ? esc_url( $logo[0] ) : '';
		} else if ( has_custom_logo() ) {
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			$logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
			$url = !empty( $logo[0] ) ? esc_url( $logo[0] ) : '';
		} else {
			$url = esc_url( get_site_icon_url( 270 ) );
		}

		$bg_color = get_theme_mod('simple_wplogin_bg_color', '#f1f1f1');
		$text_color = get_theme_mod('simple_wplogin_text_color', '#3c434a');
		ob_start();
	?>
    <style type="text/css">
    	body, body.interim-login {
    		background-color: <?php echo esc_attr( $bg_color ); ?>;
			color: <?php echo esc_attr( $text_color ); ?>;
    	}
		.login #nav, .login #backtoblog {
			padding: 0 12px 0;
		}
		.login #nav a, .login #backtoblog a {
			color: <?php echo esc_attr( $text_color ); ?>;
			text-decoration: underline;
		}
        #login h1 a {
            background-image: url('<?php echo esc_url( $url ) ?>');
			background-size: contain;
            margin-bottom: 20px;
            background-size: contain;
            width: 280px;
        }
        #login {
        	max-width: 400px;
        }
        .interim-login #login {
        	width: 320px;
        }
        #loginform, #lostpasswordform {
        	border-radius: 0px;
			border: none;
			
			box-shadow: 2px 1px 6px rgb(0 0 0 / 47%);
			<?php if ( strtolower($bg_color) !== '#f1f1f1' ) : ?>
			padding: 26px 0 34px;
			box-shadow: none !important;
			background-color: <?php echo esc_attr( $bg_color ); ?>;
			color: <?php echo esc_attr( $text_color ); ?>;
			<?php endif; ?>	
        }
		.admin-email-confirm-form, .message, #login_error {
			color: #000;
		}
		#login_error, .message {
			margin-top: 10px;
		}
        .wp-core-ui .button-primary {
        	padding: 0 20px 2px !important;
        	height: 35px !important;
        	border: none !important;
        	border-radius: 6px !important;
        }
        p#nav, p#backtoblog {
        	display: inline-block;
        }
        .interim-login .powered-by {
        	display: none;
        }
        .powered-by {
			<?php if ( strtolower($bg_color) !== '#f1f1f1' ) : ?>
			box-shadow: none !important;
			background-color: <?php echo esc_attr( $bg_color ); ?>;
			color: <?php echo esc_attr( $text_color ); ?>;
			<?php else : ?>
			background-color: #FFF;
			color: #000;
			box-shadow: 2px 1px 6px rgb(0 0 0 / 47%);
			<?php endif; ?>	

        	padding: 8px 0;
        	text-align: center;
        	font-weight: 600;        	
        	font-size: 12px;
        	width: 100%;
        	position: fixed;
        	bottom: 0;
        }
        .powered-by a {
        	color: #000;
        	text-decoration: none;
        }
        .powered-by a {
        	text-decoration: underline;
        }
		.g-recaptcha {
			margin: 12px auto;
			text-align: center;
			padding-bottom: 14px;
		}
		.g-recaptcha > div {
			margin: 0 auto;
		}
		.nsl-container {
			text-align: center;
		}
    </style>
	<?php 
		echo ob_get_clean();
	}

	/**
	 * Add our Footer to the login page
	 * @return void
	 */
	public function wp_print_footer_scripts() {
		$footer_text = get_option('simple_wplogin_logo_footer');
		if (empty($footer_text)) {
			return;
		}
		$footer_text = str_ireplace('{year}', wp_date('Y'), $footer_text);
		?>
		<div class="powered-by">
		  <div class="container">
		  	<div class="row">
		      <div class="col-lg-6 col-sm-6 col-xs-12">
		        <span id="copyright"><?php echo wp_kses_post( $footer_text ) ?></span>
		      </div>
		    </div>  
		  </div>
		</div>
		<?php
	}
}