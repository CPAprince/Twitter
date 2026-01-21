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
    const tweetCreateSection = document.getElementById("tweet-create-section");

    if (editBtn && editSection) {
      editBtn.style.display = "block";
      editBtn.addEventListener("click", () => {
        editSection.style.display = editSection.style.display === "none" ? "block" : "none";
      });
    }

    // Show tweet creation form
    if (tweetCreateSection) {
      tweetCreateSection.style.display = "block";
    }
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
      if (!alerts) return;
      alerts.replaceChildren();

      const formData = new FormData(profileEditForm);
      const name = formData.get("name");
      const bio = formData.get("bio");

      try {
        Loading.clearAndShow(alerts, "Saving profile...");
        Loading.disableForm(profileEditForm);

        const payload = {};
        if (name && String(name).trim() !== (profileData.name || "")) {
          payload.name = String(name).trim();
        }

        // bio може бути null/"" — нормалізуємо
        const nextBio = bio !== null ? String(bio).trim() : "";
        const prevBio = profileData.bio !== null && profileData.bio !== undefined ? String(profileData.bio) : "";

        if (nextBio !== prevBio) {
          payload.bio = nextBio.length > 0 ? nextBio : null;
        }

        if (Object.keys(payload).length === 0) {
          Loading.hide(alerts);
          Alert.append(alerts, "No changes to save", "info");
          return;
        }

        const updateProfileUrl =
          window.routes?.updateProfile && window.buildRoute
            ? window.buildRoute(window.routes.updateProfile, { userId: urlUserId })
            : `/api/profiles/${urlUserId}`;

        const result = await Api.patch(updateProfileUrl, payload);

        // Update profile display
        const headerName = document.querySelector(".profile-header h1");
        if (headerName) headerName.textContent = result.name;

        const bioElement = document.querySelector(".profile-header p.text-muted");
        if (bioElement) {
          if (result.bio) {
            bioElement.textContent = result.bio;
          } else {
            const em = document.createElement("em");
            em.textContent = "No bio yet.";
            bioElement.replaceChildren(em);
          }
        }

        // Update profileData (important for tweets_list.js fallback)
        profileData.name = result.name;
        profileData.bio = result.bio;

        // ✅ FIX: Refresh tweets section to reflect updated author name immediately
        const tweetsSection = document.querySelector(".tweets-section[data-tweets-url]");
        if (tweetsSection && window.TweetsList?.reload) {
          await window.TweetsList.reload(tweetsSection);
        }

        // Hide edit form
        const editSection = document.getElementById("profile-edit-section");
        if (editSection) editSection.style.display = "none";

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
        const editSection = document.getElementById("profile-edit-section");
        if (editSection) editSection.style.display = "none";

        // Reset form values
        const nameInput = document.getElementById("profile-name");
        const bioInput = document.getElementById("profile-bio");
        if (nameInput) nameInput.value = profileData.name || "";
        if (bioInput) bioInput.value = profileData.bio || "";
      });
    }
  }
});
