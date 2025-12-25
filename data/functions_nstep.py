import re


# Validation functions
def compare_data(data_value, config_name, config):
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
    pattern = r"^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
    if not re.match(pattern, email):
        return False

    return True


def validate_phone(phone):
    if not phone or len(phone.strip()) == 0:
        return False

    # Remove common separators
    clean_phone = re.sub(r"[\s\-\(\)\.]", "", phone)

    # Check if it contains only digits and optional + at start
    if not re.match(r"^\+?\d{8,15}$", clean_phone):
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


# Adding the data from the configs in the db if needed
def INSERT_OR_IGNORE(TABLE_NAME, data_value_person, cursor):
    if TABLE_NAME == "EDUCATION_PROGRAM":
        column_name = "program_name"
    else:
        column_name = TABLE_NAME.lower() + "_name"
    cursor.execute(
        f"INSERT OR IGNORE INTO {TABLE_NAME} ({column_name}) VALUES (?)",
        (data_value_person,),
    )
