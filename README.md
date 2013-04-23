phpoetry
========

Fast and easy prototyping using reflection to expose PHP classes as JavaScript functions


Sometimes you don't need an entire framework, and just want to test out
some ideas using PHP and JavaScript.
PHPoetry is perfect for the rapid prototyping need, using Reflection to provide a
quick and dirty PHP-to-JavaScript AJAX bridge.

## 1 Minute Overview
1. Build a [static HTML](/public_html/index.html) web page
2. Make a [PHP class](/public_html/MyExample.php) with some public functions that you would like to call from the web page
3. Include [as a javascript file](/public_html/myexample_api.php?a=js) a PHP call to "externalize expose" the class, which auto-renders an API.
4. Start calling the PHP class as JavaScript functions!



