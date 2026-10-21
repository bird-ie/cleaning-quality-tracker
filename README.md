# Cleaning Quality Tracker

A standalone WordPress plugin for commercial cleaning companies to document inspections, assign corrective tasks, and track quality assurance.

## 🎯 The Business Problem

Commercial cleaning companies manage recurring office contracts. When a client complains about a missed task, the cleaning company has no consistent record of inspections, evidence, or corrective work. This leads to disputes, lost contracts, and lack of accountability.

## 💡 The Solution

*   **Reusable Checklists:** Admins create inspection templates per site.
*   **Supervisor Portal:** Supervisors log in and fill out inspections, marking items as Pass or Fail.
*   **Corrective Actions:** Failed checks are automatically flagged as corrective actions required.
*   **Inspection Reports:** Admins can view all inspections with a summary of passed and failed items.

## 🚀 Features

### Admin Operations
*   **Inspection Sites:** Custom Post Type for managing client locations.
*   **Checklist Builder:** Dynamic add/remove checklist items for each site.
*   **Supervisor Assignment:** Assign a specific supervisor to each site.
*   **Inspection Reports:** View detailed inspection results with Pass/Fail status and corrective actions.

### Supervisor Experience
*   **Frontend Portal:** Shortcode-based portal where supervisors see their assigned sites.
*   **AJAX Inspection Form:** Fill out the checklist, mark items as Pass/Fail, and submit without a page reload.
*   **Completed Status:** Once an inspection is done for the day, the site shows a "Completed Today" badge and the last inspection date.
*   **View Last Report:** A modal displays the full report without leaving the page.

### Reporting
*   **Frontend Reports Page:** A dashboard for admins showing all inspections with Pass/Fail counts.
*   **Detailed Inspection View:** Click "View Details" to see the full checklist, colored results, and a red warning box for corrective actions.

## 🛠️ Technical Architecture

### Directory Structure
```text
cleaning-quality-tracker/
│
├── admin/
│   └── class-cqt-inspection-report.php
│
├── assets/
│   ├── frontend.css
│   └── supervisor-portal.js
│
├── frontend/
│   ├── class-cqt-reports-page.php
│   └── class-cqt-supervisor-portal.php
│
├── includes/
│   ├── class-cqt-activator.php
│   ├── class-cqt-post-types.php
│   └── class-cqt-checklist-meta.php
│
├── cleaning-quality-tracker.php
└── README.md
Key Technical Implementations
Custom Post Types: cqt_site and cqt_inspection for structured data.

Custom User Role: cqt_supervisor with restricted capabilities.

AJAX Submission: Inspection data is submitted via AJAX and saved as post meta.

Corrective Action Logic: Automatically parses failed items and displays them in a red warning box.

Auto-Setup: The plugin creates required pages (Supervisor Portal, Inspection Reports) on activation.

⚙️ Installation
Upload the cleaning-quality-tracker folder to /wp-content/plugins/.

Activate the plugin through the 'Plugins' menu in WordPress.

Upon activation, the plugin automatically creates:

Supervisor Portal page (with [cqt_supervisor_portal] shortcode).

Inspection Reports page (with [cqt_inspection_reports] shortcode).

Go to Inspection Sites → Add New to create your first site and add checklist items.

Create a Cleaning Supervisor user (Users → Add New).

Assign the supervisor to the site via the Assigned Supervisor meta box.

Log in as the supervisor and visit the Supervisor Portal page to perform an inspection.

💻 Developer Notes
AJAX Security: All AJAX endpoints verify nonces (check_ajax_referer) and capabilities (current_user_can).

Auto-Setup: The plugin creates required pages on activation using register_activation_hook.

Data Storage: Inspection results are stored as serialized arrays in post meta (_cqt_results, _cqt_failed_items).

Styling: All CSS is loaded from a single frontend.css file for easy customization.
