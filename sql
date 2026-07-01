%%bash
# Phase 1: Ensure MySQL is completely stopped and all related processes are gone
echo "Stopping MySQL service and killing all mysqld processes..."
service mysql stop
sleep 20 # Give even more time for service to stop gracefully
pkill -9 mysqld || true # Force kill any lingering mysqld processes, ignore if none exist
sleep 20 # Give even more time for processes to truly terminate

# Phase 2: Start in safe mode, set password
echo "Starting mysqld_safe in skip-grant-tables mode..."
mysqld_safe --skip-grant-tables & # Run in background
mysqld_safe_pid=$! # Capture PID of mysqld_safe
echo "mysqld_safe started with PID $mysqld_safe_pid"
sleep 30 # Plenty of time for mysqld_safe to fully start its child mysqld process

# Now connect to the running mysqld_safe instance to set password
echo "Setting root password and flushing privileges..."
mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'root'; FLUSH PRIVILEGES;"
sleep 10 # Give ample time for privileges to flush and persist

# Phase 3: Aggressively stop the safe-mode instance and start normal service
echo "Aggressively stopping safe-mode instance and restarting MySQL service normally..."

# Kill all mysqld processes associated with the safe mode start
pkill -9 mysqld || true
sleep 20 # Give ample time for processes to truly terminate

# Verify that no mysqld processes are running. This is critical.
echo "Verifying all mysqld processes are terminated before normal service start..."
for i in {1..20}; do # Increased attempts
    if pgrep mysqld > /dev/null; then
        echo "mysqld process still active after aggressive kill, waiting... (attempt $i)"
        sleep 3
    else
        echo "All mysqld processes terminated successfully."
        break
    fi
done

if pgrep mysqld > /dev/null; then
    echo "CRITICAL WARNING: mysqld processes still active after extensive shutdown attempts. Unable to proceed safely."
    exit 1 # Abort if we can't kill all processes.
fi

# Ensure systemd is reloaded, just in case.
systemctl daemon-reload || true # Ignore error if systemctl is not available/working perfectly

# Start MySQL service normally
echo "Starting MySQL service normally via service manager..."
service mysql start
sleep 40 # Give MySQL much more initial time to start fully in normal mode

echo "Waiting for MySQL to start in normal mode and 'skip_grant_tables' to be OFF and accepting connections..."
for i in {1..60}; do # Even higher number of attempts (up to 300 seconds total wait)
    # Check if MySQL is running, skip_grant_tables is OFF, and a simple query works
    if mysql -u root -proot -e "SHOW VARIABLES LIKE 'skip_grant_tables';" 2>/dev/null | grep -q "skip_grant_tables\\s*OFF" && \
       mysql -u root -proot -e "SELECT 1;" >/dev/null 2>&1; then
        echo "MySQL is fully ready in normal mode, 'skip_grant_tables' is OFF, and accessible."
        break
    else
        echo "MySQL not yet fully ready/accessible, or 'skip_grant_tables' is still ON. Waiting... (attempt $i)"
        sleep 5
    fi
done

# Final check before proceeding with DDL/DML
if ! mysql -u root -proot -e "SHOW VARIABLES LIKE 'skip_grant_tables';" 2>/dev/null | grep -q "skip_grant_tables\\s*OFF" || \
   ! mysql -u root -proot -e "SELECT 1;" >/dev/null 2>&1; then
    echo "FATAL: Failed to confirm MySQL running in normal mode with 'skip_grant_tables' OFF and accessible after multiple attempts. Aborting DDL/DML."
    if ! service mysql status > /dev/null 2>&1; then
        echo "MySQL service appears to be completely down, or unable to start in normal mode."
    fi
    exit 1
fi

echo "MySQL root password set to 'root' and service restarted."
echo "Successfully connected with new password and in normal mode."

# Phase 4: Create database and tables (only if connection was successful)
mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS anime_tier_db;"
mysql -u root -proot anime_tier_db -e "
    CREATE TABLE IF NOT EXISTS characters (
        character_id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL
    );
    CREATE TABLE IF NOT EXISTS tier_assignments (
        assignment_id INT PRIMARY KEY AUTO_INCREMENT,
        character_id INT,
        tier_rank CHAR(1) NOT NULL,
        assignment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (character_id) REFERENCES characters(character_id)
    );
"

# Insert sample character data
mysql -u root -proot anime_tier_db -e "
    INSERT IGNORE INTO characters (character_id, name) VALUES (1, 'Eren Yeager');
    INSERT IGNORE INTO characters (character_id, name) VALUES (2, 'Levi Ackerman');
    INSERT IGNORE INTO characters (character_id, name) VALUES (3, 'Mikasa Ackerman');
    INSERT IGNORE INTO characters (character_id, name) VALUES (4, 'Gojo Satoru');
    INSERT IGNORE INTO characters (character_id, name) VALUES (5, 'Sukuna');
    INSERT IGNORE INTO characters (character_id, name) VALUES (6, 'Monkey D. Luffy');
    INSERT IGNORE INTO characters (character_id, name) VALUES (7, 'Roronoa Zoro');
    INSERT IGNORE INTO characters (character_id, name) VALUES (8, 'Nami');
    INSERT IGNORE INTO characters (character_id, name) VALUES (9, 'Naruto Uzumaki');
    INSERT IGNORE INTO characters (character_id, name) VALUES (10, 'Sasuke Uchiha');"
