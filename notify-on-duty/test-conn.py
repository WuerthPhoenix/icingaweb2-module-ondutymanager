import pymysql


dbn = pymysql.connect(host='mariadb.neteyelocal',db='ondutymanager',user="root",passwd="o17k116vHkX9PISBpvvM5fU7KJ6I8GsO")
qn = dbn.cursor()
team ='Team 1'
datetime='2021-11-11'
#sql = "SELECT start_date FROM schedule WHERE team_id = (SELECT id  FROM team WHERE NAME='{}' )AND start_date <= '{}' ORDER BY start_date DESC LIMIT 1".format(team,datetime)
sql="SELECT start_date FROM schedule WHERE team_id = (SELECT id  FROM team WHERE NAME='{}' )AND start_date <= now() ORDER BY start_date DESC LIMIT 1".format(team)
qn.execute(sql)
result  = qn.fetchall()
print(result)

