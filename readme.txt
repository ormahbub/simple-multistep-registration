
=== Simple Multi-Step Registration ===
Contributors: Mahbub
Tags: registration, multi-step, form, verification, email, sms, phone, user, signup
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.2
Stable tag: 2.0
License: Free
License URI: #

A modern 4-step registration form with email and SMS verification for WordPress.

== Description ==

Transform your WordPress registration process with this professional multi-step form plugin. Perfect for membership sites, online communities, and any website requiring secure user registration.

= Key Features =

* **4-Step Registration Process**: Clean, intuitive interface with progress indicator
* **Email Verification**: Real email sending with beautiful HTML templates
* **SMS Verification**: Free SMS integration (Textbelt & Clockwork SMS)
* **Country Flags**: 20+ countries with dynamic phone validation
* **Password Strength Meter**: Real-time password security feedback
* **Mobile Responsive**: Works perfectly on all devices
* **Security Focused**: Rate limiting, code expiration, and input sanitization
* **Admin Dashboard**: Easy configuration panel
* **Translation Ready**: Full internationalization support

= Why Choose This Plugin? =

* **User Experience**: Reduce form abandonment with multi-step design
* **Security**: Double verification (email + SMS) for maximum security
* **Flexibility**: Works with free SMS services or paid APIs
* **Professional**: Modern design that matches your brand
* **Lightweight**: Minimal impact on site performance

= SMS Verification Options =

1. **Textbelt**: 1 free SMS per day (US/Canada only, no setup required)
2. **Clockwork SMS**: 5 free SMS credits, then pay-as-you-go (global coverage)
3. **Test Mode**: Display codes on screen for development/testing

= Perfect For =

* Membership websites
* E-learning platforms
* Online communities
* Booking systems
* Any site needing secure registration

== Installation ==

1. Upload the `simple-multistep-registration` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the shortcode `[smsr_shortcode]` to any page or post
4. Configure SMS settings at Settings → SMS Registration (optional)

For detailed installation instructions, visit our [GitHub repository](https://github.com/ormahbub/simple-multistep-registration).

== Frequently Asked Questions ==

= How do I add the form to my site? =
Use the shortcode `[smsr_shortcode]` in any page, post, or widget area.

= SMS verification isn't working. What should I do? =
1. Enable "Test Mode" in plugin settings to display codes on screen
2. Verify your phone number format is correct
3. Check that free SMS quota isn't exhausted (Textbelt: 1/day)
4. Consider getting a Clockwork SMS API key for reliable service

= Can I customize the form design? =
Yes! The plugin uses CSS classes that you can override in your theme's style.css file.

= Is this plugin translation ready? =
Absolutely! The plugin includes .pot file for translations. Use Poedit or similar tools to translate.

= What countries are supported for phone validation? =
The plugin includes validation for 20+ countries including US, UK, Canada, India, Australia, and more. Each country has specific format validation.

= How secure is the verification system? =
Very secure! Features include:
* Rate limiting (3 attempts per 10 minutes)
* Code expiration (10 minutes)
* No permanent storage of verification codes
* WordPress nonce verification
* Input sanitization

== Screenshots ==

1. Step 1: Contact Information - The first step collects name, email, phone, and password
2. Step 2: Terms and Conditions - Users must agree to terms before proceeding
3. Step 3: Email Verification - Enter 6-digit code sent via email
4. Step 4: SMS Verification - Enter 6-digit code sent via SMS
5. Success Message - Registration complete with login link
6. Admin Settings - Configure SMS API and email settings

== Changelog ==

= 2.0 =
* Added email verification using WordPress wp_mail()
* Added SMS verification with free APIs (Textbelt & Clockwork SMS)
* Added admin settings panel for configuration
* Added password strength meter
* Added rate limiting for security
* Added HTML email templates
* Improved phone validation with country-specific patterns
* Enhanced error handling and user feedback
* Added verification logs table (optional)

= 1.0 =
* Initial release
* 4-step registration form
* Country flag selection
* Basic form validation
* AJAX registration

== Upgrade Notice ==

= 2.0 =
Major update with email and SMS verification. Backup your site before upgrading.

== Arbitrary section ==

You may provide arbitrary sections, in the same format as the ones above. It may be of use for extremely complicated plugins where more information needs to be conveyed that doesn't fit into the categories of "description" or "installation." Arbitrary sections will be shown below the built-in sections outlined above.

== Developer Documentation ==

= Hooks and Filters =

The plugin provides several hooks for customization:

**Filters:**
* `smsr_email_template` - Customize email verification template
* `smsr_code_length` - Change verification code length
* `smsr_country_list` - Modify available countries

**Actions:**
* `smsr_before_registration` - Run before user creation
* `smsr_after_registration` - Run after successful registration
* `smsr_before_send_code` - Before sending verification code

