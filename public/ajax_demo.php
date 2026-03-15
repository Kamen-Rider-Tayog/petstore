<?php require_once '../backend/includes/header.php'; ?>

<h1>AJAX Pet Search</h1>

<div style="margin-bottom: 20px;">
    <label for="species">Select Species:</label>
    <select id="species" onchange="searchPets()">
        <option value="all">All Pets</option>
        <option value="dog">Dogs</option>
        <option value="cat">Cats</option>
        <option value="rabbit">Rabbits</option>
        <option value="bird">Birds</option>
        <option value="hamster">Hamsters</option>
    </select>
</div>

<div id="loading" style="display: none; text-align: center; padding: 20px;">
    Loading...
</div>

<div id="results"></div>

<br>
<a href="index">Back to Home</a>

<script>
function searchPets() {
    const species = document.getElementById('species').value;
    const resultsDiv = document.getElementById('results');
    const loadingDiv = document.getElementById('loading');
    
    // Show loading
    loadingDiv.style.display = 'block';
    resultsDiv.innerHTML = '';
    
    // Correct path: from public folder to backend/api/
    fetch('/petstore/backend/api/search_pets.php?species=' + encodeURIComponent(species))
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            loadingDiv.style.display = 'none';
            
            if (data.count === 0) {
                resultsDiv.innerHTML = '<p>No pets found</p>';
                return;
            }
            
            let html = '<table border="1" cellpadding="5"><tr><th>Name</th><th>Species</th><th>Age</th><th>Price</th></tr>';
            
            data.data.forEach(pet => {
                html += `
                    <tr>
                        <td>${pet.name}</td>
                        <td>${pet.species}</td>
                        <td>${pet.age} years</td>
                        <td>₱${parseFloat(pet.price).toFixed(2)}</td>
                    </tr>
                `;
            });
            
            html += '</table>';
            resultsDiv.innerHTML = html;
        })
        .catch(error => {
            loadingDiv.style.display = 'none';
            resultsDiv.innerHTML = '<p style="color: red;">Error loading data: ' + error.message + '</p>';
            console.error('Error:', error);
        });
}

// Load all pets on page load
window.onload = searchPets;
</script>

<?php require_once '../backend/includes/footer.php'; ?>