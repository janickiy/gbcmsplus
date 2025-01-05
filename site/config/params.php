<?php
return [
    'adminEmail' => 'admin@example.com',
    'jwt' => [
      'issuer' => '',  //name of your project (for information only)
      'audience' => '',  //description of the audience, eg. the website using the authentication (for info only)
      'id' => '',  //a unique identifier for the JWT, typically a random string
      'expire' => 300,  //the short-lived JWT token is here set to expire after 5 min.
      'secure' => true,  //Secure flag for cookies.
    
    ],
];
