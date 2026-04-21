# k6 Load Test — Twitter

This document explains how to prepare test data for k6, run the stress test, and fully remove all loadtest-related data
afterwards.

Assumptions:

- MySQL runs in Docker Compose as service `database`
- Database: `app`
- MySQL credentials: `app / !ChangeMe!`
- k6 script: `loadtest-twitter.js`
- JWT `token_ttl: 900` seconds (15 minutes)

---

## 1) Make sure the stack is running

```bash
docker compose up -d
docker compose ps
```

## 2) Create (if missing) N users for this RUN_ID, run loadtest (no auto-delete):

```bash
   BASE_URL=https://localhost SEED_USERS=1 USERS_COUNT=2000 RUN_ID=20260305_001 k6 run loadtest-twitter.js
```

## 3) Re-run loadtest with the same users:

```bash
   BASE_URL=https://localhost SEED_USERS=1 USERS_COUNT=2000 RUN_ID=20260305_001 k6 run loadtest-twitter.js
```

## 4) Cleanup ONLY (special command):

```bash
   MODE=cleanup RUN_ID=20260305_001 k6 run loadtest-twitter.js
```

