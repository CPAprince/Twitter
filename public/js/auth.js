window.Auth = {
  /**
   * Parse JWT token and return payload
   * @param {string} token - JWT token
   * @returns {object|null} - Decoded payload or null if invalid
   */
  parseJWT(token) {
    if (!token) {
      return null;
    }

    try {
      const parts = token.split('.');
      if (parts.length !== 3) {
        return null;
      }

      // Decode the payload (middle part)
      const payload = parts[1];
      const decoded = atob(payload.replace(/-/g, '+').replace(/_/g, '/'));
      return JSON.parse(decoded);
    } catch (e) {
      console.error('Failed to parse JWT:', e);
      return null;
    }
  },

  /**
   * Extract userId from JWT token
   * @param {string} token - JWT token
   * @returns {string|null} - User ID or null if not found
   */
  getUserId(token) {
    const payload = this.parseJWT(token);
    return payload?.id || null;
  },

  /**
   * Check if token is expired
   * @param {string} token - JWT token
   * @returns {boolean} - True if expired or invalid
   */
  isTokenExpired(token) {
    const payload = this.parseJWT(token);
    if (!payload || !payload.exp) {
      return true;
    }

    const currentTime = Math.floor(Date.now() / 1000);
    return payload.exp < currentTime;
  },

  /**
   * Get current userId from stored token
   * Automatically refreshes token if expired
   * @returns {Promise<string|null>} - User ID or null if not found
   */
  async getCurrentUserId() {
    const token = Api.getToken();
    if (!token) {
      return null;
    }

    if (this.isTokenExpired(token)) {
      // Try to refresh the token
      const newToken = await Api.refreshAccessToken();
      if (!newToken) {
        return null;
      }
      return this.getUserId(newToken);
    }

    return this.getUserId(token);
  },
};
