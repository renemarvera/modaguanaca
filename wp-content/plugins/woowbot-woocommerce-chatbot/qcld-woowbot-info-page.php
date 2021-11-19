<?php

class Qcld_woowbot_info_page
{

    function __construct()
    {
        add_action('admin_menu', array($this, 'qcopd_info_menu'));
    }

    function qcopd_info_menu()
    {
        add_submenu_page(
            'woowbot',
            esc_html__('Help'),
            esc_html__('Help'),
            'manage_options',
            'qcld_woowbot_info_page',
            array(
                $this,
                'qcopd_info_page_content'
            )
        );
    }

    function qcopd_info_page_content()
    {
        ?>
        <style>


            .qc-plugin-help-container {
                padding: 20px;
                background-color: #fff;
                border: 1px solid #cccccc;
                margin: 0 14px;
				width: 1170px;
				margin-top: -18px;
				
            }

            .qc-plugin-help-heading-lg {
                border-bottom: 1px dashed #cccccc;
                margin: 0 0 10px;
                padding: 20px 0;
            }
			* {

				webkit-box-sizing: border-box;
				-moz-box-sizing: border-box;
				box-sizing: border-box;


			}
        </style>
        <div class="wrap">

            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-2">

                    <div id="post-body-content" style="position: relative;">


                        <div class="qc-plugin-help-container">
                            <h3 class="qc-plugin-help-heading-lg">Help</h3>
                            <p>
                                Getting started with WoowBot is instantaneous. All you need to do is install and activate the plugin.
                            </p>
                            <p>
                                You can upload your own ChatBot icon from WoowBot panel->Icons section.
                            </p>
                            <p>
                                You can also upload a custom Agent icon in the pro version.
                            </p>
                            <p>
                                In the lite version there are a few language settings that you can customize to your need. The default languages are fine for stores using the English language. But you can change the bot responses literally into any language!
                            </p>
                            <p>Use the custom CSS panel if you need to tweak some colors or font settings inside WoowBot.</p>
                            
                            <div class="clear"></div>
                            


							<div style="position:relative">
							<div style="position: absolute;right: 0;top: 174px;">
								<img src="<?php echo esc_url(QCLD_WOOCHATBOT_PLUGIN_URL.'images/woowbot-chatbot.jpg'); ?>" />
							</div>
                            <h3>Why You Should Upgrade to the WoowBot Pro version :</h3>
                            <p>Please consider upgrading to the pro version. Not only will you get a set of cutting edge features to improve sales on your store – you will  also be contributing to the continued development and improvement of this plugin.</p>
                            <h4>Here are some highlights of the WoowBot pro version features :</h4>
                            <ol style="list-style: circle;    width: 60%;">
                                    <li>Many useful, built in features</li>
    <li>Google <strong>Artificial Intelligence</strong>, Machine Learning or AI Engine with DialogFlow</li>
    <li><strong>Natural Language Processing</strong> through Google's <strong>DialogFlow</strong></li>
    <li><strong>Automatically supports Custom Intents and Responses</strong> You Create in Dialog Flow</li>
    <li>Automatically supports <strong>Follow Up Intents</strong> and step by step Question Answers through Dialogflow custom Intents</li>
    <li>Automatically supports <strong>Rich Message Response &amp; Card Responses</strong> from Dialogflow as FaceBook messenger app</li>
    <li>Automatically supports images (jpgs, <strong>animated gifs</strong>) and <strong>Youtube</strong> Videos from DialogFlow Intent Responses</li>
    <li><strong>Automatically</strong> supports images (jpgs, animated gifs) and Youtube Videos from ChatBot Language Settings Responses</li>
    <li>Simply <strong>paste</strong> any image (jpg or gif) or Youtube video’s full link inside the ChatBot’s Language Center or <strong>Dialogflow</strong></li>
    <li>Add images from your WordPress media library or Giphy animated gif images easily and quickly from the ChatBot language center with the <strong>floating image options</strong></li>
    <li><strong>Advanced Search</strong> additional WooCommerce product fields like category name, tags, excerpt, SKU etc.</li>
    <li>Show or hide cart item number</li>
    <li>Fine tune WoowBot icon position</li>
    <li>Product display order by and sorting options</li>
    <li>Option to <strong>Show Only Parent Categories</strong> with or without Sub Category list.</li>
    <li>Option to display <strong>order status</strong> with or without logging in</li>
    <li>Option to <strong>choose</strong> on which <strong>pages</strong> WoowBot should load</li>
    <li>Upload custom ChatBot icon</li>
    <li>Upload <strong>custom</strong> Agent icon</li>
    <li>Choose from <strong>5 design templates</strong> for ChatBot interface</li>
    <li><strong>Unique Mini Mode</strong> template</li>
    <li>Option to <strong>disable</strong> WooWBot on <strong>Mobile Devices</strong></li>
    <li>Option to <strong>exclude</strong> out of stock products from search results</li>
    <li>Option to Enable/Disable <strong>Product Search, Featured Products, Sale Products</strong>, Order Status buttons at start</li>
    <li>Show or Hide <strong>Opening Notifications</strong></li>
    <li>Option to enable/disable<strong> asking for eMail</strong> address after asking name</li>
    <li>Upload your own <strong>background image</strong> for chatbot</li>
    <li><strong>Customize Colors</strong> of text and buttons</li>
    <li>Upload <strong>custom icons</strong> for the bottom area</li>
    <li>Create <strong>FAQ area</strong> with multiple questions and answers (supports html)</li>
    <li><strong>Add video</strong> in Support area just by pasting Youtube link</li>
    <li>Add multiple <strong>store notifications</strong> to show above the ChatBot icon</li>
    <li>Show <strong>recently viewed products</strong> for easy reference to the shopper</li>
    <li>Show <strong>featured products</strong> until shopper has viewed products</li>
    <li><strong>Quick Cart view</strong></li>
    <li>Quick <strong>access to Support</strong></li>
    <li>Quick <strong>Help for commands that can be used in-chat</strong></li>
    <li>Admin <strong>customizable chat commands</strong></li>
    <li><strong>Stop Words</strong> dictionary included and editable by admin. Bot will automatically exclude stop words from search criteria and chat commands</li>
    <li><strong>Advanced Language Center</strong> to edit and change every WoowBot responses, System languages, stop words and info messages!</li>
    <li>Add <strong>multiple variations of ChatBot responses for each node</strong>. They will be used randomly and give an appearance of more human like responses.</li>
    <li>Option to<strong> Skip Greeting and Show Start Menu</strong> even with DialogFlow integration</li>
    <li>Works <strong>with/without DialogFlow</strong> Integration using Start Menu</li>
    <li><strong>Display product details in-chat - complete with images</strong>, add to cart option and support for multiple images</li>
    <li><strong>Option to open product details in single page</strong> instead of Bot window</li>
    <li><strong>Persistent chat history</strong> over shopper session on website</li>
    <li>Remember chat history in browser local storage and <strong>greet returning shoppers</strong></li>
    <li><strong>Shortcode for WoowBot on Page</strong></li>
    <li><strong>Embed WoowBot</strong> on any site - even static HTML site (<em>excluding some advanced features</em>)</li>
    <li><strong>Language</strong> support. mo/pot file included so you can translate to any language</li>
    <li><strong>RTL</strong> support</li>
    <li>Integration with FaceBook <strong>Messenger for Live Chat</strong></li>
    <li>Integration with <strong>Skype, WhatsApp, Viber, Web Link &amp; Phone Call with floating icons</strong></li>
    <li><strong>Call me back</strong> – customer leaves phone number.</li>
    <li>Collect <strong>Customer Feedback</strong> by email option.</li>
    <li>Collect <strong>User eMail </strong>for <strong>newsletter Subscription
</strong></li>
    <li><strong>GDPR</strong> compliance (message with link to Privacy page)</li>
    <li><strong>Unsubscibe</strong> email address Command</li>
    <li>Advanced <strong>Name Recognition</strong></li>
    <li><strong>Sample DialogFlow Agent for quick import</strong></li>
    <li><strong>Prompt User for eMail subscription with Retargeting Nessage</strong></li>
    <li>Export <strong>eMail Addresses as CSV</strong></li>
    <li><strong>Onsite retargeting and remarketing</strong> to increase customer conversion rate and <strong>increase sales</strong>!</li>
    <li>Show retargeting messages for customer on <strong>Exit Intent, After Scrolling Down “X” Percent, Or after “X” seconds</strong>.</li>
    <li><strong>Custom Background color</strong> for retargeting messages.</li>
    <li><strong>Checkout reminder</strong> on set time interval to reduce cart abandonment.</li>
    <li>Checkout reminder for <strong>returning shopper </strong>who has products on the cart.</li>
    <li><strong>Schedule day and time</strong> when WoowBot will run. Make WoowBot work with other Live chat software.</li>
    <li>Option to hide support and other icons from the ChatBot footer</li>
    <li><strong>Customer Conversion Reporting</strong> with Charts and Graphs</li>
    <li>Shopper Conversion<strong> Statistics by Day, Week, Month and Custom Date Range</strong></li>
                            </ol>
							
							<ol style="list-style: circle">
								<li><strong>Chat Sessions</strong> <a href="https://www.quantumcloud.com/products/chatbot-addons/" target="_blank" rel="noopener noreferrer">AddOn</a></li>
								<li><strong>FacebBook Messenger</strong> <a href="https://www.quantumcloud.com/products/chatbot-addons/" target="_blank" rel="noopener noreferrer">AddOn</a></li>
								<li><strong>Extended Search</strong> <a href="https://www.quantumcloud.com/products/chatbot-addons/" target="_blank" rel="noopener noreferrer">AddOn</a></li>
								<li><strong>Live Chat</strong> <a href="https://www.quantumcloud.com/products/chatbot-addons/" target="_blank" rel="noopener noreferrer">AddOn</a></li>
								<li><strong>White Label</strong> <a href="https://www.quantumcloud.com/products/chatbot-addons/" target="_blank" rel="noopener noreferrer">AddOn</a></li>
								<li><strong>Mailing List Integration</strong> <a href="https://www.quantumcloud.com/products/chatbot-addons/" target="_blank" rel="noopener noreferrer">AddOn</a></li>
								
								<li><strong>and </strong> <a href="https://www.quantumcloud.com/products/chatbot-addons/" target="_blank" rel="noopener noreferrer">More...</a></li>
							</ol>
							 
                            <p style="text-align: center">
                                <a target="_blank"
                                   href="https://www.quantumcloud.com/products/woocommerce-chatbot-woowbot/"
                                   class="button button-primary">Upgrade to Pro</a>

                            </p>
						</div>
                        </div>

                        <div style="padding: 15px 10px; border: 1px solid #ccc; text-align: center; margin-top: 20px;margin-left: 14px;width: 1170px;">
                            Crafted By: <a href="http://www.quantumcloud.com" target="_blank">Web Design Company</a> -
                            QuantumCloud
                        </div>

                    </div>
                    <!-- /post-body-content -->


                </div>
                <!-- /post-body-->

            </div>
            <!-- /poststuff -->

        </div>
        <!-- /wrap -->

        <?php
    }
}

new Qcld_woowbot_info_page;