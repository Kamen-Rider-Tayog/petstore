// When any filter changes
document.querySelectorAll(".filter-input").forEach((input) => {
  input.addEventListener("change", function () {
    updateResults();
  });
});

// Sort change
document.getElementById("sort-select")?.addEventListener("change", function () {
  updateResults();
});

// Price range apply
document.getElementById("apply-price")?.addEventListener("click", function () {
  updateResults();
});

function updateResults() {
  // Collect all filter values
  const filters = {
    category: getSelectedValues("category_filter"),
    min_price: document.getElementById("min-price")?.value,
    max_price: document.getElementById("max-price")?.value,
    in_stock: document.getElementById("in-stock")?.checked,
    brand: getSelectedValues("brand_filter"),
    sort: document.getElementById("sort-select")?.value,
  };

  // Show loading indicator
  document.getElementById("results-area").innerHTML =
    '<div class="loading">Loading...</div>';

  // Fetch filtered results
  fetch("/Ria-Pet-Store/backend/api/filter_products.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(filters),
  })
    .then((response) => response.json())
    .then((data) => {
      document.getElementById("results-area").innerHTML = data.html;
      document.getElementById("results-count").textContent =
        data.count + " products found";
    })
    .catch((error) => {
      console.error("Error:", error);
      document.getElementById("results-area").innerHTML =
        '<div class="error">Error loading results. Please try again.</div>';
    });
}

function getSelectedValues(name) {
  const checkboxes = document.querySelectorAll(`input[name="${name}"]:checked`);
  return Array.from(checkboxes).map((cb) => cb.value);
}

function getCurrentFilters() {
  return {
    category: getSelectedValues("category_filter"),
    min_price: document.getElementById("min-price")?.value,
    max_price: document.getElementById("max-price")?.value,
    in_stock: document.getElementById("in-stock")?.checked,
    brand: getSelectedValues("brand_filter"),
    sort: document.getElementById("sort-select")?.value,
  };
}
