# **Login Use Case**

### **Actors**

* Unauthorized User

### **Preconditions**

* User account exists.  
* User provides valid email and password.

### **Flow**

1. Frontend → Backend  
    **POST** /api/login (see API routes in docs)  
    {

  "email": "string",  
  "password": "string"  
}

2. Backend:  
   * Find user by email.  
   * Verifies the hashed password.  
   * Generates GWT tokens (access \+ refresh).  
3. Backend → Frontend  
    **200 OK**  
    { 

  "accessToken": "jwt-string",  
  "refreshToken": "jwt-string",  
  }  
OR   
Error if incorrect email or password  
**401** **Unauthorized**  
{  
“Error”: “1001”  
“ErrorMessage”: “Incorrect email or password”  
}

# **Logout Use Case**

### **Actors**

* Authenticated User

### **Flow**

1. Frontend → Backend  
    **POST** `/api/logout`  
    Headers: `Authorization: Bearer <accessToken>`

2. Backend:  
   * Invalidates the refresh token.

3. Backend → Frontend  
    **204 No Content**

### **Required Data**

* Access token in headers  
* Refresh token (if required by implementation)

# **Registration Use Case**

### **Actors**

* New unauthorized user

### **Flow**

1) Frontend → Backend  
    **POST** `/api/register`

    `{`

  `"email": "string",`  
  `"password": "string",`  
`}`

2) Backend:  
   * Validates email and it’s uniqueness.  
   * Validate password by length, case, symbols etc.  
   * Hashes password.  
   * Creates new user if all data correct.

3) Backend → Frontend  
    **201 Created**  
    { 

  "accessToken": "jwt-string",  
  "refreshToken": "jwt-string",  
  }

OR  
if Email already exist  
{  
“Error”: “Email already registered” // or display OK status and do nothing to prevent registered email status confirmation  
}

OR

{  
“Error”: “email or invalid password” // do not pass validation rules  
}

# **Feed — All Tweets** 

### **Actors**

* Guest user  
* Authenticated user

### **Flow**

1. Frontend → Backend  
    **GET** `/api/feed?page=1&limit=100`

2. Backend:  
   * Returns list of tweets sorted by newest.  
   * Includes author name, date, like and dislike count  
3. Backend → Frontend  
    **200 OK**

{

  "tweets": [

    {
      "id": "1",
      "uuid": "e1c0b4d8-9f7f-4af0-9b67-4bc88c51c129",
      "content": "Just wrapped up a productive meeting. Excited about the new direction the team is taking — feels like we’re finally gaining some momentum.",
      "name": "AlexJohnson",
      "created_at": "2025-02-15T18:44:21Z",
      "updated_at": "2025-02-15T18:50:12Z",
      "like_count": 32,
      "dislike_count": 1

    },

    {
      "id": "2",
      "uuid": "0c72f0a9-2207-4429-a808-92a9f6bbf91f",
      "content": "Trying out a new workflow today, and so far it’s surprisingly smooth. Sometimes small adjustments make a huge difference in productivity.",
      "name": "SophieWilliams",
      "created_at": "2025-02-14T09:28:05Z",
      "updated_at": "2025-02-14T09:34:52Z",
      "like_count": 21,
      "dislike_count": 0

    },

    {
      "id": "3",
      "uuid": "4afecc2d-7466-49b8-a8a9-df84e45cb9d4",
      "content": "Weather is perfect today. Took a quick break outside and now I feel recharged. Amazing what 10 minutes in the sun can do.",
      "name": "MichaelBrown",
      "created_at": "2025-02-13T14:03:17Z",
      "updated_at": "2025-02-13T14:06:10Z",
      "like_count": 15,
      "dislike_count": 3

    },

    {
      "id": "4",
      "uuid": "8f8d0ea1-1a81-4e22-9c1b-f37a9010d3bf",
      "content": "Started reading a new book yesterday and I’m already hooked. It’s been a while since something grabbed my attention like this.",
      "name": "EmilyClark",
      "created_at": "2025-02-12T20:11:49Z",
      "updated_at": "2025-02-12T20:15:37Z",
      "like_count": 27,
      "dislike_count": 2

    }

  ]

}

# **Feed — Profile Tweets** 

### **Actors**

* Guest user  
* Authenticated user

### **Flow**

4. Frontend → Backend  
    **GET** `/api/feed/user/{uuid}/?page=1&limit=100`  
5. Backend:  
   * Returns list of tweets sorted by newest.  
   * Includes author name, date, like and dislike count  
6. Backend → Frontend  
    **200 OK**

{
  "tweets": [

    {
      "id": "1",
      "uuid": "7d92b2af-0d2a-4e6a-a4e7-2d8b61c83b2a",
      "content": "Starting my day with a strong coffee and a clear plan. Trying to stay focused and make progress one step at a time. Small consistent efforts really do add up.",
      "name": "JohnMartin",
      "created_at": "2025-02-10T07:42:11Z",
      "updated_at": "2025-02-10T07:51:26Z",
      "like_count": 23,
      "dislike_count": 2

    },

    {
      "id": "2",
      "uuid": "c2b58a13-9a2f-42d4-b75f-d6df0fc9aeb7",
      "content": "Just finished a long coding session. It’s amazing how solving even a tiny bug can give such a big dopamine boost. The trick is not giving up before it clicks\!",
      "name": "JohnMartin",
      "created_at": "2025-02-11T12:18:54Z",
      "updated_at": "2025-02-11T12:20:03Z",
      "like_count": 41,
      "dislike_count": 4

    },

    {
      "id": "3",
      "uuid": "f41a1b6a-0d52-4bd1-bf97-6c8d32e5f12e",
      "content": "Taking a short walk to refresh my mind. Fresh air really helps reset focus, especially after hours at the computer. Sometimes that’s all you need to keep going.",
      "name": "JohnMartin",
      "created_at": "2025-02-12T16:09:33Z",
      "updated_at": "2025-02-12T16:12:10Z",
      "like_count": 17,
      "dislike_count": 1

    }

  ]
}

