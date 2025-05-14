// confirmation avant suppression
document.addEventListener("DOMContentLoaded", () => {
    const deleteButtons = document.querySelectorAll(".btn-delete");

    deleteButtons.forEach(button => {
        button.addEventListener("click", (e) => {
            const confirmDelete = confirm("Êtes-vous sûr de vouloir supprimer ceci ?");
            if (!confirmDelete) {
                e.preventDefault();
            }
        });
    });
});

 // Gestion des modals
        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
        }
        
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }
        
        function openShareModal(calendarId) {
            document.getElementById('share-calendar-id').value = calendarId;
            openModal('share-calendar-modal');
        }
        
        // Fermer en cliquant à l'extérieur
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        }
         // Gestion des onglets
        function showTab(tabId) {
            // Masquer tous les contenus
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
                document.querySelector(`.tab[onclick="showTab('shared-by-me')"]`)?.classList.add('active');
            });
            
            // Désactiver tous les onglets
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Activer l'onglet sélectionné
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }

function openEditCalendarModal(id, name, color, description) {
    try {
        // Debug: Vérifiez les valeurs reçues
        console.log('Données reçues:', {id, name, color, description});
        
        // Assurez-vous que les éléments existent
        const modal = document.getElementById('edit-calendar-modal');
        const idField = document.getElementById('editCalendarId');
        const nameField = document.getElementById('editCalendarName');
        const colorField = document.getElementById('editCalendarColor');
        const descField = document.getElementById('editCalendarDescription');
        
        if (!modal || !idField || !nameField || !colorField || !descField) {
            throw new Error('Un ou plusieurs éléments du formulaire sont introuvables');
        }
        
        // Remplissage des champs
        idField.value = id;
        nameField.value = name;
        colorField.value = color;
        descField.value = description;
        
        // Debug: Vérifiez les valeurs assignées
        console.log('Valeurs assignées:', {
            id: idField.value,
            name: nameField.value,
            color: colorField.value,
            description: descField.value
        });
        
        // Affichage du modal
        modal.style.display = 'block';
        
    } catch (error) {
        console.error('Erreur dans openEditCalendarModal:', error);
        alert('Une erreur est survenue lors du chargement des données. Voir la console pour plus de détails.');
    }
} 

// Gestion des onglets calendrier
function showCalendarTab(tabId) {
    // Masquer tous les contenus d'onglets
    document.querySelectorAll('.calendar-tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Désactiver tous les onglets
    document.querySelectorAll('.calendar-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Activer l'onglet sélectionné
    document.getElementById(tabId).classList.add('active');
    document.querySelector(`.calendar-tab[onclick="showCalendarTab('${tabId}')"]`).classList.add('active');
}