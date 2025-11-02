# 📝 TO-DO List Application (PHP MVC)

## 📄 Description – Exercise Statement

This project is part of a Full Stack Developer Bootcamp. The goal is to build a **To-Do List** application using the MVC architecture pattern in PHP.

The application allows users to:

- Create tasks
- View all tasks or a specific one
- Update tasks
- Delete tasks

Each task includes a title, description, status (Pending, In Progress, Completed), timestamps (start and end), and the user who created it.

This project follows Gitflow, is developed collaboratively via GitHub, and evolves across 3 levels of data persistence (only JSON file available for now):

1. **JSON file**
2. **MySQL database**
3. **MongoDB (optional final stage)**

---

## 💻 Technologies Used

- PHP 7+
- MVC Architecture (custom framework)
- MySQL (PDO)
- Tailwind CSS
- Git & Gitflow
- JSON (for Level 1 persistence)
- Apache (via XAMPP or similar)
- HTML & basic JavaScript

---

## 📋 Requirements

- PHP ≥ 7.4
- Composer
- MySQL ≥ 8.0 (for Level 2)
- Node.js (only for Tailwind build)
- XAMPP or another local server
- Git (with Gitflow if possible)

Optional:

- MongoDB (for Level 3)

---

## 🛠️ Installation

1. **Clone the repository**
   git clone https://github.com/ascargo/S3.02.Developers-Team.git
   Go to the project directory

cd your-todo-project
Set up Tailwind CSS

npm install
npx tailwindcss -i ./public/input.css -o ./public/output.css --watch
Set up database connection (if using MySQL)
Edit config/settings.ini with your MySQL credentials.

(Optional) Start local server
If using XAMPP, place the folder in:

/Applications/XAMPP/xamppfiles/htdocs/
and run the server from the control panel.

▶️ Execution
Go to http://localhost/your-todo-project/public in your browser.

You will see the task list, and can use the UI to add, edit, or delete tasks.

🌐 Deployment
To deploy the project in a production environment:

Upload project files to your server (e.g., using FTP).

Configure settings.ini with production database credentials.

Ensure the server supports PHP and has MySQL or MongoDB installed.

Set up proper permissions for file writing (for JSON persistence if used).

Point your domain to the /public directory of the project.

🤝 Contributions
Contributions are welcome! Please follow these steps:

Fork the repository

Create a new branch:
git checkout -b feature/NewFeature

Make your changes and commit:
git commit -m 'Add New Feature'

Push your changes:
git push origin feature/NewFeature
Open a Pull Request for review
