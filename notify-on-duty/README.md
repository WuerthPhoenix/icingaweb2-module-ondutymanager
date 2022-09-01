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

Install the Python mysql module
```
https://dev.mysql.com/downloads/connector/python/
mysql-connector-python3-8.0.27-1.el7.x86_64.rpm
```


# Notification confiuguration

Syntax and run sample:

```
-test: Dry run, test mode

 sudo -u icinga '/neteye/shared/icinga2/conf/icinga2/scripts/notify-on-duty.py' '-M' 'NetEye4 - Host hostname (hostname.mydomain.lan) is DOWN - Info: test - Time: 2022-01-19 10:08:11 +0100' '-P' '+393356255945' '-T' 'TEAM1' '-test'
```
