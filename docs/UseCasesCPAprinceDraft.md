# **Login Use Case**

### **Actors**

* Unauthorized User

### **Preconditions**

* User account is registered.  
* User provides valid email and password.

### **Flow**

1. Frontend → Backend  
    **POST** /api/token  
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

  "accessToken": "\<jwt-accessToken\>",  
  "refreshToken": "\<jwt-refreshToken\>",  
“\_links”:{  
"self": { "href": "/api/token"}  
"logout": { "href": "/api/token", “method”:”delete”}  
}  
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
    **DELETE** `/api/token`  
    Headers: `Authorization: Bearer <accessToken>`  
   `{`refreshToken:”\<jwt-refreshToken\>”`}`

2. Backend:  
   * Invalidates the refresh token.

3. Backend → Frontend  
    **204 No Content**  
   **{**

   “\_links”:{

   "self":{ "href": "/api/token", “method”:”delete”},  
   “login”:{ "href": "/api/token"}

   **}**

### **Required Data**

* Access token in headers  
* Refresh token (if required by implementation)

# **Registration Use Case**

### **Actors**

* New unauthorized user

### **Flow**

1) Frontend → Backend  
    **POST** `/api/users`

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

 “\_links”:{

“login”:{ "href": "/api/token"}

  }

OR

{  
“Error”: “Wrong email or invalid password” // do not pass validation rules  
}

# **Feed — All Tweets** 

### **Actors**

* Guest user  
* Authenticated user

### **Flow**

1. Frontend → Backend  
    **GET** `/api/tweets?page=1&limit=100`

2. Backend:  
   * Returns list of tweets sorted by newest.  
   * Includes author name, date and number of likes  
3. Backend → Frontend  
    **200 OK**

{

  "tweets": \[

    {

      "uuid": "\<tweet UUID\>",

      "author":  {"id": "\<author UUID\>", "name": "AlexJohnson"},

      "content": "Just wrapped up a productive meeting. Excited about the new direction the team is taking — feels like we’re finally gaining some momentum.",

      "created\_at": "2025-02-15T18:44:21Z",

      "updated\_at": "2025-02-15T18:50:12Z",

      "tweet\_likes": 32

 “\_links”:{

“self”:{ "href": "/api/`tweets?id=`\<tweet UUID\>"}

    },

    {

       "uuid": "\<tweet UUID\>",

      "author": {"id":"\<author UUID\>",  "name": "SophieWilliams"},

      "content": "Trying out a new workflow today, and so far it’s surprisingly smooth. Sometimes small adjustments make a huge difference in productivity.",

      "created\_at": "2025-02-14T09:28:05Z",

      "updated\_at": "2025-02-14T09:34:52Z",

      "tweet\_likes": 21  

 “\_links”:{

“self”:{ "href": "/api/`tweets?id=`\<tweet UUID\>"}

  },

    {

      "uuid": "\<tweet UUID\>",

      "author": {"id":"\<author UUID\>", "name": "MichaelBrown"},

      "content": "Weather is perfect today. Took a quick break outside and now I feel recharged. Amazing what 10 minutes in the sun can do.",

      "created\_at": "2025-02-13T14:03:17Z",

      "updated\_at": "2025-02-13T14:06:10Z",

      "tweet\_likes": 15

    },

    {

     "uuid": "\<tweet UUID\>",

      "author": {"id":"\<author UUID\>", "name": "EmilyClark"},

      "content": "Started reading a new book yesterday and I’m already hooked. It’s been a while since something grabbed my attention like this.",

      "name": "EmilyClark",

      "created\_at": "2025-02-12T20:11:49Z",

      "updated\_at": "2025-02-12T20:15:37Z",

      "like\_count": 27    }

  \]

}

# **Feed — Profile Tweets** 

### **Actors**

* Guest user  
* Authenticated user

### **Flow**

4. Frontend → Backend  
    **GET** `/api/users/{uuid}/tweets/?page=1&limit=100`  
5. Backend:  
   * Returns list of tweets sorted by newest.  
   * Includes author name, date, like and dislike count  
6. Backend → Frontend  
    **200 OK**

{  
  "tweets": \[  
    {  
     "uuid": "\<tweet UUID\>",  
      "author": {"id": "\<author UUID\>", "name": "JohnMartin"},
      "content": "Starting my day with a strong coffee and a clear plan. Trying to stay focused and make progress one step at a time. Small consistent efforts really do add up.",  
      "created\_at": "2025-02-10T07:42:11Z",  
      "updated\_at": "2025-02-10T07:51:26Z",  
      "tweet\_likes": 23  
    },  
    {  
    "uuid": "\<tweet UUID\>",  
      "author": {"id": "\<author UUID\>", "name": "JohnMartin"},
      "content": "Just finished a long coding session. It’s amazing how solving even a tiny bug can give such a big dopamine boost. The trick is not giving up before it clicks\!", 
      "created\_at": "2025-02-11T12:18:54Z",  
      "updated\_at": "2025-02-11T12:20:03Z",  
      "tweet\_likes": 41  
    },  
    {  
      "uuid": "\<tweet UUID\>",  
      "author": {"id": "\<author UUID\>", "name": "JohnMartin"},
      "content": "Taking a short walk to refresh my mind. Fresh air really helps reset focus, especially after hours at the computer. Sometimes that’s all you need to keep going." 
      "created\_at": "2025-02-12T16:09:33Z",  
      "updated\_at": "2025-02-12T16:12:10Z",  
      "tweet\_likes": 17    }  
  \]  
}

