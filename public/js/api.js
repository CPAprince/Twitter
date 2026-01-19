window.Api = {
  /**
   * Get token from localStorage
   * @returns {string|null} - Token or null if not found
   */
  getToken() {
    return localStorage.getItem('accessToken');
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
   * Remove token and userId from localStorage
   */
  clearToken() {
    localStorage.removeItem('accessToken');
    localStorage.removeItem('userId');
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

  async post(url, payload, token = null) {
    const headers = { "Content-Type": "application/json" };

    const authHeader = this.getAuthHeader(token);
    if (authHeader) {
      headers["Authorization"] = authHeader;
    }

    const response = await fetch(url, {
      method: "POST",
      headers,
      body: JSON.stringify(payload),
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      throw new Error(data?.error?.message ?? data?.message ?? response.statusText);
    }

    return data;
  },

  async get(url, token = null) {
    const headers = {};

    const authHeader = this.getAuthHeader(token);
    if (authHeader) {
      headers["Authorization"] = authHeader;
    }

    const response = await fetch(url, {
      method: "GET",
      headers,
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      throw new Error(data?.error?.message ?? data?.message ?? response.statusText);
    }

    return data;
  },

  async patch(url, payload, token = null) {
    const headers = { "Content-Type": "application/json" };

    const authHeader = this.getAuthHeader(token);
    if (authHeader) {
      headers["Authorization"] = authHeader;
    }

    const response = await fetch(url, {
      method: "PATCH",
      headers,
      body: JSON.stringify(payload),
    });

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      throw new Error(data?.error?.message ?? data?.message ?? response.statusText);
    }

    return data;
  },
};
