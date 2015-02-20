# apiHelper

In the `<root>/class` directory, create individual `<objectName>/index.php` files for each object/class you wish to use in a RESTful api.

In the `index.php` file for each object/class, create the desired verb funciton (`GET`, `POST`, `PUT`, `DELETE`, etc...). The main `<root>/index.php` file will interpret the request and call the appropriate function.

##### Implement a user object:
1. Create folder **/classes/user**
2. Create file **/classes/user/index.php**
3. Create **verb** functions in new **index.php** file

##### user/index.php contents:
```
<?php
function GET() {
  // <do work here>
}
function POST() {
  // <do work here>
}
function PUT() {
  // <do work here>
}
function DELETE() {
  // <do work here>
}
```


