1) Go to https://console.developers.google.com/project and sign in/register
2) Create a project
3) Go to APIs & auth
4) Enable Google Drive API
5) Go to Credentials
6) Create a Client ID for web application, remember to whitelist the landing page
7) Pass Client ID and client secret to the constructor

From here on needs to be better implemented by whatever app is using this:

8) Click the URL through to Google
9) Authorise access
10) Set $auth_token (third parameter to the constructor) to the JSON blob
11) This authorisation lasts until revocation by the user