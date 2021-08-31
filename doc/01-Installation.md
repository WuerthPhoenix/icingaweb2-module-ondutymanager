# Installation

## Requirements
* Neteye (>= 4.18)
* Icinga Web 2 modules:
	* Auditlog
	* Director

## Installation

For the configuration, `rpm-functions.sh` and `functions.sh` provided by `neteye_secure_install` will be used.

```bash
source /usr/share/neteye/secure_install/functions.sh
source /usr/share/neteye/scripts/rpm-functions.sh
```

Declaring common variables and creating passwords

```bash
MODULE=ondutymanager
DB_PASSWORD=$(generate_and_save_pw ondutymanager_db)
CONFDIR=/neteye/shared/icingaweb2/conf/modules/ondutymanager
MODULE_DIR="/usr/share/icingaweb2/modules"
TARGET_DIR="${MODULE_DIR}/${MODULE}"
```


Clone the repository to your local system and configure it

```bash
cd ${MODULE_DIR}
git clone https://Dominik17@bitbucket.org/Dominik17/ondutymanager.git
chmod 755 ${MODULE}
chown apache:root ${MODULE}
cd ${MODULE}/
```

Creating database and preparing it for access

```bash
cat <<EOF | mysql
CREATE DATABASE $MODULE;
GRANT SELECT, INSERT, UPDATE, DELETE, DROP, CREATE VIEW, INDEX, EXECUTE ON ${MODULE}.* TO '${MODULE}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT SELECT, INSERT, UPDATE, DELETE, DROP, CREATE VIEW, INDEX, EXECUTE ON ${MODULE}.* TO '${MODULE}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
FLUSH PRIVILEGES;
EOF
```

Importing last available schema

```bash
mysql ${MODULE} < ${TARGET_DIR}/etc/schema/mysql.sql
```

Create IcingaWeb2 Resource for database ondutymanager and enabling module

```bash
create_icingaweb2_db_resource ${MODULE} ${DB_PASSWORD}
icingacli module enable ${MODULE}
```

Enter the director and create following custom variables:
- User alias: contains the alias of a user
- User phone number: contains the phone number of a user
- User mobile phone number: contains the mobile phone number of a user

Afterwards add this custom vars to a user template and insert the wished values in the user objects.
The user must also contain a usergroup. Later the team in the ondutymanager-module will take the user of a team from this usergroup.

As last step you have to go to the configuration tab of the module and insert the names of the previous set custom varibales and the suffixes for the user alias. The custom variables names have to correspond with the previous set ones, otherwise the module will not work and crash if it has to select users.
To go there you have to go to:
    Menu point: Configuration -> Modules -> Ondutymanager -> Tab: Configuration

Insert all values and you should be good to start!

## Test
1. Open your Neteye-Web-Interface
2. Authenticate to enter
3. Check if on the left menu the module "Ondutymanager" appears.
4. Click on it, fill the editor with some object if you have the permission and create a new week.