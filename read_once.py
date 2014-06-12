#!/usr/bin/python

import time
import MySQLdb
from sht1x.Sht1x import Sht1x as SHT1x
import serial

dataPin = 16
clkPin = 15

sht1x = SHT1x(dataPin, clkPin, SHT1x.GPIO_BOARD)

db = MySQLdb.connect(host = "localhost", user = "pi", passwd = "rasbian", db = "pi")
cur = db.cursor()
cur.execute("SET SESSION time_zone = '+3:00'")
db.commit()

ser = serial.Serial('/dev/ttyAMA0', 9600)

def readPressure():
	data = ser.readline()
	try:
		volt1 = float(data[:-2])
		if not volt1:
			return False
		value = round(((volt1/5.0)+0.095)/0.009*10,2)
		return value
	except Exception as e:
		print("Exception caught!",e)
		return False
		

def readValues():

	try:
		cur.execute("SELECT COUNT(*) as numero FROM lukemat WHERE 1")
		line = cur.fetchone()[0]
		num = int(line)
		if num  == 0:
			num = False
			newtemp = 0
		else:
			cur.execute("SELECT temp FROM lukemat ORDER BY timestamp DESC LIMIT 1")
			newtemp = cur.fetchone()[0]
	
			print("Latest temperature from database: "+str(newtemp))

		temp = round(sht1x.read_temperature_C(),1)
		humidity = round(sht1x.read_humidity(),1)
		pressure = readPressure()

		if (humidity > 0 and humidity < 100) and (temp < 100) and (abs(newtemp - temp) < 20 or num == False) and pressure > 900:
			cur.execute("INSERT INTO lukemat (timestamp,temp,humidity,light,pressure) VALUES(now(),"+str(temp)+","+str(humidity)+",0,"+str(pressure)+")")
			print("Temperature: "+str(temp)+" C","Humidity: "+str(humidity)+"%",str(pressure)+" hPa")	
			db.commit()
		else:
			print("Invalid humidity,pressure or temperature caught: "+str(humidity)+"%, "+str(temp)+"C, "+str(pressure)+" hPa")
			time.sleep(2)
			readValues()
	except Exception as e:
		print("Exception caught!",e)
		time.sleep(4)
		readValues()
		
readValues()
