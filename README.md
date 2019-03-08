# IANSEO Archery Tournament Results Management

This is an unofficial repository for the ianseo software.  This software
is used for managing archery tournaments.  The official site for this
software is http://www.ianseo.net/index.php.  

##### NOTE - Licenses for each project are in their folders.
* IANSEO (src) - Licensed under Lesser GPLv3.
* LiveResultsPublisher (utilities/LiveResultsPublisher) - Licensed under
  MIT

At the time of the creation of this repository 10/17/2015 we were unable
to locate an repository from which to fork so this repo was created.

This repo has been updated with the 01/01/2017 IANSEO source.

##### Note by ecelis

My fork is a fork from brian-nelson/ianseo but mixed with the official
release, since there seem to be missing some files in Brian's
repository. So it will be a mess until I figure out why and somehow get
in sync with the original sources.

## Docker

Docker setup is out of scope.

        docker run -d --name ianseodb \
            -e MYSQL_ROOT_PASSWORD=verysecret \
            -e MYSQL_DATABASE=ianseo \
            -e MYSQL_USER=ianseo \
            -e MYSQL_PASSWORD=ianseo \
            -v /srv/ianseo:/var/lib/mysql \
            -p 3306:3306 \
            mariadb:5.5

        docker run -d --name ianseo \
            -d --name ianseo --link inaseodb:mysql \
            --env-file=.env arqueria/ianseo
