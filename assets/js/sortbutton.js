/**
 * Function: sortTable
 *
 * Sorts the table by submitting a temporary form with the given sort key.
 *
 * @param {string} sortKey - The key to sort the table by.
 */
function sortTable(sortKey) {
	// Helper to create hidden input
	const createHiddenInput = (name, value) => {
		const input = document.createElement("input");
		input.type = "hidden";
		input.name = name;
		input.value = value;
		return input;
	};

	// Create and configure the form
	const form = document.createElement("form");
	form.method = "post";

	// Append required hidden inputs
	form.appendChild(createHiddenInput("sort", sortKey));
	form.appendChild(createHiddenInput("sort-send", "1"));

	// Add and submit the form
	document.body.appendChild(form);
	form.submit();
}