# HR Management System

A comprehensive Human Resources Management System built with PHP, Oracle Database, and modern frontend technologies.

## 🚀 Features

### Core Modules
- **Employee Management** - Manage permanent and contract employees with complete profiles
- **Department Management** - Organize departments with budgets and managers
- **Position Management** - Define job roles with salary ranges
- **Qualification Management** - Track certifications and assign to employees
- **Attendance Tracking** - Check-in/out system with automatic hours calculation
- **Payroll Processing** - Handle salaries, deductions, and payment records
- **Leave Management** - Request and approval workflow for employee leaves
- **Performance Evaluation** - Assess employee performance with scoring system

### Key Features
- ✅ Full CRUD operations for all modules
- ✅ Real-time API integration
- ✅ Responsive design (mobile & desktop)
- ✅ Interactive dashboard with statistics
- ✅ Filter and search functionality
- ✅ Form validation and error handling
- ✅ Modern UI with Tailwind CSS

## 🛠️ Technology Stack

### Backend
- **PHP** - OOP architecture with controllers and models
- **Oracle Database 23c** - Enterprise database with OCI8 driver
- **RESTful API** - JSON-based API endpoints

### Frontend
- **HTML5** - Semantic markup
- **Tailwind CSS** - Modern utility-first CSS framework
- **Vanilla JavaScript** - Clean, dependency-free code
- **Font Awesome** - Icon library

## 📋 Prerequisites

- PHP 7.4 or higher
- Oracle Database 23c
- OCI8 PHP extension
- Web server (Apache/Nginx)
- Modern web browser

## 🔧 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/hr-management-system.git
   cd hr-management-system
   ```

2. **Database Setup**
   - Import the database schema:
     ```sql
     @database/create_tables.sql
     ```
   - Configure database connection in `config/config.php` and `config/database.php`

3. **Configure API Base URL**
   - Update `viewsV2/app.js` if your installation path differs:
     ```javascript
     API_BASE: 'http://localhost/YOUR_PROJECT_PATH/api'
     ```

4. **Start your web server**
   - Place the project in your web root directory
   - Access via: `http://localhost/YOUR_PROJECT_PATH/viewsV2/index.html`

## 📁 Project Structure

```
├── api/                    # RESTful API endpoints
│   ├── attendance/
│   ├── departments/
│   ├── employees/
│   ├── leave/
│   ├── payroll/
│   ├── performance-evaluation/
│   ├── positions/
│   └── qualifications/
├── classes/               # PHP OOP classes
│   ├── controllers/      # Business logic controllers
│   ├── models/          # Data models
│   ├── Database.php     # Database connection handler
│   └── Response.php     # API response handler
├── config/               # Configuration files
├── database/            # SQL schema files
├── viewsV2/             # Frontend application
│   ├── index.html      # Dashboard
│   ├── app.js          # Global utilities
│   ├── sidebar.html    # Navigation component
│   └── *.html          # Module pages
└── .gitignore          # Git ignore rules
```

## 🎯 Usage

### Dashboard
Access the main dashboard at `viewsV2/index.html` to see:
- Total employees, departments, positions
- Pending leave requests
- Today's attendance
- Quick action buttons

### Module Navigation
Use the sidebar to navigate between different modules:
- Employees - Add/Edit/Delete employee records
- Departments - Manage organizational units
- Positions - Define job roles and salaries
- Qualifications - Track employee certifications
- Attendance - Record check-ins and check-outs
- Payroll - Process salary payments
- Leave - Manage leave requests
- Evaluation - Conduct performance reviews

## 🔒 Security Notes

- **Important**: Never commit `config/config.php` or `config/database.php` with real credentials
- Update database credentials for your environment
- Implement proper authentication before production use
- Sanitize all user inputs (additional validation recommended)

## 📝 API Endpoints

All endpoints return JSON responses:

```
GET  /api/{module}/list.php        - Get all records
GET  /api/{module}/get.php?id=X    - Get single record
POST /api/{module}/create.php      - Create new record
POST /api/{module}/update.php      - Update existing record
POST /api/{module}/delete.php      - Delete record
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 👥 Team

Developed as part of ICT502 Group Project

## 📞 Support

For issues and questions, please open an issue on GitHub.
