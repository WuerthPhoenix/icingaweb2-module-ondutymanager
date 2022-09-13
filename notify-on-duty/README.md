Notification logic for on-duty-manager

# Setup

Install the related mysql tables
```
cat notify-on-duty.sql | mysql ondutymanager
```

Add mysql user to for database "ondutymanager". Required permissions SELECT,INSERT
```
constants.py
GRANT SELECT, INSERT, UPDATE ON *.* TO 'notify-onduty-rw'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
```

Install the notification script and the constans file within script folder of icinga2-master:
```
# cp notify-on-duty/notify-on-duty.py notify-on-duty/constants.py /neteye/shared/icinga2/conf/icinga2/scripts/
```

## MySQL module install

This procedure has been tested on neteye 4.25 on RHEL 8

- Download and Install the Python mysql module
- Download portal from mysql/oracle: 
https://dev.mysql.com/downloads/connector/python/
```
yum install mysql-connector-python3-8.0.27-1.el7.x86_64.rpm
```

Verify the python version installed by the module:
```
[root@neteye4rhel8n1 ~]# yum install mysql-connector-python3-8.0.30-1.el8.x86_64.rpm
Updating Subscription Management repositories.
Last metadata expiration check: 2:37:31 ago on Fri 09 Sep 2022 02:46:53 PM CEST.
Dependencies resolved.
==========================================================================================================================================================================================================
 Package                                         Architecture                 Version                                                        Repository                                              Size
==========================================================================================================================================================================================================
Installing:
 mysql-connector-python3                         x86_64                       8.0.30-1.el8                                                   @commandline                                           2.6 M
Installing dependencies:
 python38                                        x86_64                       3.8.12-1.module+el8.6.0+12642+c3710b74                         rhel-8-for-x86_64-appstream-rpms                        80 k
 python38-libs                                   x86_64                       3.8.12-1.module+el8.6.0+12642+c3710b74                         rhel-8-for-x86_64-appstream-rpms                       8.3 M
 python38-pip-wheel                              noarch                       19.3.1-5.module+el8.6.0+13002+70cfc74a                         rhel-8-for-x86_64-appstream-rpms                       1.0 M
 python38-setuptools-wheel                       noarch                       41.6.0-5.module+el8.5.0+12205+a865257a                         rhel-8-for-x86_64-appstream-rpms                       304 k
Installing weak dependencies:
 python38-pip                                    noarch                       19.3.1-5.module+el8.6.0+13002+70cfc74a                         rhel-8-for-x86_64-appstream-rpms                       1.8 M
 python38-setuptools                             noarch                       41.6.0-5.module+el8.5.0+12205+a865257a                         rhel-8-for-x86_64-appstream-rpms                       668 k
Enabling module streams:
 python38                                                                     3.8

Transaction Summary
==========================================================================================================================================================================================================
Install  7 Packages
```

Here we obtain dependeny for python 3.8
Make sure to use the correct python version or create a python virtual environment




# Notification confiuguration

Make sure SMS-tools are intalled (verify path: /usr/bin/smssend)

Test run for notification script:
- -T define Team 

Syntax and run sample:

```
-test: Dry run, test mode

 sudo -u icinga '/neteye/shared/icinga2/conf/icinga2/scripts/notify-on-duty.py' '-M' 'NetEye4 - Host hostname (hostname.mydomain.lan) is DOWN - Info: test - Time: 2022-01-19 10:08:11 +0100' '-P' '+393356255945' '-T' 'TEAM 1' '-test'
```
