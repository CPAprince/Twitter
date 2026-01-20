// Initialize character counter for tweet forms
function initializeTweetCharCounter(container) {
  const textarea = container.querySelector('textarea[name="content"]');
  const counter = container.querySelector('.tweet-char-count');
  if (!textarea || !counter) return;

  // Clone textarea to remove existing listeners and prevent duplicates
  const newTextarea = textarea.cloneNode(true);
  textarea.parentNode.replaceChild(newTextarea, textarea);

  const updateCounter = () => {
    const length = newTextarea.value.length;
    const remaining = 280 - length;
    counter.textContent = length;

    // Update styling based on remaining characters
    const counterContainer = counter.closest('.tweet-char-counter');
    if (remaining < 0) {
      counterContainer.className = 'tweet-char-counter text-danger small';
    } else if (remaining <= 20) {
      counterContainer.className = 'tweet-char-counter text-warning small';
    } else {
      counterContainer.className = 'tweet-char-counter text-muted small';
    }
  };

  // Update on input
  newTextarea.addEventListener('input', updateCounter);

  // Initial update
  updateCounter();
}

document.addEventListener("DOMContentLoaded", async () => {
  // Show edit buttons only for tweets owned by current user
  const currentUserId = await Auth.getCurrentUserId();

  if (currentUserId) {
    document.querySelectorAll(".tweet-edit-btn").forEach(btn => {
      const authorId = btn.dataset.authorId;
      if (authorId === currentUserId) {
        btn.style.display = "inline-block";
      }
    });
  }

  // Helper functions for managing like state in localStorage
  const getLikesStorageKey = () => {
    const userId = localStorage.getItem('userId');
    return userId ? `tweet_likes_${userId}` : 'tweet_likes';
  };

  const getLikedTweets = () => {
    try {
      const stored = localStorage.getItem(getLikesStorageKey());
      return stored ? JSON.parse(stored) : {};
    } catch {
      return {};
    }
  };

  const setLikedTweet = (tweetId, liked) => {
    const likes = getLikedTweets();
    if (liked) {
      likes[tweetId] = true;
    } else {
      delete likes[tweetId];
    }
    localStorage.setItem(getLikesStorageKey(), JSON.stringify(likes));
  };

  // Restore like states from localStorage on page load
  if (currentUserId) {
    const likedTweets = getLikedTweets();
    document.querySelectorAll(".tweet-like-btn").forEach(btn => {
      const tweetId = btn.dataset.tweetId;
      if (tweetId && likedTweets[tweetId]) {
        const icon = btn.querySelector(".tweet-like-icon");
        const countSpan = btn.querySelector(".tweet-like-count");
        if (icon) {
          btn.dataset.liked = "true";
          icon.classList.remove("far");
          icon.classList.add("fas", "text-primary");
          
          // If count is 0 but tweet is liked, increment to at least 1
          // This handles cases where server count is stale
          if (countSpan) {
            const currentCount = parseInt(countSpan.textContent) || 0;
            if (currentCount === 0) {
              countSpan.textContent = "1";
            }
          }
        }
      }
    });
  }

  // Tweet like/unlike functionality
  document.addEventListener("click", async (e) => {
    const likeBtn = e.target.closest(".tweet-like-btn");
    if (!likeBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const tweetId = likeBtn.dataset.tweetId;
    if (!tweetId) return;

    const icon = likeBtn.querySelector(".tweet-like-icon");
    const countSpan = likeBtn.querySelector(".tweet-like-count");
    const isCurrentlyLiked = likeBtn.dataset.liked === "true";
    const currentCount = parseInt(countSpan.textContent) || 0;

    // Optimistic UI update
    const newLikedState = !isCurrentlyLiked;
    const newCount = newLikedState ? currentCount + 1 : Math.max(0, currentCount - 1);

    // Update UI immediately
    likeBtn.dataset.liked = newLikedState.toString();
    countSpan.textContent = newCount;
    
    // Update icon (far = outline, fas = filled)
    if (newLikedState) {
      icon.classList.remove("far");
      icon.classList.add("fas", "text-primary");
    } else {
      icon.classList.remove("fas", "text-primary");
      icon.classList.add("far");
    }

    // Disable button during request
    likeBtn.disabled = true;

    try {
      const toggleUrl = window.routes?.toggleLike && window.buildRoute
        ? window.buildRoute(window.routes.toggleLike, { tweetId: tweetId })
        : `/api/tweets/${tweetId}/likes/toggle`;
      
      const result = await Api.post(toggleUrl, {});

      // Update UI based on actual API response
      const actualLiked = result.liked;
      likeBtn.dataset.liked = actualLiked.toString();
      
      // Save to localStorage
      setLikedTweet(tweetId, actualLiked);
      
      // If the optimistic update was wrong, correct it
      if (actualLiked !== newLikedState) {
        const correctedCount = actualLiked ? currentCount + 1 : Math.max(0, currentCount - 1);
        countSpan.textContent = correctedCount;
        
        if (actualLiked) {
          icon.classList.remove("far");
          icon.classList.add("fas", "text-primary");
        } else {
          icon.classList.remove("fas", "text-primary");
          icon.classList.add("far");
        }
      }
    } catch (error) {
      // Revert optimistic update on error
      likeBtn.dataset.liked = isCurrentlyLiked.toString();
      countSpan.textContent = currentCount;
      
      if (isCurrentlyLiked) {
        icon.classList.remove("far");
        icon.classList.add("fas", "text-primary");
      } else {
        icon.classList.remove("fas", "text-primary");
        icon.classList.add("far");
      }

      console.error("Failed to toggle like:", error);
    } finally {
      likeBtn.disabled = false;
    }
  });

  // Tweet edit handling - using the reusable form component
  // Edit button click handler - only handles visibility toggle and form initialization
  document.querySelectorAll(".tweet-edit-btn").forEach(btn => {
    btn.addEventListener("click", (e) => {
      const tweetElement = e.currentTarget.closest(".tweet");
      const contentDisplay = tweetElement.querySelector(".tweet-content-display");
      const editFormContainer = tweetElement.querySelector(".tweet-edit-form");
      const form = editFormContainer.querySelector(".tweet-form");

      // Get current displayed content (in case it was already edited)
      const currentContent = contentDisplay.querySelector("p").textContent.trim();

      // Toggle visibility
      const isEditing = editFormContainer.style.display !== "none";
      contentDisplay.style.display = isEditing ? "block" : "none";
      editFormContainer.style.display = isEditing ? "none" : "block";

      if (!isEditing) {
        // Entering edit mode
        const textarea = form.querySelector("textarea");
        if (textarea) {
          textarea.value = currentContent;
        }

        // Initialize character counter for edit form
        const formContainer = editFormContainer.querySelector(".tweet-form-container");
        if (formContainer) {
          initializeTweetCharCounter(formContainer);
        }

        // Clear any previous alerts
        const alerts = editFormContainer.querySelector(".tweet-form-alerts");
        if (alerts) {
          alerts.replaceChildren();
        }
      }
    });
  });

  // Delegated event listener for tweet form submission (edit mode)
  document.addEventListener("submit", async (e) => {
    const form = e.target;
    if (!form.classList.contains("tweet-form")) return;

    const formContainer = form.closest(".tweet-form-container");
    if (!formContainer) return;

    const formMode = formContainer.dataset.formMode;
    if (formMode !== "edit") return; // Only handle edit forms here

    e.preventDefault();

    const tweetId = formContainer.dataset.tweetId;
    if (!tweetId) return;

    const tweetElement = formContainer.closest(".tweet");
    const contentDisplay = tweetElement.querySelector(".tweet-content-display");
    const editFormContainer = tweetElement.querySelector(".tweet-edit-form");
    const alerts = editFormContainer.querySelector(".tweet-form-alerts");
    const originalContent = contentDisplay.querySelector("p").textContent.trim();

    alerts.replaceChildren();

    const formData = new FormData(form);
    const content = formData.get("content").trim();

    if (!content) {
      Alert.append(alerts, "Tweet content cannot be empty", "danger");
      return;
    }

    if (content === originalContent) {
      Alert.append(alerts, "No changes to save", "info");
      return;
    }

    try {
      Loading.clearAndShow(alerts, "Saving tweet...");
      Loading.disableForm(form);

      const updateTweetUrl = window.routes?.updateTweet && window.buildRoute
        ? window.buildRoute(window.routes.updateTweet, { tweetId: tweetId })
        : `/api/tweets/${tweetId}`;
      const result = await Api.patch(updateTweetUrl, { content });

      // Update tweet display
      contentDisplay.querySelector("p").textContent = result.content;
      contentDisplay.style.display = "block";
      editFormContainer.style.display = "none";

      Loading.hide(alerts);
      Alert.append(alerts, "Tweet updated successfully!", "success");

      // Hide success message after a delay
      setTimeout(() => {
        alerts.replaceChildren();
      }, 3000);
    } catch (error) {
      Loading.hide(alerts);
      Alert.append(alerts, error.message || "Failed to update tweet", "danger");
    } finally {
      Loading.enableForm(form);
    }
  });

  // Delegated event listener for cancel button clicks
  document.addEventListener("click", async (e) => {
    if (!e.target.classList.contains("tweet-form-cancel")) return;

    const formContainer = e.target.closest(".tweet-form-container");
    if (!formContainer) return;

    const tweetElement = formContainer.closest(".tweet");
    const contentDisplay = tweetElement.querySelector(".tweet-content-display");
    const editFormContainer = tweetElement.querySelector(".tweet-edit-form");
    const form = editFormContainer.querySelector(".tweet-form");

    // Reset form to current displayed content
    const currentContent = contentDisplay.querySelector("p").textContent.trim();
    form.querySelector("textarea").value = currentContent;

    // Hide edit form and show content
    contentDisplay.style.display = "block";
    editFormContainer.style.display = "none";

    // Clear alerts
    const alerts = editFormContainer.querySelector(".tweet-form-alerts");
    if (alerts) {
      alerts.replaceChildren();
    }
  });
});
