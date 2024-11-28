# PHP SQLite Project

This project is a PHP-based web application that uses SQLite for storing form submissions. It includes APIs for securely submitting form data, CSRF protection, and logging functionality. The project is ideal for lightweight applications requiring minimal setup while maintaining robust security features.

---

## Features

- **Form Submission API**: Accepts and validates user input, stores data in an SQLite database, and responds with success or error messages.
- **SQLite Integration**: A simple and efficient database structure for managing form submissions.
- **CSRF Protection**: Ensures secure form submissions by validating CSRF tokens.
- **Error Logging**: All API requests and errors are logged for debugging and monitoring.

---

## Project Files Overview

### Main Project Files

1. **`submit.php`**:  
   Handles the form submissions, validates inputs, stores data in the database, and returns appropriate responses.

2. **`get_csrf.php`**:  
   Generates and manages CSRF tokens using PHP sessions to protect against cross-site request forgery attacks.  

3. **`db.config.php`**:  
   This file configures the SQLite database connection for the project. It uses PHP's PDO for secure database interaction and handles exceptions gracefully. 

  ## Requirements

- **PHP**: 7.4 or later
- **SQLite**: 3.x
- **Nginx**: for serving the application

---
### Endpoints

- URL:  **`/get_csrf.php`**
- Method: **``GET`**
- Response: Returns a JSON object containing the CSRF token:
json
```json{
    "csrf_token": "generated_token_value" }
```


 ## Form Submission API

- URL:  **`/submit.php`**
- Method: **``POST`**
- Content-Type: **`application/json`**
The API expects the following JSON structure:

```json{
    {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+1234567890",
    "select_service": "Web Development",
    "select_price": "1000",
    "comments": "No comments",
    "user_ip": "192.168.1.1"
}
```
## Response
Success (HTTP 200):
```json{
{
    "success": true,
    "redirect_url": "https://google.com",
    "message": "Data successfully processed!"
}
```
## Error (HTTP 4xx or 5xx):
- Invalid phone number:
```json{
{
    "success": false,
    "message": "Invalid phone number"
}
```
 - Non valid data:
```json{
{
    "success": false,
    "message": "First name must be at least 2 characters"
}
```
- SCSRF token error:
```json{
{
    "success": false,
    "message": "Invalid CSRF token"
}
```
- Internal server error:
```json{
{
    "success": false,
    "message": "Internal server error"
}
```
## Logs
All API requests are logged in **``request_log.txt`** in JSON format, including the request data and the server's response.

## Database Structure
```sql{
    CREATE TABLE leads (
    id INTEGER CONSTRAINT leads_pk PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    phone TEXT NOT NULL,
    email TEXT NOT NULL,
    selected_service TEXT NOT NULL,
    select_price TEXT,
    comments TEXT,
    user_ip TEXT,
    country TEXT
);

```

## Set Up the SQLite Database
- Create the database file:
```bash{
touch database.sqlite
```




