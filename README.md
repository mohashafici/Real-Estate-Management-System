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
