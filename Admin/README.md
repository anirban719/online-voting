# VoteSecure Admin - Candidate Management System

## Overview
This is a professional candidate management system for the VoteSecure voting platform. It provides a complete interface for administrators to add, edit, and manage election candidates with image upload capabilities.

## Features

### 1. Candidate Management
- **Add New Candidates**: Complete form with validation and image upload
- **Edit Candidates**: Update existing candidate information and images
- **Delete Candidates**: Safe deletion with image cleanup
- **View All Candidates**: Professional table view with search and sorting

### 2. Image Management
- **Candidate Photos**: Upload candidate profile pictures (JPG, PNG, JPEG - Max 2MB)
- **Party Symbols**: Upload party symbols/logos (JPG, PNG, JPEG, GIF - Max 1MB)
- **Image Preview**: Real-time preview before upload
- **Drag & Drop**: Modern drag-and-drop file upload
- **Image Security**: Protected upload directories with .htaccess

### 3. Professional Design
- **Responsive Design**: Works on all devices
- **Modern UI**: Bootstrap 5 with custom styling
- **Interactive Elements**: Hover effects, animations, and transitions
- **User-Friendly Forms**: Clear labels, validation, and error messages

## File Structure

```
Admin/
├── candidates.php          # Main candidate management page
├── add-candidate.php       # Add new candidate form
├── edit-candidate.php      # Edit existing candidate
├── dashboard.php          # Admin dashboard with candidate stats
├── login.php              # Admin login
└── logout.php             # Admin logout

uploads/
├── candidates/            # Candidate photos
├── symbols/               # Party symbols
├── .htaccess             # Security configuration
└── index.html            # Directory protection
```

## Database Structure

### Candidate Table
| Field       | Type         | Description                  |
|-------------|--------------|------------------------------|
| id          | int(15)      | Primary key, auto-increment |
| name        | varchar(40)  | Candidate full name         |
| dob         | date         | Date of birth               |
| email       | varchar(50)  | Email address               |
| nameofparty | varchar(50)  | Political party name        |
| symbol      | varchar(200) | Path to party symbol image  |
| image       | varchar(200) | Path to candidate photo     |

## Usage Instructions

### Adding a New Candidate
1. Navigate to Admin Dashboard → Manage Candidates
2. Click "Add New Candidate" button
3. Fill in the required information:
   - Full Name
   - Date of Birth
   - Email Address
   - Party Name
4. Upload candidate photo (required)
5. Upload party symbol (required)
6. Submit the form

### Editing a Candidate
1. Go to Manage Candidates page
2. Click the edit icon (pencil) next to any candidate
3. Update the desired information
4. Optionally upload new images
5. Save changes

### Deleting a Candidate
1. Go to Manage Candidates page
2. Click the delete icon (trash) next to any candidate
3. Confirm deletion in the popup
4. Images will be automatically deleted from server

## Security Features
- **File Type Validation**: Only allowed image formats accepted
- **File Size Limits**: Prevents large file uploads
- **Directory Protection**: .htaccess prevents direct access
- **SQL Injection Prevention**: Prepared statements used throughout
- **XSS Protection**: All output properly escaped
- **Session Management**: Secure admin authentication

## Technical Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- GD Library for image processing
- File upload enabled in PHP

## Browser Support
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Customization Options
- **Colors**: Modify CSS variables in style.css
- **Validation Rules**: Adjust in PHP validation sections
- **File Limits**: Change in PHP upload handling
- **Table Display**: Customize DataTables configuration

## Troubleshooting

### Common Issues
1. **Images not uploading**: Check PHP file upload limits
2. **Permission errors**: Ensure uploads directory is writable
3. **Database errors**: Verify database connection in config.php
4. **Session issues**: Check PHP session configuration

### Error Messages
- "Invalid image format": Ensure file is JPG, PNG, or JPEG
- "File too large": Reduce image size or increase PHP limits
- "Email already exists": Use unique email for each candidate

## Future Enhancements
- Bulk candidate import via CSV
- Advanced image cropping tool
- Candidate bio/description field
- Social media links
- Campaign website URL
- Candidate video uploads
- Real-time vote counting display
- Export candidate data to PDF/Excel

## Support
For technical support or feature requests, please refer to the main project documentation or contact the development team.
