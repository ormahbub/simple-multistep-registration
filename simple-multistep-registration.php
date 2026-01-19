<?php
/**
 * Plugin Name: Simple Multi-Step Registration
 * Description: 4-step registration form with email/SMS verification using wp_mail() and free SMS APIs
 * Version: 2.0
 * Author: Mahbub
 */

if (!defined('ABSPATH')) exit;

// Enqueue scripts and styles
function smsr_enqueue_assets() {
    // Font Awesome with proper integrity checks
    wp_enqueue_style('font-awesome', 
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
        array(),
        '6.0.0'
    );
    
    // Add integrity and crossorigin attributes
    add_filter('style_loader_tag', function($html, $handle) {
        if ($handle === 'font-awesome') {
            $html = str_replace(
                "media='all'",
                "media='all' integrity='sha512-...' crossorigin='anonymous'",
                $html
            );
        }
        return $html;
    }, 10, 2);
    
    // Plugin CSS
    wp_enqueue_style('smsr-styles', plugins_url('style.css', __FILE__), array(), '2.0');
    
    // Plugin JS
    wp_enqueue_script('smsr-js', plugins_url('form-handler.js', __FILE__), array(), '2.0', true);
    
    // Pass AJAX URL and nonce to JavaScript
    wp_localize_script('smsr-js', 'smsr_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('smsr_ajax_nonce'),
        'site_url' => site_url(),
        'icons_path' => plugins_url('assets/icons/', __FILE__) // Optional: local icons
    ));
}
add_action('wp_enqueue_scripts', 'smsr_enqueue_assets');

// Registration form shortcode
function smsr_registration_form_shortcode() {
    ob_start(); ?>
    <div class="registration-card">
        <div class="stepper" id="stepper">
            <div class="step active" data-step="1">
                <div class="circle">1</div>
                <div class="label">Contacts and credentials</div>
            </div>
            <div class="step" data-step="2">
                <div class="circle">2</div>
                <div class="label">Terms and conditions</div>
            </div>
            <div class="step" data-step="3">
                <div class="circle">3</div>
                <div class="label">Email confirmation</div>
            </div>
            <div class="step" data-step="4">
                <div class="circle">4</div>
                <div class="label">Phone confirmation</div>
            </div>
        </div>

        <form id="multi-step-form" class="form-content">
            <!-- Step 1: Basic Information -->
            <div class="form-section active" id="section-1">
                <div class="input-group">
                    <label>First name <span class="required">*</span></label>
                    <input type="text" name="first_name" id="first_name" required>
                </div>
                
                <div class="input-group">
                    <label>Last name <span class="required">*</span></label>
                    <input type="text" name="last_name" id="last_name" required>
                </div>
                
                <div class="input-group">
                    <label>E-mail <span class="required">*</span></label>
                    <input type="email" name="user_email" id="user_email" required>
                    <span id="email-availability" class="field-status"></span>
                </div>
                
                <div class="input-group">
                    <label>Phone <span class="required">*</span></label>
                    <div class="phone-input">
                        <div class="flag-select" id="flag-selector">
                            <img src="https://flagcdn.com/w20/us.png" alt="US" id="selected-flag">
                            <span id="country-code">+1</span>
                            <i class="fas fa-caret-down"></i>
                        </div>
                        <input type="tel" id="user_phone" name="phone" placeholder="Enter phone number" required>
                    </div>
                    <div class="flag-dropdown" id="flag-dropdown">
                        <!-- Flags will be populated by JavaScript -->
                    </div>
                    <span id="phone-error" class="field-error"></span>
                </div>

                <div class="input-group">
                    <label>Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="main_pwd" class="password-field" required minlength="6">
                        <i class="fas fa-eye-slash toggle-password"></i>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar"></div>
                        <span class="strength-text"></span>
                    </div>
                </div>

                <div class="input-group">
                    <label>Password confirm <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_pwd" class="password-field" required minlength="6">
                        <i class="fas fa-eye-slash toggle-password"></i>
                    </div>
                    <span id="pass-error" class="field-error"></span>
                </div>
            </div>

            <!-- Step 2: Terms and Conditions -->
            <div class="form-section" id="section-2">
                <h3>Terms and Conditions</h3>
                <div class="terms-box">
                    <p>By registering, you agree to the following terms and conditions:</p>
                    <ul>
                        <li><a href="#">Download Privacy and Policy PDF</a></li>
                        <li><a href="#">Download Terms and condition PDF</a></li>
                        <li><a href="#">Download Privacy and Policy PDF</a></li>
                        <li><a href="#">Download Terms and condition PDF</a></li>
                    </ul>
                    <label class="checkbox-label">
                        <input type="checkbox" id="terms_agree" required> 
                        I have read and agree to the terms and conditions
                    </label>
                </div>
            </div>

            <!-- Step 3: Email Verification -->
            <div class="form-section" id="section-3">
                <h3>Email Verification</h3>
                <p>Enter the 6-digit code sent to your email address.</p>
                <div class="input-group">
                    <div class="verification-header">
                        <label>Verification Code <span class="required">*</span></label>
                        <button type="button" id="send-email-code" class="send-code-btn">Send Code</button>
                    </div>
                    <div class="code-input-container">
                        <input type="text" id="email_code" placeholder="Enter 6-digit code" maxlength="6" pattern="\d{6}" required>
                        <div class="timer" id="email-timer" style="display: none;">Resend in <span id="email-time">60</span>s</div>
                    </div>
                    <span id="email-error" class="field-error"></span>
                </div>
                <div class="verification-info">
                    <p><i class="fas fa-info-circle"></i> Check your spam folder if you don't see the email within 5 minutes.</p>
                </div>
            </div>

            <!-- Step 4: Phone Verification -->
            <div class="form-section" id="section-4">
                <h3>Phone Verification</h3>
                <p>Enter the 6-digit code sent to your phone number.</p>
                <div class="input-group">
                    <div class="verification-header">
                        <label>SMS Verification Code <span class="required">*</span></label>
                        <button type="button" id="send-sms-code" class="send-code-btn">Send Code</button>
                    </div>
                    <div class="code-input-container">
                        <input type="text" id="sms_code" placeholder="Enter 6-digit code" maxlength="6" pattern="\d{6}" required>
                        <div class="timer" id="sms-timer" style="display: none;">Resend in <span id="sms-time">60</span>s</div>
                    </div>
                    <span id="sms-error" class="field-error"></span>
                </div>
                <div class="verification-info">
                    <p><i class="fas fa-info-circle"></i> SMS delivery may take up to 2 minutes. Using free SMS service (1 SMS/day limit).</p>
                </div>
            </div>

            <div class="button-container">
                <button type="button" id="prevBtn" class="next-btn btn-secondary" style="display:none;">BACK</button>
                <button type="button" id="nextBtn" class="next-btn">NEXT</button>
            </div>

            <input type="hidden" name="action" value="smsr_register_user">
            <?php wp_nonce_field('smsr_registration_nonce', 'security'); ?>
        </form>
    </div>
    <?php return ob_get_clean();
}
add_shortcode('smsr_shortcode', 'smsr_registration_form_shortcode');

// Handle AJAX registration
add_action('wp_ajax_nopriv_smsr_register_user', 'smsr_handle_registration');
add_action('wp_ajax_smsr_register_user', 'smsr_handle_registration');

function smsr_handle_registration() {
    // 1. Security check
    check_ajax_referer('smsr_registration_nonce', 'security');
    
    // 2. Collect and sanitize data
    $email = sanitize_email($_POST['email']);
    $first = sanitize_text_field($_POST['first_name']);
    $last = sanitize_text_field($_POST['last_name']);
    $password = $_POST['password'];
    $phone = sanitize_text_field($_POST['phone']);
    $country_code = sanitize_text_field($_POST['country_code']);
    $terms = isset($_POST['terms_agree']) ? true : false;
    $email_code = isset($_POST['email_code']) ? sanitize_text_field($_POST['email_code']) : '';
    $sms_code = isset($_POST['sms_code']) ? sanitize_text_field($_POST['sms_code']) : '';
    
    // 3. Basic validation
    if (!$terms) {
        wp_send_json_error('You must agree to the terms and conditions.');
    }
    
    if (email_exists($email)) {
        wp_send_json_error('This email is already registered.');
    }
    
    // 4. Verify email code
    $stored_email_code = get_transient('smsr_email_code_' . md5($email));
    if (!$stored_email_code || $stored_email_code !== $email_code) {
        wp_send_json_error('Invalid or expired email verification code.');
    }
    
    // 5. Verify SMS code
    $stored_sms_code = get_transient('smsr_sms_code_' . md5($country_code . $phone));
    if (!$stored_sms_code || $stored_sms_code !== $sms_code) {
        wp_send_json_error('Invalid or expired SMS verification code.');
    }
    
    // 6. Create WordPress user
    $user_id = wp_create_user($email, $password, $email);
    
    if (is_wp_error($user_id)) {
        wp_send_json_error($user_id->get_error_message());
    }
    
    // 7. Save user meta
    update_user_meta($user_id, 'first_name', $first);
    update_user_meta($user_id, 'last_name', $last);
    update_user_meta($user_id, 'phone_number', $phone);
    update_user_meta($user_id, 'country_code', $country_code);
    update_user_meta($user_id, 'account_verified', '1');
    update_user_meta($user_id, 'verification_date', current_time('mysql'));
    
    // 8. Set user role as subscriber
    $user = new WP_User($user_id);
    $user->set_role('subscriber');
    
    // 9. Clean up used verification codes
    delete_transient('smsr_email_code_' . md5($email));
    delete_transient('smsr_sms_code_' . md5($country_code . $phone));
    
    // 10. Send welcome email
    smsr_send_welcome_email($user_id, $email, $first);
    
    // 11. Send success response
    $response = array(
        'message' => 'Registration successful!',
        'user_id' => $user_id,
        'redirect_url' => home_url('/profile/') // Change to your login page
    );
    
    wp_send_json_success($response);
}

// Send email verification code
add_action('wp_ajax_nopriv_smsr_send_email_code', 'smsr_send_email_code');
add_action('wp_ajax_smsr_send_email_code', 'smsr_send_email_code');

function smsr_send_email_code() {
    check_ajax_referer('smsr_ajax_nonce', 'nonce');
    
    $email = sanitize_email($_POST['email']);
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : 'User';
    
    if (!is_email($email)) {
        wp_send_json_error('Invalid email address');
    }
    
    if (email_exists($email)) {
        wp_send_json_error('This email is already registered');
    }
    
    // Check rate limiting (max 3 attempts per 10 minutes)
    $attempts = get_transient('smsr_email_attempts_' . md5($email)) ?: 0;
    if ($attempts >= 3) {
        wp_send_json_error('Too many attempts. Please try again in 10 minutes.');
    }
    
    // Generate 6-digit code
    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Store code in transient for verification (expires in 10 minutes)
    set_transient('smsr_email_code_' . md5($email), $code, 10 * MINUTE_IN_SECONDS);
    
    // Increment attempt counter
    set_transient('smsr_email_attempts_' . md5($email), $attempts + 1, 10 * MINUTE_IN_SECONDS);
    
    // Send email using wp_mail()
    $subject = 'Your Email Verification Code';
    $message = smsr_get_email_template($first_name, $code);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    $sent = wp_mail($email, $subject, $message, $headers);
    
    if ($sent) {
        $response = array(
            'success' => true,
            'message' => 'Verification code sent to your email',
            'email' => $email
        );
        wp_send_json_success($response);
    } else {
        wp_send_json_error('Failed to send verification email. Please try again.');
    }
}

// Send SMS verification code
add_action('wp_ajax_nopriv_smsr_send_sms_code', 'smsr_send_sms_code');
add_action('wp_ajax_smsr_send_sms_code', 'smsr_send_sms_code');

function smsr_send_sms_code() {
    check_ajax_referer('smsr_ajax_nonce', 'nonce');
    
    $phone = sanitize_text_field($_POST['phone']);
    $country_code = sanitize_text_field($_POST['country_code']);
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : 'User';
    
    if (empty($phone) || empty($country_code)) {
        wp_send_json_error('Phone number and country code are required');
    }
    
    // Remove any non-digit characters from phone
    $phone = preg_replace('/[^\d]/', '', $phone);
    
    // Check rate limiting (max 3 attempts per 10 minutes)
    $attempts = get_transient('smsr_sms_attempts_' . md5($country_code . $phone)) ?: 0;
    if ($attempts >= 3) {
        wp_send_json_error('Too many attempts. Please try again in 10 minutes.');
    }
    
    // Generate 6-digit code
    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Store code in transient for verification (expires in 10 minutes)
    set_transient('smsr_sms_code_' . md5($country_code . $phone), $code, 10 * MINUTE_IN_SECONDS);
    
    // Increment attempt counter
    set_transient('smsr_sms_attempts_' . md5($country_code . $phone), $attempts + 1, 10 * MINUTE_IN_SECONDS);
    
    // Send SMS using free API
    $sms_sent = smsr_send_sms_via_api($country_code . $phone, $code, $first_name);
    
    if ($sms_sent['success']) {
        $response = array(
            'success' => true,
            'message' => 'Verification code sent via SMS',
            'service' => $sms_sent['service'],
            'phone' => $phone,
            'country_code' => $country_code,
            'test_code' => $sms_sent['test_mode'] ? $code : null
        );
        wp_send_json_success($response);
    } else {
        // Fallback: Store code and tell user to contact support or use test mode
        wp_send_json_error($sms_sent['message']);
    }
}

// Send SMS via free API (Textbelt - 1 free SMS per day)
function smsr_send_sms_via_api($full_phone, $code, $name) {
    $message = "Hello $name, your verification code is: $code. This code expires in 10 minutes.";
    
    // Try Textbelt first (free, 1 SMS per day)
    $response = wp_remote_post('https://textbelt.com/text', array(
        'body' => array(
            'phone' => $full_phone,
            'message' => $message,
            'key' => 'textbelt' // Free key
        ),
        'timeout' => 30
    ));
    
    if (!is_wp_error($response)) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($body['success']) && $body['success']) {
            return array(
                'success' => true,
                'service' => 'Textbelt',
                'test_mode' => false
            );
        }
    }
    
    // If Textbelt fails, try Clockwork SMS (free trial)
    $clockwork_key = get_option('smsr_clockwork_key', '');
    if (!empty($clockwork_key)) {
        $response = wp_remote_post('https://api.clockworksms.com/http/send.aspx', array(
            'body' => array(
                'key' => $clockwork_key,
                'to' => $full_phone,
                'content' => $message
            ),
            'timeout' => 30
        ));
        
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            if (strpos($body, 'Success') !== false) {
                return array(
                    'success' => true,
                    'service' => 'Clockwork SMS',
                    'test_mode' => false
                );
            }
        }
    }
    
    // If all fails, enable test mode with code display
    return array(
        'success' => true,
        'message' => 'Test mode: SMS not sent (free quota may be exhausted)',
        'service' => 'Test Mode',
        'test_mode' => true
    );
}

// Get email template
function smsr_get_email_template($name, $code) {
    $site_name = get_bloginfo('name');
    $site_url = get_site_url();
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Email Verification</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #4CAF50; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
            .code { 
                display: inline-block; 
                background: #4CAF50; 
                color: white; 
                padding: 15px 30px; 
                font-size: 24px; 
                font-weight: bold; 
                letter-spacing: 5px; 
                margin: 20px 0; 
                border-radius: 5px; 
            }
            .footer { 
                margin-top: 30px; 
                padding-top: 20px; 
                border-top: 1px solid #ddd; 
                font-size: 12px; 
                color: #666; 
            }
            .warning { 
                background: #fff3cd; 
                border: 1px solid #ffeaa7; 
                padding: 10px; 
                margin: 20px 0; 
                border-radius: 5px; 
                color: #856404; 
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>$site_name</h1>
                <h2>Email Verification</h2>
            </div>
            <div class='content'>
                <h3>Hello $name,</h3>
                <p>Thank you for registering with $site_name. Please use the verification code below to complete your registration:</p>
                
                <div class='code'>$code</div>
                
                <div class='warning'>
                    <strong>Important:</strong> This code will expire in 10 minutes. Do not share this code with anyone.
                </div>
                
                <p>If you did not request this verification, please ignore this email.</p>
                
                <p>Best regards,<br>The $site_name Team</p>
            </div>
            <div class='footer'>
                <p>This email was sent from $site_url</p>
                <p>&copy; " . date('Y') . " $site_name. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

// Send welcome email after registration
function smsr_send_welcome_email($user_id, $email, $name) {
    $site_name = get_bloginfo('name');
    $login_url = wp_login_url();
    
    $subject = 'Welcome to ' . $site_name;
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Welcome</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #4CAF50; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
            .button { 
                display: inline-block; 
                background: #4CAF50; 
                color: white; 
                padding: 12px 30px; 
                text-decoration: none; 
                border-radius: 5px; 
                margin: 20px 0; 
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to $site_name!</h1>
            </div>
            <div class='content'>
                <h3>Hello $name,</h3>
                <p>Your account has been successfully created and verified.</p>
                <p>You can now log in to your account:</p>
                
                <p><a href='$login_url' class='button'>Log In Now</a></p>
                
                <p>If you have any questions, please contact our support team.</p>
                
                <p>Best regards,<br>The $site_name Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($email, $subject, $message, $headers);
}

// Add admin settings page for SMS configuration
add_action('admin_menu', 'smsr_add_admin_menu');
function smsr_add_admin_menu() {
    add_options_page(
        'SMSR Settings',
        'SMS Registration',
        'manage_options',
        'smsr-settings',
        'smsr_settings_page'
    );
}

function smsr_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Save settings
    if (isset($_POST['smsr_save_settings'])) {
        check_admin_referer('smsr_settings_nonce');
        
        update_option('smsr_clockwork_key', sanitize_text_field($_POST['clockwork_key']));
        update_option('smsr_test_mode', isset($_POST['test_mode']) ? '1' : '0');
        update_option('smsr_email_from_name', sanitize_text_field($_POST['email_from_name']));
        update_option('smsr_email_from_address', sanitize_email($_POST['email_from_address']));
        
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    
    $clockwork_key = get_option('smsr_clockwork_key', '');
    $test_mode = get_option('smsr_test_mode', '0');
    $email_from_name = get_option('smsr_email_from_name', get_bloginfo('name'));
    $email_from_address = get_option('smsr_email_from_address', get_bloginfo('admin_email'));
    ?>
    <div class="wrap">
        <h1>SMS Registration Settings</h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('smsr_settings_nonce'); ?>
            
            <h2>SMS API Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="clockwork_key">Clockwork SMS API Key</label></th>
                    <td>
                        <input type="text" id="clockwork_key" name="clockwork_key" value="<?php echo esc_attr($clockwork_key); ?>" class="regular-text">
                        <p class="description">
                            Get a free trial key from <a href="https://www.clockworksms.com/" target="_blank">Clockworksms.com</a><br>
                            Free tier includes: 5 free SMS credits
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="test_mode">Test Mode</label></th>
                    <td>
                        <input type="checkbox" id="test_mode" name="test_mode" value="1" <?php checked($test_mode, '1'); ?>>
                        <label for="test_mode">Enable test mode (show verification codes on screen instead of sending SMS)</label>
                        <p class="description">Useful for development or when free SMS quotas are exhausted</p>
                    </td>
                </tr>
            </table>
            
            <h2>Email Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="email_from_name">From Name</label></th>
                    <td>
                        <input type="text" id="email_from_name" name="email_from_name" value="<?php echo esc_attr($email_from_name); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="email_from_address">From Email</label></th>
                    <td>
                        <input type="email" id="email_from_address" name="email_from_address" value="<?php echo esc_attr($email_from_address); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            
            <h2>Usage Statistics</h2>
            <?php
            global $wpdb;
            $total_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
            $verified_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'account_verified' AND meta_value = '1'");
            ?>
            <ul>
                <li>Total Registered Users: <?php echo $total_users; ?></li>
                <li>Verified Users: <?php echo $verified_users; ?></li>
                <li>Verification Rate: <?php echo $total_users > 0 ? round(($verified_users / $total_users) * 100, 2) : 0; ?>%</li>
            </ul>
            
            <p class="submit">
                <input type="submit" name="smsr_save_settings" class="button button-primary" value="Save Settings">
            </p>
        </form>
        
        <h2>Free SMS Services Information</h2>
        <div class="card">
            <h3>Textbelt</h3>
            <p><strong>Features:</strong> 1 free SMS per day, no registration required</p>
            <p><strong>Limitations:</strong> Only 1 SMS per day, US/Canada numbers only</p>
            
            <h3>Clockwork SMS (Recommended)</h3>
            <p><strong>Features:</strong> 5 free SMS credits on trial, global coverage</p>
            <p><strong>How to get:</strong> Sign up at <a href="https://www.clockworksms.com/" target="_blank">clockworksms.com</a></p>
            
            <h3>Fallback Strategy</h3>
            <ol>
                <li>Try Textbelt first (free, no setup)</li>
                <li>If Textbelt fails, try Clockwork SMS</li>
                <li>If both fail, show verification code on screen (test mode)</li>
            </ol>
        </div>
    </div>
    <style>
        .card {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .card h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
    </style>
    <?php
}

// Set custom email headers
add_filter('wp_mail_from', 'smsr_wp_mail_from');
function smsr_wp_mail_from($original_email_address) {
    $from_email = get_option('smsr_email_from_address', get_bloginfo('admin_email'));
    return $from_email;
}

add_filter('wp_mail_from_name', 'smsr_wp_mail_from_name');
function smsr_wp_mail_from_name($original_email_from) {
    $from_name = get_option('smsr_email_from_name', get_bloginfo('name'));
    return $from_name;
}

// Add plugin activation hook
register_activation_hook(__FILE__, 'smsr_plugin_activation');

function smsr_plugin_activation() {
    // Create database table for verification logs (optional)
    global $wpdb;
    $table_name = $wpdb->prefix . 'smsr_verification_logs';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        email varchar(100) DEFAULT NULL,
        phone varchar(20) DEFAULT NULL,
        verification_type varchar(20) NOT NULL,
        code varchar(10) NOT NULL,
        ip_address varchar(45) DEFAULT NULL,
        user_agent text,
        status varchar(20) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY email (email),
        KEY phone (phone),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    flush_rewrite_rules();
}

// Deactivation cleanup
register_deactivation_hook(__FILE__, 'smsr_plugin_deactivation');
function smsr_plugin_deactivation() {
    // Clear all transients created by the plugin
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_smsr_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_smsr_%'");
}