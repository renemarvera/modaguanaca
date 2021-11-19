<?php
/*
* QuantumCloud Promo + Support Page
* Revised On: 06-01-2017
*/

/*******************************
 * Add Ajax Object at the head part
 *******************************/
add_action('wp_head', 'qc_process_support_form_ajax_header');

if( !function_exists('qc_process_support_form_ajax_header') )
{
	function qc_process_support_form_ajax_header() 
	{

	   echo '<script type="text/javascript">
	           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
	         </script>';

	} //End of qc_process_support_form_ajax_header

} //End of function_exists

/*******************************
 * Handle Ajex Request for Form Processing
 *******************************/
add_action( 'wp_ajax_process_qc_promo_form', 'process_qc_promo_form' );

if( !function_exists('process_qc_promo_form') )
{
	function process_qc_promo_form()
	{
		
		$data['status'] = 'failed';
		$data['message'] = __('Problem in processing your form submission request! Apologies for the inconveniences.<br> 
Please email to <span style="color:#22A0C9;font-weight:bold !important;font-size:14px "> quantumcloud@gmail.com </span> with any feedback. We will get back to you right away!', 'quantumcloud');

		$name = trim(sanitize_text_field($_POST['post_name']));
		$email = trim(sanitize_email($_POST['post_email']));
		$subject = trim(sanitize_text_field($_POST['post_subject']));
		$message = trim(sanitize_text_field($_POST['post_message']));
		$plugin_name = trim(sanitize_text_field($_POST['post_plugin_name']));

		if( $name == "" || $email == "" || $subject == "" || $message == "" )
		{
			$data['message'] = 'Please fill up all the requried form fields.';
		}
		else if ( filter_var($email, FILTER_VALIDATE_EMAIL) === false ) 
		{
			$data['message'] = 'Invalid email address.';
		}
		else
		{

			//build email body

			$bodyContent = "";
				
			$bodyContent .= "<p><strong>Support Request Details:</strong></p><hr>";

			$bodyContent .= "<p>Name : ".$name."</p>";
			$bodyContent .= "<p>Email : ".$email."</p>";
			$bodyContent .= "<p>Subject : ".$subject."</p>";
			$bodyContent .= "<p>Message : ".$message."</p>";

			$bodyContent .= "<p>Sent Via the Plugin: ".$plugin_name."</p>";

			$bodyContent .="<p></p><p>Mail sent from: <strong>".get_bloginfo('name')."</strong>, URL: [".get_bloginfo('url')."].</p>";
			$bodyContent .="<p>Mail Generated on: " . date("F j, Y, g:i a") . "</p>";			
			
			$toEmail = "quantumcloud@gmail.com"; //Receivers email address
			//$toEmail = "qc.kadir@gmail.com"; //Receivers email address

			//Extract Domain
			$url = get_site_url();
			$url = parse_url($url);
			$domain = $url['host'];
			

			$fakeFromEmailAddress = "wordpress@" . $domain;
			
			$to = $toEmail;
			$body = $bodyContent;
			$headers = array();
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			$headers[] = 'From: '.$name.' <'.$fakeFromEmailAddress.'>';
			$headers[] = 'Reply-To: '.$name.' <'.$email.'>';

			$finalSubject = "From Plugin Support Page: " . $subject;
			
			$result = wp_mail( $to, $finalSubject, $body, $headers );

			if( $result )
			{
				$data['status'] = 'success';
				$data['message'] = __('Your email was sent successfully. Thanks!', 'quantumcloud');
			}

		}

		ob_clean();

		
		echo json_encode($data);
	
		die();
	}
}





/*******************************
 * Main Class to Display Support
 * form and the promo pages
 *******************************/

if( !class_exists('QcSupportAndPromoPage') ){


	class QcSupportAndPromoPage{
	
		public $plugin_menu_slug = "";
		public $plugin_slug = ""; //Should be unique, like: qcsld_p123
		public $promo_page_title = 'More WordPress Goodies for You!';
		public $promo_menu_title = 'Support';
		public $plugin_name = 'WooWBot WooCommerce Chatbot';
		
		public $page_slug = "";
		
		public $relative_folder_url;
		
		//public $relative_folder_url = plugin_dir_url( __FILE__ );
		
		function __construct( $plugin_slug = null )
		{
			/*
			if(!function_exists('wp_get_current_user')) {
				include(ABSPATH . "wp-includes/pluggable.php"); 
			}
			*/
			
			$this->page_slug = 'qcpro-promo-page-' . $plugin_slug;
			$this->relative_folder_url = plugin_dir_url( __FILE__ );
			
			add_action('admin_enqueue_scripts', array(&$this, 'include_promo_page_scripts'));
			
			//add_action( 'wp_ajax_process_qc_promo_form', array(&$this,'process_qc_promo_form') );
			
		} //End of Constructor
		
		function include_promo_page_scripts( $hook )
		{                                 
		   
		   wp_enqueue_script( 'jquery' );
		   wp_enqueue_script( 'jquery-ui-core');
		   wp_enqueue_script( 'jquery-ui-tabs' );
		   wp_enqueue_script( 'jquery-custom-form-processor', $this->relative_folder_url . '/js/support-form-script.js',  array('jquery', 'jquery-ui-core','jquery-ui-tabs') );
		   
		}
		
		function show_promo_page()
		{

			if( $this->plugin_menu_slug == "" ){
			   return;
			}
			
			add_action( 'admin_menu', array(&$this, 'show_promo_page_callback_func') );
			
		  
		} //End of function show_promo_page
		
		/*******************************
		 * Callback function to add the menu
		 *******************************/
		function show_promo_page_callback_func()
		{
			add_submenu_page(
				$this->plugin_menu_slug,
				$this->promo_page_title,
				$this->promo_menu_title,
				'manage_options',
				$this->page_slug,
				array(&$this, 'qcpromo_support_page_callback_func' )
			);
		} //show_promo_page_callback_func
		
		/*******************************
		 * Callback function to show the HTML
		 *******************************/
		function qcpromo_support_page_callback_func()
		{
			wp_enqueue_style( 'qcpd-google-font-lato', 'https://fonts.googleapis.com/css?family=Lato' );
			wp_enqueue_style( 'qcpd-font-awesome', $this->relative_folder_url . '/css/font-awesome.min.css' );
			wp_enqueue_style( 'qcpd-style', $this->relative_folder_url . '/css/style.css' );
			wp_enqueue_style( 'qcpd-responsive', $this->relative_folder_url . '/css/responsive.css' );
		?>
				
				<div class="qc_support_container"><!--qc_support_container-->
	
    <div class="qc_tabcontent clearfix-div">
    	<div class="qc-row">
            <div class="support-btn-main clearfix-div">
                 <div class="qc-column-12">
					<h4>Bug report, feature request or any feedback – we are here for you.</h4>
                    <div class="support-btn">
                        <a class="free-support" href="https://www.quantumcloud.com/resources/free-support/">Free Support</a>
                    </div>
                </div>
            
                <div class="qc-column-12">
					<h4>All our Pro Version users get Premium, Guaranteed Quick, One on One Priority Support.</h4>
                    <div class="support-btn">
                        <a class="premium-support" href="https://qc.ticksy.com/">GET PRIORITY SUPPORT</a>
                    </div>
					<div class="support-btn">
                        <a class="premium-support" href="https://www.quantumcloud.com/products/woocommerce-chatbot-woowbot/">Upgrade to Pro</a>
                    </div>
                </div>

            </div>
			<h2 class="plugin-title" style="text-align: center;margin-bottom: 60px;">Check Out Some of Our Other Works that Might Make Your Website Better</h2>
            <div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/infographic-maker-ilist/"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/plugin-images/iList-icon-256x256.png'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/infographic-maker-ilist/">InfoGraphic Maker – iList</a></h4>
                        <p>iList is first of its kind <strong>InfoGraphic maker</strong> WordPress plugin to create Infographics and elegant Lists effortlessly to visualize data. 
                        It is a must have content curation tool.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
            
            <div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/slider-hero"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/plugin-images/slider-hero-icon-256x256.png'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/slider-hero/">Slider Hero</a></h4>
                        <p>Slider Hero is a unique slider plugin that allows you to create <strong>Cinematic Product Intro Adverts</strong> and 
                        <strong>Hero sliders</strong> with great Javascript animation effects.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
            
            
            <div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/simple-link-directory/"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/plugin-images/sld-icon-256x256.png'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/simple-link-directory/">Simple Link Directory</a></h4>
                        <p>Directory plugin with a unique approach! Simple Link Directory is an advanced WordPress Directory plugin for One Page 
                        directory and Content Curation solution.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
            
            
            <div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/simple-business-directory/"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/plugin-images/icon.png'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/simple-business-directory/">Simple Business Directory</a></h4>
                        <p>This innovative and powerful, yet<strong> Simple &amp; Multi-purpose Business Directory</strong> WordPress PlugIn allows you to create 
                        comprehensive Lists of Businesses with maps and tap to call features.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
            
            <div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/woocommerce-chatbot-woowbot/"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/chatbot.jpg'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/woocommerce-chatbot-woowbot/">WPBot Pro</a></h4>
                        <p>WPBot Pro is a stand alone Chat Bot plugin for WordPress with AI powered by Dialogflow . You can also use it without DialogFlow and simply install and get it to work as a floating HelpDesk.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
			
			<div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/woocommerce-chatbot-woowbot/"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/plugin-images/logo.png'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/woocommerce-chatbot-woowbot/">WoowBot WooCommerce ChatBot</a></h4>
                        <p>WooWBot is a stand alone WooCommerce Chat Bot with zero configuration. This plug and play chatbot also does not require 
                        any 3rd party service integration like Facebook.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
            
            
            <div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/woocommerce-shop-assistant-jarvis/"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/plugin-images/jarvis-icon-256x256.png'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/woocommerce-shop-assistant-jarvis/">WooCommerce Shop Assistant</a></h4>
                        <p>WooCommerce Shop Assistant – <strong>JARVIS</strong> shows recent user activities, provides advanced search, floating cart, featured products, 
                        store notifications, order notifications – all in one place for easy access.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
            
            
            <div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/portfolio-x-plugin/"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/plugin-images/portfolio-x-logo-dark-2.png'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/portfolio-x-plugin/">Portfolio X</a></h4>
                        <p>Portfolio X is an advanced, responsive portfolio with streamlined workflow and unique designs to show your works or projects.&nbsp;<strong>
                        Portfolio Showcase</strong> and <strong>Portfolio Widgets</strong> are included.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
            
            <div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/woo-tabbed-category-product-listing/"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/plugin-images/woo-tabbed-icon-256x256.png'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/woo-tabbed-category-product-listing/">Woo Tabbed Category Products</a></h4>
                        <p>WooCommerce plugin that allows you to showcase your products category wise in tabbed format. This is a unique woocommerce plugin that lets dynaimically 
                        load your products in tabs based.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
            
            
            <div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/knowledgebase-helpdesk/"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/knowledgebase-helpdesk.jpg'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/knowledgebase-helpdesk/" target="_blank" rel="noopener noreferrer">KnowledgeBase HelpDesk</a></h4>
                        <p><p>KnowledgeBase HelpDesk is an advanced Knowledgebase plugin with helpdesk<strong>, </strong>glossary and FAQ features all in one. 
						KnowledgeBase HelpDesk is extremely simple and easy to use.</p></p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
            
            
            <div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/express-shop/"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/plugin-images/express-shop.png'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/express-shop/">Express Shop</a></h4>
                        <p>Express Shop is a WooCommerce addon to show all products in one page. User can add products to cart and go to checkout. 
						Filtering and search integrated in single page.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
			
			
			<div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/seo-help"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/seo-help.jpg'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/seo-help">SEO Help</a></h4>
                        <p>SEO Help is a unique WordPress plugin to help you write better Link Bait titles. It will take the 
						WordPress post title as Subject and generate alternative ClickBait titles for you to choose from.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
			
			
			<div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/ichart/"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/ilist-chat.png'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/ichart/">iChart – Easy Charts and Graphs</a></h4>
                        <p>Charts and graphs are now easy to build and add to any WordPress page with just a few clicks.
						iChart is a Google chartjs implementation to add graphs &amp; 
						<strong>charts</strong> – directly from WordPress Visual editor.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
			
			
			
			<div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://www.quantumcloud.com/products/comment-link-remove/"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/Comment-Link-Remove-300x300 (1).jpg'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://www.quantumcloud.com/products/comment-link-remove/">Comment Link Remove</a></h4>
                        <p>All in one solution to fight comment spammers. Tired of deleting useless spammy comments? This 
						plugin removes author link and any other links from the user comments.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
			
			<div class="qc-column-4"><!-- qc-column-4 -->
                <!-- Feature Box 1 -->
                <div class="support-block ">
                    <div class="support-block-img">
                        <a href="https://wordpress.org/plugins/increase-sales/"> <img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/icon-256x256.png'); ?>" alt=""></a>
                    </div>
                    <div class="support-block-info">
                        <h4><a href="https://wordpress.org/plugins/increase-sales/">Increase Sales</a></h4>
                        <p>Increase Sales is a new and unique WooCommerce addon that strategically places your cross-sells products inline inside, at top or bottom of the Cart during checkout.</p>

                    </div>
                </div>
            </div><!--/qc-column-4 -->
            
             
            
        </div>
        <!--qc row-->
    </div>
    
    
    
    

</div><!--qc_support_container-->
				
			<?php
		} //End of qcpromo_support_page_callback_function
		
		
	
	} //End of the class QcSupportAndPromoPage


} //End of class_exists


/*
* Create Instance, set instance variables and then call appropriate worker.
*/

//Supply Unique Promo Page Slug as the constructor parameter of the class QcSupportAndPromoPage. ex: sld-page-2124a to the constructor

//Please create an unique instance for your use, example: $instance_sldf2

$instance_woowbot = new QcSupportAndPromoPage('woowbot-free');

if( is_admin() )
{
    $instance_woowbot->plugin_menu_slug = "woowbot";
    $instance_woowbot->plugin_name = "WooWBot WooCommerce Chatbot";
    $instance_woowbot->show_promo_page();
}
