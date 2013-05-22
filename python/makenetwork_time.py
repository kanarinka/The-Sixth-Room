import simplejson as json
from pprint import pprint
from random import randint 
import datetime
from dateutil.relativedelta import relativedelta

DAYS = 1
GROUPS_TO_CONTINENTS = ["Antarctica","Australia","Asia","Africa","South America","Europe","North America"]

'''
network has many subcomponents organized by day
subcomponents not interconnected 


'''
def insertNewDay(data, currentdate):
    nodes_per_day = randint(200,1000)
    link_density = 2
    first_source_node = len(data["nodes"])
    last_source_node = first_source_node + nodes_per_day - 1
    current_node = first_source_node
    currentdatestr = str(currentdate.month) + "/" + str(currentdate.day) + "/" + str(currentdate.year)
    
    for i in range(nodes_per_day):
        node = getRandomNode(currentdatestr, current_node)
        data["nodes"].append(node)
        
        if (nodes_per_day > 1):
            for j in range(randint(1,link_density)):
                data["links"].append(getLinkInRange(current_node, first_source_node, last_source_node))
                print("appended a link")
        
        current_node+=1
    print "appended " + str(nodes_per_day) + " nodes"


def getLinkInRange(current_node, first_source_node, last_source_node):

    target_node = randint(first_source_node, last_source_node)
    

    while (target_node == current_node):
        target_node = randint(first_source_node, last_source_node)
       

    return dict({"source":current_node,"target":target_node,"value":1})

def getRandomNode(currentdatestr, idx):
    group = randint(1,7)
    continent = GROUPS_TO_CONTINENTS[group - 1]
    is_guestbook_signer = randint(0,9) == 1
    if (is_guestbook_signer):
        venue = 'guestbook'
    elif (randint(0,2) == 1):
        venue = 'museum'
    else: 
        venue = 'online'
    name = 'Ulysses Percival Starbuck ' + str(randint(0, 1000)) if is_guestbook_signer else "Internet visitor"
    return dict({'name': name, 'group': group, 'date': currentdatestr, 'idx': idx, 'continent':continent, 'is_guestbook_signer':is_guestbook_signer, 'venue':venue})

def getRandomLink(num_nodes):
    return dict({"source":randint(0,num_nodes),"target":randint(0,num_nodes),"value":randint(1,15)})

data = dict({"nodes":[],"links":[] })#json.load(data_file)
today = datetime.date.today()
currentdate = today 
for i in range(DAYS):   
    insertNewDay(data, currentdate)
    currentdate = currentdate+relativedelta(days=+1)

json.dump(data, open('networkdata_time_'+ str(DAYS) +'.json', 'w'),indent=1)
