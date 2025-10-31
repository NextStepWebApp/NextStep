#!/bin/python3

# temporary !!!!
fname_data_import = "data.csv"
fname_config_accessibility = (
    "/home/william/Documents/programming/PWS/NextStep/config/accessibility.csv"
)
fname_config_city = "/home/william/Documents/programming/PWS/NextStep/config/city.csv"
fname_config_class = "/home/william/Documents/programming/PWS/NextStep/config/class.csv"
fname_config_country = (
    "/home/william/Documents/programming/PWS/NextStep/config/country.csv"
)
fname_config_education = (
    "/home/william/Documents/programming/PWS/NextStep/config/education.csv"
)
fname_config_school = (
    "/home/william/Documents/programming/PWS/NextStep/config/school.csv"
)
fname_config_status = (
    "/home/william/Documents/programming/PWS/NextStep/config/status.csv"
)


file_list = [
    (fname_data_import, "data_import"),
    (fname_config_accessibility, "config_accessibility"),
    (fname_config_city, "config_city"),
    (fname_config_class, "config_class"),
    (fname_config_country, "config_country"),
    (fname_config_education, "config_education"),
    (fname_config_school, "config_school"),
    (fname_config_status, "config_status"),
]

# open the files
file_handles = {}
for fname, name in file_list:
    try:
        file_handles[name] = open(fname)
    except Exception as e:
        print(f"ERROR – could not open: {fname}")
        print(f"  Reason: {e}")
        exit()

# Put all the data of the files in there own list

file_contents = {}
for name, file_handle in file_handles.items():
    lines = []
    for line in file_handle:
        lines.append(line.rstrip("\n"))
    file_contents[name] = lines

# This will be like the list with all the lines of the csv file
# Need a loop ittiration to get the items
data_list = []

for line in file_contents["data_import"][1:]:  # skip the first line (header)
    data = line.split(",")
    cleaned_data = [item.strip().strip("\"'") for item in data]
    data_list.append(cleaned_data)

########################################################
# From this point all the data is in list or dictionaries
# The next part will make shure that every enry of the
# import data is a valid entry - So it is verification
# ######################################################

# time ,  email, name, phone, class, country, city, school, education_program, status, accessibility
# 0        1       2     3      4      5      6        7             8            9         10
# This is the csv standart used (from googled docs, with google email requierd)


# This funtion compares the data from master csv file with the config functions
def compare_data(data_number, config_name):
    data_exists = False
    for class_name in file_contents[config_name]:
        if person[data_number] == class_name:
            data_exists = True
    if data_exists == True:
        # print("data supported")
        data_exists = False
        return True
    else:
        # print(f"ERROR - {person[3]} has a invalid data value that does not exist in {config_name}")
        return False


result_verification = True
for person in data_list:
    if (
        compare_data(4, "config_class") == False
        or compare_data(5, "config_country") == False
        or compare_data(6, "config_city") == False
        or compare_data(7, "config_school") == False
        or compare_data(8, "config_education") == False
        or compare_data(9, "config_status") == False
        or compare_data(10, "config_accessibility") == False
    ):
        result_verification = False


# funtion that closes all the files
def close_all_files():
    for name, f in file_handles.items():
        f.close()


if result_verification == True:
    print(f"SUCCES - all the data is verified")
else:
    print("ERROR - invalid data")
    close_all_files
    exit()

# Popluate database
import sqlite3

# opening the database
try:
    conn = sqlite3.connect(
        "/home/william/Documents/programming/PWS/NextStep/setup/nextstep_data.db"
    )  # this is temporary !!!!!!!
    cursor = conn.cursor()
    print("connection succes")
except:
    print("error opening database")
    # loop that closes all the files
    close_all_files()
    exit()

# adding the data from the configs in the db if needed


def INSERT_OR_IGNORE(TABLE_NAME, data_value_person):
    if TABLE_NAME == "EDUCATION_PROGRAM":
        column_name = "program_name"
    else:
        column_name = TABLE_NAME.lower() + "_name"
    cursor.execute(
        f"INSERT OR IGNORE INTO {TABLE_NAME} ({column_name}) VALUES (?)",
        (data_value_person,),
    )


# TABLE_NAMES = ["ACCESSIBILITY", "CITY", "CLASS", "COUNTRY", "EDUCATION_PROGRAM", "SCHOOL", "STATUS"]
# time ,  email, name, phone, class, country, city, school, education_program, status, accessibility
# 0        1       2     3      4      5      6        7             8            9         10
# This is the csv standart used (from googled docs, with google email requierd)

for person in data_list:
    INSERT_OR_IGNORE("ACCESSIBILITY", person[10])
    INSERT_OR_IGNORE("CITY", person[6])
    INSERT_OR_IGNORE("CLASS", person[4])
    INSERT_OR_IGNORE("COUNTRY", person[5])
    INSERT_OR_IGNORE("EDUCATION_PROGRAM", person[8])
    INSERT_OR_IGNORE("SCHOOL", person[7])
    INSERT_OR_IGNORE("STATUS", person[9])


# This code block gets the id's of the different tables from specific person
for person in data_list:
    cursor.execute(
        "SELECT accessibility_id FROM ACCESSIBILITY WHERE accessibility_name = ?",
        (person[10],),
    )
    result = cursor.fetchone()
    accessibility_id = result[0] if result else None

    cursor.execute("SELECT city_id FROM CITY WHERE city_name = ?", (person[6],))
    result = cursor.fetchone()
    city_id = result[0] if result else None

    cursor.execute("SELECT class_id FROM CLASS WHERE class_name = ?", (person[4],))
    result = cursor.fetchone()
    class_id = result[0] if result else None

    cursor.execute(
        "SELECT country_id FROM COUNTRY WHERE country_name = ?", (person[5],)
    )
    result = cursor.fetchone()
    country_id = result[0] if result else None

    cursor.execute(
        "SELECT program_id FROM EDUCATION_PROGRAM WHERE program_name = ?", (person[8],)
    )
    result = cursor.fetchone()
    program_id = result[0] if result else None

    cursor.execute("SELECT school_id FROM SCHOOL WHERE school_name = ?", (person[7],))
    result = cursor.fetchone()
    school_id = result[0] if result else None

    cursor.execute("SELECT status_id FROM STATUS WHERE status_name = ?", (person[9],))
    result = cursor.fetchone()
    status_id = result[0] if result else None

    # Now insert with the correct IDs for this person
    cursor.execute(
        """INSERT OR REPLACE INTO STUDENTS (students_name, students_email, students_phone_number, students_class_id,
        students_country_id, students_city_id, students_school_id, students_education_program_id, students_status_id,
        students_accessibility_id, students_created_date, students_last_updated)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, strftime('%Y', 'now'), date('now'))""",
        (
            person[2],
            person[1],
            person[3],
            class_id,
            country_id,
            city_id,
            school_id,
            program_id,
            status_id,
            accessibility_id,
        ),
    )

conn.commit()
conn.close()

close_all_files()
