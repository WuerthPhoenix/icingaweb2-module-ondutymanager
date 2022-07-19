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
git clone https://github.com/WuerthPhoenix/icingaweb2-module-ondutymanager.git
mv icingaweb2-module-ondutymanager ${MODULE}
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

Importing last available schema:
Note:
- to import an empyt DB use file: mysql.sql.
- CAREFUL: Starting with an empty schema the UI will not show the forms correctly and you need to start configuring the first objects on DB-level.
- Therefore it is sugested to start with a DB including some example data use file: mysql_with_sample_data.sql

```bash
mysql ${MODULE} < ${TARGET_DIR}/etc/schema/mysql_with_sample_data.sql
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

Query to generate field in director:
```
INSERT INTO `director_datafield` (`category_id`, `varname`, `caption`, `description`, `datatype`, `format`) VALUES
(NULL, 'user_mobile_phone', 'User Mobile Phone', NULL, 'Icinga\\Module\\Director\\DataType\\DataTypeString', NULL),
(NULL, 'user_phone', 'User Phone', NULL, 'Icinga\\Module\\Director\\DataType\\DataTypeString', NULL),
(NULL, 'user_alias', 'User Alias', NULL, 'Icinga\\Module\\Director\\DataType\\DataTypeString', NULL);
```

Configure the Module: Define mapping to created variables. 
Access the configuration area for the modules, access ondutymanager and go to TAB: configuration. 
To go there you have to go to:  Menu point: Configuration -> Modules -> Ondutymanager -> Tab: Configuration
Define:
```
User alias: user_alias
User phone number: user_phone
User phone number suffix: -F
User mobile phone number: user_mobile_phone
User mobile phone number suffix: -H
Value not used: (Users group: user_sms_group)
```

The mapping of the users to the group is done via usergrup assignment of user. 
The user must also contain a usergroup. Later the team in the ondutymanager-module will take the user of a team from this usergroup.
1. Define a usergroup
2. Assign users to usergroup
3. Define for a ondutymanager team the usergroup.

Insert all values and you should be good to start!

## Test
1. Open your Neteye-Web-Interface
2. Authenticate to enter
3. Check if on the left menu the module "Ondutymanager" appears.
4. Click on it, fill the editor with some object if you have the permission and create a new week.
