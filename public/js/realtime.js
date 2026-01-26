(function () {
  let eventSource = null;
  let reconnectAttempts = 0;
  const MAX_RECONNECT_DELAY = 30000;

  function connect() {
    const mercureUrl = new URL('/.well-known/mercure', window.location.origin);
    mercureUrl.searchParams.append('topic', '/tweets/{id}/likes');

    eventSource = new EventSource(mercureUrl);

    eventSource.onopen = () => {
      reconnectAttempts = 0;
    };

    eventSource.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);
        updateLikeCount(data.tweetId, data.likesCount);
      } catch (e) {
        console.warn('Failed to parse SSE message:', e);
      }
    };

    eventSource.onerror = () => {
      eventSource.close();
      scheduleReconnect();
    };
  }

  function updateLikeCount(tweetId, count) {
    const selector = `.tweet-like-btn[data-tweet-id="${tweetId}"] .tweet-like-count`;

    document.querySelectorAll(selector).forEach((el) => {
      const btn = el.closest('.tweet-like-btn');
      if (btn?.disabled) return;

      if (el.textContent !== String(count)) {
        el.textContent = count;
      }
    });
  }

  function scheduleReconnect() {
    const delay = Math.min(1000 * Math.pow(2, reconnectAttempts), MAX_RECONNECT_DELAY);
    reconnectAttempts++;
    setTimeout(connect, delay);
  }

  function disconnect() {
    if (eventSource) {
      eventSource.close();
      eventSource = null;
    }
  }

  document.addEventListener('DOMContentLoaded', connect);
  window.addEventListener('beforeunload', disconnect);

  window.Realtime = { connect, disconnect };
})();
