# Simple Multi-Step Registration Plugin for WordPress

A professional, secure, and feature-rich multi-step registration form plugin for WordPress with email and SMS verification capabilities.

## 📋 Overview

Simple Multi-Step Registration provides a modern, user-friendly registration experience with built-in verification systems. The plugin transforms standard WordPress registration into a seamless 4-step process with email and SMS verification using free services.

### 🎯 Key Features

- **4-Step Registration Process**: Clean, intuitive multi-step interface
- **Email Verification**: Real email sending via WordPress `wp_mail()` with HTML templates
- **SMS Verification**: Integration with free SMS APIs (Textbelt & Clockwork SMS)
- **Country Flags**: 20+ country flags with dynamic phone number validation
- **Password Strength Meter**: Real-time password strength indicator
- **Mobile Responsive**: Fully responsive design for all devices
- **Security Focused**: Rate limiting, transient storage, and nonce protection
- **Admin Dashboard**: Configuration panel for SMS settings
- **No External Dependencies**: Self-contained solution

## 🚀 Installation

### Method 1: WordPress Admin (Recommended)

1. Download the plugin zip file
2. Navigate to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the zip file
4. Click "Install Now" then "Activate"

### Method 2: Manual Installation

1. Upload the `simple-multistep-registration` folder to `/wp-content/plugins/`
2. Navigate to WordPress Admin → Plugins
3. Find "Simple Multi-Step Registration" and click "Activate"

## ⚙️ Configuration

### Basic Setup

1. **Add Form to Page**: Use shortcode `[smsr_shortcode]` in any page or post
2. **Configure Email Settings**: Set sender name/email in plugin settings
3. **Test Functionality**: Verify both email and SMS verification work

### SMS API Configuration

1. **Textbelt**: Works out of the box (1 free SMS/day, US/Canada only)
2. **Clockwork SMS** (Recommended for production):
   - Sign up at [clockworksms.com](https://www.clockworksms.com/)
   - Get API key from dashboard
   - Enter key in WordPress Admin → Settings → SMS Registration
3. **Test Mode**: Enable in settings to display codes on screen (for development)

### Email Configuration

Ensure WordPress can send emails by:

- Configuring SMTP plugin (recommended)
- Checking server mail configuration
- Verifying emails don't go to spam

## 📱 Usage

### Shortcodes

- **Basic Form**: `[smsr_shortcode]`

### The 4-Step Process

1. **Step 1**: Contact information (name, email, phone, password)
2. **Step 2**: Terms and conditions agreement
3. **Step 3**: Email verification (6-digit code)
4. **Step 4**: SMS verification (6-digit code)
