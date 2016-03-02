CREATE TABLE 'energy' (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    power_usage_low NUMERIC,
    power_usage_hi NUMERIC,
    power_return_low NUMERIC,
    power_return_hi NUMERIC,
    current_power_usage NUMERIC,
    current_power_return NUMERIC,
    gas_usage NUMERIC,'datetime' DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE 'energy_day' (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    power_usage_low NUMERIC,
    power_usage_hi NUMERIC,
    power_return_low NUMERIC,
    power_return_hi NUMERIC,
    gas_usage NUMERIC,'date' DATETIME
);

CREATE VIEW "TotalsByDay" AS SELECT
    strftime('%Y-%m-%d', date) as day,
    ROUND(SUM("power_usage_hi") + SUM("power_usage_low"), 2) as powerUsage,
    ROUND(SUM("power_return_hi") + SUM("power_return_low"), 2) as powerReturn,
    ROUND(SUM("gas_usage"), 2) as gasUsage
FROM "energy_day"
GROUP BY day
ORDER BY day DESC;

CREATE VIEW "TotalsByMonth" AS SELECT
    strftime('%Y-%m', date) as month,
    ROUND(SUM("power_usage_low"), 2) as powerUsageLow,
    ROUND(SUM("power_usage_hi"), 2) as powerUsageHi,
    ROUND(SUM("power_usage_hi") + SUM("power_usage_low"), 2) as powerUsage,
    ROUND(SUM("power_return_low"), 2) as powerReturnLow,
    ROUND(SUM("power_return_hi"), 2) as powerReturnHi,
    ROUND(SUM("power_return_hi") + SUM("power_return_low"), 2) as powerReturn,
    ROUND(SUM("gas_usage"), 2) as gasUsage
FROM "energy_day" GROUP BY month;

CREATE VIEW "TotalsByYear" AS SELECT
    strftime('%Y', date) as year,
    ROUND(SUM("power_usage_low"), 2) as powerUsageLow,
    ROUND(SUM("power_usage_hi"), 2) as powerUsageHi,
    ROUND(SUM("power_usage_hi") + SUM("power_usage_low"), 2) as powerUsage,
    ROUND(SUM("power_return_low"), 2) as powerReturnLow,
    ROUND(SUM("power_return_hi"), 2) as powerReturnHi,
    ROUND(SUM("power_return_hi") + SUM("power_return_low"), 2) as powerReturn,
    ROUND(SUM("gas_usage"), 2) as gasUsage
FROM "energy_day" GROUP BY year;

