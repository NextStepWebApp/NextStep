#!/bin/python3
# Program to export the database to csv file

import sqlite3
from datetime import datetime

# opening the database
try:
    conn = sqlite3.connect(
        "/home/william/Documents/programming/PWS/NextStep/setup/nextstep_data.db"
    )  # this is temporary !!!!!!!
    cursor = conn.cursor()
    print("SUCCESS - opening database")
except:
    print("ERROR - opening database")
    exit()


cursor.execute("""SELECT
    STUDENTS.students_name,
    STUDENTS.students_email,
    STUDENTS.students_phone_number,
    CLASS.class_name,
    COUNTRY.country_name,
    CITY.city_name,
    SCHOOL.school_name,
    EDUCATION_PROGRAM.program_name,
    STATUS.status_name,
    ACCESSIBILITY.accessibility_name,
    STUDENTS.students_created_date,
    STUDENTS.students_last_updated
FROM STUDENTS
LEFT JOIN CLASS ON STUDENTS.students_class_id = CLASS.class_id
LEFT JOIN COUNTRY ON STUDENTS.students_country_id = COUNTRY.country_id
LEFT JOIN CITY ON STUDENTS.students_city_id = CITY.city_id
LEFT JOIN SCHOOL ON STUDENTS.students_school_id = SCHOOL.school_id
LEFT JOIN EDUCATION_PROGRAM ON STUDENTS.students_education_program_id = EDUCATION_PROGRAM.program_id
LEFT JOIN STATUS ON STUDENTS.students_status_id = STATUS.status_id
LEFT JOIN ACCESSIBILITY ON STUDENTS.students_accessibility_id = ACCESSIBILITY.accessibility_id;""")

rows = cursor.fetchall() # all the data from the db

# Creating a csv file
fname = "export_data.csv"
fhand = open(fname, 'w')

# time ,  email, name, phone, class, country, city, school, education_program, status, accessibility
# 0        1       2     3      4      5      6        7             8            9         10
# This is the csv standart used (from googled docs, with google email requierd)

# Creating the header
header_list = ["time", "email", "name", "phone", "class", "country", "city", "school", "education_program", "status", "accessibility"]
count_list = len(header_list) - 1
count = 0
for header_item in header_list:
    if count != count_list:
        fhand.write(f"{header_item}, ")
    else:
        fhand.write(f"{header_item}\n")
    count += 1

# loop that writes the data in the csv file
for row in rows:
    current_time = datetime.now() # get the current date and time
    fhand.write(f"{current_time}, ")
    count_tuple = len(row) - 1
    count = 0
    for item in row:
        if count != count_tuple:
            fhand.write(f"{item}, ")
        else:
            fhand.write(f"{item}\n")
        count += 1

fhand.close()
conn.close()
