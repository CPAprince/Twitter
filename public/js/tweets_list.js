(function () {
  function clear(el) {
    while (el.firstChild) el.removeChild(el.firstChild);
  }

  function formatTimeHHMM(iso) {
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return "";
    return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit", hour12: false });
  }

  async function getJson(url) {
    if (window.Api?.get) return await window.Api.get(url);

    const res = await fetch(url, { headers: { Accept: "application/json" } });
    if (res.status === 204) return null;
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return await res.json();
  }

  function normalizeTweetsPayload(data) {
    if (Array.isArray(data)) return data;
    if (Array.isArray(data?.tweets)) return data.tweets;
    return [];
  }

  function resolveAuthorName(t, authorId) {
    const apiName = t?.author?.name;
    if (apiName && String(apiName).trim()) return apiName;

    const pageProfileName = window.profileData?.name;
    if (pageProfileName && String(pageProfileName).trim()) return pageProfileName;

    if (authorId) return `@${String(authorId).slice(0, 8)}`;
    return "Unknown";
  }

  function renderTweets(container, tweets) {
    const tplItem = document.getElementById("tpl-tweet-item");
    const tplEmpty = document.getElementById("tpl-tweets-empty");
    if (!tplItem || !tplEmpty) return;

    clear(container);

    if (!Array.isArray(tweets) || tweets.length === 0) {
      container.appendChild(tplEmpty.content.cloneNode(true));
      return;
    }

    for (const t of tweets) {
      const node = tplItem.content.cloneNode(true);
      const root = node.querySelector(".tweet");

      const tweetId = t?.id ?? "";
      const authorId = t?.author?.id ?? t?.userId ?? "";
      const authorName = resolveAuthorName(t, authorId);
      const createdAt = t?.createdAt ?? "";
      const content = t?.content ?? "";
      const likes = t?.likes ?? 0;

      if (root) root.dataset.tweetId = tweetId;

      const authorLink = node.querySelector("[data-tweet-author-link]");
      const authorNameEl = node.querySelector("[data-tweet-author-name]");
      if (authorLink) authorLink.href = `/p/${authorId}`;
      if (authorNameEl) authorNameEl.textContent = authorName;

      const createdAtEl = node.querySelector("[data-tweet-created-at]");
      if (createdAtEl) createdAtEl.textContent = ` · ${formatTimeHHMM(createdAt)}`;

      const contentEl = node.querySelector("[data-tweet-content]");
      if (contentEl) contentEl.textContent = content;

      // like button
      const likeBtn = node.querySelector(".tweet-like-btn");
      const likeCount = node.querySelector(".tweet-like-count");
      if (likeBtn) likeBtn.dataset.tweetId = tweetId;
      if (likeCount) likeCount.textContent = String(likes);

      const external = node.querySelector("[data-tweet-external-link]");
      if (external) external.href = "#";

      const editBtn = node.querySelector(".tweet-edit-btn");
      if (editBtn) {
        editBtn.dataset.tweetId = tweetId;
        editBtn.dataset.authorId = authorId;
      }

      const editFormContainer = node.querySelector(".tweet-edit-form .tweet-form-container");
      if (editFormContainer) editFormContainer.dataset.tweetId = tweetId;

      const editTextarea = node.querySelector(".tweet-edit-form textarea[name=\"content\"]");
      if (editTextarea) editTextarea.value = content;

      container.appendChild(node);
    }
  }

  async function load(container) {
    const url = container.getAttribute("data-tweets-url");
    if (!url) return;

    try {
      const data = await getJson(url);
      const tweets = normalizeTweetsPayload(data);
      renderTweets(container, tweets);

      if (window.Tweets?.applyLikedState) await window.Tweets.applyLikedState(container);
      if (window.Tweets?.applyEditVisibility) await window.Tweets.applyEditVisibility(container);
    } catch (_) {
      renderTweets(container, []);
    }
  }

  window.TweetsList = {
    reload: load,
  };

  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".tweets-section[data-tweets-url]").forEach((el) => load(el));
  });
})();
