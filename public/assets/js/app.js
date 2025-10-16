document.addEventListener("DOMContentLoaded", function () {
    console.log("pouet")
    const quillContainer = document.getElementById("editor");
    const hiddenTextarea = document.querySelector(".quill-editor");

    if (!quillContainer || !hiddenTextarea) return;

    const quill = new Quill(quillContainer, {
        theme: "snow",
        placeholder: "Écris ton texte ici...",
    });

    // Remplir Quill avec le contenu existant du textarea (si édition)
    if (hiddenTextarea.value) {
        quill.root.innerHTML = hiddenTextarea.value;
    }

    // Avant l’envoi du formulaire, mettre le contenu Quill dans le textarea
    const form = hiddenTextarea.closest("form");
    form.addEventListener("submit", () => {
        hiddenTextarea.value = quill.root.innerHTML;
    });
});
