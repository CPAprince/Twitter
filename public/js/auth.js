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
   * Automatically refreshes token if expired using the new middleware
   * @returns {Promise<string|null>} - User ID or null if not found
   */
  async getCurrentUserId() {
    // Use ensureValidToken to get a valid token (will auto-refresh if needed)
    const token = await Api.ensureValidToken();
    if (!token) {
      return null;
    }

    return this.getUserId(token);
  },
};
