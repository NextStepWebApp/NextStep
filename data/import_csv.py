#!/bin/python3

# temporary !!!!
fname_data_import = "data.csv"
fname_config_accessibility = "/home/william/Documents/programming/PWS/NextStep/config/accessibility.csv"
fname_config_city = "/home/william/Documents/programming/PWS/NextStep/config/city.csv"
fname_config_class = "/home/william/Documents/programming/PWS/NextStep/config/class.csv"
fname_config_country = "/home/william/Documents/programming/PWS/NextStep/config/country.csv"
fname_config_education = "/home/william/Documents/programming/PWS/NextStep/config/education.csv"
fname_config_school = "/home/william/Documents/programming/PWS/NextStep/config/school.csv"
fname_config_status = "/home/william/Documents/programming/PWS/NextStep/config/status.csv"

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

data_list = []
count = 0

for line in file_handles["data_import"]:
    print(line.rstrip())
    data = line.strip().split(",")
    data_list.append(data)
    count += 1

print(f"Total entries read: {count}")

for row in data_list:
    print(row)

for name, f in file_handles.items():
    f.close()
