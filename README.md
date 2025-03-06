# Crossword Puzzle Application

A modern web application for creating and solving crossword puzzles, built with Laravel and Tailwind CSS.

## 🎯 Features

- **User Authentication**
  - Custom login and registration with crossword-themed UI
  - Admin and regular user roles
  - Phone and company information collection

- **Puzzle Management**
  - Create and manage crossword puzzles (Admin)
  - Set puzzle difficulty and time limits
  - Automatic puzzle grid generation
  - Active/Inactive puzzle status

- **Gameplay**
  - Interactive crossword puzzle solving
  - Real-time answer validation
  - Timer functionality
  - Auto-save progress
  - Mobile-responsive design

- **Scoring System**
  - Points based on completion time
  - Global leaderboard
  - Puzzle-specific leaderboards
  - User progress tracking

## 🔧 Requirements

- PHP 8.2+
- MySQL 5.7+
- Composer
- Node.js & NPM
- Laravel 10.x

## 📦 Installation

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd crossword
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install Node Dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database**
   - Update `.env` with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=crossword
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run Migrations and Seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Create Admin User**
   ```bash
   php artisan user:make-admin user@example.com
   ```

8. **Compile Assets**
   ```bash
   npm run dev
   ```

9. **Start the Server**
   ```bash
   php artisan serve
   ```

## 🚀 Usage

### Admin Access
1. Login with admin credentials
2. Access admin dashboard via `/admin`
3. Create and manage puzzles
4. View global leaderboard

### User Access
1. Register a new account
2. Browse available puzzles
3. Play puzzles and track progress
4. View scores on leaderboard

## 📱 Game Features

### Puzzle Solving
- Click on cells to enter letters
- Use arrow keys for navigation
- Auto-validation of answers
- Timer tracking for scoring

### Scoring System
- Points based on:
  - Completion time
  - Correct answers
  - Puzzle difficulty

## 🔐 Security Features

- CSRF Protection
- XSS Prevention
- Form Validation
- Secure Password Handling
- Role-based Access Control

## 🎨 Customization

### Theme Customization
- Modify `resources/css/app.css` for styling
- Update Tailwind configuration in `tailwind.config.js`

### Game Settings
- Adjust time limits in puzzle creation
- Modify scoring algorithm in `GameController`
- Customize grid sizes

## 🔄 Database Structure

### Key Tables
- `users` - User information and roles
- `puzzles` - Crossword puzzle data
- `clues` - Puzzle clues and answers
- `user_scores` - User progress and scores

## 🛠️ Development

### Commands
```bash
# Run tests
php artisan test

# Clear cache
php artisan cache:clear

# Create new puzzle
php artisan make:puzzle

# Update leaderboard
php artisan update:leaderboard
```

### Key Files
- `app/Http/Controllers/GameController.php` - Game logic
- `app/Services/CrosswordGenerator.php` - Puzzle generation
- `resources/views/game/` - Game views
- `resources/js/crossword.js` - Frontend logic

## 📝 Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 🐛 Troubleshooting

### Common Issues
1. **Database Connection**
   - Verify `.env` credentials
   - Check MySQL service status

2. **Compilation Issues**
   ```bash
   npm run dev -- --clean
   composer dump-autoload
   ```

3. **Permission Issues**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## 👥 Authors

- Your Name - *Initial work*

## 🙏 Acknowledgments

- Laravel Team
- Tailwind CSS
- All contributors
