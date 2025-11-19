# Wadadli Flare Catering Website - Implementation Plan

## Overview
This document outlines the implementation plan for the Wadadli Flare Catering website, based on the requirements in `specification.md`.

## Project Goals
1. Create a clear, navigable website that showcases Chef Jamie's diverse culinary capabilities
2. Emphasize variety of cuisines (not just Caribbean) to attract higher-end market
3. Target Main Line and West Chester, PA markets
4. Implement clean, modern design with Caribbean-inspired colors
5. Ensure WCAG AA accessibility compliance

## Technology Stack
- **Server**: Ubuntu 24.04.3
- **Web Server**: Apache 2.4.58
- **Backend**: PHP 8.3.6
- **Database**: MySQL 8.0.43
- **Frontend**: HTML, CSS, JavaScript
- **Email**: MailChimp SMTP (Mandrill)
- **SSL**: Certbot
- **Testing**: Playwright MCP Server

## Directory Structure
```
/var/www/wadadliflarecatering.com/
├── public/          # Publicly accessible files
│   ├── css/        # Stylesheets
│   ├── js/         # JavaScript files
│   ├── gallery/    # Gallery images
│   ├── menu/       # Menu images
│   ├── includes/   # PHP includes (header, footer, config)
│   └── *.php       # Page files
└── private/        # Private files
    ├── includes/   # Private includes (db, email config)
    ├── forms/      # Form processing
    └── *.md        # Documentation
```

## URL Configuration
- **Domain**: https://wadadli.stephens.page
- **Clean URLs**: No `.php` extensions (e.g., `/about` not `/about.php`)
- **HTTPS**: Enforced via Apache redirects
- **SSL Certificate**: Managed via Certbot

## Pages and Structure

### Required Pages (from Sitemap)
1. **Home** (`index.php`) - Hero section, key features, call-to-action
2. **About / Chef Jamie** (`about.php`) - Full bio, experience, credentials
3. **Services Overview** (`services.php`) - Overview of all services
4. **Weddings** (`weddings.php`) - Wedding-specific menu and services
5. **Corporate Events** (`corporate.php`) - Corporate catering options
6. **Private Events** (`private-events.php`) - Private event services
7. **BBQ Experience** (`bbq-experience.php`) - BBQ menu and offerings
8. **Caribbean Experience** (`caribbean-experience.php`) - Caribbean menu
9. **Pick Up & Drop Off** (`pickup-dropoff.php`) - Self-service options
10. **Venues** (`venues.php`) - Venue information
11. **Gallery** (`gallery.php`) - Image gallery with filtering
12. **Reviews** (`reviews.php`) - Facebook and Google reviews
13. **Policies** (`policies.php`) - Business policies
14. **FAQ** (`faq.php`) - Frequently asked questions
15. **Contact** (`contact.php`) - Contact form and information
16. **Quote Request** (`quote-request.php`) - Quote request form

## Design Specifications

### Color Palette
From `Wadadli_Flare_Menu_Page_1.png`:
- **Light sky blue**: `#b2f9fe`
- **Dark ocean blue**: `#00b3c5` (primary blue)
- **Light coral red**: `#ff4c54`
- **Dark crab red**: `#eb444a` (primary red)
- **Light grass green**: `#9bd83d`
- **Dark palm leaf green**: `#468931` (primary green)
- **Light yellow**: `#ffffac`
- **Dark pineapple yellow**: `#fedb53` (primary yellow)

**Design Principle**: Colorful but avoid rainbow motifs. Use cohesive color schemes.

### Typography
- **Headers**: Lango Regular or Nervatica Bold (using Oswald Bold as web alternative)
- **Sub-headers**: Helsinki, Martines N50, or Kipp Clean No. 2 (using Montserrat as web alternative)
- **Body text**: Helvetica

### Accessibility
- **WCAG AA Compliance**: All text must meet at least 4.5:1 contrast ratio (normal text) or 3:1 (large text)
- **Color Contrast**: Ensure all text/background combinations meet standards
- **Responsive Design**: Mobile-first approach with breakpoints

### Design Inspiration
- **Layout**: Simple, clean, readable design like `kristinascatering.com`
- **Style**: Colorful, Caribbean-inspired flair from current `wadadliflarecatering.com` and menu images

## Features and Functionality

### Navigation
- Sticky header with logo and main navigation
- Dropdown menus for service categories
- Mobile-responsive hamburger menu
- Footer with links and contact information

### Forms
1. **Contact Form** (`contact.php`)
   - Fields: Name, Email, Phone, Message
   - Email sent to Chef Jamie via Mandrill SMTP
   - Submission stored in MySQL database
   - Client-side and server-side validation

2. **Quote Request Form** (`quote-request.php`)
   - Fields: Name, Email, Phone, Event Type, Event Date, Guest Count, Location, Message
   - Email sent to Chef Jamie via Mandrill SMTP
   - Submission stored in MySQL database
   - Client-side and server-side validation

### Gallery
- Image gallery with filtering by category:
  - All
  - Menu (menu images from `/menu/` directory)
  - Weddings
  - Corporate
  - Private Events
  - BBQ
  - Caribbean
  - Setup/Table Settings
- Lightbox functionality for image viewing
- Responsive grid layout
- Lazy loading for performance

### Reviews
- Display Facebook reviews
- Display Google reviews
- Star ratings
- Review cards with author, date, and content

### Menus
- Different menus displayed on different service pages:
  - Weddings page: Wedding-specific menu
  - Corporate page: Corporate menu
  - Private Events page: Private event menu
- Menu images displayed in gallery
- Menu items with descriptions and pricing

## Database Schema

### Tables
1. **contact_submissions**
   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
   - name (VARCHAR)
   - email (VARCHAR)
   - phone (VARCHAR)
   - message (TEXT)
   - submitted_at (TIMESTAMP)

2. **quote_requests**
   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
   - name (VARCHAR)
   - email (VARCHAR)
   - phone (VARCHAR)
   - event_type (VARCHAR)
   - event_date (DATE)
   - guest_count (INT)
   - location (VARCHAR)
   - message (TEXT)
   - submitted_at (TIMESTAMP)

## Email Configuration
- **SMTP Server**: Mandrill (MailChimp)
- **Configuration**: Stored in `private/includes/email.php`
- **Credentials**: Managed via environment variables or secure config

## Apache Configuration

### Virtual Host Setup
- HTTP Virtual Host: `/etc/apache2/sites-available/wadadli.stephens.page.conf`
- HTTPS Virtual Host: `/etc/apache2/sites-available/wadadli.stephens.page-le-ssl.conf`
- SSL Certificate: Managed via Certbot

### .htaccess Rules
- Remove `.php` extensions (clean URLs)
- Force HTTPS redirect
- Handle trailing slashes for directory-like URLs
- Directory listing disabled

## Content Requirements

### Key Messages
1. "Let us take some work out of your summer BBQ so you have more time to chill."
2. "If you don't see something you'd like on the menu, please feel free to ask."
3. "We love our customers, so feel free to contact us at (267) 481-5872."

### Chef Jamie's Bio
- 34 years of culinary experience
- Graduated from Caribbean culinary school (1989)
- Executive Chef at St. James Club, Antigua (5-star resort)
- Worked at Ritz Carlton Marina Del Rey
- 16 years at 12th Street Catering Company
- Emphasize diverse cuisines: Italian, French, Caribbean, American, Asian

### Contact Information
- Phone/Text: (267) 481-5872
- Email: wadadliflare.catering@gmail.com
- Facebook: https://www.facebook.com/people/wadadli-flare-Catering/100093105235894/
- Instagram: https://www.instagram.com/wadadli_flare_catering/
- Google Maps: https://maps.app.goo.gl/rwZ2hSf6WmHhSUde6

### Service Areas
- Twin Valley, Pennsylvania
- West Chester, PA
- Main Line (west of Philadelphia)
- Elverson, PA

## Implementation Checklist

### Phase 1: Setup and Configuration
- [x] Set up directory structure
- [x] Configure Apache Virtual Hosts
- [x] Install and configure SSL certificate (Certbot)
- [x] Set up .htaccess for clean URLs and HTTPS
- [x] Configure database connection
- [x] Set up email configuration (Mandrill SMTP)

### Phase 2: Core Pages
- [x] Create header and footer includes
- [x] Create config file with constants
- [x] Implement home page
- [x] Implement about page
- [x] Implement all service pages
- [x] Implement gallery page
- [x] Implement reviews page
- [x] Implement policies page
- [x] Implement FAQ page

### Phase 3: Forms and Functionality
- [x] Create contact form
- [x] Create quote request form
- [x] Implement form validation (client and server-side)
- [x] Set up database tables
- [x] Implement email sending
- [x] Implement form submission storage

### Phase 4: Design and Styling
- [x] Create CSS variables for color palette
- [x] Implement responsive design
- [x] Style navigation and header
- [x] Style hero section
- [x] Style cards and sections
- [x] Style forms
- [x] Style gallery with filtering
- [x] Style footer
- [x] Implement Google Fonts
- [x] Ensure WCAG AA contrast compliance

### Phase 5: Content and Media
- [x] Add gallery images
- [x] Add menu images
- [x] Implement gallery filtering
- [x] Add Chef Jamie's bio
- [x] Add all required content from specification
- [x] Add contact information
- [x] Add social media links

### Phase 6: Testing and Refinement
- [x] Test all pages load correctly
- [x] Test clean URLs work
- [x] Test HTTPS redirects
- [x] Test forms submit correctly
- [x] Test email sending
- [x] Test database storage
- [x] Test responsive design
- [x] Test accessibility (contrast ratios)
- [x] Test gallery filtering
- [x] Test lightbox functionality

## Maintenance and Updates

### Regular Tasks
- Monitor form submissions
- Update gallery images as needed
- Update menus and pricing
- Add new reviews
- Update policies as needed

### Security Considerations
- Keep PHP and Apache updated
- Secure database credentials
- Validate and sanitize all form inputs
- Use prepared statements for database queries
- Implement CSRF protection for forms
- Regular backups of database

## Notes
- The website emphasizes Chef Jamie's diverse culinary capabilities, not just Caribbean cuisine
- Target market is higher-end: Main Line and West Chester, PA
- Design should be colorful but avoid rainbow motifs
- All text must meet WCAG AA contrast standards
- Clean URLs are required (no .php extensions)
- HTTPS is enforced site-wide



