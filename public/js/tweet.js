async function getTweet(tweetId) {
  try {
    const response = await fetch(`/api/tweets/${tweetId}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const data = await response.json();
    console.log('Tweet JSON data:', data);

    return data;
  } catch (error) {
    console.error('Error:', error);
  }
}

function formatPublishDate(isoString) {
  const date = new Date(isoString);
  if (isNaN(date)) return '';

  const now = Date.now();
  let diff = now - date.getTime();
  if (diff < 0) diff = 0; // future dates => treat as “just now”

  const minute = 60 * 1000;
  const hour = 60 * minute;
  const day = 24 * hour;

  if (diff < hour) {
    const minutes = Math.max(1, Math.floor(diff / minute));
    return `${minutes}m ago`;
  }

  if (diff < day) {
    const hours = Math.max(1, Math.floor(diff / hour));
    return `${hours}h ago`;
  }

  if (diff < 3 * day) {
    const days = Math.floor(diff / day);
    return `${days}d ago`;
  }

  return date.toLocaleString('en-US', {
    day: 'numeric',    // 1–31
    month: 'short',    // Jan, Feb, …
    year: 'numeric'    // 4 digits
  });
}



function showTweet(tweet) {

  console.log('Tweet JSON data in show:', tweet);
  document.querySelector('[tweet_author]').innerHTML = '<a href=\"\/p\/' + tweet.author.id + '\">' + tweet.author.name + '</a>';
  document.querySelector('[tweet_createdAt]').textContent = ' · ' + formatPublishDate(tweet.createdAt);
  document.querySelector('[tweet_content]').textContent = tweet.content;
  document.querySelector('[tweet_likes]').textContent = tweet.likes;

  document.querySelector('.tweet').style.visibility = 'visible';
}
