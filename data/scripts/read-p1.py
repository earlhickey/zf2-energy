#!/usr/bin/python
# SMARTMETER P1 reader
# @author pG
version = "1.1"

# needed libraries
import sys
import serial
import time
import datetime
import re
import sqlite3

# timeout when script takes to long
timeout = time.time() + 30

# connect with the p1 port
ser = serial.Serial()
ser.baudrate = 9600
ser.bytesize=serial.SEVENBITS
ser.parity=serial.PARITY_NONE
ser.stopbits=serial.STOPBITS_ONE
ser.xonxoff=1
ser.rtscts=0
ser.timeout=20
ser.port="/dev/ttyUSB0"

try:
  ser.open()
except:
  sys.exit ("Error opening port %s"  % ser.name)

# init
response = False
# start list for data collection
dataList = [None]*7
db="/var/db/db-name"

# define regex for testing data
kwh = re.compile("0\d{4}\.\d{3}\*kWh")
kw = re.compile("000\d{1}\.\d{2}\*kW")
m3 = re.compile("0\d{4}\.\d{1,3}")

# get last record
conn = sqlite3.connect(db)
c = conn.cursor()
c.execute('SELECT "power_usage_low","power_usage_hi","power_return_low","power_return_hi","gas_usage" FROM "energy" ORDER BY id DESC LIMIT 1;')
lastRecord = c.fetchone()
c.close()
conn.close()

lastUsageLow = float(lastRecord[0])
lastUsageHi = float(lastRecord[1])
lastReturnLow = float(lastRecord[2])
lastReturnHi = float(lastRecord[3])
lastGasUsage = float(lastRecord[4])

# loop though the data
while True:
  if ser.readable():
    data_raw = ser.readline()
    data = str(data_raw).strip()

    # start
    if data[0:1] == "/":
      response = True

    if response :
      # powerUsageLow
      if data[0:9] == "1-0:1.8.1" and kwh.match(data[10:23]) :
        lastUsageLowDiff = float(data[10:19]) - lastUsageLow
        if lastUsageLowDiff >= -1 and lastUsageLowDiff < 2 :
          dataList[0] = float(data[10:19])
      # powerUsageHi
      elif data[0:9] == "1-0:1.8.2" and kwh.match(data[10:23]) :
        lastUsageHiDiff = float(data[10:19]) - lastUsageHi
        if lastUsageHiDiff >= -1 and lastUsageHiDiff < 2 :
          dataList[1] = float(data[10:19])
      # powerReturnLow
      elif data[0:9] == "1-0:2.8.1" and kwh.match(data[10:23]) :
        lastReturnLowDiff = float(data[10:19]) - lastReturnLow
        if lastReturnLowDiff >= -1 and lastReturnLowDiff < 2 :
          dataList[2] = float(data[10:19])
      # powerReturnHi
      elif data[0:9] == "1-0:2.8.2" and kwh.match(data[10:23]) :
        lastReturnHiDiff = float(data[10:19]) - lastReturnHi
        if lastReturnHiDiff >= -1 and lastReturnHiDiff < 2 :
          dataList[3] = float(data[10:19])
      # currentPowerUsage
      elif data[0:9] == "1-0:1.7.0" and kw.match(data[10:20]) and float(data[10:17])*1000 <= 7999 :
        dataList[4] = float(data[10:17])*1000
      # currentPowerReturn
      elif data[0:9] == "1-0:2.7.0" and kw.match(data[10:20]) and float(data[10:17])*1000 <= 1250 :
        dataList[5] = float(data[10:17])*1000
      # gasUsage
      elif data[0:1] == "(" and m3.match(data[1:9]) :
        lastGasUsageDiff = float(data[1:9]) - lastGasUsage
        if lastGasUsageDiff >= -1 and lastGasUsageDiff < 2 :
          dataList[6] = float(data[1:9])

    # test if all data is collected
    if data[0:1] == "!" and (None not in dataList or time.time() > timeout) :
      break;

# close the connection
try:
  ser.close()
except:
  sys.exit ("Error %s. Could not close serial port" % ser.name )

# get datetime and add to list
ts = time.time()
dt = datetime.datetime.fromtimestamp(ts).strftime('%Y-%m-%d %H:%M:%S')
dataList.append(dt)

if None not in dataList :
  conn = sqlite3.connect(db)
  c = conn.cursor()
  q = '''INSERT INTO "energy" ("power_usage_low","power_usage_hi","power_return_low","power_return_hi","current_power_usage","current_power_return","gas_usage","datetime") VALUES (?,?,?,?,?,?,?,?)'''
  c.execute(q, (dataList))
  conn.commit()
  c.close()
  conn.close()

print (dataList)

#print "power_usage", dataList[4]
#print "power_return", dataList[5]
