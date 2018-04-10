# move.php

Move a single content object from one course to another. Some of the assets cause issues

USAGE: php move.php <source_module_id> <destination_course_id>

# move_all.php (php5)

Move all content from one course to another (emptying the original). Much more stable.

USAGE: php move_all.php <source_course_id> <destination_course_id>

# move_all7.php (php7)

Install mongoDB driver for php

	apt-get install php-dev
	pecl install mongodb 
		> edit php.ini and add library
  	composer require mongodb/mongodb

To use see php5 version


