import simplejson as json
from pprint import pprint
from random import randint 
import random
import datetime
from dateutil.relativedelta import relativedelta

DAYS = 10
GROUPS_TO_CONTINENTS = ["Antarctica","Australia","Asia","Africa","South America","Europe","North America"]
'''
network has many subcomponents organized by day
subcomponents not interconnected 


'''
def insertNewDay(data, currentdate):
    nodes_per_day = randint(2,200)
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
                data["links"].append(getLinkInRange(node, current_node, data["nodes"]))
                print("appended a link")
        
        current_node+=1
    print "appended " + str(nodes_per_day) + " nodes"


def getLinkInRange(current_node, idx, nodeData):
    triesPossible = 30
    triesActual = 0
    randomNode = random.choice(nodeData)
    while ((current_node == randomNode or current_node["group"] != randomNode["group"]) and triesActual < triesPossible):
        randomNode = random.choice(nodeData)
        triesActual +=1
        
    print "node group is " + str(randomNode["group"]) + " and new node group is " + str(current_node["group"])
    return dict({"source":idx,"target":randomNode["idx"],"value":1})

def getRandomNode(currentdatestr, idx):
    group = randint(1,7)
    continent = GROUPS_TO_CONTINENTS[group - 1]
    is_guestbook_signer = randint(0,9) == 1
    name = 'Ulysses Percival Starbuck ' + str(randint(0, 1000)) if is_guestbook_signer else "Internet visitor, from London/Paris/Perth/etc"
    return dict({'name': name, 'group': group, 'date': currentdatestr, 'idx': idx, 'continent':continent, 'is_guestbook_signer':is_guestbook_signer})

def getRandomLink(num_nodes):
    return dict({"source":randint(0,num_nodes),"target":randint(0,num_nodes),"value":randint(1,15)})

data = dict({"nodes":[],"links":[] })#json.load(data_file)
today = datetime.date.today()
currentdate = today 
for i in range(DAYS):   
    insertNewDay(data, currentdate)
    currentdate = currentdate+relativedelta(days=+1)

json.dump(data, open('networkdata_space_'+ str(DAYS) +'.json', 'w'),indent=1)
