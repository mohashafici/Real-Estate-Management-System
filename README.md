# Real Estate Management System

## Description
This project is a **Real Estate Management System** built using **PHP** and **MySQL**, with a focus on delivering a user-friendly and feature-rich platform for managing real estate properties. The system incorporates authentication with role-based access for Admins, Agents, and Buyers. The platform is designed to handle property listings, inquiries, and user management efficiently.

---

## Features

### **Authentication**
- **Role-Based Access Control**:
  - **Admin**: Full control of the platform, including property approvals, reports, and user management.
  - **Agent**: Can add, manage, and feature properties.
  - **Buyer/User**: Can browse properties, save favorites, and make inquiries.

### **Admin Panel**
- **Dashboard**:
  - Overview of total properties, featured properties, and available properties.
  - Performance reports for rental properties.
- **Properties Management**:
  - Add, view, and manage property listings.
  - Approve or reject pending properties.
- **Reports**:
  - View performance reports of featured and rental properties.
- **Manage Queries**:
  - Handle buyer inquiries efficiently.

### **User Panel**
- **Home Page**:
  - Welcome message with a property search bar.
  - Display of featured properties.
- **Property Listings**:
  - Browse through available properties with filtering options.
  - Save properties as favorites.
- **Inquiry Management**:
  - Submit inquiries about properties.

### **Agent Panel**
- **Property Management**:
  - Add new property listings.
  - Mark properties as featured.
- **Dashboard**:
  - Overview of managed properties and performance.

### Additional Features
- **Responsive Design**:
  - Built with **Bootstrap**, ensuring compatibility across devices (desktop, tablet, mobile).
- **Favorites**:
  - Buyers can mark properties as favorites for easy access.
- **Search Functionality**:
  - Search properties by name, location, or type.
- **Dynamic Reports**:
  - Visual performance tracking of properties.

---

## Technologies Used
- **Frontend**:
  - HTML, CSS, JavaScript
  - Bootstrap for responsive UI
- **Backend**:
  - PHP
- **Database**:
  - MySQL
- **Authentication**:
  - Session-based authentication with role management

---

## Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/real-estate-management.git
   ```
2. Navigate to the project directory:
   ```bash
   cd real-estate-management
   ```
3. Import the database:
   - Locate the `real_estate.sql` file in the project.
   - Import it into your MySQL database using tools like phpMyAdmin or MySQL CLI.
4. Update the database configuration:
   - Edit the `config.php` file to match your database credentials:
     ```php
     $host = 'your-database-host';
     $user = 'your-database-username';
     $password = 'your-database-password';
     $database = 'real_estate';
     ```
5. Start a local server:
   - Use tools like XAMPP, WAMP, or MAMP to host the application locally.
6. Access the application:
   - Open your browser and navigate to `http://localhost/real-estate-management`.

---

## Screenshots
### Home Page
![Home Page](path/to/homepage-screenshot.png)

### Admin Dashboard
![Admin Dashboard](path/to/admin-dashboard-screenshot.png)

### Property Listings
![Property Listings](path/to/property-listings-screenshot.png)

---

## Future Enhancements
- Add payment gateway integration.
- Implement advanced filtering options (price range, property size, etc.).
- Enhance reporting with graphical analytics.
- Add multi-language support for global usability.

---

## Contributing
Contributions are welcome! To contribute:
1. Fork the repository.
2. Create a new branch:
   ```bash
   git checkout -b feature-branch-name
   ```
3. Make your changes and commit:
   ```bash
   git commit -m 'Add some feature'
   ```
4. Push to the branch:
   ```bash
   git push origin feature-branch-name
   ```
5. Open a pull request.

---

## License
This project is licensed under the MIT License. See the `LICENSE` file for details.

---

## Contact
For inquiries or support, feel free to contact:
- **Name**: Moha Shafici
- **Email**: your-email@example.com
- **GitHub**: [your-github-profile](https://github.com/your-username)
#   R e a l - E s t a t e - M a n a g e m e n t - S y s t e m  
 