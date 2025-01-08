# RealEstate Web Application

Welcome to the **RealEstate** web application! This platform is designed to streamline the process of managing and exploring real estate properties, catering to both agents and buyers. This README provides an overview of the project's features, installation steps, usage guidelines, and other essential information to help administrators effectively manage and maintain the application.

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Features](#features)
   - [Agent Features](#agent-features)
   - [Buyer Features](#buyer-features)
3. [Architecture](#architecture)
4. [File Structure](#file-structure)
5. [Installation & Setup](#installation--setup)
6. [Usage](#usage)
7. [Security Considerations](#security-considerations)
8. [Technologies Used](#technologies-used)
9. [Contribution Guidelines](#contribution-guidelines)
10. [License](#license)
11. [Contact](#contact)

---

## Project Overview

**RealEstate** is a comprehensive web application built using **PHP** and **MySQL**, aimed at facilitating seamless interactions between real estate agents and buyers. Agents can manage property listings, generate reports, and monitor inquiries, while buyers can search for properties, mark favorites, and make inquiries directly through the platform.

---

## Features

### Agent Features

- **Dashboard (`index.php`)**
  - Overview of property statistics including total, featured, available, and rental properties.
  - Quick access to property management sections and reports.

- **Add Property (`add-property.php`)**
  - Form to add new properties with details such as title, description, bedrooms, bathrooms, area, price, location, status, and photos.
  - Image upload functionality with validation for allowed file types.

- **Manage Rental Properties (`rental-properties.php`)**
  - View and manage properties that are currently rented.
  - Option to update property statuses and end rentals.

- **Generate Reports (`reports.php`)**
  - Generate various reports based on property performance, inquiries, and sales.
  - Export reports in formats like PDF and CSV for easy sharing and analysis.

### Buyer Features

- **Search Properties (`buyer/search.php`)**
  - Advanced search functionality with filters for keywords, price range, location, and property type.
  - Pagination to navigate through search results.

- **Property Listings (`buyer/properties-listing.php`)**
  - Browse available properties with options to view details and add to favorites.
  - Real-time favorite toggling with visual feedback.

- **Property Details (`buyer/property-details.php`)**
  - Detailed view of selected properties including images, descriptions, and contact forms.
  - Inquiry submission to contact agents directly from the property page.

- **Favorites (`favorites.php`)**
  - View and manage a list of favorite properties.
  - Remove properties from favorites with confirmation prompts.

- **Manage Inquiries (`buyer/inquiries.php`)**
  - Track and manage all inquiries made by the buyer.
  - View responses from agents and update inquiry statuses.

---

## Architecture

The application follows a **Modular MVC (Model-View-Controller)** architecture to ensure scalability, maintainability, and a clear separation of concerns.

- **Models**: Handle data interactions with the MySQL database.
- **Views**: Present data to users through HTML, CSS, and JavaScript.
- **Controllers**: Manage the flow of data between Models and Views.

Additionally, reusable components like the sidebar (`sidebar.php`) are modularized for consistency across different pages.

---

## File Structure

- **`index.php`**: Dashboard page.
- **`add-property.php`**: Add new property.
- **`rental-properties.php`**: Manage rental properties.
- **`reports.php`**: Generate reports.
- **`buyer/search.php`**: Buyer search page.
- **`buyer/properties-listing.php`**: Buyer property listings page.
- **`buyer/property-details.php`**: Buyer property details page.
- **`favorites.php`**: Buyer favorites page.
- **`buyer/inquiries.php`**: Buyer inquiries page.

---

## Installation & Setup

Follow these steps to set up the **RealEstate** application locally:

1. **Clone the Repository**
   ```bash
   git clone https://github.com/your-username/real-estate-management.git
   ```

2. **Navigate to the Project Directory**
   ```bash
   cd real-estate-management
   ```

3. **Import the Database**
   - Locate the `real_estate.sql` file in the project.
   - Import it into your MySQL database using tools like phpMyAdmin or the MySQL CLI:
     ```bash
     mysql -u your_username -p your_database < real_estate.sql
     ```

4. **Configure Database Connection**
   - Edit the `config/database.php` file with your database credentials:
     ```php:config/database.php
     <?php
     class Database {
         private $host = "localhost";
         private $db_name = "real_estate";
         private $username = "your_database_username";
         private $password = "your_database_password";
         public $conn;

         public function getConnection(){
             $this->conn = null;
             try{
                 $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                       $this->username, 
                                       $this->password);
                 $this->conn->exec("set names utf8");
             } catch(PDOException $exception){
                 echo "Connection error: " . $exception->getMessage();
             }
             return $this->conn;
         }
     }
     ?>
     ```

5. **Start a Local Server**
   - Use tools like **XAMPP**, **WAMP**, or **MAMP** to host the application locally.
   - Ensure the server's document root points to the `real-estate-management` directory.

6. **Access the Application**
   - Open your browser and navigate to `http://localhost/real-estate-management`.
   - Log in using the credentials set up during the database import.

---

## Usage

### Logging In

- **Admin/Agent**:
  - Access the agent dashboard to manage properties and generate reports.
- **Buyer**:
  - Browse, search, and manage favorite properties.
  - Submit inquiries for properties of interest.

### Managing Properties

- **Adding a Property**:
  - Navigate to the "Add Property" section.
  - Fill in all required details and upload property images.
  - Submit the form to add the property to the database.

- **Viewing Rental Properties**:
  - Access the "Rental Properties" section to view all rented listings.
  - Update property statuses as needed.

### Generating Reports

- Navigate to the "Reports" section.
- Select the type of report and desired parameters.
- Generate and download reports in your preferred format.

### Managing Favorites

- Browse properties and click on the heart icon to add to favorites.
- Access the "Favorites" section to view and manage your saved properties.

### Handling Inquiries

- Submit inquiries directly from property detail pages.
- View and track the status of your inquiries in the "Inquiries" section.

---

## Security Considerations

The application incorporates several security measures to ensure data integrity and protect user information:

1. **Input Validation & Sanitization**
   - All user inputs are sanitized using `htmlspecialchars` and other PHP functions to prevent XSS attacks.
     ```php
     <?php echo htmlspecialchars($property['title']); ?>
     ```

2. **Prepared Statements**
   - Database interactions utilize prepared statements to safeguard against SQL injection.
     ```php
     <?php
     $stmt = $db->prepare("SELECT * FROM properties WHERE id = :id");
     $stmt->bindParam(':id', $property_id);
     $stmt->execute();
     ?>
     ```

3. **File Upload Validation**
   - Only allows specific file types for uploads and renames files to prevent malicious uploads.
     ```php:add-property.php
     if (!in_array($file_extension, $allowed_extensions)) {
         throw new Exception('Invalid file type.');
     }
     ```

4. **Session Management**
   - Validates user sessions on every protected page to ensure authenticated access.
   - Sessions are initiated at the beginning of each PHP script using `session_start()`.

5. **Error Handling**
   - Errors are logged using `error_log` instead of being displayed to users, preventing leakage of sensitive information.
     ```php:index.php
     catch (PDOException $e) {
         error_log($e->getMessage());
     }
     ```

6. **Role-Based Access Control**
   - Users are redirected based on their roles to prevent unauthorized access.
     ```php:index.php
     if ($_SESSION['role'] !== 'agent') {
         header("Location: buyer/home.php");
         exit;
     }
     ```

---

## Technologies Used

- **Backend**:
  - PHP
  - MySQL

- **Frontend**:
  - HTML5
  - CSS3
  - JavaScript
  - Bootstrap 5
  - Font Awesome

- **Tools**:
  - phpMyAdmin
  - XAMPP/WAMP/MAMP for local development

---

## Contribution Guidelines

Contributions are welcome! Please follow these steps to contribute to the project:

1. **Fork the Repository**
   - Click the "Fork" button at the top of the repository page to create your own copy.

2. **Clone Your Fork**
   ```bash
   git clone https://github.com/your-username/real-estate-management.git
   cd real-estate-management
   ```

3. **Create a New Branch**
   ```bash
   git checkout -b feature/YourFeatureName
   ```

4. **Make Your Changes**
   - Implement your feature or bug fix.

5. **Commit Your Changes**
   ```bash
   git add .
   git commit -m "Add feature: YourFeatureName"
   ```

6. **Push to Your Fork**
   ```bash
   git push origin feature/YourFeatureName
   ```

7. **Submit a Pull Request**
   - Navigate to the original repository and click on "Compare & pull request."
   - Provide a clear description of your changes and submit the request.

---

## License

This project is licensed under the [MIT License](LICENSE).

---

## Contact

For any questions, issues, or suggestions, please contact:

- **Email**: admin@realestateapp.com
- **GitHub**: [your-username](https://github.com/your-username)
- **Website**: [www.realestateapp.com](http://www.realestateapp.com)

---

Thank you for using the **RealEstate** web application! We hope it serves your real estate management and browsing needs effectively.
