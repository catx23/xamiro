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


-flysystem-test
-flysystem-test-local-00
client id : 34179804656-d6a852gc3pij6ep8nqtmbh29q0no6hmq.apps.googleusercontent.com
email : 4179804656-d6a852gc3pij6ep8nqtmbh29q0no6hmq@developer.gserviceaccount.com


Client ID	34179804656-v7ckk0id5h1dcrpoj07c2343vbf2qto2.apps.googleusercontent.com
Email address	34179804656-v7ckk0id5h1dcrpoj07c2343vbf2qto2@developer.gserviceaccount.com
Client secret	ZnWLJFaJh_c1AO8wk_d4Y7fk
Redirect URIs
http://mc007ibi.dyndns.org:81/x4mm/Code/trunk/xide-php/xapp/xcf/index.php?debug=true
Javascript Origins
http://mc007ibi.dyndns.org

{"web":{"auth_uri":"https://accounts.google.com/o/oauth2/auth","client_secret":"ZnWLJFaJh_c1AO8wk_d4Y7fk","token_uri":"https://accounts.google.com/o/oauth2/token","client_email":"34179804656-v7ckk0id5h1dcrpoj07c2343vbf2qto2@developer.gserviceaccount.com","redirect_uris":["http://mc007ibi.dyndns.org:81/x4mm/Code/trunk/xide-php/xapp/xcf/index.php?debug\u003dtrue"],"client_x509_cert_url":"https://www.googleapis.com/robot/v1/metadata/x509/34179804656-v7ckk0id5h1dcrpoj07c2343vbf2qto2@developer.gserviceaccount.com","client_id":"34179804656-v7ckk0id5h1dcrpoj07c2343vbf2qto2.apps.googleusercontent.com","auth_provider_x509_cert_url":"https://www.googleapis.com/oauth2/v1/certs","javascript_origins":["http://mc007ibi.dyndns.org"]}}
