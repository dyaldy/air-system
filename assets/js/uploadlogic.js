// Add an event listener to the "uploadBtn" button
document.getElementById("uploadBtn").addEventListener("click", function () {
	// Get the file input element
	const fileInput = document.getElementById("formFile");

	// Get the selected file from the input
	const file = fileInput.files[0];

	// Check if a file is uploaded
	if (!file) {
		alert("Harap pilih file sebelum mengupload!");
		return;
	}

	// Validate file type (Only allow .xlsx)
	if (
		file.type !==
		"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
	) {
		alert("Format file tidak valid! Hanya file .xlsx yang diperbolehkan.");
		return;
	}

	// If validation passes, submit the form
	document.getElementById("uploadForm").submit();
});