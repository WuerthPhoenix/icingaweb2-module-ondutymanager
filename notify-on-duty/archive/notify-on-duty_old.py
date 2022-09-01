#!/usr/bin/python


#TODO import
import sys
import argparse
import pymysql
import re

version = '2.0.0'
program = ''
# delaclaration of variables
#[opt_h,opt_V]
opt_h =''
opt_v =''
team = None
defaultpager = None
message = None
datetime = None
mysql_host = "localhost"
mysql_onduty_database  = "ondutymanager"
#mysql_monarch_database = "monarch"
mysql_director_database = "director"
mysql_username = "root"
mysql_password = "o17k116vHkX9PISBpvvM5fU7KJ6I8GsO"
opt_verbose = None
opt_debug = []
opt_testonly = None
notifycmd = "/etc/nagios/neteye/sms/smssend.sh '\@CONTACT\@' '\@MESSAGE\@'"

# use Getopt::Long; -> python argparse: https://stackoverflow.com/questions/34528985/perls-getoplong-equivalent-in-python-options-with-multiple-values

# GetOptions(
#         "V"   => \$opt_V,        "version"          => \$opt_V,
#         "h"   => \$opt_h,        "help"             => \$opt_h,
# 	"T=s" => \$team,         "team=s"           => \$team,
# 	"P=s" => \$defaultpager, "pager=s"          => \$defaultpager,
# 	"M=s" => \$message,      "message=s"        => \$message,
# 	"d=s" => \$datetime,     "datetime=s"       => \$datetime,
# 	"N=s" => \$notifycmd,    "notifycmd=s"      => \$notifycmd,
#                                  "mysql_host=s"     => \$mysql_host,
#                                  "mysql_database=s" => \$mysql_onduty_database,
#                                  "mysql_username=s" => \$mysql_username,
#                                  "mysql_password=s" => \$mysql_password,
#         "v"   => \$opt_verbose,  "verbose"          => \$opt_verbose,
#         "D"   => \@opt_debug,    "debug"            => \@opt_debug,
#         "test" => \$opt_testonly,
# );

parser = argparse.ArgumentParser()
parser.add_argument('-V','--version',action='version',version=version)
#parser.add_argument('-h','--help')
parser.add_argument('-T','--team')
parser.add_argument('-M','--message')
parser.add_argument('-d','--datetime')
parser.add_argument('-N','--notifycmd')
parser.add_argument('-P','--defaulpager')

parser.add_argument('-mysql_host')
parser.add_argument('-mysql_database')
parser.add_argument('-mysql_username')
parser.add_argument('-mysql_password')


parser.add_argument('-v','--verbose')
parser.add_argument('-D','--debug')
parser.add_argument('-test')

args = parser.parse_args()


#test print input parameters
# print(args.version)
# print(args.team)

def main():
    # populate
    
    
    
    
    
    if(args.version):
        print("%s, Version %s\n",version)
    if(args.team is None):
        print("Please specify the team to send this notification to, aborting!\n")
        sys.exit(1)
    if(args.message is None):
        print("Please specify the message to send in this notification, aborting!\n")
        sys.exit(1)

    team = args.team
    defaultpager = None
    message = args.message
    datetime = args.datetime
    mysql_host = "localhost"
    mysql_onduty_database  = "notify_onduty"
    mysql_monarch_database = "monarch"
    mysql_username = "notify"
    mysql_password = "onduty"
    opt_verbose = None
    opt_debug
    opt_testonly = None
    notifycmd = "/etc/nagios/neteye/sms/smssend.sh '\@CONTACT\@' '\@MESSAGE\@'"

    dbnotify  = "DBI:mysql:database={};host={}".format(args.mysql_onduty_database,args.mysql_host)
    #substitute monarch
    dbdirector = "DBI:mysql:database={};host={}".format(args.mysql_director_database,args.mysql_host)

   

    try:

        #conn with on duty
        #dbn = pymysql.connect(dbnotify,"mysql_username","mysql_password")
        dbn = pymysql.connect(host=args.mysql_host,db=args.mysql_onduty_database,user="mysql_username",passwd="mysql_password")
        #conn with director
        #dbd =  pymysql.connect(dbdirector,"mysql_username","mysql_password")
        qn = dbn.cursor()
        qni = None
        qm = None
        ref = None
        schedules = None

        if(datetime is not None):
            #schedules = qn.execute("SELECT datetime FROM schedules WHERE team = {} AND datetime <= {} ORDER BY datetime DESC LIMIT 1".format(args.team,args.datetime))
            schedules = qn.execute("SELECT start_date FROM schedule WHERE team_id = (SELECT id  FROM team WHERE NAME='{}' )AND start_date <= '{}' ORDER BY start_date DESC LIMIT 1".format(team,datetime))
        else:
            #schedules = qn.execute("SELECT datetime FROM schedules WHERE team = {} AND datetime  <= NOW() ORDER BY datetime DESC LIMIT 1").format(args.team)
            schedules = qn.execute("SELECT start_date FROM schedule WHERE team_id = (SELECT id  FROM team WHERE NAME='{}' )AND start_date <= now() ORDER BY start_date DESC LIMIT 1".format(team))
        if(len(schedules) < 1):
            if(args.pager is not None):
                #send notification send_notification($team, undef, $defaultpager, $notifycmd, $message);
                send_notification(team,None,)
                print("defaultpager is not None")
                if(args.verbose):
                    print("WARNING: Cannot find any entry in schedules table, notification sent to defaultpager ({}})!\n".format(args.defaultpager))
                    sys.exit(0)
            if(args.verbose):
                print("ERROR: Cannot find any entry in schedules table, no notification sent!\n")
            sys.exit(1)
        datetime = schedules

        #
        # Now search all entries with this datetime and this team
        #
        schedules = qn.execute("SELECT * FROM schedules WHERE team = {} AND datetime = {} ORDER BY contact_id").format(team,datetime)

        contact_id = None
        pager = None
        for schedule in schedules:
            if(opt_debug):
                print("%s\n")
            contact_id = schedule['contact_id']
            send_notification(team, contact_id, defaultpager, notifycmd, message)

    except: 
        print("except")
    finally:
        qn.close()
        sys.exit(0)

def send_notification(team, contact_id, defaultpager, sendcmd, message,qn):
    prefix = ""
    sending = False
    #dbdirector = "DBI:mysql:database={};host={}".format(args.mysql_director_database,args.mysql_host)
    
    try:
        #dbd = pymysql.connect(dbdirector,"mysql_username","mysql_password")
        dbd = pymysql.connect(host=args.mysql_host,db=args.mysql_director_database,user="mysql_username",passwd="mysql_password")
        qnd = dbd.cursor()
        if(contact_id is not None):
            pagers = qnd.execute("SELECT pager FROM contacts WHERE {} = '$contact_id'".format(contact_id)) 
            if(pagers is not None):
                sending = True
            elif(defaultpager is not None):
                pager = defaultpager
                prefix = "CONTACT_ID({}) NOT FOUND: ".format(contact_id)
                contact_id = 9999
                sending = True
                if(args.verbose()):
                    print("Notifying to Default Pager ({}), contact_id ({}) not found in DB\n".format(pager,contact_id))
        elif(defaultpager is not None):
            pager = defaultpager
            contact_id = 9999
            sending = True
            if(args.verbose):
                print("Notifying to Default Pager ({})\n".format(pager))
        if(not sending):
            if(args.test):
                print("NOT notifying anything, as contact_id could not be found and no defaultpager\n")
                return
            
            qn.execute("INSERT INTO notify_log (timestamp, team, contact_id, message) VALUES (NOW(), '{}', '0', '{}')".format(team,message))
            return
        if(args.test):
            print("Notifying Contact ({}) on Pager ({})\n".format(contact_id,pager))
            return
        # $sendcmd =~ s/\@CONTACT\@/$pager/g;
	    # $sendcmd =~ s/\@MESSAGE\@/$message/g;
	    # `$sendcmd`;
        qn.execute("INSERT INTO notify_log (timestamp, team, contact_id, message) VALUES (NOW(), '{}', '{}', '{}{}')".format(team,contact_id,prefix,message))
    except (pymysql.Error,pymysql.Warning) as e:
        raise e
    finally:
        qnd.close()

def print_help():
        print("%s, Version %s\n",program, version)
        print("Copyright (c) 2018 Juergen Vigna <juergen.vigna\@wuerth-phoenix.com>\n")
        print("This program is licensed under the terms of the\n")
        print("GNU General Public License\n(check source code for details)\n")
        print("\n")
        print("Script to send notifications over a schedule table in MySQL\n")
        print("\n")
        print_usage()
        print("\n")
        print(" -V (--version)      Plugin version\n")
        print(" -h (--help)         usage help\n")
        print(" --test              Test Mode only, do not send anything\n")
        print(" -T (--team)         Name of team to send this notification (required)\n")
        print(" -P (--defaultpager) Default Pager to send notification to if team or contact_id could not be found\n")
        print(" -M (--message)      Text of message to send for this notification (required)\n")
        print(" -d (--datetime)     MySQL Datetime to send this notification for (default: NOW())\n")
        print(" -N (--notifycmd)    The command to use for this notification\n")
        print("                     Use this 2 macros inside the command:\n")
        print("                     \@CONTACT\@: Is substituted by the actual contact(s)\n")
        print("                     \@MESSAGE\@: Is substituted by your message\n")
        print("                     default: $notifycmd\n")
        print("\n")
        print(" MySQL Integration:\n")
        print(" --mysql_onduty_database mysql database name (default $mysql_onduty_database)\n")
        print(" --mysql_username        mysql database username (default $mysql_username)\n")
        print(" --mysql_password        mysql database password (default ******)\n")
        print(" --mysql_host            mysql database hostname/ip (default $mysql_host)\n")
        print("\n")
def print_usage():
    print("Usage: \n")
    print("  $PROGNAME -T|--team <teamname> -M <message> [-P <defaultpager>] [-d <datetime>] [-N <nofifycmd>] [--mysql_onduty_database <databasename>] [--mysql_username <username>] [--mysql_password <password>] [--mysql_host <host/ip>]\n")
    print("  $PROGNAME [-h | --help]\n")
    print("  $PROGNAME [-V | --version]\n")

if __name__ == "__main__":
    main()
