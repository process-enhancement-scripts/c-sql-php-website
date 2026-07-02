# Anime Character Tier List Website

A web application where you can rank your favorite anime characters in a tier list (S, A, B, C, D, E, F).

## Features

- ✨ Interactive web-based tier list interface
- 🎨 Beautiful responsive design with gradient backgrounds
- 🗄️ MySQL database backend for persistent storage
- 🚀 Easy character ranking system
- 🔄 Reset and save functionality
- 📱 Mobile-friendly interface

## Project Structure

```
├── index.html              # Main web interface
├── api.php                 # Backend API endpoints
├── display.php             # Legacy tier list display
├── main.cpp                # C++ console application (optional)
├── install                 # Install dependencies script
├── launch                  # Start development server script
├── init_database.sh        # Initialize MySQL database
└── README.md               # This file
```

## Prerequisites

- **PHP** 7.0 or higher
- **MySQL** 5.7 or higher
- **Linux/macOS** or Windows with WSL/Git Bash

## Installation

### 1. Install Dependencies

```bash
bash install
```

Or manually install:
```bash
sudo apt-get update
sudo apt-get install -y php mysql-server php-mysql
```

### 2. Initialize Database

```bash
bash init_database.sh
```

This will:
- Stop existing MySQL processes
- Start MySQL in safe mode
- Set root password to `root`
- Create the `anime_tier_db` database
- Create necessary tables
- Insert sample anime characters

### 3. Start Development Server

```bash
bash launch
```

Or manually:
```bash
php -S localhost:8000
```

### 4. Access the Website

Open your browser and navigate to:
```
http://localhost:8000
```

## Usage

### Adding Characters to Tiers

1. Select a character from the dropdown under each tier
2. The character will be immediately assigned to that tier
3. Characters can be moved between tiers by removing and re-adding

### Removing Characters

- Click the ✕ button next to any character to remove it from that tier
- The character will return to the unranked pool

### Resetting

- Click "🔄 Reset All" to clear all tier assignments
- You'll be prompted to confirm before clearing

### Saving

- Click "💾 Save Tier List" to confirm your ranking (data is saved to database automatically)

## Database Schema

### Characters Table
```sql
CREATE TABLE characters (
    character_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL
);
```

### Tier Assignments Table
```sql
CREATE TABLE tier_assignments (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    character_id INT,
    tier_rank CHAR(1) NOT NULL,
    assignment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(character_id)
);
```

## API Endpoints

All API endpoints return JSON responses.

### Get Tier List
```
GET /api.php?action=get_tier_list
```
Returns all characters and current tier assignments.

### Assign Character to Tier
```
POST /api.php?action=assign_tier
Parameters: character_id (int), tier (char: S,A,B,C,D,E,F)
```

### Remove Character from Tier
```
POST /api.php?action=remove_tier
Parameters: character_id (int)
```

### Reset All Assignments
```
POST /api.php?action=reset_all
```

## Configuration

To modify database credentials, edit `api.php` and `display.php`:

```php
$db = new PDO('mysql:host=localhost;dbname=anime_tier_db', 'root', 'root');
```

Change:
- `host` - MySQL server address (default: localhost)
- `dbname` - Database name (default: anime_tier_db)
- `username` - MySQL username (default: root)
- `password` - MySQL password (default: root)

## Customization

### Adding More Characters

Connect to MySQL and insert new characters:
```bash
mysql -u root -proot anime_tier_db -e "
    INSERT INTO characters (name) VALUES ('Character Name');
"
```

### Changing Tier Colors

Edit the color scheme in `index.html` CSS section:
```css
.tier-s { background: #ff6b6b; }  /* S tier color */
.tier-a { background: #ffa500; }  /* A tier color */
/* etc. */
```

## Troubleshooting

### Database Connection Failed
- Ensure MySQL is running: `sudo service mysql status`
- Check credentials in `api.php`
- Verify database exists: `mysql -u root -proot -e "SHOW DATABASES;"`

### Characters Not Loading
- Check PHP error logs
- Verify database tables exist: `mysql -u root -proot anime_tier_db -e "SHOW TABLES;"`
- Ensure PHP MySQL extension is installed: `php -m | grep -i mysql`

### Port Already in Use
Use a different port:
```bash
php -S localhost:8080
```

## Performance Notes

- Tier assignments are saved to database immediately (no manual save required)
- The website is optimized for modern browsers
- Supports up to 1000+ characters efficiently

## Future Enhancements

- [ ] User accounts and authentication
- [ ] Image support for characters
- [ ] Tier list sharing/export functionality
- [ ] Search and filter options
- [ ] Drag-and-drop interface
- [ ] Dark mode toggle
- [ ] Mobile app

## License

See LICENSE file for details.

## Support

For issues or questions, check the README or review the database initialization logs.
