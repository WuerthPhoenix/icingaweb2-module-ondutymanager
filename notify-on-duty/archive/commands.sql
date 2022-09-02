--
-- Create notify-on-duty commands in monarch DB
--

INSERT into monarch.commands (`name` , `type` , `data` , `comment`) values ('notify-on-duty', 'notify', '<?xml version="1.0" ?>\n<data>\n  <prop name="command_line"><![CDATA[/opt/neteye/bin/notify-on-duty.pl -T ''$CONTACTNAME$'' -P ''$CONTACTPAGER$'' -M ''NetEye - $NOTIFICATIONTYPE$ - $SERVICEDESC$ - $HOSTNAME$ - $HOSTADDRESS$ - $SERVICESTATE$ - $SHORTDATETIME$ - $SERVICEOUTPUT$'']]>\n  </prop>\n</data>', NULL);
INSERT into monarch.commands (`name` , `type` , `data` , `comment`) values ('host-notify-on-duty', 'notify', '<?xml version="1.0" ?>\n<data>\n  <prop name="command_line"><![CDATA[/opt/neteye/bin/notify-on-duty.pl -T ''$CONTACTNAME$'' -P ''$CONTACTPAGER$'' -M "Host ''$HOSTNAME$'' ($HOSTADDRESS$) is $HOSTSTATE$ - Info: $HOSTOUTPUT$ - Time: $SHORTDATETIME$"]]>\n  </prop>\n</data>', NULL);

