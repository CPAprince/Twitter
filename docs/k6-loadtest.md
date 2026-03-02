# k6 Load Test — Twitter

This document explains how to prepare test data for k6, run the stress test, and fully remove all loadtest-related data afterwards.

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

## 2) Create loadtest users
Email pattern: loadtest_000001@local.test … loadtest_002000@local.test
Password: Pa$$word1

```bash
HASH='$2y$12$d765Nx30xRCm/cttxY4kJ.OmA3k4Ptd.4wz48.9WNaAbLBUAGK5aa'
for i in $(seq -w 1 2000); do
  echo "INSERT INTO users (id, roles, email, password_hash, created_at, updated_at)
        VALUES (UUID_TO_BIN(UUID()), '[\"ROLE_USER\"]', 'loadtest_${i}@local.test', '${HASH}', NOW(), NOW());"
done | docker compose exec -T database mysql -uapp -p'!ChangeMe!' app
```

Verify:

```bash
docker compose exec -T database mysql -uapp -p'!ChangeMe!' -e \
"SELECT COUNT(*) AS cnt FROM app.users WHERE email LIKE 'loadtest_%';"
```


## 3) Create profiles for loadtest users
profiles.user_id has FK ON DELETE CASCADE to users.id, so profiles are removed automatically when users are deleted.
Create profiles for all loadtest users that do not have a profile yet:

```bash
docker compose exec -T database mysql -uapp -p'!ChangeMe!' app -e "
INSERT INTO profiles (user_id, name, bio, created_at, updated_at)
SELECT u.id,
       CONCAT('LoadUser ', SUBSTRING_INDEX(SUBSTRING_INDEX(u.email, '@', 1), '_', -1)),
       'LOADTEST',
       NOW(),
       NOW()
FROM users u
LEFT JOIN profiles p ON p.user_id = u.id
WHERE u.email LIKE 'loadtest_%'
  AND p.user_id IS NULL;
"
```

Verify:

```bash
docker compose exec -T database mysql -uapp -p'!ChangeMe!' -e "
SELECT
  (SELECT COUNT(*) FROM app.users WHERE email LIKE 'loadtest_%') AS users_cnt,
  (SELECT COUNT(*) FROM app.profiles WHERE user_id IN (SELECT id FROM app.users WHERE email LIKE 'loadtest_%')) AS profiles_cnt;
"
```

## 4) Run the k6 test
```bash
k6 run loadtest-twitter.js
```

## 5) Cleanup — delete loadtest users and related data
Schema facts:
profiles.user_id has ON DELETE CASCADE → profiles are removed automatically
tweets.user_id has ON DELETE CASCADE → tweets are removed automatically
likes has no foreign keys → likes must be deleted manually
Correct cleanup flow:
delete likes:
likes made by loadtest users
likes on tweets that belong to loadtest users (if other users liked them)
delete loadtest users (profiles + tweets are removed via cascades)

```bash
docker compose exec -T database mysql -uapp -p'!ChangeMe!' app -e "
-- Likes made by loadtest users
DELETE FROM likes
WHERE user_id IN (SELECT id FROM users WHERE email LIKE 'loadtest_%');

-- Likes on tweets of loadtest users (if other users liked them)
DELETE FROM likes
WHERE tweet_id IN (
  SELECT t.id
  FROM tweets t
  JOIN users u ON u.id = t.user_id
  WHERE u.email LIKE 'loadtest_%'
);

-- Delete users (profiles + tweets are deleted via ON DELETE CASCADE)
DELETE FROM users
WHERE email LIKE 'loadtest_%';
"
```

Verify:

```bash
docker compose exec -T database mysql -uapp -p'!ChangeMe!' -e \
"SELECT COUNT(*) AS cnt FROM app.users WHERE email LIKE 'loadtest_%';"
```

## 6) Common issues

### `Field 'updated_at' doesn't have a default value`
Cause: `updated_at` is `NOT NULL` in `users` and `profiles`, and MySQL has no default value for it.  
Fix: Always set `updated_at` explicitly in INSERTs (the commands above use `NOW()`).

---
