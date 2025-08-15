/**
 * Auth Handler for JWT Token Management
 * 
 * Menangani auto-refresh token ketika access token expired
 */

class AuthHandler {
    constructor() {
        this.isRefreshing = false;
        this.failedQueue = [];
        this.setupAxiosInterceptors();
    }

    // Setup axios interceptors untuk menangani token expired
    setupAxiosInterceptors() {
        // Request interceptor untuk menambahkan token ke header
        if (typeof axios !== 'undefined') {
            axios.interceptors.request.use(
                (config) => {
                    const token = localStorage.getItem('access_token');
                    if (token) {
                        config.headers.Authorization = `Bearer ${token}`;
                    }
                    return config;
                },
                (error) => {
                    return Promise.reject(error);
                }
            );

            // Response interceptor untuk menangani token expired
            axios.interceptors.response.use(
                (response) => {
                    return response;
                },
                async (error) => {
                    const original = error.config;

                    if (error.response?.status === 401 && !original._retry) {
                        if (this.isRefreshing) {
                            // Jika sedang refresh, antri request
                            return new Promise((resolve, reject) => {
                                this.failedQueue.push({ resolve, reject });
                            }).then(token => {
                                original.headers.Authorization = `Bearer ${token}`;
                                return axios(original);
                            }).catch(err => {
                                return Promise.reject(err);
                            });
                        }

                        original._retry = true;
                        this.isRefreshing = true;

                        try {
                            const newToken = await this.refreshToken();
                            this.processQueue(null, newToken);
                            original.headers.Authorization = `Bearer ${newToken}`;
                            return axios(original);
                        } catch (refreshError) {
                            this.processQueue(refreshError, null);
                            this.logout();
                            return Promise.reject(refreshError);
                        } finally {
                            this.isRefreshing = false;
                        }
                    }

                    return Promise.reject(error);
                }
            );
        }
    }

    // Setup jQuery AJAX global error handler
    setupJQueryHandler() {
        $(document).ajaxError((event, xhr, settings, error) => {
            if (xhr.status === 401 && !settings.url.includes('/refresh')) {
                this.handleTokenExpired(settings);
            }
        });

        // Global jQuery AJAX setup untuk menambahkan token
        $.ajaxSetup({
            beforeSend: (xhr) => {
                const token = localStorage.getItem('access_token');
                if (token) {
                    xhr.setRequestHeader('Authorization', `Bearer ${token}`);
                }
            }
        });
    }

    // Refresh token
    async refreshToken() {
        const refreshToken = localStorage.getItem('refresh_token');
        if (!refreshToken) {
            throw new Error('No refresh token available');
        }

        try {
            const response = await fetch(base_url + 'api/v1/auth/refresh', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    refresh_token: refreshToken
                })
            });

            const data = await response.json();

            if (data.success) {
                localStorage.setItem('access_token', data.data.access_token);
                localStorage.setItem('refresh_token', data.data.refresh_token);
                return data.data.access_token;
            } else {
                throw new Error('Refresh token failed');
            }
        } catch (error) {
            localStorage.removeItem('access_token');
            localStorage.removeItem('refresh_token');
            throw error;
        }
    }

    // Handle token expired untuk jQuery AJAX
    async handleTokenExpired(originalSettings) {
        try {
            const newToken = await this.refreshToken();
            
            // Retry original request dengan token baru
            originalSettings.headers = originalSettings.headers || {};
            originalSettings.headers.Authorization = `Bearer ${newToken}`;
            
            $.ajax(originalSettings);
        } catch (error) {
            console.error('Token refresh failed:', error);
            this.logout();
        }
    }

    // Process antrian request yang gagal
    processQueue(error, token = null) {
        this.failedQueue.forEach(({ resolve, reject }) => {
            if (error) {
                reject(error);
            } else {
                resolve(token);
            }
        });
        
        this.failedQueue = [];
    }

    // Logout user
    logout() {
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        
        // Redirect ke login page
        if (typeof base_url !== 'undefined') {
            window.location.href = base_url + 'login';
        } else {
            window.location.href = '/login';
        }
    }

    // Check apakah token masih valid
    isTokenExpired(token) {
        if (!token) return true;
        
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            const currentTime = Date.now() / 1000;
            return payload.exp < currentTime;
        } catch (error) {
            return true;
        }
    }

    // Get current access token
    getAccessToken() {
        return localStorage.getItem('access_token');
    }

    // Set tokens
    setTokens(accessToken, refreshToken) {
        localStorage.setItem('access_token', accessToken);
        localStorage.setItem('refresh_token', refreshToken);
    }
}

// Inisialisasi auth handler
$(document).ready(function() {
    window.authHandler = new AuthHandler();
    
    // Setup jQuery handler jika menggunakan jQuery
    if (typeof $ !== 'undefined') {
        window.authHandler.setupJQueryHandler();
    }
});
