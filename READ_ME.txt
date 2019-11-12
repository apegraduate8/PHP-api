
# ANTHONY PEGUES

○ How long it took you to complete the test.
 - In total about 1 1/2 days

○ A list of general steps you took to complete the test from start to finish.
  - My experience with php has been working with an existing platform, Magento for example.
    I never had to really do anything like this so I had to figure out how to do it properly.
    I watched several youtube videos and googled a bunch of stuff. Overall I wouldn't say it was very complicated.
    I did have some small issues getting xampp set up on my computer but nothing really serious.
    If I had to do this again I could complete it much quicker.
    *Note - I was also aware of the fact that I could of created seperate files for each endpoints, login.php, register.php, etc.., but I felt more comfortable with this approach.

○ A link to your register.php script (i.e http://dev.datechnologies.co/Tests/scripts/user-signup.php )
 - https://github.com/apegraduate8/PHP-api/blob/master/Files/User.php


○ Notes on possible issues with how the endpoints are currently structured, how they could be improved, any possible
security issues with the current implementation.
 - I would saying developing a more secure authentication system for users, OAUth. Registration and login
 - Maybe even use encryption methods on messages sent between users


○ If you were were designing the endpoints yourself, what changes would you make?
 - Probably develop the registration endpoint a little more.
 - Incorporate a secret key. Require a secret key to present for every message sent between users


● All of these files should be submitted in a single zip file named with the following format:
“lastname_firstname_backend_test.zip”



TESTING:
- When testing you might have to change $this->host = '127.0.0.1:3306'; around line 27 in D-A-php/Files/Model.php to match your db ip
- CD into D-A-php/Files and run php -S localhost:8000
- Use POSTMAN to make get/post requests to each endpoint

EXAMPLE REQUESTS:
  1) /login
  2) /register
  3) /getUsers?request_user_id=12
  4) /viewMessages?user_id_a=1&user_id_b=2
  5) /sendMessage



MY REFERENCES:

PHP 5.4 and later have a built-in web server these days.
Just RUN
cd path/to/your/app
php -S localhost:8000


https://www.php.net/manual/en/function.spl-autoload-register.php
https://www.arclab.com/en/kb/php/php-warning-cannot-modify-header-information-headers-already-sent.html

// error - Warning:  mysqli::real_connect(): (HY000/2002): Cannot assign requested address in ..
https://en.it1352.com/article/c9c692028f3b49479af4868386ff6308.html

https://community.webfaction.com/questions/14392/no-such-file-or-directory-when-trying-to-connect-to-custom-mysql-instance

mysql datetime
http://www.mysqltutorial.org/mysql-datetime/
https://dev.mysql.com/doc/refman/8.0/en/timestamp-initialization.html

// query to grab view all messages between two users
// SELECT * FROM `messages` WHERE sender_user_id IN (1,2) AND recipient_id IN (2,1) ORDER BY epoch


//https://blog.restcase.com/top-5-rest-api-security-guidelines/
