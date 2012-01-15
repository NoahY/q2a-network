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
- ability to migrate questions, including entire set of children and grandchildren (see `Migrating`_ below)
- show optional migrated notice below questions

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

-----------
Sites
-----------
.. Sites:
Networking sites is partially enabled in the core; there are various ways to accomplish this, depending on your site setup.  If you are using single-sign-on or Wordpress integration, you are half-way there.  If you are not, see the instructions in qa-config.php to set up your sites to user the same qa_users table.  Either way, **both sets of database tables must be in the same database** for this plugin to work.

It is also possible to setup two sites to use the same set of php files, though it is not necessary.  To accomplish this, here's what I do:

1. Place your source Q2A files in a directory outside of your site root, e.g. /home/me/q2a/

2. Make a new directory somewhere else on your server where your actual q2a site will reside, e.g. /var/www/q2a/

3. Make symbolic links from the source files (not the root directory) to the new directory:
::
    ln -s /home/me/q2a/* /var/www/q2a/

Note: if you are going to use neat urls, copy or link to the original .htaccess as well:
::
    ln -s /home/me/q2a/.htaccess /var/www/q2a/

4. Copy the original qa-config-example.php to qa-config.php in the new directory    

5. Edit the new qa-config.php file as needed, using a unique table prefix.

6. Repeat steps 2-5 for each site, making sure the table prefix of each site is unique.

This seems to work with Q2A 1.5 to allow multiple sites to use the same core code, plugins, and themes.  If you want to use unique sets of plugins and themes for each site, just delete the symlink for the qa-theme and qa-plugin directories for that site, and replace them with actual directories with actual plugins and themes.

-----------
Migrating
-----------
.. Migrating:
Below the main admin form, there is another form to migrate posts.  This is highly experimental, and may lead to data loss, hair loss, dead kittens, etc.  This process also moves all children and grandchildren (i.e. answers and comments), votes, flags, and selected answers.  It doesn't move related questions, it just unrelates them.  It also allows you to set the post category, though I'm not sure if this works as expected.  Once you have migrated a post, **you must** go to the site you migrated to and run all the updates at admin/stats.

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
