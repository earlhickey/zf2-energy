
# README



## usefull queries for future usage

```
SELECT
    strftime('%Y-%m', date) as month,
    ROUND(SUM("power_usage_low"), 2) as powerUsageLow,
    ROUND(SUM("power_usage_hi"), 2) as powerUsageHi,
    powerUsageLow+powerUsageHi as powerUsage,
    ROUND(SUM("power_return_low"), 2) as power_return_low,
    ROUND(SUM("power_return_hi"), 2) as power_return_hi,
    ROUND(SUM("gas_usage"), 2) as gas_usage
FROM "energy_day" GROUP BY month;
```

```
CREATE VIEW "TotalsByHour" AS SELECT
    strftime('%Y-%m-%d %H', datetime) as hour,
    ROUND(SUM("power_usage_hi") + SUM("power_usage_low"), 2) as powerUsage,
    ROUND(SUM("power_return_hi") + SUM("power_return_low"), 2) as powerReturn,
    ROUND(SUM("gas_usage"), 2) as gasUsage
FROM "energy"
GROUP BY hour
ORDER BY hour DESC;
```
