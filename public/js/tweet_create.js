document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("tweet-create-form");
  if (!form) return;

  const alerts = form.querySelector(".tweet-create-alerts");
  const textarea = document.getElementById("tweet-content");
  const counter = document.getElementById("tweet-content-counter");
  const tweetsSection = document.querySelector(".tweets-section");

  if (!alerts || !textarea || !counter) return;

  const updateCounter = () => {
    counter.textContent = String(textarea.value.length);
  };

  textarea.addEventListener("input", updateCounter);
  updateCounter();

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const content = textarea.value.trim();
    alerts.replaceChildren();

    if (!content) {
      Alert.append(alerts, "Tweet content cannot be empty", "danger");
      return;
    }

    const createUrl = window.routes?.createTweet;
    const fragmentUrl = window.routes?.profileTweetsFragment;

    if (!createUrl || !fragmentUrl) {
      Alert.append(alerts, "Missing routes.", "danger");
      return;
    }

    try {
      Loading.clearAndShow(alerts, "Posting tweet...");
      Loading.disableForm(form);

      await Api.post(createUrl, { content });

      textarea.value = "";
      updateCounter();

      if (tweetsSection) {
        await refreshTweetsFragment(tweetsSection, fragmentUrl);
      }

      alerts.replaceChildren();
      Alert.append(alerts, "Tweet posted successfully!", "success");
    } catch (error) {
      alerts.replaceChildren();
      Alert.append(alerts, normalizeError(error), "danger");
    } finally {
      Loading.enableForm(form);
    }
  });
});

function normalizeError(error) {
  const message = String(error?.message || "");
  return message || "Failed to post tweet";
}

async function refreshTweetsFragment(tweetsSectionEl, url) {
  try {
    const response = await fetch(url, {
      method: "GET",
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });

    if (!response.ok) return;

    tweetsSectionEl.innerHTML = await response.text();

    if (window.Tweets) {
      window.Tweets.applyLikedState(tweetsSectionEl);
      window.Tweets.applyEditVisibility(tweetsSectionEl);
    }
  } catch (_) {}
}
