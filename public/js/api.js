window.Api = {
  /**
   * In-flight refresh promise to prevent concurrent refresh attempts
   * @private
   */
  _refreshPromise: null,

  /**
   * Get token from localStorage
   * @returns {string|null} - Token or null if not found
   */
  getToken() {
    return localStorage.getItem('accessToken');
  },

  /**
   * Get refresh token from localStorage
   * @returns {string|null} - Refresh token or null if not found
   */
  getRefreshToken() {
    return localStorage.getItem('refreshToken');
  },

  /**
   * Store token in localStorage
   * Also extracts and caches userId for quick access
   * @param {string} token - JWT token
   */
  setToken(token) {
    localStorage.setItem('accessToken', token);
    // Cache userId for performance
    if (window.Auth) {
      const userId = window.Auth.getUserId(token);
      if (userId) {
        localStorage.setItem('userId', userId);
      }
    }
  },

  /**
   * Store refresh token in localStorage
   * @param {string} refreshToken - Refresh token
   */
  setRefreshToken(refreshToken) {
    localStorage.setItem('refreshToken', refreshToken);
  },

  /**
   * Store both access and refresh tokens
   * @param {string} accessToken - Access token
   * @param {string} refreshToken - Refresh token
   */
  setTokens(accessToken, refreshToken) {
    this.setToken(accessToken);
    if (refreshToken) {
      this.setRefreshToken(refreshToken);
    }
  },

  /**
   * Remove token and userId from localStorage
   */
  clearToken() {
    localStorage.removeItem('accessToken');
    localStorage.removeItem('refreshToken');
    localStorage.removeItem('userId');
  },

  /**
   * Check if token is expired or will expire soon (within 60 seconds)
   * @param {string} token - JWT token to check
   * @returns {boolean} - True if expired or expiring soon
   */
  isTokenExpiredOrExpiringSoon(token) {
    if (!token || !window.Auth) {
      return true;
    }

    const payload = window.Auth.parseJWT(token);
    if (!payload || !payload.exp) {
      return true;
    }

    const currentTime = Math.floor(Date.now() / 1000);
    const bufferTime = 60; // Refresh if expiring within 60 seconds
    return payload.exp < (currentTime + bufferTime);
  },

  /**
   * Ensure access token is valid, refresh if needed
   * Uses a queue to prevent concurrent refresh attempts
   * @returns {Promise<string|null>} - Valid access token or null
   */
  async ensureValidToken() {
    const currentToken = this.getToken();

    // If we have a token, check if it's still valid
    if (currentToken) {
      // If token is still valid, return it
      if (!this.isTokenExpiredOrExpiringSoon(currentToken)) {
        return currentToken;
      }
    }

    // Token is missing or expired - try to refresh if we have a refresh token
    if (!this.getRefreshToken()) {
      // No refresh token available, clear everything
      if (currentToken) {
        this.clearToken();
      }
      return null;
    }

    // If refresh is already in progress, wait for it
    if (this._refreshPromise) {
      return await this._refreshPromise;
    }

    // Start refresh process
    this._refreshPromise = this.refreshAccessToken()
      .finally(() => {
        // Clear the promise when done (success or failure)
        this._refreshPromise = null;
      });

    return await this._refreshPromise;
  },

  /**
   * Refresh the access token using the refresh token
   * Updated to skip auth check for the refresh endpoint itself
   * @returns {Promise<string|null>} - New access token or null if refresh failed
   */
  async refreshAccessToken() {
    const refreshToken = this.getRefreshToken();
    if (!refreshToken) {
      return null;
    }

    try {
      // Use _request directly to inspect response before it's converted to error
      // Note: gesdinet_jwt_refresh_token expects 'refresh_token' parameter name
      const refreshUrl = window.routes?.tokenRefresh || '/api/token/refresh';
      const response = await this._request(refreshUrl, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          refresh_token: refreshToken
        }),
      }, true); // skipAuth = true

      const data = await response.json().catch(() => null);

      if (response.ok && data) {
        const newAccessToken = data.token || data.accessToken;
        const newRefreshToken = data.refresh_token || data.refreshToken;

        if (newAccessToken) {
          this.setToken(newAccessToken);
          if (newRefreshToken) {
            this.setRefreshToken(newRefreshToken);
          }
          return newAccessToken;
        }
      } else {
        // Check if error indicates invalid refresh token
        const errorCode = data?.error?.code;
        const isTokenInvalid = errorCode === 'AUTH_TOKEN_INVALID' ||
                               response.status === 422 ||
                               (response.status === 401 && errorCode === 'AUTH_TOKEN_INVALID');

        if (isTokenInvalid) {
          // Refresh token is invalid/expired, clear all tokens
          this.clearToken();
        }
        // For other errors (network, server errors), keep tokens and let user retry
      }
    } catch (e) {
      // Network error or other exception - don't clear tokens, might be temporary
      console.error('Token refresh error:', e);
    }

    return null;
  },

  /**
   * Core request method that handles all HTTP requests
   * This is the middleware that intercepts all requests
   * @private
   * @param {string} url - Request URL
   * @param {object} options - Fetch options (method, headers, body, etc.)
   * @param {boolean} skipAuth - Skip authentication for this request
   * @returns {Promise<Response>}
   */
  async _request(url, options = {}, skipAuth = false) {
    // Ensure valid token before request (unless skipping auth)
    if (!skipAuth) {
      const authToken = await this.ensureValidToken();
      if (authToken) {
        options.headers = options.headers || {};
        options.headers['Authorization'] = `Bearer ${authToken}`;
      }
    }

    // Make the request
    let response = await fetch(url, options);

    // If we get 401, try refreshing (handles both expired tokens and missing tokens with refresh token available)
    // This is a fallback in case the token expired between the check and the request
    if (response.status === 401 && !skipAuth &&
        !url.includes('/token/refresh') && this.getRefreshToken()) {

      // Clear the cached refresh promise to force a new refresh attempt
      this._refreshPromise = null;

      // Try to refresh and get a new token
      const newToken = await this.refreshAccessToken();
      if (newToken) {
        // Retry the request with the new token
        // Clone options to avoid mutating the original
        const retryOptions = {
          ...options,
          headers: {
            ...options.headers,
            'Authorization': `Bearer ${newToken}`
          }
        };
        response = await fetch(url, retryOptions);
      }
      // If refresh failed, don't clear tokens here - refreshAccessToken() handles that
      // Only clear if the refresh token itself is invalid
    }

    return response;
  },

  /**
   * Make a POST request
   * @param {string} url - Request URL
   * @param {object} payload - Request body
   * @param {object} options - Additional options (skipAuth, etc.)
   * @returns {Promise<any>}
   */
  async post(url, payload, options = {}) {
    const response = await this._request(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      body: JSON.stringify(payload),
    }, options.skipAuth);

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      throw new Error(data?.error?.message ?? data?.message ?? response.statusText);
    }

    return data;
  },

  /**
   * Make a GET request
   * @param {string} url - Request URL
   * @param {object} options - Additional options (skipAuth, etc.)
   * @returns {Promise<any>}
   */
  async get(url, options = {}) {
    const response = await this._request(url, {
      method: 'GET',
      headers: options.headers || {},
    }, options.skipAuth);

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      throw new Error(data?.error?.message ?? data?.message ?? response.statusText);
    }

    return data;
  },

  /**
   * Make a PATCH request
   * @param {string} url - Request URL
   * @param {object} payload - Request body
   * @param {object} options - Additional options (skipAuth, etc.)
   * @returns {Promise<any>}
   */
  async patch(url, payload, options = {}) {
    const response = await this._request(url, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      body: JSON.stringify(payload),
    }, options.skipAuth);

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      throw new Error(data?.error?.message ?? data?.message ?? response.statusText);
    }

    return data;
  },
};
