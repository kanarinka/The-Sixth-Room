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
'''

def makeStreamgraphData(model, filename, sql, venueList):

    with open(filename, 'wb') as csvfile:
        mywriter = csv.writer(csvfile, delimiter=',', quotechar='|', quoting=csv.QUOTE_MINIMAL)
        mywriter.writerow(['index', 'date', 'venue', 'num_visitors'])
        
        try:
            conn = MySQLdb.connect(host = "localhost",user = "webapp",passwd = "1l0ves1x",db = "thesixthroom")
            cursor = conn.cursor()

            # Get count from individual visitors by venue & by day
            # Put in data structure indexed by 
            
            cursor.execute(sql)
            visitor_counts = cursor.fetchall()
            index=0
            previous_date=None
            venues_not_covered = list(venueList)
            for visitor_count in visitor_counts:
                num_visitors = visitor_count[0]
                visit_date = visitor_count[1]
                venue = visitor_count[2].lower() if model == 'time' else visitor_count[2]

                visit_date = visit_date.strftime('%m/%d/%Y')
                if (previous_date != None and visit_date != previous_date):
                    if (len(venues_not_covered) != 0):
                        for emptyVenue in venues_not_covered:
                            mywriter.writerow([index, previous_date, emptyVenue, 0])
                    index+=1
                    venues_not_covered = list(venueList)
                
                print venue
                print venueList
                print venues_not_covered
                venues_not_covered.remove(venue)
                        
                mywriter.writerow([index, visit_date, venue, num_visitors])
                
                previous_date = visit_date
            
            if (len(venues_not_covered) != 0):
                for venue in venues_not_covered:
                    mywriter.writerow([index, previous_date, venue, 0])
            cursor.close()
            
        except MySQLdb.Error, e:
          
            print "[ERROR] %d: %s\n" % (e.args[0], e.args[1])
            sys.exit(1)

        finally:
            
            if conn:
                conn.close()
################################################
# MAIN
#################################################

makeStreamgraphData('time',
                    '/home/ubuntu/thesixthroom/The-Sixth-Room/data/streamgraph_time.csv',
                    'SELECT COUNT(*) AS visitors, visit_date, venue FROM individual_visitors GROUP BY visit_date, venue ORDER BY visit_date',
                    ['guestbook','online','museum'])
makeStreamgraphData('space',
                    '/home/ubuntu/thesixthroom/The-Sixth-Room/data/streamgraph_space.csv',
                    'SELECT COUNT(*) AS visitors, visit_date, continent FROM individual_visitors WHERE continent !=  \'\' GROUP BY visit_date, continent ORDER BY visit_date',
                    ['Europe','North America','South America','Australia','Asia','Antarctica','Africa'])