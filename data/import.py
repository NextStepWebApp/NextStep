#!/bin/python3
import json
import time
import csv
import sqlite3
import os
import re
import sys

start_time = time.time()

# Get the command line argument
try:
    csv_file_name = sys.argv[1]
except Exception: 
    print("Missing command line argument (file path to csv)")
    exit()

# Example csv_file_name = "/var/www/html/NextStep/data/data.csv"

# This is the file path to the configs and the csv file name
#path_config = "/home/william/Documents/programming/PWS/NextStep/config/config.json"
path_config = "/var/www/html/NextStep/config/config.json"
#path_database = "/home/william/Documents/programming/PWS/NextStep/setup/nextstep_data.db"
path_database = "/var/www/html/NextStep/setup/nextstep_data.db"
errors_path = "/var/www/html/NextStep/data/errors.json"

# This is the json template
errors = {
    "validation_errors": [],
    "duplicate_errors": [],
    "format_errors": []
}

# Remove old errors.json and create new one from start
if os.path.exists(errors_path):
    os.remove(errors_path)

# Create new errors.json file
try: 
    fhand_errors = open(errors_path, 'w')
    json.dump(errors, fhand_errors, indent=4)
    fhand_errors.close()
except Exception as e:
    print("Could not create the errors.json")
    print(f"Reason: {e}")
    exit()

# Load config file
try:
    fhand_config = open(path_config)
    config = json.load(fhand_config)
    fhand_config.close()
except Exception as e:
    print("Could not load the config file")
    print(f"Reason: {e}")
    exit()

# Load and process CSV file
csv_data = []
try:
    fhand_csv = open(csv_file_name, 'r', encoding='utf-8')
    reader = csv.DictReader(fhand_csv)
    
   # Validate CSV headers before processing
    required_columns = ['name', 'email', 'phone', 'class', 'country', 'city', 
        'school', 'education_program', 'status', 'accessibility']
       
    if reader.fieldnames is None:
        print("ERROR: CSV file is empty or invalid")
        fhand_csv.close()
        exit()
       
    csv_headers = [h.strip() for h in reader.fieldnames]
    missing = [col for col in required_columns if col not in csv_headers]
    
    if missing:
        print(f"ERROR: CSV missing required columns: {missing}")
        fhand_csv.close()
        exit()
    
    for row in reader:
        clean_row = {key.strip(): value.strip().strip("\"'") for key, value in row.items()}
        csv_data.append(clean_row)
    
    fhand_csv.close()
    
except Exception as e:
    print("Could not load the csv file")
    print(f"Reason: {e}")
    exit()
    
# Validation functions
def compare_data(data_value, config_name):
    data_exists = False
    for config_value in config[config_name]:
        if data_value == config_value:
            data_exists = True
            break
    return data_exists

def validate_email(email):
    if not email or len(email.strip()) == 0:
        return False
    
    # Basic email regex pattern
    pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
    if not re.match(pattern, email):
        return False
    
    return True

def validate_phone(phone):
    if not phone or len(phone.strip()) == 0:
        return False
    
    # Remove common separators
    clean_phone = re.sub(r'[\s\-\(\)\.]', '', phone)
    
    # Check if it contains only digits and optional + at start
    if not re.match(r'^\+?\d{8,15}$', clean_phone):
        return False
    
    return True

def validate_name(name):
    if not name or len(name.strip()) == 0:
        return False
    
    if len(name.strip()) < 2:
        return False
    
    if len(name) > 100:
        return False
    
    # Check if name contains only letters, spaces, hyphens, and apostrophes
    if not re.match(r"^[a-zA-Z\s\-'\.]+$", name):
        return False
    
    return True

# Validate all people
valid_people = []

for person in csv_data:
    person_valid = True
    
    # Format validation (email, phone, name)
    if not validate_email(person["email"]):
        errors["format_errors"].append(person)
        continue
    
    if not validate_phone(person["phone"]):
        errors["format_errors"].append(person)
        continue
    
    if not validate_name(person["name"]):
        errors["format_errors"].append(person)
        continue
    
    # Config validation, only if format validation passed
    if compare_data(person['class'], "class") == False:
        person_valid = False
        
    if compare_data(person['country'], "country") == False:
        person_valid = False
        
    if compare_data(person['city'], "city") == False:
        person_valid = False
        
    if compare_data(person['school'], "school") == False:
        person_valid = False
        
    if compare_data(person['education_program'], "education") == False:
        person_valid = False
        
    if compare_data(person['status'], "status") == False:
        person_valid = False
        
    if compare_data(person['accessibility'], "accessibility") == False:
        person_valid = False
    
    if person_valid == True:
        valid_people.append(person)
    else:
        errors["validation_errors"].append(person)
 
# Populate database part
try:
    conn = sqlite3.connect(path_database)
    cursor = conn.cursor()
except Exception as e:
    print("error opening database")
    print(f"Reason: {e}")
    exit()

# Adding the data from the configs in the db if needed
def INSERT_OR_IGNORE(TABLE_NAME, data_value_person):
    if TABLE_NAME == "EDUCATION_PROGRAM":
        column_name = "program_name"
    else:
        column_name = TABLE_NAME.lower() + "_name"
    cursor.execute(
        f"INSERT OR IGNORE INTO {TABLE_NAME} ({column_name}) VALUES (?)",
        (data_value_person,),
    )

# Insert all data from the tables  
for person in valid_people:
    INSERT_OR_IGNORE("ACCESSIBILITY", person['accessibility'])
    INSERT_OR_IGNORE("CITY", person['city'])
    INSERT_OR_IGNORE("CLASS", person['class'])
    INSERT_OR_IGNORE("COUNTRY", person['country'])
    INSERT_OR_IGNORE("EDUCATION_PROGRAM", person['education_program'])
    INSERT_OR_IGNORE("SCHOOL", person['school'])
    INSERT_OR_IGNORE("STATUS", person['status'])

# This code block gets the id's of the different tables for each specific person
for person in valid_people:
    cursor.execute(
        "SELECT accessibility_id FROM ACCESSIBILITY WHERE accessibility_name = ?",
        (person['accessibility'],),
    )
    result = cursor.fetchone()
    accessibility_id = result[0] if result else None

    cursor.execute("SELECT city_id FROM CITY WHERE city_name = ?", (person['city'],))
    result = cursor.fetchone()
    city_id = result[0] if result else None

    cursor.execute("SELECT class_id FROM CLASS WHERE class_name = ?", (person['class'],))
    result = cursor.fetchone()
    class_id = result[0] if result else None

    cursor.execute(
        "SELECT country_id FROM COUNTRY WHERE country_name = ?", (person['country'],)
    )
    result = cursor.fetchone()
    country_id = result[0] if result else None

    cursor.execute(
        "SELECT program_id FROM EDUCATION_PROGRAM WHERE program_name = ?", (person['education_program'],)
    )
    result = cursor.fetchone()
    program_id = result[0] if result else None

    cursor.execute("SELECT school_id FROM SCHOOL WHERE school_name = ?", (person['school'],))
    result = cursor.fetchone()
    school_id = result[0] if result else None

    cursor.execute("SELECT status_id FROM STATUS WHERE status_name = ?", (person['status'],))
    result = cursor.fetchone()
    status_id = result[0] if result else None

    # Check if student with this email already exists
    cursor.execute(
        "SELECT students_id FROM STUDENTS WHERE students_email = ?",
        (person['email'],)
    )
    result_email = cursor.fetchone()
    
    # Check if name or phone already exists (but with different email)
    cursor.execute(
        "SELECT students_id FROM STUDENTS WHERE (students_phone_number = ? OR students_name = ?) AND students_email != ?",
        (person['phone'], person['name'], person['email'],)
    )
    result_name_phone = cursor.fetchone()
      
    if result_email:
        # Student exists with this email, UPDATE the record
        student_id = result_email[0]
        cursor.execute(
            """UPDATE STUDENTS SET 
            students_name = ?,
            students_phone_number = ?,
            students_class_id = ?,
            students_country_id = ?,
            students_city_id = ?,
            students_school_id = ?,
            students_education_program_id = ?,
            students_status_id = ?,
            students_accessibility_id = ?,
            students_last_updated = CAST(strftime('%s', 'now') AS INTEGER)
            WHERE students_id = ?""",
            (
                person['name'],
                person['phone'],
                class_id,
                country_id,
                city_id,
                school_id,
                program_id,
                status_id,
                accessibility_id,
                student_id,
            ),
        )
    elif result_name_phone:
        # Name or phone exists with different email, skip and add to errors
        errors["duplicate_errors"].append(person)
        continue
    else:
        # Student doesn't exist, INSERT new record
        cursor.execute(
            """INSERT INTO STUDENTS (students_name, students_email, students_phone_number, students_class_id,
            students_country_id, students_city_id, students_school_id, students_education_program_id, students_status_id,
            students_accessibility_id, students_created_date, students_last_updated)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,  CAST(strftime('%Y', 'now') AS INTEGER), CAST(strftime('%s', 'now') AS INTEGER))""",
            (
                person['name'],
                person['email'],
                person['phone'],
                class_id,
                country_id,
                city_id,
                school_id,
                program_id,
                status_id,
                accessibility_id,
            ),
        )

try:
    conn.commit()
except Exception as e:
    conn.rollback() 
    print(f"Database error, rolled back: {e}")
    exit()

conn.close()

# Final error handling, always write to errors.json
try: 
    fhand_errors = open(errors_path, 'w')
    json.dump(errors, fhand_errors, indent=4)
    fhand_errors.close()
except Exception as e:
    print("Could not write to errors.json")
    print(f"Reason: {e}")

# Report results
total_errors = len(errors["validation_errors"]) + len(errors["duplicate_errors"]) + len(errors["format_errors"])
if total_errors > 0:
    print(f"\nERROR - Import completed with {total_errors} error(s):")
    if len(errors["format_errors"]) > 0:
        print(f"  - {len(errors['format_errors'])} format error(s) (email/phone/name)")
    if len(errors["validation_errors"]) > 0:
        print(f"  - {len(errors['validation_errors'])} config validation error(s)")
    if len(errors["duplicate_errors"]) > 0:
        print(f"  - {len(errors['duplicate_errors'])} duplicate error(s)")
    print("Check errors.json for details")
else:
    print("\nSUCCESS - Import completed with 0 errors")
    print("errors.json is empty")

end_time = time.time()
print(f"Execution time: {end_time - start_time:.2f} seconds")
