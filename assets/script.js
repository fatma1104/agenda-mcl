//  confirmation avant suppression
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
