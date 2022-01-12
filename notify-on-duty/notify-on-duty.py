#!/usr/bin/python3

import datetime
import argparse
import sys
import mysql.connector 
import os
from contextlib import closing
from  constants import *

version = '2.0.0'
parser = argparse.ArgumentParser()
# parser.add_argument("ls")
parser.add_argument('-V', '--version', action='version', version=version)
# parser.add_argument('-h','--help')
parser.add_argument('-T', '--team')
parser.add_argument('-M', '--message')
parser.add_argument('-d', '--datetime')
parser.add_argument('-N', '--notifycmd')
parser.add_argument('-P', '--defaultpager')

parser.add_argument('-mysql_host')
parser.add_argument('-mysql_database')
parser.add_argument('-mysql_username')
parser.add_argument('-mysql_password')


parser.add_argument('-v', '--verbose')
parser.add_argument('-D', '--debug')
parser.add_argument('-test',action='store_true')

args = parser.parse_args()


team = args.team
message = args.message

# db connection
if args.mysql_host is not None:
    host = args.mysql_host
else:
    host = MYSQL_HOST
if args.mysql_database is not None:  
    db = args.mysql_database
else:
    db = MYSQL_DATABASE
if args.mysql_username is not None:    
    user = args.mysql_username
else:
     user = MYSQL_USERNAME    
if args.mysql_password is not None:   
    passwd = args.mysql_password
else:
    passwd = MYSQL_PASSWORD
    
defaultpager = args.defaultpager

NOTIFYCMD = "/usr/bin/smssend"
#/usr/bin/smssend.sh

def conversion(sec):
    sec_value = sec % (24 * 3600)
    hour_value = sec_value // 3600
    sec_value %= 3600
    min = sec_value // 60
    sec_value %= 60
    return hour_value,min
   
def eqdate(date1,date2):
    return date1.year==date2.year and date1.month==date2.month and date1.day == date2.day

def main():
    # if(args.version):
    #         print("%s, Version %s\n",version)
    if(args.team is None):
        print("Please specify the team to send this notification to, aborting!\n")
        sys.exit(1)
    if(args.message is None):
        print("Please specify the message to send in this notification, aborting!\n")
        sys.exit(1)
    datetime_str = args.datetime

    try:
        schedules = None
        if(datetime_str is not None):
            schedules = execute_sql_objects(
                "SELECT * FROM schedule WHERE team_id = (SELECT id  FROM team WHERE NAME='{}' )AND start_date = '{}' order by start_time".format(team, datetime_str))
        else:
           schedules = execute_sql_objects("SELECT * FROM schedule WHERE team_id = (SELECT id  FROM team WHERE NAME='{}' )AND start_date = now() order by start_time".format(team))

        print(schedules)
        if(len(schedules) < 1):
            if(args.defaultpager is not None):
                # send notification 
                send_notification(team, None, args.pager, NOTIFYCMD, message)
                print("defaultpager is not None")
                if(args.verbose):
                    print("WARNING: Cannot find any entry in schedules table, notification sent to defaultpager ({}})!\n".format(
                        args.defaultpager))
                    return
            if(args.verbose):
                print(
                    "ERROR: Cannot find any entry in schedules table, no notification sent!\n")
                return
        
        actual_date = datetime.datetime.now()
        #actual_date = datetime.datetime(2021,11,26,8,1 ,1)
        condition = False
        items = dict()
        for count,schedule in enumerate(schedules):
            condition =  eqdate(schedule['start_date'],actual_date) and (conversion(schedule['start_time'].seconds)[0] <= actual_date.hour  and conversion(schedule['start_time'].seconds)[1] <= actual_date.minute)
            if condition:
                items[count] = schedule
                #break
                   
        
        #
        # Now search all entries with this datetime and this team
        #
        #schedules =execute_sql_objects("SELECT * FROM schedule WHERE  team_id = (SELECT id  FROM team WHERE NAME='{}' ) AND start_date = '{}' ORDER BY team_id".format(team,datetime))
        schedule = schedules[len(items)-1]
        
        contact_id = None
        pager = None
        # for schedule in schedules:
        #     #if(opt_debug):
        #     if(args.test):
        #         print("%s\n")
        #     send_notification(team, schedule, defaultpager, NOTIFYCMD, message)
        # print("*** END ***")
        if(args.test):
            print("%s\n")
        send_notification(team, schedule, defaultpager, NOTIFYCMD, message)
        print("*** END ***")
    except Exception as err:
        print(err)


def send_notification(team, schedule, defaultpager, sendcmd, message):
    print("SEND NOTIFICATION")
    prefix = ""
    sending = False
    pager = ''
    # if user_phone_number not none => sending = True
    if schedule['user_phone_number'] is not None:
        sending = True
        pager = schedule['user_phone_number']
    elif defaultpager is not None:
        pager = defaultpager
        user = '9999'
        sending = True
        prefix  = "Phone number NOT FOUND:"
        if args.verbose:
            print("Notifying to Default Pager {}".format(pager)) 
    if(not sending):
        if(args.test):
            print("Notifying Contact ({}) on Pager ({})\n".format(schedule['user_id'],pager))
            return
        try:
            execute_sql_insert_upd("INSERT INTO notify_log (timestamp, team, contact_id, message) VALUES (NOW(), '{}', '0', '{}')".format(team,message)) 
        except Exception as e:
            print(e)      
        return
    if args.test:
        print("Notifying Contact ({}) on Pager ({})\n".format(schedule['user_id'],pager))    
        return
    sendcmd = NOTIFYCMD + " " + pager + " " + message
    os.system(sendcmd)
    try:
        execute_sql_insert_upd("INSERT INTO notify_log (timestamp, team, contact_id, message) VALUES (NOW(), '{}', '{}', '{}{}')".format(team,schedule['user_id'],prefix,message))
    except  Exception as e:
        print(e)   

           
    
    
    
def execute_sql(sql,json_str=False,test=False):
    """[summary]

    Args:
        sql ([string]): [sql query]

    Returns:
        [array]: [list of objects from db]
    """
    if test == True:
        return sql;
    try:
        with closing(mysql.connector.connect(host=host, db=db, user=user, passwd=passwd)) as my_conn:
            with closing(my_conn.cursor()) as my_curs:
                my_curs.execute(sql)
                return my_curs.fetchall()
    except mysql.connector.Error as err:
        print(err)   

def execute_sql_insert_upd(sql,json_str=False,test=False):
    """[summary]

    Args:
        sql ([string]): [sql query]

    Returns:
        [array]: [list of objects from db]
    """
    if test == True:
        return sql;
    try:
        with closing(mysql.connector.connect(host=host, db=db, user=user, passwd=passwd)) as my_conn:
            with closing(my_conn.cursor()) as my_curs:
                my_curs.execute(sql)
                my_conn.commit()
                return my_curs.lastrowid 
    except mysql.connector.Error as err:
        raise err

def execute_sql_objects(sql):
    with closing(mysql.connector.connect(host=host, db=db, user=user, passwd=passwd)) as my_conn:
            with closing(my_conn.cursor()) as my_curs:
                my_curs.execute(sql)
                rows = [x for x in my_curs]
                cols = [x[0] for x in my_curs.description]
                objects = []
                for row in rows:
                    object = {}
                    for prop, val in zip(cols, row):
                        object[prop] = val
                    objects.append(object)
                return objects;    

if __name__ == "__main__":
    main()
