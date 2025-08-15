/**
 * Web Auth Handler for Backend Web Interface
 * 
 * Menangani session timeout dan re-authentication untuk web interface
 */

class WebAuthHandler {
    constructor() {
        this.sessionCheckInterval = 5 * 60 * 1000; // Check setiap 5 menit
        this.warningTime = 2 * 60 * 1000; // Warning 2 menit sebelum expired
        this.lastActivity = Date.now();
        this.sessionTimeout = 30 * 60 * 1000; // 30 menit default
        
        this.init();
    }

    init() {
        this.trackUserActivity();
        this.startSessionCheck();
        this.setupAjaxErrorHandler();
    }

    // Track user activity
    trackUserActivity() {
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, () => {
                this.lastActivity = Date.now();
            }, { passive: true });
        });
    }

    // Start periodic session check
    startSessionCheck() {
        setInterval(() => {
            this.checkSession();
        }, this.sessionCheckInterval);
    }

    // Check session status
    async checkSession() {
        try {
            const response = await fetch(base_url + 'backend/auth/check-session', {
                method: 'GET',
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (!data.success || !data.authenticated) {
                this.handleSessionExpired();
                return;
            }

            // Check if session will expire soon
            const timeLeft = data.time_left || 0;
            if (timeLeft > 0 && timeLeft <= this.warningTime / 1000) {
                this.showSessionWarning(timeLeft);
            }

        } catch (error) {
            console.error('Session check failed:', error);
        }
    }

    // Handle session expired
    handleSessionExpired() {
        // Show modal atau redirect
        this.showSessionExpiredModal();
    }

    // Show session warning
    showSessionWarning(timeLeft) {
        if ($('#sessionWarningModal').length === 0) {
            this.createSessionWarningModal();
        }

        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        
        $('#sessionWarningModal .session-time-left').text(
            `${minutes}:${seconds.toString().padStart(2, '0')}`
        );
        
        $('#sessionWarningModal').modal('show');
    }

    // Create session warning modal
    createSessionWarningModal() {
        const modal = `
            <div class="modal fade" id="sessionWarningModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title">
                                <i class="fas fa-clock me-2"></i>
                                Sesi Akan Berakhir
                            </h5>
                        </div>
                        <div class="modal-body text-center">
                            <p>Sesi Anda akan berakhir dalam:</p>
                            <h4 class="text-danger session-time-left">--:--</h4>
                            <p class="small text-muted">Klik "Perpanjang Sesi" untuk melanjutkan.</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-success" onclick="webAuthHandler.extendSession()">
                                <i class="fas fa-refresh me-1"></i>
                                Perpanjang Sesi
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="webAuthHandler.logout()">
                                <i class="fas fa-sign-out-alt me-1"></i>
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modal);
    }

    // Show session expired modal
    showSessionExpiredModal() {
        if ($('#sessionExpiredModal').length === 0) {
            this.createSessionExpiredModal();
        }
        
        $('#sessionExpiredModal').modal('show');
    }

    // Create session expired modal
    createSessionExpiredModal() {
        const modal = `
            <div class="modal fade" id="sessionExpiredModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Sesi Berakhir
                            </h5>
                        </div>
                        <div class="modal-body text-center">
                            <p>Sesi Anda telah berakhir.</p>
                            <p class="small text-muted">Silakan login kembali untuk melanjutkan.</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-primary" onclick="webAuthHandler.redirectToLogin()">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                Login Kembali
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modal);
    }

    // Extend session
    async extendSession() {
        try {
            const response = await fetch(base_url + 'backend/auth/extend-session', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                $('#sessionWarningModal').modal('hide');
                
                // Show success notification
                if (typeof $.notify !== 'undefined') {
                    $.notify({
                        message: 'Sesi berhasil diperpanjang'
                    }, {
                        type: 'success',
                        delay: 3000
                    });
                }
            } else {
                this.handleSessionExpired();
            }

        } catch (error) {
            console.error('Extend session failed:', error);
            this.handleSessionExpired();
        }
    }

    // Setup AJAX error handler
    setupAjaxErrorHandler() {
        $(document).ajaxError((event, xhr, settings, error) => {
            if (xhr.status === 401 || xhr.status === 403) {
                // Check if it's session expired
                try {
                    const response = xhr.responseJSON;
                    if (response && (response.message.includes('session') || response.message.includes('login'))) {
                        this.handleSessionExpired();
                        return;
                    }
                } catch (e) {
                    // Ignore JSON parse errors
                }
            }
            
            // Handle other errors
            if (xhr.status >= 500) {
                console.error('Server error:', xhr.status, error);
            }
        });

        // Add CSRF token to all AJAX requests
        $.ajaxSetup({
            beforeSend: (xhr, settings) => {
                // Add CSRF token if available
                const csrfToken = $('meta[name="X-CSRF-TOKEN"]').attr('content');
                if (csrfToken && !settings.headers['X-CSRF-TOKEN']) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                }
                
                // Add X-Requested-With header
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            }
        });
    }

    // Logout
    logout() {
        window.location.href = base_url + 'backend/auth/logout';
    }

    // Redirect to login
    redirectToLogin() {
        window.location.href = base_url + 'backend/auth/login';
    }
}

// Initialize when document ready
$(document).ready(function() {
    if (typeof base_url !== 'undefined') {
        window.webAuthHandler = new WebAuthHandler();
    }
});
