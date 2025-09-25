// Check if the upload button exists before adding event listener
const uploadBtn = document.getElementById("uploadBtn");

if (uploadBtn) {
    // Add an event listener to the "uploadBtn" button
    uploadBtn.addEventListener("click", function () {
        // Get the file input element
        const fileInput = document.getElementById("formFile");
        
        // Check if file input exists
        if (!fileInput) {
            console.error("File input element not found");
            return;
        }

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

        // Check if upload form exists
        const uploadForm = document.getElementById("uploadForm");
        if (!uploadForm) {
            console.error("Upload form element not found");
            return;
        }

        // If validation passes, submit the form
        uploadForm.submit();
    });
}