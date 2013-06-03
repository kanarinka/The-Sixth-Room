import csv
import datetime
import os
from random import randint
from dateutil.relativedelta import relativedelta

DAYS = 90

resultFile = os.getcwd() + '/streamdata_space_'+ str(DAYS) +'.csv'
print resultFile
with open(resultFile, 'wb') as csvfile:
    mywriter = csv.writer(csvfile, delimiter=',', quotechar='|', quoting=csv.QUOTE_MINIMAL)

    today = datetime.date.today()
    currentdate = today
    mywriter.writerow(['index', 'date', 'venue', 'num_visitors'])
    for i in range(DAYS):
        currentdatestr = str(currentdate.month) + "/" + str(currentdate.day) + "/" + str(currentdate.year)
        mywriter.writerow([i, currentdatestr, 'North America', randint(1000, 2000)])
        mywriter.writerow([i, currentdatestr, 'Europe', randint(1500, 2000)])
        mywriter.writerow([i, currentdatestr, 'South America', randint(500, 1000)])
        mywriter.writerow([i, currentdatestr, 'Africa', randint(200, 500)])
        mywriter.writerow([i, currentdatestr, 'Asia', randint(500, 1000)])
        mywriter.writerow([i, currentdatestr, 'Australia', randint(500, 1000)])
        mywriter.writerow([i, currentdatestr, 'Antarctica', randint(0, 10)])
        currentdate = currentdate+relativedelta(days=+1)
