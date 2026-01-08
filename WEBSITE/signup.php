<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Legend House</title>
    <link rel="stylesheet" href="auth-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-bg-pattern"></div>
        
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <svg width="48" height="48" viewBox="0 0 48 48">
                        <rect x="8" y="14" width="32" height="24" rx="3" fill="none" stroke="currentColor" stroke-width="3"/>
                        <polygon points="24,7 32,14 16,14" fill="currentColor"/>
                        <rect x="19" y="24" width="10" height="14" fill="currentColor" opacity="0.7"/>
                    </svg>
                </div>
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join Legend House today</p>
            </div>
            
            <form id="signupForm" class="auth-form">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="form-input-wrapper">
                        <svg class="form-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <input type="text" id="username" name="username" class="form-input" placeholder="Choose a username" required autocomplete="username" pattern="[a-zA-Z0-9_]{3,20}">
                    </div>
                    <small class="form-hint">3-20 characters, letters, numbers, and underscores</small>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="form-input-wrapper">
                        <svg class="form-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" required autocomplete="email">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="form-input-wrapper">
                        <svg class="form-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Create a password" required autocomplete="new-password" minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <svg id="eyeIcon1" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                    <small class="form-hint">At least 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <div class="form-input-wrapper">
                        <svg class="form-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" placeholder="Confirm your password" required autocomplete="new-password" minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                            <svg id="eyeIcon2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required>
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-text">I agree to the Terms of Service and Privacy Policy</span>
                </label>
                
                <button type="submit" class="btn-submit" id="submitBtn">
                    <span class="btn-text">Create Account</span>
                    <svg class="btn-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                </button>
                
                <div id="errorMessage" class="error-message" style="display: none;"></div>
                
                <div class="divider">
                    <span class="divider-text">or sign up with</span>
                </div>
                
                <button type="button" class="btn-google" onclick="signUpWithGoogle()">
                    <svg class="google-icon" viewBox="0 0 24 24" width="20" height="20">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span class="btn-text">Sign up with Google</span>
                </button>
                <p style="font-size: 0.75rem; color: #999; text-align: center; margin-top: 0.5rem;">
                    If Google OAuth is not configured, use email/password above
                </p>
            </form>
            
            <div class="auth-footer">
                <p class="auth-footer-text">
                    Already have an account? 
                    <a href="login.php" class="auth-link">Sign in</a>
                </p>
                <a href="index.php" class="back-home">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                    Back to Home
                </a>
            </div>
        </div>
        
        <div class="auth-features">
            <div class="feature-item">
                <div class="feature-icon">🎬</div>
                <div class="feature-text">
                    <h3>Stream Instantly</h3>
                    <p>Watch movies in your browser</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">📥</div>
                <div class="feature-text">
                    <h3>Download Torrents</h3>
                    <p>Access to millions of files</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">💾</div>
                <div class="feature-text">
                    <h3>Save History</h3>
                    <p>Track your downloads</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>
    
    <script>
        const signupForm = document.getElementById('signupForm');
        const submitBtn = document.getElementById('submitBtn');
        const errorMessage = document.getElementById('errorMessage');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const passwordStrength = document.getElementById('passwordStrength');
        
        // Password strength indicator
        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            let strength = 0;
            let text = '';
            let color = '';
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if (password.length === 0) {
                passwordStrength.style.display = 'none';
                return;
            }
            
            passwordStrength.style.display = 'block';
            
            if (strength <= 2) {
                text = 'Weak';
                color = '#ef4444';
            } else if (strength === 3) {
                text = 'Medium';
                color = '#f59e0b';
            } else {
                text = 'Strong';
                color = '#10b981';
            }
            
            passwordStrength.innerHTML = `<span style="color: ${color}">● ${text}</span>`;
        });
        
        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Validation
            if (!username || !email || !password || !confirmPassword) {
                showError('Please fill in all fields');
                return;
            }
            
            if (password !== confirmPassword) {
                showError('Passwords do not match');
                return;
            }
            
            if (password.length < 6) {
                showError('Password must be at least 6 characters');
                return;
            }
            
            if (!/^[a-zA-Z0-9_]{3,20}$/.test(username)) {
                showError('Invalid username format');
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            hideError();
            
            try {
                const formData = new FormData();
                formData.append('action', 'register');
                formData.append('username', username);
                formData.append('email', email);
                formData.append('password', password);
                
                const response = await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Account created! Redirecting to login...', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    showError(data.error || 'Registration failed');
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                }
            } catch (error) {
                showError('Network error. Please try again.');
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
            }
        });
        
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(inputId === 'password' ? 'eyeIcon1' : 'eyeIcon2');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                      <line x1="1" y1="1" x2="23" y2="23"/>`;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                     <circle cx="12" cy="12" r="3"/>`;
            }
        }
        
        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
        }
        
        function hideError() {
            errorMessage.style.display = 'none';
        }
        
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast toast-${type} show`;
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
        
        // Google Sign-Up (uses same OAuth flow as sign-in)
        async function signUpWithGoogle() {
            try {
                const response = await fetch('auth.php?action=google_auth_url');
                const data = await response.json();
                
                if (data.success && data.url) {
                    window.location.href = data.url;
                } else {
                    showError(data.error || 'Google Sign-Up is not available. Please use email/password signup or contact the administrator.');
                }
            } catch (error) {
                showError('Network error. Please try again.');
            }
        }
        
        // Check for URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error')) {
            showError(urlParams.get('error'));
        }
    </script>
</body>
</html>
