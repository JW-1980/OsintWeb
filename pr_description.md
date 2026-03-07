🔒 Fix SQL Injection Vulnerability in DisinformationController Sorting

🎯 **What:**
Fixed an unvalidated sort parameter vulnerability in `app/Http/Controllers/Api/DisinformationController.php`. The `index`, `flags`, and `patterns` methods were directly passing `$request->input('sort_by')` into the Query Builder's `orderBy` clause without proper validation.

⚠️ **Risk:**
Directly interpolating unvalidated user input into an `orderBy` clause is a known SQL injection vector. Attackers could potentially manipulate the `sort_by` parameter to execute arbitrary SQL commands, leading to data exfiltration, information disclosure, or denial of service.

🛡️ **Solution:**
Implemented a strict array allowlist for the `$sortField` parameter in each affected method. If an invalid or malicious column name is provided, the code safely falls back to a default sort column (`created_at`, `flagged_at`, or `name`). Additionally, explicitly cast `$request->input('sort_direction')` to a string before using `strtolower()` to avoid PHP 8.1+ deprecation warnings when the parameter is missing.
