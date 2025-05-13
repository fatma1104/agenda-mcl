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
            });
            
            // Désactiver tous les onglets
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
               // Activer l'onglet sélectionné
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }