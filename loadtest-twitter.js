import http from 'k6/http';
import { check, group, sleep } from 'k6';
import { textSummary } from 'https://jslib.k6.io/k6-summary/0.0.1/index.js';
import sql from 'k6/x/sql';
import driver from 'k6/x/sql/driver/mysql';

/* =========================
   🔧 CONFIG
========================= */

const MySQL_USER = __ENV.MYSQL_USER || 'app';
const MySQL_PASSWORD = __ENV.MYSQL_PASSWORD || '!ChangeMe!';
const MySQL_IP = __ENV.MYSQL_IP || '127.0.0.1';
const MySQL_PORT = __ENV.MYSQL_PORT || '3306';
const MySQL_DATABASE = __ENV.MYSQL_DATABASE || 'app';

const db = sql.open(
  driver,
  `${MySQL_USER}:${MySQL_PASSWORD}@tcp(${MySQL_IP}:${MySQL_PORT})/${MySQL_DATABASE}`
);

const BASE_URL = __ENV.BASE_URL || 'https://localhost';

const FEED_LIMIT = parseInt(__ENV.FEED_LIMIT || '50', 10);
const maxFeedPages = parseInt(__ENV.FEED_PAGES || '7', 10);
const scrollProbability = parseFloat(__ENV.SCROLL_PROB || '0.9');

const THINK_TIME_MIN = parseFloat(__ENV.THINK_TIME_MIN || '0');
const THINK_TIME_MAX = parseFloat(__ENV.THINK_TIME_MAX || '0');

const JWT_TTL_SECONDS = parseInt(__ENV.JWT_TTL_SECONDS || '900', 10);
const JWT_REFRESH_SAFETY_SECONDS = parseInt(__ENV.JWT_REFRESH_SAFETY_SECONDS || '60', 10);

/* =========================
   🌱 MODE / SEED
========================= */

const MODE = __ENV.MODE || 'loadtest'; // loadtest | cleanup

const SEED_USERS = (__ENV.SEED_USERS || '0') === '1';
const USERS_COUNT = parseInt(__ENV.USERS_COUNT || '2000', 10);

const RUN_ID = __ENV.RUN_ID || `${Date.now()}`;
const EMAIL_PREFIX = `loadtest_${RUN_ID}_`;
const EMAIL_LIKE = `${EMAIL_PREFIX}%`;

const PLAIN_PASSWORD = __ENV.LOADTEST_PASSWORD || 'Pa$$word1';

const PASSWORD_HASH =
  __ENV.PASSWORD_HASH ||
  '$2y$12$d765Nx30xRCm/cttxY4kJ.OmA3k4Ptd.4wz48.9WNaAbLBUAGK5aa';

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

export const options =
  MODE === 'cleanup'
    ? {
      insecureSkipTLSVerify: true,
      scenarios: {
        cleanup: {
          executor: 'per-vu-iterations',
          vus: 1,
          iterations: 1,
          exec: 'scenarioCleanup',
        },
      },
    }
    : {
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
   📦 HELPERS (common)
========================= */

function asciiRowToString(v) {
  if (typeof v === 'string') return v;
  return String.fromCharCode(...v);
}

function mustNotBeEmpty(arr, name) {
  if (!arr || arr.length === 0) {
    throw new Error(`[TEST DATA] ${name} is empty`);
  }
}

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
   🌱 SEED IMPLEMENTATION
========================= */

function seedLoadtestUsers(count) {
  const rows = db.query('SELECT COUNT(*) AS cnt FROM app.users WHERE email LIKE ?;', EMAIL_LIKE);
  const existing = Number(rows[0].cnt);

  if (existing >= count) return;

  for (let i = existing + 1; i <= count; i++) {
    const n = String(i).padStart(6, '0');
    const email = `${EMAIL_PREFIX}${n}@local.test`;

    db.exec(
      `INSERT INTO app.users (id, roles, email, password_hash, created_at, updated_at)
       VALUES (UUID_TO_BIN(UUID()), '["ROLE_USER"]', ?, ?, NOW(), NOW());`,
      email,
      PASSWORD_HASH
    );
  }
}

function seedProfilesForLoadtestUsers() {
  db.exec(
    `INSERT INTO app.profiles (user_id, name, bio, created_at, updated_at)
     SELECT u.id,
            CONCAT('LoadUser ', SUBSTRING_INDEX(SUBSTRING_INDEX(u.email, '@', 1), '_', -1)),
            'LOADTEST',
            NOW(),
            NOW()
     FROM app.users u
     LEFT JOIN app.profiles p ON p.user_id = u.id
     WHERE u.email LIKE ?
       AND p.user_id IS NULL;`,
    EMAIL_LIKE
  );
}

function loadTestData() {
  let rows;

  const emailsAvailable = [];
  rows = db.query('SELECT email FROM app.users WHERE email LIKE ? LIMIT 10000;', EMAIL_LIKE);
  for (const row of rows) emailsAvailable.push(asciiRowToString(row.email));

  const profilesAvailable = [];
  rows = db.query(
    `SELECT BIN_TO_UUID(user_id) AS profile_uuid
     FROM app.profiles
     WHERE user_id IN (SELECT id FROM app.users WHERE email LIKE ?)
     LIMIT 10000;`,
    EMAIL_LIKE
  );
  for (const row of rows) profilesAvailable.push(asciiRowToString(row.profile_uuid));

  const tweetsAvailable = [];
  rows = db.query('SELECT BIN_TO_UUID(id) as tweet_id FROM app.tweets LIMIT 10000;');
  for (const row of rows) tweetsAvailable.push(asciiRowToString(row.tweet_id));

  mustNotBeEmpty(emailsAvailable, 'emailsAvailable (loadtest users)');
  mustNotBeEmpty(profilesAvailable, 'profilesAvailable (loadtest profiles)');
  mustNotBeEmpty(tweetsAvailable, 'tweetsAvailable (tweets)');

  console.log(
    `#TEST DATA# Mode=${MODE} RunId=${RUN_ID} | Emails=${emailsAvailable.length} | Profiles=${profilesAvailable.length} | TweetsToLike=${tweetsAvailable.length}`
  );

  return { emailsAvailable, profilesAvailable, tweetsAvailable };
}

/* =========================
   ✅ SETUP / TEARDOWN
========================= */

export function setup() {
  if (MODE === 'cleanup') {
    return { emailLike: EMAIL_LIKE, runId: RUN_ID };
  }

  if (SEED_USERS) {
    seedLoadtestUsers(USERS_COUNT);
    seedProfilesForLoadtestUsers();
  }

  const data = loadTestData();
  return {
    runId: RUN_ID,
    emailPrefix: EMAIL_PREFIX,
    emailLike: EMAIL_LIKE,
    ...data,
  };
}

export function teardown() {
  db.close();
}

/* =========================
   🔐 AUTH
========================= */

function login(emailsAvailable) {
  const payload = JSON.stringify({
    email: randomItem(emailsAvailable),
    password: PLAIN_PASSWORD,
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

function getJwtOncePerVu(emailsAvailable) {
  if (!jwtCache || jwtExpiredOrSoon()) {
    jwtCache = login(emailsAvailable);
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

function getProfile(jwt, profilesAvailable) {
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

function toggleLike(jwt, tweetsAvailable) {
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
   🧹 CLEANUP SCENARIO
========================= */

export function scenarioCleanup(data) {
  const like = data.emailLike;

  const r1 = db.query('SELECT COUNT(*) AS cnt FROM app.users WHERE email LIKE ?;', like);
  const users = Number(r1[0].cnt);

  const r2 = db.query(
    'SELECT COUNT(*) AS cnt FROM app.profiles WHERE user_id IN (SELECT id FROM app.users WHERE email LIKE ?);',
    like
  );
  const profiles = Number(r2[0].cnt);

  console.log(`#CLEANUP# Found users=${users}, profiles=${profiles}, like=${like}`);

  db.exec(
    'DELETE FROM app.profiles WHERE user_id IN (SELECT id FROM app.users WHERE email LIKE ?);',
    like
  );
  db.exec('DELETE FROM app.users WHERE email LIKE ?;', like);

  const r3 = db.query('SELECT COUNT(*) AS cnt FROM app.users WHERE email LIKE ?;', like);
  console.log(`#CLEANUP# Remaining users=${Number(r3[0].cnt)} for like=${like}`);
}

/* =========================
   🚶 SCENARIOS
========================= */

export function scenarioAuth(data) {
  login(data.emailsAvailable);
  randomSleep();
}

export function scenarioFeed(data) {
  mainFeed(null);
  const jwt = getJwtOncePerVu(data.emailsAvailable);
  mainFeed(jwt);
  randomSleep();
}

export function scenarioProfile(data) {
  const jwt = getJwtOncePerVu(data.emailsAvailable);
  const views = 3 + Math.floor(Math.random() * 6);
  for (let i = 0; i < views; i++) {
    getProfile(jwt, data.profilesAvailable);
    randomSleep();
  }
}

export function scenarioLike(data) {
  const jwt = getJwtOncePerVu(data.emailsAvailable);
  const likes = 5 + Math.floor(Math.random() * 8);
  for (let i = 0; i < likes; i++) {
    toggleLike(jwt, data.tweetsAvailable);
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
