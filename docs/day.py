#!/usr/bin/python
# SMARTMETER agregate data
# calculate and safe yesterdays energy usage
# @author pG
version = "1.1"

# needed libraries
from datetime import date, timedelta
import sqlite3

# get yesterdays date
ts = date.today() - timedelta(1)
yesterday = ts.strftime('%Y-%m-%d')

# get first record of today and yesterday
conn = sqlite3.connect('/var/db/pg')
c = conn.cursor()
c.execute('''SELECT power_usage_low, power_usage_hi, power_return_low,power_return_hi, gas_usage, MIN(datetime)
  FROM energy
  WHERE datetime > ?
  GROUP BY strftime('%Y-%m-%d', datetime)
  ORDER BY datetime DESC;''', (yesterday,))
results = c.fetchall()

# results should return 2 records
print results[0] # first record of today
print results[1] # first record of yesterday

# calculate yesterdays totals
power_usage_low = round(results[0][0] - results[1][0], 3)
power_usage_hi = round(results[0][1] - results[1][1], 3)
power_return_low = round(results[0][2] - results[1][2], 3)
power_return_hi = round(results[0][3] - results[1][3], 3)
gas_usage = round(results[0][4] - results[1][4], 3)

# insert agregated data
q = '''INSERT INTO "energy_day" ("power_usage_low","power_usage_hi","power_return_low","power_return_hi","gas_usage","date") VALUES (?,?,?,?,?,?)'''
c.execute(q, (power_usage_low, power_usage_hi, power_return_low, power_return_hi, gas_usage, yesterday))
conn.commit()
c.close()
conn.close()
