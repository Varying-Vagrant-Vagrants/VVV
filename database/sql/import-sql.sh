#!/bin/bash
#
# Import provided SQL files in to MariaDB/MySQL.
#
# The files in the {vvv-dir}/database/backups/ directory should be created by
# mysqldump or some other export process that generates a full set of SQL commands
# to create the necessary tables and data required by a database.
#
# For an import to work properly, the SQL file should be named `db_name.sql` in which
# `db_name` matches the name of a database already created in {vvv-dir}/database/init-custom.sql
# or {vvv-dir}/database/sql/init.sql.
#
# If a filename does not match an existing database, it will not import correctly.
#
# If tables already exist for a database, the import will not be attempted again. After an
# initial import, the data will remain persistent and available to MySQL on future boots
# through {vvv-dir}/database/data
#
# Let's begin...
set -eo pipefail
set -u

if [ -z "${VVV_LOG+x}" ]; then
  export VVV_LOG=""
fi

source /srv/provision/provision-helpers.sh

DEFAULT_SKIP_DBS=(
  "mysql"
  "information_schema"
  "performance_schema"
  "sys"
  "test"
  "phpmyadmin"
  "wordpress_unit_tests"
)

VVV_CONFIG=/srv/config/default-config.yml
if [[ -f /srv/config/config.yml ]]; then
	VVV_CONFIG=/srv/config/config.yml
fi

FORCE_RESTORE="0"
POSITIONAL_ARGS=()
while [[ $# -gt 0 ]]; do
  case $1 in
    -f|--force) # quick mode
      FORCE_RESTORE="1"
      shift # past argument
      ;;
    --*|-*)
      echo "Unknown option $1"
      exit 1
      ;;
    *)
      POSITIONAL_ARGS+=("$1") # save positional arg
      shift # past argument
      ;;
  esac
done
set -- "${POSITIONAL_ARGS[@]}" # restore positional parameters

run_restore=$(shyaml get-value general.db_restore 2> /dev/null < ${VVV_CONFIG})
exclude_list=$(get_config_values "general.db_restore.exclude")
include_list=$(get_config_values "general.db_restore.include")
restore_by_default=$(get_config_values "general.db_restore.restore_by_default")
ALL_EXCLUDES=("${DEFAULT_SKIP_DBS[@]}" "${exclude_list[@]}")

should_skip_db() {
  local db="${1}"
  if [[ " ${ALL_EXCLUDES[*]} " =~ $db ]]; then
    return 0
  fi
  if [[ "$restore_by_default" == "true" ]]; then
    return 0
  fi
  for include in "${include_list[@]}"; do
    [[ "${include}" == "${db}" ]] && return 1
  done
  return 1
}

skipped=()
imported=()
failed=()

if [[ $run_restore == "False" ]]
then
	vvv_info " * Skipping DB import script, disabled via the VVV config file"
	exit;
fi

# Move into the newly mapped backups directory, where mysqldump(ed) SQL files are stored
vvv_info " * Starting MariaDB Database Import"
# create the backup folder if it doesn't exist
mkdir -p /srv/database/backups
cd /srv/database/backups/

SAVEIFS=$IFS
IFS=$(echo -en "\n\b")

# Parse through each file in the directory and use the file name to
# import the SQL file into the database of the same name
sql_count=$(ls -1 ./*.sql* 2>/dev/null | wc -l)
vvv_info " * Found ${sql_count} database dumps"
if [ "$sql_count" != 0 ]; then
	for file in $( ls ./*.sql* )
	do
		# get rid of the extension
		db_name=$(basename "${file}" .sql)
    if [[ "$file" == *.sql.gz ]]; then
      db_name=$(basename "$file" .sql.gz)
      if ! file "$file" | grep -q 'gzip compressed'; then
        vvv_warning " ! ${file} has a gzip extension but gunzip reports it is not a compressed gzip, proceed with caution."
      fi
    elif [[ "$file" == *.sql ]]; then
      head -n 1 "${file}" | grep -qE '^(--|CREATE|INSERT|DROP|USE|SET)' || {
        vvv_warning " * Skipping suspicious file: ${file}"
        continue
      }
      db_name=$(basename "$file" .sql)
    else
      vvv_info " * Skipping unrecognized file format: ${file}"
      continue
    fi

    # Skip if db is in skip list
    if should_skip_db "${db_name}"; then
      skipped+=("${db_name}")
      vvv_info " * skipped <b>${db_name}</b>" && continue;
    fi

    vvv_info " * Processing <b>${db_name}</b><info>:"

		# if we specified databases, only restore specified ones
		if [[ "${#@}" -gt 0 ]]; then
			FOUND=0
			for var in "$@"; do
				if [[ "${var}" == "${db_name}" ]]; then
					FOUND=1
					break;
				fi
			done
			if [[ "${FOUND}" -eq 0 ]]; then
        skipped+=("${db_name}")
				continue;
			fi
		fi

		if [ "1" == "${FORCE_RESTORE}" ]; then
			vvv_info "   - Forcing restore of <b>${db_name}</b><info> database, and granting the wp user access"
			mysql -e "DROP DATABASE IF EXISTS \`${db_name}\`"
		else
			vvv_info "   - Creating the <b>${db_name}</b><info> database if it doesn't already exist, and granting the wp user access"
		fi

		mysql -e "CREATE DATABASE IF NOT EXISTS \`${db_name}\`"
		mysql -e "GRANT ALL PRIVILEGES ON \`${db_name}\`.* TO wp@localhost IDENTIFIED BY 'wp';"

		mysql_cmd="SHOW TABLES FROM \`${db_name}\`" # Required to support hyphens in database names
		db_has_tables=$(mysql --skip-column-names -e "${mysql_cmd}")
		if [ $? -gt 0 ]; then
      failed+=("${db_name}")
      vvv_info "${db_has_tables}"
			vvv_error "   ! Error - Checking if the <b>${db_name}</b><error> database already contains tables failed!"
		else
			if [ "" == "${db_has_tables}" ]; then
				vvv_info "   - Importing <b>${db_name}</b><info> from <b>${file}</b>"
				if [ "${file: -3}" == ".gz" ]; then
          if ! gunzip < "${file}" | mysql "${db_name}"; then
            failed+=("${db_name}")
            vvv_error "   ! Import failed for ${db_name} from ${file}"
            continue
          fi
				else
          if ! mysql "${db_name}" < "${file}"; then
            failed+=("${db_name}")
            vvv_error "   ! Import failed for ${db_name} from ${file}"
            continue
          fi
				fi
				vvv_success "   - Import of <b>'${db_name}'</b><success> successful</success>"
        imported+=("${db_name}")
			else
				vvv_info "   - Skipped import of <b>\`${db_name}\`</b><info> - tables already exist"
        skipped+=("${db_name}")
			fi
		fi
	done
else
	vvv_success " * No custom databases to import"
fi

vvv_success " * Database import script finished"

IFS=","
if [ ${#imported[@]} -gt 0 ]; then
  vvv_success " * Imported databases: <b>${imported[*]}</>"
fi

if [ ${#skipped[@]} -gt 0 ]; then
  vvv_info " * Skipped databases: <b>${skipped[*]}</>"
fi

if [ ${#failed[@]} -gt 0 ]; then
  vvv_error " * Failed databases: <b>${failed[*]}</>"
fi

IFS=$SAVEIFS
