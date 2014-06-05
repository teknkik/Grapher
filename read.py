#!/usr/bin/python

import time
import MySQLdb
from sht1x.Sht1x import Sht1x as SHT1x
dataPin = 16
clkPin = 15

sht1x = SHT1x(dataPin, clkPin, SHT1x.GPIO_BOARD)

db = MySQLdb.connect(host = "localhost", user = "pi", passwd = "rasbian", db = "pi")
cur = db.cursor()
cur.execute("SET SESSION time_zone = '+3:00'")
db.commit()

while True:
	temp = round(sht1x.read_temperature_C(),1)
	humidity = round(sht1x.read_humidity(),1)

	cur.execute("INSERT INTO lukemat (timestamp,temp,humidity,light,pressure) VALUES(now(),"+str(temp)+","+str(humidity)+",0,0)")
	print("Temperature: "+str(temp)+" C","Humidity: "+str(humidity)+"%")	
	db.commit()
	time.sleep(150)

