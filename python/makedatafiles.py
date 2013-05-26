import simplejson as json
from pprint import pprint
import MySQLdb
import csv
from collections import OrderedDict
import datetime

'''
get data from individual visitors, from gallery visitors from all time
generate time & spaces streamgraphs.csv - Streamgraphs are over the whole time period, so TWO files total
generate time & space networks.json - Networks are by day, so 2 files/day over the whole time period
save to /data dir
cap online visitors at 1000 for each day but make sure to get correct geographic ratios across the whole data set per day
make sure all guestbook people not capped


make three dictionaries, one for each venue
get data from indiv visitors
get data from gallery visitors

'''

online_visitor_count = []
museum_visitor_count = []
guestbook_visitor_count = []

try:
    conn = MySQLdb.connect(host = "localhost",user = "webapp",passwd = "1l0ves1x",db = "thesixthroom")
    cursor = conn.cursor()

    # Get count from individual visitors by venue & by day
    # Put in data structure indexed by 
    sql = 'SELECT COUNT(*) AS visitors, visit_date, venue FROM individual_visitors GROUP BY visit_date, venue ORDER BY visit_date'
    cursor.execute(sql)
    visitor_counts = cursor.fetchall()
    for visitor_count in visitor_counts:
        num_visitors = visitor_count[0]
        visit_date = visitor_count[1]
        venue = visitor_count[2].lower()

        visit_date = datetime.datetime.strptime(visit_date, '%Y-%m-%d %H:%M:%S')
        
        if (venue == 'online'):
            online_visitor_count.append({"num_visitors" : num_visitors, "visit_date" : visit_date, "venue": venue})
        else
            guestbook_visitor_count.append({"num_visitors" : num_visitors, "visit_date" : visit_date, "venue": venue})
        
       
    cursor.close()
    
except MySQLdb.Error, e:
  
    print "[ERROR] %d: %s\n" % (e.args[0], e.args[1])
    sys.exit(1)

finally:
    
    if conn:
        conn.close()

with open('/home/ubuntu/thesixthroom/The-Sixth-Room/data/streamgraph_time_FROM_PYTHON.csv', 'wb') as csvfile:
    mywriter = csv.writer(csvfile, delimiter=',', quotechar='|', quoting=csv.QUOTE_MINIMAL)
    mywriter.writerow(['index', 'date', 'venue', 'num_visitors'])
    #mywriter.writerow([1, visit_date, venue, num_visitors])