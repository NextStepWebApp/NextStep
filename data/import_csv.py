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

# Put all the data of the files in there own list

file_contents = {}
for name, file_handle in file_handles.items():
    lines = []
    for line in file_handle:
        lines.append(line.rstrip('\n'))
    file_contents[name] = lines

# This will be like the list with all the lines of the csv file
# Need a loop ittiration to get the items
data_list = []

for line in file_contents['data_import'][1:]: # skip the first line (header)
    data = line.split(",")
    cleaned_data = [item.strip().strip('"\'') for item in data]
    data_list.append(cleaned_data)

# Example
# How to access data
#print(data_list)
#print(data_list[0][3])
#print(file_contents['config_city'])
#print(file_contents['config_class'])
#print(file_contents['data_import'])

########################################################
# From this point all the data is in list or dictionaries
# The next part will make shure that everie enry of the
# import data is a valid entry
# ######################################################

#time ,  email1, email2, name, phone, class, country, city, school, education_program, status, accessibility
# 0        1        2      3      4      5      6        7     8            9             10          11
# This is the csv standart used (from googled docs, with google email requierd)



def compare_data(data_number, config_name):
    data_exists = False
    for class_name in file_contents[config_name]:
        if person[data_number] == class_name:
            data_exists = True
    if data_exists == True:
        #print("data supported")
        data_exists = False
        return True
    else :
        print(f"ERROR - {person[3]} has a invalid data value that does not exist in {config_name}")
        return False

result_person = False
for person in data_list:
    if compare_data(5, "config_class") == True and \
    compare_data(6, "config_country") == True and \
    compare_data(7, "config_city") == True and \
    compare_data(8, "config_school") == True and \
    compare_data(9, "config_education") == True and \
    compare_data(10, "config_status") == True and \
    compare_data(11, "config_accessibility") == True:
        result_person = True

    if result_person == True:
        print(f"SUCCES - {person[3]}")


# Loop that closes all the files
for name, f in file_handles.items():
    f.close()
