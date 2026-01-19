window.Api = {
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
   * Refresh the access token using the refresh token
   * @returns {Promise<string|null>} - New access token or null if refresh failed
   */
  async refreshAccessToken() {
    const refreshToken = this.getRefreshToken();
    if (!refreshToken) {
      return null;
    }

    try {
      const response = await this.patch('/api/token/refresh', {
        refreshToken: refreshToken
      });

      const newAccessToken = response.token || response.accessToken;
      const newRefreshToken = response.refresh_token || response.refreshToken;

      if (newAccessToken) {
        this.setToken(newAccessToken);
        if (newRefreshToken) {
          this.setRefreshToken(newRefreshToken);
        }
        return newAccessToken;
      }
    } catch (e) {
      // Refresh failed, clear tokens
      this.clearToken();
      return null;
    }

    return null;
  },

  /**
   * Get authorization header value
   * @param {string|null} token - Optional token, uses stored token if not provided
   * @returns {string|null} - Authorization header value or null
   */
  getAuthHeader(token = null) {
    const authToken = token || this.getToken();
    return authToken ? `Bearer ${authToken}` : null;
  },

  async post(url, payload, token = null, retried = false) {
    const headers = { "Content-Type": "application/json" };

    let authToken = token || this.getToken();
    const authHeader = this.getAuthHeader(authToken);
    if (authHeader) {
      headers["Authorization"] = authHeader;
    }

    let response = await fetch(url, {
      method: "POST",
      headers,
      body: JSON.stringify(payload),
    });

    // If unauthorized and we have a refresh token, try to refresh
    if (response.status === 401 && !token && !retried && this.getRefreshToken() && !url.includes('/token/refresh')) {
      const newToken = await this.refreshAccessToken();
      if (newToken) {
        // Retry the request with the new token
        return this.post(url, payload, newToken, true);
      }
    }

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      throw new Error(data?.error?.message ?? data?.message ?? response.statusText);
    }

    return data;
  },

  async get(url, token = null, retried = false) {
    const headers = {};

    let authToken = token || this.getToken();
    const authHeader = this.getAuthHeader(authToken);
    if (authHeader) {
      headers["Authorization"] = authHeader;
    }

    let response = await fetch(url, {
      method: "GET",
      headers,
    });

    // If unauthorized and we have a refresh token, try to refresh
    if (response.status === 401 && !token && !retried && this.getRefreshToken() && !url.includes('/token/refresh')) {
      const newToken = await this.refreshAccessToken();
      if (newToken) {
        // Retry the request with the new token
        return this.get(url, newToken, true);
      }
    }

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      throw new Error(data?.error?.message ?? data?.message ?? response.statusText);
    }

    return data;
  },

  async patch(url, payload, token = null, retried = false) {
    const headers = { "Content-Type": "application/json" };

    let authToken = token || this.getToken();
    const authHeader = this.getAuthHeader(authToken);
    if (authHeader) {
      headers["Authorization"] = authHeader;
    }

    let response = await fetch(url, {
      method: "PATCH",
      headers,
      body: JSON.stringify(payload),
    });

    // If unauthorized and we have a refresh token, try to refresh
    // Skip retry for refresh endpoint itself to prevent infinite loops
    if (response.status === 401 && !token && !retried && this.getRefreshToken() && !url.includes('/token/refresh')) {
      const newToken = await this.refreshAccessToken();
      if (newToken) {
        // Retry the request with the new token
        return this.patch(url, payload, newToken, true);
      }
    }

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      throw new Error(data?.error?.message ?? data?.message ?? response.statusText);
    }

    return data;
  },
};
