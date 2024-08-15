console.log("hello");
document.addEventListener("DOMContentLoaded", function() {
    const investisseurCheckbox = document.querySelector(
        '.js-investisseur-checkbox input[type="checkbox"]'
    );
    const intradayCheckbox = document.querySelector(
        '.js-intraday-checkbox input[type="checkbox"]'
    );

    function toggleIntradayCheckbox() {
        if (investisseurCheckbox.checked) {
            intradayCheckbox.disabled = false;
        } else {
            intradayCheckbox.checked = false;
            intradayCheckbox.disabled = true;
        }
    }

    // Initial call
    toggleIntradayCheckbox();

    // Add event listener
    investisseurCheckbox.addEventListener("change", toggleIntradayCheckbox);
});