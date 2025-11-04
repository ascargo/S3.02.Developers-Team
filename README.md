# 📝 TO-DO List Application (PHP MVC)

## 📄 Description – Exercise Statement

This project is a PHP MVC application developed as part of the Developers Team exercise part of a Full Stack Developer Bootcamp. It allows users to manage a list of tasks, including adding, viewing, updating and deleting them.
The app stores task data in a JSON file and displays them in three columns: To do, In progress, and Done.
It aims to practise object-oriented PHP, MVC architecture, routing, and file persistence.

💻 Technologies Used

PHP (Object-Oriented Programming, MVC pattern)
HTML & CSS
Tailwind CSS (styling framework)
JSON (for local data persistence)
Apache (via XAMPP for local server)
Git & GitHub (version control)

📋 Requirements

To run this project locally, you need:
PHP ≥ 8.0
Apache (included with XAMPP or MAMP)
A web browser
Git (for cloning the repository)

🛠️ Installation

Clone the repository
git clone https://github.com/ascargo/S3.02.Developers-Team.git

Move the project to your server root
For example, on macOS with XAMPP: /Applications/XAMPP/xamppfiles/htdocs/

If there are any issues with saving the task this steps might help:
Create the data folder if missing
mkdir -p data
echo "[]" > data/tasks.json

Ensure write permissions
chmod 777 data
chmod 666 data/tasks.json

Execution

Start Apache from your XAMPP (or MAMP) control panel.

Open your browser and go to:
http://localhost/<folder-name>/web
for example, as my project is saved in cursoPHP folder:
http://localhost/cursoPHP/S3.02.Developers-Team/web/

The application should display the three task columns.
You can now add, edit, and delete tasks directly from the interface.

🌐 Deployment

To deploy the app to a production environment:
Upload all project files to your web server (e.g., /var/www/html/).
Make sure the web server user (e.g., www-data) has write access to /data/tasks.json.
Confirm your server runs PHP 8 or later.
Access the deployed app via your server’s domain or IP address.

🤝 Contributions

Contributions are welcome. Please follow these steps:
Fork the repository.

Create a new branch for your feature:
git checkout -b feature/NewFeature

Commit your changes:
git commit -m "Add NewFeature"

Push the branch:
git push origin feature/NewFeature
Open a Pull Request and describe your update clearly.
