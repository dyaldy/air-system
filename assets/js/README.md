# JavaScript Utilities Documentation

This document provides comprehensive documentation for the JavaScript utilities available in the Air System project.

## Common Utilities (`assets/js/common.js`)

The `AirSystemUtils` namespace provides commonly used JavaScript functions across the application.

### Message Display

#### `AirSystemUtils.showSuccessMessage(message, containerId = 'alert-container')`

Displays a success message using Bootstrap alert styling.

**Parameters:**

- `message` (string): The success message to display
- `containerId` (string, optional): ID of container to append alert (default: 'alert-container')

**Usage:**

```javascript
// Basic usage
AirSystemUtils.showSuccessMessage("Data saved successfully!");

// With custom container
AirSystemUtils.showSuccessMessage(
	"Upload completed!",
	"custom-alert-container"
);
```

#### `AirSystemUtils.showErrorMessage(message, containerId = 'alert-container')`

Displays an error message using Bootstrap alert styling.

**Parameters:**

- `message` (string): The error message to display
- `containerId` (string, optional): ID of container to append alert (default: 'alert-container')

**Usage:**

```javascript
AirSystemUtils.showErrorMessage("Please check your input!");
```

### File Validation

#### `AirSystemUtils.validateExcelFile(fileInput, maxSizeKB = 2048)`

Validates Excel file uploads for size and type.

**Parameters:**

- `fileInput` (HTMLInputElement): The file input element
- `maxSizeKB` (number, optional): Maximum file size in KB (default: 2048)

**Returns:**

- `Object`: Validation result with `isValid` boolean and `message` string

**Usage:**

```javascript
const fileInput = document.getElementById("excel-file");
const validation = AirSystemUtils.validateExcelFile(fileInput, 1024);

if (validation.isValid) {
	// File is valid, proceed with upload
	form.submit();
} else {
	// Show error message
	alert(validation.message);
}
```

#### `AirSystemUtils.handleExcelUpload(formId, fileInputId, onSuccess, onError)`

Handles Excel file upload with validation and callbacks.

**Parameters:**

- `formId` (string): ID of the upload form
- `fileInputId` (string): ID of the file input element
- `onSuccess` (Function, optional): Callback function on successful validation
- `onError` (Function, optional): Callback function on validation error

**Usage:**

```javascript
AirSystemUtils.handleExcelUpload(
	"uploadForm",
	"fileInput",
	function (form, fileInput) {
		// Custom success handling
		AirSystemUtils.showSuccessMessage("File validated, uploading...");
		form.submit();
	},
	function (errorMessage) {
		// Custom error handling
		AirSystemUtils.showErrorMessage(errorMessage);
	}
);
```

### Filter Operations

#### `AirSystemUtils.applyFilters(filterData, action = '')`

Applies filters by building and submitting a POST form.

**Parameters:**

- `filterData` (Object): Filter data as key-value pairs
- `action` (string, optional): Form action URL (defaults to current page)

**Usage:**

```javascript
// Apply single filter
AirSystemUtils.applyFilters({
	"user-level": "admin",
});

// Apply multiple filters
AirSystemUtils.applyFilters({
	"user-level": "admin",
	department: "IT",
	status: "active",
});

// With custom action
AirSystemUtils.applyFilters(
	{
		category: "electronics",
	},
	"/products/filter"
);
```

### Utility Functions

#### `AirSystemUtils.formatNumber(num)`

Formats numbers with thousand separators using Indonesian locale.

**Parameters:**

- `num` (number): Number to format

**Returns:**

- `string`: Formatted number string

**Usage:**

```javascript
console.log(AirSystemUtils.formatNumber(1234567)); // "1.234.567"
console.log(AirSystemUtils.formatNumber(999)); // "999"
```

#### `AirSystemUtils.confirmDelete(itemName, onConfirm)`

Shows confirmation dialog for delete operations.

**Parameters:**

- `itemName` (string): Name of item to be deleted
- `onConfirm` (Function, optional): Callback function when confirmed

**Returns:**

- `boolean`: True if confirmed, false otherwise

**Usage:**

```javascript
// Basic usage
if (AirSystemUtils.confirmDelete("this user")) {
	// Proceed with deletion
	window.location.href = "/user/delete/" + userId;
}

// With callback
AirSystemUtils.confirmDelete("this record", function () {
	// Custom deletion logic
	fetch("/api/delete/" + recordId, { method: "DELETE" }).then((response) => {
		AirSystemUtils.showSuccessMessage("Record deleted successfully");
	});
});
```

### Initialization

#### `AirSystemUtils.init()`

Initializes common page functionality (called automatically on DOM ready).

- Initializes Bootstrap tooltips and popovers
- Adds confirmation to delete buttons
- Sets up common event handlers

**Auto-initialization:**
The `init()` function runs automatically when the DOM is ready. Manual calling is not required.

## Filter Manager (`assets/js/filter-manager.js`)

The `FilterManager` namespace provides advanced filter functionality for data tables.

### Filter Initialization

#### `FilterManager.initFilters(config)`

Initializes filter dropdowns with change event handlers.

**Parameters:**

- `config` (Object): Configuration object with filter mappings

**Configuration Object:**

```javascript
{
    controllerName: 'user',  // Controller name for form submission
    filters: {
        'user-level': 'user_level',      // 'element-id': 'backend_filter_name'
        'department': 'department',
        'status': 'status'
    }
}
```

**Usage:**

```javascript
// Initialize filters for user management
FilterManager.initFilters({
	controllerName: "user",
	filters: {
		"user-level": "user_level",
		department: "department",
	},
});
```

### Filter Operations

#### `FilterManager.applyFilter(filterId, filterName, controllerName)`

Applies a single filter.

**Parameters:**

- `filterId` (string): HTML element ID of the filter (without '-filter' suffix)
- `filterName` (string): Backend filter name
- `controllerName` (string): Controller name for form submission

**Usage:**

```javascript
FilterManager.applyFilter("user-level", "user_level", "user");
```

#### `FilterManager.applyMultipleFilters(filterMap, controllerName)`

Applies multiple filters simultaneously.

**Parameters:**

- `filterMap` (Object): Object mapping filter IDs to filter names
- `controllerName` (string): Controller name for form submission

**Usage:**

```javascript
FilterManager.applyMultipleFilters(
	{
		"user-level": "user_level",
		department: "department",
		status: "status",
	},
	"user"
);
```

#### `FilterManager.resetFilters(controllerName = '')`

Resets all filters.

**Parameters:**

- `controllerName` (string, optional): Controller name for form submission

**Usage:**

```javascript
FilterManager.resetFilters("user");
```

### Advanced Features

#### `FilterManager.setFilterValues(filterData)`

Sets filter values from server data (for maintaining state after page reload).

**Parameters:**

- `filterData` (Object): Filter data from server

**Usage:**

```javascript
// Set filter values from PHP data
FilterManager.setFilterValues(<?= json_encode($sessionData['filter'] ?? []) ?>);
```

#### `FilterManager.initDependentDropdowns(config)`

Initializes dependent dropdowns (e.g., type → subtype).

**Parameters:**

- `config` (Object): Configuration for dependent dropdowns

**Configuration Object:**

```javascript
{
    parentId: 'type-filter',           // Parent dropdown ID
    childId: 'subtype-filter',         // Child dropdown ID
    ajaxUrl: '/fitting/get_subtypes',  // AJAX endpoint URL
    emptyOption: 'Select Subtype'      // Default empty option text
}
```

**Usage:**

```javascript
FilterManager.initDependentDropdowns({
	parentId: "type-filter",
	childId: "subtype-filter",
	ajaxUrl: "/fitting/get_subtypes",
	emptyOption: "Pilih Subtype",
});
```

#### `FilterManager.initResetButton(buttonId, controllerName)`

Initializes filter reset button.

**Parameters:**

- `buttonId` (string): ID of the reset button
- `controllerName` (string): Controller name

**Usage:**

```javascript
FilterManager.initResetButton("reset-all-filters", "user");
```

## Complete HTML Example

Here's a complete example showing how to use these JavaScript utilities in an HTML page:

```html
<!DOCTYPE html>
<html>
	<head>
		<title>User Management</title>
		<link
			href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
			rel="stylesheet"
		/>
	</head>
	<body>
		<!-- Alert container for messages -->
		<div id="alert-container"></div>

		<!-- Filter section -->
		<div class="row mb-3">
			<div class="col-md-3">
				<select id="user-level-filter" class="form-select">
					<option value="">All Levels</option>
					<option value="admin">Admin</option>
					<option value="user">User</option>
				</select>
			</div>
			<div class="col-md-3">
				<select id="department-filter" class="form-select">
					<option value="">All Departments</option>
					<option value="IT">IT</option>
					<option value="HR">HR</option>
				</select>
			</div>
			<div class="col-md-3">
				<button
					id="reset-filters"
					class="btn btn-secondary"
					data-controller="user"
				>
					Reset Filters
				</button>
			</div>
		</div>

		<!-- Data table -->
		<table class="table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Level</th>
					<th>Department</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($users as $user): ?>
				<tr>
					<td><?= $user['name'] ?></td>
					<td><?= $user['level'] ?></td>
					<td><?= $user['department'] ?></td>
					<td>
						<button
							class="btn btn-danger btn-sm btn-delete"
							data-item-name="<?= $user['name'] ?>"
							onclick="deleteUser(<?= $user['id'] ?>)"
						>
							Delete
						</button>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<!-- Upload modal -->
		<div class="modal fade" id="uploadModal">
			<div class="modal-dialog">
				<div class="modal-content">
					<form id="uploadForm" method="POST" enctype="multipart/form-data">
						<div class="modal-header">
							<h5 class="modal-title">Upload Excel File</h5>
							<button
								type="button"
								class="btn-close"
								data-bs-dismiss="modal"
							></button>
						</div>
						<div class="modal-body">
							<input
								type="file"
								id="fileInput"
								name="excel_file"
								class="form-control"
								accept=".xlsx,.xls"
							/>
						</div>
						<div class="modal-footer">
							<button
								type="button"
								class="btn btn-secondary"
								data-bs-dismiss="modal"
							>
								Cancel
							</button>
							<button type="button" id="uploadBtn" class="btn btn-primary">
								Upload
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<!-- Scripts -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
		<script src="<?= base_url('assets/js/common.js') ?>"></script>
		<script src="<?= base_url('assets/js/filter-manager.js') ?>"></script>

		<script>
			// Initialize filters
			FilterManager.initFilters({
			    controllerName: 'user',
			    filters: {
			        'user-level': 'user_level',
			        'department': 'department'
			    }
			});

			// Set current filter values from server
			FilterManager.setFilterValues(<?= json_encode($sessionData['filter'] ?? []) ?>);

			// Handle Excel upload
			document.getElementById('uploadBtn').addEventListener('click', function() {
			    AirSystemUtils.handleExcelUpload('uploadForm', 'fileInput',
			        function(form) {
			            AirSystemUtils.showSuccessMessage('Uploading file...');
			            form.submit();
			        },
			        function(error) {
			            AirSystemUtils.showErrorMessage(error);
			        }
			    );
			});

			// Custom delete function
			function deleteUser(userId) {
			    // The confirmation is handled automatically by AirSystemUtils.init()
			    // This function will only run if user confirms
			    window.location.href = '/user/delete/' + userId;
			}

			// Show messages from PHP flash data
			<?php if ($this->session->flashdata('action')): ?>
			<?php $message = $this->session->flashdata('action'); ?>
			<?php if ($message[0] === 'success'): ?>
			AirSystemUtils.showSuccessMessage('<?= addslashes($message[1]) ?>');
			<?php else: ?>
			AirSystemUtils.showErrorMessage('<?= addslashes($message[1]) ?>');
			<?php endif; ?>
			<?php endif; ?>
		</script>
	</body>
</html>
```

## Legacy Support

Both utility libraries provide legacy support for backward compatibility:

### Global Functions Available:

- `showSuccessMessage()`
- `showErrorMessage()`
- `validateExcelFile()`
- `handleExcelUpload()`
- `applyFilters()`
- `formatNumber()`
- `confirmDelete()`

These can be used directly without the namespace for existing code:

```javascript
// Legacy usage (still supported)
showSuccessMessage("Success!");
confirmDelete("this item");

// Modern usage (recommended)
AirSystemUtils.showSuccessMessage("Success!");
AirSystemUtils.confirmDelete("this item");
```

## Best Practices

1. **Always use the namespaced versions** (`AirSystemUtils.*`, `FilterManager.*`) in new code
2. **Include both common.js and filter-manager.js** in pages that need filtering
3. **Provide alert containers** in your HTML for message display
4. **Use data attributes** on buttons for automatic initialization
5. **Handle server-side filter state** by calling `FilterManager.setFilterValues()`
6. **Validate files client-side** before form submission to improve user experience
