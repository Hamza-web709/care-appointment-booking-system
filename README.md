# CARE - Medical Appointment System

CARE is a web-based medical appointment and information system built with Core PHP and MySQL. The system allows patients to search doctors, book appointments, manage profiles, and view health-related information. It also includes separate dashboards for Admin, Doctor, and Patient roles.

## Project Overview

This project is designed to make the medical appointment process simple and organized. Patients can create an account, search doctors by city or specialization, check doctor availability, and book appointments online. Doctors can manage their availability and appointments, while the admin can manage users, doctors, patients, appointments, cities, diseases, and medical news.

## Features

### Public Website

* Home page with healthcare platform introduction
* Doctor listing page
* Disease information page
* Latest health news page
* Login and registration system
* Forgot password and reset password system

### Patient Panel

* Patient dashboard
* Search doctors
* Book appointment
* View appointment history
* Manage patient profile
* Upload profile image

### Doctor Panel

* Doctor dashboard
* Manage appointment requests
* Manage availability slots
* Update doctor profile
* Upload profile image

### Admin Panel

* Admin dashboard
* Manage users
* Manage doctors
* Manage patients
* Manage appointments
* Manage cities
* Manage diseases
* Manage medical news

## Technologies Used

* Core PHP
* MySQL
* MySQLi
* HTML5
* CSS3
* JavaScript
* Bootstrap 5
* Bootstrap Icons
* Font Awesome
* XAMPP / Apache Server

## Project Folder Structure

```text
care/
│
├── admin/
│   ├── add_user.php
│   ├── appointments.php
│   ├── cities.php
│   ├── dashboard.php
│   ├── diseases.php
│   ├── doctors.php
│   ├── news.php
│   ├── patients.php
│   └── users.php
│
├── assets/
│   ├── css/
│   │   ├── header.css
│   │   └── style.css
│   ├── js/
│   │   ├── header.js
│   │   └── script.js
│   └── img/
│
├── config/
│   └── db.php
│
├── database/
│   └── care_project.sql
│
├── doctor/
│   ├── appointments.php
│   ├── availability.php
│   ├── dashboard.php
│   └── profile.php
│
├── includes/
│   ├── auth.php
│   ├── footer.php
│   └── header.php
│
├── patient/
│   ├── appointments.php
│   ├── book_appointment.php
│   ├── dashboard.php
│   ├── profile.php
│   └── search_doctors.php
│
├── uploads/
│   ├── news/
│   └── profiles/
│
├── diseases.php
├── doctors.php
├── forgot_password.php
├── index.php
├── login.php
├── logout.php
├── news.php
├── register.php
├── reset_password.php
└── README.md
```

## Database Tables

The project uses the following main database tables:

* admins
* users
* doctors
* patients
* appointments
* availability
* cities
* diseases
* cures
* preventions
* medical_news

## Installation Guide

Follow these steps to run the project locally.

### 1. Download or Clone the Project

```bash
git clone https://github.com/your-username/care.git
```

Or download the ZIP file and extract it.

### 2. Move Project to XAMPP htdocs

Move the project folder into:

```text
C:/xampp/htdocs/
```

Example:

```text
C:/xampp/htdocs/care/
```

### 3. Start XAMPP

Open XAMPP Control Panel and start:

```text
Apache
MySQL
```

### 4. Create Database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Create a new database:

```text
care_project
```

### 5. Import Database

Import the SQL file located at:

```text
database/care_project.sql
```

### 6. Configure Database Connection

Open this file:

```text
config/db.php
```

Update database credentials if needed:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'care_project');
```

Also update the base URL according to your local folder name:

```php
define('BASE_URL', 'http://localhost/care/');
```

If your folder name is `care_project`, use:

```php
define('BASE_URL', 'http://localhost/care_project/');
```

### 7. Run the Project

Open your browser and visit:

```text
http://localhost/care/
```

## Login System

The system supports three roles:

```text
Admin
Doctor
Patient
```

Patients and doctors can register from the registration page. Admin users can manage the complete system from the admin dashboard.

## Project Screenshots

### Landing Page

<img src="screenshots/landing-page.png" alt="CARE Landing Page" width="100%">

---

### Login Page

<img src="screenshots/login-page.png" alt="CARE Login Page" width="100%">

---

### Patient Registration

<img src="screenshots/patient-reg.png" alt="Patient Registration Page" width="100%">

---

### Doctor Registration

<img src="screenshots/doctor-reg.png" alt="Doctor Registration Page" width="100%">

---

### Doctors Listing Page

<img src="screenshots/doctors.png" alt="Doctors Listing Page" width="100%">

---

### Diseases Page

<img src="screenshots/diseses.png" alt="Diseases Page" width="100%">

---

### Book Appointment Page

<img src="screenshots/book-appointment.png" alt="Book Appointment Page" width="100%">

---

## Patient Panel Screenshots

### Patient Dashboard

<img src="screenshots/patients-dashboard.png" alt="Patient Dashboard" width="100%">

---

### Patient Profile

<img src="screenshots/patients-profile.png" alt="Patient Profile" width="100%">

---

### Patient Appointments

<img src="screenshots/patient-appointment.png" alt="Patient Appointments" width="100%">

---

## Doctor Panel Screenshots

### Doctor Dashboard

<img src="screenshots/doctor-dashboard.png" alt="Doctor Dashboard" width="100%">

---

### Doctor Profile

<img src="screenshots/doctor-profile.png" alt="Doctor Profile" width="100%">

---

### Doctor Availability

<img src="screenshots/doctor-availability.png" alt="Doctor Availability" width="100%">

---

### Doctor Appointments

<img src="screenshots/doctor-appointment.png" alt="Doctor Appointments" width="100%">

---

## Admin Panel Screenshots

### Manage Users

<img src="screenshots/manage-users.png" alt="Manage Users" width="100%">

---

### Manage Doctors

<img src="screenshots/manage-doctors.png" alt="Manage Doctors" width="100%">

---

### Manage Patients

<img src="screenshots/manage-patients.png" alt="Manage Patients" width="100%">

---

### Manage Cities

<img src="screenshots/manage-cities.png" alt="Manage Cities" width="100%">

---

### Manage Diseases

<img src="screenshots/manage-disease.png" alt="Manage Diseases" width="100%">

---

### Medical News Page

<img src="screenshots/medical-news.png" alt="Medical News Page" width="100%">

---

### Manage Medical News

<img src="screenshots/manage-medical-news.png" alt="Manage Medical News" width="100%">


## Important Notes

* Make sure Apache and MySQL are running before opening the project.
* Make sure the database name in `config/db.php` matches your phpMyAdmin database name.
* Make sure the `uploads/news/` and `uploads/profiles/` folders exist for image uploads.
* If pages are not opening correctly, check the `BASE_URL` value in `config/db.php`.

## Future Improvements

* Add email verification
* Add appointment approval notifications
* Add online payment system
* Add doctor rating and review system
* Add advanced search filters
* Add responsive admin dashboard improvements
* Add API support for mobile app integration

## Author

Developed by **M. Hamza Muqtaddir**

## License

This project is created for educational and portfolio purposes.
