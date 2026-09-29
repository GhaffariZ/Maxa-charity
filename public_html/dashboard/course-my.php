<?php
/* Redirect old dashboard my-courses URL to clean public URL */
header('Location: /courses/my', true, 301);
exit;
