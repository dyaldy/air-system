# Common Helper Functions Documentation

This document provides detailed documentation for all helper functions available in the `common_helper.php` file.

## View Rendering

### `render_view(string $view, array $data = []): void`

Renders a view with the standard header and footer template.

**Parameters:**

- `$view` (string): Path to the view file (e.g., 'user/index')
- `$data` (array): Data to pass to the view templates

**Usage:**

```php
render_view('user/index', ['title' => 'User Management', 'users' => $users]);
```

## Message Handling

### `set_message(array $message): void`

Sets a flash message for display on the next page load.

**Parameters:**

- `$message` (array): Message array with format `[type, text]`

**Usage:**

```php
set_message(['success', 'User created successfully']);
set_message(['danger', 'Error occurred']);
set_message(['warning', 'Please check your input']);
set_message(['info', 'Process completed']);
```

## Authentication

### `check_user_authentication(string $redirectPath = 'auth'): void`

Checks if user is authenticated and redirects if not logged in.

**Parameters:**

- `$redirectPath` (string): Path to redirect to if not authenticated (default: 'auth')

**Usage:**

```php
// In controller constructor
check_user_authentication(); // Redirects to 'auth' if not logged in
check_user_authentication('login'); // Redirects to 'login' if not logged in
```

### `get_current_user_nik(): ?string`

Retrieves the current logged-in user's NIK from session.

**Returns:**

- `string|null`: User NIK or null if not found

**Usage:**

```php
$userNik = get_current_user_nik();
if ($userNik) {
    // User is logged in
    $this->audit_log($userNik, 'action_performed');
}
```

## Session Management

### `reset_controller_session(string $controllerName): void`

Resets session data when switching between controllers.

**Parameters:**

- `$controllerName` (string): Name of the current controller

**Usage:**

```php
// In controller constructor
reset_controller_session('user');
```

### `handle_session_state(string $redirect, array $filterMap = []): void`

Generic session state handler for search, filter, sort, and reset operations.

**Parameters:**

- `$redirect` (string): Controller name to redirect to after handling
- `$filterMap` (array): Mapping of session keys to POST keys

**Usage:**

```php
handle_session_state('user', [
    'user_level' => 'user-level',
    'department' => 'department'
]);
```

## Pagination

### `setup_pagination(string $baseUrl, int $totalRows, int $itemsPerPage = 7): array`

Sets up pagination configuration with Bootstrap styling.

**Parameters:**

- `$baseUrl` (string): Base URL for pagination links
- `$totalRows` (int): Total number of records
- `$itemsPerPage` (int): Records per page (default: 7)

**Returns:**

- `array`: Pagination configuration array for CI_Pagination

**Usage:**

```php
$config = setup_pagination(site_url('user/index'), $totalUsers, 10);
$this->pagination->initialize($config);
```

## Form Validation

### `get_rules(array $validationConfig, array $fields): array`

Extracts validation rules from controller configuration.

**Parameters:**

- `$validationConfig` (array): Validation configuration from controller CONFIG
- `$fields` (array): Array of field names to extract rules for

**Returns:**

- `array`: Array of validation rules for `set_rules()`

**Usage:**

```php
// In controller with CONFIG['validation'] defined
$rules = get_rules(self::CONFIG['validation'], ['nik', 'name', 'email']);
$this->form_validation->set_rules($rules);
```

## Excel Operations

### `output_excel_file($spreadsheet, string $filename): void`

Outputs Excel file to browser for download with proper headers.

**Parameters:**

- `$spreadsheet` (PhpOffice\PhpSpreadsheet\Spreadsheet): The spreadsheet object
- `$filename` (string): Filename for download

**Usage:**

```php
$spreadsheet = new Spreadsheet();
// ... populate spreadsheet
output_excel_file($spreadsheet, 'user_data.xlsx');
```

### `apply_excel_header_style($sheet, string $cellRange): void`

Applies consistent header styling to Excel worksheet.

**Parameters:**

- `$sheet` (PhpOffice\PhpSpreadsheet\Worksheet\Worksheet): The worksheet object
- `$cellRange` (string): Cell range for headers (e.g., 'A1:D1')

**Usage:**

```php
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Name');
$sheet->setCellValue('B1', 'Email');
apply_excel_header_style($sheet, 'A1:B1');
```

### `auto_size_excel_columns($sheet, string $startCol, string $endCol): void`

Automatically adjusts column widths to fit content.

**Parameters:**

- `$sheet` (PhpOffice\PhpSpreadsheet\Worksheet\Worksheet): The worksheet object
- `$startCol` (string): Starting column letter (e.g., 'A')
- `$endCol` (string): Ending column letter (e.g., 'D')

**Usage:**

```php
auto_size_excel_columns($sheet, 'A', 'F'); // Auto-size columns A through F
```

### `validate_excel_file(array $file, int $maxSize = 2048, array $allowedTypes = ['xlsx', 'xls']): array`

Validates uploaded Excel file for size, type, and format.

**Parameters:**

- `$file` (array): $\_FILES array for the uploaded file
- `$maxSize` (int): Maximum file size in KB (default: 2048)
- `$allowedTypes` (array): Allowed file extensions (default: ['xlsx', 'xls'])

**Returns:**

- `array`: Validation result with 'valid' boolean and 'error' message

**Usage:**

```php
$validation = validate_excel_file($_FILES['excel_file'], 1024, ['xlsx']);
if ($validation['valid']) {
    // Process file
    $this->process_excel($_FILES['excel_file']['tmp_name']);
} else {
    set_message(['danger', $validation['error']]);
}
```

## Utility Functions

### `format_datetime_indonesian(string $datetime): string`

Formats datetime string to Indonesian format.

**Parameters:**

- `$datetime` (string): DateTime string to format

**Returns:**

- `string`: Formatted datetime string (DD MMM YYYY HH:ii:ss)

**Usage:**

```php
$formatted = format_datetime_indonesian('2023-12-25 14:30:00');
// Returns: "25 Dec 2023 14:30:00"

// In views
echo format_datetime_indonesian($user['created_at']);
```

## Complete Controller Example

Here's an example of how to use these helpers in a controller:

```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Example controller demonstrating helper usage.
 *
 * @package AirSystem
 * @subpackage Controllers
 * @category Example
 * @author Apparel One Indonesia
 * @version 1.0.0
 */
class Example extends CI_Controller
{
    private const CONFIG = [
        'pagination' => ['items_per_page' => 10],
        'validation' => [
            'name' => [
                'field' => 'name',
                'label' => 'Name',
                'rules' => 'required|trim|max_length[100]',
                'errors' => [
                    'required' => '%s is required',
                    'max_length' => '%s must not exceed 100 characters'
                ]
            ]
        ]
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('common');

        // Check authentication
        check_user_authentication();

        // Reset session for this controller
        reset_controller_session('example');

        $this->load->model('Example_model');
        $this->load->library(['form_validation', 'pagination']);
    }

    public function index(): void
    {
        // Handle session state (search, filter, sort, reset)
        handle_session_state('example', ['category' => 'category']);

        $sessionData = [
            'search' => $this->session->userdata('keyword'),
            'filter' => $this->session->userdata('filter'),
            'sort'   => $this->session->userdata('sort')
        ];

        // Setup pagination
        $totalRows = $this->Example_model->countRecords($sessionData['search'], $sessionData['filter']);
        $config = setup_pagination(site_url('example/index'), $totalRows, self::CONFIG['pagination']['items_per_page']);

        $this->pagination->initialize($config);

        $startData = ($this->uri->segment(3) > 0) ? (($this->uri->segment(3) - 1) * $config['per_page']) : 0;

        $data = [
            'title' => 'Example Page',
            'records' => $this->Example_model->getRecords(
                $config['per_page'],
                $startData,
                $sessionData['search'],
                $sessionData['filter'],
                $sessionData['sort']
            ),
            'pagination' => $this->pagination->create_links()
        ];

        render_view('example/index', $data);
    }

    public function create(): void
    {
        // Set validation rules using helper
        $rules = get_rules(self::CONFIG['validation'], ['name']);
        $this->form_validation->set_rules($rules);

        if ($this->form_validation->run()) {
            $result = $this->Example_model->create([
                'name' => $this->input->post('name', true),
                'created_by' => get_current_user_nik()
            ]);

            if ($result) {
                set_message(['success', 'Record created successfully']);
            } else {
                set_message(['danger', 'Failed to create record']);
            }
        }

        redirect('example');
    }

    public function export(): void
    {
        $records = $this->Example_model->getAllRecords();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Created At');

        // Apply header styling
        apply_excel_header_style($sheet, 'A1:C1');

        // Add data
        $row = 2;
        foreach ($records as $record) {
            $sheet->setCellValue("A{$row}", $record['id']);
            $sheet->setCellValue("B{$row}", $record['name']);
            $sheet->setCellValue("C{$row}", format_datetime_indonesian($record['created_at']));
            $row++;
        }

        // Auto-size columns
        auto_size_excel_columns($sheet, 'A', 'C');

        // Output file
        output_excel_file($spreadsheet, 'example_data.xlsx');
    }

    public function import(): void
    {
        if (!isset($_FILES['excel_file'])) {
            set_message(['danger', 'No file uploaded']);
            redirect('example');
        }

        // Validate file
        $validation = validate_excel_file($_FILES['excel_file']);
        if (!$validation['valid']) {
            set_message(['danger', $validation['error']]);
            redirect('example');
        }

        // Process file...
        set_message(['success', 'Data imported successfully']);
        redirect('example');
    }
}
```

This example demonstrates the proper usage of all common helper functions in a typical controller scenario.
