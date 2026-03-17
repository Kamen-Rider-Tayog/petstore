// Get search input element
const searchInput = document.getElementById("search-input");
const suggestionsDiv = document.getElementById("search-suggestions");

// Add input event listener
searchInput.addEventListener("input", function () {
  const term = this.value;

  if (term.length < 2) {
    suggestionsDiv.style.display = "none";
    return;
  }

  // Fetch suggestions
  fetch(
    `/Ria-Pet-Store/backend/api/live_search.php?term=${encodeURIComponent(term)}`,
  )
    .then((response) => response.json())
    .then((data) => {
      displaySuggestions(data);
    });
});

function displaySuggestions(data) {
  // Clear previous suggestions
  suggestionsDiv.innerHTML = "";

  // Add product suggestions
  if (data.products.length > 0) {
    const productHeader = document.createElement("div");
    productHeader.className = "suggestion-header";
    productHeader.textContent = "Products";
    suggestionsDiv.appendChild(productHeader);

    data.products.forEach((product) => {
      const item = document.createElement("a");
      item.href = product.url;
      item.className = "suggestion-item";
      item.textContent = product.name;
      suggestionsDiv.appendChild(item);
    });
  }

  // Add category suggestions
  if (data.categories.length > 0) {
    const categoryHeader = document.createElement("div");
    categoryHeader.className = "suggestion-header";
    categoryHeader.textContent = "Categories";
    suggestionsDiv.appendChild(categoryHeader);

    data.categories.forEach((category) => {
      const item = document.createElement("a");
      item.href = category.url;
      item.className = "suggestion-item";
      item.textContent = category.name;
      suggestionsDiv.appendChild(item);
    });
  }

  suggestionsDiv.style.display = "block";
}

// Hide suggestions when clicking outside
document.addEventListener("click", function (e) {
  if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
    suggestionsDiv.style.display = "none";
  }
});

// Hide suggestions on escape key
searchInput.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    suggestionsDiv.style.display = "none";
  }
});
