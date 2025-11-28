<?php
/**
 * Plugin Name: Testimonial Form Submission
 * Plugin URI: https://yoursite.com
 * Description: Beautiful testimonial form with star ratings that submits to ACF custom post type
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yoursite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Testimonial_Form_Plugin {
    
    public function __construct() {
        // Register shortcode
        add_shortcode('testimonial_form', array($this, 'render_form'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_submit_testimonial', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_submit_testimonial', array($this, 'handle_submission'));
        
        add_action('wp_ajax_upload_testimonial_image', array($this, 'handle_image_upload'));
        add_action('wp_ajax_nopriv_upload_testimonial_image', array($this, 'handle_image_upload'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_settings_page'));
    }
    
    public function add_settings_page() {
        add_options_page(
            'Testimonial Form Settings',
            'Testimonial Form',
            'manage_options',
            'testimonial-form-settings',
            array($this, 'render_settings_page')
        );
    }
    
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Testimonial Form Settings</h1>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>📋 How to Use the Testimonial Form</h2>
                
                <h3>Step 1: Add Form to a Page</h3>
                <p>Copy this shortcode and paste it into any page or post:</p>
                <code style="background: #f0f0f0; padding: 10px; display: block; margin: 10px 0; font-size: 14px;">
                    [testimonial_form]
                </code>
                
                <hr style="margin: 30px 0;">
                
                <h3>Step 2: Email Button Links</h3>
                <p>Use these links in your email campaigns to pre-fill customer information:</p>
                
                <h4>📧 Basic Link Format:</h4>
                <code style="background: #f0f0f0; padding: 10px; display: block; margin: 10px 0; font-size: 12px; word-break: break-all;">
                    https://yoursite.com/your-page-slug/#name=Customer Name&title=Customer Title
                </code>
                
                <h4>📧 MailerLite Example:</h4>
                <p>Use these merge tags in your MailerLite campaigns:</p>
                <code style="background: #f0f0f0; padding: 10px; display: block; margin: 10px 0; font-size: 12px; word-break: break-all;">
                    https://yoursite.com/your-page-slug/#name={$name}&title={$title}
                </code>
                
                <h4>📧 Email Button HTML:</h4>
                <p>Copy and paste this into your email editor (update the URL):</p>
                <textarea readonly style="width: 100%; height: 150px; font-family: monospace; font-size: 12px; padding: 10px; margin: 10px 0;">
&lt;a href="https://yoursite.com/your-page-slug/#name={$name}&title={$title}" 
   style="background: #ffeb3b; color: #2c3e50; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; display: inline-block;"&gt;
   Submit Your Testimonial
&lt;/a&gt;</textarea>
                
                <hr style="margin: 30px 0;">
                
                <h3>Step 3: Custom Post Type & ACF Fields</h3>
                <p>The plugin creates testimonial posts with these ACF fields:</p>
                <ul style="line-height: 2;">
                    <li><strong>Post Title:</strong> Customer Name</li>
                    <li><strong>stars:</strong> Rating (0.5 to 5 in half steps)</li>
                    <li><strong>profile_pic:</strong> Profile picture (image ID)</li>
                    <li><strong>testimonial:</strong> Testimonial message</li>
                    <li><strong>title:</strong> Customer title/role</li>
                </ul>
                
                <p><strong>Post Type Slug:</strong> <code>testimonial</code></p>
                <p><em>Make sure your ACF fields match these names exactly!</em></p>
                
                <hr style="margin: 30px 0;">
                
                <h3>🔧 Common Merge Tags by Email Service</h3>
                <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
                    <tr style="background: #f9f9f9;">
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Service</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Name Field</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Custom Field</th>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;">MailerLite</td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>{$name}</code></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>{$custom_field}</code></td>
                    </tr>
                    <tr style="background: #f9f9f9;">
                        <td style="padding: 10px; border: 1px solid #ddd;">Mailchimp</td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>*|FNAME|* *|LNAME|*</code></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>*|MERGE_FIELD|*</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;">ConvertKit</td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>{{ subscriber.first_name }}</code></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>{{ subscriber.field_name }}</code></td>
                    </tr>
                    <tr style="background: #f9f9f9;">
                        <td style="padding: 10px; border: 1px solid #ddd;">ActiveCampaign</td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>%FIRSTNAME% %LASTNAME%</code></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><code>%FIELD_NAME%</code></td>
                    </tr>
                </table>
                
                <hr style="margin: 30px 0;">
                
                <h3>💡 Tips</h3>
                <ul style="line-height: 2;">
                    <li>Use hash (#) not question mark (?) in URLs for better compatibility</li>
                    <li>Replace spaces with <code>%20</code> in URLs: "Joe Smith" → "Joe%20Smith"</li>
                    <li>Test your email links before sending to customers</li>
                    <li>Testimonials are set to "Pending" status for review by default</li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    public function enqueue_assets() {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'testimonial_form')) {
            wp_enqueue_style(
                'testimonial-form-style',
                plugin_dir_url(__FILE__) . 'assets/testimonial-form.css',
                array(),
                '1.0.6'
            );
            
            wp_enqueue_script(
                'testimonial-form-script',
                plugin_dir_url(__FILE__) . 'assets/testimonial-form.js',
                array('jquery'),
                '1.0.6',
                true
            );
            
            wp_localize_script('testimonial-form-script', 'testimonialAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('testimonial_form_nonce')
            ));
        }
    }
    
    public function render_form() {
        ob_start();
        ?>
        <div class="testimonial-form-wrapper">
            <div class="testimonial-card">
                <div id="successMessage" class="success-message">
                    ✓ Testimonial submitted successfully!
                </div>
                <div id="errorMessage" class="error-message"></div>

                <form id="testimonialForm">
                    <div class="profile-section">
                        <label for="profilePic" style="cursor: pointer;">
                            <div class="profile-preview" id="profilePreview">
                                <span class="placeholder">👤</span>
                            </div>
                        </label>
                        <div class="file-input-wrapper">
                            <input type="file" id="profilePic" accept="image/*">
                            <label for="profilePic" class="upload-btn">Upload Profile Picture</label>
                        </div>
                    </div>

                    <div class="star-rating-section">
                        <div class="star-rating-label">Rate your experience</div>
                        <div class="star-rating" id="starRating">
                            <span class="testimonial-star" data-value="1">★</span>
                            <span class="testimonial-star" data-value="2">★</span>
                            <span class="testimonial-star" data-value="3">★</span>
                            <span class="testimonial-star" data-value="4">★</span>
                            <span class="testimonial-star" data-value="5">★</span>
                        </div>
                        <div class="rating-value" id="ratingValue">0 stars</div>
                    </div>

                    <textarea 
                        class="testimonial-input" 
                        id="testimonial" 
                        placeholder='"Share your experience..."'
                        maxlength="500"
                    ></textarea>
                    <div class="char-count"><span id="charCount">0</span>/500</div>

                    <div class="name-title-section">
                        <input 
                            type="text" 
                            class="name-input" 
                            id="nameInput" 
                            placeholder="Your Name"
                        >
                        <input 
                            type="text" 
                            class="title-input" 
                            id="titleInput" 
                            placeholder="Your Title"
                        >
                    </div>
                    
                    <div class="verified-badge">
                        <span class="verified-icon">✓</span>
                        Verified Buyer
                    </div>

                    <button type="submit" class="submit-btn">Submit Testimonial</button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function handle_image_upload() {
        check_ajax_referer('testimonial_form_nonce', 'nonce');
        
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $uploadedfile = $_FILES['file'];
        $upload_overrides = array('test_form' => false);
        
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            $attachment = array(
                'post_mime_type' => $movefile['type'],
                'post_title' => sanitize_file_name($uploadedfile['name']),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $movefile['file']);
            
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
            wp_update_attachment_metadata($attach_id, $attach_data);
            
            wp_send_json_success(array(
                'id' => $attach_id,
                'url' => wp_get_attachment_url($attach_id)
            ));
        } else {
            wp_send_json_error(array('message' => $movefile['error']));
        }
    }
    
    public function handle_submission() {
        check_ajax_referer('testimonial_form_nonce', 'nonce');
        
        // Sanitize input
        $name = sanitize_text_field($_POST['name']);
        $title = sanitize_text_field($_POST['title']);
        $testimonial = sanitize_textarea_field($_POST['testimonial']);
        $rating = floatval($_POST['rating']);
        $profile_pic_id = intval($_POST['profile_pic_id']);
        
        // Validate
        if (empty($name) || empty($title) || empty($testimonial) || $rating <= 0) {
            wp_send_json_error(array('message' => 'Please fill in all required fields.'));
            return;
        }
        
        // Create the post
        $post_data = array(
            'post_title'   => $name,
            'post_content' => $testimonial,
            'post_status'  => 'pending', // Change to 'publish' if you want auto-publish
            'post_type'    => 'testimonial', // Change to your custom post type slug
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => 'Failed to create testimonial.'));
            return;
        }
        
        // Update ACF fields
        if (function_exists('update_field')) {
            update_field('stars', $rating, $post_id);
            update_field('title', $title, $post_id);
            update_field('testimonial', $testimonial, $post_id);
            
            if ($profile_pic_id) {
                update_field('profile_pic', $profile_pic_id, $post_id);
            }
        }
        
        wp_send_json_success(array(
            'message' => 'Testimonial submitted successfully!',
            'post_id' => $post_id
        ));
    }
}

// Initialize the plugin
new Testimonial_Form_Plugin();