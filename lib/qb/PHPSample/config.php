
<?php
  // setting up session
  /* note: This is not a secure way to store oAuth tokens. You should use a secure
  *     data sore. We use this for simplicity in this example.
  */
  session_save_path('/tmp');
  session_start();

define('OAUTH_CONSUMER_KEY', 'qyprd3v8NFl7W89u9z1rvgIsxSu6zo');
  define('OAUTH_CONSUMER_SECRET', 'NQzoaHCCRB3vXqTzYZ4GYE2OTtPFbP5lh3zhxYMb');
  
  
  if(strlen(OAUTH_CONSUMER_KEY) < 5 OR strlen(OAUTH_CONSUMER_SECRET) < 5 ){
    echo "<h3>Set the consumer key and secret in the config.php file before you run this example</h3>";
  }
  
 ?>
