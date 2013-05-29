import simplejson as json
import pprint
import MySQLdb
import csv
from collections import OrderedDict
import datetime
import random
from random import randint
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
'''
- get all dates
- iterate through and collect all nodes for that date for which venue != gallery
- make a node from each online
- if model = time then link in time, if model = space then link in space
'''
def makeNodes(date, model):
    global pp
    nodes = []
    idx = 0
    groups = {"Africa" : 1, "Antarctica" : 2, "Asia" : 3, "Europe" : 4, "Australia" : 5, "North America" : 6, "South America" : 7}
    try:
        
        conn = MySQLdb.connect(host = "localhost",user = "webapp",passwd = "1l0ves1x",db = "thesixthroom")
        cursor = conn.cursor()
        sql = 'SELECT * FROM individual_visitors WHERE (continent != \'\' and visit_date LIKE \'' + date.strftime('%Y-%m-%d') + '%\') or (venue = \'GUESTBOOK\') ORDER BY visit_date'
        
        #get all individual dates
        cursor.execute(sql)
        visitors = cursor.fetchall()

        for visitor in visitors:
            name = visitor[1]
            visit_date = visitor[2].strftime('%m/%d/%Y')
            city = visitor[3]
            state = visitor[4]
            country = visitor[6]
            continent = visitor[8]
            venue = visitor[9].lower()
            name = name + " from " + city + ", " + country +  ", " + visit_date

            nodes.append( dict({'name': name, 'group': groups[continent], 'date': visit_date, 'idx': idx, 'continent':continent, 'is_guestbook_signer': 'true' if venue == 'guestbook' else 'false', 'venue':venue}) )
            idx +=1 

    except MySQLdb.Error, e:
          
            print "[ERROR] %d: %s\n" % (e.args[0], e.args[1])
            sys.exit(1)

    finally:
        
        if conn:
            conn.close()
    return nodes
def makeLinks(date, model, nodes):
    links = []
    idx = 0
    for node in nodes:
        source_node_id = node["idx"]
        #make links based on time of entry into the network
        if (model == "time"):
            #simplest: make links based on one forward, one back
            if (idx > 0):
                links.append(dict({"source":source_node_id,"target":source_node_id-1,"value":2}))
            if (idx + 1 < len(nodes)):
                links.append(dict({"source":source_node_id,"target":source_node_id+1,"value":2}))

            #make a weaker link based on similar venue    
            seeker = idx + 1
            venue = node["venue"]
            while (seeker < len(nodes)):
                if (nodes[seeker]["venue"] == venue):
                    links.append(dict({"source":source_node_id,"target":seeker,"value":1}))
                    break
                seeker +=1
            seeker = idx
            
            
            while (seeker >= 0):
                if (nodes[seeker]["venue"] == venue):
                    links.append(dict({"source":source_node_id,"target":seeker,"value":1}))
                    break
                seeker -=1

            
        # make linkes based on continent of origin
        # only 2 links at the moment
        else:
            seeker = idx + 1
            continent = node["continent"]
            found = 0
            while (seeker < len(nodes) and found < 1):
                if (nodes[seeker]["continent"] == continent):
                    links.append(dict({"source":source_node_id,"target":seeker,"value":2}))
                    found+=1
                seeker +=1
            seeker = idx
            found = 0
            
            while (seeker >= 0 and found < 1):
                if (nodes[seeker]["continent"] == continent):
                    links.append(dict({"source":source_node_id,"target":seeker,"value":2}))
                    found +=1
                seeker -=1

            #try to make between 1-4 random spatial links
            triesPossible = 30
            triesActual = 0
            randomNode = random.choice(nodes)
            while ((node == randomNode or node["group"] != randomNode["group"]) and triesActual < triesPossible):
                randomNode = random.choice(nodes)
                triesActual +=1
            links.append(dict({"source":source_node_id,"target":randomNode["idx"],"value":1}))
           
        idx +=1
    return links

def makeNetworkData(model, file_prefix):
    global pp
    try:
        
        conn = MySQLdb.connect(host = "localhost",user = "webapp",passwd = "1l0ves1x",db = "thesixthroom")
        cursor = conn.cursor()

        #get all individual dates
        cursor.execute('SELECT DISTINCT (date(visit_date)) FROM individual_visitors ORDER BY visit_date')

        alldates = [row[0] for row in cursor.fetchall()]

    except MySQLdb.Error, e:
          
            print "[ERROR] %d: %s\n" % (e.args[0], e.args[1])
            sys.exit(1)

    finally:
        
        if conn:
            conn.close()

    for date in alldates:
        nodes = makeNodes(date, model)
        links = makeLinks(date, model, nodes)
        #pp.pprint(links)
        data = {"nodes":nodes, "links":links}
        json.dump(data, open(file_prefix + str(date).replace("-","_") +'.json', 'w'),indent=1)

#def makeWorldNetworkData(filename):

################################################
# MAIN
#################################################
pp = pprint.PrettyPrinter(indent=4)
makeStreamgraphData('time',
                    '/home/ubuntu/thesixthroom/The-Sixth-Room/data/streamgraph_time.csv',
                    'SELECT COUNT(date(visit_date)) AS visitors, visit_date, venue FROM individual_visitors GROUP BY date(visit_date), venue ORDER BY visit_date',
                    ['guestbook','online','museum'])
makeStreamgraphData('space',
                    '/home/ubuntu/thesixthroom/The-Sixth-Room/data/streamgraph_space.csv',
                    'SELECT COUNT(date(visit_date)) AS visitors, visit_date, continent FROM individual_visitors WHERE continent != \'\' GROUP BY date(visit_date), continent ORDER BY visit_date',
                    ['Europe','North America','South America','Australia','Asia','Antarctica','Africa'])
makeNetworkData('time', '/home/ubuntu/thesixthroom/The-Sixth-Room/data/networkdata_time_')
makeNetworkData('space', '/home/ubuntu/thesixthroom/The-Sixth-Room/data/networkdata_space_')
print "all done maestro"
