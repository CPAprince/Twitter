(function () {
  function clear(el) {
    while (el.firstChild) el.removeChild(el.firstChild);
  }

  function formatTimeHHMM(iso) {
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return "";
    return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit", hour12: false });
  }

  async function getJson(url, params = {}) {
    const urlObj = new URL(url, window.location.origin);
    for (const [key, value] of Object.entries(params)) {
      urlObj.searchParams.set(key, value);
    }
    const finalUrl = urlObj.toString();

    if (window.Api?.get) return await window.Api.get(finalUrl);

    const res = await fetch(finalUrl, { headers: { Accept: "application/json" } });
    if (res.status === 204) return null;
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return await res.json();
  }

  function normalizeTweetsPayload(data) {
    if (Array.isArray(data)) return data;
    if (Array.isArray(data?.data)) return data.data;
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

  function renderLimitSelector(meta, loadCallback) {
    const container = document.getElementById("tweets-limit-control");
    if (!container) return;

    if (container.querySelector("select")) {
      const select = container.querySelector("select");
      select.value = meta.limit;
    }

    container.innerHTML = `
      <div class="d-flex align-items-center gap-2">
        <label class="small text-muted mb-0">Show:</label>
        <select class="form-select form-select-sm" style="width: auto;">
            <option value="10" ${meta.limit === 10 ? "selected" : ""}>10</option>
            <option value="20" ${meta.limit === 20 ? "selected" : ""}>20</option>
            <option value="50" ${meta.limit === 50 ? "selected" : ""}>50</option>
        </select>
      </div>
    `;

    const select = container.querySelector("select");
    select.addEventListener("change", (e) => {
      loadCallback({ limit: parseInt(e.target.value, 10), page: 1 }, false);
    });
  }

  function renderPagination(container, meta, loadCallback) {
    renderLimitSelector(meta, loadCallback);

    let nav = container.nextElementSibling;
    if (nav && nav.classList.contains("tweets-pagination")) {
      nav.remove();
    }

    if (!meta) return;

    const { page, totalPages } = meta;

    const navEl = document.createElement("div");
    navEl.className = "tweets-pagination d-flex justify-content-center align-items-center mt-3 pt-3";

    const btnWrapper = document.createElement("div");
    const prevDis = page <= 1 ? "disabled" : "";
    const nextDis = page >= totalPages ? "disabled" : "";

    btnWrapper.innerHTML = `
        <div class="btn-group">
            <button class="btn btn-outline-secondary btn-sm pagination-prev" ${prevDis}>
                <i class="fas fa-chevron-left"></i> Prev
            </button>
            <span class="btn btn-sm btn-light disabled">
                Page ${page} of ${Math.max(1, totalPages)}
            </span>
            <button class="btn btn-outline-secondary btn-sm pagination-next" ${nextDis}>
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    `;

    btnWrapper.querySelector(".pagination-prev").addEventListener("click", () => {
      if (page > 1) loadCallback({ page: page - 1, limit: meta.limit }, true);
    });
    btnWrapper.querySelector(".pagination-next").addEventListener("click", () => {
      if (page < totalPages) loadCallback({ page: page + 1, limit: meta.limit }, true);
    });

    navEl.appendChild(btnWrapper);
    container.parentNode.insertBefore(navEl, container.nextSibling);
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
      const likes = t?.likesCount ?? t?.likes ?? 0;

      if (root) root.dataset.tweetId = tweetId;

      const authorLink = node.querySelector("[data-tweet-author-link]");
      const authorNameEl = node.querySelector("[data-tweet-author-name]");
      if (authorLink) authorLink.href = `/p/${authorId}`;
      if (authorNameEl) authorNameEl.textContent = authorName;

      const createdAtEl = node.querySelector("[data-tweet-created-at]");
      if (createdAtEl) createdAtEl.textContent = ` · ${formatTimeHHMM(createdAt)}`;

      const contentEl = node.querySelector("[data-tweet-content]");
      if (contentEl) contentEl.textContent = content;

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

  function getParamsFromUrl() {
    const params = new URLSearchParams(window.location.search);
    return {
      page: parseInt(params.get("page") || "1", 10),
      limit: parseInt(params.get("limit") || "20", 10)
    };
  }

  function setParamsToUrl(page, limit) {
    const url = new URL(window.location);
    url.searchParams.set("page", page);
    url.searchParams.set("limit", limit);
    window.history.pushState({}, "", url);
  }

  async function load(container, params = {}) {
    const baseUrl = container.getAttribute("data-tweets-url");
    if (!baseUrl) return;

    const currentLimit = parseInt(container.dataset.limit || "20", 10);
    const currentPage = parseInt(container.dataset.page || "1", 10);

    const newLimit = params.limit ?? currentLimit;
    const newPage = params.page ?? currentPage;

    container.dataset.limit = newLimit;
    container.dataset.page = newPage;

    setParamsToUrl(newPage, newLimit);

    try {
      const data = await getJson(baseUrl, { page: newPage, limit: newLimit });
      const tweets = normalizeTweetsPayload(data);
      const meta = data?.meta;
      const totalPages = meta?.pages ?? 1;
      const paginationMeta = meta ? { ...meta, totalPages } : { page: newPage, limit: newLimit, totalPages: 1 };

      renderTweets(container, tweets);
      renderPagination(container, paginationMeta, (newParams, shouldScroll = false) => {
        if (shouldScroll) {
          window.scrollTo({ top: 0, behavior: "smooth" });
        }
        load(container, newParams);
      });

      if (window.Tweets?.applyLikedState) await window.Tweets.applyLikedState(container);
      if (window.Tweets?.applyEditVisibility) await window.Tweets.applyEditVisibility(container);
    } catch (_) {
      renderTweets(container, []);
      const nav = container.nextElementSibling;
      if (nav && nav.classList.contains("tweets-pagination")) nav.remove();
    }
  }

  window.TweetsList = {
    reload: (container) => load(container, { page: 1 }),
  };

  document.addEventListener("DOMContentLoaded", () => {
    const urlParams = getParamsFromUrl();
    document.querySelectorAll(".tweets-section[data-tweets-url]").forEach((el) => {
      load(el, urlParams);
    });

    window.addEventListener("popstate", () => {
      const poppedParams = getParamsFromUrl();
      document.querySelectorAll(".tweets-section[data-tweets-url]").forEach((el) => {
        load(el, poppedParams);
      });
    });
  });
})();
