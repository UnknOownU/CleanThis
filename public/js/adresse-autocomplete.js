document.addEventListener('DOMContentLoaded', function() {
    // Récupération des éléments HTML
    var streetInput = document.querySelector('.adresse-autocomplete');
    var zipcodeInput = document.querySelector('.zipcode_ope');
    var cityInput = document.querySelector('.city_ope');
    var suggestionsContainer = document.createElement('div'); // Création de la div
    suggestionsContainer.className = 'suggestions-container'; // Ajout de la classe à la div

    // Vérification de l'existence des éléments HTML
    if (!streetInput || !zipcodeInput || !cityInput) return;

    // Fonction pour effectuer une recherche et mettre à jour les champs de code postal et de ville
    function performSearchAndUpdateInputs(query) {
        fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=5`)
            .then(response => response.json())
            .then(data => {
                suggestionsContainer.innerHTML = ''; // Nettoyer le conteneur à chaque nouvelle suggestion
                data.features.forEach((feature) => {
                    var suggestion = document.createElement('div');
                    suggestion.className = 'suggestion';
                    suggestion.innerText = feature.properties.label; // Afficher l'adresse complète dans la suggestion
                    suggestion.addEventListener('click', function() {
                        var zipcode = feature.properties.postcode || '';
                        var city = feature.properties.city || '';
                        var street = feature.properties.name || '';
                        streetInput.value = street; // Mettre à jour le champ de rue
                        zipcodeInput.value = zipcode; // Mettre à jour le champ de code postal
                        cityInput.value = city; // Mettre à jour le champ de ville
                        suggestionsContainer.innerHTML = ''; // Effacer les suggestions

                        // Ajouter une animation de transition pour le champ de rue
                        streetInput.classList.add('animated', 'bounceIn');
                        setTimeout(function() {
                            streetInput.classList.remove('animated', 'bounceIn');
                        }, 1000); // Retirer la classe d'animation après 1 seconde
                    });
                    suggestionsContainer.appendChild(suggestion);
                });
            })
            .catch(error => console.error('Erreur API:', error));

        // Ajout de la suggestionsContainer au DOM s'il n'existe pas déjà
        if (!document.querySelector('.suggestions-container')) {
            streetInput.parentNode.appendChild(suggestionsContainer);
        }
    }

    // Événement de saisie pour le champ de rue
    streetInput.addEventListener('input', function() {
        var query = streetInput.value;
        if (query.length < 3) return; // Éviter les requêtes trop fréquentes
        performSearchAndUpdateInputs(query);
    });
});
