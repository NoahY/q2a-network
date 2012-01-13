==============================
Question2Answer Network Sites
==============================
-----------
Description
-----------
This is a plugin for **Question2Answer** that provides site networking capabilities. 

--------
Features
--------
- adds individual active site icons and site points to user_meta
- option to restrict "active" to minimum score per site
- optionally shows network points instead of this site points in user_meta
- widget lists all sites in network

------------
Installation
------------
#. Install Question2Answer_
#. Get the source code for this plugin from github_, either using git_, or downloading directly:

   - To download using git, install git and then type 
     ``git clone git://github.com/NoahY/q2a-network.git network``
     at the command prompt (on Linux, Windows is a bit different)
   - To download directly, go to the `project page`_ and click **Download**

#. Go to **Admin -> Plugins** on your q2a install, add a new site (see `Sites`_ below for notes on this), select the '**Activate Network Sites**' option, then '**Save Changes**'.

.. _Question2Answer: http://www.question2answer.org/install.php
.. _git: http://git-scm.com/
.. _github:
.. _project page: https://github.com/NoahY/q2a-network

.. Sites:

-----------
Sites
-----------
Networking sites is partially enabled in the core; there are various ways to accomplish this, depending on your site setup.  If you are using single-sign-on or Wordpress integration, you are half-way there.  If you are not, see the instructions in qa-config.php to set up your sites to user the same qa_users table.  Either way, **both sets of database tables must be in the same database** for this plugin to work.

It is also possible to setup two sites to use the same set of php files, though this is a bit of a hack.  To do this, here's what I do:

1. Choose one site as the parent, where the code will reside.  Edit qa-config.php and comment out (or remove) the line that says:
::
	define('QA_MYSQL_TABLE_PREFIX', 'qa_');

2. Edit index.php and add the above line there, before the following line:
::
	require 'qa-include/qa-index.php';

3. Make a new directory somewhere else on your server where the second site will reside.  Add a new index.php file to that directory.

4. Make symbolic links from the first site's qa-content, qa-theme, and qa-plugin directories to this new directory.

5. Edit the new index.php file to read as follows:
::
	<?php
		define('QA_BASE_DIR','/var/www/q2a/' ); // where your original directory is on the server
		define('QA_MYSQL_TABLE_PREFIX', 'qa2_'); // the second site's prefix
		require('/var/www/q2a/qa-include/qa-index.php'); // where this file is in the qa-include directory of the original install.
		
This seems to work with Q2A 1.5 to allow multiple sites to use the same core code, plugins, and themes.

----------
Disclaimer
----------
This is **beta** code.  It is probably okay for production environments, but may not work exactly as expected.  Refunds will not be given.  If it breaks, you get to keep both parts.

-------
Release
-------
All code herein is Copylefted_.

.. _Copylefted: http://en.wikipedia.org/wiki/Copyleft

---------
About q2A
---------
Question2Answer is a free and open source platform for Q&A sites. For more information, visit:

http://www.question2answer.org/
