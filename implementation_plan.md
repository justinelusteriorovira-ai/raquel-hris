# Report Generation Module Implementation

The objective is to implement the "Report Generation" functional module inside the HR Manager dashboard (`manager/reports.php`), allowing users to dynamically filter and download comprehensive HR reports in CSV format, or print them directly (which handles PDF generation natively via browser's Print to PDF). 

## User Review Required

> [!NOTE]
> Based on your feedback:
> - **Attendance Report**: Will be omitted/replaced with a "Basic Employee Roster".
> - **PDF Export**: We will integrate a lightweight PHP library (**FPDF**) to generate true `.pdf` file downloads directly instead of using the browser print dialogue.
> - **CSV Export**: Fully implemented for raw data (Excel).
> - **Role Restriction**: This feature will be hardcoded specifically for the **HR Manager** only.

You mentioned "don't start building yet", so please let me know when you are ready to proceed with these finalised specs.

## Proposed Changes

### Database Dependencies
The generated reports will rely on querying following core tables:
- `employees`, `branches`, `departments`
- `evaluations`, `evaluation_scores`
- `career_movements`

---

### UI & Frontend Logic

#### [MODIFY] manager/reports.php
Replaces the "Coming Soon" placeholder with a fully functional Reporting Dashboard.
- **Filter Form:** 
  - `Report Type` (Dropdown: Employee Masterlist, Performance Summary, Career Movements)
  - `Branch` & `Department` Filters (Dropdowns populated from DB)
  - `Date Range` (Start Date / End Date) for filtering movements/evaluations.
- **Action Buttons:** 
  - `Generate Report (Preview)`: Fetches HTML via AJAX and displays it on the screen.
  - `Export CSV`: Submits the form data to a download script.
  - `Export PDF`: Submits the form data to generate and download a true PDF file.

#### [NEW] includes/plugins/fpdf/fpdf.php
- We will download and include the standard FPDF library (which is a single lightweight PHP file) to seamlessly generate PDF downloads without needing complex Composer setups.

---

### Backend Logic & API

#### [NEW] manager/ajax/generate-report.php
Endpoint that handles AJAX requests from `reports.php`. 
- Validates the incoming filters safely.
- Dynamically constructs SQL queries based on the chosen report type.
- Returns formatted HTML (e.g., `<table>`) containing the report results for on-screen preview.

#### [NEW] manager/export-report.php
Endpoint that handles both CSV and PDF exact file downloads dependent on a submitted `export_type` parameter.
- Shares the query logic.
- **CSV Mode**: Sets HTTP headers for `text/csv`, streams output via `fputcsv`.
- **PDF Mode**: Uses FPDF to construct a document, iteratively prints table cells, and outputs via `$pdf->Output('D', 'report.pdf')` for an instant raw download.

## Open Questions

1. Do you want to restrict report generation strictly to "HR Manager" rules, or should it eventually be extended to other roles like HR Admin?
2. Are there any specific columns you absolutely need in the *Employee Masterlist* CSV export that might not be visible on the Preview table?

## Verification Plan

### Automated Tests
- No automated unit tests exist in this codebase, so testing will be done directly in the browser via specific test cases.

### Manual Verification
1. Access `http://localhost/raquel-hris/manager/reports.php`.
2. Select "Performance Summary", choose a specific Date Range, and click "Generate Preview". Verify the table renders correctly.
3. Click "Export CSV" and open the downloaded file in Excel to ensure data is properly formatted and columns align.
4. Click "Print to PDF" and ensure the preview window ONLY shows the report (no sidebar or nav menus).
