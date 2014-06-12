#!/usr/bin/python

import time
import os
import MySQLdb

from sht1x.Sht1x import Sht1x as SHT1x
dataPin = 16
clkPin = 15

sht1x = SHT1x(dataPin, clkPin, SHT1x.GPIO_BOARD)
while True:
	try:	
		temp = round(sht1x.read_temperature_C(),1)
		humidity = round(sht1x.read_humidity(),1)
		print(str(temp)+","+str(humidity))	

		if os.path.exists("temps.txt"):
			f = file("temps.txt","w")
		else:	
			f = file("temps.txt","w")
		f.write(str(temp)+","+str(humidity));
		f.close();
		time.sleep(1)
	except Exception as e:
		print(e)
