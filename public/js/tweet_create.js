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
      Alert.append(alerts, "Empty.", "danger");
      return;
    }

    const createUrl = window.routes?.createTweet;
    const fragmentUrl = window.routes?.profileTweetsFragment;

    if (!createUrl || !fragmentUrl) {
      Alert.append(alerts, "Config error.", "danger");
      return;
    }

    try {
      Loading.clearAndShow(alerts);
      Loading.disableForm(form);

      await Api.post(createUrl, { content });

      textarea.value = "";
      updateCounter();

      if (tweetsSection) {
        await refreshTweetsFragment(tweetsSection, fragmentUrl);
      }

      alerts.replaceChildren();
      Alert.append(alerts, "Added.", "success");
    } catch (err) {
      alerts.replaceChildren();
      Alert.append(alerts, normalizeError(err), "danger");
    } finally {
      Loading.enableForm(form);
    }
  });
});

function normalizeError(err) {
  const msg = String(err?.message || "").toLowerCase();
  if (msg.includes("unauthorized")) return "Unauthorized.";
  if (msg.includes("validation") || msg.includes("invalid")) return "Invalid.";
  return "Failed.";
}

async function refreshTweetsFragment(tweetsSectionEl, url) {
  try {
    const res = await fetch(url, {
      method: "GET",
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });

    if (!res.ok) return;

    tweetsSectionEl.innerHTML = await res.text();
  } catch (_) {}
}
