import http from 'k6/http';
import { check, group, sleep } from 'k6';
import { textSummary } from 'https://jslib.k6.io/k6-summary/0.0.1/index.js';
import sql from 'k6/x/sql';
import driver from 'k6/x/sql/driver/mysql';

/* =========================
   🔧 CONFIG
========================= */

const MySQL_USER = 'app';
const MySQL_PASSWORD = '!ChangeMe!';
const MySQL_IP = '127.0.0.1';
const MySQL_PORT = '3306';
const MySQL_DATABASE = 'app';

const db = sql.open(
  driver,
  `${MySQL_USER}:${MySQL_PASSWORD}@tcp(${MySQL_IP}:${MySQL_PORT})/${MySQL_DATABASE}`
);

const BASE_URL = 'https://localhost';

const FEED_LIMIT = 50;
const maxFeedPages = 7;
const scrollProbability = 0.9;

const THINK_TIME_MIN = 0;
const THINK_TIME_MAX = 0;

const JWT_TTL_SECONDS = 900;
const JWT_REFRESH_SAFETY_SECONDS = 60;

/* =========================
   📊 OPTIONS
========================= */

const endpoints = [
  'tweets_page1',
  'tweets_page2',
  'tweets_page3',
  'tweets_page4plus',
  'ProfileNameBio',
  'ProfileFeed',
  'Like',
  'Login',
];

export const options = {
  insecureSkipTLSVerify: true,

  scenarios: {
    auth_stress: {
      executor: 'ramping-vus',
      exec: 'scenarioAuth',
      stages: [
        { duration: '10s', target: 3 },
        { duration: '3m', target: 3 },
        { duration: '3m', target: 3 },
        { duration: '1m', target: 3 },
      ],
      gracefulRampDown: '30s',
      gracefulStop: '30s',
    },

    feed_stress: {
      executor: 'ramping-vus',
      exec: 'scenarioFeed',
      stages: [
        { duration: '10s', target: 6 },
        { duration: '3m', target: 6 },
        { duration: '3m', target: 6 },
        { duration: '1m', target: 6 },
      ],
      gracefulRampDown: '30s',
      gracefulStop: '30s',
    },

    profile_stress: {
      executor: 'ramping-vus',
      exec: 'scenarioProfile',
      stages: [
        { duration: '10s', target: 3 },
        { duration: '3m', target: 3 },
        { duration: '3m', target: 3 },
        { duration: '1m', target: 3 },
      ],
      gracefulRampDown: '30s',
      gracefulStop: '30s',
    },

    like_stress: {
      executor: 'ramping-vus',
      exec: 'scenarioLike',
      stages: [
        { duration: '10s', target: 2 },
        { duration: '3m', target: 2 },
        { duration: '3m', target: 2 },
        { duration: '1m', target: 2 },
      ],
      gracefulRampDown: '30s',
      gracefulStop: '30s',
    },
  },

  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_waiting: ['p(95)<10000'],
    ...endpoints.reduce(
      (acc, endpoint) => ({
        ...acc,
        [`http_req_duration{endpoint:${endpoint}}`]: ['p(95)<10000'],
        [`http_reqs{endpoint:${endpoint}}`]: ['count>=0'],
      }),
      {}
    ),
  },
};

/* =========================
   📦 DATA LOAD
========================= */

function asciiRowToString(v) {
  return String.fromCharCode(...v);
}

function mustNotBeEmpty(arr, name) {
  if (!arr || arr.length === 0) {
    throw new Error(`[TEST DATA] ${name} is empty`);
  }
}

let rows;

let emailsAvailable = [];
rows = db.query("SELECT email FROM app.users WHERE email LIKE 'loadtest_%' LIMIT 10000;");
for (const row of rows) emailsAvailable.push(asciiRowToString(row.email));

let profilesAvailable = [];
rows = db.query(`
  SELECT BIN_TO_UUID(user_id) AS profile_uuid
  FROM app.profiles
  WHERE user_id IN (SELECT id FROM app.users WHERE email LIKE 'loadtest_%')
    LIMIT 10000;
`);
for (const row of rows) profilesAvailable.push(asciiRowToString(row.profile_uuid));

let tweetsAvailable = [];
rows = db.query('SELECT BIN_TO_UUID(id) as tweet_id FROM app.tweets LIMIT 10000;');
for (const row of rows) tweetsAvailable.push(asciiRowToString(row.tweet_id));

mustNotBeEmpty(emailsAvailable, 'emailsAvailable (loadtest users)');
mustNotBeEmpty(profilesAvailable, 'profilesAvailable (loadtest profiles)');
mustNotBeEmpty(tweetsAvailable, 'tweetsAvailable (tweets)');

if (__VU === 1) {
  console.log(
    `#TEST DATA# Loadtest Emails: ${emailsAvailable.length} | Loadtest Profiles: ${profilesAvailable.length} | TweetsToLike: ${tweetsAvailable.length}`
  );
}

export function teardown() {
  db.close();
}

/* =========================
   🛠 HELPERS
========================= */

function randomItem(arr) {
  return arr[Math.floor(Math.random() * arr.length)];
}

function randomSleep() {
  const t = THINK_TIME_MIN + Math.random() * (THINK_TIME_MAX - THINK_TIME_MIN);
  sleep(t);
}

function authHeaders(jwt) {
  return jwt ? { headers: { Authorization: `Bearer ${jwt}` } } : {};
}

function nowSeconds() {
  return Math.floor(Date.now() / 1000);
}

/* =========================
   🔐 AUTH
========================= */

function login() {
  const payload = JSON.stringify({
    email: randomItem(emailsAvailable),
    password: 'Pa$$word1',
  });

  const params = {
    headers: { 'Content-Type': 'application/json' },
    tags: {
      endpoint: 'Login',
      name: 'POST /api/token',
    },
  };

  return group('POST /api/token (login)', () => {
    const res = http.post(`${BASE_URL}/api/token`, payload, params);
    const token = res.status === 200 ? res.json('token') : null;

    check(res, {
      'login status is 200': (r) => r.status === 200,
      'JWT received': () => !!token,
    });

    return token;
  });
}

/* =========================
   🔑 JWT CACHE (per VU)
========================= */

let jwtCache = null;
let jwtCacheCreatedAt = 0;

function jwtExpiredOrSoon() {
  if (!jwtCache) return true;
  const age = nowSeconds() - jwtCacheCreatedAt;
  return age >= JWT_TTL_SECONDS - JWT_REFRESH_SAFETY_SECONDS;
}

function getJwtOncePerVu() {
  if (!jwtCache || jwtExpiredOrSoon()) {
    jwtCache = login();
    jwtCacheCreatedAt = nowSeconds();
  }
  return jwtCache;
}

/* =========================
   📰 FEED
========================= */

function mainFeed(jwt) {
  let page = 1;

  while (page <= maxFeedPages) {
    const endpointTag =
      page === 1
        ? 'tweets_page1'
        : page === 2
          ? 'tweets_page2'
          : page === 3
            ? 'tweets_page3'
            : 'tweets_page4plus';

    group(`GET /api/tweets (page=${page})`, () => {
      http.get(`${BASE_URL}/api/tweets?limit=${FEED_LIMIT}&page=${page}`, {
        ...authHeaders(jwt),
        tags: {
          endpoint: endpointTag,
          name: 'GET /api/tweets',
        },
      });
    });

    if (page === maxFeedPages || Math.random() > scrollProbability) break;
    page++;
    randomSleep();
  }
}

/* =========================
   👤 PROFILE
========================= */

function getProfile(jwt) {
  const profileId = randomItem(profilesAvailable);

  group('GET /api/profiles/{id}', () => {
    const res = http.get(`${BASE_URL}/api/profiles/${profileId}`, {
      ...authHeaders(jwt),
      tags: {
        endpoint: 'ProfileNameBio',
        name: 'GET /api/profiles/:id',
      },
    });

    if (res.status === 401) jwtCache = null;
  });

  group('GET /api/profiles/{id}/tweets', () => {
    const res = http.get(`${BASE_URL}/api/profiles/${profileId}/tweets?limit=${FEED_LIMIT}&page=1`, {
      ...authHeaders(jwt),
      tags: {
        endpoint: 'ProfileFeed',
        name: 'GET /api/profiles/:id/tweets',
      },
    });

    if (res.status === 401) jwtCache = null;
  });
}

/* =========================
   ❤️ LIKE
========================= */

function toggleLike(jwt) {
  const tweetId = randomItem(tweetsAvailable);

  group('POST /api/tweets/{id}/likes/toggle', () => {
    const res = http.post(`${BASE_URL}/api/tweets/${tweetId}/likes/toggle`, null, {
      ...authHeaders(jwt),
      tags: {
        endpoint: 'Like',
        name: 'POST /api/tweets/:id/likes/toggle',
      },
    });

    if (res.status === 401) jwtCache = null;
  });
}

/* =========================
   🚶 SCENARIOS
========================= */

export function scenarioAuth() {
  login();
  randomSleep();
}

export function scenarioFeed() {
  mainFeed(null);
  const jwt = getJwtOncePerVu();
  mainFeed(jwt);
  randomSleep();
}

export function scenarioProfile() {
  const jwt = getJwtOncePerVu();
  const views = 3 + Math.floor(Math.random() * 6);
  for (let i = 0; i < views; i++) {
    getProfile(jwt);
    randomSleep();
  }
}

export function scenarioLike() {
  const jwt = getJwtOncePerVu();
  const likes = 5 + Math.floor(Math.random() * 8);
  for (let i = 0; i < likes; i++) {
    toggleLike(jwt);
    randomSleep();
  }
}

/* =========================
   SUMMARY
========================= */

export function handleSummary(data) {
  return {
    stdout: textSummary(data, { indent: ' ', enableColors: true }),
  };
}
