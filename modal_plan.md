# Plan for Resource Details Modal Implementation

## Overview
Implement a modal window that displays complete details of a library resource when the "Ver detalles" button is clicked.

## Current State Analysis
- Button exists: `<button onclick="showResourceDetails(<?php echo $resource['id']; ?>)">`
- Function exists but only shows alert: `showResourceDetails(resourceId)`
- Database table: `library_resources` with fields: id, title, author, type, subject, file_path, description, uploaded_by, upload_date
- Related table: `users` for uploader name

## Implementation Steps

### 1. Create API Endpoint
- File: `api/library.php`
- Purpose: Fetch detailed resource information by ID
- Method: GET with parameter `id`
- Return: JSON with all resource fields plus uploader name

### 2. Add Modal HTML Structure
- Location: `modules/library/index.php` before closing `</main>`
- Include: Modal container, overlay, close button, content areas for all resource fields

### 3. Update JavaScript Function
- Replace alert with modal display logic
- Fetch data from API endpoint
- Populate modal with fetched data
- Handle loading states and errors

### 4. Add CSS Styling
- Location: `assets/css/style.css` or inline in the modal
- Style: Modal overlay, modal content, animations, responsive design

### 5. Test Functionality
- Click "Ver detalles" button
- Verify modal opens with correct data
- Test close functionality
- Test with different resource types

## Database Fields to Display
- Title
- Author
- Type (with icon)
- Subject
- Description
- Upload Date
- Uploader Name
- File Path (if exists)
- Download link (if file exists)

## Technical Considerations
- Use existing PDO connection from config.php
- Sanitize inputs
- Handle cases where resource doesn't exist
- Ensure modal is accessible (keyboard navigation, screen readers)
- Make modal responsive for mobile devices

## Security
- Validate resource ID is numeric
- Ensure user has permission to view resource details (if needed)
- Sanitize output to prevent XSS