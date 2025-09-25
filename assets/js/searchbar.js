// Cache elements - check if they exist first
const searchBar = document.getElementById("search-bar");
const clearButton = document.getElementById("clear-button");
const searchForm = document.getElementById("search-form");

// Only proceed if the required elements exist
if (searchBar && clearButton && searchForm) {
    /**
     * Global Variables
     *
     * @var {string} keyword - Stores the value of the search bar input.
     */
    let keyword = searchBar.value || "";

    // On load: show clear button if keyword exists
    if (keyword) clearButton.style.display = "block";

    /**
     * Function: displayClear
     *
     * Toggle the visibility of the clear button
     */
    function displayClear() {
        clearButton.style.display = searchBar.value ? "block" : "none";
    }

    /**
     * Function: clearKeyword
     *
     * Clears the keyword and refreshes the search
     */
    function clearKeyword() {
        // Clear input field
        searchBar.value = "";

        // Immediately reflect UI
        displayClear();

        // Only submit if keyword existed initially
        if (keyword) {
            searchForm.submit();
        }
    }

    // Make functions available globally for onclick handlers
    window.displayClear = displayClear;
    window.clearKeyword = clearKeyword;
}