# Use Cases – Auth, Profile, Tweet, Like/Dislike

This document describes **domain-level use cases** for authentication and working with tweets (including feed), as well as profile and likes.

Focus:
- what the system does (behaviour, **not HTTP**);
- what data is required (mandatory vs optional);
- business rules and domain errors.

---

## 0. HTTP Status Mapping (API) *(reference)*

- **200** OK – successful GET / successful operation returning data
- **201** Created – successful POST (new resource created)
- **401** Unauthorized – not authenticated
- **403** Forbidden – authenticated but not allowed
- **404** Not Found – resource not found
- **415** Unsupported Media Type – only `application/json`
- **422** Unprocessable Entity – validation error (instead of 400)

---

## 1. Error Codes (Domain)

Short domain error codes (later mapped to HTTP):

- `AUTH_UNAUTHORIZED` – user is not authenticated.
- `AUTH_FORBIDDEN` – user has no permission for the operation (e.g. updating someone else’s tweet).
- `AUTH_INVALID_CREDENTIALS` – email or password is incorrect.
- `AUTH_TOKEN_INVALID` – refresh token is invalid or expired.
- `USER_ALREADY_EXISTS` – user with this email already exists.
- `USER_NOT_FOUND` – user does not exist (rare; often hidden behind `AUTH_INVALID_CREDENTIALS` or `AUTH_TOKEN_INVALID`).
- `VALIDATION_ERROR` – invalid input data (generic).
- `PROFILE_NOT_FOUND` – profile does not exist.
- `TWEET_NOT_FOUND` – tweet does not exist.
- `TWEET_VALIDATION_ERROR` – invalid tweet content (empty / > 280 chars).

> Like/Dislike is a **toggle**, so there is no `LIKE_ALREADY_EXISTS` / `LIKE_NOT_FOUND`.

---

## 2. Authentication

### 2.1. Required API Routes

- `POST /auth/login`
- `POST /auth/register`
- `POST /auth/logout`
- `POST /auth/refresh`

---

### UC-A1: Login

**Goal**  
Authenticate a user and issue access + refresh tokens.

**Actor**  
Unauthorized user.

**Endpoint**  
`POST /auth/login`

**Required Data**
- `email`
- `password`

**Preconditions**
- User account exists.

**Flow**
1. User provides `email` and `password`.
2. System:
   - finds User by `email`;
   - verifies the password against the stored hash.
3. If credentials are valid:
   - system issues an access token;
   - system issues a refresh token.
4. System returns both tokens.

**Example (output)**

```json
{
  "accessToken": "jwt-access-token",
  "refreshToken": "jwt-refresh-token"
}
```

**Possible Errors**
- `AUTH_INVALID_CREDENTIALS` – email or password is incorrect.

---

### UC-A2: Logout

**Goal**  
Invalidate the current refresh token and terminate the session.

**Actor**  
Authenticated user.

**Endpoint**  
`POST /auth/logout`

**Required Data**
- `refreshToken`

**Preconditions**
- User is authenticated.

**Flow**
1. User initiates logout.
2. System invalidates the provided refresh token.
3. Session is terminated.

**Example (output)**

```json
{
  "loggedOut": true
}
```

**Possible Errors**
- `AUTH_UNAUTHORIZED` – user is not authenticated.
- `AUTH_TOKEN_INVALID` – refresh token is invalid or already expired.

---

### UC-A3: Register User

**Goal**  
Create a new user account.

**Actor**  
Unauthorized user.

**Endpoint**  
`POST /auth/register`

**Required Data**
- `email`
- `password`

**Business Rules**
- Email must have a valid format.
- Email must be unique.
- Password must satisfy security rules (min length/complexity – validated at domain level).
- Password is stored only as a hash.

**Flow**
1. User provides registration data.
2. System:
   - validates email format;
   - checks email uniqueness;
   - validates password;
   - hashes the password.
3. System creates a new User.
4. System triggers **UC-P1: Create Profile (system)** for the new `userId` + `email`.

**Example (output)**

```json
{
  "userId": "user-42"
}
```

**Possible Errors**
- `USER_ALREADY_EXISTS` – email is already registered.
- `VALIDATION_ERROR` – email or password does not meet validation rules.

---

### UC-A4: Refresh Access Token *(optional, if supported)*

**Goal**  
Issue a new access token using a refresh token.

**Actor**  
Unauthorized user (who has a refresh token).

**Endpoint**  
`POST /auth/refresh`

**Required Data**
- `refreshToken`

**Flow**
1. User provides `refreshToken`.
2. System validates that the refresh token:
   - is well-formed / signature is valid;
   - is not expired;
   - is not invalidated (logout, rotation, etc.).
3. System issues a new access token (and optionally rotates refresh token).

**Example (output)**

```json
{
  "accessToken": "new-jwt-access-token",
  "refreshToken": "jwt-refresh-token"
}
```

**Possible Errors**
- `AUTH_TOKEN_INVALID` – refresh token is invalid or expired.

---

## 3. Profile (CRU)

### 3.1. Required API Routes

- `POST /profile` *(internal/system; triggered by registration)*
- `GET /profile/{userId}`
- `PATCH /profile/me`

---

### 3.2. Profile Entity: Data

- Required:
  - `userId`
- User-editable fields:
  - `name` – public display name (**auto-generated on registration**, can be changed later)
  - `bio` – optional
- System fields:
  - `updatedAt`

---

### UC-P1: Create Profile (system)

**Goal**  
Automatically create a Profile linked to the new User after successful registration.

**Actor**  
System (Auth module).

**Endpoint**  
`POST /profile` *(internal/system)*

**Required Data**
- `userId`
- `email`

**Business Rules (name generation)**
- `name` is generated from the email local-part (before `@`), e.g. `johndoe@gmail.com` → `johndoe`
- Normalization: trim, lowercase
- Uniqueness:
  - if `johndoe` already exists → try `johndoe2`
  - if `johndoe2` exists → try `johndoe3`
  - repeat until unique

**Flow**
1. Auth completes registration and passes `userId` + `email`.
2. System generates an initial `name` and ensures uniqueness.
3. System creates Profile:
   - `userId`
   - `name` (generated unique value)
   - `bio = ""`
   - `updatedAt = now`

**Examples**
- `johndoe@gmail.com` → `name = "johndoe"`
- `johndoe@outlook.com` → `name = "johndoe2"`

**Possible Errors**
- Typically none (if persistence fails → unexpected system error, not a domain code).

---

### UC-P2: Read Profile

**Goal**  
Return public information about a user profile.

**Actor**  
Any user.

**Endpoint**  
`GET /profile/{userId}`

**Required Data**
- `userId`

**Flow**
1. System finds Profile by `userId`.
2. System returns `userId`, `name`, `bio`, `updatedAt`.

**Example (output)**

```json
{
  "userId": "user-42",
  "name": "johndoe",
  "bio": "Hello!",
  "updatedAt": "2025-12-10T10:00:00Z"
}
```

**Possible Errors**
- `PROFILE_NOT_FOUND`

---

### UC-P3: Update Profile

**Goal**  
Allow a user to change `name` and/or `bio`.

**Actor**  
Authenticated user.

**Endpoint**  
`PATCH /profile/me`

**Required Data**
- Authentication context (current `userId`)

**Optional Input**
- new `name`
- new `bio`  
*(at least one field must be present)*

**Flow**
1. User submits new `name` and/or `bio`.
2. System checks authentication.
3. System finds Profile by current `userId`.
4. System validates provided fields.
5. If `name` is provided:
   - validate format/length;
   - ensure uniqueness (same rule as UC-P1; suffixes `2`, `3`, … if needed).
6. System updates Profile and sets `updatedAt = now`.
7. System returns updated Profile.

**Example (output)**

```json
{
  "userId": "user-42",
  "name": "john_doe",
  "bio": "Fishing & mountains",
  "updatedAt": "2025-12-10T11:00:00Z"
}
```

**Possible Errors**
- `AUTH_UNAUTHORIZED`
- `VALIDATION_ERROR`
- `PROFILE_NOT_FOUND` *(if profile for current userId does not exist)*

---

## 4. Tweet (CRU)

### 4.1. Required API Routes

- `POST /users/{userId}/tweets`
- `GET /users/{userId}/tweets` *(user feed)*
- `GET /users/{userId}/tweets/{tweetId}`
- `PATCH /users/{userId}/tweets/{tweetId}`
- `GET /tweet` *(global feed)*

---

### 4.2. Tweet View (output contract)

In all read/list/create/update scenarios, a tweet is returned as a “Tweet view”:

- `id`
- `content`
- `createdAt`
- `updatedAt` *(null if never edited)*
- `author`:
  - `id` (userId)
  - `name` (profile display name)
- `likesCount` *(total likes for the tweet)*

---

### UC-T1: Create Tweet

**Goal**  
An authenticated user publishes a new tweet.

**Actor**  
Authenticated user.

**Endpoint**  
`POST /users/{userId}/tweets`

**Required Data**
- `content` (≤ 280 chars)
- `userId` is taken from auth context (must match `{userId}`)

**Preconditions**
- User is authenticated.
- Profile exists for this user.

**Flow**
1. System checks authentication.
2. Validate `content` (not empty, ≤ 280).
3. Create Tweet (`id`, `content`, `createdAt`, `updatedAt = null`).
4. Resolve author Profile (`id`, `name`) and set `likesCount = 0`.
5. Return Tweet view.

**Example (output)**

```json
{
  "id": "tweet-123",
  "content": "Hello, world",
  "createdAt": "2025-12-10T09:00:00Z",
  "updatedAt": null,
  "likesCount": 0,
  "author": {
    "id": "user-42",
    "name": "johndoe"
  }
}
```

**Possible Errors**
- `AUTH_UNAUTHORIZED`
- `PROFILE_NOT_FOUND`
- `TWEET_VALIDATION_ERROR`

---

### UC-T2: Read Single Tweet

**Goal**  
Retrieve a specific tweet by `tweetId`, including author info.

**Actor**  
Any user.

**Endpoint**  
`GET /users/{userId}/tweets/{tweetId}`

**Required Data**
- `userId`
- `tweetId`

**Flow**
1. Find Tweet by `tweetId`.
2. Ensure Tweet belongs to `{userId}` (route constraint).
3. Resolve author Profile by internal `tweet.userId`.
4. Calculate `likesCount`.
5. Return Tweet view.

**Example (output)**

```json
{
  "id": "tweet-123",
  "content": "Hello, world",
  "createdAt": "2025-12-10T09:00:00Z",
  "updatedAt": "2025-12-10T10:00:00Z",
  "likesCount": 12,
  "author": {
    "id": "user-42",
    "name": "johndoe"
  }
}
```

**Possible Errors**
- `TWEET_NOT_FOUND`

---

### UC-T3: Read User Tweets (User Feed)

**Goal**  
Retrieve tweets published by a specific user.

**Actor**  
Any user (anonymous or authenticated).

**Endpoint**  
`GET /users/{userId}/tweets`

**Required Data**
- `userId` *(authorId)*

**Optional Input**
- pagination parameters (`limit`, `offset` or `page`)

**Flow**
1. Verify that the author Profile exists.
2. Select tweets where `tweet.userId = userId`.
3. Sort tweets by `createdAt` (newest first).
4. Apply pagination.
5. Return list of Tweet views (with `likesCount`).

**Possible Errors**
- `PROFILE_NOT_FOUND`

---

### UC-T4: Read Global Tweets (Global Feed)

**Goal**  
Retrieve a list of recent tweets from all users.

**Actor**  
Any user (anonymous or authenticated).

**Endpoint**  
`GET /tweets`

**Optional Input**
- pagination parameters (`limit`, `offset` or `page`)

**Flow**
1. Select all tweets.
2. Sort tweets by `createdAt` (newest first).
3. Apply pagination.
4. Return list of Tweet views (with `author` and `likesCount`).

**Possible Errors**
- None. An empty list is a valid result.

---

### UC-T5: Update Tweet

**Goal**  
Allow the author of a tweet to change its text.

**Actor**  
Authenticated user.

**Endpoint**  
`PATCH /users/{userId}/tweets/{tweetId}`

**Required Data**
- `userId` from auth context (must match `{userId}`)
- `tweetId`
- `content` (≤ 280)

**Access Rules**
- Must be authenticated.
- Can update only own tweets.

**Flow**
1. Check auth.
2. Find Tweet by `tweetId`.
3. If not found → `TWEET_NOT_FOUND`.
4. If `tweet.userId != current userId` → `AUTH_FORBIDDEN`.
5. Validate new `content` (not empty, ≤ 280).
6. Update `content`, set `updatedAt = now`.
7. Return updated Tweet view.

**Possible Errors**
- `AUTH_UNAUTHORIZED`
- `TWEET_NOT_FOUND`
- `AUTH_FORBIDDEN`
- `TWEET_VALIDATION_ERROR`

---

## 5. Like / Dislike (Toggle)

### 5.1. Required API Routes

- `POST /users/{userId}/likes/{tweetId}`

---

### UC-LD1: Toggle Like

**Goal**  
Toggle like state for a tweet for the current user.

**Actor**  
Authenticated user.

**Endpoint**  
`POST /users/{userId}/likes/{tweetId}`

**Required Data**
- `tweetId`
- `userId` from auth context (must match `{userId}`)

**Flow**
1. Check auth.
2. Ensure Tweet exists.
3. If Like(`userId`, `tweetId`) does not exist → create Like → return `{ "liked": true }`.
4. Else → remove Like → return `{ "liked": false }`.

**Examples (output)**

```json
{
  "liked": true
}
```

```json
{
  "liked": false
}
```

**Possible Errors**
- `AUTH_UNAUTHORIZED`
- `TWEET_NOT_FOUND`
