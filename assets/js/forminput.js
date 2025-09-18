// Load input config
const inputConfigs = window.inputConfigs;

/**
 * Function: showClearButtonsOnLoad
 *
 * Show clear button if the input has value on load
 */
function showClearButtonsOnLoad() {
	inputConfigs.forEach(({ id, button }) => {
		const input = document.getElementById(id);
		const clearBtn = document.getElementById(button);
		if (input && clearBtn && input.value) {
			clearBtn.style.display = "block";
		}
	});
}

// Call on page load
showClearButtonsOnLoad();

/**
 * Function: toggleClear
 *
 * Toggle clear button visibility on input change
 */
function toggleClear(id, button) {
	const input = document.getElementById(id);
	const clearBtn = document.getElementById(button);
	if (input && clearBtn) {
		clearBtn.style.display = input.value ? "block" : "none";
	}
}

/**
 * Function: clearInput
 *
 * Clear input and hide button
 */
function clearInput(id, button) {
	const input = document.getElementById(id);
	const clearBtn = document.getElementById(button);
	if (input && clearBtn) {
		input.value = "";
		clearBtn.style.display = "none";
	}
}