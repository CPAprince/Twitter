document.addEventListener("DOMContentLoaded", async () => {
  const profileData = window.profileData;
  if (!profileData) return;

  const urlUserId = profileData.userId;
  const currentUserId = await Auth.getCurrentUserId();
  const isOwnProfile = Boolean(currentUserId && urlUserId === currentUserId);

  if (isOwnProfile) {
    document.body.classList.add("is-own-profile");
  }

  if (isOwnProfile) {
    const editBtn = document.getElementById("edit-profile-btn");
    const editSection = document.getElementById("profile-edit-section");
    const tweetCreateSection = document.getElementById("tweet-create-section");

    if (tweetCreateSection) tweetCreateSection.style.display = "block";

    if (editBtn && editSection) {
      editBtn.style.display = "block";
      editBtn.addEventListener("click", () => {
        editSection.style.display = editSection.style.display === "none" ? "block" : "none";
      });
    }
  }

  if (isOwnProfile) {
    const nameInput = document.getElementById("profile-name");
    const bioInput = document.getElementById("profile-bio");
    if (nameInput) nameInput.value = profileData.name || "";
    if (bioInput) bioInput.value = profileData.bio || "";
  }

  const profileEditForm = document.getElementById("profile-edit-form");
  if (profileEditForm && isOwnProfile) {
    profileEditForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const alerts = document.getElementById("profile-edit-alerts");
      alerts.replaceChildren();

      const formData = new FormData(profileEditForm);
      const name = formData.get("name");
      const bio = formData.get("bio");

      try {
        Loading.clearAndShow(alerts, "Saving profile...");
        Loading.disableForm(profileEditForm);

        const payload = {};
        if (name && name.trim() !== profileData.name) payload.name = name.trim();
        if (bio !== profileData.bio) payload.bio = bio ? bio.trim() : null;

        if (Object.keys(payload).length === 0) {
          Loading.hide(alerts);
          Alert.append(alerts, "No changes to save", "info");
          return;
        }

        const updateProfileUrl = window.routes?.updateProfile && window.buildRoute
          ? window.buildRoute(window.routes.updateProfile, { userId: urlUserId })
          : `/api/profiles/${urlUserId}`;

        const result = await Api.patch(updateProfileUrl, payload);

        document.querySelector(".profile-header h1").textContent = result.name;
        const bioElement = document.querySelector(".profile-header p.text-muted");
        if (result.bio) {
          bioElement.textContent = result.bio;
        } else {
          const em = document.createElement("em");
          em.textContent = "No bio yet.";
          bioElement.replaceChildren(em);
        }

        profileData.name = result.name;
        profileData.bio = result.bio;

        document.getElementById("profile-edit-section").style.display = "none";
        Loading.hide(alerts);
        Alert.append(alerts, "Profile updated successfully!", "success");
      } catch (error) {
        Loading.hide(alerts);
        Alert.append(alerts, error.message || "Failed to update profile", "danger");
      } finally {
        Loading.enableForm(profileEditForm);
      }
    });

    const cancelBtn = document.getElementById("cancel-profile-edit");
    if (cancelBtn) {
      cancelBtn.addEventListener("click", () => {
        document.getElementById("profile-edit-section").style.display = "none";
        document.getElementById("profile-name").value = profileData.name;
        document.getElementById("profile-bio").value = profileData.bio || "";
      });
    }
  }

  document.addEventListener("click", (e) => {
    const btn = e.target.closest?.(".tweet-edit-btn");
    if (!btn) return;
    if (!isOwnProfile) return;

    const tweetId = btn.dataset.tweetId;
    const tweetElement = btn.closest(".tweet");
    if (!tweetElement) return;

    const contentDisplay = tweetElement.querySelector(".tweet-content-display");
    const editForm = tweetElement.querySelector(".tweet-edit-form");
    const editFormInner = tweetElement.querySelector(".tweet-edit-form-inner");
    if (!contentDisplay || !editForm || !editFormInner) return;

    const originalContent = contentDisplay.querySelector("p")?.textContent ?? "";

    contentDisplay.style.display = contentDisplay.style.display === "none" ? "block" : "none";
    editForm.style.display = editForm.style.display === "none" ? "block" : "none";

    const textarea = editFormInner.querySelector("textarea");
    if (textarea) textarea.value = originalContent;

    editFormInner.addEventListener("submit", async (submitEvent) => {
      submitEvent.preventDefault();

      const alerts = editForm.querySelector(".tweet-edit-alerts");
      if (!alerts) return;
      alerts.replaceChildren();

      const formData = new FormData(editFormInner);
      const content = String(formData.get("content") || "").trim();

      if (!content) {
        Alert.append(alerts, "Empty.", "danger");
        return;
      }

      if (content === originalContent) {
        Alert.append(alerts, "No changes.", "info");
        return;
      }

      try {
        Loading.clearAndShow(alerts);
        Loading.disableForm(editFormInner);

        const updateTweetUrl = window.routes?.updateTweet && window.buildRoute
          ? window.buildRoute(window.routes.updateTweet, { tweetId })
          : `/api/tweets/${tweetId}`;

        const result = await Api.patch(updateTweetUrl, { content });

        const p = contentDisplay.querySelector("p");
        if (p) p.textContent = result.content;

        contentDisplay.style.display = "block";
        editForm.style.display = "none";

        Alert.append(alerts, "Saved.", "success");
      } catch (error) {
        Alert.append(alerts, "Failed.", "danger");
      } finally {
        Loading.enableForm(editFormInner);
      }
    }, { once: true });

    const cancelBtn = editForm.querySelector(".tweet-edit-cancel");
    if (cancelBtn) {
      cancelBtn.addEventListener("click", () => {
        contentDisplay.style.display = "block";
        editForm.style.display = "none";
      }, { once: true });
    }
  });
});
