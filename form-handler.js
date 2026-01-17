document.addEventListener("DOMContentLoaded", function () {
  let currentStep = 1;
  const totalSteps = 4;
  const nextBtn = document.getElementById("nextBtn");
  const prevBtn = document.getElementById("prevBtn");
  const flagSelector = document.getElementById("flag-selector");
  const flagDropdown = document.getElementById("flag-dropdown");
  const selectedFlag = document.getElementById("selected-flag");
  const countryCodeSpan = document.getElementById("country-code");
  const phoneInput = document.getElementById("user_phone");
  const emailInput = document.getElementById("user_email");
  const emailSendBtn = document.getElementById("send-email-code");
  const smsSendBtn = document.getElementById("send-sms-code");

  // Get AJAX URL from localized script
  const ajaxUrl = smsr_ajax.ajax_url;
  const ajaxNonce = smsr_ajax.nonce;
  const siteUrl = smsr_ajax.site_url;

  // Country data with flags and phone codes
  const countries = [
    {
      code: "US",
      name: "United States",
      dial_code: "+1",
      flag: "https://flagcdn.com/w20/us.png",
      pattern: /^(\d{3})?(\d{3})?(\d{4})$/,
    },
    {
      code: "GB",
      name: "United Kingdom",
      dial_code: "+44",
      flag: "https://flagcdn.com/w20/gb.png",
      pattern: /^(\d{5})?(\d{6})$/,
    },
    {
      code: "BD",
      name: "Bangladesh",
      dial_code: "+880",
      flag: "https://flagcdn.com/w20/bd.png",
      pattern: /^(01)[3-9]\d{8}$/,
    },
    {
      code: "IN",
      name: "India",
      dial_code: "+91",
      flag: "https://flagcdn.com/w20/in.png",
      pattern: /^[6-9]\d{9}$/,
    },
    {
      code: "CA",
      name: "Canada",
      dial_code: "+1",
      flag: "https://flagcdn.com/w20/ca.png",
      pattern: /^(\d{3})?(\d{3})?(\d{4})$/,
    },
    {
      code: "AU",
      name: "Australia",
      dial_code: "+61",
      flag: "https://flagcdn.com/w20/au.png",
      pattern: /^(04\d{8})$/,
    },
    {
      code: "DE",
      name: "Germany",
      dial_code: "+49",
      flag: "https://flagcdn.com/w20/de.png",
      pattern: /^(\d{3})?(\d{3})?(\d{4,5})$/,
    },
    {
      code: "FR",
      name: "France",
      dial_code: "+33",
      flag: "https://flagcdn.com/w20/fr.png",
      pattern: /^(\d{3})?(\d{2})?(\d{2})?(\d{2})?(\d{2})$/,
    },
    {
      code: "IT",
      name: "Italy",
      dial_code: "+39",
      flag: "https://flagcdn.com/w20/it.png",
      pattern: /^(\d{3})?(\d{3})?(\d{4})$/,
    },
    {
      code: "ES",
      name: "Spain",
      dial_code: "+34",
      flag: "https://flagcdn.com/w20/es.png",
      pattern: /^(\d{3})?(\d{3})?(\d{3})$/,
    },
    {
      code: "JP",
      name: "Japan",
      dial_code: "+81",
      flag: "https://flagcdn.com/w20/jp.png",
      pattern: /^(\d{3})?(\d{4})?(\d{4})$/,
    },
    {
      code: "CN",
      name: "China",
      dial_code: "+86",
      flag: "https://flagcdn.com/w20/cn.png",
      pattern: /^(\d{3})?(\d{4})?(\d{4})$/,
    },
    {
      code: "RU",
      name: "Russia",
      dial_code: "+7",
      flag: "https://flagcdn.com/w20/ru.png",
      pattern: /^(\d{3})?(\d{3})?(\d{2})?(\d{2})$/,
    },
    {
      code: "BR",
      name: "Brazil",
      dial_code: "+55",
      flag: "https://flagcdn.com/w20/br.png",
      pattern: /^(\d{2})?(\d{4,5})?(\d{4})$/,
    },
    {
      code: "MX",
      name: "Mexico",
      dial_code: "+52",
      flag: "https://flagcdn.com/w20/mx.png",
      pattern: /^(\d{3})?(\d{3})?(\d{4})$/,
    },
    {
      code: "SA",
      name: "Saudi Arabia",
      dial_code: "+966",
      flag: "https://flagcdn.com/w20/sa.png",
      pattern: /^(\d{2})?(\d{3})?(\d{4})$/,
    },
    {
      code: "AE",
      name: "UAE",
      dial_code: "+971",
      flag: "https://flagcdn.com/w20/ae.png",
      pattern: /^(\d{2})?(\d{3})?(\d{4})$/,
    },
    {
      code: "SG",
      name: "Singapore",
      dial_code: "+65",
      flag: "https://flagcdn.com/w20/sg.png",
      pattern: /^[689]\d{7}$/,
    },
    {
      code: "MY",
      name: "Malaysia",
      dial_code: "+60",
      flag: "https://flagcdn.com/w20/my.png",
      pattern: /^(\d{2})?(\d{3})?(\d{4})$/,
    },
    {
      code: "PK",
      name: "Pakistan",
      dial_code: "+92",
      flag: "https://flagcdn.com/w20/pk.png",
      pattern: /^(\d{2})?(\d{3})?(\d{4})$/,
    },
  ];

  // Current selected country
  let selectedCountry = countries[0];
  let emailCheckTimeout = null;

  // 1. Initialize flag dropdown
  function initFlagDropdown() {
    flagDropdown.innerHTML = "";
    countries.forEach((country) => {
      const flagItem = document.createElement("div");
      flagItem.className = "flag-item";
      flagItem.innerHTML = `
        <img src="${country.flag}" alt="${country.code}" width="20">
        <span class="flag-name">${country.name}</span>
        <span class="flag-code">${country.dial_code}</span>
      `;

      flagItem.addEventListener("click", () => {
        selectCountry(country);
      });

      flagDropdown.appendChild(flagItem);
    });
  }

  // 2. Select country function
  function selectCountry(country) {
    selectedCountry = country;
    selectedFlag.src = country.flag;
    selectedFlag.alt = country.code;
    countryCodeSpan.textContent = country.dial_code;
    flagDropdown.style.display = "none";

    // Update phone placeholder and validation
    updatePhonePlaceholder(country);

    // Re-validate phone if there's already a value
    if (phoneInput.value.trim()) {
      validatePhoneNumber(phoneInput.value);
    }
  }

  function updatePhonePlaceholder(country) {
    switch (country.code) {
      case "US":
      case "CA":
        phoneInput.placeholder = "(123) 456-7890";
        break;
      case "GB":
        phoneInput.placeholder = "07123 456789";
        break;
      case "BD":
        phoneInput.placeholder = "017XXXXXXXX";
        break;
      case "IN":
        phoneInput.placeholder = "98765 43210";
        break;
      case "AU":
        phoneInput.placeholder = "0412 345 678";
        break;
      case "SG":
        phoneInput.placeholder = "9123 4567";
        break;
      default:
        phoneInput.placeholder = "Enter phone number";
    }
  }

  // 3. Toggle flag dropdown
  flagSelector.addEventListener("click", (e) => {
    e.stopPropagation();
    flagDropdown.style.display =
      flagDropdown.style.display === "block" ? "none" : "block";
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", () => {
    flagDropdown.style.display = "none";
  });

  // 4. Password Visibility Toggle
  document.querySelectorAll(".toggle-password").forEach((icon) => {
    icon.addEventListener("click", function () {
      const input = this.parentElement.querySelector(".password-field");
      const isPassword = input.type === "password";
      input.type = isPassword ? "text" : "password";
      this.classList.toggle("fa-eye-slash", !isPassword);
      this.classList.toggle("fa-eye", isPassword);
    });
  });

  // 5. Password Strength Checker
  const mainPwdInput = document.getElementById("main_pwd");
  const strengthBar = document.querySelector(".strength-bar");
  const strengthText = document.querySelector(".strength-text");

  function checkPasswordStrength(password) {
    let strength = 0;

    // Length check
    if (password.length >= 8) strength += 1;
    if (password.length >= 12) strength += 1;

    // Complexity checks
    if (/[a-z]/.test(password)) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;

    // Update UI
    const width = (strength / 6) * 100;
    strengthBar.style.width = width + "%";

    if (strength <= 2) {
      strengthBar.style.backgroundColor = "#ff4d4d";
      strengthText.textContent = "Weak";
      strengthText.style.color = "#ff4d4d";
    } else if (strength <= 4) {
      strengthBar.style.backgroundColor = "#ffa500";
      strengthText.textContent = "Medium";
      strengthText.style.color = "#ffa500";
    } else {
      strengthBar.style.backgroundColor = "#4CAF50";
      strengthText.textContent = "Strong";
      strengthText.style.color = "#4CAF50";
    }
  }

  mainPwdInput.addEventListener("input", function () {
    checkPasswordStrength(this.value);
  });

  // 6. Email availability check
  emailInput.addEventListener("input", function () {
    clearTimeout(emailCheckTimeout);

    const email = this.value.trim();
    const statusSpan = document.getElementById("email-availability");

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      statusSpan.textContent = "";
      return;
    }

    statusSpan.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';

    emailCheckTimeout = setTimeout(() => {
      // Simple client-side check (full check happens server-side)
      statusSpan.innerHTML = '<i class="fas fa-check"></i> Valid E-mail';
      statusSpan.style.color = "#4CAF50";
    }, 500);
  });

  // 7. Phone number validation
  function validatePhoneNumber(phone) {
    const phoneError = document.getElementById("phone-error");
    const pattern = selectedCountry.pattern;

    // Clean the phone number
    const cleanPhone = phone.replace(/\D/g, "");

    if (!cleanPhone) {
      phoneError.textContent = "Phone number is required";
      phoneError.style.display = "block";
      return false;
    }

    // Country-specific validation
    if (pattern && !pattern.test(cleanPhone)) {
      phoneError.textContent =
        "Invalid phone number format for " + selectedCountry.name;
      phoneError.style.display = "block";
      return false;
    }

    // General length check
    if (cleanPhone.length < 7 || cleanPhone.length > 15) {
      phoneError.textContent = "Phone number should be between 7-15 digits";
      phoneError.style.display = "block";
      return false;
    }

    phoneError.style.display = "none";
    return true;
  }

  phoneInput.addEventListener("input", function () {
    validatePhoneNumber(this.value);
  });

  // 8. UI Update Function
  function updateUI() {
    // Update sections
    document
      .querySelectorAll(".form-section")
      .forEach((s) => s.classList.remove("active"));
    document.getElementById(`section-${currentStep}`).classList.add("active");

    // Update stepper
    document.querySelectorAll(".step").forEach((s, idx) => {
      s.classList.toggle("active", idx + 1 <= currentStep);
    });

    // Update buttons
    prevBtn.style.display = currentStep === 1 ? "none" : "inline-block";
    nextBtn.innerText = currentStep === totalSteps ? "REGISTER" : "NEXT";

    // Auto-send verification codes when reaching steps
    if (currentStep === 3 && emailInput.value) {
      // Auto-send email code after 1 second
      setTimeout(() => {
        if (!document.getElementById("email_code").value) {
          sendEmailCode();
        }
      }, 1000);
    }

    if (currentStep === 4 && phoneInput.value) {
      // Auto-send SMS code after 1 second
      setTimeout(() => {
        if (!document.getElementById("sms_code").value) {
          sendSMSCode();
        }
      }, 1000);
    }
  }

  // 9. Validate Step 1
  function validateStep1() {
    let valid = true;

    // Check required fields
    const requiredFields = document.querySelectorAll(
      "#section-1 input[required]",
    );
    requiredFields.forEach((field) => {
      if (!field.value.trim()) {
        field.reportValidity();
        valid = false;
      }
    });

    if (!valid) return false;

    // Email validation
    const email = emailInput.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      alert("Please enter a valid email address");
      return false;
    }

    // Password match validation
    const pwd = mainPwdInput.value;
    const confirm = document.getElementById("confirm_pwd").value;
    const errorMsg = document.getElementById("pass-error");

    if (pwd !== confirm) {
      errorMsg.textContent = "Passwords do not match";
      errorMsg.style.display = "block";
      return false;
    } else {
      errorMsg.style.display = "none";
    }

    // Password strength
    if (pwd.length < 6) {
      alert("Password must be at least 6 characters long");
      return false;
    }

    // Phone validation
    if (!validatePhoneNumber(phoneInput.value)) {
      return false;
    }

    return true;
  }

  // 10. Validate other steps
  function validateStep(step) {
    if (step === 1) return validateStep1();

    if (step === 2) {
      const termsCheckbox = document.getElementById("terms_agree");
      if (!termsCheckbox.checked) {
        alert("You must agree to the terms and conditions");
        return false;
      }
      return true;
    }

    if (step === 3) {
      const emailCode = document.getElementById("email_code").value;
      if (!emailCode || !/^\d{6}$/.test(emailCode)) {
        const emailError = document.getElementById("email-error");
        emailError.textContent = "Please enter a valid 6-digit code";
        emailError.style.color = "#ff4d4d";
        emailError.style.display = "block";
        return false;
      }
      return true;
    }

    if (step === 4) {
      const smsCode = document.getElementById("sms_code").value;
      if (!smsCode || !/^\d{6}$/.test(smsCode)) {
        const smsError = document.getElementById("sms-error");
        smsError.textContent = "Please enter a valid 6-digit code";
        smsError.style.color = "#ff4d4d";
        smsError.style.display = "block";
        return false;
      }
      return true;
    }

    return true;
  }

  // 11. Navigation Handler
  nextBtn.addEventListener("click", () => {
    if (!validateStep(currentStep)) {
      return;
    }

    if (currentStep < totalSteps) {
      currentStep++;
      updateUI();
    } else {
      // Submit registration
      submitRegistration();
    }
  });

  prevBtn.addEventListener("click", () => {
    if (currentStep > 1) {
      currentStep--;
      updateUI();
    }
  });

  // 12. Phone input formatting
  phoneInput.addEventListener("input", function (e) {
    // Remove non-numeric characters except + and -
    this.value = this.value.replace(/[^\d\+\-\s\(\)]/g, "");
  });

  // 13. Timer function for resend buttons
  function startTimer(buttonId, timerId, timeSpanId, duration = 60) {
    const button = document.getElementById(buttonId);
    const timer = document.getElementById(timerId);
    const timeSpan = document.getElementById(timeSpanId);

    button.disabled = true;
    button.style.opacity = "0.5";
    timer.style.display = "block";

    let timeLeft = duration;
    timeSpan.textContent = timeLeft;

    const countdown = setInterval(() => {
      timeLeft--;
      timeSpan.textContent = timeLeft;

      if (timeLeft <= 0) {
        clearInterval(countdown);
        button.disabled = false;
        button.style.opacity = "1";
        timer.style.display = "none";

        // Update button text for retry
        if (buttonId === "send-email-code") {
          button.innerHTML = "Resend Code";
        } else if (buttonId === "send-sms-code") {
          button.innerHTML = "Resend SMS";
        }
      }
    }, 1000);
  }

  // 14. Send email verification code
  function sendEmailCode() {
    const email = emailInput.value;
    const firstName = document.getElementById("first_name").value || "User";
    const emailError = document.getElementById("email-error");
    const button = emailSendBtn;

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      emailError.textContent = "Please enter a valid email address first";
      emailError.style.color = "#ff4d4d";
      emailError.style.display = "block";
      return;
    }

    // Disable button and show loading
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    emailError.style.display = "none";

    // Send AJAX request
    const formData = new FormData();
    formData.append("action", "smsr_send_email_code");
    formData.append("email", email);
    formData.append("first_name", firstName);
    formData.append("nonce", ajaxNonce);

    fetch(ajaxUrl, {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Show success message
          emailError.textContent = data.data.message;
          emailError.style.color = "#4CAF50";
          emailError.style.display = "block";

          // Start timer for resend
          startTimer("send-email-code", "email-timer", "email-time", 60);

          // Auto-focus the code input
          document.getElementById("email_code").focus();
        } else {
          emailError.textContent = data.data;
          emailError.style.color = "#ff4d4d";
          emailError.style.display = "block";
          button.disabled = false;
          button.innerHTML = "Send Code";
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        emailError.textContent = "Network error. Please try again.";
        emailError.style.color = "#ff4d4d";
        emailError.style.display = "block";
        button.disabled = false;
        button.innerHTML = "Send Code";
      });
  }

  // 15. Send SMS verification code
  function sendSMSCode() {
    const phone = phoneInput.value;
    const firstName = document.getElementById("first_name").value || "User";
    const smsError = document.getElementById("sms-error");
    const button = smsSendBtn;

    if (!validatePhoneNumber(phone)) {
      smsError.style.display = "block";
      return;
    }

    // Disable button and show loading
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    smsError.style.display = "none";

    // Send AJAX request
    const formData = new FormData();
    formData.append("action", "smsr_send_sms_code");
    formData.append("phone", phone.replace(/\D/g, ""));
    formData.append("country_code", selectedCountry.dial_code);
    formData.append("first_name", firstName);
    formData.append("nonce", ajaxNonce);

    fetch(ajaxUrl, {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Show success message
          let message = data.data.message;
          if (data.data.test_code) {
            message += ` (Test code: ${data.data.test_code})`;
          }
          smsError.textContent = message;
          smsError.style.color = "#4CAF50";
          smsError.style.display = "block";

          // Start timer for resend
          startTimer("send-sms-code", "sms-timer", "sms-time", 60);

          // Auto-focus the code input
          document.getElementById("sms_code").focus();
        } else {
          smsError.textContent = data.data;
          smsError.style.color = "#ff4d4d";
          smsError.style.display = "block";
          button.disabled = false;
          button.innerHTML = "Send SMS";
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        smsError.textContent = "Network error. Please try again.";
        smsError.style.color = "#ff4d4d";
        smsError.style.display = "block";
        button.disabled = false;
        button.innerHTML = "Send SMS";
      });
  }

  // Attach button click events
  emailSendBtn.addEventListener("click", sendEmailCode);
  smsSendBtn.addEventListener("click", sendSMSCode);

  // 16. Registration Submission
  function submitRegistration() {
    // Collect all form data
    const formData = new FormData();
    formData.append("action", "smsr_register_user");
    formData.append(
      "security",
      document.querySelector('input[name="security"]').value,
    );
    formData.append("email", emailInput.value);
    formData.append("first_name", document.getElementById("first_name").value);
    formData.append("last_name", document.getElementById("last_name").value);
    formData.append("password", mainPwdInput.value);
    formData.append("phone", phoneInput.value.replace(/\D/g, ""));
    formData.append("country_code", selectedCountry.dial_code);
    formData.append(
      "terms_agree",
      document.getElementById("terms_agree").checked ? "1" : "0",
    );
    formData.append("email_code", document.getElementById("email_code").value);
    formData.append("sms_code", document.getElementById("sms_code").value);

    // Disable button and show processing
    nextBtn.innerText = "PROCESSING...";
    nextBtn.disabled = true;

    // Send AJAX request
    fetch(ajaxUrl, {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Show success message
          const form = document.getElementById("multi-step-form");
          form.innerHTML = `
            <div class="success-msg">
              <i class="fas fa-check-circle" style="font-size: 48px; color: #4CAF50; margin-bottom: 20px;"></i>
              <h3>Registration Successful!</h3>
              <p>${data.data.message}</p>
              <p>Your account has been created and verified successfully.</p>
              <p>Check your email for welcome message.</p>
              <p>You can now <a href="${data.data.redirect_url || siteUrl + "/wp-login.php"}">log in</a> to your account.</p>
              <p><small>User ID: ${data.data.user_id}</small></p>
            </div>
          `;
        } else {
          alert("Error: " + data.data);
          nextBtn.innerText = "REGISTER";
          nextBtn.disabled = false;
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("An error occurred. Please try again.");
        nextBtn.innerText = "REGISTER";
        nextBtn.disabled = false;
      });
  }

  // Initialize
  initFlagDropdown();
  selectCountry(countries[0]); // Default to US
  updateUI();
});
