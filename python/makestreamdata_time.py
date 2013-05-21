import csv
import datetime
from random import randint
from dateutil.relativedelta import relativedelta

with open('/Users/kanarinka/Desktop/catherine/mit/Spring2013/networkscience/thesixthroom/test/streamdata90.csv', 'wb') as csvfile:
    mywriter = csv.writer(csvfile, delimiter=',', quotechar='|', quoting=csv.QUOTE_MINIMAL)

    today = datetime.date.today()
    currentdate = today
    mywriter.writerow(['index', 'date', 'venue', 'num_visitors'])
    for i in range(90):
        currentdatestr = str(currentdate.month) + "/" + str(currentdate.day) + "/" + str(currentdate.year)
        mywriter.writerow([i, currentdatestr, 'online', randint(1000, 2000)])
        mywriter.writerow([i, currentdatestr, 'museum', randint(500, 1000)])
        mywriter.writerow([i, currentdatestr, 'guestbook', randint(2, 400)])
        currentdate = currentdate+relativedelta(days=+1)
