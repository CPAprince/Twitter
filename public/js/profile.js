document.addEventListener("DOMContentLoaded", async () => {
  const profileData = window.profileData;
  if (!profileData) {
    console.error("Profile data not found");
    return;
  }

  const urlUserId = profileData.userId;
  const currentUserId = await Auth.getCurrentUserId();
  const isOwnProfile = currentUserId && urlUserId === currentUserId;

  // Show edit UI if viewing own profile
  if (isOwnProfile) {
    const editBtn = document.getElementById("edit-profile-btn");
    const editSection = document.getElementById("profile-edit-section");

    if (editBtn) {
      editBtn.style.display = "block";
      editBtn.addEventListener("click", () => {
        editSection.style.display = editSection.style.display === "none" ? "block" : "none";
      });
    }

    // Show edit buttons on tweets
    document.querySelectorAll(".tweet-edit-btn").forEach(btn => {
      btn.style.display = "inline-block";
    });
  }

  // Initialize profile edit form with current values
  if (isOwnProfile) {
    const nameInput = document.getElementById("profile-name");
    const bioInput = document.getElementById("profile-bio");
    if (nameInput) nameInput.value = profileData.name || "";
    if (bioInput) bioInput.value = profileData.bio || "";
  }

  // Profile edit form handling
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
        if (name && name.trim() !== profileData.name) {
          payload.name = name.trim();
        }
        if (bio !== profileData.bio) {
          payload.bio = bio ? bio.trim() : null;
        }

        if (Object.keys(payload).length === 0) {
          Loading.hide(alerts);
          Alert.append(alerts, "No changes to save", "info");
          return;
        }

        const updateProfileUrl = window.routes?.updateProfile && window.buildRoute
          ? window.buildRoute(window.routes.updateProfile, { userId: urlUserId })
          : `/api/profiles/${urlUserId}`;
        const result = await Api.patch(updateProfileUrl, payload);

        // Update profile display
        document.querySelector(".profile-header h1").textContent = result.name;
        const bioElement = document.querySelector(".profile-header p.text-muted");
        if (result.bio) {
          bioElement.textContent = result.bio;
        } else {
          const em = document.createElement('em');
          em.textContent = "No bio yet.";
          bioElement.replaceChildren(em);
        }

        // Update profileData
        profileData.name = result.name;
        profileData.bio = result.bio;

        // Hide edit form
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
        // Reset form values
        document.getElementById("profile-name").value = profileData.name;
        document.getElementById("profile-bio").value = profileData.bio || "";
      });
    }
  }

  // Tweet edit handling
  document.querySelectorAll(".tweet-edit-btn").forEach(btn => {
    btn.addEventListener("click", (e) => {
      const tweetId = e.currentTarget.dataset.tweetId;
      const tweetElement = e.currentTarget.closest(".tweet");
      const contentDisplay = tweetElement.querySelector(".tweet-content-display");
      const editForm = tweetElement.querySelector(".tweet-edit-form");
      const editFormInner = tweetElement.querySelector(".tweet-edit-form-inner");
      const originalContent = contentDisplay.querySelector("p").textContent;

      // Toggle visibility
      contentDisplay.style.display = contentDisplay.style.display === "none" ? "block" : "none";
      editForm.style.display = editForm.style.display === "none" ? "block" : "none";

      // Set form value
      const textarea = editFormInner.querySelector("textarea");
      if (textarea) {
        textarea.value = originalContent;
      }

      // Handle form submission
      editFormInner.addEventListener("submit", async (submitEvent) => {
        submitEvent.preventDefault();
        const alerts = editForm.querySelector(".tweet-edit-alerts");
        alerts.replaceChildren();

        const formData = new FormData(editFormInner);
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
          Loading.disableForm(editFormInner);

          const updateTweetUrl = window.routes?.updateTweet && window.buildRoute
            ? window.buildRoute(window.routes.updateTweet, { tweetId: tweetId })
            : `/api/tweets/${tweetId}`;
          const result = await Api.patch(updateTweetUrl, { content });

          // Update tweet display
          contentDisplay.querySelector("p").textContent = result.content;
          contentDisplay.style.display = "block";
          editForm.style.display = "none";

          Loading.hide(alerts);
          Alert.append(alerts, "Tweet updated successfully!", "success");
        } catch (error) {
          Loading.hide(alerts);
          Alert.append(alerts, error.message || "Failed to update tweet", "danger");
        } finally {
          Loading.enableForm(editFormInner);
        }
      }, { once: true });

      // Handle cancel
      const cancelBtn = editForm.querySelector(".tweet-edit-cancel");
      if (cancelBtn) {
        cancelBtn.addEventListener("click", () => {
          contentDisplay.style.display = "block";
          editForm.style.display = "none";
        }, { once: true });
      }
    });
  });
});
